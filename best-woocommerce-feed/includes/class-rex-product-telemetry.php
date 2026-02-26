<?php

class Rex_Product_Telemetry {

    /**
     * Rex_Product_Telemetry constructor.
     *
     * Initialize telemetry hooks for the plugin.
     * Note: plugin_activated and plugin_deactivated events are now
     * automatically tracked by the coderex-telemetry package.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'rex_product_feed_activated', array( $this, 'track_plugin_activation' ));
        add_action( 'transition_post_status', array( $this, 'track_first_feed_published' ), 10, 3);
        add_action('current_screen', array( $this, 'track_page_view' ) );
        add_action('rex_product_feed_advanced_feature_used', array( $this, 'track_advanced_feature_used' ), 10, 2);
        add_action('rex_product_feed_custom_filter_used', array( $this, 'track_advanced_feature_used' ), 10, 2);
        add_action('rex_product_feed_deactivated', array( $this, 'track_plugin_deactivation' ) );
        
        // AJAX handlers for tracking events
        add_action( 'wp_ajax_rex_feed_track_paywall_hit', array( $this, 'ajax_track_paywall_hit' ) );
        add_action( 'wp_ajax_rex_feed_track_upgrade_clicked', array( $this, 'ajax_track_upgrade_clicked' ) );
        add_action( 'wp_ajax_rex_feed_track_setup_started', array( $this, 'ajax_track_setup_started' ) );
        add_action( 'wp_ajax_rex_feed_track_setup_completed', array( $this, 'ajax_track_setup_completed' ) );
        add_action( 'wp_ajax_rex_feed_save_optin_preference', array( $this, 'ajax_save_optin_preference' ) );
    }


    /**
     * Track plugin activation
     *
     * Sends telemetry event when the plugin is activated.
     *
     * @since 1.0.0
     */
    public function track_plugin_activation() {
        coderex_telemetry_track(
            WPFM__FILE__,
            'plugin_activation',
            array(
                'activation_time' => get_option( 'rex_wpfm_installed_time', time() ),
            )
        );
    }

    /**
     * Track plugin deactivation
     *
     * Sends telemetry event when the plugin is deactivated.
     *
     * @since 1.0.0
     */
    public function track_plugin_deactivation() {
        // Calculate usage duration
        $activation_time = get_option( 'rex_wpfm_activated_time', 0 );
        $usage_duration = 'Not available';
        if ( $activation_time > 0 ) {
            $duration_seconds = time() - $activation_time;
            $usage_duration = $this->format_duration( $duration_seconds );
        }

        // Get last core action
        $last_core_action = get_option( 'best-woocommerce-feed_last_core_action', '' );

        // Track deactivation event
        coderex_telemetry_track(
            WPFM__FILE__,
            'plugin_deactivated',
            array(
                'usage_duration' => $usage_duration,
                'last_core_action' => $last_core_action,
            )
        );

        // Clean up activation time option
        delete_option( 'rex_wpfm_activated_time' );
    }


    /**
     * Track the first published feed
     *
     * Sends telemetry when the first feed is published for the plugin.
     *
     * @param string $new_status The new post status
     * @param string $old_status The previous post status
     * @param object $post  \WP_Post The post object
     * @since 1.0.0
     */
    public function track_first_feed_published( $new_status, $old_status, $post ) {
        if ($post->post_type !== 'product-feed') {
            return;
        }
        $merchant       = get_post_meta( $post->ID, '_rex_feed_merchant', true );
        $feed_format    = get_post_meta( $post->ID, '_rex_feed_feed_format', true );
        $schedule       = get_post_meta( $post->ID, '_rex_feed_schedule', true );

        if ($new_status === 'publish' && in_array($old_status, ['auto-draft', 'draft', 'new', ''])) {
            $feed_count = wp_count_posts('product-feed');
            $total_feeds = $feed_count->publish + $feed_count->draft;

            $feed_data = array(
                'merchant' => $merchant,
                'feed_type' => get_post_meta($post->ID, '_rex_feed_feed_format', true),
                'title' => $post->post_title,
                'created_at' => current_time('mysql')
            );
            do_action('rex_product_feed_feed_created', $post->ID, $feed_data);
            if (1 === $total_feeds) {
                // Update last core action
                coderex_telemetry_update_last_action( WPFM__FILE__, 'first_feed_generated' );

                coderex_telemetry_track(
                    WPFM__FILE__,
                    'first_feed_generated',
                    array(
                        'format'        => $feed_format,
                        'merchant'      => $merchant,
                        'feed_title'    => $post->post_title,
                        'time'          => current_time('mysql'),
                        'schedule_type' => $schedule
                    )
                );

                // Also track first_strike (first successful value moment)
                coderex_telemetry_update_last_action( WPFM__FILE__, 'first_strike' );

                coderex_telemetry_track(
                    WPFM__FILE__,
                    'first_strike',
                    array(
                        'format'        => $feed_format,
                        'merchant'      => $merchant,
                        'feed_title'    => $post->post_title,
                        'time'          => current_time('mysql'),
                    )
                );
            }
        } else if ($new_status === 'publish' && $old_status === 'publish') {
            // Update last core action
            coderex_telemetry_update_last_action( WPFM__FILE__, 'feed_updated' );
            coderex_telemetry_track(
                WPFM__FILE__,
                'feed_updated',
                array(
                    'format' => $feed_format,
                    'merchant' => $merchant,
                    'feed_title' => $post->post_title,
                    'time' => current_time('mysql'),
                    'schedule_type' => $schedule
                )
            );
        }
    }

