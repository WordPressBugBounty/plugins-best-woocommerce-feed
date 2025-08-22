<?php
/**
 * Signup moment events.
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
 * Class SignupEvents
 *
 * Tracks signup-related events.
 *
 * @package RexProductFeedManager\Tracking\Events
 * @since   7.4.47
 */
class SignupEvents extends AbstractEvent {

	/**
	 * Register WordPress hooks for this event.
	 *
	 * @since 7.4.47
	 */
	public function register_hooks() {
		add_action( 'rex_product_feed_activated', array( $this, 'track_plugin_activation' ) );
		add_action( 'rex_product_feed_first_feed_published', array( $this, 'track_first_feed_published' ) );
	}

	/**
	 * Track plugin activation.
	 *
	 * @since 7.4.47
	 */
	public function track_plugin_activation() {
		$this->track_signup_moment( 'plugin_activation', array(
			'is_new_installation' => is_null(get_site_option('rex_wpfm_version', null)),
		) );
	}

	/**
	 * Track first feed published.
	 *
	 * @param int $feed_id The feed ID.
	 * @since 7.4.47
	 */
	public function track_first_feed_published( $feed_id ) {
		$feed = get_post( $feed_id );

		if ( ! $feed ) {
			return;
		}

		$feed_type = get_post_meta( $feed_id, 'feed_type', true );

		$this->track_signup_moment( 'first_feed_generated', array(
			'feed_type' => $feed_type,
			'feed_title' => $feed->post_title,
		) );
	}
}
