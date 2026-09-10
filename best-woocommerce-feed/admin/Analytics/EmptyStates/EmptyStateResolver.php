<?php

namespace RexTheme\Analytics\EmptyStates;

/**
 * The five distinct empty-state cases from design.md Decision 6 / tasks.md
 * section 7. Each returns null when the section has real data to show, or a
 * `{code, message}` pair with copy specific to that cause — never a single
 * generic "no data" message shared across causes.
 */
class EmptyStateResolver {

	public static function no_orders(): array {
		return array(
			'code'    => 'no_orders_in_range',
			'message' => __( 'No orders yet in the selected date range.', 'rex-product-feed' ),
		);
	}

	/**
	 * @param \WC_Order[] $orders
	 */
	public static function overview( array $orders ): ?array {
		return empty( $orders ) ? self::no_orders() : null;
	}

	/**
	 * @param \WC_Order[] $orders
	 */
	public static function traffic_by_source( array $orders, int $attributed_count ): ?array {
		if ( empty( $orders ) ) {
			return self::no_orders();
		}
		if ( 0 === $attributed_count ) {
			return array(
				'code'    => 'zero_attribution_signal',
				'message' => __( 'Orders exist in this range, but none carry attribution data yet. This can happen with orders placed before this feature, or when ad-blockers/consent tools block WooCommerce’s tracking.', 'rex-product-feed' ),
			);
		}
		return null;
	}

	/**
	 * Feed Performance's own empty state. No channel-level fallback row
	 * exists in that table any more (design.md Decision 9) — the old
	 * "channel resolves, feed doesn't" case is re-homed here as the table's
	 * own empty state instead of a row.
	 *
	 * @param \WC_Order[] $orders
	 */
	public static function feed_performance( array $orders, int $feed_resolved_count, int $channel_resolved_count, bool $portfolio_non_attributable ): ?array {
		if ( empty( $orders ) ) {
			return self::no_orders();
		}
		if ( $portfolio_non_attributable ) {
			return array(
				'code'    => 'permanently_non_attributable',
				'message' => __( 'Attribution isn’t available for your current channel mix — every active feed is on a platform whose checkout model doesn’t support it. This is permanent, not a temporary gap.', 'rex-product-feed' ),
			);
		}
		if ( 0 === $feed_resolved_count && 0 === $channel_resolved_count ) {
			return array(
				'code'    => 'zero_attribution_signal',
				'message' => __( 'Orders exist in this range, but none carry attribution data yet. This can happen with orders placed before this feature, or when ad-blockers/consent tools block WooCommerce’s tracking.', 'rex-product-feed' ),
			);
		}
		if ( 0 === $feed_resolved_count ) {
			return array(
				'code'    => 'channel_only_attribution',
				'message' => __( 'Orders are attributed to a channel, but not to a specific published feed. This happens when a tracking tag was customized to an ambiguous or unmapped value, predates this feature, or the resolving feed is unpublished.', 'rex-product-feed' ),
			);
		}
		return null;
	}

	/**
	 * Top Products only counts UTM-tagged orders (design.md Decision 10) —
	 * distinct from `traffic_by_source()`'s "zero attribution signal" state:
	 * that one describes the whole store's traffic, this one is specific to
	 * why Top Products in particular looks empty when Overview doesn't.
	 */
	public static function top_products( bool $has_any_orders, bool $has_utm_orders ): ?array {
		if ( ! $has_any_orders ) {
			return self::no_orders();
		}
		if ( ! $has_utm_orders ) {
			return array(
				'code'    => 'no_utm_tagged_orders',
				'message' => __( 'Orders exist in this range, but none carry UTM data — Top Products only reflects feed/campaign-tagged traffic. Overall revenue is still shown in Overview.', 'rex-product-feed' ),
			);
		}
		return null;
	}

	/**
	 * Per-feed "recently regenerated, awaiting platform crawl" state.
	 * `$last_tagged` is the `_wpfm_analytics_tagging_last_applied` meta value
	 * (MySQL datetime string) — empty when the feed has never regenerated
	 * with tracking at all, which is a task-4.4 UI concern, not this state.
	 */
	public static function feed_awaiting_crawl( string $last_tagged, bool $has_attributed_orders ): ?array {
		if ( $has_attributed_orders || '' === $last_tagged ) {
			return null;
		}

		$tagged_time = strtotime( $last_tagged . ' UTC' );
		if ( ! $tagged_time || $tagged_time < strtotime( '-14 days' ) ) {
			return null;
		}

		return array(
			'code'    => 'awaiting_platform_crawl',
			'message' => __( 'This feed was recently regenerated with tracking. Traffic may take a few days to appear while the platform re-crawls the updated URLs.', 'rex-product-feed' ),
		);
	}
}
