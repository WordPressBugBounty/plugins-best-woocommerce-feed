<?php

namespace RexTheme\Analytics\Services;

use RexTheme\Analytics\Attribution\AttributionResolver;
use RexTheme\Analytics\Attribution\OrderAttribution;
use RexTheme\Analytics\Currency\CurrencyGuard;
use RexTheme\Analytics\EmptyStates\EmptyStateResolver;
use RexTheme\Analytics\Repositories\OrderRepository;

/**
 * Computed directly from `WC_Order` objects in range — see
 * OverviewService's doc comment for why this doesn't use `wc_order_stats`
 * (design.md Decision 15: sync lag showed $0/0-orders on fresh orders).
 *
 * Only counts orders attributed to a feed via UTM (same resolution
 * `FeedPerformanceService` uses) so this trend and Feed Performance's totals
 * are never in visible disagreement (analytics-page-polish change) — a paid
 * order with no feed attribution contributes to neither.
 */
class PerformanceOverTimeService {

	private OrderRepository $orders;

	public function __construct( OrderRepository $orders ) {
		$this->orders = $orders;
	}

	public function get_data( string $start, string $end ): array {
		$orders   = $this->orders->get_paid_orders_in_range( $start, $end );
		$filtered = CurrencyGuard::filter_orders( $orders );

		$by_date = array();
		$cursor  = $start;
		while ( strtotime( $cursor ) <= strtotime( $end ) ) {
			$by_date[ $cursor ] = array(
				'date'    => $cursor,
				'revenue' => 0.0,
				'orders'  => 0,
			);
			$cursor = gmdate( 'Y-m-d', strtotime( $cursor . ' +1 day' ) );
		}

		$attributed_count = 0;

		foreach ( $filtered['kept'] as $order ) {
			$utm = OrderAttribution::raw_utm( $order );
			if ( '' === $utm['term'] ) {
				continue;
			}

			$date_created = $order->get_date_created();
			$order_date   = $date_created ? $date_created->date( 'Y-m-d H:i:s' ) : current_time( 'mysql', true );
			$resolution   = AttributionResolver::resolve( $utm['term'], $order_date );
			if ( 'resolved' !== $resolution['status'] ) {
				continue;
			}

			$day = $date_created ? gmdate( 'Y-m-d', $date_created->getOffsetTimestamp() ) : null;
			if ( null === $day || ! isset( $by_date[ $day ] ) ) {
				continue;
			}

			$attributed_count++;
			$by_date[ $day ]['revenue'] += (float) $order->get_total() - (float) $order->get_total_refunded();
			$by_date[ $day ]['orders']++;
		}

		foreach ( $by_date as &$day ) {
			$day['revenue'] = round( $day['revenue'], 2 );
		}
		unset( $day );

		return array(
			'series'      => array_values( $by_date ),
			'currency'    => CurrencyGuard::base_currency(),
			'empty_state' => 0 === $attributed_count ? EmptyStateResolver::no_orders() : null,
		);
	}
}
