<?php

namespace RexTheme\Analytics\Attribution;

/**
 * Restricts a set of orders to those whose raw captured UTM value matches a
 * chosen dimension/value pair. Operates on raw captured data, independent of
 * feed-level attribution resolution (analytics-utm-filter spec).
 */
class UtmFilter {

	const DIMENSIONS = array( 'source', 'medium', 'campaign', 'term', 'content' );

	/**
	 * @param \WC_Order[] $orders
	 * @return \WC_Order[]
	 */
	public static function apply( array $orders, string $dimension, string $value ): array {
		if ( '' === $value || ! in_array( $dimension, self::DIMENSIONS, true ) ) {
			return $orders;
		}

		return array_values(
			array_filter(
				$orders,
				static function ( \WC_Order $order ) use ( $dimension, $value ) {
					$utm = OrderAttribution::raw_utm( $order );
					return isset( $utm[ $dimension ] ) && $utm[ $dimension ] === $value;
				}
			)
		);
	}
}
