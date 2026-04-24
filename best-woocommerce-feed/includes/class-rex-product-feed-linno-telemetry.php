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

        add_filter( 'product-feed-manager_telemetry_deactivation_reasons', array( $this, 'override_deactivation_reasons' ) );
        add_filter( 'product-feed-manager_deactivation_payload', array( $this, 'enrich_deactivation_payload' ), 10, 3 );
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

    /**
     * Replace SDK default reasons with relabeled, structured set.
     *
     * @return array
     */
    public function override_deactivation_reasons(): array {
        return array(
            array(
                'id'          => 'setup_too_complex',
                'text'        => __( 'I couldn\'t figure out how to create a feed', 'rex-product-feed' ),
                'placeholder' => __( 'Which part was difficult? Merchant selection, mapping, or something else?', 'rex-product-feed' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23"><g fill="none"><g fill="#3B86FF"><path d="M11.5 0C17.9 0 23 5.1 23 11.5 23 17.9 17.9 23 11.5 23 10.6 23 9.6 22.9 8.8 22.7L8.8 22.6C9.3 22.5 9.7 22.3 10 21.9 10.3 21.6 10.4 21.3 10.4 20.9 10.8 21 11.1 21 11.5 21 16.7 21 21 16.7 21 11.5 21 6.3 16.7 2 11.5 2 6.3 2 2 6.3 2 11.5 2 13 2.3 14.3 2.9 15.6 2.7 16 2.4 16.3 2.2 16.8L2.1 17.1 2.1 17.3C2 17.5 2 17.7 2 18 0.7 16.1 0 13.9 0 11.5 0 5.1 5.1 0 11.5 0ZM6 13.6C6 13.7 6.1 13.8 6.1 13.9 6.3 14.5 6.2 15.7 6.1 16.4 6.1 16.6 6 16.9 6 17.1 6 17.1 6.1 17.1 6.1 17.1 7.1 16.9 8.2 16 9.3 15.5 9.8 15.2 10.4 15 10.9 15 11.2 15 11.4 15 11.6 15.2 11.9 15.4 12.1 16 11.6 16.4 11.5 16.5 11.3 16.6 11.1 16.7 10.5 17 9.9 17.4 9.3 17.7 9 17.9 9 18.1 9.1 18.5 9.2 18.9 9.3 19.4 9.3 19.8 9.4 20.3 9.3 20.8 9 21.2 8.8 21.5 8.5 21.6 8.1 21.7 7.9 21.8 7.6 21.9 7.3 21.9L6.5 22C6.3 22 6 21.9 5.8 21.9 5 21.8 4.4 21.5 3.9 20.9 3.3 20.4 3.1 19.6 3 18.8L3 18.5C3 18.2 3 17.9 3.1 17.7L3.1 17.6C3.2 17.1 3.5 16.7 3.7 16.3 4 15.9 4.2 15.4 4.3 15 4.4 14.6 4.4 14.5 4.6 14.2 4.6 13.9 4.7 13.7 4.9 13.6 5.2 13.2 5.7 13.2 6 13.6ZM11.7 11.2C13.1 11.2 14.3 11.7 15.2 12.9 15.3 13 15.4 13.1 15.4 13.2 15.4 13.4 15.3 13.8 15.2 13.8 15 13.9 14.9 13.8 14.8 13.7 14.6 13.5 14.4 13.2 14.1 13.1 13.5 12.6 12.8 12.3 12 12.2 10.7 12.1 9.5 12.3 8.4 12.8 8.3 12.8 8.2 12.8 8.1 12.8 7.9 12.8 7.8 12.4 7.8 12.2 7.7 12.1 7.8 11.9 8 11.8 8.4 11.7 8.8 11.5 9.2 11.4 10 11.2 10.9 11.1 11.7 11.2ZM16.3 5.9C17.3 5.9 18 6.6 18 7.6 18 8.5 17.3 9.3 16.3 9.3 15.4 9.3 14.7 8.5 14.7 7.6 14.7 6.6 15.4 5.9 16.3 5.9ZM8.3 5C9.2 5 9.9 5.8 9.9 6.7 9.9 7.7 9.2 8.4 8.2 8.4 7.3 8.4 6.6 7.7 6.6 6.7 6.6 5.8 7.3 5 8.3 5Z"/></g></g></svg>',
            ),
            array(
                'id'          => 'missing_merchant',
                'text'        => __( 'Missing a Merchant / Channel', 'rex-product-feed' ),
                'placeholder' => __( 'Which merchant or channel were you looking for?', 'rex-product-feed' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="17" viewBox="0 0 24 17"><g fill="none"><g fill="#3B86FF"><path d="M19.4 0C19.7 0.6 19.8 1.3 19.8 2 19.8 3.2 19.4 4.4 18.5 5.3 17.6 6.2 16.5 6.7 15.2 6.7 15.2 6.7 15.2 6.7 15.2 6.7 14 6.7 12.9 6.2 12 5.3 11.2 4.4 10.7 3.3 10.7 2 10.7 1.3 10.8 0.6 11.1 0L7.6 0 7 0 6.5 0 6.5 5.7C6.3 5.6 5.9 5.3 5.6 5.1 5 4.6 4.3 4.3 3.5 4.3 3.5 4.3 3.5 4.3 3.4 4.3 1.6 4.4 0 5.9 0 7.9 0 8.6 0.2 9.2 0.5 9.7 1.1 10.8 2.2 11.5 3.5 11.5 4.3 11.5 5 11.2 5.6 10.8 6 10.5 6.3 10.3 6.5 10.2L6.5 10.2 6.5 17 6.5 17 7 17 7.6 17 22.5 17C23.3 17 24 16.3 24 15.5L24 0 19.4 0Z"/></g></g></svg>',
            ),
            array(
                'id'          => 'feed_generation_failed',
                'text'        => __( 'Feed generation failed / Error', 'rex-product-feed' ),
                'placeholder' => __( 'Did you see an error message, or did it hang at 0%?', 'rex-product-feed' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23"><g fill="none"><g fill="#3B86FF"><path d="M11.5 0C17.9 0 23 5.1 23 11.5 23 17.9 17.9 23 11.5 23 5.1 23 0 17.9 0 11.5 0 5.1 5.1 0 11.5 0ZM11.8 14.4C11.2 14.4 10.7 14.8 10.7 15.4 10.7 16 11.2 16.4 11.8 16.4 12.4 16.4 12.8 16 12.8 15.4 12.8 14.8 12.4 14.4 11.8 14.4ZM12 7C10.1 7 9.1 8.1 9 9.6L10.5 9.6C10.5 8.8 11.1 8.3 11.9 8.3 12.7 8.3 13.2 8.8 13.2 9.5 13.2 10.1 13 10.4 12.2 10.9 11.3 11.4 10.9 12 11 12.9L11 13.4 12.5 13.4 12.5 13C12.5 12.4 12.7 12.1 13.5 11.6 14.4 11.1 14.9 10.4 14.9 9.4 14.9 8 13.7 7 12 7Z"/></g></g></svg>',
            ),
            array(
                'id'          => 'missing_advanced_logic',
                'text'        => __( 'Missing advanced logic / filters', 'rex-product-feed' ),
                'placeholder' => __( 'What filtering or logic did you need?', 'rex-product-feed' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23"><g fill="none"><g fill="#3B86FF"><path d="M11.5 0C17.9 0 23 5.1 23 11.5 23 17.9 17.9 23 11.5 23 5.1 23 0 17.9 0 11.5 0 5.1 5.1 0 11.5 0ZM11.5 2C6.3 2 2 6.3 2 11.5 2 16.7 6.3 21 11.5 21 16.7 21 21 16.7 21 11.5 21 6.3 16.7 2 11.5 2ZM12.5 12.9L12.7 5 10.2 5 10.5 12.9 12.5 12.9ZM11.5 17.4C12.4 17.4 13 16.8 13 15.9 13 15 12.4 14.4 11.5 14.4 10.6 14.4 10 15 10 15.9 10 16.8 10.6 17.4 11.5 17.4Z"/></g></g></svg>',
            ),
            array(
                'id'          => 'found_better_plugin',
                'text'        => __( 'Found a better plugin', 'rex-product-feed' ),
                'placeholder' => __( 'Which plugin did you choose? What did it do better?', 'rex-product-feed' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23"><g fill="none"><g fill="#3B86FF"><path d="M17.1 14L22.4 19.3C23.2 20.2 23.2 21.5 22.4 22.4 21.5 23.2 20.2 23.2 19.3 22.4L19.3 22.4 14 17.1C15.3 16.3 16.3 15.3 17.1 14L17.1 14ZM8.6 0C13.4 0 17.3 3.9 17.3 8.6 17.3 13.4 13.4 17.2 8.6 17.2 3.9 17.2 0 13.4 0 8.6 0 3.9 3.9 0 8.6 0ZM8.6 2.2C5.1 2.2 2.2 5.1 2.2 8.6 2.2 12.2 5.1 15.1 8.6 15.1 12.2 15.1 15.1 12.2 15.1 8.6 15.1 5.1 12.2 2.2 8.6 2.2ZM8.6 3.6L8.6 5C6.6 5 5 6.6 5 8.6L5 8.6 3.6 8.6C3.6 5.9 5.9 3.6 8.6 3.6L8.6 3.6Z"/></g></g></svg>',
            ),
            array(
                'id'          => 'one_time_export',
                'text'        => __( 'Only needed a one-time export', 'rex-product-feed' ),
                'placeholder' => '',
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="17" viewBox="0 0 24 17"><g fill="none"><g fill="#3B86FF"><path d="M23.5 9C23.5 9 23.5 8.9 23.5 8.9 23.5 8.9 23.5 8.9 23.5 8.9 23.4 8.6 23.2 8.3 23 8 22.2 6.5 20.6 3.7 19.8 2.6 18.8 1.3 17.7 0 16.1 0 15.7 0 15.3 0.1 14.9 0.2 13.8 0.6 12.6 1.2 12.3 2.7L11.7 2.7C11.4 1.2 10.2 0.6 9.1 0.2 8.7 0.1 8.3 0 7.9 0 6.3 0 5.2 1.3 4.2 2.6 3.4 3.7 1.8 6.5 1 8 0.8 8.3 0.6 8.6 0.5 8.9 0.5 8.9 0.5 8.9 0.5 8.9 0.5 8.9 0.5 9 0.5 9 0.2 9.7 0 10.5 0 11.3 0 14.4 2.5 17 5.5 17 7.3 17 8.8 16.1 9.8 14.8L14.2 14.8C15.2 16.1 16.7 17 18.5 17 21.5 17 24 14.4 24 11.3 24 10.5 23.8 9.7 23.5 9ZM5.5 15C3.6 15 2 13.2 2 11 2 8.8 3.6 7 5.5 7 7.4 7 9 8.8 9 11 9 13.2 7.4 15 5.5 15ZM18.5 15C16.6 15 15 13.2 15 11 15 8.8 16.6 7 18.5 7 20.4 7 22 8.8 22 11 22 13.2 20.4 15 18.5 15Z"/></g></g></svg>',
            ),
            array(
                'id'          => 'other',
                'text'        => __( 'Other', 'rex-product-feed' ),
                'placeholder' => __( 'Could you tell us more?', 'rex-product-feed' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="23" viewBox="0 0 24 6"><g fill="none"><g fill="#3B86FF"><path d="M3 0C4.7 0 6 1.3 6 3 6 4.7 4.7 6 3 6 1.3 6 0 4.7 0 3 0 1.3 1.3 0 3 0ZM12 0C13.7 0 15 1.3 15 3 15 4.7 13.7 6 12 6 10.3 6 9 4.7 9 3 9 1.3 10.3 0 12 0ZM21 0C22.7 0 24 1.3 24 3 24 4.7 22.7 6 21 6 19.3 6 18 4.7 18 3 18 1.3 19.3 0 21 0Z"/></g></g></svg>',
            ),
        );
    }

    /**
     * Adds plugin-specific fields to the deactivation payload via the library filter.
     */
    public function enrich_deactivation_payload( array $payload, string $reason_id, string $reason_info ): array {
        $installed_time = (int) get_option( 'rex_wpfm_installed_time', 0 );
        $payload['time_since_install_minutes'] = $installed_time > 0
            ? (int) round( ( time() - $installed_time ) / 60 )
            : -1;

        if ( 'feed_generation_failed' === $reason_id ) {
            $payload['php_version'] = phpversion();
            $payload['wc_version']  = function_exists( 'WC' ) && is_object( WC() ) ? WC()->version : '';
        }

        return $payload;
    }
}

new Rex_Product_Feed_Linno_Telemetry();
