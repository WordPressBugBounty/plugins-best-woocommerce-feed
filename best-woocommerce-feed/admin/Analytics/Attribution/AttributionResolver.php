<?php

namespace RexTheme\Analytics\Attribution;

/**
 * Feed-level attribution: default-UTM-fill on feed save/regen, the
 * `utm_term`-to-feed history table it feeds, and the resolution algorithm
 * that maps a captured order `utm_term` back to the feed that generated it,
 * valid at the order's placed date (design.md Decision 1).
 */
class AttributionResolver {

	/**
	 * Fill any blank UTM field with a computed default, leaving merchant-set
	 * fields untouched. Called from the shared feed-generation payload
	 * builder, so it runs on both manual regeneration and the existing
	 * scheduled cadence — no new scheduling mechanism needed.
	 *
	 * @return array The merged UTM params, persisted back to post meta.
	 */
	public static function fill_defaults( int $feed_id, string $merchant ): array {
		$stored = get_post_meta( $feed_id, '_rex_feed_analytics_params', true );
		$stored = is_array( $stored ) ? $stored : array();

		$defaults = array(
			'utm_source'   => $merchant ?: 'feed',
			'utm_medium'   => 'feed',
			'utm_campaign' => $merchant ?: 'feed',
			'utm_term'     => 'pfm-feed-' . $feed_id,
		);

		$merged = $stored;
		foreach ( $defaults as $key => $default_value ) {
			$existing = trim( (string) ( $stored[ $key ] ?? '' ) );
			$merged[ $key ] = ( '' !== $existing && 'auto-draft' !== $existing ) ? $existing : $default_value;
		}

		// Compared against the last value *we recorded a history row for*,
		// not the current stored meta — the merchant can change
		// `_rex_feed_analytics_params` directly via the feed settings form
		// (admin/class-rex-product-feed-handle-data.php) between
		// regenerations, so by the time this next runs the stored value
		// already reflects their edit. Using a separate bookkeeping meta key
		// is what lets that edit still be detected as a change.
		$new_term       = $merged['utm_term'];
		$last_recorded  = (string) get_post_meta( $feed_id, '_wpfm_analytics_last_history_term', true );
		if ( $new_term !== $last_recorded ) {
			self::record_term_change( $feed_id, $last_recorded, $new_term );
			update_post_meta( $feed_id, '_wpfm_analytics_last_history_term', $new_term );
		}

		update_post_meta( $feed_id, '_rex_feed_analytics_params', $merged );
		update_post_meta( $feed_id, '_wpfm_analytics_tagging_last_applied', current_time( 'mysql', true ) );

		return $merged;
	}

	/**
	 * Close the previous open history row for this feed (if any) and open a
	 * new one for the new value. A blank old/new term is skipped (nothing to
	 * close / nothing to open) — this only happens on a feed's first save.
	 */
	private static function record_term_change( int $feed_id, string $old_term, string $new_term ): void {
		global $wpdb;
		$table = UtmHistoryTable::table_name();
		$now   = current_time( 'mysql', true );

		if ( '' !== $old_term ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a constant derived from $wpdb->prefix.
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table} SET effective_to = %s WHERE feed_id = %d AND value = %s AND effective_to IS NULL",
					$now,
					$feed_id,
					$old_term
				)
			);
		}

		if ( '' !== $new_term ) {
			$wpdb->insert(
				$table,
				array(
					'value'          => $new_term,
					'feed_id'        => $feed_id,
					'effective_from' => $now,
					'effective_to'   => null,
				),
				array( '%s', '%d', '%s', '%s' )
			);
		}
	}

	/**
	 * Resolve a captured order `utm_term` to a feed ID, valid at the order's
	 * placed date.
	 *
	 * @return array{status: string, feed_id: int|null} status is one of
	 *         'resolved', 'unresolved' (zero matches — channel-level only),
	 *         'ambiguous' (more than one match — never guessed).
	 */
	public static function resolve( string $utm_term, string $order_date ): array {
		$utm_term = trim( $utm_term );
		if ( '' === $utm_term ) {
			return array(
				'status'  => 'unresolved',
				'feed_id' => null,
			);
		}

		// Fast path: the default, untouched value — no table lookup needed.
		if ( preg_match( '/^pfm-feed-(\d+)$/', $utm_term, $matches ) ) {
			return array(
				'status'  => 'resolved',
				'feed_id' => (int) $matches[1],
			);
		}

		global $wpdb;
		$table = UtmHistoryTable::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a constant derived from $wpdb->prefix.
		$feed_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT feed_id FROM {$table} WHERE value = %s AND effective_from <= %s AND ( effective_to IS NULL OR effective_to > %s )",
				$utm_term,
				$order_date,
				$order_date
			)
		);
		$feed_ids = array_values( array_unique( array_map( 'intval', $feed_ids ) ) );

		if ( 1 === count( $feed_ids ) ) {
			return array(
				'status'  => 'resolved',
				'feed_id' => $feed_ids[0],
			);
		}

		if ( count( $feed_ids ) > 1 ) {
			return array(
				'status'  => 'ambiguous',
				'feed_id' => null,
			);
		}

		return array(
			'status'  => 'unresolved',
			'feed_id' => null,
		);
	}
}
