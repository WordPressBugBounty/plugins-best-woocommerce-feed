<?php
/**
 * Habit moment events.
 *
 * @package RexTheme\RexProductFeedManager\Tracking
 * @since  7.4.47
 */

namespace RexTheme\RexProductFeedManager\Tracking\Events;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RexTheme\RexProductFeedManager\Tracking\AbstractEvent;

/**
 * Class HabitMomentEvents
 *
 * Tracks habit moment-related events.
 *
 * @package RexProductFeedManager\Tracking\Events
 * @since   7.4.47
 */
class HabitMomentEvents extends AbstractEvent {

	/**
	 * Register WordPress hooks for this event.
	 *
	 * @since 7.4.47
	 */
	public function register_hooks() {
		add_action( 'rex_product_feed_feed_settings_updated', array( $this, 'track_feed_settings_updated' ) );
		add_action( 'rex_product_feed_advanced_feature_used', array( $this, 'track_advanced_feature_used' ));
        add_action('current_screen', array( $this, 'track_page_view' ) );
	}

    /**
     * Track page views with user information
     *
     * @param \WP_Screen $screen The current screen object
     * @since 7.4.47
     */
    public function track_page_view($screen) {
        if (!is_admin() || !$screen->id) {
            return;
        }

        static $allowed_pages = [
            'edit.php?post_type=product-feed',
            'post-new.php?post_type=product-feed',
            'edit.php?post_type=product-feed&page=category_mapping',
            'edit.php?post_type=product-feed&page=merchant_settings',
            'edit.php?post_type=product-feed&page=wpfm_dashboard',
            'edit.php?post_type=product-feed&page=wpfm-license',
            'edit.php?post_type=product-feed&page=wpfm-setup-wizard'
        ];

        $current_page = $_SERVER['REQUEST_URI'] ?? '';

        // Use more efficient string matching
        $should_track = false;
        foreach ($allowed_pages as $page) {
            if (str_contains($current_page, $page)) {
                $should_track = true;
                break;
            }
        }

        if (!$should_track) {
            return;
        }

        // Get user data once to reduce function calls
        $current_user = wp_get_current_user();
        if (!$current_user->exists()) {
            return;
        }

        // Combine increment operation
        $meta_key = '_rex_page_view_count_' . $screen->id;
        $page_view_count = (int) get_user_meta($current_user->ID, $meta_key, true) + 1;
        update_user_meta($current_user->ID, $meta_key, $page_view_count);

        $this->track_habit_moment('page_view', [
            'screen_id' => $screen->id,
            'screen_base' => $screen->base,
            'view_count' => $page_view_count,
            'user_email' => $current_user->user_email
        ]);
    }

	/**
	 * Track feed settings updates.
	 *
	 * @param int   $feed_id  The feed ID.
	 * @param array $settings The updated settings (optional).
	 * @since 7.4.47
	 */
	public function track_feed_settings_updated( $feed_id, $settings = array() ) {
		$this->track_habit_moment( 'feed_updated', array(
			'settings_updated' => !empty($settings) ? array_keys( $settings ) : array('general_update'),
		) );
	}


	/**
	 * Track advanced feature usage.
	 *
	 * @param int   $feed_id      The feed ID.
	 * @param array $feature_data Additional feature data.
	 * @since 7.4.47
	 */
	public function track_advanced_feature_used( $feed_id, $feature_data = array() ) {
		$data = array(
			'feed_id' => $feed_id,
		);

		if ( ! empty( $feature_data ) ) {
			$data['feature_data'] = $feature_data;
		}

		$this->track_habit_moment( 'advanced_feature_used', $data );
	}


	/**
	 * Get the total count of feeds.
	 *
	 * @return int Feed count.
	 * @since 7.4.47
	 */
	private function get_feed_count() {
		$feed_count = wp_count_posts( 'product-feed' );
		return (int) $feed_count->publish;
	}
}
