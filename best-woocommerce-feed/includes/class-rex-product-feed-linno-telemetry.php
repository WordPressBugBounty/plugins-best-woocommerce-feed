<?php

use Linno\Telemetry\Client;

/**
 * Linno telemetry integration for Product Feed Manager.
 */
class Rex_Product_Feed_Linno_Telemetry {

    /**
     * Linno API key.
     *
     * @var string
     */
    private $api_key = '1aa16c66-3002-402c-b043-87aaa3dd26b4';

    /**
     * Linno API secret.
     *
     * @var string
     */
    private $api_secret = 'sec_3a27bf9b64279c58cb0e';

    /**
     * Bootstrap telemetry hooks.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'init_client' ), 1 );
        add_action( 'transition_post_status', array( $this, 'maybe_track_manual_publish' ), 10, 3 );
    }

    /**
     * Initialize Linno telemetry client and trigger mapping.
     *
     * @return void
     */
    public function init_client() {
        if ( ! class_exists( 'Linno\\Telemetry\\Client' ) || ! defined( 'WPFM__FILE__' ) ) {
            return;
        }

        Client::set_text_domain( 'rex-product-feed' );

        $telemetry_client = new Client(
            $this->api_key,
            $this->api_secret,
            'Product Feed Manager for WooCommerce',
            WPFM__FILE__
        );

        $telemetry_client->define_triggers(
            array(
                'setup'        => 'rex_product_feed_setup_completed',
                'first_strike' => 'rex_product_feed_first_strike',
                'kui'          => array(
                    'feed_published' => array(
                        'hook'      => 'rex_product_feed_feed_published',
                        'threshold' => array(
                            'count'  => 2,
                            'period' => 'week',
                        ),
                        'callback'  => array( $this, 'build_kui_payload' ),
                    ),
                ),
            )
        );

        $telemetry_client->init();
    }

    /**
     * Track first publish transition and map to first_strike + KUI manual source.
     *
     * @param string  $new_status New post status.
     * @param string  $old_status Previous post status.
     * @param WP_Post $post       Post object.
     *
     * @return void
     */
    public function maybe_track_manual_publish( $new_status, $old_status, $post ) {
        if ( 'product-feed' !== $post->post_type ) {
            return;
        }

        if ( 'publish' !== $new_status || 'publish' === $old_status ) {
            return;
        }

        if ( ! $this->is_setup_wizard_create_request() ) {
            do_action( 'rex_product_feed_feed_published', $post->ID, 'manual' );
        }

        $first_strike_tracked = get_option( 'rex_feed_first_strike_tracked', 'no' );
        if ( 'yes' !== $first_strike_tracked ) {
            update_option( 'rex_feed_first_strike_tracked', 'yes' );
            do_action( 'rex_product_feed_first_strike', $post->ID );
        }
    }

    /**
     * Determine whether the current request is setup wizard feed creation.
     *
     * @return bool
     */
    private function is_setup_wizard_create_request() {
        if ( ! function_exists( 'wp_doing_ajax' ) || ! wp_doing_ajax() ) {
            return false;
        }

        $action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

        return 'pfm_create_feed' === $action;
    }

    /**
     * Build KUI event payload.
     *
     * @param int    $feed_id Feed ID.
     * @param string $source  Trigger source.
     *
     * @return array
     */
    public function build_kui_payload( $feed_id, $source = 'manual' ) {
        return array(
            'feed_id'   => (int) $feed_id,
            'source'    => sanitize_text_field( (string) $source ),
            'merchant'  => (string) get_post_meta( $feed_id, '_rex_feed_merchant', true ),
            'format'    => (string) get_post_meta( $feed_id, '_rex_feed_feed_format', true ),
            'published' => current_time( 'mysql' ),
        );
    }
}

new Rex_Product_Feed_Linno_Telemetry();
