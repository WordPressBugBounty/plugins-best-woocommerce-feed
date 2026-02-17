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
        update_option('best-woocommerce-feed_allow_tracking', '1' === $consent ? 'yes' : 'no');
        
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
        if ( function_exists( 'coderex_telemetry_track' ) && defined( 'WPFM__FILE__' ) ) {
            coderex_telemetry_track(
                WPFM__FILE__,
                'setup_started',
                array(
                    'time' => current_time('mysql'),
                )
            );
        }

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

        // Check if tracking is allowed
        $tracking_allowed = get_option('best-woocommerce-feed_allow_tracking', 'no');
        if ( 'yes' !== $tracking_allowed ) {
            wp_send_json_success( array( 'message' => 'Tracking not allowed' ), 200 );
            return;
        }

        if ( function_exists( 'coderex_telemetry_track' ) && defined( 'WPFM__FILE__' ) ) {
            coderex_telemetry_track(
                WPFM__FILE__,
                'setup_completed',
                array(
                    'time' => current_time('mysql'),
                )
            );
        }

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

        // Check if tracking is allowed
        $tracking_allowed = get_option('best-woocommerce-feed_allow_tracking', 'no');
        if ( 'yes' !== $tracking_allowed ) {
            wp_send_json_success( array( 'message' => 'Tracking not allowed' ), 200 );
            return;
        }

        $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
        if ( !wp_verify_nonce( $nonce, 'rex-product-feed' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
            return;
        }

        $feed_data = isset($_POST['feed_data']) ? $_POST['feed_data'] : array();
        
        if ( function_exists( 'coderex_telemetry_track' ) && defined( 'WPFM__FILE__' ) ) {
            coderex_telemetry_track(
                WPFM__FILE__,
                'first_strike_completed',
                array(
                    'feed_name' => isset($feed_data['name']) ? sanitize_text_field($feed_data['name']) : '',
                    'merchant' => isset($feed_data['merchant']) ? sanitize_text_field($feed_data['merchant']) : '',
                    'format' => isset($feed_data['format']) ? sanitize_text_field($feed_data['format']) : '',
                    'frequency' => isset($feed_data['frequency']) ? sanitize_text_field($feed_data['frequency']) : '',
                    'time' => current_time('mysql'),
                )
            );
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
        $popular_ids = array('google', 'facebook', 'tiktok', 'instagram', 'yandex');
        
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
            update_post_meta( $feed_id, '_rex_feed_parent_product', 'no' );
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

            // Return success with feed data and batch info
            wp_send_json_success( array(
                'feed_id' => $feed_id,
                'feed_url' => $feed_url,
                'edit_url' => admin_url( 'post.php?post=' . $feed_id . '&action=edit' ),
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
        
        // If feed_config is not in payload or is empty, read from post meta
        if ( empty($payload['feed_config']) && !empty($payload['info']['post_id']) ) {
            $feed_id = intval($payload['info']['post_id']);
            $feed_config = get_post_meta( $feed_id, '_rex_feed_feed_config', true );
            if ( !empty($feed_config) && is_array($feed_config) ) {
                $payload['feed_config'] = $feed_config;
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
}