    /**
     * Track feed creation
     *
     * Sends telemetry when a new feed is created.
     *
     * @param int   $feed_id The ID of the created feed
     * @param array $config  Configuration array for the feed
     * @since 1.0.0
     */
    public function track_feed_created( $feed_id, $config ) {
        // Update last core action
        coderex_telemetry_update_last_action( WPFM__FILE__, 'feed_created' );

        coderex_telemetry_track(
            WPFM__FILE__,
            'feed_generated',
            array(
                'format' => isset( $config['feed_type'] ) ? $config['feed_type'] : '',
                'merchant' => isset( $config['merchant'] ) ? $config['merchant'] : '',
                'feed_title' => isset( $config['title'] ) ? $config['title'] : '',
                'time' => current_time('mysql'),
                'schedule_type' => get_post_meta( $feed_id, '_rex_feed_schedule', true )
            )
        );
    }

    /**
     * Track advanced feature usage
     *
     * Sends telemetry when an advanced feature is used on a feed.
     *
     * @param int   $feed_id      The ID of the feed
     * @param array $feature_data Optional additional feature data
     * @since 1.0.0
     */
    public function track_advanced_feature_used( $feed_id, $feature_data = array() ) {
        // Update last core action
        coderex_telemetry_update_last_action( WPFM__FILE__, 'advanced_feature_used' );

        coderex_telemetry_track(
            WPFM__FILE__,
            'advanced_feature_used',
            $feature_data
        );
    }


    /**
     * Track page views
     *
     * Sends telemetry when specific admin pages for the plugin are viewed.
     *
     * @param WP_Screen $screen Current admin screen object
     * @return void
     * @since 7.4.55
     */
    public function track_page_view( $screen ) {
        if ( ! is_admin() || empty( $screen->id ) ) {
            return;
        }

        // Map request URI fragments to friendly page names
        $page_map = array(
            'edit.php?post_type=product-feed' => 'Feeds list',
            'post-new.php?post_type=product-feed' => 'New Feed',
            'edit.php?post_type=product-feed&page=category_mapping' => 'Category mapping',
            'edit.php?post_type=product-feed&page=merchant_settings' => 'Merchant settings',
            'edit.php?post_type=product-feed&page=wpfm_dashboard' => 'Dashboard',
            'edit.php?post_type=product-feed&page=wpfm-license' => 'License',
            'edit.php?post_type=product-feed&page=wpfm-setup-wizard' => 'Setup wizard',
        );

        $current_page = $_SERVER['REQUEST_URI'] ?? '';
        if ( '' === $current_page ) {
            return;
        }

        $page_name = null;
        foreach ( $page_map as $fragment => $name ) {
            if ( strpos( $current_page, $fragment ) !== false ) {
                $page_name = $name;
                break;
            }
        }

        if ( null === $page_name ) {
            // Not an allowed/interesting page for telemetry
            return;
        }

        // Ensure a logged in user exists before sending telemetry
        $current_user = wp_get_current_user();
        if ( ! $current_user->exists() ) {
            return;
        }

        coderex_telemetry_track(
            WPFM__FILE__,
            'page_view',
            array(
                'page' => $current_page,
                'page_name' => $page_name,
                'time' => current_time( 'mysql' ),
            )
        );
    }

    /**
     * AJAX handler to track paywall hit event
     *
     * Tracks when a free user attempts to access a pro-only feature.
     *
     * @return void
     * @since 1.0.0
     */
    public function ajax_track_paywall_hit() {
        // Verify nonce
        check_ajax_referer( 'rex-wpfm-ajax', 'nonce' );

        $feature_name = isset( $_POST['feature_name'] ) ? sanitize_text_field( $_POST['feature_name'] ) : 'Unknown Feature';

        // Update last core action
        coderex_telemetry_update_last_action( WPFM__FILE__, 'paywall_hit' );

        // Track the paywall hit event
        coderex_telemetry_track(
            WPFM__FILE__,
            'paywall_hit',
            array(
                'feature_name' => $feature_name,
                'time' => current_time( 'mysql' ),
            )
        );

        wp_send_json_success( array( 'message' => 'Event tracked' ) );
    }

