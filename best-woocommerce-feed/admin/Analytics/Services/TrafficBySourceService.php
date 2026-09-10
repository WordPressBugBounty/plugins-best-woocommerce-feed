<?php

namespace RexTheme\Analytics\Services;

use RexTheme\Analytics\Attribution\OrderAttribution;
use RexTheme\Analytics\Attribution\UtmFilter;
use RexTheme\Analytics\Currency\CurrencyGuard;
use RexTheme\Analytics\EmptyStates\EmptyStateResolver;
use RexTheme\Analytics\Repositories\OrderRepository;
use RexTheme\Analytics\Repositories\OrderStatsRepository;

/**
 * The "UTM analytics" table — owns the UTM dimension/value filter and
 * pagination (design.md Decision 12); no other Analytics section is
 * filterable by UTM dimension any more.
 */
class TrafficBySourceService {

	const DEFAULT_PER_PAGE = 5;

	private OrderRepository $orders;
	private OrderStatsRepository $stats;

	public function __construct( OrderRepository $orders, OrderStatsRepository $stats ) {
		$this->orders = $orders;
		$this->stats  = $stats;
	}

	public function get_data(
		string $start,
		string $end,
		string $utm_dimension = '',
		string $utm_value = '',
		int $page = 1,
		int $per_page = self::DEFAULT_PER_PAGE
	): array {
		$orders   = $this->orders->get_paid_orders_in_range( $start, $end );
		$filtered = CurrencyGuard::filter_orders( $orders );
		$orders   = UtmFilter::apply( $filtered['kept'], $utm_dimension, $utm_value );

		$net_totals = $this->stats->get_net_totals_by_order_id(
			array_map( static fn( \WC_Order $order ) => $order->get_id(), $orders )
		);

		$rows       = array();
		$attributed = 0;

		foreach ( $orders as $order ) {
			$attribution = OrderAttribution::extract( $order );
			$raw_utm     = OrderAttribution::raw_utm( $order );
			$label       = $attribution['is_referral']
				/* translators: %s: referrer domain or source-type. */
				? sprintf( __( '%s (Referrer, non-UTM)', 'rex-product-feed' ), $attribution['source'] )
				: $attribution['source'];
			$key = implode( '|', array( $label, $attribution['medium'], $raw_utm['campaign'], $raw_utm['content'] ?? '' ) );

			if ( $attribution['is_attributed'] ) {
				$attributed++;
			}

			if ( ! isset( $rows[ $key ] ) ) {
				$rows[ $key ] = array(
					'source'          => $label,
					'medium'          => $attribution['medium'],
					'campaign'        => $raw_utm['campaign'],
					'content'         => $raw_utm['content'] ?? '',
					'orders'          => 0,
					'revenue'         => 0.0,
					'visitors'        => null, // Not available: no visitor/session tracking exists (analytics-traffic-by-source spec).
					'conversion_rate' => null,
				);
			}

			$order_id = $order->get_id();
			$revenue  = $net_totals[ $order_id ] ?? ( (float) $order->get_total() - (float) $order->get_total_refunded() );

			$rows[ $key ]['orders']++;
			$rows[ $key ]['revenue'] += $revenue;
		}

		foreach ( $rows as &$row ) {
			$row['revenue'] = round( $row['revenue'], 2 );
			$row['aov']     = $row['orders'] > 0 ? round( $row['revenue'] / $row['orders'], 2 ) : 0.0;
		}
		unset( $row );

		usort( $rows, static fn( $a, $b ) => $b['revenue'] <=> $a['revenue'] );
		$rows = array_values( $rows );

		$total     = count( $rows );
		$per_page  = max( 1, $per_page );
		$page      = max( 1, $page );
		$page_rows = array_slice( $rows, ( $page - 1 ) * $per_page, $per_page );

		return array(
			'rows'                     => $page_rows,
			'currency'                 => CurrencyGuard::base_currency(),
			'currency_excluded_orders' => $filtered['excluded'],
			'pagination'               => array(
				'page'     => $page,
				'per_page' => $per_page,
				'total'    => $total,
			),
			'empty_state'              => EmptyStateResolver::traffic_by_source( $orders, $attributed ),
		);
	}
}
