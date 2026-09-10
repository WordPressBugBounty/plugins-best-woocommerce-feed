<?php

namespace RexTheme\Analytics\Endpoints;

use RexTheme\Analytics\Contracts\AnalyticsEndpointInterface;

abstract class AbstractAnalyticsEndpoint implements AnalyticsEndpointInterface {

	const NAMESPACE = 'wpfm/v1';

	public function permission_callback(): bool {
		return current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' );
	}

	protected function json( array $data ): \WP_REST_Response {
		return new \WP_REST_Response( $data, 200 );
	}

	/**
	 * Resolve the `start`/`end` request params to a validated date range,
	 * defaulting to the last 30 days (inclusive) when absent or invalid.
	 *
	 * @return array{start: string, end: string} Y-m-d dates.
	 */
	protected function sanitize_date_range( \WP_REST_Request $request ): array {
		$start = (string) $request->get_param( 'start' );
		$end   = (string) $request->get_param( 'end' );

		$valid = static function ( string $date ): bool {
			$parts = explode( '-', $date );
			if ( 3 !== count( $parts ) ) {
				return false;
			}
			return checkdate( (int) $parts[1], (int) $parts[2], (int) $parts[0] );
		};

		if ( ! $valid( $end ) ) {
			$end = current_time( 'Y-m-d' );
		}
		if ( ! $valid( $start ) ) {
			$start = gmdate( 'Y-m-d', strtotime( $end . ' -29 days' ) );
		}
		if ( strtotime( $start ) > strtotime( $end ) ) {
			[ $start, $end ] = [ $end, $start ];
		}

		return array(
			'start' => $start,
			'end'   => $end,
		);
	}

	/**
	 * @return array{dimension: string, value: string}
	 */
	protected function sanitize_utm_filter( \WP_REST_Request $request ): array {
		$dimension = sanitize_key( (string) $request->get_param( 'utm_dimension' ) );
		$value     = sanitize_text_field( (string) $request->get_param( 'utm_value' ) );

		return array(
			'dimension' => $dimension,
			'value'     => $value,
		);
	}
}
