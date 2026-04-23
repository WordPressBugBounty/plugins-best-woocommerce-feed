<?php
/**
 * Setup wizard AJAX handlers
 *
 * @package ''
 * @since 7.4.14
 */

class Rex_Product_Feed_Setup_Wizard_Ajax
{
    /**
     * Constructor - Register AJAX handlers
     *
     * @since 7.4.14
     */
    public function __construct() {
        // Register AJAX handlers
        add_action('wp_ajax_pfm_save_consent', array($this, 'save_consent'));
        add_action('wp_ajax_pfm_track_setup_start', array($this, 'track_setup_start'));
        add_action('wp_ajax_pfm_track_setup_completed', array($this, 'track_setup_completed'));
        add_action('wp_ajax_pfm_track_first_strike', array($this, 'track_first_strike'));
        add_action('wp_ajax_pfm_get_all_merchants', array($this, 'get_all_merchants'));
        add_action('wp_ajax_pfm_get_template_mappings', array($this, 'get_template_mappings'));
        add_action('wp_ajax_pfm_create_feed', array($this, 'create_feed'));
        add_action('wp_ajax_rexfeed-generate-feed', array($this, 'generate_feed'));
        add_action('wp_ajax_pfm_install_activate_plugin', array($this, 'install_activate_plugin'));
        add_action('wp_ajax_pfm_skip_upsell', array($this, 'skip_upsell'));
        add_action('wp_ajax_pfm_track_companion_impression', array($this, 'track_companion_impression'));
        add_action('wp_ajax_pfm_settings_wpfunnels_widget_track', array($this, 'track_settings_wpfunnels_widget'));
        add_action('wp_ajax_pfm_dashboard_banner_track',          array($this, 'dashboard_banner_track'));
        add_action('wp_ajax_pfm_dashboard_banner_dismiss',        array($this, 'dashboard_banner_dismiss'));
        add_action('wp_ajax_pfm_wizard_dismiss',                   array($this, 'dismiss_wizard'));
        add_action('wp_ajax_pfm_wizard_mark_completed',            array($this, 'mark_wizard_completed'));
        add_action('wp_ajax_pfm_tour_update_status',               array($this, 'tour_update_status'));
        add_action('admin_init',                                   array($this, 'maybe_suppress_consent_notice'));
        add_filter('views_edit-product-feed',                      array($this, 'inject_re_engagement_banner'));
    }

    /**
     * Save user consent for tracking
     *
     * @since 7.4.14
     */
    public function save_consent() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
        if ( !wp_verify_nonce( $nonce, 'rex-product-feed' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
            return;
        }

        $consent = isset($_POST['consent']) ? sanitize_text_field($_POST['consent']) : '0';
        $feed_id = isset($_POST['feed_id']) ? absint($_POST['feed_id']) : 0;
        $is_consent_given = '1' === $consent;
        update_option('best-woocommerce-feed_allow_tracking', $is_consent_given ? 'yes' : 'no');
        do_action( 'rex_product_feed_consent_updated', $is_consent_given );

        if ( $is_consent_given ) {
            Rex_Product_Feed_Create_Contact::create_contact_for_current_user();
            if ( $feed_id > 0 ) {
                do_action( 'rex_product_feed_feed_published', $feed_id, 'wizard' );
            }
        }

        wp_send_json_success( array( 'message' => __('Consent saved.', 'rex-product-feed') ), 200 );
    }

    /**
     * Track setup wizard start event
     *
     * @since 7.4.14
     */
    public function track_setup_start() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        $setup_started = get_option('rex_feed_setup_wizard_started', false);
        if ( $setup_started ) {
            wp_send_json_success( array( 'message' => 'Already tracked' ), 200 );
            return;
        }

        update_option('rex_feed_setup_wizard_started', true);
        do_action( 'rex_product_feed_setup_started' );

