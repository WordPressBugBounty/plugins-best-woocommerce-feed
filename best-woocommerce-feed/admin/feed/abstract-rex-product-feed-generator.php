<?php
/**
 * Abstract Rex Product Feed Generator
 *
 * An abstract class definition that includes functions used for generating xml feed.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 * The XML Feed Generator.
 *
 * This is used to generate xml feed based on given settings.
 *
 * @since      1.0.0
 * @package    Rex_Product_Feed_Abstract_Generator
 * @author     RexTheme <info@rextheme.com>
 */
abstract class Rex_Product_Feed_Abstract_Generator
{

    /**
     * The feed Merchant.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator $merchant Contains merchant name of the feed.
     */
    public $merchant;
    /**
     * The feed rules containing all attributes and their value mappings for the feed.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator $feed_config Contains attributes and value mappings for the feed.
     */
    public $feed_config;
    /**
     * Append variation
     * product name
     *
     * @since    3.2
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $append_variation
     */
    public $append_variation;
    /**
     *
     * @var Rex_Product_Feed_Abstract_Generator $aelia_currency
     */
    public $aelia_currency;
    /**
     *
     * @var Rex_Product_Feed_Abstract_Generator $wmc_currency
     */
    public $wmc_currency;
    /**
     * @var $analytics
     */
    public $analytics;
    /**
     * @var $analytics_params
     */
    public $analytics_params = [];
    public $wcml_currency;
    public $wcml;
    public $product_meta_keys;
    public $product_condition;

    /**
     * The Product/Feed Config.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator    config    Feed config.
     */
    protected $config;
    /**
     * The Product/Feed ID.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator    id    Feed id.
     */
    protected $id;
    /**
     * Feed Title.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator    title    Feed title
     */
    protected $title;
    /**
     * Feed Description.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator    desc    Feed description.
     */
    protected $desc;
    /**
     * Feed Link.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator    link    Feed link.
     */
    protected $link;
    /**
     * The feed format.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator $feed_format Contains format of the feed.
     */
    protected $feed_format;
    /**
     * The feed filter rules containing all condition and values for the feed.
     *
     * @since    1.1.10
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator $feed_filters Contains condition and value for the feed.
     */
    protected $feed_filters;
    /**
     * The Product Query args to retrieve specific products for making the Feed.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator $products_args Contains products query args for feed.
     */
    protected $products_args;
    /**
     * Array contains all products.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator $products Contains all products to make feed.
     */
    protected $products;
    /**
     * Array contains all variable products for creating feed with variations.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator $products Contains all products to make feed.
     */
    protected $variable_products;
    /**
     * Array contains all variable products for creating feed with variations.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Product_Feed_Abstract_Generator $products Contains all products to make feed.
     */
    protected $grouped_products;
    /**
     * The Feed.
     * @since    1.0.0
     * @access   protected
     * @var Rex_Product_Feed_Abstract_Generator $feed Feed as text.
     */
    protected $feed;
    /**
     * Allowed Product
     *
     * @since    1.1.10
     * @access   private
     * @var      bool $allowed
     */
    protected $allowed;
    /**
     * Product Filter Condition
     *
     * @since    1.1.10
     * @access   private
     * @var      bool $allowed
     */
    protected $product_filter_condition;
    /**
     * Post per page
     *
     * @since    1.0.0
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $posts_per_page
     */
    protected $posts_per_page;
    /**
     * Product Scope
     *
     * @since    1.1.10
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $product_scope
     */
    protected $product_scope;
    /**
     * Product Offset
     *
     * @since    1.3.0
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $offset
     */
    protected $offset;
    /**
     * Product Current Batch
     *
     * @since    1.3.0
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $batch
     */
    protected $batch;
    /**
     * Product Total Batch
     *
     * @since    1.3.0
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $tbatch
     */
    protected $tbatch;
    /**
     * Bypass functionality from child
     *
     * @since    2.0.0
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $bypass
     */
    protected $bypass;
    /**
     * Variable Product include/exclude
     *
     * @since    2.0.1
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $variable_product
     */
    protected $variable_product;
    /**
     * Product variations include/exclude
     *
     * @since    2.0.1
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $variations
     */
    protected $variations;
    /**
     * parent product include/exclude
     *
     * @since    2.0.3
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $parent_product
     */
    protected $parent_product;
    /**
     * wpml enable
     *
     * @since    2.2.2
     * @access   private
     * @var      Rex_Product_Feed_Abstract_Generator $wpml_language
     */
    public $wpml_language;
    /**
     * enable logging
     *
     * @var Rex_Product_Feed_Abstract_Generator $is_logging_enabled
     */
    protected $is_logging_enabled;
    /**
     *
     * @var Rex_Product_Feed_Abstract_Generator $exclude_hidden_products
     */
    protected $exclude_hidden_products;
    /**
     *
     * @var Rex_Product_Feed_Abstract_Generator $rex_feed_skip_product
     */
    protected $rex_feed_skip_product;
    /**
     *
     * @var Rex_Product_Feed_Abstract_Generator $rex_feed_skip_row
     */
    protected $rex_feed_skip_row;
    /**
     *
     * @var Rex_Product_Feed_Abstract_Generator $feed_separator
     */
    protected $feed_separator;
    /**
     *
     * @var Rex_Product_Feed_Abstract_Generator $include_out_of_stock
     */
    protected $include_out_of_stock;

    protected $include_zero_priced;

    protected $feed_string_footer = '';

    protected $item_wrapper = '';

    public $feed_rules;

    protected $custom_filter_option;

    protected $custom_filter_var_exclude = false;

    /**
     * Variable to store country to retrieve
     * shipping and tax related values
     * @since 7.2.9
     * @var $feed_country
     */
    protected $feed_country;

    /**
     * Variable to store wrapper value for custom xml feed
     * @since 7.2.18
     * @var $custom_wrapper
     */
    protected $custom_wrapper;

    /**
     * Variable to store items wrapper value for custom xml feed
     * @since 7.2.18
     * @var $custom_items_wrapper
     */
    protected $custom_items_wrapper;

    /**
     * Variable to store wrapper element value for custom xml feed
     * @since 7.2.18
     * @var $custom_wrapper_el
     */
    protected $custom_wrapper_el;

    /**
     * Variable to store custom
     * xml file header option to exclude/include
     * @since 7.2.19
     * @var $custom_xml_header
     */
    protected $custom_xml_header;

    /**
     * Variable to store country to retrieve
     * shipping and tax related values
     * @since 7.2.9
     * @var $feed_zip_code
     */
    protected $feed_zip_code;

    /**
     * Variable to store
     * company name for yandex xml feed
     * @since 7.2.21
     * @var $yandex_company_name
     */
    protected $yandex_company_name;

    /**
     * Variable to store option to
     * include/exclude old price for yandex xml feed
     * @since 7.2.21
     * @var $yandex_company_name
     */
    protected $yandex_old_price;

    /**
     * @var bool
     */
    public $feed_rules_option;

    /**
     * @var array
     */
    public $custom_filter_args;

    /**
     * Hotline firm name
     *
     * @since 7.3.2
     * @var string
     */
    protected $hotline_firm_name;

    /**
     * Hotline firm id
     *
     * @since 7.3.2
     * @var string
     */
    protected $hotline_firm_id;

    /**
     * Hotline exchange rate
     *
     * @since 7.3.2
     * @var string
     */
    protected $hotline_exch_rate;

    /**
     * Polylang taxonomy ids
     *
     * @since 7.4.4
     * @var string
     */
    protected $polylang_taxonomy_ids = [];

    /**
     * Currency by WOOCS
     *
     * @since 7.4.15
     * @var string
     */
    public $woocs_currency = '';

	protected $is_google_content_api = false;

