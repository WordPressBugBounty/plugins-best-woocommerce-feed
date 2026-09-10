<?php

namespace RexTheme\Analytics\Endpoints;

use RexTheme\Analytics\Services\TrafficBySourceService;

class TrafficBySourceEndpoint extends AbstractAnalyticsEndpoint {

	private TrafficBySourceService $service;

	public function __construct( TrafficBySourceService $service ) {
		$this->service = $service;
	}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/analytics/traffic-by-source',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'handle' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
	}

	public function handle( \WP_REST_Request $request ): \WP_REST_Response {
		$range    = $this->sanitize_date_range( $request );
		$utm      = $this->sanitize_utm_filter( $request );
		$page     = max( 1, (int) $request->get_param( 'page' ) );
		$per_page = max( 1, min( 100, (int) ( $request->get_param( 'per_page' ) ?: TrafficBySourceService::DEFAULT_PER_PAGE ) ) );
		return $this->json( $this->service->get_data( $range['start'], $range['end'], $utm['dimension'], $utm['value'], $page, $per_page ) );
	}
}
