<?php

namespace RexTheme\Analytics\Endpoints;

use RexTheme\Analytics\Services\OverviewService;

class OverviewEndpoint extends AbstractAnalyticsEndpoint {

	private OverviewService $service;

	public function __construct( OverviewService $service ) {
		$this->service = $service;
	}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/analytics/overview',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'handle' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
	}

	public function handle( \WP_REST_Request $request ): \WP_REST_Response {
		$range = $this->sanitize_date_range( $request );
		return $this->json( $this->service->get_data( $range['start'], $range['end'] ) );
	}
}