	/**
	 * TranslatePress language
	 *
	 * @since 7.4.20
	 * @var string
	 */
    public $translatepress_language = '';

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     * @param $config
     * @param $bypass
     * @since    1.0.0
     */
    public function __construct( $config, $bypass = false, $product_ids = array() )
    {
        $this->products           = [];
        $this->variable_products  = [];
        $this->grouped_products   = [];
        $this->config             = $config;
        $this->is_logging_enabled = is_wpfm_logging_enabled();
        $this->bypass             = $bypass;
        $this->merchant           = $config[ 'merchant' ] ?? '';
        $this->feed_format        = $config[ 'feed_format' ] ?? '';
        $this->wcml               = function_exists( 'wpfm_is_wcml_active' ) && wpfm_is_wcml_active();

        if ( $this->bypass ) {
            $wc_currency                   = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';
            $this->id                      = !empty( $config[ 'info' ][ 'post_id' ] ) ? $config[ 'info' ][ 'post_id' ] : 0;
            $this->title                   = !empty( $config[ 'info' ][ 'title' ] ) ? $config[ 'info' ][ 'title' ] : get_bloginfo();
            $this->desc                    = !empty( $config[ 'info' ][ 'desc' ] ) ? $config[ 'info' ][ 'desc' ] : get_bloginfo();
            $this->batch                   = !empty( $config[ 'info' ][ 'batch' ] ) ? (int)$config[ 'info' ][ 'batch' ] : 1;
            $this->tbatch                  = !empty( $config[ 'info' ][ 'total_batch' ] ) ? (int)$config[ 'info' ][ 'total_batch' ] : 1;
            $this->offset                  = isset( $config[ 'info' ][ 'offset' ] ) ? (int)$config[ 'info' ][ 'offset' ] : -1;
            $this->posts_per_page          = !empty( $config[ 'info' ][ 'per_page' ] ) ? (int)$config[ 'info' ][ 'per_page' ] : 200;
            $this->feed_config             = !empty( $config[ 'feed_config' ] ) ? $config[ 'feed_config' ] : [];
            $this->feed_filters            = !empty( $config[ 'feed_filter' ] ) ? $config[ 'feed_filter' ] : [];
            $this->feed_rules              = !empty( $config[ 'feed_rules' ] ) ? $config[ 'feed_rules' ] : [];
            $this->variations              = !empty( $config[ 'include_variations' ] ) ? $config[ 'include_variations' ] : '';
            $this->parent_product          = !empty( $config[ 'parent_product' ] ) ? $config[ 'parent_product' ] : '';
            $this->variable_product        = !empty( $config[ 'variable_product' ] ) ? $config[ 'variable_product' ] : '';
            $this->append_variation        = !empty( $config[ 'append_variations' ] ) ? $config[ 'append_variations' ] : '';
            $this->include_out_of_stock    = !empty( $config[ 'include_out_of_stock' ] ) && $config[ 'include_out_of_stock' ] === 'yes';
            $this->include_zero_priced     = !empty( $config[ 'include_zero_price_products' ] ) && $config[ 'include_zero_price_products' ] === 'yes';
            $this->exclude_hidden_products = !empty( $config[ 'exclude_hidden_products' ] ) ? $config[ 'exclude_hidden_products' ] : '';
            $this->feed_separator          = !empty( $config[ 'feed_separator' ] ) ? $config[ 'feed_separator' ] : '';
            $this->rex_feed_skip_product   = !empty( $config[ 'skip_product' ] ) ? $config[ 'skip_product' ] : false;
            $this->rex_feed_skip_row       = !empty( $config[ 'skip_row' ] ) ? $config[ 'skip_row' ] : false;
            $this->wpml_language           = !empty( $config[ 'wpml_language' ] ) ? $config[ 'wpml_language' ] : '';
            $this->wcml_currency           = !empty( $config[ 'wcml_currency' ] ) ? $config[ 'wcml_currency' ] : $wc_currency;
            $this->aelia_currency          = !empty( $config[ 'aelia_currency' ] ) ? $config[ 'aelia_currency' ] : $wc_currency;
            $this->wmc_currency            = !empty( $config[ 'wmc_currency' ] ) ? $config[ 'wmc_currency' ] : $wc_currency;
            $this->woocs_currency          = !empty( $config[ 'woocs_currency' ] ) ? $config[ 'woocs_currency' ] : $wc_currency;
            $this->analytics               = !empty( $config[ 'analytics' ] ) && ( 'yes' === $config[ 'analytics' ] || 'on' === $config[ 'analytics' ] );
            $this->analytics_params        = !empty( $config[ 'analytics_params' ] ) ? $config[ 'analytics_params' ] : '';
            $this->product_condition       = !empty( $config[ 'product_condition' ] ) ? $config[ 'product_condition' ] : '';
            $this->feed_country            = !empty( $config[ 'feed_country' ] ) ? $config[ 'feed_country' ] : '';
            $this->custom_wrapper          = !empty( $config[ 'custom_wrapper' ] ) ? $config[ 'custom_wrapper' ] : '';
            $this->custom_wrapper_el       = !empty( $config[ 'custom_wrapper_el' ] ) ? $config[ 'custom_wrapper_el' ] : '';
            $this->custom_items_wrapper    = !empty( $config[ 'custom_items_wrapper' ] ) ? $config[ 'custom_items_wrapper' ] : '';
            $this->feed_zip_code           = !empty( $config[ 'feed_zip_code' ] ) ? $config[ 'feed_zip_code' ] : '';
            $this->custom_xml_header       = !empty( $config[ 'custom_xml_header' ] ) ? $config[ 'custom_xml_header' ] : '';
            $this->yandex_company_name     = !empty( $config[ 'yandex_company_name' ] ) ? $config[ 'yandex_company_name' ] : '';
            $this->yandex_old_price        = !empty( $config[ 'yandex_old_price' ] ) ? $config[ 'yandex_old_price' ] : '';
            $this->hotline_firm_id         = !empty( $config[ 'hotline_firm_id' ] ) ? $config[ 'hotline_firm_id' ] : '';
            $this->hotline_firm_name       = !empty( $config[ 'hotline_firm_name' ] ) ? $config[ 'hotline_firm_name' ] : '';
            $this->hotline_exch_rate       = !empty( $config[ 'hotline_exch_rate' ] ) ? $config[ 'hotline_exch_rate' ] : '';
            $this->is_google_content_api   = !empty( $config[ 'is_google_content_api' ] ) ? $config[ 'is_google_content_api' ] : false;
	        $this->translatepress_language = !empty( $translatepress_language[ 'translatepress_language' ] ) ? $translatepress_language[ 'translatepress_language' ] : '';
	        $this->link                    = esc_url( home_url( '/' ) );

            if ( isset( $config[ 'custom_filter_option' ] ) && 'added' === $config[ 'custom_filter_option' ] ) {
                $this->custom_filter_option = true;
            }
            else {
                $this->custom_filter_option = false;
            }

            if ( isset( $config[ 'feed_rules_button' ] ) && 'added' === $config[ 'feed_rules_button' ] ) {
                $this->feed_rules_option = true;
            }
            else {
                $this->feed_rules_option = false;
            }

            $this->prepare_products_args( $config[ 'info' ] );
        }
        else {
            $this->setup_feed_data( $config[ 'info' ] );
            $this->setup_feed_configs( $config[ 'feed_config' ] );
            $this->setup_feed_meta( $config[ 'feed_config' ] );
            $this->setup_feed_filter_rules( $config[ 'feed_config' ] );
            if( 1 === $this->batch ) {
                $this->save_feed_meta( $config[ 'feed_config' ] );
            }
            $this->prepare_products_args( $config[ 'products' ] );
        }

        $this->setup_products();

        /**
         * log for feed
         */
        if ( $this->is_logging_enabled ) {
            $log = wc_get_logger();
            if ( $this->bypass ) {
                if ( $this->batch === 1 ) {
                    $log->info( __( 'Start feed processing job by cron', 'rex-product-feed' ), array( 'source' => 'WPFM', ) );
                    $log->info( 'Feed ID: ' . $config[ 'info' ][ 'post_id' ], array( 'source' => 'WPFM', ) );
                    $log->info( 'Feed Name: ' . $config[ 'info' ][ 'title' ], array( 'source' => 'WPFM', ) );
                    $log->info( 'Merchant Type: ' . $this->merchant, array( 'source' => 'WPFM', ) );
                }
                $log->info( 'Total Batches: ' . $this->batch, array( 'source' => 'WPFM', ) );
                $log->info( 'Current Batch: ' . $this->tbatch, array( 'source' => 'WPFM', ) );
            }
            else {
                if ( $this->batch === 1 ) {
                    $log->info( __( 'Start feed processing job.', 'rex-product-feed' ), array( 'source' => 'WPFM', ) );
                    $log->info( 'Feed ID: ' . $config[ 'info' ][ 'post_id' ], array( 'source' => 'WPFM', ) );
                    $log->info( 'Feed Name: ' . $config[ 'info' ][ 'title' ], array( 'source' => 'WPFM', ) );
                    $log->info( 'Merchant Type: ' . $this->merchant, array( 'source' => 'WPFM', ) );
                }
                $log->info( 'Total Batches: ' . $this->batch, array( 'source' => 'WPFM', ) );
                $log->info( 'Current Batch: ' . $this->tbatch, array( 'source' => 'WPFM', ) );
            }
        }

        if ( $this->tbatch == $this->batch ) {
            $wp_date_format = 'F j, Y';
            $wp_time_format = 'g:i a';
            update_post_meta( $this->id, 'updated', current_time( $wp_date_format . ' ' . $wp_time_format ) );
        }
    }

