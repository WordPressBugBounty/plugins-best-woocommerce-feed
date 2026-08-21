<?php

use RexFeed\Vendor\Google\ApiCore\ApiException;
use RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\Client\DataSourcesServiceClient;
use RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\CreateDataSourceRequest;
use RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\UpdateDataSourceRequest;
use RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\FetchDataSourceRequest;
use RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\DataSource;
use RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\FileInput;
use RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\FileInput\FetchSettings;
use RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\PrimaryProductDataSource;
use RexFeed\Vendor\Google\Protobuf\FieldMask;
use RexFeed\Vendor\Google\Type\TimeOfDay;

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

        wp_ajax_helper()->handle( 'rexfeed-dispatch-feed-generation' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'dispatch_feed_generation' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'rexfeed-get-feed-generation-status' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'get_feed_generation_status' ) )
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

        // Reset Google merchant credentials.
        wp_ajax_helper()->handle( 'rexfeed-reset-google-credentials' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'reset_google_api_credentials' ) )
                        ->with_validation( $validations );

        // Send to Google Merchant Center.
        wp_ajax_helper()->handle( 'rexfeed-send-to-google' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'send_to_google' ) )
                        ->with_validation( $validations );

        // Fetch Google DataSource (Merchant API v1).
        wp_ajax_helper()->handle( 'rexfeed-fetch-google-datasource' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'fetch_google_datasource' ) )
                        ->with_validation( $validations );

        // Migrate existing Content API feed to Merchant API.
        wp_ajax_helper()->handle( 'rexfeed-migrate-to-merchant-api' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'migrate_to_merchant_api' ) )
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

        wp_ajax_helper()->handle( 'wpfm-cleanup-jobs' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'cleanup_jobs' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'wpfm-save-job-retention' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'save_job_retention' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'wpfm-save-generation-mode' )
                        ->with_callback( array( 'Rex_Product_Feed_Ajax', 'save_generation_mode' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'wpfm-save-feed-error-email' )
                        ->with_callback( array( 'Rex_Feed_Product_Count_Guard', 'save_email_settings' ) )
                        ->with_validation( $validations );

        wp_ajax_helper()->handle( 'wpfm-save-feed-error-admin-notice' )
                        ->with_callback( array( 'Rex_Feed_Product_Count_Guard', 'save_admin_notice_setting' ) )
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

        $btn_id         = !empty( $payload[ 'button_id' ] ) ? $payload[ 'button_id' ] : '';
        $is_premium     = apply_filters( 'wpfm_is_premium', false );
        $eligible_total = self::get_feed_product_count( $payload, $feed_id );

        $limit_notice = false;
        if ( !$is_premium ) {
            $total_products = min( $eligible_total, WPFM_FREE_MAX_PRODUCT_LIMIT );
            $limit_notice   = self::get_product_limit_notice( $eligible_total, false );
        }
        else {
            $total_products = $eligible_total;
        }

        $products = [
            'products' => $total_products,
        ];
        $per_page = get_option( 'rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT );

        if ( (int) $per_page >= WPFM_FREE_MAX_PRODUCT_LIMIT && !$is_premium ) {
            $posts_per_page = WPFM_FREE_MAX_PRODUCT_LIMIT;
        }
        else {
            $posts_per_page = max( 1, (int) $per_page );
        }

        if ( $feed_id && $btn_id ) {
            update_post_meta( $feed_id, '_rex_feed_publish_btn', $btn_id );
        }

        $total_batch = max( 1, (int) ceil( (int) $products[ 'products' ] / $posts_per_page ) );

        $response = [
            'products'    => $products[ 'products' ],
            'per_batch'   => $posts_per_page,
            'total_batch' => $total_batch,
            'feed_title'  => 'unique',
        ];

        if ( $limit_notice ) {
            $response[ 'product_limit' ] = $limit_notice;
        }

        return $response;
    }

    /**
     * Count the products eligible for the submitted feed configuration.
     *
     * @param array      $payload Submitted feed data.
     * @param int|string $feed_id Feed ID.
     *
     * @return int
     */
    private static function get_feed_product_count( $payload, $feed_id ) {
        $feed_config = [];
        if ( !empty( $payload[ 'feed_config' ] ) && is_string( $payload[ 'feed_config' ] ) ) {
            wp_parse_str( $payload[ 'feed_config' ], $feed_config );
        }

        $product_scope = !empty( $feed_config[ 'rex_feed_products' ] )
            ? sanitize_key( $feed_config[ 'rex_feed_products' ] )
            : ( get_post_meta( $feed_id, '_rex_feed_products', true ) ?: get_post_meta( $feed_id, 'rex_feed_products', true ) );
        $product_scope = $product_scope ?: 'all';

        $merchant = !empty( $feed_config[ 'rex_feed_merchant' ] )
            ? sanitize_key( $feed_config[ 'rex_feed_merchant' ] )
            : ( get_post_meta( $feed_id, '_rex_feed_merchant', true ) ?: get_post_meta( $feed_id, 'rex_feed_merchant', true ) );

        $variation_settings = [
            'rex_feed_variations',
            'rex_feed_default_variation',
            'rex_feed_highest_variation',
            'rex_feed_cheapest_variation',
            'rex_feed_first_variation',
            'rex_feed_last_variation',
        ];
        $should_fetch_variations = !$feed_id && empty( $feed_config );
        foreach ( $variation_settings as $setting ) {
            $value = $feed_config[ $setting ] ?? ( get_post_meta( $feed_id, "_{$setting}", true ) ?: get_post_meta( $feed_id, $setting, true ) );
            if ( 'yes' === $value ) {
                $should_fetch_variations = true;
                break;
            }
        }

        $post_types = [ 'product' ];
        if ( apply_filters( 'rexfeed_fetch_variation_products', $should_fetch_variations, $feed_id ) && 'skroutz' !== $merchant ) {
            $post_types[] = 'product_variation';
        }

        $custom_filter_enabled = 'added' === ( $feed_config[ 'rex_feed_custom_filter_option_btn' ] ?? get_post_meta( $feed_id, '_rex_feed_custom_filter_option', true ) );
        $feed_filters          = !empty( $feed_config[ 'ff' ] )
            ? $feed_config[ 'ff' ]
            : get_post_meta( $feed_id, '_rex_feed_feed_config_filter', true );
        $feed_filters          = is_array( $feed_filters ) ? $feed_filters : [];

        if ( $custom_filter_enabled && !empty( $feed_config[ 'ff' ] ) ) {
            reset( $feed_filters );
            unset( $feed_filters[ key( $feed_filters ) ] );
        }

        if ( $custom_filter_enabled && self::custom_filter_excludes_variations( $feed_filters ) ) {
            $post_types = [ 'product' ];
        }

        $post_status = [ 'publish' ];
        if ( 'yes' === get_option( 'wpfm_allow_private', 'no' ) ) {
            $post_status[] = 'private';
        }

        $query_args = [
            'post_type'              => $post_types,
            'post_status'            => $post_status,
            'posts_per_page'         => 1,
            'fields'                 => 'ids',
            'post__in'               => [],
            'post__not_in'           => array_map( 'absint', (array) get_option( 'rex_feed_abandoned_child_list', [] ) ),
            'no_found_rows'          => false,
            'cache_results'          => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'suppress_filters'       => false,
        ];

        self::apply_product_scope_to_count_query( $query_args, $feed_config, $feed_id, $product_scope );
        self::switch_to_feed_language( $feed_config, $feed_id );

        $where_filter = null;
        $join_filter  = null;
        $distinct_filter = static function () {
            return 'DISTINCT';
        };
        add_filter( 'posts_distinct', $distinct_filter );

        if ( $custom_filter_enabled && !empty( $feed_filters ) ) {
            $custom_filter_args = Rex_Product_Filter::get_custom_filter_where_query( $feed_filters );
            if ( !empty( $custom_filter_args[ 'where' ] ) ) {
                $where_filter = static function ( $where ) use ( $custom_filter_args ) {
                    return "{$where} AND ({$custom_filter_args[ 'where' ]}) ";
                };
                $join_filter = static function ( $join ) use ( $custom_filter_args ) {
                    return self::add_custom_filter_count_joins( $join, $custom_filter_args );
                };

                add_filter( 'posts_where', $where_filter );
                add_filter( 'posts_join', $join_filter );
            }
        }

        $query = new WP_Query(
            $query_args
        );

        if ( $where_filter ) {
            remove_filter( 'posts_where', $where_filter );
            remove_filter( 'posts_join', $join_filter );
        }
        remove_filter( 'posts_distinct', $distinct_filter );

        return (int) $query->found_posts;
    }

    /**
     * Check whether custom taxonomy filters require parent-only queries.
     *
     * @param array $feed_filters Feed filter groups.
     *
     * @return bool
     */
    private static function custom_filter_excludes_variations( $feed_filters ) {
        foreach ( $feed_filters as $filters ) {
            if ( !is_array( $filters ) ) {
                continue;
            }
            foreach ( $filters as $filter_key => $filter ) {
                if ( 'cfo' === $filter_key || !is_array( $filter ) ) {
                    continue;
                }
                if ( in_array( $filter[ 'if' ] ?? '', [ 'product_cats', 'product_tags', 'product_brands' ], true ) ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Apply the selected product scope to a count query.
     *
     * @param array      $query_args Query arguments passed by reference.
     * @param array      $feed_config Submitted feed configuration.
     * @param int|string $feed_id Feed ID.
     * @param string     $product_scope Selected product scope.
     *
     * @return void
     */
    private static function apply_product_scope_to_count_query( &$query_args, $feed_config, $feed_id, $product_scope ) {
        $taxonomy_scopes = [
            'product_cat'   => 'rex_feed_cats',
            'product_tag'   => 'rex_feed_tags',
            'product_brand' => 'rex_feed_brands',
        ];

        if ( isset( $taxonomy_scopes[ $product_scope ] ) ) {
            $query_args[ 'post_type' ] = [ 'product' ];
            $terms_key                 = $taxonomy_scopes[ $product_scope ];
            $terms                     = $feed_config[ $terms_key ] ?? wp_get_post_terms( $feed_id, $product_scope, [ 'fields' => 'slugs' ] );
            $terms                     = is_array( $terms ) ? array_map( 'sanitize_title', $terms ) : [];

            if ( !empty( $terms ) ) {
                $query_args[ 'tax_query' ] = [
                    [
                        'taxonomy' => $product_scope,
                        'field'    => 'slug',
                        'terms'    => $terms,
                    ],
                ];
            }
        }
        elseif ( 'product_filter' === $product_scope ) {
            $product_ids = $feed_config[ 'rex_feed_product_filter_ids' ]
                ?? ( get_post_meta( $feed_id, '_rex_feed_product_filter_ids', true ) ?: get_post_meta( $feed_id, 'rex_feed_product_filter_ids', true ) );
            $product_ids = array_filter( array_map( 'absint', (array) $product_ids ) );
            $condition   = $feed_config[ 'product_filter_condition' ]
                ?? ( get_post_meta( $feed_id, '_rex_feed_product_condition', true ) ?: get_post_meta( $feed_id, 'rex_feed_product_condition', true ) );
            $condition   = is_array( $condition ) ? implode( '', $condition ) : $condition;

            if ( !empty( $product_ids ) ) {
                if ( 'inc' === $condition ) {
                    $query_args[ 'post__in' ] = $product_ids;
                }
                else {
                    $query_args[ 'post__not_in' ] = array_merge( $query_args[ 'post__not_in' ], $product_ids );
                }
            }
        }
        elseif ( 'featured' === $product_scope ) {
            $query_args[ 'tax_query' ] = [
                [
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => 'featured',
                    'operator' => 'IN',
                ],
            ];
        }
    }

    /**
     * Switch multilingual integrations to the feed's saved language.
     *
     * @param array      $feed_config Submitted feed configuration.
     * @param int|string $feed_id Feed ID.
     *
     * @return void
     */
    private static function switch_to_feed_language( $feed_config, $feed_id ) {
        if ( !function_exists( 'wpfm_switch_site_lang' ) ) {
            return;
        }

        $language = get_post_meta( $feed_id, '_rex_feed_wpml_language', true ) ?: get_post_meta( $feed_id, 'rex_feed_wpml_language', true );
        if ( !$language && defined( 'ICL_LANGUAGE_CODE' ) ) {
            $language = ICL_LANGUAGE_CODE;
        }

        $currency = !empty( $feed_config[ 'rex_feed_wcml_currency' ] )
            ? $feed_config[ 'rex_feed_wcml_currency' ]
            : ( function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '' );
        wpfm_switch_site_lang( $language, $currency );
    }

    /**
     * Add the joins required by custom feed filters.
     *
     * @param string $join Existing query joins.
     * @param array  $custom_filter_args Prepared custom filter arguments.
     *
     * @return string
     */
    private static function add_custom_filter_count_joins( $join, $custom_filter_args ) {
        global $wpdb;

        $where = $custom_filter_args[ 'where' ] ?? '';
        if ( !$where ) {
            return $join;
        }

        if ( !empty( $custom_filter_args[ 'term_exists' ] ) ) {
            $term_joins = preg_match_all( '/RexTerm/i', $where );
            for ( $index = 1; $index <= $term_joins; $index++ ) {
                $join .= " LEFT JOIN {$wpdb->term_relationships} AS RexTerm{$index}";
                $join .= " ON ({$wpdb->posts}.ID = RexTerm{$index}.object_id) ";
            }
        }

        if ( !empty( $custom_filter_args[ 'meta_keys' ] ) ) {
            $meta_joins = (int) ( preg_match_all( '/RexMeta/i', $where ) / 2 );
            for ( $index = 1; $index <= $meta_joins; $index++ ) {
                $meta_key = $custom_filter_args[ 'meta_keys' ][ $index - 1 ] ?? '';
                $join    .= " LEFT JOIN {$wpdb->postmeta} AS RexMeta{$index}";
                $join    .= " ON ({$wpdb->posts}.ID = RexMeta{$index}.post_id) ";
                if ( $meta_key ) {
                    // The SQL alias is derived from the integer loop counter; only the meta key is user-influenced.
                    $join .= $wpdb->prepare( " AND (RexMeta{$index}.meta_key = %s) ", $meta_key ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                }
            }
        }

        return $join;
    }

    /**
     * Build the product-limit notice for the current plugin state.
     *
     * @param int  $eligible_total Total product records matched by the feed query.
     * @param bool $is_premium Whether a valid Pro license is active.
     *
     * @return array|false
     */
    private static function get_product_limit_notice( $eligible_total, $is_premium ) {
        if ( $is_premium || $eligible_total <= WPFM_FREE_MAX_PRODUCT_LIMIT ) {
            return false;
        }

        $pro_plugin   = 'best-woocommerce-feed-pro/rex-product-feed-pro.php';
        $is_installed = file_exists( WP_PLUGIN_DIR . '/' . $pro_plugin );
        $is_active    = false;

        if ( $is_installed ) {
            if ( !function_exists( 'is_plugin_active' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $is_active = is_plugin_active( $pro_plugin );
        }

        $remaining_products = $eligible_total - WPFM_FREE_MAX_PRODUCT_LIMIT;
        $notice = [
            'limited'         => true,
            'total_products'  => $eligible_total,
            'included_products' => WPFM_FREE_MAX_PRODUCT_LIMIT,
            'remaining_products' => $remaining_products,
            'product_limit'   => WPFM_FREE_MAX_PRODUCT_LIMIT,
            'warning_icon'    => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/icon-svg/product-limit-warning.svg',
            'crown_icon'      => '',
            'button_external' => false,
            'dismiss_label'   => __( 'Dismiss product limit notice', 'rex-product-feed' ),
        ];

        if ( !$is_installed ) {
            $notice[ 'scenario' ]        = 'free';
            $notice[ 'heading' ]         = __( 'Free plan product limit reached.', 'rex-product-feed' );
            $notice[ 'message' ]         = sprintf(
                /* translators: 1: product records processed, 2: total product records matched, 3: product records excluded by the free limit. */
                __( 'The feed matched %2$s product records. The free plan processed %1$s, and the remaining %3$s were not processed. Upgrade to Pro to process them all.', 'rex-product-feed' ),
                number_format_i18n( WPFM_FREE_MAX_PRODUCT_LIMIT ),
                number_format_i18n( $eligible_total ),
                number_format_i18n( $remaining_products )
            );
            $notice[ 'button_label' ]    = __( 'Upgrade to Pro', 'rex-product-feed' );
            $notice[ 'button_url' ]      = 'https://rextheme.com/best-woocommerce-product-feed/pricing/?utm_source=plugin&utm_medium=free-feed-generation&utm_campaign=upgrade-to-pro&utm_id=cro-in-plugin';
            $notice[ 'button_external' ] = true;
            $notice[ 'crown_icon' ]      = WPFM_PLUGIN_ASSETS_FOLDER . 'icon/icon-svg/product-limit-crown.svg?ver=' . WPFM_VERSION;
        }
        elseif ( !$is_active ) {
            $notice[ 'scenario' ]     = 'pro-inactive';
            $notice[ 'heading' ]      = __( 'PFM Pro is installed but not activated.', 'rex-product-feed' );
            $notice[ 'message' ]      = sprintf(
                /* translators: 1: product records processed, 2: total product records matched, 3: product records excluded by the free limit. */
                __( 'The feed matched %2$s product records. The free plan processed %1$s, and the remaining %3$s were not processed. Activate PFM Pro to process them all.', 'rex-product-feed' ),
                number_format_i18n( WPFM_FREE_MAX_PRODUCT_LIMIT ),
                number_format_i18n( $eligible_total ),
                number_format_i18n( $remaining_products )
            );
            $notice[ 'button_label' ] = __( 'Activate PFM Pro', 'rex-product-feed' );
            $notice[ 'button_url' ]   = admin_url( 'plugins.php?plugin_status=inactive' );
        }
        else {
            $notice[ 'scenario' ]     = 'license-inactive';
            $notice[ 'heading' ]      = __( 'PFM Pro license activation required.', 'rex-product-feed' );
            $notice[ 'message' ]      = sprintf(
                /* translators: 1: product records processed, 2: total product records matched, 3: product records excluded by the free limit. */
                __( 'The feed matched %2$s product records. The free plan processed %1$s, and the remaining %3$s were not processed. Activate your Pro license to process them all.', 'rex-product-feed' ),
                number_format_i18n( WPFM_FREE_MAX_PRODUCT_LIMIT ),
                number_format_i18n( $eligible_total ),
                number_format_i18n( $remaining_products )
            );
            $notice[ 'button_label' ] = __( 'License Activate', 'rex-product-feed' );
            $notice[ 'button_url' ]   = admin_url( 'edit.php?post_type=product-feed&page=wpfm-license' );
        }

        return $notice;
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
                Rex_Product_Feed_Controller::update_feed_status( $config[ 'info' ][ 'post_id' ], 'completed', true );
                update_post_meta( $config[ 'info' ][ 'post_id' ], '_rex_mas_last_sync', time() );
            }
        }
        catch ( Exception $e ) {
            return $e->getMessage();
        }
        $result         = $merchant->make_feed();
        $publish_button = get_post_meta( $config[ 'info' ][ 'post_id' ], '_rex_feed_publish_btn', true );
        if ( $config[ 'info' ][ 'batch' ] === $config[ 'info' ][ 'total_batch' ] && 'rex-bottom-preview-btn' !== $publish_button ) {
            Rex_Feed_Product_Count_Guard::accept_manual_run( $config[ 'info' ][ 'post_id' ] );
        }
        return $result;
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
        if ( !$selected_format || ! in_array( $selected_format, $feed_format, true ) ) {
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
            $feed_id_posthog = !empty( $payload[ 'feed_id' ] ) ? $payload[ 'feed_id' ] : '';

            $track = 'yes' === $payload[ 'track' ] ?? false;
            if ( $track) {
                do_action( 'rex_product_feed_advanced_feature_used',$feed_id_posthog, [
                        'feature' => 'Category Mapping',
                ] );
            }

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
            do_action( 'wpfm_category_mapping_saved' );

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
        $feed_id_posthog = !empty( $payload[ 'feed_id' ] ) ? $payload[ 'feed_id' ] : '';
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
            $feed_id_posthog = !empty( $payload[ 'feed_id' ] ) ? $payload[ 'feed_id' ] : '';
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
        $feed_id = !empty( $payload[ 'feed_id' ] ) ? $payload[ 'feed_id' ] : null;

        if ( ! $feed_id ) {
            return array( 'success' => false, 'message' => __( 'Feed ID missing.', 'rex-product-feed' ) );
        }

        $data_source_id = get_post_meta( $feed_id, '_rex_feed_google_data_source_id', true );
        $data_feed_id   = get_post_meta( $feed_id, '_rex_feed_google_data_feed_id', true ) ?: get_post_meta( $feed_id, 'rex_feed_google_data_feed_id', true );

        // Route: Always use Merchant API — Content API retires August 18, 2026 and is disabled.
        // The Merchant API path handles missing credentials with a clear error message.
        // Old feeds with only data_feed_id (no data_source_id) are auto-migrated on first send.
        $use_merchant_api = true;

        if ( $use_merchant_api ) {
            // Merchant API uses UserRefreshCredentials (auto-refreshes) — no access-token expiry check needed.
            $result = self::send_to_google_merchant_api( $feed_id, $payload, $data_source_id );
            if ( isset( $result[ 'success' ] ) && false === $result[ 'success' ] ) {
                return $result;
            }
            // Propagate migrated flag so the UI can show a migration notice.
            if ( ! empty( $result[ 'migrated' ] ) ) {
                return array_merge(
                    $result,
                    array( 'message' => __( 'Feed automatically migrated to Merchant API v1 and sent to Google Merchant Center.', 'rex-product-feed' ) )
                );
            }
        } else {
            // Legacy Content API — requires a valid (non-expired) access token.
            $rex_google_merchant = new Rex_Google_Merchant_Settings_Api();
            if ( ! $rex_google_merchant->is_authenticate() ) {
                return array( 'success' => false, 'message' => __( 'Not authenticated with Google. Please re-authorize on the Merchant Settings page.', 'rex-product-feed' ) );
            }
            $result = self::send_to_google_content_api( $feed_id, $payload, $rex_google_merchant );
            if ( isset( $result[ 'success' ] ) && false === $result[ 'success' ] ) {
                return $result;
            }
        }

        // Persist schedule meta.
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
     * Merchant API v1 path for send_to_google().
     *
     * Creates or updates a DataSource in GMC using the DataSources API, then triggers a fetch.
     *
     * @param int|string $feed_id
     * @param array      $payload
     * @param string     $data_source_id  Existing DataSource resource name, or empty string.
     * @return array     Success/error array.
     */
    private static function send_to_google_merchant_api( $feed_id, array $payload, string $data_source_id ): array {
        try {
            $merchant_client = Rex_Feed_Merchant_API_Client::from_stored_credentials();
            if ( ! $merchant_client ) {
                if ( wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development' ) {
                    if ( ! $data_source_id ) {
                        $mock_id = 'accounts/123456789/dataSources/mock_' . $feed_id;
                        update_post_meta( $feed_id, '_rex_feed_google_data_source_id', $mock_id );
                        error_log( sprintf( '[Local Mock] Bypassed DataSource creation for feed_id=%d and set mock ID: %s', (int) $feed_id, $mock_id ) );
                    } else {
                        error_log( sprintf( '[Local Mock] Bypassed DataSource update for feed_id=%d, ID: %s', (int) $feed_id, $data_source_id ) );
                    }
                    return array( 'success' => true );
                }
                // Diagnose which piece is missing so the error message is actionable.
                $token_data    = get_option( 'rex_google_access_token', '' );
                $token_data    = is_array( $token_data ) ? $token_data : json_decode( $token_data, true );
                $has_refresh   = ! empty( $token_data['refresh_token'] ?? '' );
                $has_client_id = ! empty( get_option( 'rex_google_client_id', '' ) );
                $has_secret    = ! empty( get_option( 'rex_google_client_secret', '' ) );

                if ( $has_client_id && $has_secret && ! $has_refresh ) {
                    return array(
                        'success' => false,
                        'message' => __( 'Your Google credentials need to be refreshed. Please click "Re-authenticate" on the Google Merchant settings page, then try again.', 'rex-product-feed' ),
                    );
                }
                return array(
                    'success' => false,
                    'message' => __( 'Google Merchant API credentials not configured. Please enter your Client ID, Client Secret, and Merchant ID on the Google Merchant settings page, then authenticate.', 'rex-product-feed' ),
                );
            }

            $merchant_id  = get_option( 'rex_google_merchant_id', '' );
            $feed_url     = self::get_or_restore_feed_url( (int) $feed_id );
            $feed_title   = get_the_title( $feed_id );
            $country      = isset( $payload[ 'country' ] ) ? sanitize_text_field( $payload[ 'country' ] ) : ( get_post_meta( $feed_id, '_rex_feed_google_target_country', true ) ?: 'US' );
            $language     = isset( $payload[ 'language' ] ) ? sanitize_text_field( $payload[ 'language' ] ) : ( get_post_meta( $feed_id, '_rex_feed_google_target_language', true ) ?: 'en' );
            $hour         = isset( $payload[ 'hour' ] ) ? absint( $payload[ 'hour' ] ) : 22;

            if ( ! $feed_url ) {
                return array(
                    'success' => false,
                    'message' => __( 'Feed file URL not found. Please generate the feed first.', 'rex-product-feed' ),
                );
            }

            $fetch_settings = ( new FetchSettings() )
                ->setEnabled( true )
                ->setTimeOfDay( ( new TimeOfDay() )->setHours( $hour ) )
                ->setFrequency( FetchSettings\Frequency::FREQUENCY_DAILY )
                ->setFetchUri( $feed_url );

            $default_rule = ( new \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\PrimaryProductDataSource\DefaultRule() )
                ->setTakeFromDataSources( array(
                    ( new \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\DataSourceReference() )->setSelf( true )
                ) );

            // Only FREE_LISTINGS (int 4) is used here — SHOPPING_ADS (int 1) maps to SHOPPING_PLUS
            // in the current server proto which requires account enrollment. Users can enable
            // additional destinations directly in the Google Merchant Center UI.
            $destinations = array(
                ( new \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\PrimaryProductDataSource\Destination() )
                    ->setDestination( \RexFeed\Vendor\Google\Shopping\Type\Destination\DestinationEnum::FREE_LISTINGS )
                    ->setState( \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\PrimaryProductDataSource\Destination\State::ENABLED ),
            );

            $data_source_obj = ( new DataSource() )
                ->setDisplayName( $feed_title )
                ->setPrimaryProductDataSource(
                    ( new PrimaryProductDataSource() )
                        ->setCountries( array( $country ) )
                        ->setContentLanguage( $language )
                        ->setFeedLabel( $country )
                        ->setDestinations( $destinations )
                        ->setDefaultRule( $default_rule )
                );

            $ds_client = $merchant_client->get_datasources_client();

            if ( $data_source_id ) {
                $data_source_obj->setName( $data_source_id );
                $update_request = ( new UpdateDataSourceRequest() )
                    ->setDataSource( $data_source_obj )
                    ->setUpdateMask(
                        // Do not include file_input in update to avoid immutable fileName validation errors.
                        new FieldMask( array( 'paths' => array( 'display_name', 'primary_product_data_source' ) ) )
                    );
                $ds_client->updateDataSource( $update_request );
                // Trigger GMC to re-fetch the file.
                $ds_client->fetchDataSource( ( new FetchDataSourceRequest() )->setName( $data_source_id ) );
                error_log( sprintf( '[Merchant API] Data source updated and fetch triggered. feed_id=%d, data_source_id=%s', (int) $feed_id, $data_source_id ) );
            } else {
                $create_request = ( new CreateDataSourceRequest() )
                    ->setParent( "accounts/{$merchant_id}" )
                    ->setDataSource( $data_source_obj );
                // API behavior differs by account: some accept fetch-only input, others require fileName.
                // Try fetch-only first, then retry with fileName only when explicitly required.
                $data_source_obj->setFileInput( ( new FileInput() )->setFetchSettings( $fetch_settings ) );
                try {
                    $response = $ds_client->createDataSource( $create_request );
                } catch ( ApiException $create_exception ) {
                    if ( false !== strpos( $create_exception->getMessage(), 'Required field not provided: fileInput.fileName' ) ) {
                        $data_source_obj->setFileInput(
                            ( new FileInput() )
                                ->setFileName( basename( $feed_url ) )
                                ->setFetchSettings( $fetch_settings )
                        );
                        $create_request->setDataSource( $data_source_obj );
                        $response = $ds_client->createDataSource( $create_request );
                    } else {
                        throw $create_exception;
                    }
                }
                $new_id   = $response->getName();
                update_post_meta( $feed_id, '_rex_feed_google_data_source_id', $new_id );
                // Allow Google backend to finish indexing the newly created DataSource resource.
                sleep( 2 );
                // Trigger initial fetch with retry for eventual consistency.
                try {
                    $ds_client->fetchDataSource( ( new FetchDataSourceRequest() )->setName( $new_id ) );
                } catch ( ApiException $fetch_e ) {
                    if ( false !== strpos( $fetch_e->getMessage(), 'was not found' ) ) {
                        sleep( 3 );
                        try {
                            $ds_client->fetchDataSource( ( new FetchDataSourceRequest() )->setName( $new_id ) );
                        } catch ( ApiException $fetch_e2 ) {
                            error_log( sprintf( '[Merchant API] Initial fetch trigger deferred for feed_id=%d: %s', (int) $feed_id, $fetch_e2->getMessage() ) );
                        }
                    } else {
                        throw $fetch_e;
                    }
                }
                error_log( sprintf( '[Merchant API] New Data source created and fetch triggered. feed_id=%d, new_data_source_id=%s', (int) $feed_id, $new_id ) );
                // Signal to the caller that this was an auto-migration from Content API.
                return array( 'success' => true, 'migrated' => true, 'data_source_id' => $new_id );
            }
        } catch ( ApiException $e ) {
            error_log( sprintf( '[Merchant API] API Exception encountered. feed_id=%d, status=%s, message=%s', (int) $feed_id, (string) $e->getStatus(), $e->getMessage() ) );
            // If DataSource was deleted in GMC, clear stale ID and recreate.
            $stale_ds_id = $data_source_id ?: get_post_meta( $feed_id, '_rex_feed_google_data_source_id', true );
            if ( $stale_ds_id && false !== strpos( $e->getStatus(), 'NOT_FOUND' ) && false === strpos( $e->getMessage(), 'was not found' ) ) {
                delete_post_meta( $feed_id, '_rex_feed_google_data_source_id' );
                return self::send_to_google_merchant_api( $feed_id, $payload, '' );
            }
            $normalized = Rex_Feed_Merchant_API_Client::normalize_api_error( $e );
            // GCP project not registered with this Merchant Center account — auto-register silently.
            if ( 'project_not_registered' === ( $normalized['error_type'] ?? '' ) ) {
                // Must use the authenticated Google account email, not the WordPress user email.
                $developer_email = $merchant_client->get_google_email();
                $reg_result      = $merchant_client->register_gcp( $merchant_id, $developer_email );
                if ( $reg_result['success'] ) {
                    return array(
                        'success'    => false,
                        'error_type' => 'registration_complete',
                        'message'    => __( 'Your Google Cloud project has been registered with your Merchant Center account. Please wait 5 minutes, then click "Send to Google Merchant" again.', 'rex-product-feed' ),
                    );
                }
                return $reg_result;
            }
            $log = wc_get_logger();
            $log->error(
                sprintf( '[Merchant API] send_to_google failed: %s (status: %s)', $e->getMessage(), $e->getStatus() ),
                array( 'source' => 'WPFM-google-merchant-api' )
            );
            return $normalized;
        } catch ( \Throwable $e ) {
            $log = wc_get_logger();
            $log->error(
                sprintf( '[Merchant API] send_to_google unexpected error: %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine() ),
                array( 'source' => 'WPFM-google-merchant-api' )
            );
            return array(
                'success' => false,
                'message' => $e->getMessage(),
            );
        }

        return array( 'success' => true );
    }

    /**
     * Legacy Content API path for send_to_google() — used only for feeds not yet migrated.
     *
     * @param int|string               $feed_id
     * @param array                    $payload
     * @param Rex_Google_Merchant_Settings_Api $rex_google_merchant
     * @return array
     */
    private static function send_to_google_content_api( $feed_id, array $payload, Rex_Google_Merchant_Settings_Api $rex_google_merchant ): array {
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

            $feed_exists = $rex_google_merchant->feed_exists( $feed_id );
            if ( ! $feed_exists ) {
                $datafeed->setFileName( $filename );
            } else {
                $data_feed_file = get_post_meta( $feed_id, '_rex_feed_google_data_feed_file_name', true ) ?: get_post_meta( $feed_id, 'rex_feed_google_data_feed_file_name', true );
                $datafeed->setFileName( $data_feed_file );
            }

            $fetch_schedule = new RexFeed\Google\Service\ShoppingContent\DatafeedFetchSchedule();
            if ( ! empty( $payload[ 'schedule' ] ) ) {
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

            $format = new RexFeed\Google\Service\ShoppingContent\DatafeedFormat();
            $format->setFileEncoding( 'utf-8' );
            $datafeed->setFormat( $format );
            $datafeed->setFetchSchedule( $fetch_schedule );

            try {
                $data_feed_id = get_post_meta( $feed_id, '_rex_feed_google_data_feed_id', true ) ?: get_post_meta( $feed_id, 'rex_feed_google_data_feed_id', true );
                if ( $feed_exists ) {
                    $datafeed->setId( $data_feed_id );
                    $service->datafeeds->update( $merchant_id, $data_feed_id, $datafeed );
                } else {
                    $datafeed            = $service->datafeeds->insert( $merchant_id, $datafeed );
                    $data_feed_id        = $datafeed->getId();
                    $data_feed_file_name = $datafeed->getFileName();
                    update_post_meta( $feed_id, '_rex_feed_google_data_feed_id', $data_feed_id );
                    update_post_meta( $feed_id, '_rex_feed_google_data_feed_file_name', $data_feed_file_name );
                }
                $service->datafeeds->fetchnow( $merchant_id, $data_feed_id );
            } catch ( Exception $e ) {
                if ( is_wpfm_logging_enabled() ) {
                    $log = wc_get_logger();
                    $log->info( $e->getMessage(), array( 'source' => 'WPFM-google' ) );
                }
                if ( ! is_string( $e->getMessage() ) && is_object( $e->getMessage() ) ) {
                    $error  = json_decode( $e->getMessage() );
                    $reason = ! empty( $error->error->errors ) ? $error->error->errors : '';
                } else {
                    $error = $e->getMessage();
                }
                return array(
                    'success' => false,
                    'message' => ! empty( $error->error->message ) ? $error->error->message : $error,
                    'reason'  => ! empty( $reason[ 0 ]->reason ) ? $reason[ 0 ]->reason : $error,
                );
            }

        return array( 'success' => true );
    }

    /**
     * Trigger a GMC fetch for a Merchant API DataSource.
     *
     * AJAX action: rexfeed-fetch-google-datasource
     *
     * @param array $payload  Must include feed_id.
     * @return array
     */
    public static function fetch_google_datasource( array $payload ): array {
        try {
            $feed_id        = ! empty( $payload[ 'feed_id' ] ) ? absint( $payload[ 'feed_id' ] ) : 0;
            $data_source_id = $feed_id ? get_post_meta( $feed_id, '_rex_feed_google_data_source_id', true ) : '';

            if ( ! $data_source_id ) {
                return array( 'success' => false, 'message' => __( 'No Merchant API DataSource found for this feed.', 'rex-product-feed' ) );
            }

            $merchant_client = Rex_Feed_Merchant_API_Client::from_stored_credentials();
            if ( ! $merchant_client ) {
                if ( wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development' ) {
                    error_log( sprintf( '[Local Mock] Bypassed fetch trigger for DataSource ID: %s', $data_source_id ) );
                    return array( 'success' => true );
                }
                return array( 'success' => false, 'message' => __( 'GMC credentials not configured.', 'rex-product-feed' ) );
            }

            $merchant_client->get_datasources_client()->fetchDataSource(
                ( new FetchDataSourceRequest() )->setName( $data_source_id )
            );
            return array( 'success' => true );
        } catch ( ApiException $e ) {
            if ( is_wpfm_logging_enabled() ) {
                $log = wc_get_logger();
                $log->error(
                    sprintf( '[Merchant API] fetchDataSource failed: %s', $e->getMessage() ),
                    array( 'source' => 'WPFM-google-merchant-api' )
                );
            }
            return Rex_Feed_Merchant_API_Client::normalize_api_error( $e );
        } catch ( \Throwable $e ) {
            if ( is_wpfm_logging_enabled() ) {
                $log = wc_get_logger();
                $log->error(
                    sprintf( '[Merchant API] fetchDataSource unexpected error: %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine() ),
                    array( 'source' => 'WPFM-google-merchant-api' )
                );
            }
            return array(
                'success' => false,
                'message' => $e->getMessage(),
            );
        }
    }

    /**
     * Retrieve feed XML/CSV file URL or restore it if the physical file exists on disk.
     *
     * @param int $feed_id
     * @return string
     */
    public static function get_or_restore_feed_url( int $feed_id ): string {
        $feed_url = get_post_meta( $feed_id, '_rex_feed_xml_file', true ) ?: get_post_meta( $feed_id, 'rex_feed_xml_file', true );
        if ( ! empty( $feed_url ) ) {
            return $feed_url;
        }

        $upload_dir = wp_upload_dir();
        $basedir    = trailingslashit( $upload_dir['basedir'] ) . 'rex-feed/';
        $baseurl    = trailingslashit( $upload_dir['baseurl'] ) . 'rex-feed/';

        // 1. Check for standard feed-{id}.* file
        $possible_exts = array( 'xml', 'csv', 'tsv', 'txt', 'json', 'rss' );
        foreach ( $possible_exts as $ext ) {
            $file_path = $basedir . "feed-{$feed_id}.{$ext}";
            if ( file_exists( $file_path ) ) {
                $feed_url = $baseurl . "feed-{$feed_id}.{$ext}";
                update_post_meta( $feed_id, '_rex_feed_xml_file', $feed_url );
                return $feed_url;
            }
        }

        // 2. Glob search for any file containing feed_id in rex-feed directory
        if ( is_dir( $basedir ) ) {
            $files = glob( $basedir . "*{$feed_id}.*" );
            if ( ! empty( $files ) ) {
                foreach ( $files as $file ) {
                    $filename = basename( $file );
                    if ( false === strpos( $filename, 'temp-' ) && false === strpos( $filename, 'preview-' ) ) {
                        $feed_url = $baseurl . $filename;
                        update_post_meta( $feed_id, '_rex_feed_xml_file', $feed_url );
                        return $feed_url;
                    }
                }
            }
        }

        // 3. If file does not exist on disk, auto-generate it via Rex_Feed_Scheduler
        if ( class_exists( 'Rex_Feed_Scheduler' ) ) {
            try {
                $scheduler     = new Rex_Feed_Scheduler();
                $total_batches = (int) ( get_post_meta( $feed_id, '_wpfm_feed_total_batches', true ) ?: 1 );
                for ( $batch = 1; $batch <= $total_batches; $batch++ ) {
                    $scheduler->regenerate_feed_batch( array(
                        'feed_id'       => $feed_id,
                        'current_batch' => $batch,
                        'total_batches' => $total_batches,
                        'per_batch'     => 100,
                        'offset'        => ( $batch - 1 ) * 100,
                    ) );
                }
                $feed_url = get_post_meta( $feed_id, '_rex_feed_xml_file', true ) ?: get_post_meta( $feed_id, 'rex_feed_xml_file', true );
                if ( ! empty( $feed_url ) ) {
                    return $feed_url;
                }
            } catch ( \Throwable $e ) {
                error_log( sprintf( '[WPFM] Feed auto-generation failed for feed_id=%d: %s', (int) $feed_id, $e->getMessage() ) );
            }
        }

        return '';
    }

    /**
     * One-click migration: create a Merchant API DataSource from an existing Content API feed.
     *
     * AJAX action: rexfeed-migrate-to-merchant-api
     *
     * @param array $payload  Must include feed_id.
     * @return void  Sends JSON response directly.
     */
    public static function migrate_to_merchant_api( array $payload ): void {
        try {
            $feed_id = ! empty( $payload[ 'feed_id' ] ) ? absint( $payload[ 'feed_id' ] ) : 0;
            if ( ! $feed_id ) {
                wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
                return;
            }

            $data_source_id = get_post_meta( $feed_id, '_rex_feed_google_data_source_id', true );
            if ( $data_source_id ) {
                wp_send_json_success( array(
                    'message'        => __( 'Feed already migrated to Merchant API.', 'rex-product-feed' ),
                    'data_source_id' => $data_source_id,
                ) );
                return;
            }

            $merchant_client = Rex_Feed_Merchant_API_Client::from_stored_credentials();
            if ( ! $merchant_client ) {
                if ( wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development' ) {
                    $mock_id = 'accounts/123456789/dataSources/mock_' . $feed_id;
                    update_post_meta( $feed_id, '_rex_feed_google_data_source_id', $mock_id );
                    error_log( sprintf( '[Local Mock] Bypassed content API migration for feed_id=%d and set mock ID: %s', (int) $feed_id, $mock_id ) );
                    wp_send_json_success( array(
                        'message'        => __( 'Feed successfully migrated to mock Merchant API.', 'rex-product-feed' ),
                        'data_source_id' => $mock_id,
                    ) );
                    return;
                }
                wp_send_json_error( array( 'message' => __( 'GMC credentials not configured. Please check your Google Merchant settings.', 'rex-product-feed' ) ) );
                return;
            }

            $merchant_id = get_option( 'rex_google_merchant_id', '' );
            $feed_url    = self::get_or_restore_feed_url( $feed_id );
            $feed_title  = get_the_title( $feed_id );
            $country     = get_post_meta( $feed_id, '_rex_feed_google_target_country', true ) ?: 'US';
            $language    = get_post_meta( $feed_id, '_rex_feed_google_target_language', true ) ?: 'en';
            $hour        = (int) ( get_post_meta( $feed_id, '_rex_feed_google_schedule_time', true ) ?: 22 );

            if ( ! $feed_url ) {
                wp_send_json_error( array( 'message' => __( 'Feed file URL not found. Please generate the feed first.', 'rex-product-feed' ) ) );
                return;
            }

            $fetch_settings  = ( new FetchSettings() )
                ->setEnabled( true )
                ->setTimeOfDay( ( new TimeOfDay() )->setHours( $hour ) )
                ->setFrequency( FetchSettings\Frequency::FREQUENCY_DAILY )
                ->setFetchUri( $feed_url );

            $default_rule = ( new \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\PrimaryProductDataSource\DefaultRule() )
                ->setTakeFromDataSources( array(
                    ( new \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\DataSourceReference() )->setSelf( true )
                ) );

            $destinations = array(
                ( new \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\PrimaryProductDataSource\Destination() )
                    ->setDestination( \RexFeed\Vendor\Google\Shopping\Type\Destination\DestinationEnum::FREE_LISTINGS )
                    ->setState( \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\PrimaryProductDataSource\Destination\State::ENABLED ),
            );

            $data_source_obj = ( new DataSource() )
                ->setDisplayName( $feed_title )
                ->setPrimaryProductDataSource(
                    ( new PrimaryProductDataSource() )
                        ->setCountries( array( $country ) )
                        ->setContentLanguage( $language )
                        ->setFeedLabel( $country )
                        ->setDestinations( $destinations )
                        ->setDefaultRule( $default_rule )
                );

            $create_request = ( new CreateDataSourceRequest() )
                ->setParent( "accounts/{$merchant_id}" )
                ->setDataSource( $data_source_obj );

            // API behavior differs by account: some accept fetch-only input, others require fileName.
            // Try fetch-only first, then retry with fileName only when explicitly required.
            $data_source_obj->setFileInput( ( new FileInput() )->setFetchSettings( $fetch_settings ) );
            try {
                $response = $merchant_client->get_datasources_client()->createDataSource( $create_request );
            } catch ( ApiException $create_exception ) {
                if ( false !== strpos( $create_exception->getMessage(), 'Required field not provided: fileInput.fileName' ) ) {
                    $data_source_obj->setFileInput(
                        ( new FileInput() )
                            ->setFileName( basename( $feed_url ) )
                            ->setFetchSettings( $fetch_settings )
                    );
                    $create_request->setDataSource( $data_source_obj );
                    $response = $merchant_client->get_datasources_client()->createDataSource( $create_request );
                } else {
                    throw $create_exception;
                }
            }
            $new_ds_id = $response->getName();
            update_post_meta( $feed_id, '_rex_feed_google_data_source_id', $new_ds_id );

            wp_send_json_success( array(
                'message'        => __( 'Feed migrated to Merchant API v1 successfully.', 'rex-product-feed' ),
                'data_source_id' => $new_ds_id,
            ) );
        } catch ( ApiException $e ) {
            $normalized = Rex_Feed_Merchant_API_Client::normalize_api_error( $e );
            // GCP project not registered — auto-register silently, then tell user to retry.
            if ( 'project_not_registered' === ( $normalized['error_type'] ?? '' ) ) {
                // Must use the authenticated Google account email, not the WordPress user email.
                $developer_email = isset( $merchant_client ) ? $merchant_client->get_google_email() : '';
                $reg_result      = ( isset( $merchant_client ) && ! empty( $merchant_id ) ) ? $merchant_client->register_gcp( $merchant_id, $developer_email ) : array( 'success' => false );
                if ( ! empty( $reg_result['success'] ) ) {
                    wp_send_json_error( array(
                        'error_type' => 'registration_complete',
                        'message'    => __( 'Your Google Cloud project has been registered with your Merchant Center account. Please wait 5 minutes, then click "Migrate Now" again.', 'rex-product-feed' ),
                    ) );
                    return;
                }
                wp_send_json_error( $reg_result );
                return;
            }
            if ( is_wpfm_logging_enabled() ) {
                $log = wc_get_logger();
                $log->error(
                    sprintf( '[Merchant API] migrate_to_merchant_api failed: %s', $e->getMessage() ),
                    array( 'source' => 'WPFM-google-merchant-api' )
                );
            }
            wp_send_json_error( $normalized );
        } catch ( \Throwable $e ) {
            if ( is_wpfm_logging_enabled() ) {
                $log = wc_get_logger();
                $log->error(
                    sprintf( '[Merchant API] migrate_to_merchant_api unexpected error: %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine() ),
                    array( 'source' => 'WPFM-google-merchant-api' )
                );
            }
            wp_send_json_error( array(
                'message'    => $e->getMessage(),
                'error_type' => 'unexpected_error',
            ) );
        }
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
     * Helper to clean up batch generation state and metadata for a specific feed.
     *
     * @param int $feed_id Feed post ID.
     * @return void
     */
    public static function clean_feed_batch_state( $feed_id ) {
        $feed_id = absint( $feed_id );
        if ( ! $feed_id || 'product-feed' !== get_post_type( $feed_id ) ) {
            return;
        }

        $group = "wpfm-feed-{$feed_id}";
        $hook  = defined( 'SINGLE_SCHEDULE_HOOK' ) ? SINGLE_SCHEDULE_HOOK : 'rex_feed_regenerate_feed_batch';

        // Cancel / unschedule pending ActionScheduler actions for this feed.
        if ( function_exists( 'as_unschedule_all_actions' ) ) {
            as_unschedule_all_actions( $hook, array(), $group );
            as_unschedule_all_actions( '', array(), $group );
        }

        if ( function_exists( 'as_get_scheduled_actions' ) && class_exists( 'ActionScheduler_Store' ) ) {
            $store = ActionScheduler_Store::instance();
            foreach ( array( 'pending', 'in-progress' ) as $status ) {
                $actions = as_get_scheduled_actions( array(
                    'group'    => $group,
                    'status'   => $status,
                    'per_page' => 500,
                ) );
                foreach ( $actions as $action_id => $action ) {
                    try {
                        $store->cancel_action( $action_id );
                    } catch ( Exception $cancel_exception ) {
                        $store->delete_action( $action_id );
                    }
                }
            }
        }

        // Reset feed status to failed (never mark completed on clear/cancel).
        Rex_Product_Feed_Controller::update_feed_status( $feed_id, 'failed', false );

        // Delete batch progress and generation metadata.
        delete_post_meta( $feed_id, '_rex_feed_current_batch' );
        delete_post_meta( $feed_id, 'rex_feed_current_batch' );
        delete_post_meta( $feed_id, '_rex_feed_total_batches' );
        delete_post_meta( $feed_id, 'rex_feed_total_batches' );
        delete_post_meta( $feed_id, '_generation_start_time' );
        delete_post_meta( $feed_id, 'generation_start_time' );

        // Clean up temporary XML/CSV files and meta.
        $temp_xml_url = get_post_meta( $feed_id, '_rex_feed_temp_xml_file', true ) ?: get_post_meta( $feed_id, 'rex_feed_temp_xml_file', true );
        if ( $temp_xml_url ) {
            $upload_dir = wp_upload_dir();
            $temp_path  = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $temp_xml_url );
            if ( file_exists( $temp_path ) ) {
                @unlink( $temp_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
            }
        }
        delete_post_meta( $feed_id, '_rex_feed_temp_xml_file' );
        delete_post_meta( $feed_id, 'rex_feed_temp_xml_file' );

        // Clean up guard run meta and backup if present.
        if ( class_exists( 'Rex_Feed_Product_Count_Guard' ) ) {
            $run = get_post_meta( $feed_id, Rex_Feed_Product_Count_Guard::RUN_META, true );
            if ( is_array( $run ) && ! empty( $run['backup_path'] ) && file_exists( $run['backup_path'] ) ) {
                @unlink( $run['backup_path'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
            }
            delete_post_meta( $feed_id, Rex_Feed_Product_Count_Guard::RUN_META );
        }

        // Delete temporary feed transients and filesystem batch cache.
        if ( class_exists( 'Rex_Feed_Generator_Helper' ) ) {
            Rex_Feed_Generator_Helper::wpfm_delete_feed_transients( $feed_id );
        }
    }


    /**
     * Clear batch generation for a specific feed, or all active feeds if no ID provided.
     *
     * @param array $payload Payload containing optional feed_id.
     * @return void
     * @since 1.0.0
     */
    public static function clear_batch( $payload = array() ) {
        $feed_id = 0;
        if ( is_array( $payload ) && ! empty( $payload['feed_id'] ) ) {
            $feed_id = absint( $payload['feed_id'] );
        } elseif ( ! empty( $_POST['feed_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $feed_id = absint( $_POST['feed_id'] );
        }

        try {
            // Case 1: Scoped clear for a single feed.
            if ( $feed_id ) {
                if ( 'product-feed' !== get_post_type( $feed_id ) ) {
                    wp_send_json_error( array(
                        'message' => __( 'Invalid feed ID.', 'rex-product-feed' ),
                    ) );
                    wp_die();
                }

                self::clean_feed_batch_state( $feed_id );

                wp_send_json_success( array(
                    'feed_id' => $feed_id,
                    'message' => __( 'Batch queue and progress metadata cleared successfully.', 'rex-product-feed' ),
                ) );
                wp_die();
            }

            // Case 2: Global clear (e.g. from Settings page) - Clean all active / stuck feeds safely.
            $active_feed_ids = get_posts( array(
                'post_type'      => 'product-feed',
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    'relation' => 'OR',
                    array(
                        'key'     => '_rex_feed_status',
                        'value'   => array( 'processing', 'In queue' ),
                        'compare' => 'IN',
                    ),
                    array(
                        'key'     => 'rex_feed_status',
                        'value'   => array( 'processing', 'In queue' ),
                        'compare' => 'IN',
                    ),
                ),
            ) );

            if ( ! empty( $active_feed_ids ) ) {
                foreach ( $active_feed_ids as $active_id ) {
                    self::clean_feed_batch_state( $active_id );
                }
            }

            // Cancel any orphaned / un-grouped single batch actions.
            if ( function_exists( 'as_unschedule_all_actions' ) ) {
                $hook = defined( 'SINGLE_SCHEDULE_HOOK' ) ? SINGLE_SCHEDULE_HOOK : 'rex_feed_regenerate_feed_batch';
                as_unschedule_all_actions( $hook );
            }

            wp_send_json_success( array(
                'cleared_count' => count( $active_feed_ids ),
                'message'       => sprintf(
                    /* translators: %d: number of feeds reset */
                    __( 'Batch queues cleared for %d active feed(s).', 'rex-product-feed' ),
                    count( $active_feed_ids )
                ),
            ) );
            wp_die();
        } catch ( Exception $e ) {
            if ( is_wpfm_logging_enabled() ) {
                $log = wc_get_logger();
                $log->warning( print_r( $e->getMessage(), 1 ), array( 'source' => 'WPFM' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
            }
            wp_send_json_error( array(
                'message' => $e->getMessage(),
            ) );
            wp_die();
        }
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
            $key      = sanitize_text_field( wp_unslash( $payload[ 'logKey' ] ) );
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

        /**
         * Fires after contact is created via webhook
         *
         * @param string $response Response from webhook.
         * @since 7.3.1
         */
        do_action( 'rex_feed_after_contact_created', $response );
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
     * It validates the credentials first by attempting to create an auth URL with them.
     * It sends a JSON success response after updating the options.
     *
     * @param array $payload The payload array containing the Google API credentials.
     * @return void
     *
     * @since 7.4.20
     */
    public static function save_google_api_credentials( $payload ) {
        $client_id     = isset( $payload[ 'client_id' ] ) ? sanitize_text_field( $payload[ 'client_id' ] ) : '';
        $client_secret = isset( $payload[ 'client_secret' ] ) ? sanitize_text_field( $payload[ 'client_secret' ] ) : '';
        $merchant_id   = isset( $payload[ 'merchant_id' ] ) ? sanitize_text_field( $payload[ 'merchant_id' ] ) : '';

        // Validate credentials by attempting to create a client with them
        if ( $client_id && $client_secret && $merchant_id ) {
            try {
                $test_client = new RexFeed\Google\Client();
                $test_client->setClientId( $client_id );
                $test_client->setClientSecret( $client_secret );
                $test_client->setRedirectUri( admin_url( 'admin.php?page=merchant_settings' ) );
                $test_client->setScopes( 'https://www.googleapis.com/auth/content' );
                
                // Try to create auth URL - this will fail if credentials are invalid
                $auth_url = $test_client->createAuthUrl();
                
                if ( empty( $auth_url ) ) {
                    wp_send_json_error( array(
                        'message' => __( 'Invalid credentials. Please check your Client ID and Client Secret.', 'rex-product-feed' )
                    ), 400 );
                    return;
                }
            } catch ( Exception $e ) {
                wp_send_json_error( array(
                    'message' => __( 'Invalid credentials. Please check your Client ID, Client Secret, and Merchant ID.', 'rex-product-feed' ),
                    'error' => $e->getMessage()
                ), 400 );
                return;
            }
        }

        // Clear access token only when credentials change
        $existing_client_id = get_option( 'rex_google_client_id', '' );
        $existing_client_secret = get_option( 'rex_google_client_secret', '' );
        $existing_merchant_id = get_option( 'rex_google_merchant_id', '' );
        
        $credentials_changed = ( $client_id !== $existing_client_id || 
                                  $client_secret !== $existing_client_secret || 
                                  $merchant_id !== $existing_merchant_id );
        
        if ( $credentials_changed ) {
            delete_option( 'rex_google_access_token' );
        }

        if ( isset( $payload[ 'client_id' ] ) ) {
            update_option( 'rex_google_client_id', $client_id );
        }
        if ( isset( $payload[ 'client_secret' ] ) ) {
            update_option( 'rex_google_client_secret', $client_secret );
        }
        if ( isset( $payload[ 'merchant_id' ] ) ) {
            update_option( 'rex_google_merchant_id', $merchant_id );
        }
        
        // Check if user is authorized after saving
        $google_api = new Rex_Feed_Google_Shopping_Api();
        $is_authorized = $google_api->is_authorized();
        
        if ( $credentials_changed ) {
            $message = __( 'Credentials updated successfully. Please click "Authenticate" to authorize access.', 'rex-product-feed' );
        } else if ( $is_authorized ) {
            $message = __( 'Credentials saved successfully. You are already authorized.', 'rex-product-feed' );
        } else {
            $message = __( 'Credentials saved successfully. Please click "Authenticate" to authorize access.', 'rex-product-feed' );
        }
        
        wp_send_json_success( array(
            'message' => $message,
            'needs_auth' => $credentials_changed || !$is_authorized,
            'is_authorized' => $is_authorized
        ) );
    }

    /**
     * Export all saved feed configurations as JSON.
     *
     * @return void
     */
    public function export_feed_configurations() {
        self::authorize_feed_configuration_transfer();

        $feeds = self::get_feed_configuration_posts();

        if ( empty( $feeds ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'No feed configurations found to export.', 'rex-product-feed' ),
                ),
                404
            );
        }

        $export = array(
            'title'        => 'Rex Product Feed Configurations',
            'format'       => 'wpfm-feed-configurations',
            'version'      => 1,
            'generated_at' => current_time( 'mysql' ),
            'feeds'        => array_map( array( __CLASS__, 'prepare_feed_configuration_export' ), $feeds ),
        );

        $content = wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

        if ( false === $content ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Failed to generate the export file.', 'rex-product-feed' ),
                ),
                500
            );
        }

        wp_send_json_success(
            array(
                'file_name' => sprintf( 'wpfm-feed-configurations-%s.json', wp_date( 'Y-m-d-His' ) ),
                'content'   => $content,
                'count'     => count( $export['feeds'] ),
            )
        );
    }

    /**
     * Import feed configurations from a JSON payload.
     *
     * @return void
     */
    public function import_feed_configurations() {
        self::authorize_feed_configuration_transfer();

        $payload = isset( $_POST['payload'] ) ? wp_unslash( $_POST['payload'] ) : '';

        if ( '' === $payload ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Select a valid JSON file to import.', 'rex-product-feed' ),
                ),
                400
            );
        }

        $data = json_decode( $payload, true );

        if ( JSON_ERROR_NONE !== json_last_error() || empty( $data['feeds'] ) || ! is_array( $data['feeds'] ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'The selected file is not a valid feed configuration export.', 'rex-product-feed' ),
                ),
                400
            );
        }

        $imported = 0;

        foreach ( $data['feeds'] as $feed ) {
            if ( ! is_array( $feed ) ) {
                continue;
            }

            $post          = isset( $feed['post'] ) && is_array( $feed['post'] ) ? $feed['post'] : array();
            $post_title    = isset( $post['title'] ) ? wp_strip_all_tags( $post['title'] ) : __( 'Imported Feed', 'rex-product-feed' );
            $post_status   = isset( $post['status'] ) ? sanitize_key( $post['status'] ) : 'publish';
            $post_statuses = array( 'publish', 'draft', 'pending', 'future', 'private' );

            if ( ! in_array( $post_status, $post_statuses, true ) ) {
                $post_status = 'publish';
            }

            $post_id = wp_insert_post(
                array(
                    'post_author'  => get_current_user_id(),
                    'post_title'   => '' !== $post_title ? $post_title : __( 'Imported Feed', 'rex-product-feed' ),
                    'post_content' => '',
                    'post_type'    => 'product-feed',
                    'post_status'  => $post_status,
                ),
                true
            );

            if ( is_wp_error( $post_id ) ) {
                continue;
            }

            self::import_feed_meta( $post_id, isset( $feed['meta'] ) ? $feed['meta'] : array() );
            self::import_feed_terms( $post_id, isset( $feed['terms'] ) ? $feed['terms'] : array() );
            self::reset_imported_feed_runtime_meta( $post_id );

            ++$imported;
        }

        if ( 0 === $imported ) {
            wp_send_json_error(
                array(
                    'message' => __( 'No feed configurations could be imported from that file.', 'rex-product-feed' ),
                ),
                400
            );
        }

        wp_send_json_success(
            array(
                'message' => sprintf(
                    /* translators: %d: imported feed count. */
                    _n( '%d feed configuration imported.', '%d feed configurations imported.', $imported, 'rex-product-feed' ),
                    $imported
                ),
                'count'   => $imported,
            )
        );
    }

    /**
     * Validate permissions and nonce for configuration transfers.
     *
     * @return void
     */
    private static function authorize_feed_configuration_transfer() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to manage feed configurations.', 'rex-product-feed' ),
                ),
                403
            );
        }

        if ( ! apply_filters( 'wpfm_is_premium_activate', false ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Feed import and export is a Pro feature. Please activate your Pro license to use it.', 'rex-product-feed' ),
                ),
                403
            );
        }
    }

    /**
     * Collect the feed posts that should be included in the export.
     *
     * @return array
     */
    private static function get_feed_configuration_posts() {
        return get_posts(
            array(
                'post_type'      => 'product-feed',
                'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'ASC',
            )
        );
    }

    /**
     * Prepare a single feed for JSON export.
     *
     * @param WP_Post $feed Feed post object.
     *
     * @return array
     */
    private static function prepare_feed_configuration_export( $feed ) {
        return array(
            'post'  => array(
                'title'  => $feed->post_title,
                'status' => $feed->post_status,
            ),
            'meta'  => self::get_exportable_post_meta( $feed->ID ),
            'terms' => self::get_exportable_feed_terms( $feed->ID ),
        );
    }

    /**
     * Get post meta that should round-trip in feed configuration exports.
     *
     * @param int $feed_id Feed post ID.
     *
     * @return array
     */
    /**
     * Get post meta that should round-trip in feed configuration exports.
     *
     * @param int $feed_id Feed post ID.
     *
     * @return array
     */
    private static function get_exportable_post_meta( $feed_id ) {
        $meta = get_post_meta( $feed_id );

        foreach ( self::get_excluded_feed_meta_keys() as $meta_key ) {
            unset( $meta[ $meta_key ] );
        }

        $processed_meta = array();

        foreach ( $meta as $meta_key => $values ) {
            if ( ! is_array( $values ) ) {
                continue;
            }

            $processed_values = array();

            foreach ( $values as $val ) {
                $val = maybe_unserialize( $val );
                $val = self::normalize_meta_term_ids_to_slugs( $meta_key, $val );
                $processed_values[] = $val;
            }

            $processed_meta[ $meta_key ] = $processed_values;
        }

        return $processed_meta;
    }

    /**
     * Normalize term IDs to term slugs inside meta structures for export.
     *
     * @param string $meta_key Meta key name.
     * @param mixed  $val      Meta value.
     *
     * @return mixed
     */
    private static function normalize_meta_term_ids_to_slugs( $meta_key, $val ) {
        $tax_map = array(
            '_rex_feed_cats'   => 'product_cat',
            'rex_feed_cats'    => 'product_cat',
            '_rex_feed_tags'   => 'product_tag',
            'rex_feed_tags'    => 'product_tag',
            '_rex_feed_brands' => 'product_brand',
            'rex_feed_brands'  => 'product_brand',
        );

        if ( isset( $tax_map[ $meta_key ] ) ) {
            $taxonomy = $tax_map[ $meta_key ];
            if ( is_array( $val ) ) {
                $slugs = array();
                foreach ( $val as $item ) {
                    $term = is_numeric( $item ) ? get_term( (int) $item, $taxonomy ) : get_term_by( 'slug', (string) $item, $taxonomy );
                    if ( $term && ! is_wp_error( $term ) ) {
                        $slugs[] = $term->slug;
                    } elseif ( is_string( $item ) && '' !== $item ) {
                        $slugs[] = $item;
                    }
                }
                return $slugs;
            } elseif ( is_numeric( $val ) ) {
                $term = get_term( (int) $val, $taxonomy );
                if ( $term && ! is_wp_error( $term ) ) {
                    return $term->slug;
                }
            }
        }

        if ( in_array( $meta_key, array( '_rex_feed_feed_config_filter', 'rex_feed_feed_config_filter', '_rex_feed_feed_config_rules', 'rex_feed_feed_config_rules' ), true ) && is_array( $val ) ) {
            foreach ( $val as $idx => $rule ) {
                if ( ! is_array( $rule ) || empty( $rule['if'] ) || ! isset( $rule['value'] ) ) {
                    continue;
                }

                $taxonomy = self::get_taxonomy_from_filter_if( $rule['if'] );
                if ( $taxonomy ) {
                    $rule_val = $rule['value'];
                    if ( is_array( $rule_val ) ) {
                        $new_vals = array();
                        foreach ( $rule_val as $v ) {
                            $term       = is_numeric( $v ) ? get_term( (int) $v, $taxonomy ) : get_term_by( 'slug', (string) $v, $taxonomy );
                            $new_vals[] = ( $term && ! is_wp_error( $term ) ) ? $term->slug : $v;
                        }
                        $val[ $idx ]['value'] = $new_vals;
                    } elseif ( is_numeric( $rule_val ) ) {
                        $term = get_term( (int) $rule_val, $taxonomy );
                        if ( $term && ! is_wp_error( $term ) ) {
                            $val[ $idx ]['value'] = $term->slug;
                        }
                    }
                }
            }
        }

        return $val;
    }

    /**
     * Resolve term slugs back to target site's integer term_ids.
     *
     * @param string $meta_key Meta key name.
     * @param mixed  $val      Meta value.
     * @param int    $post_id  Feed post ID.
     *
     * @return mixed
     */
    private static function resolve_meta_term_slugs_to_ids( $meta_key, $val, $post_id ) {
        $tax_map = array(
            '_rex_feed_cats'   => 'product_cat',
            'rex_feed_cats'    => 'product_cat',
            '_rex_feed_tags'   => 'product_tag',
            'rex_feed_tags'    => 'product_tag',
            '_rex_feed_brands' => 'product_brand',
            'rex_feed_brands'  => 'product_brand',
        );

        if ( isset( $tax_map[ $meta_key ] ) ) {
            $taxonomy = $tax_map[ $meta_key ];
            if ( is_array( $val ) ) {
                $term_ids   = array();
                $term_slugs = array();
                foreach ( $val as $item ) {
                    $term = is_numeric( $item ) ? get_term( (int) $item, $taxonomy ) : get_term_by( 'slug', (string) $item, $taxonomy );
                    if ( $term && ! is_wp_error( $term ) ) {
                        $term_ids[]   = $term->term_id;
                        $term_slugs[] = $term->slug;
                    }
                }
                if ( ! empty( $term_slugs ) ) {
                    wp_set_object_terms( $post_id, $term_slugs, $taxonomy, false );
                }
                return $term_ids;
            }
        }

        if ( in_array( $meta_key, array( '_rex_feed_feed_config_filter', 'rex_feed_feed_config_filter', '_rex_feed_feed_config_rules', 'rex_feed_feed_config_rules' ), true ) && is_array( $val ) ) {
            foreach ( $val as $idx => $rule ) {
                if ( ! is_array( $rule ) || empty( $rule['if'] ) || ! isset( $rule['value'] ) ) {
                    continue;
                }

                $taxonomy = self::get_taxonomy_from_filter_if( $rule['if'] );
                if ( $taxonomy ) {
                    $rule_val = $rule['value'];
                    if ( is_array( $rule_val ) ) {
                        $new_vals = array();
                        foreach ( $rule_val as $v ) {
                            $term       = is_numeric( $v ) ? get_term( (int) $v, $taxonomy ) : get_term_by( 'slug', (string) $v, $taxonomy );
                            $new_vals[] = ( $term && ! is_wp_error( $term ) ) ? (string) $term->term_id : $v;
                        }
                        $val[ $idx ]['value'] = $new_vals;
                    } elseif ( is_string( $rule_val ) && ! is_numeric( $rule_val ) && '' !== $rule_val ) {
                        $term = get_term_by( 'slug', $rule_val, $taxonomy );
                        if ( $term && ! is_wp_error( $term ) ) {
                            $val[ $idx ]['value'] = (string) $term->term_id;
                        }
                    }
                }
            }
        }

        return $val;
    }

    /**
     * Map filter 'if' key to corresponding WordPress taxonomy.
     *
     * @param string $if_key Filter if condition key.
     *
     * @return string|null
     */
    private static function get_taxonomy_from_filter_if( $if_key ) {
        $filter_tax_map = array(
            'product_cats'           => 'product_cat',
            'product_cat'            => 'product_cat',
            'product_tags'           => 'product_tag',
            'product_tag'            => 'product_tag',
            'product_brands'         => 'product_brand',
            'product_brand'          => 'product_brand',
            'product_shipping_class' => 'product_shipping_class',
        );

        return isset( $filter_tax_map[ $if_key ] ) ? $filter_tax_map[ $if_key ] : null;
    }

    /**
     * Export taxonomy terms using slugs so imports work across sites.
     *
     * @param int $feed_id Feed post ID.
     *
     * @return array
     */
    private static function get_exportable_feed_terms( $feed_id ) {
        $terms_by_taxonomy = array();

        foreach ( array( 'product_cat', 'product_tag', 'product_brand' ) as $taxonomy ) {
            if ( ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }

            $terms = wp_get_post_terms( $feed_id, $taxonomy, array( 'fields' => 'slugs' ) );

            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                continue;
            }

            $terms_by_taxonomy[ $taxonomy ] = array_values( array_map( 'strval', $terms ) );
        }

        return $terms_by_taxonomy;
    }

    /**
     * Persist imported feed meta values.
     *
     * @param int   $post_id Feed post ID.
     * @param array $meta    Meta values keyed by meta key.
     *
     * @return void
     */
    private static function import_feed_meta( $post_id, $meta ) {
        if ( ! is_array( $meta ) ) {
            return;
        }

        foreach ( $meta as $meta_key => $meta_values ) {
            if ( ! is_string( $meta_key ) || '' === $meta_key || in_array( $meta_key, self::get_excluded_feed_meta_keys(), true ) ) {
                continue;
            }

            $meta_values = is_array( $meta_values ) ? $meta_values : array( $meta_values );

            foreach ( $meta_values as $meta_value ) {
                if ( is_string( $meta_value ) ) {
                    $meta_value = maybe_unserialize( $meta_value );
                }

                $meta_value = self::resolve_meta_term_slugs_to_ids( $meta_key, $meta_value, $post_id );

                add_post_meta( $post_id, $meta_key, $meta_value );
            }
        }
    }

    /**
     * Persist imported taxonomy selections.
     *
     * @param int   $post_id Feed post ID.
     * @param array $terms   Taxonomy terms keyed by taxonomy.
     *
     * @return void
     */
    private static function import_feed_terms( $post_id, $terms ) {
        if ( ! is_array( $terms ) ) {
            return;
        }

        foreach ( $terms as $taxonomy => $term_slugs ) {
            if ( ! is_string( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }

            $term_slugs = is_array( $term_slugs ) ? $term_slugs : array( $term_slugs );
            $term_slugs = array_values( array_filter( array_map( 'sanitize_title', $term_slugs ) ) );

            wp_set_object_terms( $post_id, $term_slugs, $taxonomy, false );
        }
    }

    /**
     * Remove site-specific runtime state after importing a feed configuration.
     *
     * @param int $post_id Feed post ID.
     *
     * @return void
     */
    private static function reset_imported_feed_runtime_meta( $post_id ) {
        foreach ( self::get_excluded_feed_meta_keys() as $meta_key ) {
            delete_post_meta( $post_id, $meta_key );
        }
    }

    /**
     * Meta keys that should never be transferred between sites.
     *
     * @return array
     */
    private static function get_excluded_feed_meta_keys() {
        return array(
            '_edit_last',
            '_edit_lock',
            '_wp_old_slug',
            '_wp_trash_meta_status',
            '_wp_trash_meta_time',
            '_rex_feed_xml_file',
            'rex_feed_xml_file',
            '_rex_feed_preview_file',
            'rex_feed_preview_file',
            '_rex_feed_temp_xml_file',
            'rex_feed_temp_xml_file',
            '_rex_feed_status',
            'rex_feed_status',
            '_generation_start_time',
            '_rex_feed_last_active_time',
            '_rex_feed_google_data_feed_id',
            'rex_feed_google_data_feed_id',
            '_rex_mas_last_sync',
        );
    }

    /**
     * Resets Google API credentials.
     *
     * This function clears all stored Google API credentials including client ID, client secret, merchant ID,
     * and access tokens. This ensures a clean state when users want to re-authorize with different credentials.
     *
     * @param array $payload The payload array (not used, but required for AJAX handler compatibility).
     * @return void
     *
     * @since 7.4.21
     */
    public static function reset_google_api_credentials( $payload ) {
        // Delete all Google API related options
        delete_option( 'rex_google_client_id' );
        delete_option( 'rex_google_client_secret' );
        delete_option( 'rex_google_merchant_id' );
        delete_option( 'rex_google_access_token' );
        
        wp_send_json_success( array(
            'message' => __( 'All Google Merchant credentials and authorization have been cleared.', 'rex-product-feed' )
        ) );
    }

    /**
     * Dispatch manual feed generation to Action Scheduler background queue.
     * Returns immediately — does not wait for any batch to process.
     *
     * @param array $payload Feed payload from AJAX request.
     * @return array
     */
    public static function dispatch_feed_generation( $payload ) {
        // In AJAX mode, signal JS to continue driving batches itself.
        if ( 'ajax' === get_option( 'rex_feed_generation_mode', 'ajax' ) ) {
            return [ 'dispatched' => false ];
        }

        if ( !function_exists( 'as_schedule_single_action' ) || !function_exists( 'as_has_scheduled_action' ) ) {
            return [ 'dispatched' => false ];
        }

        $feed_id = !empty( $payload[ 'feed_id' ] ) ? (int) $payload[ 'feed_id' ] : 0;
        if ( !$feed_id ) {
            return [ 'dispatched' => false ];
        }

        // start_batch: caller already ran batches 1..(start_batch-1) synchronously.
        // We only schedule start_batch..total_batches here.
        $start_batch   = !empty( $payload[ 'start_batch' ] ) ? (int) $payload[ 'start_batch' ] : 1;
        $total_batches = !empty( $payload[ 'total_batches' ] ) ? (int) $payload[ 'total_batches' ] : 0;
        $per_batch     = !empty( $payload[ 'per_batch' ] ) ? (int) $payload[ 'per_batch' ] : 0;

        if ( !$total_batches || !$per_batch ) {
            $products_info = self::get_product_number( [ 'feed_id' => $feed_id ] );
            $per_batch     = !empty( $products_info[ 'per_batch' ] ) ? (int) $products_info[ 'per_batch' ] : 200;
            $total_batches = !empty( $products_info[ 'total_batch' ] ) ? (int) $products_info[ 'total_batch' ] : 1;
        }

        update_post_meta( $feed_id, '_rex_feed_total_batches', $total_batches );
        update_post_meta( $feed_id, '_rex_feed_current_batch', $start_batch - 1 );
        $generation_started_at = time();
        update_post_meta( $feed_id, '_generation_start_time', $generation_started_at );
        update_post_meta( $feed_id, '_rex_feed_last_active_time', $generation_started_at );
        Rex_Feed_Product_Count_Guard::begin_run( $feed_id, 'manual', $generation_started_at );

        if ( function_exists( 'as_unschedule_all_actions' ) ) {
            as_unschedule_all_actions( 'rex_feed_regenerate_feed_batch', [], "wpfm-feed-{$feed_id}" );
        }

        $offset = ( $start_batch - 1 ) * $per_batch;
        for ( $current_batch = $start_batch; $current_batch <= $total_batches; $current_batch++ ) {
            $data = [
                [
                    'feed_id'       => $feed_id,
                    'current_batch' => $current_batch,
                    'total_batches' => $total_batches,
                    'per_batch'     => $per_batch,
                    'offset'        => $offset,
                ],
            ];

            $scheduled = as_schedule_single_action( time(), 'rex_feed_regenerate_feed_batch', $data, 'wpfm-feed-' . $feed_id );
            if ( $start_batch === $current_batch && !is_wp_error( $scheduled ) && $scheduled ) {
                Rex_Product_Feed_Controller::update_feed_status( $feed_id, 'In queue', false );
            }

            $offset += $per_batch;
        }

        // Trigger AS async queue runner directly with current user session cookies.
        // This bypasses WP Cron entirely (works even when DISABLE_WP_CRON=true on local).
        // blocking=false + timeout=0.01 means we fire-and-forget without waiting.
        wp_remote_post(
            admin_url( 'admin-ajax.php' ),
            [
                'timeout'   => 0.01,
                'blocking'  => false,
                'body'      => [ 'action' => 'as_async_request_queue_runner' ],
                'cookies'   => isset( $_COOKIE ) ? $_COOKIE : [],
                'sslverify' => false,
            ]
        );

        // Also spawn WP cron as a secondary fallback.
        spawn_cron();

        return [
            'dispatched'    => true,
            'total_batches' => $total_batches,
        ];
    }

    /**
     * Return current feed generation status and batch progress for JS polling.
     *
     * @param array $payload Payload containing feed_id.
     * @return array
     */
    public static function get_feed_generation_status( $payload ) {
        $feed_id = !empty( $payload[ 'feed_id' ] ) ? (int) $payload[ 'feed_id' ] : 0;
        if ( !$feed_id || !get_post( $feed_id ) ) {
            return [ 'status' => 'error', 'message' => 'Invalid feed ID.' ];
        }

        $status        = get_post_meta( $feed_id, '_rex_feed_status', true ) ?: get_post_meta( $feed_id, 'rex_feed_status', true );
        $current_batch = (int) get_post_meta( $feed_id, '_rex_feed_current_batch', true );
        $total_batches = (int) get_post_meta( $feed_id, '_rex_feed_total_batches', true );

        $response = [
            'status'        => $status,
            'current_batch' => $current_batch,
            'total_batches' => $total_batches,
            'feed_url'      => '',
        ];

        if ( 'completed' === $status ) {
            $response[ 'feed_url' ] = get_post_meta( $feed_id, '_rex_feed_xml_file', true ) ?: get_post_meta( $feed_id, 'rex_feed_xml_file', true );
        } elseif ( 'failed' === $status ) {
            $last_error = get_post_meta( $feed_id, '_rex_feed_last_error', true );
            if ( is_array( $last_error ) && ! empty( $last_error['message'] ) ) {
                $response['message'] = $last_error['message'];
            } elseif ( is_string( $last_error ) && ! empty( $last_error ) ) {
                $response['message'] = $last_error;
            }
        }

        return $response;
    }

    /**
     * AJAX: manually clean up stale scheduled job records.
     *
     * @param array $payload Payload (unused, validation handled by middleware).
     * @return void
     */
    public static function cleanup_jobs( $payload ) {
        if ( ! class_exists( 'Rex_Feed_Job_Cleanup' ) ) {
            wp_send_json_error( array( 'message' => __( 'Cleanup service unavailable.', 'rex-product-feed' ) ) );
            wp_die();
        }

        $days    = absint( get_option( 'wpfm_job_history_retention_days', 30 ) );
        $cleanup = new Rex_Feed_Job_Cleanup();
        $deleted = $cleanup->cleanup( max( 1, $days ) );

        wp_send_json_success( array(
            'deleted'  => $deleted,
            'has_more' => $deleted >= Rex_Feed_Job_Cleanup::BATCH_LIMIT,
        ) );
        wp_die();
    }

    /**
     * AJAX: save the job history retention setting.
     *
     * @param array $payload Payload containing wpfm_job_history_retention_days.
     * @return void
     */
    public static function save_job_retention( $payload ) {
        $days = isset( $payload['wpfm_job_history_retention_days'] ) ? (int) $payload['wpfm_job_history_retention_days'] : 0;

        if ( $days < 1 ) {
            wp_send_json_error( array( 'message' => __( 'Retention period must be at least 1 day.', 'rex-product-feed' ) ) );
            wp_die();
        }

        update_option( 'wpfm_job_history_retention_days', $days );
        wp_send_json_success( array( 'saved' => $days ) );
        wp_die();
    }

    /**
     * Save the feed generation mode setting (ajax|scheduled_actions).
     *
     * @param array $payload Payload containing rex_feed_generation_mode.
     * @return void
     */
    public static function save_generation_mode( $payload ) {
        $mode = isset( $payload['rex_feed_generation_mode'] ) ? sanitize_key( $payload['rex_feed_generation_mode'] ) : '';
        $allowed = array( 'ajax', 'scheduled_actions' );

        if ( ! in_array( $mode, $allowed, true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid generation mode.', 'rex-product-feed' ) ) );
            wp_die();
        }

        update_option( 'rex_feed_generation_mode', $mode );
        wp_send_json_success( array( 'saved' => $mode ) );
        wp_die();
    }
}