    /**
     * AJAX handler to track upgrade clicked event
     *
     * Tracks when a user clicks an upgrade link, button, or prompt.
     *
     * @return void
     * @since 1.0.0
     */
    public function ajax_track_upgrade_clicked() {
        // Verify nonce
        check_ajax_referer( 'rex-wpfm-ajax', 'nonce' );

        $button_text = isset( $_POST['button_text'] ) ? sanitize_text_field( $_POST['button_text'] ) : 'Upgrade Link';
        $button_location = isset( $_POST['button_location'] ) ? sanitize_text_field( $_POST['button_location'] ) : 'unknown';

        // Update last core action
        coderex_telemetry_update_last_action( WPFM__FILE__, 'upgrade_clicked' );

        // Track the upgrade clicked event
        coderex_telemetry_track(
            WPFM__FILE__,
            'upgrade_clicked',
            array(
                'button_text' => $button_text,
                'button_location' => $button_location,
                'time' => current_time( 'mysql' ),
            )
        );

        wp_send_json_success( array( 'message' => 'Event tracked' ) );
    }

    /**
     * AJAX handler to track setup wizard started event
     *
     * Tracks when a user begins the setup wizard.
     *
     * @return void
     * @since 1.0.0
     */
    public function ajax_track_setup_started() {
        // Verify nonce
        check_ajax_referer( 'rex-wpfm-ajax', 'nonce' );

        // Update last core action
        coderex_telemetry_update_last_action( WPFM__FILE__, 'setup_started' );

        // Track the setup started event
        coderex_telemetry_track(
            WPFM__FILE__,
            'setup_started',
            array(
                'time' => current_time( 'mysql' ),
            )
        );

        wp_send_json_success( array( 'message' => 'Event tracked' ) );
    }

    /**
     * AJAX handler to track setup wizard completed event
     *
     * Tracks when a user completes the setup wizard.
     *
     * @return void
     * @since 1.0.0
     */
    public function ajax_track_setup_completed() {
        // Verify nonce
        check_ajax_referer( 'rex-wpfm-ajax', 'nonce' );

        // Update last core action
        coderex_telemetry_update_last_action( WPFM__FILE__, 'setup_completed' );

        // Track the setup completed event
        coderex_telemetry_track(
            WPFM__FILE__,
            'setup_completed',
            array(
                'time' => current_time( 'mysql' ),
            )
        );

        wp_send_json_success( array( 'message' => 'Event tracked' ) );
    }

    /**
     * AJAX handler to save opt-in preference
     *
     * Saves the user's opt-in preference to the database.
     *
     * @return void
     * @since 1.0.0
     */
    public function ajax_save_optin_preference() {
        // Verify nonce
        check_ajax_referer( 'rex-wpfm-ajax', 'nonce' );
        $is_checked = isset( $_POST['is_checked'] ) ? $_POST['is_checked'] : 'true';
        $option_value = 'true' === $is_checked ? 'yes' : 'no';
        update_option( 'best-woocommerce-feed_allow_tracking', $option_value);

        if ( 'yes' === $option_value ) {
            Rex_Product_Feed_Create_Contact::create_contact_for_current_user();
        }

        wp_send_json_success( array( 
            'message' => 'Preference saved',
            'value' => $option_value
        ) );
    }

    /**
     * Format duration in seconds to human-readable format
     *
     * @param int $seconds Duration in seconds
     * @return string Human-readable duration
     * @since 7.4.64
     */
    private function format_duration( $seconds ) {
        if ( $seconds < 60 ) {
            return $seconds . ' seconds';
        }

        $minutes = floor( $seconds / 60 );
        if ( $minutes < 60 ) {
            return $minutes . ' minutes';
        }

        $hours = floor( $minutes / 60 );
        if ( $hours < 24 ) {
            $remaining_minutes = $minutes % 60;
            return $hours . ' hours' . ( $remaining_minutes > 0 ? ', ' . $remaining_minutes . ' minutes' : '' );
        }

        $days = floor( $hours / 24 );
        $remaining_hours = $hours % 24;
        return $days . ' days' . ( $remaining_hours > 0 ? ', ' . $remaining_hours . ' hours' : '' );
    }
}

new Rex_Product_Telemetry();