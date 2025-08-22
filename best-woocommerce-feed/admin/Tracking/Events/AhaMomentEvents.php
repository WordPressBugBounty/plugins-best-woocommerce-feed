<?php
/**
 * Aha moment events.
 *
 * @package RexTheme\RexProductFeedManager\Tracking
 * @since 7.4.47
 */

namespace RexTheme\RexProductFeedManager\Tracking\Events;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use RexTheme\RexProductFeedManager\Tracking\AbstractEvent;

/**
 * Class AhaMomentEvents
 *
 * Tracks aha moment-related events.
 *
 * @package RexProductFeedManager\Tracking\Events
 * @since 7.4.47
 */
class AhaMomentEvents extends AbstractEvent {

    /**
     * Register WordPress hooks for this event.
     *
     * @since 7.4.47
     */
    public function register_hooks() {
        add_action( 'rex_product_feed_first_successful_generation', array( $this, 'track_first_successful_generation' ) );
        add_action( 'rex_product_feed_advanced_feature_used', array( $this, 'track_advanced_feature_used' ));
        add_action( 'rex_product_feed_successful_generation', array( $this, 'track_successful_generation' ) );
        add_action( 'rex_product_feed_scheduler_generate', array( $this, 'track_successful_schedule_feed_generation' ), 999, 1 );
    }



    /**
     * Track first successful feed generation.
     *
     * @param int $feed_id The feed ID.
     * @since 7.4.47
     */
    public function track_first_successful_generation( $feed_id ) {
        $feed_type = get_post_meta( $feed_id, '_rex_feed_feed_format', true );
        $merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );

        $this->track_aha_moment( 'first_feed_generated', array(
            'feed_type' => $feed_type,
            'merchant' => $merchant,
        ) );
    }

    /**
     * Track successful feed generation.
     *
     * @param int $feed_id The feed ID.
     * @since 7.4.47
     */
    public function track_successful_generation( $feed_id ) {
        $feed_type = get_post_meta( $feed_id, '_rex_feed_feed_format', true );
        $merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );

        $this->track_aha_moment( 'feed_generated', array(
            'feed_type' => $feed_type,
            'merchant' => $merchant,
        ) );
    }


    /**
     * Track advanced feature usage.
     *
     * @param int   $feed_id The feed ID.
     * @param array $data    Additional data about the feature usage including action name.
     * @since 7.4.47
     */
    public function track_advanced_feature_used( $feed_id, $data = array() ) {
        $feed_type = get_post_meta( $feed_id, '_rex_feed_feed_format', true );
        $merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );

        $this->track_aha_moment( 'advanced_feature_used', array(
            'feed_type' => $feed_type,
            'merchant' => $merchant,
            'action' => isset( $data['action'] ) ? $data['action'] : '',
        ) );
    }

    /**
     * Track successful scheduled feed generation.
     *
     * @param int $feed_id The feed ID.
     * @since 7.4.47
     */
    public function track_successful_schedule_feed_generation($feed_id ){
        $feed_type = get_post_meta( $feed_id, '_rex_feed_feed_format', true );
        $merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );
        $this->track_aha_moment( 'scheduled_feed_generated', array(
            'feed_type' => $feed_type,
            'merchant' => $merchant
        ) );
    }

}