        wp_send_json_success( array( 'message' => 'Setup start tracked' ), 200 );
    }

    /**
     * Track setup completed event
     *
     * @since 7.4.14
     */
    public function track_setup_completed() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        do_action( 'rex_product_feed_setup_completed' );

        wp_send_json_success( array( 'message' => 'Setup completed tracked' ), 200 );
    }

    /**
     * Track first strike (first feed created) event
     *
     * @since 7.4.14
     */
    public function track_first_strike() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
        if ( !wp_verify_nonce( $nonce, 'rex-product-feed' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
            return;
        }

        wp_send_json_success( array( 'message' => 'First strike tracked' ), 200 );
    }

    /**
     * Get all merchants for search functionality
     *
     * @since 7.4.14
     */
    public function get_all_merchants() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
        if ( !wp_verify_nonce( $nonce, 'rex-product-feed' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
            return;
        }

        // Load merchants class
        require_once plugin_dir_path(__FILE__) . '../admin/class-rex-product-feed-merchants.php';
        
        $all_merchants_data = Rex_Feed_Merchants::get_merchants();
        $merchants_list = array();
        
        // Popular merchants (already shown separately, so exclude them from search)
        $popular_ids = array('google', 'facebook', 'idealo', 'tiktok', 'pinterest');
        
        // Get premium status
        $is_premium = apply_filters( 'wpfm_is_premium', false );
        
        // Process pro merchants
        if ( isset($all_merchants_data['pro_merchants']) && is_array($all_merchants_data['pro_merchants']) ) {
            foreach ( $all_merchants_data['pro_merchants'] as $id => $merchant ) {
                if ( !in_array($id, $popular_ids) ) {
                    $merchants_list[] = array(
                        'id' => $id,
                        'name' => $merchant['name'],
                        'isPro' => true,
                        'isAvailable' => $is_premium
                    );
                }
            }
        }
        
        // Process free merchants
        if ( isset($all_merchants_data['free_merchants']) && is_array($all_merchants_data['free_merchants']) ) {
            foreach ( $all_merchants_data['free_merchants'] as $id => $merchant ) {
                if ( !in_array($id, $popular_ids) ) {
                    $merchants_list[] = array(
                        'id' => $id,
                        'name' => $merchant['name'],
                        'isPro' => false,
                        'isAvailable' => true
                    );
                }
            }
        }
        
        // Sort alphabetically by name
        usort($merchants_list, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        wp_send_json_success( $merchants_list, 200 );
    }

    /**
     * Get template mappings for a merchant
     *
     * @since 7.4.14
     */
    public function get_template_mappings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
        if ( ! wp_verify_nonce( $nonce, 'rex-product-feed' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
            return;
        }

        $merchant = isset($_POST['merchant']) ? sanitize_text_field($_POST['merchant']) : '';

        if ( empty($merchant) ) {
            wp_send_json_error( array( 'message' => 'Merchant is required' ), 400 );
            return;
        }

        try {
            // Load required classes
            require_once plugin_dir_path(__FILE__) . '../admin/class-rex-feed-template-factory.php';
            require_once plugin_dir_path(__FILE__) . '../admin/class-rex-feed-attributes.php';

            // Get template for the merchant
            $template = Rex_Feed_Template_Factory::build( $merchant, false );
            $mappings = $template->get_template_mappings();
            $attributes = $template->get_merchant_attributes();

            // Get available WooCommerce attributes
            $wc_attributes = Rex_Feed_Attributes::get_attributes();

            // Format mappings for display
            $formatted_mappings = array();
            foreach ( $mappings as $mapping ) {
                $attr = $mapping['attr'];
                $attr_label = isset($attributes[$attr]) ? $attributes[$attr] : $attr;
                
                // Get the friendly name for meta_key
                $value_label = $mapping['meta_key'];
                if ( $mapping['type'] === 'meta' ) {
                    // Try to find friendly name in WC attributes
                    foreach ( $wc_attributes as $group => $attrs ) {
                        if ( isset($attrs[$mapping['meta_key']]) ) {
                            $value_label = $attrs[$mapping['meta_key']];
                            break;
                        }
                    }
                }

                $formatted_mappings[] = array(
                    'attr' => $attr,
                    'attr_label' => $attr_label,
                    'type' => $mapping['type'],
                    'meta_key' => $mapping['meta_key'],
                    'value_label' => $value_label,
                    'st_value' => isset($mapping['st_value']) ? $mapping['st_value'] : '',
                    'prefix' => isset($mapping['prefix']) ? $mapping['prefix'] : '',
                    'suffix' => isset($mapping['suffix']) ? $mapping['suffix'] : '',
                    'escape' => isset($mapping['escape']) ? $mapping['escape'] : 'default',
                    'limit' => isset($mapping['limit']) ? $mapping['limit'] : 0
                );
            }

            wp_send_json_success( array(
                'mappings' => $formatted_mappings,
                'merchant_attributes' => $attributes,
                'wc_attributes' => $wc_attributes,
                'merchant' => $merchant
            ), 200 );
        } catch ( Exception $e ) {
            error_log( 'PFM Setup Wizard - Failed to get template mappings: ' . $e->getMessage() );
            wp_send_json_error( array( 'message' => 'Failed to get template mappings: ' . $e->getMessage() ), 500 );
        }
    }

    /**
     * Create feed from setup wizard
     *
     * @since 7.4.14
     */
    public function create_feed() {
        try {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
                return;
            }

            $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
            if ( !wp_verify_nonce( $nonce, 'rex-product-feed' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
                return;
            }

            // Get feed data from POST
            $feed_name = isset($_POST['feed_name']) ? sanitize_text_field($_POST['feed_name']) : '';
            $merchant = isset($_POST['merchant']) ? sanitize_text_field($_POST['merchant']) : '';
            $feed_format = isset($_POST['feed_format']) ? sanitize_text_field($_POST['feed_format']) : 'xml';
            $update_frequency = isset($_POST['update_frequency']) ? sanitize_text_field($_POST['update_frequency']) : 'hourly';
            $custom_mappings = isset($_POST['mappings']) ? json_decode(stripslashes($_POST['mappings']), true) : array();

            // Validate required fields
            if ( empty($feed_name) || empty($merchant) ) {
                wp_send_json_error( array( 'message' => 'Feed name and merchant are required' ), 400 );
                return;
            }

            // Create the feed post
            $post_data = array(
                'post_title'  => $feed_name,
                'post_type'   => 'product-feed',
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
            );

            $feed_id = wp_insert_post( $post_data );

            if ( is_wp_error( $feed_id ) ) {
                $error_message = $feed_id->get_error_message();
                error_log( 'PFM Setup Wizard - Failed to create feed post: ' . $error_message );
                wp_send_json_error( array( 'message' => 'Failed to create feed: ' . $error_message ), 500 );
                return;
            }

            // Mark this feed as created by the setup wizard (or demo) so it doesn't count as a manually created feed
            update_post_meta( $feed_id, 'pfm_feed_created_by', 'setup_wizard' );

            // Load required classes
            require_once plugin_dir_path(__FILE__) . '../admin/class-rex-feed-template-factory.php';

            // Use custom mappings if provided, otherwise get default template mappings
            $feed_config = array();
            if ( !empty($custom_mappings) && is_array($custom_mappings) ) {
                // Clean the mappings - remove display-only fields and ensure proper structure
                $feed_config = array();
                foreach ( $custom_mappings as $mapping ) {
                    if ( !isset($mapping['attr']) || !isset($mapping['type']) ) {
                        continue; // Skip invalid mappings
                    }
                    
                    $clean_mapping = array(
                        'attr' => sanitize_text_field($mapping['attr']),
                        'type' => sanitize_text_field($mapping['type']),
                        'meta_key' => isset($mapping['meta_key']) ? sanitize_text_field($mapping['meta_key']) : '',
                        'st_value' => isset($mapping['st_value']) ? sanitize_text_field($mapping['st_value']) : '',
                        'prefix' => isset($mapping['prefix']) ? sanitize_text_field($mapping['prefix']) : '',
                        'suffix' => isset($mapping['suffix']) ? sanitize_text_field($mapping['suffix']) : '',
                        'escape' => isset($mapping['escape']) ? $mapping['escape'] : 'default',
                        'limit' => isset($mapping['limit']) ? intval($mapping['limit']) : 0,
                    );
                    
                    $feed_config[] = $clean_mapping;
                }
            } else {
                try {
                    $template = Rex_Feed_Template_Factory::build( $merchant, false );
                    $feed_config = $template->get_template_mappings();
                } catch ( Exception $e ) {
                    // If template not found, use empty config
                    error_log( 'PFM Setup Wizard - Template not found for merchant: ' . $merchant . ' - ' . $e->getMessage() );
                    $feed_config = array();
                }
            }

            // Save feed meta data
            update_post_meta( $feed_id, '_rex_feed_merchant', $merchant );
            update_post_meta( $feed_id, '_rex_feed_feed_format', $feed_format );
            update_post_meta( $feed_id, '_rex_feed_schedule', $update_frequency );
            update_post_meta( $feed_id, '_rex_feed_feed_config', $feed_config );
            
            // Set default settings
            update_post_meta( $feed_id, '_rex_feed_variations', 'yes' );
            update_post_meta( $feed_id, '_rex_feed_variable_product', 'yes' );
            update_post_meta( $feed_id, '_rex_feed_parent_product', 'yes' );
            update_post_meta( $feed_id, '_rex_feed_include_out_of_stock', 'no' );
            update_post_meta( $feed_id, '_rex_feed_include_zero_price_products', 'no' );
            update_post_meta( $feed_id, '_rex_feed_hidden_products', 'no' );
            update_post_meta( $feed_id, '_rex_feed_skip_product', 'no' );
            update_post_meta( $feed_id, '_rex_feed_skip_row', 'no' );
            
            // Set products to "all products" by default
            update_post_meta( $feed_id, '_rex_feed_products', 'all' );
            
            // Mark as created from setup wizard
            update_post_meta( $feed_id, '_rex_feed_created_via_wizard', 'yes' );
            update_post_meta( $feed_id, '_feed_created_at', time() );
            update_post_meta( $feed_id, 'edit_count', 0 );
            update_post_meta( $feed_id, '_rex_feed_is_google_content_api', 'no' );

            // Get the feed URL
            $path = wp_upload_dir();
            $path = $path['baseurl'] . '/rex-feed';
            $feed_url = trailingslashit($path) . "feed-{$feed_id}.{$feed_format}";

            // Get product count for batch generation
            $is_premium = apply_filters( 'wpfm_is_premium', false );
            $products = apply_filters( 'wpfm_get_total_number_of_products', array( 'products' => WPFM_FREE_MAX_PRODUCT_LIMIT ), $feed_id );
            $per_page = get_option( 'rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT );

            if ( (int) $per_page >= WPFM_FREE_MAX_PRODUCT_LIMIT && !$is_premium ) {
                $posts_per_page = WPFM_FREE_MAX_PRODUCT_LIMIT;
            } else {
                $posts_per_page = (int) $per_page;
            }

            $total_products = $products['products'];
            $total_batch = ceil( $total_products / $posts_per_page );

            $count_posts = wp_count_posts('product-feed');
            $total_feeds = (isset($count_posts->publish) ? $count_posts->publish : 0) + (isset($count_posts->draft) ? $count_posts->draft : 0);
            $edit_url = admin_url( 'post.php?post=' . $feed_id . '&action=edit' );
            
            if ( $total_feeds <= 1 ) {
                $edit_url .= '&tour_guide=1';
            }

            // Return success with feed data and batch info
            wp_send_json_success( array(
                'feed_id' => $feed_id,
                'feed_url' => $feed_url,
                'edit_url' => $edit_url,
                'message' => 'Feed created successfully',
                'batch_info' => array(
                    'total_products' => $total_products,
                    'per_batch' => $posts_per_page,
                    'total_batch' => $total_batch
                )
            ), 200 );
        } catch ( Exception $e ) {
            error_log( 'PFM Setup Wizard - Unexpected error: ' . $e->getMessage() );
            wp_send_json_error( array( 'message' => 'An unexpected error occurred: ' . $e->getMessage() ), 500 );
        }
    }

    /**
     * Generate feed in batches
     * Delegate to the main AJAX handler
     *
     * @since 7.4.14
     */
    public function generate_feed() {
        $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
        if ( ! wp_verify_nonce( $nonce, 'rex-product-feed' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
            return;
        }

        // Get the payload from POST
        $payload = isset($_POST['payload']) ? $_POST['payload'] : array();
        
        if (empty($payload)) {
            wp_send_json_error(array('message' => 'No payload provided'), 400);
            return;
        }

        $products_scope = ! empty( $payload['products']['products_scope'] )
            ? sanitize_text_field( $payload['products']['products_scope'] )
            : 'all';

        if ( ! empty( $payload['feed_config'] ) ) {
            $payload['feed_config'] = $this->normalize_feed_config( $payload['feed_config'], $products_scope );
        }
        
        // If feed_config is not in payload or is empty, read from post meta
        if ( empty($payload['feed_config']) && !empty($payload['info']['post_id']) ) {
            $feed_id = intval($payload['info']['post_id']);
            $feed_config = get_post_meta( $feed_id, '_rex_feed_feed_config', true );
            if ( !empty($feed_config) && is_array($feed_config) ) {
                $payload['feed_config'] = $this->normalize_feed_config( $feed_config, $products_scope );
                error_log( 'PFM Setup Wizard - Loaded feed_config from post meta: ' . count($feed_config) . ' mappings' );
            } else {
                error_log( 'PFM Setup Wizard - No feed_config found in post meta for feed ID: ' . $feed_id );
            }
        }
        
        // Load the main AJAX class
        require_once plugin_dir_path(__FILE__) . '../admin/class-rex-product-feed-ajax.php';

        // Call the generate_feed method with payload
        $result = Rex_Product_Feed_Ajax::generate_feed($payload);
        
        // Return the result
        echo wp_json_encode($result);
        wp_die();
    }

    /**
     * Install and activate a plugin from WordPress.org
     *
     * @since 7.4.14
     */
    public function install_activate_plugin() {
        if ( ! current_user_can( 'install_plugins' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        $nonce = isset( $_POST['security'] ) ? sanitize_text_field( $_POST['security'] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'rex-product-feed' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
            return;
        }

        $allowed_slugs = array( 'wpfunnels', 'cart-lift' );
        $plugin_slug   = isset( $_POST['plugin_slug'] ) ? sanitize_text_field( $_POST['plugin_slug'] ) : '';

        if ( empty( $plugin_slug ) || ! in_array( $plugin_slug, $allowed_slugs, true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid plugin slug' ), 400 );
            return;
        }

        // Fire telemetry event for the click
        $telemetry_event = 'wpfunnels' === $plugin_slug
            ? 'pfm_wizard_companion_install_wpfunnels'
            : 'pfm_wizard_companion_install_cartlift';
        do_action( 'product-feed-manager_telemetry_track', $telemetry_event, array( 'plugin' => $plugin_slug ) );

        // Map slug → main plugin file (folder/file.php)
        $plugin_files = array(
            'wpfunnels' => 'wpfunnels/wpfnl.php',
            'cart-lift'  => 'cart-lift/cart-lift.php',
        );
        $plugin_file = $plugin_files[ $plugin_slug ];

        // If already active, return success immediately
        if ( is_plugin_active( $plugin_file ) ) {
            wp_send_json_success( array( 'message' => 'Plugin already active' ), 200 );
            return;
        }

        // If installed but not active, just activate it
        if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
            $activated = activate_plugin( $plugin_file );
            if ( is_wp_error( $activated ) ) {
                wp_send_json_error( array( 'message' => $activated->get_error_message() ), 500 );
                return;
            }
            wp_send_json_success( array( 'message' => 'Plugin activated' ), 200 );
            return;
        }

        // Install from WordPress.org then activate
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';

        $api = plugins_api( 'plugin_information', array(
            'slug'   => $plugin_slug,
            'fields' => array( 'sections' => false ),
        ) );

        if ( is_wp_error( $api ) ) {
            wp_send_json_error( array( 'message' => $api->get_error_message() ), 500 );
            return;
        }

        $skin     = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader( $skin );
        $result   = $upgrader->install( $api->download_link );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
            return;
        }

        if ( is_wp_error( $skin->result ) ) {
            wp_send_json_error( array( 'message' => $skin->result->get_error_message() ), 500 );
            return;
        }

        $activated = activate_plugin( $plugin_file );
        if ( is_wp_error( $activated ) ) {
            wp_send_json_error( array( 'message' => $activated->get_error_message() ), 500 );
            return;
        }

        wp_send_json_success( array( 'message' => 'Plugin installed and activated' ), 200 );
    }

    /**
     * Track upsell skip and return success (redirect happens client-side)
     *
     * @since 7.4.14
     */
    public function skip_upsell() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        do_action( 'product-feed-manager_telemetry_track', 'pfm_wizard_companion_skip', array() );
        update_option( 'rex_feed_setup_wizard_upsell_skipped', true );
        wp_send_json_success( array( 'message' => 'Skipped' ), 200 );
    }

    /**
     * Track companion screen impression
     *
     * @since 7.4.14
     */
    public function track_companion_impression() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        do_action( 'product-feed-manager_telemetry_track', 'pfm_wizard_companion_impression', array() );
        wp_send_json_success( array( 'message' => 'Impression tracked' ), 200 );
    }

    /**
     * Track PFM Settings page WPFunnels promo widget impression or click.
     *
     * @since 7.4.78
     */
    public function track_settings_wpfunnels_widget() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        $event = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( $_POST['event'] ) ) : '';

        if ( 'impression' === $event ) {
            do_action( 'product-feed-manager_telemetry_track', 'pfm_settings_wpfunnels_widget_impression', array() );
        }

        wp_send_json_success( array( 'message' => 'Tracked' ), 200 );
    }

    /**
     * Track a dashboard banner PostHog event.
     *
     * Accepted events: impression, click, dismiss.
     * Props forwarded: variant, user_feed_count, pfm_version.
     *
     * @since 7.4.78
     */
    public function dashboard_banner_track() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
            return;
        }

        $event      = isset( $_POST['event'] )       ? sanitize_text_field( wp_unslash( $_POST['event'] ) )       : '';
        $variant    = isset( $_POST['variant'] )     ? sanitize_text_field( wp_unslash( $_POST['variant'] ) )     : '';
        $feed_count = isset( $_POST['feed_count'] )  ? absint( $_POST['feed_count'] )                             : 0;
        $version    = isset( $_POST['pfm_version'] ) ? sanitize_text_field( wp_unslash( $_POST['pfm_version'] ) ) : '';

        $allowed = array( 'impression', 'click', 'dismiss' );
        if ( ! in_array( $event, $allowed, true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid event' ), 400 );
            return;
        }

        do_action(
            'product-feed-manager_telemetry_track',
            'pfm_wpfunnels_banner_' . $event,
            array(
                'variant'         => $variant,
                'user_feed_count' => $feed_count,
                'pfm_version'     => $version,
            )
        );

        wp_send_json_success( array( 'message' => 'Tracked' ), 200 );
    }

    /**
     * Persist a banner dismiss decision for the current user.
     *
     * type=temp      → 30-day transient
     * type=permanent → user_meta flag
     *
     * @since 7.4.78
     */
    public function dashboard_banner_dismiss() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
            return;
        }

        $type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'temp';
        $user_id = get_current_user_id();

        if ( 'permanent' === $type ) {
            update_user_meta( $user_id, 'pfm_wpfunnels_banner_dismissed', '1' );
        } else {
            set_transient( 'pfm_wpfunnels_banner_temp_' . $user_id, '1', 14 * DAY_IN_SECONDS );
        }

        wp_send_json_success( array( 'message' => 'Dismissed' ), 200 );
    }

    /**
     * Dismiss the setup wizard for the current session ("remind me later").
     *
     * @since 7.4.14
     */
    public function dismiss_wizard() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
        if ( ! wp_verify_nonce( $nonce, 'rex-product-feed' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
            return;
        }

        $step_index = isset( $_POST['step_index'] ) ? absint( $_POST['step_index'] ) : 0;
        $step_id    = isset( $_POST['step_id'] ) ? sanitize_text_field( $_POST['step_id'] ) : 'merchant';

        $existing = get_option( 'pfm_wizard_onboarding_progress', array() );

        $progress = array_merge(
            array( 'started_at' => null, 'completed_steps' => array() ),
            $existing,
            array(
                'current_step_index' => $step_index,
                'current_step_id'    => $step_id,
                'dismissed_at'       => time(),
            )
        );

        if ( empty( $progress['started_at'] ) ) {
            $progress['started_at'] = time();
        }

        update_option( 'pfm_wizard_onboarding_progress', $progress, false );
        update_user_meta( get_current_user_id(), 'pfm_wizard_dismissed', '1' );

        $count_posts = wp_count_posts('product-feed');
        $total_feeds = (isset($count_posts->publish) ? $count_posts->publish : 0) + (isset($count_posts->draft) ? $count_posts->draft : 0);

        $is_premium = apply_filters( 'wpfm_is_premium', false );

        if ( $total_feeds === 0 && ! $is_premium ) {
            $country_code = get_option('woocommerce_default_country', 'US');
            if (strpos($country_code, ':') !== false) {
                $country_code = explode(':', $country_code)[0];
            }
            $country_name = 'United States';
            if ( function_exists('WC') && isset(WC()->countries) ) {
                $countries = WC()->countries->get_countries();
                if ( isset($countries[$country_code]) ) {
                    $country_name = $countries[$country_code];
                }
            }

            $demo_merchant   = $this->get_demo_merchant_by_country( $country_code );
            $date            = current_time('Y-m-d');
            $feed_name       = "Demo Feed - $country_name - $date";
            $merchant        = $demo_merchant['slug'];
            $merchant_name   = $demo_merchant['name'];
            $feed_format     = $demo_merchant['format'];
            $is_eu_merchant  = $demo_merchant['is_eu'];
            $update_frequency = 'daily';

            require_once plugin_dir_path(__FILE__) . '../admin/class-rex-feed-template-factory.php';
            try {
                $template = Rex_Feed_Template_Factory::build( $merchant, false );
                $feed_config = $template->get_template_mappings();

                if ( is_array($feed_config) ) {
                    foreach ($feed_config as &$mapping) {
                        if ( isset($mapping['attr']) && $mapping['attr'] === 'link' ) {
                            $mapping['suffix'] = '?utm_source=pfm_demo&utm_medium=feed';
                        }
                    }
                }

                $feed_id = wp_insert_post( array(
                    'post_title'  => $feed_name,
                    'post_type'   => 'product-feed',
                    'post_status' => 'publish',
                    'post_author' => get_current_user_id(),
                ) );

                if ( ! is_wp_error( $feed_id ) && $feed_id > 0 ) {
                    update_post_meta( $feed_id, 'pfm_feed_created_by', 'setup_wizard_skip' );
                    update_post_meta( $feed_id, '_rex_feed_merchant', $merchant );
                    update_post_meta( $feed_id, '_rex_feed_feed_format', $feed_format );
                    update_post_meta( $feed_id, '_rex_feed_schedule', $update_frequency );
                    update_post_meta( $feed_id, '_rex_feed_feed_config', $feed_config );

                    update_post_meta( $feed_id, '_rex_feed_variations', 'yes' );
                    update_post_meta( $feed_id, '_rex_feed_variable_product', 'yes' );
                    update_post_meta( $feed_id, '_rex_feed_parent_product', 'yes' );
                    update_post_meta( $feed_id, '_rex_feed_include_out_of_stock', 'no' );
                    update_post_meta( $feed_id, '_rex_feed_include_zero_price_products', 'no' );
                    update_post_meta( $feed_id, '_rex_feed_hidden_products', 'no' );
                    update_post_meta( $feed_id, '_rex_feed_skip_product', 'no' );
                    update_post_meta( $feed_id, '_rex_feed_skip_row', 'no' );

                    update_post_meta( $feed_id, '_rex_feed_products', 'all' );
                    update_post_meta( $feed_id, '_feed_created_at', time() );
                    update_post_meta( $feed_id, 'edit_count', 0 );
                    update_post_meta( $feed_id, '_rex_feed_is_google_content_api', 'no' );

                    if ( $is_eu_merchant ) {
                        update_post_meta( $feed_id, '_rex_feed_feed_country', $country_code );
                    }

                    $path = wp_upload_dir();
                    $path = $path['baseurl'] . '/rex-feed';
                    $feed_url = trailingslashit($path) . "feed-{$feed_id}.{$feed_format}";
                    update_post_meta( $feed_id, '_rex_feed_xml_file', $feed_url );

                    require_once plugin_dir_path(__FILE__) . '../admin/class-rex-feed-scheduler.php';
                    $scheduler = new Rex_Feed_Scheduler();
                    $scheduler->schedule_merchant_single_batch_object( [ $feed_id ], true );

                    set_transient( 'pfm_demo_feed_created', array(
                        'feed_id'       => $feed_id,
                        'merchant_name' => $merchant_name,
                        'country_name'  => $country_name,
                    ), 60 );
                }
            } catch ( Exception $e ) {
                error_log( 'PFM Demo Feed Creation Error: ' . $e->getMessage() );
            }
        }

        wp_send_json_success( array( 'message' => 'Wizard dismissed' ), 200 );
    }

    /**
     * Mark the setup wizard as completed for the current user.
     *
     * @since 7.4.14
     */
    public function mark_wizard_completed() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
        if ( ! wp_verify_nonce( $nonce, 'rex-product-feed' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
            return;
        }

        update_user_meta( get_current_user_id(), 'pfm_wizard_completed', '1' );
        do_action( 'rex_product_feed_setup_completed' );
        wp_send_json_success( array( 'message' => 'Wizard completed' ), 200 );
    }

    /**
     * Mark the guided tour as completed or skipped for the current user.
     *
     * @since 7.X.X
     */
    public function tour_update_status() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
            return;
        }

        update_user_meta( get_current_user_id(), 'wpfm_tour_completed_or_skipped', '1' );
        wp_send_json_success( array( 'message' => 'Tour status updated' ), 200 );
    }

    /**
     * Show re-engagement banner for users who dismissed the wizard without completing it.
     *
     * @since 7.4.14
     */
    /**
     * Check whether the re-engagement banner should be visible.
     *
     * @return bool
     */
    private function should_show_re_engagement_banner() {
        $user_id = get_current_user_id();
        
        $dismissed = get_user_meta( $user_id, 'pfm_wizard_dismissed', true );
        $completed = get_user_meta( $user_id, 'pfm_wizard_completed', true );

        if ( $dismissed === '1' ) {
            return false;
        }

        if ( $completed === '1' ) {
            return false;
        }

        $feeds = get_posts( array(
            'post_type'      => 'product-feed',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ) );
        return empty( $feeds );
    }

    /**
     * Suppress the Linno consent notice on admin_init when the re-engagement banner
     * will be shown — consent notice should only appear after the wizard is completed.
     *
     * @return void
     */
    public function maybe_suppress_consent_notice() {
        if ( $this->should_show_re_engagement_banner() ) {
            add_filter( 'pre_option_linno_telemetry_notice_dismissed', array( $this, 'suppress_consent_notice_for_request' ) );
        }
    }

    /**
     * Inject the re-engagement banner before the "All (0)" views row on the feed list page.
     *
     * Hooked to views_edit-product-feed — fires only on edit.php?post_type=product-feed,
     * right between the "Product Feeds" heading and the All/Published filter links.
     *
     * @param  array $views  Existing view links (passed through unchanged).
     * @return array
     */
    public function inject_re_engagement_banner( $views ) {
        if ( ! $this->should_show_re_engagement_banner() ) {
            return $views;
        }

        $progress = get_option( 'pfm_wizard_onboarding_progress', array() );

        $step_labels = array(
            'merchant'  => __( 'Select a Channel', 'rex-product-feed' ),
            'configure' => __( 'Configure Feed', 'rex-product-feed' ),
            'aha'       => __( 'Review Feed', 'rex-product-feed' ),
        );
        $step_index  = isset( $progress['current_step_index'] ) ? (int) $progress['current_step_index'] : 0;
        $step_id     = isset( $progress['current_step_id'] ) ? $progress['current_step_id'] : 'merchant';
        $total_steps = 3;
        $step_number = $step_index + 1;
        $step_label  = isset( $step_labels[ $step_id ] ) ? $step_labels[ $step_id ] : $step_labels['merchant'];
        $progress_pct = max( 8, (int) round( ( $step_number / $total_steps ) * 40 ) );

        $wizard_url = admin_url( 'admin.php?page=wpfm-setup-wizard' );
        $primary    = '#3272EA';
        ?>
        <style>
        #pfm-reb{display:flex;align-items:center;gap:16px;background:#fff;border-radius:8px;border-left:4px solid <?php echo esc_attr( $primary ); ?>;padding:16px 20px;margin:16px 20px 0 0;box-shadow:0 1px 4px rgba(0,0,0,.08);box-sizing:border-box;}
        #pfm-reb .pfm-reb-icon{flex-shrink:0;width:40px;height:40px;border-radius:50%;background:#EEF4FF;display:flex;align-items:center;justify-content:center;}
        #pfm-reb .pfm-reb-body{flex:1;min-width:0;}
        #pfm-reb .pfm-reb-title{font-size:14px;font-weight:700;color:#1e1e1e;margin:0 0 4px;}
        #pfm-reb .pfm-reb-desc{font-size:13px;color:#6b7280;margin:0 0 10px;line-height:1.5;}
        #pfm-reb .pfm-reb-progress-row{display:flex;align-items:center;gap:10px;}
        #pfm-reb .pfm-reb-bar{flex:0 0 140px;height:6px;background:#E5E7EB;border-radius:99px;overflow:hidden;}
        #pfm-reb .pfm-reb-bar-fill{height:100%;background:<?php echo esc_attr( $primary ); ?>;border-radius:99px;transition:width .4s ease;}
        #pfm-reb .pfm-reb-step-label{font-size:12px;font-weight:600;color:<?php echo esc_attr( $primary ); ?>;}
        #pfm-reb .pfm-reb-btn{flex-shrink:0;background:<?php echo esc_attr( $primary ); ?>;color:#fff;border:none;border-radius:6px;padding:10px 20px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap;transition:opacity .2s;}
        #pfm-reb .pfm-reb-btn:hover{opacity:.88;color:#fff;}
        #pfm-reb .pfm-reb-close{flex-shrink:0;background:none;border:none;cursor:pointer;padding:4px;color:#9CA3AF;line-height:1;font-size:18px;margin-left:4px;}
        #pfm-reb .pfm-reb-close:hover{color:#374151;}
        </style>
        <div id="pfm-reb">
            <div class="pfm-reb-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 2L4.09 12.11A1 1 0 0 0 5 13.5h5.5L10 22l9.91-10.11A1 1 0 0 0 19 10.5H13.5L13 2Z" fill="<?php echo esc_attr( $primary ); ?>" opacity=".9"/>
                </svg>
            </div>
            <div class="pfm-reb-body">
                <p class="pfm-reb-title"><?php esc_html_e( 'Your product feeds aren\'t live yet.', 'rex-product-feed' ); ?></p>
                <p class="pfm-reb-desc"><?php esc_html_e( 'Finish setup to start selling on Google, Meta, and 200+ other channels. It only takes 2 minutes.', 'rex-product-feed' ); ?></p>
                <div class="pfm-reb-progress-row">
                    <div class="pfm-reb-bar">
                        <div class="pfm-reb-bar-fill" style="width:<?php echo esc_attr( $progress_pct ); ?>%"></div>
                    </div>
                    <span class="pfm-reb-step-label">
                        <?php
                        printf(
                            /* translators: 1: current step number, 2: total steps, 3: step name */
                            esc_html__( 'Step %1$d of %2$d: %3$s', 'rex-product-feed' ),
                            esc_html( $step_number ),
                            esc_html( $total_steps ),
                            esc_html( $step_label )
                        );
                        ?>
                    </span>
                </div>
            </div>
            <a href="<?php echo esc_url( $wizard_url ); ?>" class="pfm-reb-btn">
                <?php esc_html_e( 'Finish Setup', 'rex-product-feed' ); ?> &rarr;
            </a>
            <button type="button" class="pfm-reb-close" id="pfmRebClose" aria-label="<?php esc_attr_e( 'Dismiss', 'rex-product-feed' ); ?>">&#215;</button>
        </div>
        <script>
        (function(){
            document.getElementById('pfmRebClose').addEventListener('click', function(){
                document.getElementById('pfm-reb').style.display = 'none';
                document.cookie = 'pfm_reb_session_dismissed=1; path=/';
            });
        })();
        </script>
        <?php
        return $views;
    }

    /**
     * Return 'yes' for the linno_telemetry_notice_dismissed option filter.
     *
     * Applied for the current request only when the re-engagement banner is visible,
     * so the Linno consent notice is suppressed until the wizard is completed.
     *
     * @return string
     */
    public function suppress_consent_notice_for_request() {
        return 'yes';
    }

    /**
     * Return the most relevant free demo merchant for a given ISO country code.
     *
     * Priority:
     *   DE  → idealo_de  |  NL → bol  |  FR → fnac  |  PL → ceneo
     *   GR  → skroutz   |  EU (other) → idealo  |  everywhere else → google
     *
     * @param string $country_code Two-letter ISO 3166-1 alpha-2 country code.
     * @return array{slug:string, name:string, format:string, is_eu:bool}
     */
    private function get_demo_merchant_by_country( $country_code ) {
        $eu_countries = array(
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
            'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
            'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
        );

        $map = array(
            'DE' => array( 'slug' => 'idealo_de', 'name' => 'Idealo.de',  'format' => 'csv',  'is_eu' => true ),
            'NL' => array( 'slug' => 'bol',       'name' => 'Bol.com',    'format' => 'csv',  'is_eu' => true ),
            'FR' => array( 'slug' => 'fnac',      'name' => 'Fnac',       'format' => 'xml',  'is_eu' => true ),
            'PL' => array( 'slug' => 'ceneo',     'name' => 'Ceneo',      'format' => 'xml',  'is_eu' => true ),
            'GR' => array( 'slug' => 'skroutz',   'name' => 'Skroutz',    'format' => 'xml',  'is_eu' => true ),
        );

        if ( isset( $map[ $country_code ] ) ) {
            return $map[ $country_code ];
        }

        if ( in_array( $country_code, $eu_countries, true ) ) {
            return array( 'slug' => 'idealo', 'name' => 'Idealo', 'format' => 'csv', 'is_eu' => true );
        }

        return array( 'slug' => 'google', 'name' => 'Google Shopping', 'format' => 'xml', 'is_eu' => false );
    }

    /**
     * Normalize feed config to query string expected by core feed generator.
     *
     * @param mixed  $feed_config    Feed config as query string or mappings array.
     * @param string $products_scope Product scope.
     *
     * @return string
     */
    private function normalize_feed_config( $feed_config, $products_scope = 'all' ) {
        if ( is_array( $feed_config ) ) {
            $normalized = array(
                'fc'                => $feed_config,
                'rex_feed_products' => $products_scope,
            );

            return http_build_query( $normalized, '', '&' );
        }

        $feed_config = (string) $feed_config;
        if ( '' === $feed_config ) {
            return '';
        }

        $parsed = array();
        wp_parse_str( $feed_config, $parsed );

        if ( ! isset( $parsed['rex_feed_products'] ) || '' === $parsed['rex_feed_products'] ) {
            $parsed['rex_feed_products'] = $products_scope;
        }

        return http_build_query( $parsed, '', '&' );
    }
}
