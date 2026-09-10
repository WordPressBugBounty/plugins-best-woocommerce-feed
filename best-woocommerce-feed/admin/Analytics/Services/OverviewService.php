<?php

namespace RexTheme\Analytics\Services;

use RexTheme\Analytics\Currency\CurrencyGuard;
use RexTheme\Analytics\EmptyStates\EmptyStateResolver;
use RexTheme\Analytics\Repositories\OrderRepository;

/**
 * Revenue/AOV are computed directly from `WC_Order` objects in range
 * (refund-adjusted via `get_total() - get_total_refunded()`, base-currency-
 * only — design.md Decisions 7/8), matching `FeedPerformanceService`'s
 * proven-correct approach.
 *
 * Originally sourced from `wc_order_stats` for a single grouped query
 * instead of hydrating every order. Reverted (see design.md Decision 15):
 * that table is synced by WooCommerce core on a delay (Action Scheduler),
 * not synchronously with order placement — a store's very first order
 * showed 0 revenue here for a real, non-trivial window while
 * `wc_order_stats` caught up, which reads as "broken" on exactly the
 * moment a merchant is most likely to check this page. Direct order
 * iteration has no such lag; the scale trade-off that motivated the
 * report-table approach doesn't apply here the way it does for Top
 * Products' per-product ranking (see TopProductsService).
 */
class OverviewService {

	private OrderRepository $orders;

	public function __construct( OrderRepository $orders ) {
		$this->orders = $orders;
	}

	public function get_data( string $start, string $end ): array {
		$current_stats = $this->totals( $start, $end );
		$comparison    = $this->build_comparison( $start, $end, $current_stats );
		$excluded      = $current_stats['excluded'];

		$portfolio = $this->active_feed_portfolio();

		return array(
			'active_channels'          => $portfolio['channels'],
			'active_feeds'             => $portfolio['feeds'],
			'revenue'                  => $current_stats['revenue'],
			'orders'                   => $current_stats['orders'],
			'aov'                      => $current_stats['aov'],
			'currency'                 => CurrencyGuard::base_currency(),
			'currency_excluded_orders' => $excluded,
			'comparison'               => $comparison,
			'empty_state'              => 0 === $current_stats['orders'] ? EmptyStateResolver::no_orders() : null,
		);
	}

	/**
	 * Current counts, not date-range-scoped (design.md Decision 14 — "what's
	 * live right now", not "what happened in this range"). Active Channels is
	 * the count of *distinct merchants* in use across published feeds, not
	 * the full ~75-platform registry.
	 *
	 * @return array{feeds: int, channels: int}
	 */
	private function active_feed_portfolio(): array {
		$feed_ids = get_posts(
			array(
				'post_type'      => 'product-feed',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => -1,
			)
		);

		$merchants = array();
		foreach ( $feed_ids as $feed_id ) {
			$merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true ) ?: get_post_meta( $feed_id, 'rex_feed_merchant', true );
			if ( '' !== (string) $merchant ) {
				$merchants[ (string) $merchant ] = true;
			}
		}

		return array(
			'feeds'    => count( $feed_ids ),
			'channels' => count( $merchants ),
		);
	}

	private function build_comparison( string $start, string $end, array $current_stats ): ?array {
		$days        = (int) round( ( strtotime( $end ) - strtotime( $start ) ) / DAY_IN_SECONDS ) + 1;
		$prior_end   = gmdate( 'Y-m-d', strtotime( $start . ' -1 day' ) );
		$prior_start = gmdate( 'Y-m-d', strtotime( $prior_end . ' -' . ( $days - 1 ) . ' days' ) );

		$prior_stats = $this->totals( $prior_start, $prior_end );
		if ( 0 === $prior_stats['orders'] ) {
			return null;
		}

		return array(
			'revenue_change_pct' => $this->pct_change( $prior_stats['revenue'], $current_stats['revenue'] ),
			'orders_change_pct'  => $this->pct_change( $prior_stats['orders'], $current_stats['orders'] ),
			'aov_change_pct'     => $this->pct_change( $prior_stats['aov'], $current_stats['aov'] ),
			'prior_period'       => array(
				'start' => $prior_start,
				'end'   => $prior_end,
			),
		);
	}

	private function totals( string $start, string $end ): array {
		$orders   = $this->orders->get_paid_orders_in_range( $start, $end );
		$filtered = CurrencyGuard::filter_orders( $orders );

		$revenue = 0.0;
		foreach ( $filtered['kept'] as $order ) {
			$revenue += (float) $order->get_total() - (float) $order->get_total_refunded();
		}
		$count = count( $filtered['kept'] );

		return array(
			'revenue'  => round( $revenue, 2 ),
			'orders'   => $count,
			'aov'      => $count > 0 ? round( $revenue / $count, 2 ) : 0.0,
			'excluded' => $filtered['excluded'],
		);
	}

	private function pct_change( float $old, float $new ): ?float {
		if ( 0.0 === $old ) {
			return null;
		}
		return round( ( ( $new - $old ) / $old ) * 100, 1 );
	}
}
