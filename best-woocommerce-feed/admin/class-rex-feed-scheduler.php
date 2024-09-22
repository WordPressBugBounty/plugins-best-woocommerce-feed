<?php
/**
 * Class Rex_Feed_Scheduler
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    /admin/
 * @author     RexTheme <info@rextheme.com>
 */

/**
 *
 * This class is responsible for all the background process functionalities
 *
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Scheduler {

    /**
     * Deregister previous cron schedules with core WP_CRON
     *
     * @return void
     * @since 7.3.0
     */
    private function deregister_wp_cron_schedules() {
        if( wp_next_scheduled( 'rex_feed_schedule_update' ) || wp_next_scheduled( 'rex_feed_daily_update' ) || wp_next_scheduled( 'rex_feed_weekly_update' ) ) {
            $this->remove_processing_feeds_queue();
            wp_clear_scheduled_hook( 'rex_feed_schedule_update' );
            wp_clear_scheduled_hook( 'rex_feed_daily_update' );
            wp_clear_scheduled_hook( 'rex_feed_weekly_update' );
        }
    }

    /**
     * Remove the processing feeds queue [after installing action scheduler version]
     * that was generated with wp-cron and update feed status as `completed`
     *
     * @return void
     * @since 7.3.0
     */
    private function remove_processing_feeds_queue() {
        global $wpdb;

        delete_option( 'rex_wpfm_feed_queue' );

        try {
            $wpdb->delete(
                $wpdb->postmeta,
                [ 'meta_key' => 'rex_feed_status' ],
            );

            $wpdb->update(
                $wpdb->postmeta,
                [ 'meta_value' => 'completed' ],
                [ 'meta_key' => '_rex_feed_status' ],
            );

            $find_key_1 = $wpdb->esc_like( 'wp_rex_product_feed_background_process_batch_' ) . '%';
            $find_key_2 = '%' . $wpdb->esc_like( 'wp_rex_product_feed_background_process_cron' ) . '%';

            $wpdb->query(
                $wpdb->prepare(
                    'DELETE FROM %1s WHERE `option_name` LIKE %s OR `option_name` LIKE %s;',
                    $wpdb->options,
                    $find_key_1,
                    $find_key_2
                )
            );
        }
        catch( Exception $e ) {
            if( is_wpfm_logging_enabled() ) {
                $log = wc_get_logger();
                $log->warning( print_r( $e->getMessage(), 1 ), array( 'source' => 'WPFM_BACKGROUND_PROCESS_ERROR' ) );
            }
        }
    }

    /**
     * Register cron schedules for background feed processing
     *
     * @return void
     * @since 7.3.0
     */
    public function register_background_schedulers() {
        $this->deregister_wp_cron_schedules();

        if( function_exists( 'as_has_scheduled_action' ) && function_exists( 'as_schedule_recurring_action' ) ) {
            $hourly_schedule = as_has_scheduled_action( HOURLY_SCHEDULE_HOOK, null, 'wpfm' );
            if( !$hourly_schedule ) {
                as_schedule_recurring_action( time(), 3600, HOURLY_SCHEDULE_HOOK, [], 'wpfm' );
            }

            $daily_schedule = as_has_scheduled_action( DAILY_SCHEDULE_HOOK, null, 'wpfm' );
            if( !$daily_schedule ) {
                as_schedule_recurring_action( time(), 24 * 3600, DAILY_SCHEDULE_HOOK, [], 'wpfm' );
            }

            $weekly_schedule = as_has_scheduled_action( WEEKLY_SCHEDULE_HOOK, null, 'wpfm' );
            if( !$weekly_schedule ) {
                as_schedule_recurring_action( time(), 7 * 24 * 3600, WEEKLY_SCHEDULE_HOOK, [], 'wpfm' );
            }
        }
    }

    /**
     * Callback function to Hourly Cron Schedule Hook
     *
     * @return void
     * @since 7.3.0
     */
    public function hourly_cron_handler() {
        $feed_ids = $this->get_feeds( 'hourly' );

        if( !is_wp_error( $feed_ids ) && is_array( $feed_ids ) && !empty( $feed_ids ) ) {
            $this->schedule_merchant_single_batch_object( $feed_ids );
        }
    }

    /**
     * Callback function to Daily Cron Schedule Hook
     *
     * @return void
     * @since 7.3.0
     */
    public function daily_cron_handler() {
        $feed_ids = $this->get_feeds( 'daily' );

        if( !is_wp_error( $feed_ids ) && is_array( $feed_ids ) && !empty( $feed_ids ) ) {
            $this->schedule_merchant_single_batch_object( $feed_ids );
        }
    }

    /**
     * Callback function to Weekly Cron Schedule Hook
     *
     * @return void
     * @since 7.3.0
     */
    public function weekly_cron_handler() {
        $feed_ids = $this->get_feeds( 'weekly' );

        if( !is_wp_error( $feed_ids ) && is_array( $feed_ids ) && !empty( $feed_ids ) ) {
            $this->schedule_merchant_single_batch_object( $feed_ids );
        }
    }

    /**
     * Generate single batch scheduled in background
     *
     * @param array $data Feed information.
     * @return void
     */
    public function regenerate_feed_batch( array $data ) {
        if( !is_wp_error( $data ) && !empty( $data ) ) {
            $feed_id       = !empty( $data[ 'feed_id' ] ) ? $data[ 'feed_id' ] : '';
            $current_batch = !empty( $data[ 'current_batch' ] ) ? $data[ 'current_batch' ] : '';
            $total_batches = !empty( $data[ 'total_batches' ] ) ? $data[ 'total_batches' ] : '';
            $per_batch     = !empty( $data[ 'per_batch' ] ) ? $data[ 'per_batch' ] : '';
            $offset        = !empty( $data[ 'offset' ] ) ? $data[ 'offset' ] : '';

            $scheduled_actions = as_get_scheduled_actions( [
                'hook' => 'rex_feed_regenerate_feed_batch',
                'group' => "wpfm-feed-{$feed_id}",
                'status' => ActionScheduler_Store::STATUS_PENDING
            ] );

            if( !empty( $scheduled_actions ) ) {
                Rex_Product_Feed_Controller::update_feed_status( $feed_id, 'processing' );
            }

            try {
                $payload  = $this->get_feed_settings_payload( $feed_id, $current_batch, $total_batches, $per_batch, $offset );
                $merchant = Rex_Product_Feed_Factory::build( $payload, true );
                $merchant->make_feed();

                if( empty( $scheduled_actions ) ) {
                    Rex_Product_Feed_Controller::update_feed_status( $feed_id, 'completed' );
                }
            }
            catch( Exception $e ) {
                if( is_wpfm_logging_enabled() ) {
                    $log = wc_get_logger();
                    $log->warning( print_r( $e->getMessage(), 1 ), array( 'source' => 'WPFM_BACKGROUND_PROCESS_ERROR' ) );
                }
            }
        }
        else {
            if( is_wpfm_logging_enabled() ) {
                $log = wc_get_logger();
                $log->warning( 'Invalid data!', array( 'source' => 'WPFM_BACKGROUND_PROCESS_ERROR' ) );
                $log->warning( print_r( $data, 1 ), array( 'source' => 'WPFM_BACKGROUND_PROCESS_ERROR' ) );
            }
        }
    }

    /**
     * Register background scheduler for updating WC abandoned child list
     *
     * @return void
     */
    public function register_wc_abandoned_child_update_scheduler() {
        if( function_exists( 'as_has_scheduled_action' ) && function_exists( 'as_schedule_single_action' ) ) {
            $wc_scheduler = as_has_scheduled_action( WC_SINGLE_SCHEDULER, null, 'wpfm' );
            if( !$wc_scheduler ) {
                as_schedule_single_action( time(), WC_SINGLE_SCHEDULER, [], 'wpfm' );
            }
        }
    }

    /**
     * Update WC abandoned child list in option table
     *
     * @return void
     */
    public function update_wc_abandoned_child_list() {
        Rex_Product_Feed_Ajax::update_abandoned_child_list();
    }

    /**
     * Configure feed merchant in single batch wise
     * and schedule as a single process
     *
     * @param array $feed_ids Feed ids that need to be updated.
     * @param bool $update_single If only a single feed needs to be updated only.
     *
     * @return void
     * @throws Exception
     */
    public function schedule_merchant_single_batch_object( $feed_ids, $update_single = false ) {
        if( !is_wp_error( $feed_ids ) && !empty( $feed_ids ) ) {
            $products_info = wpfm_get_cached_data( 'cron_products_info' );
            if( is_wp_error( $products_info ) || !is_array( $products_info ) || empty( $products_info ) ) {
                try {
                    $products_info = Rex_Product_Feed_Ajax::get_product_number( [ 'feed_id' => '' ] );
                }
                catch( Exception $e ) {
                    $products_info = [];
                    if( is_wpfm_logging_enabled() ) {
                        $log = wc_get_logger();
                        $log->warning( print_r( $e->getMessage(), 1 ), array( 'source' => 'WPFM_BACKGROUND_PROCESS_ERROR' ) );
                    }
                }
                wpfm_set_cached_data( 'cron_products_info', $products_info );
            }

            $per_batch     = !empty( $products_info[ 'per_batch' ] ) ? $products_info[ 'per_batch' ] : 0;
            $total_batches = !empty( $products_info[ 'total_batch' ] ) ? $products_info[ 'total_batch' ] : 1;

            if( $per_batch && $total_batches ) {
                foreach( $feed_ids as $feed_id ) {
                    $update_on_product_change = get_post_meta( $feed_id, '_rex_feed_update_on_product_change', true ) ?: get_post_meta( $feed_id, 'rex_feed_update_on_product_change', true );
                    if( $update_single || ( 'yes' === $update_on_product_change && get_option( 'rex_feed_wc_product_updated', false ) ) || ( !$update_on_product_change || 'no' === $update_on_product_change ) ) {
                        $is_custom_executable = '';
                        if( !$update_single ) {
                            $schedule             = $this->get_feed_schedule_settings( $feed_id );
                            $schedule_time        = get_post_meta( $feed_id, '_rex_feed_custom_time', true ) ?: get_post_meta( $feed_id, 'rex_feed_custom_time', true );
                            $timezone             = new DateTimeZone( wp_timezone_string() );
                            $now_time             = wp_date( "H", null, $timezone );
                            $is_custom_executable = 'custom' === $schedule && '' !== $schedule_time && $schedule_time == $now_time;
                        }

                        if( $update_single || $is_custom_executable || in_array( $schedule, [ 'hourly', 'daily', 'weekly' ] ) ) {
                            $offset = 0;
                            for( $current_batch = 1; $current_batch <= $total_batches; $current_batch++ ) {
                                $data         = [];
                                $data[]       = [
                                    'feed_id'       => $feed_id,
                                    'current_batch' => $current_batch,
                                    'total_batches' => $total_batches,
                                    'per_batch'     => $per_batch,
                                    'offset'        => $offset,
                                ];
                                $is_scheduled = function_exists( 'as_has_scheduled_action' ) && as_has_scheduled_action( 'rex_feed_regenerate_feed_batch', $data, 'wpfm-feed-' . $feed_id );
                                if( !$is_scheduled ) {
                                    $scheduled = function_exists( 'as_schedule_single_action' ) && as_schedule_single_action( time(), 'rex_feed_regenerate_feed_batch', $data, 'wpfm-feed-' . $feed_id );
                                    if( 1 === $current_batch && !is_wp_error( $scheduled ) && $scheduled ) {
                                        Rex_Product_Feed_Controller::update_feed_status( $feed_id, 'In queue' );
                                    }
                                }
                                $offset += $per_batch;
                            }
                        }
                    }
                }
            }
            wpfm_purge_cached_data( 'cron_products_info' );
        }
    }

    /**
     * Get all scheduled feed ids
     *
     * @param string $schedule Schedule of the feed(s).
     *
     * @return int[]|WP_Post[]
     * @since 7.3.0
     */
    public function get_feeds( $schedule ) {
        $status = [ 'canceled', 'completed' ];

        $meta_queries = [
            [
                [
                    'key'   => '_rex_feed_schedule',
                    'value' => $schedule,
                ],
                [
                    'key'   => 'rex_feed_schedule',
                    'value' => $schedule,
                ],
                'relation' => 'OR'
            ],
            [
                [
                    'key'   => '_rex_feed_status',
                    'value' => $status,
                ],
                [
                    'key'   => 'rex_feed_status',
                    'value' => $status,
                ],
                'relation' => 'OR'
            ],
            'relation' => 'AND'
        ];

        if( 'hourly' === $schedule && !empty( $meta_queries[ 0 ] ) ) {
            $timezone = new DateTimeZone( wp_timezone_string() );
            $now_time = wp_date( "G", null, $timezone );

            $meta_queries[ 0 ][] = [
                [
                    [
                        'key'   => '_rex_feed_schedule',
                        'value' => 'custom',
                    ],
                    [
                        'key'   => 'rex_feed_schedule',
                        'value' => 'custom',
                    ],
                    'relation' => 'OR'
                ],
                [
                    [
                        'key'   => '_rex_feed_custom_time',
                        'value' => $now_time,
                    ],
                ],
                'relation' => 'AND'
            ];
        }

        $args = [
            'fields'           => 'ids',
            'post_type'        => 'product-feed',
            'post_status'      => 'publish',
            'orderby'          => 'id',
            'order'            => 'ASC',
            'meta_query'       => $meta_queries,
            'suppress_filters' => true,
        ];

        $result = new WP_Query( $args );
        return $result->get_posts();
    }

    /**
     * Generate the feed generation payload
     *
     * @param string|int $feed_id Feed id.
     * @param string|int $current_batch Current batch no.
     * @param string|int $total_batches Total batch count.
     * @param string|int $per_batch Products number need to be fetched per batch.
     * @param string|int $offset Product offset number.
     *
     * @return array
     * @since 7.3.0
     */
    private function get_feed_settings_payload( $feed_id, $current_batch, $total_batches, $per_batch, $offset ) {        $analytics_params  = [];
        $merchant          = get_post_meta( $feed_id, '_rex_feed_merchant', true ) ?: get_post_meta( $feed_id, 'rex_feed_merchant', true );
        $product_condition = get_post_meta( $feed_id, '_rex_feed_product_condition', true ) ?: get_post_meta( $feed_id, 'rex_feed_product_condition', true );
        $feed_config       = get_post_meta( $feed_id, '_rex_feed_feed_config', true ) ?: get_post_meta( $feed_id, 'rex_feed_feed_config', true );
        $analytics         = get_post_meta( $feed_id, '_rex_feed_analytics_params_options', true ) ?: get_post_meta( $feed_id, 'rex_feed_analytics_params_options', true );
        if( 'on' === $analytics || 'yes' === $analytics ) {
            $analytics_params = get_post_meta( $feed_id, '_rex_feed_analytics_params', true ) ?: get_post_meta( $feed_id, 'rex_feed_analytics_params', true );
        }
        $feed_filter                 = get_post_meta( $feed_id, '_rex_feed_feed_config_filter', true ) ?: get_post_meta( $feed_id, 'rex_feed_feed_config_filter', true );
        $product_scope               = get_post_meta( $feed_id, '_rex_feed_products', true ) ?: get_post_meta( $feed_id, 'rex_feed_products', true );
        $include_out_of_stock        = get_post_meta( $feed_id, '_rex_feed_include_out_of_stock', true ) ?: get_post_meta( $feed_id, 'rex_feed_include_out_of_stock', true );
        $include_variations          = get_post_meta( $feed_id, '_rex_feed_variations', true ) ?: get_post_meta( $feed_id, 'rex_feed_variations', true );
        $include_variations          = 'yes' === $include_variations;
        $variable_product            = get_post_meta( $feed_id, '_rex_feed_variable_product', true ) ?: get_post_meta( $feed_id, 'rex_feed_variable_product', true );
        $variable_product            = 'yes' === $variable_product;
        $parent_product              = get_post_meta( $feed_id, '_rex_feed_parent_product', true ) ?: get_post_meta( $feed_id, 'rex_feed_parent_product', true );
        $parent_product              = 'yes' === $parent_product;
        $exclude_hidden_products     = get_post_meta( $feed_id, '_rex_feed_hidden_products', true ) ?: get_post_meta( $feed_id, 'rex_feed_hidden_products', true );
        $exclude_hidden_products     = 'yes' === $exclude_hidden_products;
        $append_variations           = get_post_meta( $feed_id, '_rex_feed_variation_product_name', true ) ?: get_post_meta( $feed_id, 'rex_feed_variation_product_name', true );
        $append_variations           = 'yes' === $append_variations;
        $wpml                        = get_post_meta( $feed_id, '_rex_feed_wpml_language', true ) ?: get_post_meta( $feed_id, 'rex_feed_wpml_language', true );
        $feed_format                 = get_post_meta( $feed_id, '_rex_feed_feed_format', true ) ?: get_post_meta( $feed_id, 'rex_feed_feed_format', true );
        $feed_format                 = $feed_format ?: 'xml';
        $wcml_currency               = get_post_meta( $feed_id, '_rex_feed_wcml_currency', true ) ?: get_post_meta( $feed_id, 'rex_feed_wcml_currency', true );
        $aelia_currency              = get_post_meta( $feed_id, '_rex_feed_aelia_currency', true ) ?: get_post_meta( $feed_id, 'rex_feed_aelia_currency', true );
        $wmc_currency                = get_post_meta( $feed_id, '_rex_feed_wmc_currency', true ) ?: get_post_meta( $feed_id, 'rex_feed_wmc_currency', true );
        $woocs_currency              = get_post_meta( $feed_id, '_rex_feed_woocs_currency', true );
        $skip_product                = get_post_meta( $feed_id, '_rex_feed_skip_product', true ) ?: get_post_meta( $feed_id, 'rex_feed_skip_product', true );
        $skip_product                = 'yes' === $skip_product;
        $skip_row                    = get_post_meta( $feed_id, '_rex_feed_skip_row', true ) ?: get_post_meta( $feed_id, 'rex_feed_skip_row', true );
        $skip_row                    = 'yes' === $skip_row;
        $feed_separator              = get_post_meta( $feed_id, '_rex_feed_separator', true ) ?: get_post_meta( $feed_id, 'rex_feed_separator', true );
        $include_zero_price_products = get_post_meta( $feed_id, '_rex_feed_include_zero_price_products', true ) ?: get_post_meta( $feed_id, 'rex_feed_include_zero_price_products', true );
        $custom_filter_option        = get_post_meta( $feed_id, '_rex_feed_custom_filter_option', true ) ?: get_post_meta( $feed_id, 'rex_feed_custom_filter_option', true );
        $feed_rules_button           = get_post_meta( $feed_id, '_rex_feed_feed_rules_button', true ) ?: get_post_meta( $feed_id, 'rex_feed_feed_rules_button', true );
        $feed_country                = get_post_meta( $feed_id, '_rex_feed_feed_country', true ) ?: get_post_meta( $feed_id, 'rex_feed_feed_country', true );
        $custom_wrapper              = get_post_meta( $feed_id, '_rex_feed_custom_wrapper', true );
        $custom_wrapper_el           = get_post_meta( $feed_id, '_rex_feed_custom_wrapper_el', true );
        $custom_items_wrapper        = get_post_meta( $feed_id, '_rex_feed_custom_items_wrapper', true );
        $custom_xml_header           = get_post_meta( $feed_id, '_rex_feed_custom_xml_header', true );
        $yandex_company_name         = get_post_meta( $feed_id, '_rex_feed_yandex_company_name', true );
        $yandex_old_price            = get_post_meta( $feed_id, '_rex_feed_yandex_old_price', true );
        $hotline_firm_id             = get_post_meta( $feed_id, '_rex_feed_hotline_firm_id', true );
        $hotline_firm_name           = get_post_meta( $feed_id, '_rex_feed_hotline_firm_name', true );
        $hotline_exch_rate           = get_post_meta( $feed_id, '_rex_feed_hotline_exchange_rate', true );
        $yandex_old_price            = 'include' === $yandex_old_price;

        if( apply_filters( 'wpfm_is_premium', false ) ) {
            $feed_rules = get_post_meta( $feed_id, '_rex_feed_feed_config_rules', true ) ?: get_post_meta( $feed_id, 'rex_feed_feed_config_rules', true );
        }
        else {
            $feed_rules = array();
        }

        $terms_array   = array();
        $ignored_scope = array( 'all', 'filter', 'product_filter', 'featured', '' );

        if( !in_array( $product_scope, $ignored_scope ) ) {
            $terms = wp_get_post_terms( $feed_id, $product_scope );
            if( $terms ) {
                foreach( $terms as $term ) {
                    $terms_array[] = $term->slug;
                }
            }
        }

        return array(
            'merchant'                    => $merchant,
            'feed_format'                 => $feed_format,
            'feed_config'                 => $feed_config,
            'append_variations'           => $append_variations,
            'info'                        => array(
                'post_id'        => $feed_id,
                'title'          => get_the_title( $feed_id ),
                'desc'           => get_the_title( $feed_id ),
                'total_batch'    => $total_batches,
                'batch'          => $current_batch,
                'per_page'       => $per_batch,
                'offset'         => $offset,
                'products_scope' => $product_scope,
                'cats'           => $terms_array,
                'tags'           => $terms_array,
            ),
            'feed_filter'                 => $feed_filter,
            'feed_rules'                  => $feed_rules,
            'product_condition'           => $product_condition,
            'include_variations'          => $include_variations,
            'include_out_of_stock'        => $include_out_of_stock,
            'include_zero_price_products' => $include_zero_price_products,
            'variable_product'            => $variable_product,
            'parent_product'              => $parent_product,
            'exclude_hidden_products'     => $exclude_hidden_products,
            'wpml_language'               => $wpml,
            'analytics'                   => $analytics,
            'analytics_params'            => $analytics_params,
            'wcml_currency'               => $wcml_currency,
            'aelia_currency'              => $aelia_currency,
            'wmc_currency'                => $wmc_currency,
            'woocs_currency'              => $woocs_currency,
            'skip_product'                => $skip_product,
            'skip_row'                    => $skip_row,
            'feed_separator'              => $feed_separator,
            'custom_filter_option'        => $custom_filter_option,
            'feed_country'                => $feed_country,
            'custom_wrapper'              => $custom_wrapper,
            'custom_wrapper_el'           => $custom_wrapper_el,
            'custom_items_wrapper'        => $custom_items_wrapper,
            'custom_xml_header'           => $custom_xml_header,
            'yandex_company_name'         => $yandex_company_name,
            'yandex_old_price '           => $yandex_old_price,
            'hotline_firm_id'             => $hotline_firm_id,
            'hotline_firm_name'           => $hotline_firm_name,
            'hotline_exch_rate'           => $hotline_exch_rate,
            'feed_rules_button'           => $feed_rules_button
        );
    }

    /**
     * Update [for previous meta key] and get feed schedule
     *
     * @param string|int $feed_id Feed id.
     *
     * @return string|bool
     * @since 7.2.18
     */
    private function get_feed_schedule_settings( $feed_id ) {
        $feed_schedule = get_post_meta( $feed_id, '_rex_feed_schedule', true );
        if( $feed_schedule ) {
            delete_post_meta( $feed_id, 'rex_feed_schedule' );
        }
        else {
            $feed_schedule = get_post_meta( $feed_id, 'rex_feed_schedule', true );
            if( $feed_schedule ) {
                update_post_meta( $feed_id, '_rex_feed_schedule', $feed_schedule );
                delete_post_meta( $feed_id, 'rex_feed_schedule' );
            }
        }
        return $feed_schedule;
    }
}