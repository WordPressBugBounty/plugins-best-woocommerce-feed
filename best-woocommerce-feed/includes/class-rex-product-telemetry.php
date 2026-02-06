<?php

class Rex_Product_Telemetry {

    /**
     * Rex_Product_Telemetry constructor.
     *
     * Initialize telemetry hooks for the plugin.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'best-woocommerce-feed_tracker_optin', array( $this, 'track_plugin_activation' ));
        add_action( 'transition_post_status', array( $this, 'track_first_feed_published' ), 10, 3);
        add_action( 'rex_product_feed_feed_created', array( $this, 'track_feed_created' ), 10, 2);
        add_action('current_screen', array( $this, 'track_page_view' ) );
        add_action('rex_product_feed_advanced_feature_used', array( $this, 'track_advanced_feature_used' ), 10, 2);
        add_action('rex_product_feed_custom_filter_used', array( $this, 'track_advanced_feature_used' ), 10, 2);
        add_action('rex_product_feed_deactivated', array( $this, 'track_plugin_deactivation' ) );
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
        coderex_telemetry_track(
            WPFM__FILE__,
            'plugin_deactivated',
            array(
                'plugin_version' => defined('WPFM_VERSION') ? WPFM_VERSION : 'unknown',
                'deactivation_time' => current_time( 'mysql' ),
            )
        );
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
            }
        } else if ($new_status === 'publish' && $old_status === 'publish') {
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
}

new Rex_Product_Telemetry();