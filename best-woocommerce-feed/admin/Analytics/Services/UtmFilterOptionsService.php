<?php

namespace RexTheme\Analytics\Services;

use RexTheme\Analytics\Attribution\OrderAttribution;
use RexTheme\Analytics\Repositories\OrderRepository;

class UtmFilterOptionsService {

	private OrderRepository $orders;

	public function __construct( OrderRepository $orders ) {
		$this->orders = $orders;
	}

	public function get_data( string $start, string $end ): array {
		$orders = $this->orders->get_paid_orders_in_range( $start, $end );

		$values = array(
			'source'   => array(),
			'medium'   => array(),
			'campaign' => array(),
			'term'     => array(),
			'content'  => array(),
		);

		foreach ( $orders as $order ) {
			$utm = OrderAttribution::raw_utm( $order );
			foreach ( $values as $dimension => &$set ) {
				if ( '' !== $utm[ $dimension ] ) {
					$set[ $utm[ $dimension ] ] = true;
				}
			}
			unset( $set );
		}

		foreach ( $values as &$set ) {
			$set = array_values( array_keys( $set ) );
			sort( $set );
		}
		unset( $set );

		return $values;
	}
}
