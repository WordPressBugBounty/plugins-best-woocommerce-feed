<?php
/**
 * Setup moment events.
 *
 * @package RexTheme\RexProductFeedManager\Tracking
 * @since   7.4.47
 */

namespace RexTheme\RexProductFeedManager\Tracking\Events;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RexTheme\RexProductFeedManager\Tracking\AbstractEvent;

/**
 * Class SetupEvents
 *
 * Tracks setup-related events.
 *
 * @package RexProductFeedManager\Tracking\Events
 * @since   7.4.47
 */
class SetupEvents extends AbstractEvent {

	/**
	 * Register WordPress hooks for this event.
	 *
	 * @since 7.4.47
	 */
	public function register_hooks() {
		add_action( 'rex_product_feed_feed_created', array( $this, 'track_feed_created' ), 10, 2 );
	}

    /**
	 * Track feed creation.
	 *
	 * @param int   $feed_id The feed ID.
	 * @param array $config  The feed configuration.
	 * @since 7.4.47
	 */
    public function track_feed_created( $feed_id, $config ) {
        $this->track_setup_moment( 'feed_generated', array(
            'feed_type' => isset( $config['feed_type'] ) ? $config['feed_type'] : '',
            'merchant' => isset( $config['merchant'] ) ? $config['merchant'] : '',
            'feed_title' => isset( $config['feed_title'] ) ? $config['feed_title'] : '',
        ) );
    }

}
