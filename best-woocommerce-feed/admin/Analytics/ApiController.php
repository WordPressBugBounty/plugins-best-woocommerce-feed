<?php

namespace RexTheme\Analytics;

use RexTheme\Analytics\Attribution\UtmHistoryTable;
use RexTheme\Analytics\Endpoints\FeedPerformanceEndpoint;
use RexTheme\Analytics\Endpoints\OverviewEndpoint;
use RexTheme\Analytics\Endpoints\PerformanceOverTimeEndpoint;
use RexTheme\Analytics\Endpoints\TopProductsEndpoint;
use RexTheme\Analytics\Endpoints\TrafficBySourceEndpoint;
use RexTheme\Analytics\Endpoints\UtmFilterOptionsEndpoint;
use RexTheme\Analytics\Repositories\OrderRepository;
use RexTheme\Analytics\Repositories\OrderStatsRepository;
use RexTheme\Analytics\Services\FeedPerformanceService;
use RexTheme\Analytics\Services\OverviewService;
use RexTheme\Analytics\Services\PerformanceOverTimeService;
use RexTheme\Analytics\Services\TopProductsService;
use RexTheme\Analytics\Services\TrafficBySourceService;
use RexTheme\Analytics\Services\UtmFilterOptionsService;

class ApiController {

	public function init(): void {
		// Self-heals the UTM history table on upgrade too, not only on
		// (re)activation — see UtmHistoryTable::maybe_create()'s doc comment.
		add_action( 'init', array( __CLASS__, 'maybe_create_tables' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public static function maybe_create_tables(): void {
		UtmHistoryTable::maybe_create();
	}

	public function register_routes(): void {
		$order_repo = new OrderRepository();
		$stats_repo = new OrderStatsRepository();

		$endpoints = array(
			new OverviewEndpoint( new OverviewService( $order_repo ) ),
			new PerformanceOverTimeEndpoint( new PerformanceOverTimeService( $order_repo ) ),
			new TrafficBySourceEndpoint( new TrafficBySourceService( $order_repo, $stats_repo ) ),
			new FeedPerformanceEndpoint( new FeedPerformanceService( $order_repo, $stats_repo ) ),
			new TopProductsEndpoint( new TopProductsService( $order_repo ) ),
			new UtmFilterOptionsEndpoint( new UtmFilterOptionsService( $order_repo ) ),
		);

		foreach ( $endpoints as $endpoint ) {
			$endpoint->register_routes();
		}
	}
}
