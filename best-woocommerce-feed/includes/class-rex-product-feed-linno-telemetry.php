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
        add_action( 'rex_product_feed_feed_published', array( $this, 'accumulate_feed_publish' ), 10, 2 );
        add_action( 'rex_product_feed_consent_updated', array( $this, 'handle_consent_updated' ) );
        add_action( 'rex_product_feed_setup_completed', array( $this, 'track_onboarding' ) );
        add_action( 'wpfm_flush_feed_telemetry', array( $this, 'flush_daily_telemetry' ) );
        if ( ! wp_next_scheduled( 'wpfm_flush_feed_telemetry' ) ) {
            wp_schedule_event( strtotime( 'tomorrow midnight' ), 'daily', 'wpfm_flush_feed_telemetry' );
        }
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
     * Track onboarding completion without consent — no PII, just site_url + unique_id.
     *
     * Fires on rex_product_feed_setup_completed regardless of consent state,
     * mirroring the same no-consent pattern used for plugin_activated/deactivated.
     *
     * @return void
     */
    public function track_onboarding() {
        global $telemetry_client;
        if ( ! is_object( $telemetry_client ) || ! method_exists( $telemetry_client, 'has_sent_event' ) ) {
            return;
        }
        $event_key = 'onboarding_completed';
        if ( $telemetry_client->has_sent_event( $event_key ) ) {
            return;
        }
        $telemetry_client->track_lifecycle_event(
            'activation/onboarding_completed',
            array(
                'site_url'  => get_site_url(),
                'unique_id' => $telemetry_client->get_unique_id(),
                'timestamp'  => current_time( 'mysql' ),
            )
        );
        $telemetry_client->mark_event_sent( $event_key );
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

    /**
     * Accumulate feed publish data into a daily buffer instead of firing one event per publish.
     *
     * @param int    $feed_id Feed post ID.
     * @param string $source  'manual' or 'scheduled'.
     *
     * @return void
     */
    public function accumulate_feed_publish( $feed_id, $source = '' ) {
        $buffer = get_option( '_wpfm_feed_telemetry_buffer', array() );

        $buffer['feed_count']  = ( $buffer['feed_count']  ?? 0 ) + 1;
        $buffer['merchants']   = array_unique( array_merge( $buffer['merchants'] ?? array(), array( (string) get_post_meta( $feed_id, '_rex_feed_merchant', true ) ) ) );
        $buffer['formats']     = array_unique( array_merge( $buffer['formats']   ?? array(), array( (string) get_post_meta( $feed_id, '_rex_feed_feed_format', true ) ) ) );

        $interval = (string) get_post_meta( $feed_id, '_rex_feed_schedule', true );
        if ( $interval && 'no' !== $interval ) {
            $buffer['intervals']              = $buffer['intervals'] ?? array();
            $buffer['intervals'][ $interval ] = ( $buffer['intervals'][ $interval ] ?? 0 ) + 1;
        }

        if ( 'manual' === $source ) {
            $buffer['manual_count'] = ( $buffer['manual_count'] ?? 0 ) + 1;
        } else {
            $buffer['scheduled_count'] = ( $buffer['scheduled_count'] ?? 0 ) + 1;
        }

        if ( get_post_meta( $feed_id, '_rex_feed_is_google_content_api', true ) ) {
            $buffer['google_api'] = 'yes';
        }

        if ( get_post_meta( $feed_id, '_rex_feed_google_product_category', true ) ) {
            $buffer['category_mapping'] = 'yes';
        }

        $feed_rules = get_post_meta( $feed_id, '_rex_feed_products', true );
        if ( $feed_rules && 'all' !== $feed_rules ) {
            $buffer['feed_filter_rules'] = 'yes';
        }

        update_option( '_wpfm_feed_telemetry_buffer', $buffer, false );
    }

    /**
     * Flush the daily aggregated feed buffer as a single telemetry event, then clear it.
     *
     * Called by the wpfm_flush_feed_telemetry WP-Cron job.
     *
     * @return void
     */
    public function flush_daily_telemetry() {
        global $telemetry_client;
        if ( ! is_object( $telemetry_client ) || ! method_exists( $telemetry_client, 'track' ) ) {
            return;
        }

        $buffer = get_option( '_wpfm_feed_telemetry_buffer', array() );
        if ( empty( $buffer ) || empty( $buffer['feed_count'] ) ) {
            return;
        }

        $intervals = $buffer['intervals'] ?? array();
        arsort( $intervals );

        $telemetry_client->track(
            'retention/feature_used',
            array(
                'feature'               => 'feed_generation',
                'feed_count'            => $buffer['feed_count']             ?? 0,
                'manual_count'          => $buffer['manual_count']           ?? 0,
                'scheduled_count'       => $buffer['scheduled_count']        ?? 0,
                'google_api'            => $buffer['google_api']       ?? 'no',
                'category_mapping'      => $buffer['category_mapping'] ?? 'no',
                'feed_filter_rules'     => $buffer['feed_filter_rules'] ?? 'no',
                'merchants'             => implode( ',', $buffer['merchants'] ?? array() ),
                'formats'               => implode( ',', $buffer['formats']   ?? array() ),
                'intervals'             => implode( ',', array_keys( $intervals ) ),
                'top_interval'          => array_key_first( $intervals ) ?? '',
            )
        );

        delete_option( '_wpfm_feed_telemetry_buffer' );
    }
}

new Rex_Product_Feed_Linno_Telemetry();
