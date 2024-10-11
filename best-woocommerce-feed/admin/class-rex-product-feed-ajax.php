<?php
/**
 * Class Rex_Product_Feed_Ajax
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Metabox
 * @subpackage Rex_Product_Feed/admin
 */

/**
 * The admin-specific functionality of the plugin
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Metabox
 * @subpackage Rex_Product_Feed/admin
 */
class Rex_Product_Feed_Ajax {

    /**
     * The Product/Feed Config.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator    config    Feed config.
     */
    protected $config;

    /**
     * The feed format.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator $feed_format Contains format of the feed.
     */
    protected $feed_format;

    /**
     * Product Scope
     *
     * @since    1.1.10
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $product_scope
     */
    protected $product_scope;

    /**
     * Hook in ajax handlers.
     *
     * @since    1.0.0
     */
    public static function init() {
        $validations = array(
            'logged_in' => true,
            'user_can'  => 'manage_options',
        );

        wp_ajax_helper()->handle( 'rexfeed-get-total-products' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'get_product_number' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rexfeed-generate-feed' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'generate_feed' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rexfeed-load-config-table' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'show_feed_template' ) )
                        ->with_validation( $validations );

        // Google Category Mapping.
        wp_ajax_helper()->handle( 'rexfeed-save-category-mapping' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'save_category_mapping' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rexfeed-update-category-mapping' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'update_category_mapping' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rexfeed-delete-category-mapping' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'delete_category_mapping' ) )
                        ->with_validation( $validations );

        // Google merchant settings.
        wp_ajax_helper()->handle( 'rexfeed-google-merchant-settings' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'save_google_api_credentials' ) )
                        ->with_validation( $validations );

        // Send to Google Merchant Center.
        wp_ajax_helper()->handle( 'rexfeed-send-to-google' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'send_to_google' ) )
                        ->with_validation( $validations );

        // Database Update.
        wp_ajax_helper()->handle( 'rex-wpfm-database-update' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'database_update' ) )
                        ->with_validation( $validations );

        // Database Update.
        wp_ajax_helper()->handle( 'rex-wpfm-fetch-google-category' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'fetch_google_category' ) )
                        ->with_validation( $validations );

        // Update batch.
        wp_ajax_helper()->handle( 'rex-product-update-batch-size' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'update_batch_size' ) )
                        ->with_validation( $validations );

        // Clear batch.
        wp_ajax_helper()->handle( 'rex-product-clear-batch' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'clear_batch' ) )
                        ->with_validation( $validations );

        // Show log.
        wp_ajax_helper()->handle( 'rex-product-feed-show-log' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'show_wpfm_log' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'wpfm-enable-fb-pixel' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'enable_fb_pixel' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rexfeed-save-fb-pixel-value' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'save_fb_pixel_value' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rexfeed-save-tiktok-pixel-value' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'save_tiktok_pixel_value' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-enable-log' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'enable_log' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rexfeed-save-wpfm-transient' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'save_transient' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rexfeed-purge-wpfm-transient-cache' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'purge_transient_cache' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rexfeed-allow-private-products' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'allow_private_products' ) )
                        ->with_validation( $validations );

        // Trigger review request.
        wp_ajax_helper()->handle( 'rexfeed-trigger-review-request' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'trigger_review_request' ) )
                        ->with_validation( $validations );

        // Save WPFM Custom meta field values to show in the front view.
        wp_ajax_helper()->handle( 'rex-product-save-custom-fields-data' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'save_custom_fields_data' ) )
                        ->with_validation( $validations );

        // New UI changes message.
        wp_ajax_helper()->handle( 'rexfeed-new-ui-changes-message' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'new_ui_changes_message' ) )
                        ->with_validation( $validations );

        // Loads taxonomies.
        wp_ajax_helper()->handle( 'rex-feed-load-taxonomies' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'load_taxonomies' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-feed-get-appsero-options' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'get_appsero_options' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'wpfm-remove-plugin-data' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'remove_plugin_data' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-feed-handle-custom-filters-content' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'rex_feed_get_custom_filters_content' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-feed-save-char-limit-option' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'save_char_limit_option' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-feed-delete-publish-btn-id' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'delete_publish_btn_id' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-feed-hide-char-limit-col' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'hide_char_limit_col' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-feed-update-abandoned-child-list' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'update_abandoned_child_list' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-feed-update-single-feed' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'update_single_feed' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-feed-save-filters-data' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'save_filters_data' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-feed-save-settings-data' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'save_settings_data' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-feed-is-filter-changed' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'is_filter_changed' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rex-feed-is-settings-changed' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'is_settings_changed' ) )
                        ->with_validation( $validations );

	    wp_ajax_helper()->handle( 'rexfeed-fetch-gmc-report' )
	                    ->with_callback( array( 'Rex_Product_Feed_Ajax', 'fetch_gmc_report' ) )
	                    ->with_validation( $validations );
    }


    /**
     * Get total number of products
     *
     * @param array $payload Payload.
     *
     * @since    2.0.0
     */
    public static function get_product_number( $payload ) {
        $feed_id = !empty( $payload[ 'feed_id' ] ) ? $payload[ 'feed_id' ] : '';

        if ( isset( $payload[ 'feed_title' ] ) && '' !== $payload[ 'feed_title' ] ) {
            $args = [
                'post_type'      => 'product-feed',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'title'          => $payload[ 'feed_title' ],
            ];

            $feed_ids     = get_posts( $args );
            $current_feed = array_search( $feed_id, $feed_ids );
            if ( false !== $current_feed ) {
                unset( $feed_ids[ $current_feed ] );
            }

            if ( !empty( $feed_ids ) ) {
                return [
                    'feed_title' => 'duplicate',
                ];
            }
        }

        $btn_id     = !empty( $payload[ 'button_id' ] ) ? $payload[ 'button_id' ] : '';
        $is_premium = apply_filters( 'wpfm_is_premium', false );
        $products   = apply_filters( 'wpfm_get_total_number_of_products', array( 'products' => WPFM_FREE_MAX_PRODUCT_LIMIT ), $feed_id );
        $per_page   = get_option( 'rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT );

        if ( (int) $per_page >= WPFM_FREE_MAX_PRODUCT_LIMIT && !$is_premium ) {
            $posts_per_page = WPFM_FREE_MAX_PRODUCT_LIMIT;
        }
        else {
            $posts_per_page = (int) $per_page;
        }

        update_post_meta( $feed_id, '_rex_feed_publish_btn', $btn_id );

        return [
            'products'    => $products[ 'products' ],
            'per_batch'   => $posts_per_page,
            'total_batch' => ceil( $products[ 'products' ] / $posts_per_page ),
            'feed_title'  => 'unique',
        ];
    }


    /**
     * Generate feed
     *
     * @param array $config Feed configs.
     *
     * @return string
     */
    public static function generate_feed( $config ) {
        try {
            $merchant = Rex_Product_Feed_Factory::build( $config );
        if( $config[ 'info' ][ 'batch' ] === $config[ 'info' ][ 'total_batch' ] ) {
              Rex_Product_Feed_Controller::update_feed_status( $config[ 'info' ][ 'post_id' ], 'completed' );
	        update_post_meta( $config[ 'info' ][ 'post_id' ], '_rex_mas_last_sync', time() );
        }
        }
        catch ( Exception $e ) {
            return $e->getMessage();
        }
        return $merchant->make_feed();
    }


    /**
     * Show feed template
     *
     * @param array $merchant Merchant name.
     *
     * @return array
     * @throws Exception Exception.
     */
    public static function show_feed_template( $merchant ) {
        $post_id        = !empty( $merchant[ 'post_id' ] ) ? $merchant[ 'post_id' ] : '';
        $feed_configs     = get_post_meta( $post_id, '_rex_feed_feed_config', true ) ?: get_post_meta( $post_id, 'rex_feed_feed_config', true );
        $merchant_name  = !empty( $merchant[ 'merchant' ] ) ? $merchant[ 'merchant' ] : '';
        $saved_merchant = get_post_meta( $post_id, '_rex_feed_merchant', true ) ?: get_post_meta( $post_id, 'rex_feed_merchant', true );

        if ( $merchant_name !== $saved_merchant ) {
            $feed_configs = false;
        }

        $feed_template  = Rex_Feed_Template_Factory::build( $merchant_name, $feed_configs );
        $feed_format    = Rex_Feed_Merchants::get_feed_formats( $merchant_name );
        $feed_separator = Rex_Feed_Merchants::get_csv_feed_separators( $merchant_name );

        ob_start();

        /**
         * Applies filters to the template markup path and related parameters for displaying a feed configuration metabox.
         *
         * This function triggers the dynamic filter hook "rexfeed_{$merchant_name}_template_markups" which allows developers
         * to modify the template markup path used for displaying a feed configuration metabox and related parameters.
         *
         * @param string  $template_markup       The default path to the template markup file.
         * @param string  $feed_template         The current feed template.
         * @param string  $feed_format           The format of the feed.
         * @param string  $feed_separator        The separator used in the feed.
         *
         * @return string The filtered template markup path.
         * @since 7.3.11
         */
        $template_markup = apply_filters(
            "rexfeed_{$merchant_name}_template_markups",
            plugin_dir_path( __FILE__ ) . 'partials/feed-config-metabox-display.php',
            $feed_template, $feed_format, $feed_separator,
        );
        include_once $template_markup;

        $result = ob_get_contents();
        ob_end_clean();
        ob_flush();

        $selected_format = get_post_meta( $merchant[ 'post_id' ], '_rex_feed_feed_format', true ) ?: get_post_meta( $merchant[ 'post_id' ], 'rex_feed_feed_format', true );
        if ( !$selected_format ) {
            $selected_format = $feed_format[ 0 ];
        }

        return array(
            'success'        => true,
            'html'           => $result,
            'feed_format'    => $feed_format,
            'feed_separator' => $feed_separator,
            'select'         => $selected_format,
            'saved_merchant' => $saved_merchant,
        );
    }


    /**
     * Save Category Map
     *
     * @param array $payload Payload.
     *
     * @return void
     */
    public static function save_category_mapping( $payload ) {
        $cat_map_url = esc_url( admin_url( 'admin.php?page=category_mapping' ) );
        if( !empty( $payload[ 'map_name' ] ) ) {
            $map_name     = $payload[ 'map_name' ];
            $category_map = get_option( 'rex-wpfm-category-mapping' ) ? get_option( 'rex-wpfm-category-mapping' ) : array();
            $status       = 'success';
            $wpfm_hash    = !empty( $payload[ 'hash' ] ) ? $payload[ 'hash' ] : '';

            if( '' !== $wpfm_hash && array_key_exists( $wpfm_hash, $category_map ) ) {
                wp_send_json_success(
                    array(
                        'status'   => $status,
                        'location' => $cat_map_url,
                    ),
                );
            }
            if( '' !== $wpfm_hash ) {
                $status = 'reload';
            }

            $map_name_hash = '' !== $wpfm_hash ? $wpfm_hash : md5( sanitize_title( $map_name ) . time() );
            $cat_map_array = array();
            parse_str( $payload[ 'cat_map' ], $cat_map_array );
            $config_array = array();
            $map_array    = array();
            if( $cat_map_array ) {
                foreach( $cat_map_array as $key => $value ) {
                    $cat_id        = preg_replace( '/[^0-9]/', '', $key );
                    $product_cat   = get_term_by( 'id', $cat_id, 'product_cat' );
                    $category_name = '';
                    if( $product_cat ) {
                        $category_name = $product_cat->name;
                    }
                    $config_array[] = array(
                        'map-key'   => $cat_id,
                        'map-value' => $value,
                        'cat-name'  => $category_name,
                    );
                }
            }

            $map_array[ 'map-name' ]   = $map_name;
            $map_array[ 'map-config' ] = $config_array;
            $category_map[ $map_name_hash ] = $map_array;

            update_option( 'rex-wpfm-category-mapping', $category_map );

            wp_send_json_success( [
                'status'   => $status,
                'location' => $cat_map_url,
            ] );
        }
        wp_send_json_error( [
            'status'   => 'failed',
            'location' => $cat_map_url,
        ] );
    }


    /**
     * Generate category mapping
     *
     * @param array $payload Payload.
     *
     * @return string
     */
    public static function update_category_mapping( $payload ) {
        $map_key       = !empty( $payload[ 'map_key' ] ) ? $payload[ 'map_key' ] : '';
        $map_name      = !empty( $payload[ 'map_name' ] ) ? $payload[ 'map_name' ] : '';
        $cat_map_array = [];

        parse_str( $payload[ 'cat_map' ], $cat_map_array );
        $config_array = [];
        $map_array    = [];

        if ( $cat_map_array ) {
            foreach ( $cat_map_array as $key => $value ) {
                $cat_id        = preg_replace( '/[^0-9]/', '', $key );
                $product_cat   = get_term_by( 'id', $cat_id, 'product_cat' );
                $category_name = '';
                if ( $product_cat ) {
                    $category_name = $product_cat->name;
                }
                $config_array[] = [
                    'map-key'   => $cat_id,
                    'map-value' => $value,
                    'cat-name'  => $category_name,
                ];
            }
        }

        $map_array[ 'map-name' ]   = $map_name;
        $map_array[ 'map-config' ] = $config_array;
        $category_map              = get_option( 'rex-wpfm-category-mapping' ) ? get_option( 'rex-wpfm-category-mapping' ) : array();
        $category_map[ $map_key ]  = $map_array;
        update_option( 'rex-wpfm-category-mapping', $category_map );
        return 'success';
    }


    /**
     * Delete Category Mapping
     *
     * @param array $payload Payload.
     *
     * @return string
     */
    public static function delete_category_mapping( $payload ) {
        if( !empty( $payload[ 'map_key' ] ) ) {
            $map_key      = $payload[ 'map_key' ];
            $category_map = get_option( 'rex-wpfm-category-mapping' );
            unset( $category_map[ $map_key ] );
            update_option( 'rex-wpfm-category-mapping', $category_map );
            return [ 'status' => 'success' ];
        }
        return [ 'status' => 'failed' ];
    }


    /**
     * Send feed to Google
     *
     * @param array $payload Payload.
     *
     * @return array
     */
    public static function send_to_google( $payload ) {
        $feed_id             = !empty( $payload[ 'feed_id' ] ) ? $payload[ 'feed_id' ] : null;
        $rex_google_merchant = new Rex_Google_Merchant_Settings_Api();
        if ( $feed_id && $rex_google_merchant->is_authenticate() ) {
            $feed_url      = get_post_meta( $feed_id, '_rex_feed_xml_file', true ) ?: get_post_meta( $feed_id, 'rex_feed_xml_file', true );
            $feed_title    = get_the_title( $feed_id );
            $client        = $rex_google_merchant::get_client();
            $client_id     = $rex_google_merchant::$client_id;
            $client_secret = $rex_google_merchant::$client_secret;
            $merchant_id   = $rex_google_merchant::$merchant_id;

            $access_token = $rex_google_merchant->get_access_token();
            $client->setClientId( $client_id );
            $client->setClientSecret( $client_secret );
            $client->setScopes( 'https://www.googleapis.com/auth/content' );
            $client->setAccessToken( $access_token );

            // Initialize service and datafeed.
            $service  = new RexFeed\Google\Service\ShoppingContent( $client );
            $datafeed = new RexFeed\Google\Service\ShoppingContent\Datafeed();
            $target   = new RexFeed\Google\Service\ShoppingContent\DatafeedTarget();

            $name     = $feed_title;
            $filename = $name . uniqid();

            if ( isset( $payload[ 'language' ] ) ) {
                $target->setLanguage( $payload[ 'language' ] );
                $datafeed->setAttributeLanguage( $payload[ 'language' ] );
            }
            if ( isset( $payload[ 'country' ] ) ) {
                $target->setCountry( $payload[ 'country' ] );
            }

            $datafeed->setName( $name );
            $datafeed->setContentType( 'products' );
            $datafeed->setTargets( array( $target ) );

            if ( !$rex_google_merchant->feed_exists( $feed_id ) ) {
                $datafeed->setFileName( $filename );
            }
            else {
                $data_feed_file = get_post_meta( $feed_id, '_rex_feed_google_data_feed_file_name', true ) ?: get_post_meta( $feed_id, 'rex_feed_google_data_feed_file_name', true );
                $datafeed->setFileName( $data_feed_file );
            }

            // Initialize Schedule.
            $fetch_schedule = new RexFeed\Google\Service\ShoppingContent\DatafeedFetchSchedule();
            if ( !empty( $payload[ 'schedule' ] ) ) {
                if ( 'monthly' === $payload[ 'schedule' ] && isset( $payload[ 'month' ] ) ) {
                    $fetch_schedule->setDayOfMonth( $payload[ 'month' ] );
                }
                if ( 'weekly' === $payload[ 'schedule' ] && isset( $payload[ 'day' ] ) ) {
                    $fetch_schedule->setWeekday( $payload[ 'day' ] );
                }
            }

            if ( isset( $payload[ 'hour' ] ) ) {
                $fetch_schedule->setHour( $payload[ 'hour' ] );
            }
            $fetch_schedule->setFetchUrl( $feed_url );

            // Initialize feed format.
            $format = new RexFeed\Google\Service\ShoppingContent\DatafeedFormat();
            $format->setFileEncoding( 'utf-8' );
            $datafeed->setFormat( $format );
            $datafeed->setFetchSchedule( $fetch_schedule );

            try {
                if ( $rex_google_merchant->feed_exists( $feed_id ) ) {
                    $data_feed_id = get_post_meta( $feed_id, '_rex_feed_google_data_feed_id', true ) ?: get_post_meta( $feed_id, 'rex_feed_google_data_feed_id', true );
                    $datafeed->setId( $data_feed_id );
                    $service->datafeeds->update( $merchant_id, $data_feed_id, $datafeed );
                }
                else {
                    $datafeed            = $service->datafeeds->insert( $merchant_id, $datafeed );
                    $data_feed_id        = $datafeed->getId();
                    $data_feed_file_name = $datafeed->getFileName();
                    update_post_meta( $feed_id, '_rex_feed_google_data_feed_id', $data_feed_id );
                    update_post_meta( $feed_id, '_rex_feed_google_data_feed_file_name', $data_feed_file_name );
                }
                $service->datafeeds->fetchnow( $merchant_id, $data_feed_id );
            }
            catch ( Exception $e ) {
                if ( is_wpfm_logging_enabled() ) {
                    $log = wc_get_logger();
                    $log->info( $e->getMessage(), array( 'source' => 'WPFM-google' ) );
                }

                if ( !is_string( $e->getMessage() ) && is_object( $e->getMessage() ) ) {
                    $error  = json_decode( $e->getMessage() );
                    $reason = !empty( $error->error->errors ) ? $error->error->errors : '';
                }
                else {
                    $error = $e->getMessage();
                }

                return array(
                    'success' => false,
                    'message' => !empty( $error->error->message ) ? $error->error->message : $error,
                    'reason'  => !empty( $reason[ 0 ]->reason ) ? $reason[ 0 ]->reason : $error,
                );
            }
        }

        if ( isset( $payload[ 'schedule' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_google_schedule', $payload[ 'schedule' ] );
        }
        if ( isset( $payload[ 'hour' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_google_schedule_time', $payload[ 'hour' ] );
        }
        if ( isset( $payload[ 'month' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_google_schedule_month', $payload[ 'month' ] );
        }
        if ( isset( $payload[ 'day' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_google_schedule_week_day', $payload[ 'day' ] );
        }
        if ( isset( $payload[ 'country' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_google_target_country', $payload[ 'country' ] );
        }
        if ( isset( $payload[ 'language' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_google_target_language', $payload[ 'language' ] );
        }
        return array( 'success' => true );
    }


    /**
     * WPFM database update
     *
     * @return void
     */
    public static function database_update() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );
        require_once WPFM_PLUGIN_DIR_PATH . 'includes/class-rex-product-feed-activator.php';
        set_transient( 'rex-wpfm-database-update-running', true, 3153600000 );
        global $rex_product_feed_database_update;
        $db_updates_callbacks = Rex_Product_Feed_Activator::get_db_update_callbacks();
        $rex_product_feed_database_update->push_to_queue( $db_updates_callbacks );
        $rex_product_feed_database_update->save()->dispatch();
        Rex_Product_Feed_Activator::update_db_version( '2.2.5' );
        wp_send_json_success( 'success' );
        wp_die();
    }


    /**
     * Fetch google category
     *
     * @return string
     */
    public static function fetch_google_category() {
        $file = dirname( __FILE__ ) . '/partials/google_category_list.txt';
        if ( file_exists( $file ) ) {
            $handle  = @fopen( $file, "r" ); //phpcs:ignore
            $matches = array();
            while ( !feof( $handle ) ) {
                $cat       = fgets( $handle );
                $matches[] = $cat;
            }
            fclose( $handle ); //phpcs:ignore
            return wp_json_encode( $matches, JSON_PRETTY_PRINT );
        }
        return wp_json_encode( array(), JSON_PRETTY_PRINT );
    }


    /**
     * Clear current batch
     *
     * @return void
     * @since 1.0.0
     */
    public static function clear_batch() {
        global $wpdb;

        try {
            $wpdb->update(
                $wpdb->actionscheduler_actions,
                [ 'status' => 'failed' ],
                [
                    'hook'   => 'rex_feed_regenerate_feed_batch',
                    'status' => 'processing',
                ],
            );
            $wpdb->update(
                $wpdb->actionscheduler_actions,
                [ 'status' => 'failed' ],
                [
                    'hook'   => 'rex_feed_regenerate_feed_batch',
                    'status' => 'pending',
                ],
            );
            $wpdb->update(
                $wpdb->postmeta,
                [ 'meta_value' => 'completed' ],
                [ 'meta_key' => '_rex_feed_status' ],
            );
        }
        catch( Exception $e ) {
            if( is_wpfm_logging_enabled() ) {
                $log = wc_get_logger();
                $log->warning( print_r( $e->getMessage(), 1 ), array( 'source' => 'WPFM' ) );
            }
        }

        wp_send_json_success( 'success' );
        wp_die();
    }

    /**
     * Update batch size
     *
     * @param array $payload Payload.
     *
     * @return void
     */
    public static function update_batch_size( $payload ) {
        update_option( 'rex-wpfm-product-per-batch', $payload );
        wp_send_json_success( 'success' );
        wp_die();
    }


    /**
     * WPFM log
     *
     * @param array $payload Payload.
     *
     * @return array
     */
    public static function show_wpfm_log( $payload ) {
        if ( !empty( $payload[ 'logKey' ] ) && defined( 'WC_LOG_DIR' ) ) {
            $wc_log   = WC_Admin_Status::scan_log_files();
            $key      = filter_var( $payload[ 'logKey' ], FILTER_SANITIZE_STRING );
            $file_url = realpath( WC_LOG_DIR . $key );

            if ( !in_array( $key, $wc_log ) || empty( $file_url ) || false === strpos( $file_url, WC_LOG_DIR ) ) {
                return [
                    'success'  => false,
                    'content'  => 'Access Denied!',
                    'file_url' => '',
                ];
            }

            ob_start();
            include_once $file_url;
            $out = ob_get_clean();
            ob_end_clean();
            return [
                'success'  => true,
                'content'  => $out,
                'file_url' => $file_url,
            ];
        }
        return [
            'success'  => false,
            'content'  => '',
            'file_url' => '',
        ];
    }


    /**
     * Black friday notice dismiss
     *
     * @return array
     */
    public static function black_friday_notice_dismiss() {
        $current_time = time();
        $date_now     = gmdate( "Y-m-d", $current_time );
        if ( '2019-11-29' === $date_now || '2019-11-28' === $date_now ) {
            $wpfm_bf_notice = array(
                'show_notice' => 'never',
                'updated_at'  => time(),
            );
        }
        else {
            $wpfm_bf_notice = array(
                'show_notice' => 'no',
                'updated_at'  => time(),
            );
        }
        update_option( 'wpfm_bf_notice', wp_json_encode( $wpfm_bf_notice ) );
        return array(
            'success' => true,
        );
    }


    /**
     * Enable facebook pixel tracking
     *
     * @param array $payload Payload.
     *
     * @return array
     */
    public static function enable_fb_pixel( $payload ) {
        if ( 'yes' === $payload[ 'wpfm_fb_pixel_enabled' ] ) {
            update_option( 'wpfm_fb_pixel_enabled', 'yes' );
            return array(
                'success' => true,
                'data'    => 'enabled',
            );
        }
        else {
            update_option( 'wpfm_fb_pixel_enabled', 'no' );
            return array(
                'success' => true,
                'data'    => 'disabled',
            );
        }
    }

    /**
     * Save facebook pixel key
     *
     * @param array $payload Payload.
     *
     * @return array
     */
    public static function save_fb_pixel_value( $payload ) {
        update_option( 'wpfm_fb_pixel_value', $payload );
        return array(
            'success' => true,
        );
    }

    /**
     * Save facebook pixel key
     *
     * @param array $payload Payload.
     *
     * @return array
     */
    public static function save_tiktok_pixel_value( $payload ) {
        update_option( 'wpfm_tiktok_pixel_value', $payload );
        return array(
            'success' => true,
        );
    }

    /**
     * Enable logging
     *
     * @param array $payload Payload.
     *
     * @return array
     */
    public static function enable_log( $payload ) {
        if ( 'yes' === $payload[ 'wpfm_enable_log' ] ) {
            update_option( 'wpfm_enable_log', 'yes' );
            return array(
                'success' => true,
                'data'    => 'enabled',
            );
        }
        else {
            update_option( 'wpfm_enable_log', 'no' );
            return array(
                'success' => true,
                'data'    => 'disabled',
            );
        }
    }

    /**
     * Save transient
     *
     * @param array $payload Payload.
     *
     * @return bool[]
     */
    public static function save_transient( $payload ) {
        if ( isset( $payload[ 'value' ] ) ) {
            update_option( 'wpfm_cache_ttl', $payload[ 'value' ] );
        }
        return array(
            'success' => true,
        );
    }

    /**
     * Clear transient
     *
     * @return bool[]
     */
    public static function purge_transient_cache() {
        wpfm_purge_cached_data();
        return array(
            'success' => true,
        );
    }


    /**
     * Enable/Disable private products
     *
     * @param array $payload Payload.
     *
     * @return array
     */
    public static function allow_private_products( $payload ) {
        if ( isset( $payload[ 'allow_private' ] ) ) {
            update_option( 'wpfm_allow_private', $payload[ 'allow_private' ] );
        }
        return array(
            'success' => true,
        );
    }


    /**
     * Black friday notice dismiss
     *
     * @return array
     * @since 6.1.0
     */
    public static function rt_black_friday_offer_notice_dismiss() {
        $current_time = time();
        $info         = array(
            'show_notice' => 'no',
            'updated_at'  => $current_time,
        );
        update_option( 'rt_bf_notice', $info );
        return array(
            'success' => true,
        );
    }


    /**
     * Update into database - Trigger Based Review Request
     *
     * @param array $payload Payload.
     *
     * @return bool[]
     */
    public static function trigger_review_request( $payload ) {
        $data = array(
            'show'      => !empty( $payload[ 'show' ] ) ? $payload[ 'show' ] : '',
            'time'      => !empty( $payload[ 'frequency' ] ) && 'never' !== $payload[ 'frequency' ] ? time() : '',
            'frequency' => !empty( $payload[ 'frequency' ] ) ? $payload[ 'frequency' ] : '',
        );

        update_option( 'rex_feed_review_request', $data );

        return array(
            'success' => true,
        );
    }


    /**
     * Update into database - New Changes Message
     *
     * @return bool[]
     */
    public static function new_ui_changes_message() {
        update_option( 'rex_feed_new_changes_msg', 'hide' );

        return array(
            'success' => true,
        );
    }


    /**
     * Loads product taxonomies
     *
     * @param array $payload Payload.
     *
     * @return bool[]
     */
    public static function load_taxonomies( $payload ) {
        ob_start();
        $feed_id = !empty( $payload[ 'feed_id' ] ) ? (int) $payload[ 'feed_id' ] : null;
        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-product-taxonomies-section.php';
        $html_content = ob_get_contents();
        ob_get_clean();

        return array(
            'success'      => true,
            'html_content' => $html_content,
        );
    }


    /**
     * Checks if there's any required attribute missing in Google Shopping Feed
     *
     * @return void
     */
    public static function check_for_missing_attributes() {
        $nonce = !empty( $_POST[ 'security' ] ) ? htmlspecialchars( trim( $_POST[ 'security' ] ) ) : null; // phpcs:ignore

        if ( wp_verify_nonce( $nonce, 'rex-wpfm-ajax' ) ) {
            $feed_config = array();
            $config      = !empty( $_POST[ 'payload' ][ 'feed_config' ] ) ? $_POST[ 'payload' ][ 'feed_config' ] : ''; // phpcs:ignore
            parse_str( $config, $feed_config );

            $feed_config = function_exists( 'rex_feed_get_sanitized_get_post' ) ? rex_feed_get_sanitized_get_post( $feed_config ) : array();
            $feed_config = !empty( $feed_config[ 'fc' ] ) ? $feed_config[ 'fc' ] : '';
            $feed_attr   = array();

            if ( is_array( $feed_config ) ) {
                $feed_config = filter_var_array( $feed_config, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                array_shift( $feed_config );
                $feed_attr = is_array( $feed_config ) && !empty( $feed_config ) ? array_column( $feed_config, 'attr' ) : [];
            }

            $required_attr = array( 'id', 'title', 'description', 'link', 'image_link', 'availability', 'price', 'brand', 'gtin', 'mpn' );
            $labels        = array(
                'id'           => 'Product Id [id]',
                'title'        => 'Product Title [title]',
                'description'  => 'Product Description [description]',
                'link'         => 'Product URL [link]',
                'image_link'   => 'Main Image [image_link]',
                'availability' => 'Stock Status [availability]',
                'price'        => 'Regular Price [price]',
                'brand'        => 'Manufacturer [brand]',
                'gtin'         => 'GTIN [gtin]',
                'mpn'          => 'MPN [mpn]',
            );

            wp_send_json_success(
                array(
                    'feed_attr'   => $feed_attr,
                    'feed_config' => $feed_config,
                    'req_attr'    => $required_attr,
                    'labels'      => $labels,
                ),
            );
        }
        wp_send_json_error(
            array(
                'feed_attr'   => '',
                'feed_config' => '',
                'req_attr'    => '',
                'labels'      => '',
            ),
        );
    }


    /**
     * Get Appsero options
     *
     * @param array $payload Payload.
     */
    public static function get_appsero_options( $payload ) {
        $nonce = !empty( $payload[ 'security' ] ) ? $payload[ 'security' ] : null;
        $html  = '';

        if ( wp_verify_nonce( $nonce, 'rex-wpfm-ajax' ) ) {
            ob_start();
            ?>
            <li data-placeholder="Which plugin?">
                <label>
                    <input type="radio" name="selected-reason" value="found-better-plugin">
                    <div class="wd-de-reason-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23">
                            <g fill="none">
                                <g fill="#3B86FF">
                                    <path d="M17.1 14L22.4 19.3C23.2 20.2 23.2 21.5 22.4 22.4 21.5 23.2 20.2 23.2 19.3 22.4L19.3 22.4 14 17.1C15.3 16.3 16.3 15.3 17.1 14L17.1 14ZM8.6 0C13.4 0 17.3 3.9 17.3 8.6 17.3 13.4 13.4 17.2 8.6 17.2 3.9 17.2 0 13.4 0 8.6 0 3.9 3.9 0 8.6 0ZM8.6 2.2C5.1 2.2 2.2 5.1 2.2 8.6 2.2 12.2 5.1 15.1 8.6 15.1 12.2 15.1 15.1 12.2 15.1 8.6 15.1 5.1 12.2 2.2 8.6 2.2ZM8.6 3.6L8.6 5C6.6 5 5 6.6 5 8.6L5 8.6 3.6 8.6C3.6 5.9 5.9 3.6 8.6 3.6L8.6 3.6Z"></path>
                                </g>
                            </g>
                        </svg>
                    </div>
                    <div class="wd-de-reason-text">Found a better plugin</div>
                </label>
            </li>
            <li data-placeholder="How many products do you have in you store?">
                <label>
                    <input type="radio" name="selected-reason" value="product-limit">
                    <div class="wd-de-reason-icon">
                        <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd"
                             clip-rule="evenodd" fill="#3B86FF">
                            <path d="M11.5 23l-8.5-4.535v-3.953l5.4 3.122 3.1-3.406v8.772zm1-.001v-8.806l3.162 3.343 5.338-2.958v3.887l-8.5 4.534zm-10.339-10.125l-2.161-1.244 3-3.302-3-2.823 8.718-4.505 3.215 2.385 3.325-2.385 8.742 4.561-2.995 2.771 2.995 3.443-2.242 1.241v-.001l-5.903 3.27-3.348-3.541 7.416-3.962-7.922-4.372-7.923 4.372 7.422 3.937v.024l-3.297 3.622-5.203-3.008-.16-.092-.679-.393v.002z"/>
                        </svg>
                    </div>
                    <div class="wd-de-reason-text">Product limit</div>
                </label>
            </li>
            <li data-placeholder="Would you like us to assist you?">
                <label>
                    <input type="radio" name="selected-reason" value="could-not-understand">
                    <div class="wd-de-reason-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23">
                            <g fill="none">
                                <g fill="#3B86FF">
                                    <path d="M11.5 0C17.9 0 23 5.1 23 11.5 23 17.9 17.9 23 11.5 23 10.6 23 9.6 22.9 8.8 22.7L8.8 22.6C9.3 22.5 9.7 22.3 10 21.9 10.3 21.6 10.4 21.3 10.4 20.9 10.8 21 11.1 21 11.5 21 16.7 21 21 16.7 21 11.5 21 6.3 16.7 2 11.5 2 6.3 2 2 6.3 2 11.5 2 13 2.3 14.3 2.9 15.6 2.7 16 2.4 16.3 2.2 16.8L2.1 17.1 2.1 17.3C2 17.5 2 17.7 2 18 0.7 16.1 0 13.9 0 11.5 0 5.1 5.1 0 11.5 0ZM6 13.6C6 13.7 6.1 13.8 6.1 13.9 6.3 14.5 6.2 15.7 6.1 16.4 6.1 16.6 6 16.9 6 17.1 6 17.1 6.1 17.1 6.1 17.1 7.1 16.9 8.2 16 9.3 15.5 9.8 15.2 10.4 15 10.9 15 11.2 15 11.4 15 11.6 15.2 11.9 15.4 12.1 16 11.6 16.4 11.5 16.5 11.3 16.6 11.1 16.7 10.5 17 9.9 17.4 9.3 17.7 9 17.9 9 18.1 9.1 18.5 9.2 18.9 9.3 19.4 9.3 19.8 9.4 20.3 9.3 20.8 9 21.2 8.8 21.5 8.5 21.6 8.1 21.7 7.9 21.8 7.6 21.9 7.3 21.9L6.5 22C6.3 22 6 21.9 5.8 21.9 5 21.8 4.4 21.5 3.9 20.9 3.3 20.4 3.1 19.6 3 18.8L3 18.5C3 18.2 3 17.9 3.1 17.7L3.1 17.6C3.2 17.1 3.5 16.7 3.7 16.3 4 15.9 4.2 15.4 4.3 15 4.4 14.6 4.4 14.5 4.6 14.2 4.6 13.9 4.7 13.7 4.9 13.6 5.2 13.2 5.7 13.2 6 13.6ZM11.7 11.2C13.1 11.2 14.3 11.7 15.2 12.9 15.3 13 15.4 13.1 15.4 13.2 15.4 13.4 15.3 13.8 15.2 13.8 15 13.9 14.9 13.8 14.8 13.7 14.6 13.5 14.4 13.2 14.1 13.1 13.5 12.6 12.8 12.3 12 12.2 10.7 12.1 9.5 12.3 8.4 12.8 8.3 12.8 8.2 12.8 8.1 12.8 7.9 12.8 7.8 12.4 7.8 12.2 7.7 12.1 7.8 11.9 8 11.8 8.4 11.7 8.8 11.5 9.2 11.4 10 11.2 10.9 11.1 11.7 11.2ZM16.3 5.9C17.3 5.9 18 6.6 18 7.6 18 8.5 17.3 9.3 16.3 9.3 15.4 9.3 14.7 8.5 14.7 7.6 14.7 6.6 15.4 5.9 16.3 5.9ZM8.3 5C9.2 5 9.9 5.8 9.9 6.7 9.9 7.7 9.2 8.4 8.2 8.4 7.3 8.4 6.6 7.7 6.6 6.7 6.6 5.8 7.3 5 8.3 5Z"></path>
                                </g>
                            </g>
                        </svg>
                    </div>
                    <div class="wd-de-reason-text">Couldn't understand</div>
                </label>
            </li>
            <li data-placeholder="Could you tell us more about that feature?">
                <label>
                    <input type="radio" name="selected-reason" value="not-have-that-feature">
                    <div class="wd-de-reason-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="17" viewBox="0 0 24 17">
                            <g fill="none">
                                <g fill="#3B86FF">
                                    <path d="M19.4 0C19.7 0.6 19.8 1.3 19.8 2 19.8 3.2 19.4 4.4 18.5 5.3 17.6 6.2 16.5 6.7 15.2 6.7 15.2 6.7 15.2 6.7 15.2 6.7 14 6.7 12.9 6.2 12 5.3 11.2 4.4 10.7 3.3 10.7 2 10.7 1.3 10.8 0.6 11.1 0L7.6 0 7 0 6.5 0 6.5 5.7C6.3 5.6 5.9 5.3 5.6 5.1 5 4.6 4.3 4.3 3.5 4.3 3.5 4.3 3.5 4.3 3.4 4.3 1.6 4.4 0 5.9 0 7.9 0 8.6 0.2 9.2 0.5 9.7 1.1 10.8 2.2 11.5 3.5 11.5 4.3 11.5 5 11.2 5.6 10.8 6 10.5 6.3 10.3 6.5 10.2L6.5 10.2 6.5 17 6.5 17 7 17 7.6 17 22.5 17C23.3 17 24 16.3 24 15.5L24 0 19.4 0Z"></path>
                                </g>
                            </g>
                        </svg>
                    </div>
                    <div class="wd-de-reason-text">Missing a specific feature</div>
                </label>
            </li>
            <li data-placeholder="Could you tell us a bit more?">
                <label>
                    <input type="radio" name="selected-reason" value="bugs">
                    <div class="wd-de-reason-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 20 20">
                            <g fill="none">
                                <g fill="#3B86FF">
                                    <path d="M4.355.522a.5.5 0 0 1 .623.333l.291.956A4.979 4.979 0 0 1 8 1c1.007 0 1.946.298 2.731.811l.29-.956a.5.5 0 1 1 .957.29l-.41 1.352A4.985 4.985 0 0 1 13 6h.5a.5.5 0 0 0 .5-.5V5a.5.5 0 0 1 1 0v.5A1.5 1.5 0 0 1 13.5 7H13v1h1.5a.5.5 0 0 1 0 1H13v1h.5a1.5 1.5 0 0 1 1.5 1.5v.5a.5.5 0 1 1-1 0v-.5a.5.5 0 0 0-.5-.5H13a5 5 0 0 1-10 0h-.5a.5.5 0 0 0-.5.5v.5a.5.5 0 1 1-1 0v-.5A1.5 1.5 0 0 1 2.5 10H3V9H1.5a.5.5 0 0 1 0-1H3V7h-.5A1.5 1.5 0 0 1 1 5.5V5a.5.5 0 0 1 1 0v.5a.5.5 0 0 0 .5.5H3c0-1.364.547-2.601 1.432-3.503l-.41-1.352a.5.5 0 0 1 .333-.623zM4 7v4a4 4 0 0 0 3.5 3.97V7H4zm4.5 0v7.97A4 4 0 0 0 12 11V7H8.5zM12 6a3.989 3.989 0 0 0-1.334-2.982A3.983 3.983 0 0 0 8 2a3.983 3.983 0 0 0-2.667 1.018A3.989 3.989 0 0 0 4 6h8z"/>
                                </g>
                            </g>
                        </svg>
                    </div>
                    <div class="wd-de-reason-text">Bugs</div>
                </label>
            </li>
            <?php
            $html = ob_get_clean();
            wp_send_json_success( array( 'html' => $html ) );
        }
        wp_send_json_error( array( 'html' => $html ) );
    }


    /**
     * Save WPFM Custom meta field values to show in the front view
     *
     * @param array $payload Payload.
     */
    public static function save_custom_fields_data( $payload ) {
        $nonce = !empty( $payload[ 'security' ] ) ? $payload[ 'security' ] : null;

        if ( wp_verify_nonce( $nonce, 'rex-wpfm-ajax' ) ) {
            $fields_value = !empty( $payload[ 'fields_value' ] ) ? $payload[ 'fields_value' ] : array();

            if ( !empty( $fields_value ) ) {
                update_option( 'wpfm_product_custom_fields_frontend', $fields_value );
            }
            else {
                delete_option( 'wpfm_product_custom_fields_frontend' );
            }
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    /**
     * Update plugin removal option data
     *
     * @param array $payload Payload.
     *
     * @return void
     */
    public static function remove_plugin_data( $payload ) {
        if ( isset( $payload[ 'wpfm_remove_plugin_data' ] ) ) {
            update_option( 'wpfm_remove_plugin_data', $payload[ 'wpfm_remove_plugin_data' ] );
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    /**
     * Get custom filters content
     *
     * @param array $payload Payload.
     *
     * @return array
     * @since 7.2.5
     */
    public static function rex_feed_get_custom_filters_content( $payload ) {
        $status = 'click' !== $payload[ 'event' ] ? get_post_meta( $payload[ 'feed_id' ], '_rex_feed_custom_filter_option', true ) : 'added';
        if( 'added' !== $status ) {
            return [ 'status' => false, 'markups' => '' ];
        }

        if ( !empty( $payload[ 'feed_id' ] ) ) {
            $prev_product_filter_option = get_post_meta( $payload[ 'feed_id' ], '_rex_feed_products', true ) ?: get_post_meta( $payload[ 'feed_id' ], 'rex_feed_products', true );
            if ( 'filter' === $prev_product_filter_option ) {
                update_post_meta( $payload[ 'feed_id' ], '_rex_feed_products', 'all' );
            }
        }

        $feed_filter = get_post_meta( $payload[ 'feed_id' ], '_rex_feed_feed_config_filter', true ) ?: get_post_meta( $payload[ 'feed_id' ], 'rex_feed_feed_config_filter', true );
        $feed_filter = new Rex_Product_Filter( $feed_filter );
        ob_start();
        include_once plugin_dir_path(__FILE__) . '/partials/rex-product-feed-feed-filters-body.php';
        $markups = ob_get_contents();
        ob_end_clean();
        return [ 'status' => true, 'markups' => $markups ];
    }


    /**
     * Save option value to show/hide character
     * limit field in the field mapping table
     *
     * @param int|string $opt_val Payload.
     *
     * @return void
     * @since 7.2.18
     */
    public static function save_char_limit_option( $opt_val ) {
        if ( $opt_val ) {
            update_option( 'rex_feed_hide_character_limit_field', $opt_val );
            wp_send_json_success();
        }
        wp_send_json_error();
        wp_die();
    }

    /**
     * Delete publish button id on page load
     *
     * @param int|string $feed_id Feed id.
     *
     * @return void
     * @since 7.2.18
     */
    public static function delete_publish_btn_id( $feed_id ) {
        if ( $feed_id ) {
            delete_post_meta( $feed_id, '_rex_feed_publish_btn' );
            delete_post_meta( $feed_id, 'rex_feed_publish_btn' );
        }
        wp_send_json_success();
        wp_die();
    }


    /**
     * Get the plugin global option status
     * for hiding character limit column
     *
     * @return void
     * @since 7.2.18
     */
    public static function hide_char_limit_col() {
        wp_send_json( array( 'hide_char' => get_option( 'rex_feed_hide_character_limit_field', 'on' ) ) );
    }


    /**
     * Get abandoned child list
     * and save them in database option table
     *
     * @return string[]
     * @since 7.2.20
     */
    public static function update_abandoned_child_list() {
        $abandoned_childs = wpfm_get_abandoned_child();
        if ( !is_wp_error( $abandoned_childs ) && is_array( $abandoned_childs ) ) {
            update_option( 'rex_feed_abandoned_child_list', $abandoned_childs );
        }
        if ( is_wp_error( $abandoned_childs ) ) {
            return array( 'status' => 'error' );
        }
        return array( 'status' => 'success' );
    }

    /**
     * @desc Schedule single feed processing in the background
     * on clicking `Update` button in all feed page
     * @param int $feed_id
     * @return void
     * @since 7.3.0
     */
    public static function update_single_feed( int $feed_id ) {
        if( $feed_id ) {
            $schedule = new Rex_Feed_Scheduler();
            $schedule->schedule_merchant_single_batch_object( [ $feed_id ], true );
            wp_send_json_success( [ 'status' => 'success' ] );
        }
        wp_send_json_error( [ 'status' => 'failed' ] );
        wp_die();
    }

    /**
     * Saves the filters data for a specific feed.
     *
     * This function takes a payload array containing the feed ID and feed data, and
     * saves the filter drawer data for that feed. It first checks if the feed ID and
     * feed data are empty, and if so, it returns an array with a 'status' value of false.
     * The function then parses the feed data using wp_parse_str() and extracts the filter
     * data using the get_filter_drawer_data() function from the Rex_Product_Feed_Data_Handle class.
     * If there is filter data available, it is saved using the save_filter_drawer_data()
     * function from the same class. After saving the filter data, it triggers the
     * 'rex_feed_after_feed_config_saved' action with the feed ID and feed data as parameters.
     * Finally, it returns an array with a 'status' value of true.
     *
     * @param array $payload The payload array containing the feed ID and feed data.
     * @return array An array with a 'status' value indicating the success of the operation.
     * @since 7.3.1
     */
    public static function save_filters_data( $payload ) {
        if( empty( $payload[ 'feed_id' ] ) && empty( $payload[ 'feed_data' ] ) ) {
            return [ 'status' => false ];
        }
        wp_parse_str( $payload[ 'feed_data' ], $feed_data );

        $filter_data = Rex_Product_Feed_Data_Handle::get_filter_drawer_data( $feed_data );

        if( !empty( $filter_data ) ) {
            Rex_Product_Feed_Data_Handle::save_filter_drawer_data( $payload[ 'feed_id' ], $filter_data );
        }

        /**
         * Fires after saving filters drawer data
         *
         * @param string|int $payload[ 'feed_id' ] Feed id.
         * @param array $feed_data Feed configurations.
         *
         * @since 7.3.1
         */
        do_action( 'rex_feed_after_feed_config_saved', $payload[ 'feed_id' ], $feed_data );

        return [ 'status' => true ];
    }

    /**
     * Saves the settings data for a specific feed.
     *
     * This function takes a payload array containing the feed ID and feed data, and
     * saves the settings drawer data for that feed. It first checks if the feed ID and
     * feed data are empty, and if so, it returns an array with a 'status' value of false.
     * The function then parses the feed data using wp_parse_str() and extracts the settings
     * data using the get_settings_drawer_data() function from the Rex_Product_Feed_Data_Handle class.
     * If there is settings data available, it is saved using the save_settings_drawer_data()
     * function from the same class. After saving the settings data, it triggers the
     * 'rex_feed_after_feed_config_saved' action with the feed ID and feed data as parameters.
     * Finally, it returns an array with a 'status' value of true.
     *
     * @param array $payload The payload array containing the feed ID and feed data.
     * @return array An array with a 'status' value indicating the success of the operation.
     * @since 7.3.1
     */
    public static function save_settings_data( $payload ) {
        if( empty( $payload[ 'feed_id' ] ) && empty( $payload[ 'feed_data' ] ) ) {
            return [ 'status' => false ];
        }
        wp_parse_str( $payload[ 'feed_data' ], $feed_data );

        $settings_data = Rex_Product_Feed_Data_Handle::get_settings_drawer_data( $feed_data );

        if( !empty( $settings_data ) ) {
            Rex_Product_Feed_Data_Handle::save_settings_drawer_data( $payload[ 'feed_id' ], $settings_data );
        }

        /**
         * Fires after saving settings drawer data
         *
         * @param string|int $payload[ 'feed_id' ] Feed id.
         * @param array $feed_data Feed configurations.
         *
         * @since 7.3.1
         */
        do_action( 'rex_feed_after_feed_config_saved', $payload[ 'feed_id' ], $feed_data );

        return [ 'status' => true ];
    }

    /**
     * Checks if the filter data has changed between the previous data and the latest data.
     *
     * @param array $payload The payload containing the previous data and the latest data.
     *                       Format: ['prev_data' => string, 'latest_data' => string]
     * @return array Returns an array with the 'status' indicating whether the filter data has changed or not.
     *               Format: ['status' => bool]
     * @since 7.3.1
     */
    public static function is_filter_changed( $payload ) {
        if( empty( $payload[ 'prev_data' ] ) && empty( $payload[ 'latest_data' ] ) ) {
            return [ 'status' => true ];
        }

        wp_parse_str( $payload[ 'prev_data' ], $prev_data );
        wp_parse_str( $payload[ 'latest_data' ], $latest_data );

        $prev_filter_data   = Rex_Product_Feed_Data_Handle::get_filter_drawer_data( $prev_data );
        $latest_filter_data = Rex_Product_Feed_Data_Handle::get_filter_drawer_data( $latest_data );

        return [ 'status' => $prev_filter_data !== $latest_filter_data ];
    }

    /**
     * Checks if the settings data has changed between the previous data and the latest data.
     *
     * @param array $payload The payload containing the previous data and the latest data.
     *                       Format: ['prev_data' => string, 'latest_data' => string]
     * @return array Returns an array with the 'status' indicating whether the settings data has changed or not.
     *               Format: ['status' => bool]
     * @since 7.3.1
     */
    public static function is_settings_changed( $payload ) {
        if( empty( $payload[ 'prev_data' ] ) && empty( $payload[ 'latest_data' ] ) ) {
            return [ 'status' => true ];
        }

        wp_parse_str( $payload[ 'prev_data' ], $prev_data );
        wp_parse_str( $payload[ 'latest_data' ], $latest_data );

        $prev_settings_data   = Rex_Product_Feed_Data_Handle::get_settings_drawer_data( $prev_data );
        $latest_settings_data = Rex_Product_Feed_Data_Handle::get_settings_drawer_data( $latest_data );

        return [ 'status' => $prev_settings_data !== $latest_settings_data ];
    }

    /**
     * Creates a contact using the provided name and email.
     *
     * This function verifies a nonce for security, then extracts the name and email
     * from the POST request. It then creates a new contact instance and sends it via webhook.
     *
     * @since 4.7.14
     * @return void
     */
    public function create_contact() {
        $nonce = filter_input(INPUT_POST, 'security', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ( !wp_verify_nonce( $nonce, 'rex-wpfm-ajax' ) ) {
            wp_send_json_error( array( 'message' => __('Unauthorized request', 'rex-product-feed') ), 400 );
            return;
        }

        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $name = !empty( $name) ? $name  : '';


        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $email = !empty($email) ? $email : '';

        if ( empty( $email ) ) {
            wp_send_json_error( array( 'message' => __('Email is required', 'rex-product-feed') ), 400 );
        }elseif(!is_email( $_POST['email'])){
            wp_send_json_error( array( 'message' => __('Email is invalid', 'rex-product-feed') ), 400 );
        }

        $create_contact_instance = new Rex_Product_Feed_Create_Contact( $email, $name );

        $response = $create_contact_instance->create_contact_via_webhook();

        if ( $response ) {
            wp_send_json_success( array( 'message' => __('Contact created successfully', 'rex-product-feed') ), 200 );
        } else {
            wp_send_json_error( array( 'message' => __('Failed to create contact', 'rex-product-feed') ), 500 );
        }
    }

	/**
	 * Fetches Google Merchant Center (GMC) report data based on provided payload parameters.
	 *
	 * @param array $payload An array containing parameters like pageToken, maxResult, and feed_id.
	 *
	 * @return void Sends a JSON response with the GMC report data and related markups or an error if no data is available.
	 * @since 7.4.20
	 */
	public static function fetch_gmc_report( $payload ) {
		$page_token          = $payload[ 'pageToken' ] ?? null;
		$max_result          = $payload[ 'maxResult' ] ?? 10;
		$feed_id             = $payload[ 'feed_id' ] ?? null;

		$rex_google_api   = new Rex_Feed_Google_Shopping_Api();
		$product_status_data = $rex_google_api->get_product_detailed_stats( $page_token, $max_result );
		if ( !empty( $product_status_data ) ) {
			$markups = $rex_google_api->build_product_status_table_data( $product_status_data, $feed_id );
			wp_send_json_success( [
				'report'  => $product_status_data,
				'markups' => $markups,
			] );
		}
		wp_send_json_error( $product_status_data );
	}

	/**
	 * Saves Google API credentials.
	 *
	 * This function updates the options for Google API credentials, including client ID, client secret, and merchant ID.
	 * It sends a JSON success response after updating the options.
	 *
	 * @param array $payload The payload array containing the Google API credentials.
	 * @return void
     *
     * @since 7.4.20
	 */
    public static function save_google_api_credentials( $payload ) {
	    if ( isset( $payload[ 'client_id' ] ) ) {
		    update_option( 'rex_google_client_id', $payload[ 'client_id' ] );
	    }
	    if ( isset( $payload[ 'client_secret' ] ) ) {
		    update_option( 'rex_google_client_secret', $payload[ 'client_secret' ] );
	    }
	    if ( isset( $payload[ 'merchant_id' ] ) ) {
		    update_option( 'rex_google_merchant_id', $payload[ 'merchant_id' ] );
	    }
        wp_send_json_success();
    }
}