    /**
     * Prepare the Products Query args for retrieving  products.
     * @param $args
     */
    protected function prepare_products_args( $args )
    {
        $this->product_scope = $args[ 'products_scope' ];
        $post_types          = [ 'product' ];

        /**
         * Apply filters to fetch variation products for a feed.
         *
         * This method applies the 'rexfeed_fetch_variation_products' filter hook to allow customization
         * of the list of variation products fetched for the feed based on specific conditions.
         *
         * @param array $variations The array of variation products to be fetched for the feed.
         * @param int $feed_id The ID of the feed being processed.
         * @return array The filtered array of variation products to be fetched for the feed.
         *
         * @since 7.4.5
         */
        if ( apply_filters( 'rexfeed_fetch_variation_products', $this->variations, $this->id ) && 'skroutz' !== $this->merchant ) {
            $post_types[] = 'product_variation';
        }

        if ( $this->custom_filter_option ) {
            foreach ( $this->feed_filters as $filters ) {
                foreach( $filters as $filter ) {
                    $if = $filter[ 'if' ];

                    if ( $if === 'product_cats' || $if === 'product_tags' || $if === 'product_brands' ) {
                        unset( $post_types[ 1 ] );
                        $this->custom_filter_var_exclude = true;
                    }
                }
            }
        }

        $post_status = array( 'publish' );

        $wpfm_allow_private_products = get_option( 'wpfm_allow_private', 'no' );
        if ( $wpfm_allow_private_products === 'yes' ) {
            $post_status[] = 'private';
        }

        $this->products_args = array(
            'post_type'              => $post_types,
            'fields'                 => 'ids',
            'post_status'            => $post_status,
            'posts_per_page'         => $this->posts_per_page,
            'offset'                 => $this->offset,
            'orderby'                => 'ID',
            'order'                  => 'ASC',
            'post__in'               => array(),
            'post__not_in'           => get_option( 'rex_feed_abandoned_child_list', [] ),
            'update_post_term_cache' => true,
            'update_post_meta_cache' => true,
            'cache_results'          => false,
            'suppress_filters'       => false,
        );

        if ( $args[ 'products_scope' ] === 'product_cat' || $args[ 'products_scope' ] === 'product_tag' ) {
            $terms = $args[ 'products_scope' ] === 'product_tag' ? 'tags' : 'cats';
            $this->products_args[ 'post_type' ] = array( 'product' );

            if ( isset( $args[ $terms ] ) && is_array( $args[ $terms ] ) ) {
                $this->products_args[ 'tax_query' ][] = array(
                    'taxonomy' => $args[ 'products_scope' ],
                    'field'    => 'slug',
                    'terms'    => $args[ $terms ],
                );
                $this->products_args[ 'tax_query' ][ 'relation' ] = 'OR';

                if ( $this->batch === 1 ) {
                    wp_set_object_terms( $this->id, $args[ $terms ], $args[ 'products_scope' ] );
                }
            }
        }

        if ( $args[ 'products_scope' ] === 'product_filter' ) {

            $ids = get_post_meta( $this->id, '_rex_feed_product_filter_ids', true ) ?: get_post_meta( $this->id, 'rex_feed_product_filter_ids', true );

            if ( !$this->product_filter_condition ) {
                $condition     = get_post_meta( $this->id, '_rex_feed_product_condition' ) ?: get_post_meta( $this->id, 'rex_feed_product_condition' );
                $condition_str = implode( '', $condition );

                if ( is_array( $ids ) && !empty( $ids ) ) {
                    if ( $condition_str == 'inc' ) {
                        $this->products_args[ 'post__in' ] =  array_merge( $ids, $this->products_args[ 'post__in' ] );
                    }
                    else {
                        $this->products_args[ 'post__not_in' ] = array_merge( $ids, $this->products_args[ 'post__not_in' ] );
                    }
                }

            }
            else {

                if ( isset( $args[ 'data' ] ) && is_array( $args[ 'data' ] ) && !empty( $args[ 'data' ] ) ) {
                    if ( $this->product_filter_condition == 'inc' ) {

                        $this->products_args[ 'post__in' ] = array_merge( $args[ 'data' ], $this->products_args[ 'post__in' ] );
                    }
                    else {
                        $this->products_args[ 'post__not_in' ] = array_merge( $args[ 'data' ], $this->products_args[ 'post__not_in' ] );
                    }
                }
                else {
                    if ( is_array( $ids ) && !empty( $ids ) ) {
                        if ( $this->product_filter_condition == 'inc' ) {

                            $this->products_args[ 'post__in' ] =  array_merge( $ids, $this->products_args[ 'post__in' ] );
                        }
                        else {
                            $this->products_args[ 'post__not_in' ] = array_merge( $ids, $this->products_args[ 'post__not_in' ] );
                        }
                    }
                }
            }
        }

        if ( $args[ 'products_scope' ] === 'featured' ) {
            $this->products_args[ 'tax_query' ][] = array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'featured',
                'operator' => 'IN',
            );
        }
    }

    /**
     * Setup the Feed Related info
     * @param $info
     */
    protected function setup_feed_data( $info )
    {

        $this->tbatch         = isset( $info[ 'total_batch' ] ) ? (int) $info[ 'total_batch' ] : 1;
        $this->posts_per_page = isset( $info[ 'per_batch' ] ) ? $info[ 'per_batch' ] : 0;
        $this->id             = isset( $info[ 'post_id' ] ) ? $info[ 'post_id' ] : 0;
        $this->title          = isset( $info[ 'title' ] ) && '' !== $info[ 'title' ] ? $info[ 'title' ] : get_bloginfo();
        $this->desc           = isset( $info[ 'desc' ] ) && '' !== $info[ 'desc' ] ? $info[ 'desc' ] : get_bloginfo();
        $this->offset         = isset( $info[ 'offset' ] ) ? $info[ 'offset' ] : -1;
        $this->batch          = isset( $info[ 'batch' ] ) ? (int) $info[ 'batch' ] : 1;
        $this->link           = esc_url( home_url( '/' ) );
    }

    /**
     * Set up the rules
     * @param $info
     */
    protected function setup_feed_configs( $info )
    {
        $feed_rules = array();
        wp_parse_str( $info, $feed_rules );

        $this->product_scope = $feed_rules[ 'rex_feed_products' ];
        if ( !empty( $feed_rules[ 'rex_feed_analytics_params_options' ] ) ) {
            $this->analytics = 'yes' === $feed_rules[ 'rex_feed_analytics_params_options' ] || 'on' === $feed_rules[ 'rex_feed_analytics_params_options' ];
            if ( $this->analytics ) {
                $this->analytics_params = $feed_rules[ 'rex_feed_analytics_params' ] ?? [];
                if ( $this->batch === 1 ) {
                    update_post_meta( $this->id, '_rex_feed_analytics_params_options', $feed_rules[ 'rex_feed_analytics_params_options' ] );
                    update_post_meta( $this->id, '_rex_feed_analytics_params', $this->analytics_params );
                }
            }
        }


        if ( !empty( $feed_rules[ 'rex_feed_wcml_currency' ] ) ) {
            $this->wcml_currency = $feed_rules[ 'rex_feed_wcml_currency' ];
        }

        if ( function_exists( 'icl_object_id' ) ) {
            if ( !class_exists( 'Polylang' ) ) {
                $language = get_post_meta( $this->id, '_rex_feed_wpml_language', true ) ?: get_post_meta( $this->id, 'rex_feed_wpml_language', true );
                if ( $language ) {
                    $this->wpml_language = $language;
                }
                else {
                    $this->wpml_language = ICL_LANGUAGE_CODE;
                }

                if ( $this->batch === 1 ) {
                    update_post_meta( $this->id, '_rex_feed_wpml_language', ICL_LANGUAGE_CODE );
                }
            }
        }
        else {
            $this->wpml_language = false;
        }

        if ( wpfm_is_wcml_active() ) {
            $wcml_currency = $feed_rules[ 'rex_feed_wcml_currency' ] ?? '';
            update_post_meta( $this->id, '_rex_feed_wcml_currency', $wcml_currency );
        }

        $this->feed_config= $feed_rules[ 'fc' ] ?? [];

        // save the feed_rules into feed post_meta.
        if ( $this->batch === 1 ) {
            update_post_meta( $this->id, '_rex_feed_feed_config', $this->feed_config);
        }
    }

    /**
     * Setup the rules for filter
     * @param $info
     */
    protected function setup_feed_filter_rules( $info )
    {
        wp_parse_str( $info, $feed_rules_filters );

        if ( $this->custom_filter_option ) {
            $this->feed_filters = !empty( $feed_rules_filters[ 'ff' ] ) ? $feed_rules_filters[ 'ff' ] : array();

            reset( $this->feed_filters );
            $key = key( $this->feed_filters );
            unset( $this->feed_filters[ $key ] );

            // save the feed_rules_filter into feed post_meta.
            if ( $this->batch == 1 && !empty( $this->feed_filters ) ) {
                update_post_meta( $this->id, '_rex_feed_feed_config_filter', $this->feed_filters );
            }
        }

        if( $this->feed_rules_option ) {
            $this->feed_rules = !empty( $feed_rules_filters[ 'fr' ] ) ? $feed_rules_filters[ 'fr' ] : array();

            reset( $this->feed_rules );
            $key = key( $this->feed_rules );
            unset( $this->feed_rules[ $key ] );

            if( 1 == $this->batch && !empty( $this->feed_rules ) ) {
                update_post_meta( $this->id, '_rex_feed_feed_config_rules', array_values( $this->feed_rules ) );
            }
        }
    }

    /**
     * Setup the feed meta values
     *
     * @param $config
     */
    protected function setup_feed_meta( $config )
    {
        $feed_configs = [];
        $wc_currency  = get_woocommerce_currency();
        wp_parse_str( $config, $feed_configs );
	    $include_variable_product    = isset( $feed_configs[ 'rex_feed_variable_product' ] ) ? esc_attr( $feed_configs[ 'rex_feed_variable_product' ] ) : '';
	    $include_variations          = isset( $feed_configs[ 'rex_feed_variations' ] ) ? esc_attr( $feed_configs[ 'rex_feed_variations' ] ) : '';
	    $include_parent              = isset( $feed_configs[ 'rex_feed_parent_product' ] ) ? esc_attr( $feed_configs[ 'rex_feed_parent_product' ] ) : '';
	    $include_variations_name     = isset( $feed_configs[ 'rex_feed_variation_product_name' ] ) ? esc_attr( $feed_configs[ 'rex_feed_variation_product_name' ] ) : '';
	    $exclude_hidden_products     = isset( $feed_configs[ 'rex_feed_hidden_products' ] ) ? esc_attr( $feed_configs[ 'rex_feed_hidden_products' ] ) : '';
	    $rex_feed_skip_product       = isset( $feed_configs[ 'rex_feed_skip_product' ] ) ? esc_attr( $feed_configs[ 'rex_feed_skip_product' ] ) : '';
	    $rex_feed_skip_row           = isset( $feed_configs[ 'rex_feed_skip_row' ] ) ? esc_attr( $feed_configs[ 'rex_feed_skip_row' ] ) : '';
	    $include_out_of_stock        = isset( $feed_configs[ 'rex_feed_include_out_of_stock' ] ) ? esc_attr( $feed_configs[ 'rex_feed_include_out_of_stock' ] ) : '';
	    $include_zero_priced         = isset( $feed_configs[ 'rex_feed_include_zero_price_products' ] ) ? esc_attr( $feed_configs[ 'rex_feed_include_zero_price_products' ] ) : '';
	    $this->feed_separator        = isset( $feed_configs[ 'rex_feed_separator' ] ) ? esc_attr( $feed_configs[ 'rex_feed_separator' ] ) : '';
	    $this->aelia_currency        = isset( $feed_configs[ 'rex_feed_aelia_currency' ] ) ? esc_attr( $feed_configs[ 'rex_feed_aelia_currency' ] ) : 'USD';
	    $custom_filter_option        = isset( $feed_configs[ 'rex_feed_custom_filter_option_btn' ] ) ? esc_attr( $feed_configs[ 'rex_feed_custom_filter_option_btn' ] ) : 'removed';
	    $this->feed_country          = isset( $feed_configs[ 'rex_feed_feed_country' ] ) ? esc_attr( $feed_configs[ 'rex_feed_feed_country' ] ) : '';
	    $this->custom_wrapper        = isset( $feed_configs[ 'rex_feed_custom_wrapper' ] ) ? esc_attr( $feed_configs[ 'rex_feed_custom_wrapper' ] ) : '';
	    $this->custom_wrapper_el     = isset( $feed_configs[ 'rex_feed_custom_wrapper_el' ] ) ? esc_attr( $feed_configs[ 'rex_feed_custom_wrapper_el' ] ) : '';
	    $this->custom_items_wrapper  = isset( $feed_configs[ 'rex_feed_custom_items_wrapper' ] ) ? esc_attr( $feed_configs[ 'rex_feed_custom_items_wrapper' ] ) : '';
	    $this->feed_zip_code         = isset( $feed_configs[ 'rex_feed_zip_codes' ] ) ? esc_attr( $feed_configs[ 'rex_feed_zip_codes' ] ) : '';
	    $this->custom_xml_header     = isset( $feed_configs[ 'rex_feed_custom_xml_header' ] ) ? esc_attr( $feed_configs[ 'rex_feed_custom_xml_header' ] ) : '';
	    $this->yandex_company_name   = isset( $feed_configs[ 'rex_feed_yandex_company_name' ] ) ? esc_attr( $feed_configs[ 'rex_feed_yandex_company_name' ] ) : '';
	    $this->feed_rules_option     = isset( $feed_configs[ 'rex_feed_feed_rules_button' ] ) ? esc_attr( $feed_configs[ 'rex_feed_feed_rules_button' ] ) : 'removed';
	    $this->yandex_old_price      = isset( $feed_configs[ 'rex_feed_yandex_old_price' ] ) ? esc_attr( $feed_configs[ 'rex_feed_yandex_old_price' ] ) : '';
	    $this->hotline_firm_name     = isset( $feed_configs[ 'rex_feed_hotline_firm_name' ] ) ? esc_attr( $feed_configs[ 'rex_feed_hotline_firm_name' ] ) : '';
	    $this->hotline_firm_id       = isset( $feed_configs[ 'rex_feed_hotline_firm_id' ] ) ? esc_attr( $feed_configs[ 'rex_feed_hotline_firm_id' ] ) : '';
	    $this->hotline_exch_rate     = isset( $feed_configs[ 'rex_feed_hotline_exchange_rate' ] ) ? esc_attr( $feed_configs[ 'rex_feed_hotline_exchange_rate' ] ) : '';
	    $this->wcml_currency         = ! empty( $feed_configs[ 'rex_feed_wcml_currency' ] ) ? esc_html( $feed_configs[ 'rex_feed_wcml_currency' ] ) : $wc_currency;
	    $this->wmc_currency          = ! empty( $feed_configs[ 'rex_feed_wmc_currency' ] ) ? esc_html( $feed_configs[ 'rex_feed_wmc_currency' ] ) : $wc_currency;
	    $this->woocs_currency        = ! empty( $feed_configs[ 'rex_feed_woocs_currency' ] ) ? esc_html( $feed_configs[ 'rex_feed_woocs_currency' ] ) : $wc_currency;
	    $this->is_google_content_api = ! empty( $feed_configs[ 'rex_feed_is_google_content_api' ] ) && 'yes' === $feed_configs[ 'rex_feed_is_google_content_api' ] && 'google' === $this->merchant;
	    $this->yandex_old_price      = 'include' === $this->yandex_old_price;
        $this->translatepress_language = ! empty( $feed_configs[ 'rex_feed_translate_press_language' ] ) ? esc_html( $feed_configs[ 'rex_feed_translate_press_language' ] ) : '';

        if ( isset( $feed_configs[ 'product_filter_condition' ] ) ) {
            $this->product_filter_condition = $feed_configs[ 'product_filter_condition' ];
        }

        $this->variable_product        = 'yes' === $include_variable_product;
        $this->include_out_of_stock    = 'yes' === $include_out_of_stock;
        $this->variations              = 'yes' === $include_variations;
        $this->parent_product          = 'yes' === $include_parent;
        $this->append_variation        = 'yes' === $include_variations_name;
        $this->exclude_hidden_products = 'yes' === $exclude_hidden_products;
        $this->rex_feed_skip_product   = 'yes' === $rex_feed_skip_product;
        $this->rex_feed_skip_row       = 'yes' === $rex_feed_skip_row;
        $this->include_zero_priced     = 'yes' === $include_zero_priced;
        $this->custom_filter_option    = 'added' === $custom_filter_option;
        $this->feed_rules_option       = 'added' === $this->feed_rules_option;
    }

    /**
     * Saving feed meta into database
     * @param $config
     */
    protected function save_feed_meta( $config ) {
        if( !$this->bypass ) {
            Rex_Product_Feed_Controller::update_feed_status( $this->id, 'processing' );
        }

        $feed_configs = array();
        wp_parse_str( $config, $feed_configs );

        // Attribute Configs section STARTS.
        $config_keys = [
            'rex_feed_merchant',
            'rex_feed_separator',
            'rex_feed_google_destination',
            'rex_feed_google_target_country',
            'rex_feed_google_target_language',
            'rex_feed_google_schedule',
            'rex_feed_google_schedule_month',
            'rex_feed_google_schedule_week_day',
            'rex_feed_google_schedule_time',
            'rex_feed_ebay_seller_site_id',
            'rex_feed_ebay_seller_country',
            'rex_feed_ebay_seller_currency',
            'rex_feed_custom_wrapper',
            'rex_feed_custom_items_wrapper',
            'rex_feed_custom_wrapper_el',
            'rex_feed_custom_xml_header',
            'rex_feed_yandex_company_name',
            'rex_feed_yandex_old_price',
            'rex_feed_hotline_firm_id',
            'rex_feed_hotline_firm_name',
            'rex_feed_hotline_exchange_rate',
            'rex_feed_woocs_currency',
        ];

        foreach ( $config_keys as $key ) {
            if ( isset( $feed_configs[ $key ] ) ) {
                update_post_meta( $this->id, "_$key", $feed_configs[ $key ] );
            }
        }

        $filter_data = Rex_Product_Feed_Data_Handle::get_filter_drawer_data( $feed_configs );
        if( !empty( $filter_data ) ) {
            Rex_Product_Feed_Data_Handle::save_filter_drawer_data( $this->id, $filter_data );
        }

        $settings_data = Rex_Product_Feed_Data_Handle::get_settings_drawer_data( $feed_configs );
		if ( 'google' !== $this->merchant ) {
			unset( $settings_data[ 'rex_feed_is_google_content_api' ] );
		}
        if( !empty( $settings_data ) ) {
            Rex_Product_Feed_Data_Handle::save_settings_drawer_data( $this->id, $settings_data );
        }

		if ( $this->is_google_content_api ) {
			delete_post_meta( $this->id, '_rex_feed_xml_file' );
		}

        /**
         * Fires after saving settings drawer data
         *
         * @param string|int $this->id Feed id.
         * @param array $feed_configs Feed configurations.
         *
         * @since 7.3.1
         */
        do_action( 'rex_feed_after_feed_config_saved', $this->id, $feed_configs );
    }

    /**
     * Get the products to generate feed
     */
    protected function setup_products()
    {
        wpfm_switch_site_lang( $this->wpml_language, $this->wcml_currency );

        if ( isset( $this->products_args[ 'post__in' ] ) && $this->products_args[ 'post__in' ] && $this->product_filter_condition ) {
            update_post_meta( $this->id, '_rex_feed_product_condition', $this->product_filter_condition );
        }

        if ( $this->custom_filter_option ) {
            $this->custom_filter_args = wpfm_get_cached_data( "rexfeed_custom_filter_query_{$this->id}" );
            if( empty( $this->custom_filter_args ) ) {
                $this->custom_filter_args = Rex_Product_Filter::get_custom_filter_where_query( $this->feed_filters );
                wpfm_set_cached_data( "rexfeed_custom_filter_query_{$this->id}", $this->custom_filter_args );
            }
            if( $this->tbatch === $this->batch ) {
                wpfm_purge_cached_data( "rexfeed_custom_filter_query_{$this->id}" );
                wpfm_purge_cached_data( 'shipping_methods' );
            }
            add_filter( 'posts_where', array( $this, 'add_custom_filter_where_query' ) );
            add_filter( 'posts_join', array( $this, 'modify_join_query_for_custom_filter' ) );
        }

        add_filter( 'posts_distinct', array( $this, 'set_distinct' ) );
        add_filter( 'posts_where', array( $this, 'modify_where_query_for_multilingual_support' ) );
        add_filter( 'posts_join', array( $this, 'modify_join_query_for_polylang' ) );

        $result         = new WP_Query( $this->products_args );
        $this->products = $result->posts;

        if ( $this->custom_filter_option ) {
            remove_filter( 'posts_where', array( $this, 'add_custom_filter_where_query' ) );
            remove_filter( 'posts_join', array( $this, 'modify_join_query_for_custom_filter' ) );
        }
        remove_filter( 'posts_distinct', array( $this, 'set_distinct' ) );
        remove_filter( 'posts_where', array( $this, 'modify_where_query_for_multilingual_support' ) );
        remove_filter( 'posts_join', array( $this, 'modify_join_query_for_polylang' ) );

        if ( is_array( $this->products ) ) {
            $this->products = array_unique( $this->products );

            if ( $this->batch === 1 ) {
                update_post_meta( $this->id, '_rex_feed_product_ids', $this->products );
            }
            else {
                $product_ids = get_post_meta( $this->id, '_rex_feed_product_ids', true ) ?: get_post_meta( $this->id, 'rex_feed_product_ids', true );
                if ( $product_ids ) {
                    $prev_product_ids = $product_ids;
                    $product_ids      = array_merge( $prev_product_ids, $this->products );
                    update_post_meta( $this->id, '_rex_feed_product_ids', $product_ids );
                }
                else {
                    update_post_meta( $this->id, '_rex_feed_product_ids', $this->products );
                }
            }
        }
    }

    /**
     * Add custom where query for `Custom Filter` feature
     *
     * @param $where
     * @return mixed|string
     * @since 7.3.0
     */
    public function add_custom_filter_where_query( $where ) {
        if( !empty( $this->custom_filter_args[ 'where' ] ) ) {
            return "{$where} AND ({$this->custom_filter_args[ 'where' ]}) ";
        }
        return $where;
    }

    /**
     * Add custom join query for `Custom Filter` feature
     *
     * @param string $join Join query.
     * @return mixed|string
     *
     * @since 7.3.0
     */
    public function modify_join_query_for_custom_filter( $join ) {
        global $wpdb;
        $term_join = '';
        $meta_join = '';

        if( !empty( $this->custom_filter_args[ 'where' ] ) ) {
            $query = $this->custom_filter_args[ 'where' ];

            $term_join = wpfm_get_cached_data( "rexfeed_custom_filter_term_join_$this->id" );
            if( empty( $term_join ) && !empty( $this->custom_filter_args[ 'term_exists' ] ) ) {
                $total_join = preg_match_all('/RexTerm/i', $query);
                if( $total_join ) {
                    for( $i = 1; $i <= $total_join; $i++ ) {
                        $term_join .= " LEFT JOIN {$wpdb->term_relationships} AS RexTerm{$i}";
                        $term_join .= " ON ({$wpdb->posts}.ID = RexTerm{$i}.object_id) ";
                    }
                    wpfm_set_cached_data( "rexfeed_custom_filter_term_join_$this->id", $term_join );
                }
            }

            $meta_join = wpfm_get_cached_data( "rexfeed_custom_filter_meta_join_$this->id" );
            if( empty( $meta_join ) && !empty( $this->custom_filter_args[ 'meta_keys' ] ) ) {
                $total_meta = preg_match_all('/RexMeta/i', $query) / 2;
                if( $total_meta ) {
	                for( $i = 1; $i <= $total_meta; $i++ ) {
		                $meta_key = $this->custom_filter_args[ 'meta_keys' ][$i-1] ?? null;
                        $meta_join .= " LEFT JOIN {$wpdb->postmeta} AS RexMeta{$i}";
                        $meta_join .= " ON ({$wpdb->posts}.ID = RexMeta{$i}.post_id) ";
						if ( !empty( $meta_key ) ) {
							$meta_join .= " AND (RexMeta{$i}.meta_key = '{$meta_key}') ";
						}
                    }
                    wpfm_set_cached_data( "rexfeed_custom_filter_meta_join_$this->id", $meta_join );
                }
            }
        }

        if( $this->tbatch === $this->batch ) {
            wpfm_purge_cached_data( "rexfeed_custom_filter_term_join_$this->id" );
            wpfm_purge_cached_data( "rexfeed_custom_filter_meta_join_$this->id" );
        }

        return $join . $term_join . $meta_join;
    }

    /**
     * Modifies wordpress core query requests to DISTINCT results
     *
     * @param $join
     * @return string
     */
    public function set_distinct()
    {
        return 'DISTINCT';
    }


    /**
     * Customize where query for multilingual compatibility
     *
     * @param $where
     * @return array|mixed|string|string[]
     *
     * @since 7.3.0
     */
    public function modify_where_query_for_multilingual_support( $where ) {
        if( wpfm_is_wpml_active() ) {
            global $sitepress;
            $search  = "language_code = '" . $sitepress->get_default_language() . "'";
            $replace = "language_code = '" . $this->wpml_language . "'";
            $where   = str_replace( $search, $replace, $where );
        }
        if( wpfm_is_polylang_active() && $this->bypass ) {
            $this->polylang_taxonomy_ids = get_the_terms( $this->id, 'language' );
            $this->polylang_taxonomy_ids = is_array( $this->polylang_taxonomy_ids ) && !empty( $this->polylang_taxonomy_ids ) ? array_column( $this->polylang_taxonomy_ids, 'term_id' ) : [];
            $this->polylang_taxonomy_ids = implode( ', ', $this->polylang_taxonomy_ids );
            if ( !empty( $this->polylang_taxonomy_ids ) ) {
                $where .= " AND (RexPLL.term_taxonomy_id IN({$this->polylang_taxonomy_ids})) ";
            }
        }
        return $where;
    }

    /**
     * Modifies WordPress core join statements
     * in order to exclude variations with drafted/deleted parent
     *
     * @param $join
     *
     * @return string
     *
     * @since 7.3.0
     */
    public function modify_join_query_for_polylang( $join )
    {
        global $wpdb;
        if ( wpfm_is_polylang_active() && $this->bypass && !empty( $this->polylang_taxonomy_ids ) ) {
            $join .= " LEFT JOIN {$wpdb->term_relationships} AS RexPLL";
            $join .= " ON ({$wpdb->posts}.ID = RexPLL.object_id)";
            $this->polylang_taxonomy_ids = [];
        }
        return $join;
    }

    /**
     * Get product data
     * @param WC_Product $product
     * @param $product_meta_keys
     * @return array
     */
    protected function get_product_data( WC_Product $product, $product_meta_keys )
    {
        $retriever_class = 'Rex_Product_Data_Retriever';
        if ( class_exists( 'Rex_Product_Data_Retriever_Pro' ) ) {
            $retriever_class = 'Rex_Product_Data_Retriever_Pro';
        }
        if ( 'etsy' === $this->merchant ) {
            $retriever_class = 'Etsy_Data_Retriever';
        }

        $data     = new $retriever_class( $product, $this, $product_meta_keys );
        $all_data = $data->get_all_data();

        if ( $this->merchant === 'pinterest' && ( $this->feed_format === 'csv' ) ) {
            return $this->additional_img_link_pinterest( $all_data );
        }

        return $all_data;
    }

    /**
     * Converts all additional image link
     * as one string for pinterest.
     *
     * @param $data
     * @return mixed
     */
    protected function additional_img_link_pinterest( $data )
    {
        $additional_image_link_values = array();
        $additional_image_link_keys   = $this->preg_array_key_exists( '/^additional_image_link_/', $data );

        if ( !empty( $additional_image_link_keys ) ) {

            foreach ( $additional_image_link_keys as $key ) {
                $additional_image_link_values[] = $data[ $key ];
                unset( $data[ $key ] );
            }

            $additional_image_link_str       = implode( ', ', $additional_image_link_values );
            $data[ 'additional_image_link' ] = $additional_image_link_str;

            return $data;
        }
        return $data;
    }

    /**
     * Returns keys of an array with matching pattern.
     *
     * @param $pattern
     * @param $array
     * @return array|false
     */
    protected function preg_array_key_exists( $pattern, $array )
    {
        // extract the keys.
        $keys = array_keys( $array );

        // convert the preg_grep() returned array to int..and return.
        // the ret value of preg_grep() will be an array of values.
        // that match the pattern.
        return preg_grep( $pattern, $keys );
    }

    /**
     * Save the feed as XML file
     *
     * @return string
     */
    protected function save_feed( $format )
    {
        $publish_btn = get_post_meta( $this->id, '_rex_feed_publish_btn', true ) ?: get_post_meta( $this->id, 'rex_feed_publish_btn', true );

        if( 'rex-bottom-preview-btn' === $publish_btn ) {
            $feed_file_name = "preview-feed-{$this->id}";
            $feed_file_meta_key = '_rex_feed_preview_file';
        }
        else {
            $feed_file_name = "feed-{$this->id}";
            $feed_file_meta_key = '_rex_feed_xml_file';
        }

        $prev_feed_name = $this->get_prev_feed_file_name();

        $path    = wp_upload_dir();
        $baseurl = $path[ 'baseurl' ];
        $path    = $path[ 'basedir' ] . '/rex-feed';

        // make directory if not exist
        if ( !file_exists( $path ) ) {
            wp_mkdir_p( $path );
        }

        if ( $this->batch === $this->tbatch ) {
            if( !$this->bypass ) {
                Rex_Product_Feed_Controller::update_feed_status( $this->id, 'completed' );
            }
            if ( $this->is_logging_enabled ) {
                $log = wc_get_logger();
                $log->info( __( 'Completed feed generation job.', 'rex-product-feed' ), array( 'source' => 'WPFM', ) );
                $log->info( '**************************************************', array( 'source' => 'WPFM', ) );
            }
        }

        update_post_meta( $this->id, '_rex_feed_feed_format', $this->feed_format );
        update_post_meta( $this->id, '_rex_feed_separator', $this->feed_separator );

        if ( 'xml' === $format || 'rss' === $format ) {
            $file = trailingslashit( $path ) . "temp-{$feed_file_name}." . $format;

            $this->feed = wpfm_replace_special_char( $this->feed );

            if ( file_exists( $file ) ) {
                if ( $this->batch === 1 ) {
                    $feed = new DOMDocument;
                    $feed->loadXML( $this->feed );
                    $this->feed = $feed->saveXML( $feed, LIBXML_NOEMPTYTAG );

                    if ( $this->tbatch > 1 ) {
                        $this->footer_replace();
                    }
                    file_put_contents( $file, $this->feed );
                }
                else {
                    $feed = $this->get_items();
                    file_put_contents( $file, $feed, FILE_APPEND );
                }
            }
            else {
                if ( (int) $this->tbatch > 1 ) {
                    $this->footer_replace();
                }
                file_put_contents( $file, $this->feed, FILE_APPEND );
            }

            if ( $this->batch === $this->tbatch && file_exists( $file ) && function_exists( 'rename' ) ) {
                if ( function_exists( 'rex_feed_is_valid_xml' ) && rex_feed_is_valid_xml( $file, $this->id, $this->merchant ) ) {
                    rename( $file, trailingslashit( $path ) . "{$feed_file_name}.{$format}" );
                    delete_post_meta( $this->id, '_rex_feed_temp_xml_file' );
                    delete_post_meta( $this->id, 'rex_feed_temp_xml_file' );
                    update_post_meta( $this->id, $feed_file_meta_key,  "{$baseurl}/rex-feed/{$feed_file_name}.{$format}" );

                    if( 'publish' === $publish_btn ) {
                        $this->delete_prev_feed_file( "{$feed_file_name}.{$format}", $prev_feed_name, $path );
                    }
                }
                else {
                    update_post_meta( $this->id, '_rex_feed_temp_xml_file', "{$baseurl}/rex-feed/temp-{$feed_file_name}.{$format}" );
                    return 'false';
                }
            }
            return 'true';
        }
        elseif ( $format === 'text' ) {
            if( $this->feed ) {
                $file = trailingslashit( $path ) . "{$feed_file_name}.txt";

                if( (int) $this->batch === 1 && file_exists( $file ) ) {
                    unlink( $file );
                }

                if ( (int) $this->batch > 1 && file_exists( $file ) ) {
                    $header       = strtok( $this->feed, "\n" );
                    $saved        = file_get_contents( $file );
                    $saved_header = strtok( $saved, "\n" );

                    if( false !== strpos( $saved_header, $header ) ) {
                        $this->feed = substr( $this->feed, strpos( $this->feed, "\n" ) + 1 );
                    }
                }

                if( file_exists( $file ) ) {
                    if( $this->batch === 1 ) {
                        file_put_contents( $file, $this->feed );
                    }
                    else {
                        $feed = $this->feed;
                        if( $feed ) {
                            file_put_contents( $file, $feed, FILE_APPEND );
                        }
                    }
                }
                else {
                    file_put_contents( $file, $this->feed );
                }
            }

            if( $this->batch === $this->tbatch ) {
                if( 'publish' === $publish_btn ) {
                    $this->delete_prev_feed_file( "{$feed_file_name}.txt", $prev_feed_name, $path );
                }
                update_post_meta( $this->id, $feed_file_meta_key, $baseurl . "/rex-feed/{$feed_file_name}.txt" );
            }
            return 'true';
        }
        elseif ( $format === 'tsv' ) {
            $this->feed = iconv( "UTF-8", "Windows-1252//IGNORE", $this->feed );

            $file = trailingslashit( $path ) . "{$feed_file_name}.tsv";

            if ( file_exists( $file ) ) {
                if ( 1 === (int)$this->batch ) {
                    file_put_contents( $file, $this->feed );
                }
                else {
                    $feed = $this->feed;
                    $first_element = strtok($feed, "\n");
                    $feed = ltrim(str_replace( $first_element, '', $feed ));

                    if ( $feed ) {
                        file_put_contents( $file, $feed, FILE_APPEND );
                    }
                }
            }
            else {
                file_put_contents( $file, $this->feed );
            }
            if( $this->batch === $this->tbatch ) {
                if( 'publish' === $publish_btn ) {
                    $this->delete_prev_feed_file( "{$feed_file_name}.{$format}", $prev_feed_name, $path );
                }
                update_post_meta( $this->id, $feed_file_meta_key, $baseurl . "/rex-feed/{$feed_file_name}.tsv" );
            }
            return 'true';
        }
        elseif ( $format === 'csv' ) {
            $file = trailingslashit( $path ) . "{$feed_file_name}.csv";

            if( $this->batch === $this->tbatch ) {
                if( 'publish' === $publish_btn ) {
                    $this->delete_prev_feed_file( "{$feed_file_name}.{$format}", $prev_feed_name, $path );
                }
                update_post_meta( $this->id, $feed_file_meta_key, $baseurl . "/rex-feed/{$feed_file_name}.csv" );
            }

            return wpfm_generate_csv_feed( $this->feed, $file, $this->feed_separator, $this->batch );
        }
        else {
            $file = trailingslashit( $path ) . "{$feed_file_name}.xml";
            update_post_meta( $this->id, $feed_file_meta_key, $baseurl . "/rex-feed/{$feed_file_name}.xml" );

            $this->feed = wpfm_replace_special_char( $this->feed );

            if ( file_exists( $file ) ) {
                if ( $this->batch === 1 ) {
                    $this->footer_replace();
                    return file_put_contents( $file, $this->feed ) ? 'true' : 'false';
                }
                else {
                    $feed = $this->get_items();

                    if ( $this->merchant === 'google' && $this->feed_string_footer !== '' ) {
                        $request        = wp_remote_get($baseurl .'/rex-feed'.  "/{$feed_file_name}." . $format, array('sslverify' => FALSE));
                        if( is_wp_error( $request ) ) {
                            return 'false';
                        }
                        $file_contents  = wp_remote_retrieve_body( $request );
                        if ( !strpos( $file_contents, $this->item_wrapper ) ) {
                            $feed = '';
                        }
                    }

                    file_put_contents( $file, $feed, FILE_APPEND );
                    return 'true';
                }
            }
            else {
                return file_put_contents( $file, $this->feed ) ? 'true' : 'false';
            }
        }
    }

    /**
     * Get feed item as string
     *
     * @return string
     */
    public function get_items()
    {

        $feed = new DOMDocument;
        $feed->loadXML( $this->feed );

        $google_merchants = [
            'google', 'facebook', 'tiktok', 'twitter', 'pinterest', 'ciao', 'daisycon', 'instagram', 'liveintent',
            'google_shopping_actions', 'google_express', 'doofinder', 'emarts', 'epoq', 'google_local_products_inventory',
            'google_merchant_promotion', 'google_manufacturer_center', 'bing_image', 'rss', 'criteo', 'adcrowd',
            'google_local_inventory_ads', 'compartner', 'bing'
        ];

        if ( in_array( $this->merchant, $google_merchants ) ) {
            $node = $feed->getElementsByTagName( "item" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper       = '<item>';
                $this->feed_string_footer .= '</channel></rss>';
            }
        }
        elseif ( $this->merchant === 'ebay_mip' ) {
            if ( $feed->getElementsByTagName( "product" ) ) {
                $node = $feed->getElementsByTagName( "product" );
                $this->item_wrapper = '<product>';
            }
            else {
                $node = $feed->getElementsByTagName( "productVariationGroup" );
                $this->item_wrapper = '<productVariationGroup>';
            }
            if ( $this->batch === $this->tbatch ) {
                $this->feed_string_footer .= '</productRequest>';
            }
        }
        elseif ( $this->merchant === 'ceneo' ) {
            $node = $feed->getElementsByTagName( "o" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<o>';
                $this->feed_string_footer .= '</offers>';
            }
        }
        elseif ( $this->merchant === 'heureka'
            || $this->merchant === 'zbozi'
            || $this->merchant === 'rakuten'
            || $this->merchant === 'domodi'
            || $this->merchant === 'glami'
        ) {
            $node = $feed->getElementsByTagName( "SHOPITEM" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<SHOPITEM>';
                $this->feed_string_footer .= '</SHOP>';
            }
        }
        elseif ( $this->merchant === 'marktplaats' ) {
            $node = $feed->getElementsByTagName( "admarkt:ad" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<admarkt:ad>';
                $this->feed_string_footer .= '</admarkt:ads>';
            }
        }
        elseif ( $this->merchant === 'trovaprezzi' ) {
            $node = $feed->getElementsByTagName( "Offer" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<Offer>';
                $this->feed_string_footer .= '</Products>';
            }
        }
        elseif( $this->merchant === 'yandex'
            || $this->merchant === 'rozetka'
            || $this->merchant === 'admitad'
            || $this->merchant === 'ibud'
        ) {
            $node = $feed->getElementsByTagName( "offer" );
            if( $this->batch === $this->tbatch ) {
                $this->item_wrapper       = '<offer>';
                $this->feed_string_footer .= '</offers></shop></yml_catalog>';
            }
        }
        elseif ( $this->merchant === 'vivino' ) {
            $node = $feed->getElementsByTagName( "product" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<product>';
                $this->feed_string_footer .= '</vivino-product-list>';
            }
        }
        elseif ( $this->merchant === 'skroutz' ) {
            $node = $feed->getElementsByTagName( "product" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<product>';
                $this->feed_string_footer .= '</products></mywebstore>';
            }
        }
        elseif ( $this->merchant === 'google_review' ) {
            $node = $feed->getElementsByTagName( "review" );

            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<review>';
                $this->feed_string_footer .= '</reviews></feed>';
            }
        }
        elseif ( $this->merchant === 'drezzy'
            || $this->merchant === 'homedeco'
            || $this->merchant === 'fashiola'
            || $this->merchant === 'datatrics'
            || $this->merchant === 'listupp'
            || $this->merchant === 'adform'
            || $this->merchant === 'clubic'
            || $this->merchant === 'drm'
            || $this->merchant === 'job_board_io'
            || $this->merchant === 'kleding'
            || $this->merchant === 'shopalike'
            || $this->merchant === 'ladenzeile'
            || $this->merchant === 'winesearcher'
            || $this->merchant === 'whiskymarketplace'
        ) {
            $node = $feed->getElementsByTagName( "item" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<item>';
                $this->feed_string_footer .= '</items>';
            }
        }
        elseif ( $this->merchant === 'homebook' ) {
            $node = $feed->getElementsByTagName( "offer" );
            if( $this->batch === $this->tbatch ) {
                $this->item_wrapper       = '<offer>';
                $this->feed_string_footer .= '</offers>';
            }
        }
        elseif ( $this->merchant === 'emag' ) {
            $node = $feed->getElementsByTagName( "product" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<product>';
                $this->feed_string_footer .= '</shop>';
            }
        }
        elseif ( $this->merchant === 'grupo_zap' ) {
            $node = $feed->getElementsByTagName( "Listing" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<Listing>';
                $this->feed_string_footer .= '</Listings></ListingDataFeed>';
            }
        }
        elseif ( $this->merchant === 'lyst' ) {
            $node = $feed->getElementsByTagName( "item" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<item>';
                $this->feed_string_footer .= '</channel>';
            }
        }
        elseif ( $this->merchant === 'hertie' ) {
            $node = $feed->getElementsByTagName( "Artikel" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<Artikel>';
                $this->feed_string_footer .= '</Katalog>';
            }
        }
        elseif ( $this->merchant === 'leguide' || $this->merchant === 'whiskymarketplace' ) {
            $node = $feed->getElementsByTagName( "item" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<item>';
                $this->feed_string_footer .= '</products>';
            }
        }
        elseif ( $this->merchant === '123i' ) {
            $node = $feed->getElementsByTagName( "item" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<item>';
                $this->feed_string_footer .= '</Imoveis></Carga>';
            }
        }
        elseif ( $this->merchant === 'adtraction' || $this->merchant === 'webgains' ) {
            $node = $feed->getElementsByTagName( "item" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<item>';
                $this->feed_string_footer .= '</feed>';
            }
        }
        elseif ( $this->merchant === 'bloomville' ) {
            $node = $feed->getElementsByTagName( "CourseTemplate" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<CourseTemplate>';
                $this->feed_string_footer .= '</CourseTemplates>';
            }
        }
        elseif ( $this->merchant === 'custom' ) {
            $item_wrapper = !empty( $this->custom_wrapper ) ? $this->custom_wrapper : 'product';
            $node = $feed->getElementsByTagName( $item_wrapper );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = "</{$item_wrapper}>";
                $this->feed_string_footer .= '</products>';
                if( $this->custom_items_wrapper ) {
                    $this->feed_string_footer = "</{$this->custom_items_wrapper}>";
                }
                if( $this->custom_wrapper_el ) {
                    $this->feed_string_footer =  "</{$this->custom_wrapper_el}>{$this->feed_string_footer}";
                }
            }
        }
        elseif ( $this->merchant === 'domodi' ) {
            $node = $feed->getElementsByTagName( "SHOP" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<SHOP>';
                $this->feed_string_footer .= '</SHOPITEM>';
            }
        }
        elseif ( $this->merchant === 'incurvy' ) {
            $node = $feed->getElementsByTagName( "item" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<item>';
                $this->feed_string_footer .= '</produkte>';
            }
        }
        elseif ( $this->merchant === 'indeed' ) {
            $node = $feed->getElementsByTagName( "job" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<job>';
                $this->feed_string_footer .= '</source>';
            }
        }
        elseif ( $this->merchant === 'jobbird' ) {
            $node = $feed->getElementsByTagName( "job" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<job>';
                $this->feed_string_footer .= '</jobs>';
            }
        }
        elseif ( $this->merchant === 'joblift' ) {
            $node = $feed->getElementsByTagName( "job" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<job>';
                $this->feed_string_footer .= '</feed>';
            }
        }
        elseif ( $this->merchant === 'ibud' ) {
            $node = $feed->getElementsByTagName( "shop" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<shop>';
                $this->feed_string_footer .= '</shop>';
            }
        }
        elseif ( $this->merchant === 'mirakl' ) {
            $node = $feed->getElementsByTagName( "offer" );
            if ( $this->batch == $this->tbatch ) {
                $this->item_wrapper = '<offer>';
                $this->feed_string_footer .= '</offers></import>';
            }
        }
        elseif ( $this->merchant === 'spartooFr' ) {
            $node = $feed->getElementsByTagName( "product" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<product>';
                $this->feed_string_footer .= '</products></root>';
            }
        }
        elseif ( $this->merchant === 'Bestprice' ) {
            $node = $feed->getElementsByTagName( "product" );
            if ( $this->batch === $this->tbatch ) {
                $this->item_wrapper = '<product>';
                $this->feed_string_footer .= '</products></store>';
            }
        }
        elseif ( $this->merchant === 'DealsForU' ) {
            $node = $feed->getElementsByTagName( "offer" );
            if( $this->batch === $this->tbatch ) {
                $this->item_wrapper       = '<offer>';
                $this->feed_string_footer .= '</offers></import>';
            }
        }
        elseif ($this->merchant === 'gulog_gratis') {
            $node = $feed->getElementsByTagName("ad");

            if($this->batch === $this->tbatch) {
                $this->item_wrapper = '<ad>';
                $this->feed_string_footer .= '</ads>';
            }
        }elseif ($this->merchant === 'zap_co_il') {
            $node = $feed->getElementsByTagName("PRODUCT");

            if($this->batch === $this->tbatch) {
                $this->item_wrapper = '<PRODUCT>';
                $this->feed_string_footer .= '</PRODUCTS></STORE>';
            }
        }elseif ($this->merchant === 'hotline') {
            $node = $feed->getElementsByTagName("item");

            if($this->batch === $this->tbatch) {
                $this->item_wrapper = '<item>';
                $this->feed_string_footer .= '</items></price>';
            }
        }
        elseif ($this->merchant === 'heureka_availability') {
            $node = $feed->getElementsByTagName("item");

            if($this->batch === $this->tbatch) {
                $this->item_wrapper = '<item>';
                $this->feed_string_footer .= '</item_list>';
            }
        }
        else {
            $node = $feed->getElementsByTagName( "product" );
            if( $this->batch === $this->tbatch ) {
                $this->item_wrapper       = '<product>';
                $this->feed_string_footer .= '</products>';
            }
        }
        $str = '';

        if ( !empty( $node ) ) {
            for ( $i = 0; $i < $node->length; $i++ ) {
                $item = $node->item( $i );
                if ( $item != NULL ) {
                    $str .= $feed->saveXML( $item, LIBXML_NOEMPTYTAG );
                }
            }
        }

        $str .= $this->feed_string_footer;

        return $str;
    }

    /**
     * Gets the feed format of current feed
     *
     * @return mixed|Rex_Product_Feed_Abstract_Generator
     */
    public function get_feed_format() {
        return $this->feed_format;
    }

    /**
     * Gets selected country for the feed
     *
     * @return mixed|string
     * @since 7.2.9
     */
    public function get_shipping() {
        return $this->feed_country;
    }

    /**
     * Gets zip code country for the feed
     *
     * @return mixed|string
     * @since 7.2.18
     */
    public function get_zip_code() {
        return $this->feed_zip_code;
    }

    /**
     * Get previously save for the current feed
     *
     * @return string
     * @since 7.2.12
     */
    private function get_prev_feed_file_name() {
        $prev_feed_url = get_post_meta( $this->id, '_rex_feed_xml_file', true ) ?: get_post_meta( $this->id, 'rex_feed_xml_file', true );

        $feed_file_name = explode( '/', $prev_feed_url );
        return $feed_file_name[ array_key_last( $feed_file_name ) ];
    }

    /**
     * Delete previous feed file incase of new feed title/format
     *
     * @param $new_name
     * @param $prev_name
     * @param $path
     * @return void
     * @since 7.2.12
     */
    private function delete_prev_feed_file( $new_name, $prev_name, $path ) {
        if( $prev_name && is_string( $prev_name ) && $prev_name !== $new_name ) {
            $file_name = trailingslashit( $path ) . $prev_name;
            if( file_exists( $file_name ) ) {
                unlink( $file_name );
            }
        }
    }

    /**
     * Check if the product is out of stock
     *
     * @param WC_Product $product
     * @return bool
     */
    protected function is_out_of_stock( $product ) {
        if ( ( !$this->include_out_of_stock )
            && ( !$product->is_in_stock()
                || $product->is_on_backorder()
                || (is_integer($product->get_stock_quantity()) && 0 >= $product->get_stock_quantity())
            )
        ) {
            return false;
        }
        return true;
    }

    /**
     * Responsible for creating the feed
     *
     * @return string
     **/
    abstract public function make_feed();

    /**
     * Responsible for replacing feed's footer in every batch
     *
     * @return string
     **/
    abstract public function footer_replace();
}