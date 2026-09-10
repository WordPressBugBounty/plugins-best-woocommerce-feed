<?php

namespace RexTheme\Analytics\Repositories;

use RexTheme\Analytics\Currency\CurrencyGuard;

/**
 * Reads WooCommerce's own `wc_order_product_lookup` table — a per-order-item
 * report table WC core keeps in sync itself, indexed by `product_id` and
 * `date_created`, with `product_net_revenue` already refund-adjusted. A
 * single grouped query returns the ranked product list without hydrating any
 * `WC_Order` object or its line items (design.md Decisions 8/10) — the tool
 * WooCommerce ships specifically for this, at any order/product volume.
 *
 * Optionally gates results to orders carrying at least one captured UTM
 * value, branching between HPOS's own order-meta table and legacy postmeta
 * (design.md Decision 10).
 */
class OrderProductLookupRepository {

	private static ?bool $table_exists = null;

	public function table_available(): bool {
		if ( null !== self::$table_exists ) {
			return self::$table_exists;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'wc_order_product_lookup';
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a constant derived from $wpdb->prefix.
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		self::$table_exists = ( $found === $table );
		return self::$table_exists;
	}

	/**
	 * @return array<int, array{product_id: int, orders: int, revenue: float}>
	 */
	public function get_top_products_with_utm( string $start, string $end, int $limit ): array {
		global $wpdb;
		$table       = $wpdb->prefix . 'wc_order_product_lookup';
		$utm_filter  = $this->utm_order_id_subquery();
		$currency    = CurrencyGuard::order_id_currency_fragment();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a constant derived from $wpdb->prefix; $utm_filter/$currency['sql'] are fixed fragments built below, not user input.
		$sql = $wpdb->prepare(
			"SELECT product_id,
				COUNT(DISTINCT order_id) AS orders,
				COALESCE(SUM(product_net_revenue), 0) AS revenue
			FROM {$table}
			WHERE date_created BETWEEN %s AND %s
			AND product_id > 0
			{$utm_filter['sql']}
			{$currency['sql']}
			GROUP BY product_id
			ORDER BY revenue DESC
			LIMIT %d",
			array_merge(
				array( $start . ' 00:00:00', $end . ' 23:59:59' ),
				$utm_filter['params'],
				$currency['params'],
				array( $limit )
			)
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql built via $wpdb->prepare() above.
		$rows = $wpdb->get_results( $sql, ARRAY_A );

		return array_map(
			static fn( $row ) => array(
				'product_id' => (int) $row['product_id'],
				'orders'     => (int) $row['orders'],
				'revenue'    => round( (float) $row['revenue'], 2 ),
			),
			$rows ?: array()
		);
	}

	/**
	 * Whether ANY order in the range carries UTM data at all — used to pick
	 * between the "no orders" and "orders exist, none tagged" empty states
	 * without running the full aggregation query.
	 */
	public function has_any_utm_orders_in_range( string $start, string $end ): bool {
		global $wpdb;
		$table      = $wpdb->prefix . 'wc_order_product_lookup';
		$utm_filter = $this->utm_order_id_subquery();

		if ( '' === $utm_filter['sql'] ) {
			return true; // No UTM meta table reachable — don't block on this check.
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a constant derived from $wpdb->prefix; $utm_filter['sql'] is a fixed fragment, not user input.
		$sql = $wpdb->prepare(
			"SELECT 1 FROM {$table}
			WHERE date_created BETWEEN %s AND %s
			{$utm_filter['sql']}
			LIMIT 1",
			array_merge(
				array( $start . ' 00:00:00', $end . ' 23:59:59' ),
				$utm_filter['params']
			)
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql built via $wpdb->prepare() above.
		return null !== $wpdb->get_var( $sql );
	}

	/**
	 * @return array{sql: string, params: array}
	 */
	private function utm_order_id_subquery(): array {
		global $wpdb;

		$utm_keys = array(
			'_wc_order_attribution_utm_source',
			'_wc_order_attribution_utm_medium',
			'_wc_order_attribution_utm_campaign',
			'_wc_order_attribution_utm_term',
			'_wc_order_attribution_utm_content',
		);
		$key_placeholders = implode( ',', array_fill( 0, count( $utm_keys ), '%s' ) );

		$hpos = class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' )
			&& \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();

		if ( $hpos ) {
			$meta_table = $wpdb->prefix . 'wc_orders_meta';
			return array(
				'sql'    => " AND order_id IN ( SELECT order_id FROM {$meta_table} WHERE meta_key IN ({$key_placeholders}) AND meta_value != '' ) ",
				'params' => $utm_keys,
			);
		}

		return array(
			'sql'    => " AND order_id IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ({$key_placeholders}) AND meta_value != '' ) ",
			'params' => $utm_keys,
		);
	}
}
