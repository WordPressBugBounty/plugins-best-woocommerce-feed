<?php
/**
 * Main orchestrator for the feature usage tracking module.
 *
 * @package RexTheme\RexProductFeedManager\Tracking
 * @since   7.4.47
 */

namespace RexTheme\RexProductFeedManager\Tracking;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RexTheme\RexProductFeedManager\Tracking\Events\SignupEvents;
use RexTheme\RexProductFeedManager\Tracking\Events\SetupEvents;
use RexTheme\RexProductFeedManager\Tracking\Events\AhaMomentEvents;
use RexTheme\RexProductFeedManager\Tracking\Events\HabitMomentEvents;

/**
 * Class Tracker
 *
 * Initializes and manages the tracking system. It checks if tracking is
 * enabled and, if so, instantiates and initializes event handlers.
 *
 * @package RexTheme\RexProductFeedManager\Tracking
 * @since   7.4.47
 */
class Tracker {

	/**
	 * Instance of the PosthogClient.
	 *
	 * @var PosthogClient
     * @since 7.4.47
	 */
	private $client;

	/**
	 * Array of event handlers.
	 *
	 * @var array
     * @since 7.4.47
	 */
	private $event_handlers = array();

	/**
	 * Whether tracking is enabled.
	 *
	 * @var bool
     * @since 7.4.47
	 */
	private $is_tracking_enabled = false;

	/**
	 * Constructor.
	 *
	 * @since 7.4.47
	 */
	public function __construct() {
		$this->check_if_tracking_enabled();
		if ( $this->is_tracking_enabled ) {
			$this->init_client();
			$this->init_event_handlers();
            $this->client->init_backend_tracking();
		}
	}

	/**
	 * Check if tracking is enabled.
	 *
	 * @since 7.4.47
	 */
	private function check_if_tracking_enabled() {
		$this->is_tracking_enabled = apply_filters( 'rex_product_feed_tracking_enabled', true );
	}

	/**
	 * Initialize the PostHog client.
	 *
	 * @since 7.4.47
	 */
	private function init_client() {
		$this->client = new PosthogClient();
	}

	/**
	 * Initialize event handlers.
	 *
	 * @since 7.4.47
	 */
	private function init_event_handlers() {
		if ( ! $this->client ) {
			return;
		}

		// Initialize event handlers.
		$this->event_handlers = array(
			'signup' => new SignupEvents( $this->client ),
			'setup'  => new SetupEvents( $this->client ),
			'aha'    => new AhaMomentEvents( $this->client ),
			'habit'  => new HabitMomentEvents( $this->client ),
		);
	}

	/**
	 * Get the PostHog client.
	 *
	 * @return PosthogClient|null The PostHog client or null if tracking is disabled.
	 * @since 7.4.47
	 */
	public function get_client() {
		return $this->client;
	}

	/**
	 * Get an event handler by type.
	 *
	 * @param string $type The event handler type.
	 *
	 * @return object|null The event handler or null if it doesn't exist.
	 * @since 7.4.47
	 */
	public function get_event_handler( $type ) {
		return isset( $this->event_handlers[ $type ] ) ? $this->event_handlers[ $type ] : null;
	}

	/**
	 * Check if tracking is enabled.
	 *
	 * @return bool Whether tracking is enabled.
	 * @since 7.4.47
	 */
	public function is_enabled() {
		return $this->is_tracking_enabled;
	}
}
