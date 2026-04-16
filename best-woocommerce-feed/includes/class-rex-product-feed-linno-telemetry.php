<?php

use LinnoSDK\Telemetry\Client;

/**
 * Linno telemetry integration for Product Feed Manager.
 */
class Rex_Product_Feed_Linno_Telemetry {

    /**
     * PostHog project API key.
     *
     * @var string
     */
    private $posthog_api_key = 'phc_h9bEsVUzRaHIJmF3sHWFdjM1mLHdntzXnebp5FRwlLr';

    /**
     * Bootstrap telemetry hooks.
     */
    public function __construct() {
        $this->init_client();
        add_action( 'transition_post_status', array( $this, 'maybe_track_manual_publish' ), 10, 3 );
        add_action( 'rex_product_feed_feed_published', array( $this, 'maybe_track_aha' ) );
        add_action( 'rex_product_feed_consent_updated', array( $this, 'handle_consent_updated' ) );
    }

    /**
     * Initialize Linno telemetry client and trigger mapping.
     *
     * @return void
     */
    public function init_client() {
        global $telemetry_client;
        if ( ! class_exists( 'LinnoSDK\\Telemetry\\Client' ) || ! defined( 'WPFM__FILE__' ) ) {
            return;
        }

        Client::set_text_domain( 'rex-product-feed' );

        $telemetry_client = new Client(
            array(
                'pluginFile'    => WPFM__FILE__,
                'slug'          => 'product-feed-manager',
                'pluginName'    => 'Product Feed Manager for WooCommerce',
                'version'       => WPFM_VERSION,
                'driver'        => 'posthog',
                'driver_config' => array(
                    'host'    => 'https://eu.i.posthog.com',
                    'api_key' => $this->posthog_api_key,
                ),
            )
        );

        $telemetry_client->define_triggers(
            array(
                'onboarding'   => 'rex_product_feed_setup_completed',
                'feature_used' => array(
                    'feed_generation'  => array(
                        'hook' => 'rex_product_feed_feed_published',
                        'callback'  => function( $feed_id ) {
                            $properties = array(
                                'category_mapping'      => 'no',
                                'feed_filter_rules'     => 'no',
                                'merchant'              => (string) get_post_meta( $feed_id, '_rex_feed_merchant', true ),
                                'scheduled'             => get_post_meta( $feed_id, '_rex_feed_schedule', true ) ,
                                'format'                => get_post_meta( $feed_id, '_rex_feed_feed_format', true ) ,
                                'google_content_api'    => get_post_meta( $feed_id, '_rex_feed_is_google_content_api', true )
                            );
                            $google_product_category = get_post_meta( $feed_id, '_rex_feed_google_product_category', true );
                            $feed_rules = get_post_meta( $feed_id, '_rex_feed_products', true );
                            if ( $google_product_category ) {
                                $properties['category_mapping'] = 'yes';
                            }
                            if ( $feed_rules && 'all' !== $feed_rules ) {
                                $properties['feed_filter_rules'] = 'yes';
                            }
                            return $properties;
                        },
                    ),
                ),
            )
        );
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
     * Handle consent state change from the setup wizard.
     *
     * @param bool $is_consent_given Whether the user has given consent.
     *
     * @return void
     */
    public function handle_consent_updated( $is_consent_given ) {
        global $telemetry_client;
        if ( ! is_object( $telemetry_client ) || ! method_exists( $telemetry_client, 'set_optin_state' ) ) {
            return;
        }
        $telemetry_client->set_optin_state( $is_consent_given ? 'yes' : 'no' );
    }

    /**
     * Track AHA milestone with once-only deduplication.
     *
     * The SDK's aha/kui trigger with no threshold fires on every hook call.
     * We guard with has_sent_event/mark_event_sent for cross-request dedup.
     *
     * @param int $post_id Feed post ID.
     *
     * @return void
     */
    public function maybe_track_aha( $post_id ) {
        global $telemetry_client;
        if ( ! is_object( $telemetry_client ) || ! method_exists( $telemetry_client, 'has_sent_event' ) ) {
            return;
        }
        $event_key = 'aha_reached_first_feed_generated';
        if ( $telemetry_client->has_sent_event( $event_key ) ) {
            return;
        }
        $properties = $this->build_aha_payload( (int) $post_id );
        $telemetry_client->track_kui( 'first_feed_generated', $properties );
        $telemetry_client->mark_event_sent( $event_key );
    }

    /**
     * Build AHA event payload from feed post meta.
     *
     * @param int $post_id Feed post ID.
     *
     * @return array
     */
    public function build_aha_payload( int $post_id ): array {
        return array(
            'marketplace'   => (string) get_post_meta( $post_id, '_rex_feed_merchant', true ),
            'product_count' => (int) get_post_meta( $post_id, '_rex_feed_products', true ),
        );
    }
}

new Rex_Product_Feed_Linno_Telemetry();
