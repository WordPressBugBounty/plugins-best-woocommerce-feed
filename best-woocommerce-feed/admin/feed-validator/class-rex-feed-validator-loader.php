<?php
/**
 * Feed Validator Loader
 *
 * Handles loading and initialization of the feed validation system.
 *
 * @since 7.4.58
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Feed Validator Loader.
 *
 * This class handles:
 * - Loading validator class files
 * - Registering AJAX handlers
 * - Integration with feed generation
 *
 * @since 7.4.58
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validator_Loader {

    /**
     * The single instance of the class.
     *
     * @since 7.4.58
     * @access protected
     * @var    Rex_Feed_Validator_Loader
     */
    protected static $instance = null;

    /**
     * Validation enabled flag.
     *
     * @since 7.4.58
     * @access protected
     * @var    bool
     */
    protected $validation_enabled = true;

    /**
     * Main instance.
     *
     * @since 7.4.58
     * @access public
     * @return Rex_Feed_Validator_Loader
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @since 7.4.58
     */
    public function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required dependencies.
     *
     * @since 7.4.58
     * @access protected
     * @return void
     */
    protected function load_dependencies() {
        $validator_path = plugin_dir_path( __FILE__ );

        // Load abstract validator first
        require_once $validator_path . 'abstract-rex-feed-validator.php';

        // Load results handler
        require_once $validator_path . 'class-rex-feed-validation-results.php';

        // Load factory
        require_once $validator_path . 'class-rex-feed-validator-factory.php';

        // Load Google validator (others will be loaded on-demand by factory)
        require_once $validator_path . 'class-rex-feed-validator-google.php';

        // Load Facebook validator
        require_once $validator_path . 'class-rex-feed-validator-facebook.php';

        // Load Instagram validator
        require_once $validator_path . 'class-rex-feed-validator-instagram.php';

        // Load OpenAI validator
        require_once $validator_path . 'class-rex-feed-validator-openai.php';
    }

    /**
     * Initialize hooks.
     *
     * @since 7.4.58
     * @access protected
     * @return void
     */
    protected function init_hooks() {
        // AJAX handlers
        add_action( 'wp_ajax_rex_feed_validate_feed', array( $this, 'ajax_validate_feed' ) );
        add_action( 'wp_ajax_rex_feed_get_validation_results', array( $this, 'ajax_get_validation_results' ) );
        add_action( 'wp_ajax_rex_feed_clear_validation_results', array( $this, 'ajax_clear_validation_results' ) );
        add_action( 'wp_ajax_rex_feed_export_validation_results', array( $this, 'ajax_export_validation_results' ) );

        // Integration with feed generation
        add_action( 'rex_feed_after_product_processed', array( $this, 'validate_product_during_generation' ), 10, 4 );
        add_action( 'rex_feed_after_generation_complete', array( $this, 'finalize_validation' ), 10, 2 );

        // Auto-validate when scheduled feed generation completes
        add_action( 'rex_product_feed_scheduler_generate', array( $this, 'auto_validate_on_schedule' ), 10, 1 );

        // Admin menu/UI integration
        add_filter( 'rex_feed_product_feed_tabs', array( $this, 'add_validation_tab' ) );
        add_action( 'rex_feed_after_feed_updated', array( $this, 'auto_clear_and_run_validation_on_feed_update' ), 10, 1 );
    }

    /**
     * Auto-validate feed when scheduled generation completes.
     *
     * @since 7.4.58
     * @access public
     * @param int $feed_id The feed ID.
     * @return void
     */
    public function auto_validate_on_schedule( $feed_id ) {
        if ( ! $feed_id ) {
            return;
        }

        // Check if this is a Google feed (only Google validation supported currently)
        $merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );
        if ( empty( $merchant ) ) {
            $merchant = get_post_meta( $feed_id, 'rex_feed_merchant', true );
        }

        // Only auto-validate for supported merchants
        $supported_merchants = array( 'google', 'google_shopping', 'google_local', 'google_local_inventory', 'facebook', 'facebook_marketplace', 'instagram', 'instagram_shopping', 'openai', 'openai_commerce' );
        $merchant_normalized = strtolower( str_replace( ' ', '_', $merchant ) );
        
        if ( ! in_array( $merchant_normalized, $supported_merchants, true ) ) {
            return;
        }

        // Run validation
        $this->run_validation( $feed_id );
    }

    /**
     * Run validation for a feed.
     *
     * @since 7.4.58
     * @access public
     * @param int $feed_id The feed ID.
     * @return array|false Validation results or false on failure.
     */
    public function run_validation( $feed_id ) {
        $validator = Rex_Feed_Validator_Factory::create_from_feed( $feed_id );
        
        if ( ! $validator ) {
            return false;
        }

        // Get feed products data
        $products_data = $this->get_feed_products_data( $feed_id );

        if ( empty( $products_data ) ) {
            return false;
        }

        // Validate each product
        $all_errors = array();
        $max_errors_to_keep = 500; // Limit memory usage

        foreach ( $products_data as $product_data ) {
            $errors = $validator->validate_product(
                $product_data['product_id'],
                $product_data['attributes'],
                $product_data['title']
            );
            $all_errors = array_merge( $all_errors, $errors );
            
            // Prevent memory exhaustion by truncating errors during processing
            if ( count( $all_errors ) > $max_errors_to_keep ) {
                // Prioritize errors over warnings
                $error_items = array_filter( $all_errors, function( $item ) {
                    return ( $item['severity'] ?? '' ) === 'error';
                } );
                $warning_items = array_filter( $all_errors, function( $item ) {
                    return ( $item['severity'] ?? '' ) === 'warning';
                } );
                $info_items = array_filter( $all_errors, function( $item ) {
                    return ( $item['severity'] ?? '' ) === 'info';
                } );
                
                // Keep errors first, then warnings, then info
                $all_errors = array_merge(
                    array_slice( $error_items, 0, (int) ( $max_errors_to_keep * 0.6 ) ),
                    array_slice( $warning_items, 0, (int) ( $max_errors_to_keep * 0.3 ) ),
                    array_slice( $info_items, 0, (int) ( $max_errors_to_keep * 0.1 ) )
                );
                
                // Free memory
                unset( $error_items, $warning_items, $info_items );
            }
        }

        // Get summary
        $summary = $validator->get_validation_summary( $all_errors );

        // Save results
        $results_handler = new Rex_Feed_Validation_Results( $feed_id );
        $results_handler->save_results( $all_errors, $summary );


        return $all_errors;
    }

    /**
     * AJAX handler to validate a feed.
     *
     * @since 7.4.58
     * @access public
     * @return void
     */
    public function ajax_validate_feed() {

        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $feed_id = isset( $_POST['feed_id'] ) ? absint( $_POST['feed_id'] ) : 0;


        if ( ! $feed_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
        }

        // Get merchant info
        $merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );
        if ( empty( $merchant ) ) {
            $merchant = get_post_meta( $feed_id, 'rex_feed_merchant', true );
        }

        $validator = Rex_Feed_Validator_Factory::create_from_feed( $feed_id );

        if ( ! $validator ) {
            wp_send_json_error( array(
                'message' => sprintf(
                    __( 'Validation is not available for %s feeds yet.', 'rex-product-feed' ),
                    ucfirst( $merchant )
                ),
            ) );
        }

        // Get feed config and products
        $products_data = $this->get_feed_products_data( $feed_id );

        if ( empty( $products_data ) ) {
            $feed_format = get_post_meta( $feed_id, '_rex_feed_feed_format', true );
            if ( empty( $feed_format ) ) {
                $feed_format = get_post_meta( $feed_id, 'rex_feed_feed_format', true );
            }
            
            
            wp_send_json_error( array( 
                'message' => __( 'No products found. Please generate the feed first before validating.', 'rex-product-feed' ) 
            ) );
        }


        // Run validation
        $all_errors = array();
        $processed_count = 0;
        $max_errors_to_keep = 500; // Limit memory usage by keeping only top errors

        foreach ( $products_data as $product_data ) {
            $errors = $validator->validate_product(
                $product_data['product_id'],
                $product_data['attributes'],
                $product_data['title'] // Pass display title (includes variation info)
            );
            $all_errors = array_merge( $all_errors, $errors );
            $processed_count++;
            
            // Prevent memory exhaustion by truncating errors during processing
            if ( count( $all_errors ) > $max_errors_to_keep ) {
                // Prioritize errors over warnings
                $error_items = array_filter( $all_errors, function( $item ) {
                    return ( $item['severity'] ?? '' ) === 'error';
                } );
                $warning_items = array_filter( $all_errors, function( $item ) {
                    return ( $item['severity'] ?? '' ) === 'warning';
                } );
                $info_items = array_filter( $all_errors, function( $item ) {
                    return ( $item['severity'] ?? '' ) === 'info';
                } );
                
                // Keep errors first, then warnings, then info
                $all_errors = array_merge(
                    array_slice( $error_items, 0, (int) ( $max_errors_to_keep * 0.6 ) ),
                    array_slice( $warning_items, 0, (int) ( $max_errors_to_keep * 0.3 ) ),
                    array_slice( $info_items, 0, (int) ( $max_errors_to_keep * 0.1 ) )
                );
                
                // Free memory
                unset( $error_items, $warning_items, $info_items );
            }
        }


        // Get summary
        $summary = $validator->get_validation_summary( $all_errors );
        
        
        // Save results
        $results_handler = new Rex_Feed_Validation_Results( $feed_id );
        $results_handler->save_results( $all_errors, $summary );

        wp_send_json_success( array(
            'message'       => __( 'Validation complete.', 'rex-product-feed' ),
            'summary'       => $summary,
            'total_errors'  => count( $all_errors ),
        ) );
    }

    /**
     * AJAX handler to get validation results.
     *
     * @since 7.4.58
     * @access public
     * @return void
     */
    public function ajax_get_validation_results() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $feed_id  = isset( $_POST['feed_id'] ) ? absint( $_POST['feed_id'] ) : 0;
        $page     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 50;
        $filters  = isset( $_POST['filters'] ) ? array_map( 'sanitize_text_field', (array) $_POST['filters'] ) : array();

        if ( ! $feed_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
        }

        $results_handler = new Rex_Feed_Validation_Results( $feed_id );

        $paginated        = $results_handler->get_paginated_results( $page, $per_page, $filters );
        $summary          = $results_handler->get_summary(); // Original full summary
        $has_filters      = ! empty( array_filter( $filters ) );
        
        // Use filtered summary only when filters are active
        // Otherwise use the full summary which has all counts
        $filtered_summary = $has_filters ? $results_handler->get_filtered_summary( $filters ) : $summary;
        $total_products   = $results_handler->get_total_products_validated();
        $is_truncated     = $results_handler->is_truncated();
        $total_issues     = $results_handler->get_total_issues_count();

        wp_send_json_success( array(
            'results'                => $paginated,
            'summary'                => $summary,
            'filtered_summary'       => $filtered_summary,
            'total_products'         => $total_products,
            'has_filters'            => $has_filters,
            'is_truncated'           => $is_truncated,
            'total_issues'           => $total_issues,
            'is_display_truncated'   => $paginated['is_display_truncated'],
            'total_before_limit'     => $paginated['total_before_limit'],
            'display_limit'          => $paginated['display_limit'],
            'last_validated'         => $results_handler->get_last_validated(),
            'attribute_summary'      => $results_handler->get_attribute_summary(),
            'rule_summary'           => $results_handler->get_rule_summary(),
            'top_products'           => $results_handler->get_top_problematic_products(),
        ) );
    }

    /**
     * AJAX handler to clear validation results.
     *
     * @since 7.4.58
     * @access public
     * @return void
     */
    public function ajax_clear_validation_results() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $feed_id = isset( $_POST['feed_id'] ) ? absint( $_POST['feed_id'] ) : 0;

        if ( ! $feed_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
        }

        $results_handler = new Rex_Feed_Validation_Results( $feed_id );
        $results_handler->clear_results();

        wp_send_json_success( array( 'message' => __( 'Validation results cleared.', 'rex-product-feed' ) ) );
    }

    /**
     * AJAX handler to export validation results.
     *
     * @since 7.4.58
     * @access public
     * @return void
     */
    public function ajax_export_validation_results() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $feed_id = isset( $_POST['feed_id'] ) ? absint( $_POST['feed_id'] ) : 0;
        $format  = isset( $_POST['format'] ) ? sanitize_text_field( $_POST['format'] ) : 'csv';
        $filters = isset( $_POST['filters'] ) ? array_map( 'sanitize_text_field', (array) $_POST['filters'] ) : array();

        if ( ! $feed_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
        }

        $results_handler = new Rex_Feed_Validation_Results( $feed_id );

        if ( $format === 'json' ) {
            $content = $results_handler->export_to_json( $filters );
            $mime    = 'application/json';
            $ext     = 'json';
        } else {
            $content = $results_handler->export_to_csv( $filters );
            $mime    = 'text/csv';
            $ext     = 'csv';
        }

        $filename = 'validation-results-' . $feed_id . '-' . date( 'Y-m-d-His' ) . '.' . $ext;

        wp_send_json_success( array(
            'content'  => $content,
            'filename' => $filename,
            'mime'     => $mime,
        ) );
    }

    /**
     * Validate product during feed generation.
     *
     * @since 7.4.58
     * @access public
     * @param  int    $product_id   The product ID.
     * @param  array  $product_data The product data/attributes.
     * @param  string $merchant     The merchant name.
     * @param  int    $feed_id      The feed ID.
     * @return void
     */
    public function validate_product_during_generation( $product_id, $product_data, $merchant, $feed_id ) {
        if ( ! $this->validation_enabled ) {
            return;
        }

        // Check if validation is supported for this merchant
        if ( ! Rex_Feed_Validator_Factory::is_supported( $merchant ) ) {
            return;
        }

        // Get or create validator instance
        static $validators = array();
        $cache_key = $feed_id . '_' . $merchant;

        if ( ! isset( $validators[ $cache_key ] ) ) {
            $validators[ $cache_key ] = Rex_Feed_Validator_Factory::create( $merchant, $feed_id );
        }

        $validator = $validators[ $cache_key ];

        if ( ! $validator ) {
            return;
        }

        // Ensure product_data has title included for validation
        if ( ! isset( $product_data['title'] ) ) {
            $product = wc_get_product( $product_id );
            $product_data['title'] = $product ? $product->get_name() : '';
        }

        // Validate product
        $errors = $validator->validate_product( $product_id, $product_data );

        // Store errors in transient for batch processing
        $transient_key = 'rex_feed_validation_' . $feed_id;
        $stored_errors = get_transient( $transient_key );

        if ( ! is_array( $stored_errors ) ) {
            $stored_errors = array();
        }

        $stored_errors = array_merge( $stored_errors, $errors );

        // Limit stored errors to prevent memory issues
        if ( count( $stored_errors ) > Rex_Feed_Validation_Results::MAX_STORED_ERRORS ) {
            $stored_errors = array_slice( $stored_errors, 0, Rex_Feed_Validation_Results::MAX_STORED_ERRORS );
        }

        set_transient( $transient_key, $stored_errors, HOUR_IN_SECONDS );
    }

    /**
     * Finalize validation after feed generation is complete.
     *
     * @since 7.4.58
     * @access public
     * @param  int   $feed_id       The feed ID.
     * @param  array $feed_stats    Optional feed statistics.
     * @return void
     */
    public function finalize_validation( $feed_id, $feed_stats = array() ) {
        $transient_key = 'rex_feed_validation_' . $feed_id;
        $errors        = get_transient( $transient_key );

        if ( ! is_array( $errors ) ) {
            return;
        }

        // Get validator for summary generation
        $merchant  = get_post_meta( $feed_id, '_rex_feed_merchant', true );
        if ( empty( $merchant ) ) {
            $merchant = get_post_meta( $feed_id, 'rex_feed_merchant', true );
        }
        $validator = Rex_Feed_Validator_Factory::create( $merchant, $feed_id );

        if ( $validator ) {
            $summary = $validator->get_validation_summary( $errors );
        } else {
            $summary = array(
                'total_errors'   => count( array_filter( $errors, function( $e ) { return $e['severity'] === 'error'; } ) ),
                'total_warnings' => count( array_filter( $errors, function( $e ) { return $e['severity'] === 'warning'; } ) ),
                'total_info'     => count( array_filter( $errors, function( $e ) { return $e['severity'] === 'info'; } ) ),
            );
        }

        // Save results
        $results_handler = new Rex_Feed_Validation_Results( $feed_id );
        $results_handler->save_results( $errors, $summary );

        // Clean up transient
        delete_transient( $transient_key );
    }

    /**
     * Add validation tab to feed edit page.
     *
     * @since 7.4.58
     * @access public
     * @param  array $tabs The existing tabs.
     * @return array
     */
    public function add_validation_tab( $tabs ) {
        $tabs['validation'] = array(
            'title' => __( 'Validation', 'rex-product-feed' ),
            'icon'  => 'dashicons-yes-alt',
        );
        return $tabs;
    }

    /**
     * Get feed products data for validation.
     * Uses the actual products from the generated feed file, not the stored product IDs.
     * This ensures validation only includes products that passed all filters.
     *
     * @since 7.4.58
     * @access protected
     * @param  int $feed_id The feed ID.
     * @return array
     */
    protected function get_feed_products_data( $feed_id ) {
        $products_data = array();

        // Get feed configuration
        $feed_config = get_post_meta( $feed_id, '_rex_feed_feed_config', true );
        if ( empty( $feed_config ) ) {
            $feed_config = get_post_meta( $feed_id, 'rex_feed_feed_config', true );
        }

        $merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );
        if ( empty( $merchant ) ) {
            $merchant = get_post_meta( $feed_id, 'rex_feed_merchant', true );
        }

        $feed_format = get_post_meta( $feed_id, '_rex_feed_feed_format', true );
        if ( empty( $feed_format ) ) {
            $feed_format = get_post_meta( $feed_id, 'rex_feed_feed_format', true );
        }

        if ( empty( $feed_config ) || ! is_array( $feed_config ) ) {
            return $products_data;
        }

        // Try to parse actual product data (including attributes) from the generated feed file
        $parsed_products = $this->get_products_data_from_feed_file( $feed_id, $feed_format );
        
        if ( ! empty( $parsed_products ) ) {
            $processed_count = 0;
            foreach ( $parsed_products as $product_id => $attributes ) {
                $product = wc_get_product( $product_id ); // Still need product object for display title
                $product_title = $product ? $product->get_name() : ( $attributes['title'] ?? 'Product #' . $product_id );
                $display_title = $product_title;

                if ( $product && $product->is_type( 'variation' ) ) {
                    $parent_product = wc_get_product( $product->get_parent_id() );
                    $parent_title = $parent_product ? $parent_product->get_name() : $product_title;
                    $display_title = sprintf( '%s - Variation | Child ID: #%d', $parent_title, $product_id );
                }

                $products_data[] = array(
                    'product_id' => $product_id,
                    'title'      => $display_title,
                    'attributes' => $attributes,
                );
                $processed_count++;
            }
            return $products_data;
        }

        // Fallback: Use stored IDs and build attributes manually if feed file parsing fails
        $product_ids = get_post_meta( $feed_id, '_rex_feed_product_ids', true );
        if ( empty( $product_ids ) ) {
            $product_ids = get_post_meta( $feed_id, 'rex_feed_product_ids', true );
        }

        if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
            return $products_data;
        }

        // Limit for performance
        $product_ids = array_slice( $product_ids, 0, 500 );

        foreach ( $product_ids as $product_id ) {
            $product = wc_get_product( $product_id );
            if ( ! $product ) continue;

            $attributes = $this->build_product_attributes( $product, $feed_config, $feed_id );
            $product_title = $product->get_name();
            $display_title = $product_title;
            $is_variation  = $product->is_type( 'variation' );
            
            if ( $is_variation ) {
                $parent_product = wc_get_product( $product->get_parent_id() );
                $parent_title = $parent_product ? $parent_product->get_name() : $product_title;
                $display_title = sprintf( '%s - Variation | Child ID: #%d', $parent_title, $product_id );
            }

            if ( ! isset( $attributes['title'] ) ) {
                $attributes['title'] = $product_title;
            }

            $products_data[] = array(
                'product_id'    => $product_id,
                'title'         => $display_title,
                'is_variation'  => $is_variation,
                'parent_id'     => $is_variation ? $product->get_parent_id() : 0,
                'attributes'    => $attributes,
            );
        }
        
        return $products_data;
    }

    /**
     * Build product attributes from feed configuration.
     *
     * @since 7.4.58
     * @param  int         $feed_id     The feed ID.
     * @param  array       $feed_config The feed configuration.
     * @return array
     */
    protected function build_product_attributes( $product, $feed_config, $feed_id ) {
        $attributes = array();
        
        // For variations, get parent product to inherit missing values
        $parent_product = null;
        if ( $product->is_type( 'variation' ) ) {
            $parent_product = wc_get_product( $product->get_parent_id() );
        }

        // Collect all attributes mapped in the feed config for this merchant
        $all_possible_attrs = array();
        $google_category_meta_key = 'wpfm_google_product_category_default';
        foreach ( $feed_config as $config ) {
            if ( empty( $config ) || ! is_array( $config ) ) {
                continue;
            }
            $attr_name = $config['attr'] ?? '';
            if ( !empty( $attr_name ) ) {
                $all_possible_attrs[] = $attr_name;
            }
            // Capture google_product_category meta key if present
            if ( $attr_name === 'google_product_category' && !empty($config['meta_key']) ) {
                $google_category_meta_key = $config['meta_key'];
            }
        }

        // Always ensure these essential attributes are present for validation
        $essentials = array('id', 'title', 'description', 'link', 'price', 'availability', 'image_link', 'google_product_category');
        $all_possible_attrs = array_unique( array_merge( $all_possible_attrs, $essentials ) );

        // Populate all possible attributes
        foreach ( $all_possible_attrs as $attr_name ) {
            // Try to find config for this attr
            $config = null;
            foreach ( $feed_config as $c ) {
                if ( isset( $c['attr'] ) && $c['attr'] === $attr_name ) {
                    $config = $c;
                    break;
                }
            }
            $type = $config['type'] ?? 'attribute';
            $meta_key = $config['meta_key'] ?? $attr_name;
            $value = '';
            if ( $type === 'static' && isset( $config['st_value'] ) ) {
                $value = $config['st_value'];
            } else {
                // Special handling for GTIN: try all possible meta keys
                if ( $attr_name === 'gtin' ) {
                    $gtin_keys = array('gtin', 'ean', 'upc', 'isbn');
                    foreach ( $gtin_keys as $gtin_key ) {
                        $gtin_value = $this->get_product_value( $product, $gtin_key );
                        if ( !empty($gtin_value) ) {
                            $value = $gtin_value;
                            break;
                        }
                    }
                }
                // Special handling for google_product_category: use correct meta key
                elseif ( $attr_name === 'google_product_category' ) {
                    $value = $this->get_product_value( $product, $google_category_meta_key );
                    
                    // Fallback to Category Mapping if meta value is empty
                    if ( empty( $value ) ) {
                        $value = $this->get_category_mapping_value( $product, $feed_id );
                    }
                } else {
                    $value = $this->get_product_value( $product, $meta_key );
                }
            }
            $attributes[ $attr_name ] = $value;
        }

        // For variations: inherit parent values if empty
        if ( $parent_product ) {
            // Description inheritance
            $desc_value = trim( $attributes['description'] ?? '' );
            if ( empty( $desc_value ) ) {
                $parent_desc = $parent_product->get_description();
                if ( empty( $parent_desc ) ) {
                    $parent_desc = $parent_product->get_short_description();
                }
                if ( ! empty( $parent_desc ) ) {
                    $attributes['description'] = $parent_desc;
                }
            }
            
            // Availability inheritance
            $avail_value = trim( $attributes['availability'] ?? '' );
            if ( empty( $avail_value ) ) {
                $parent_stock = $parent_product->get_stock_status();
                if ( ! empty( $parent_stock ) ) {
                    $attributes['availability'] = $parent_stock;
                }
            }
        }
        
        // ALWAYS ensure availability has a valid value (even for simple products)
        if ( empty( trim( $attributes['availability'] ?? '' ) ) ) {
            // Last resort: check if product is actually in stock
            if ( $product->is_in_stock() ) {
                $attributes['availability'] = 'instock';
            } else {
                $attributes['availability'] = 'outofstock';
            }
        }
        
        // ALWAYS ensure description has a value
        if ( empty( trim( $attributes['description'] ?? '' ) ) ) {
            $attributes['description'] = $product->get_name();
        }
        
        // Normalize availability format (instock -> in stock)
        if ( ! empty( $attributes['availability'] ) ) {
            $availability = strtolower( $attributes['availability'] );
            $availability_map = array(
                'instock'     => 'in stock',
                'in_stock'    => 'in stock',
                'outofstock'  => 'out of stock',
                'out_of_stock' => 'out of stock',
                'onbackorder' => 'preorder',
                'backorder'   => 'preorder',
            );
            if ( isset( $availability_map[ $availability ] ) ) {
                $attributes['availability'] = $availability_map[ $availability ];
            }
        }
        
        // Special handling for price: if it's "0" or "0.00", treat as empty and use fallback
        if ( isset( $attributes['price'] ) ) {
            $price_numeric = (float) preg_replace( '/[^0-9.]/', '', $attributes['price'] );
            if ( $price_numeric <= 0 ) {
                $fallback_price = $product->get_regular_price();
                if ( ! empty( $fallback_price ) && (float) $fallback_price > 0 ) {
                    $attributes['price'] = $fallback_price;
                }
            }
            
            // Add currency if price doesn't already have it
            if ( ! empty( $attributes['price'] ) && ! preg_match( '/[A-Z]{3}/', $attributes['price'] ) ) {
                $currency = get_woocommerce_currency();
                $attributes['price'] = $attributes['price'] . ' ' . $currency;
            }
        }

        return $attributes;
    }

    /**
     * Get product value by meta key.
     *
     * Simplified value retrieval for validation purposes.
     *
     * @since 7.4.58
     * @access protected
     * @param  WC_Product $product  The product.
     * @param  string     $meta_key The meta key.
     * @return mixed
     */
    protected function get_product_value( $product, $meta_key ) {
        if ( empty( $meta_key ) ) {
            return '';
        }

        // Handle common product properties
        switch ( $meta_key ) {
            case 'id':
            case 'product_id':
                return $product->get_id();

            case 'title':
            case 'product_title':
            case 'name':
                return $product->get_name();

            case 'description':
            case 'product_description':
                $description = $product->get_description();
                // For variations without description, inherit from parent
                if ( ( empty( $description ) || trim( $description ) === '' ) && $product->is_type( 'variation' ) ) {
                    $parent = wc_get_product( $product->get_parent_id() );
                    if ( $parent ) {
                        $description = $parent->get_description();
                        // If parent description is also empty, try short description
                        if ( empty( $description ) || trim( $description ) === '' ) {
                            $description = $parent->get_short_description();
                        }
                    }
                }
                // If still empty, use product name as fallback
                if ( empty( $description ) || trim( $description ) === '' ) {
                    $description = $product->get_name();
                }
                return $description;

            case 'short_description':
                return $product->get_short_description();

            case 'sku':
            case 'product_sku':
                return $product->get_sku();

            case 'link':
            case 'product_link':
            case 'url':
                return $product->get_permalink();

            case 'image_link':
            case 'main_image':
            case 'featured_image':
                $image_id = $product->get_image_id();
                return $image_id ? wp_get_attachment_url( $image_id ) : '';

            case 'price':
            case 'regular_price':
                return $product->get_regular_price();

            case 'sale_price':
                return $product->get_sale_price();

            case 'availability':
            case 'stock_status':
                $stock_status = $product->get_stock_status();
                // For variations without stock status, inherit from parent
                if ( ( empty( $stock_status ) || $stock_status === '' ) && $product->is_type( 'variation' ) ) {
                    $parent = wc_get_product( $product->get_parent_id() );
                    if ( $parent ) {
                        $stock_status = $parent->get_stock_status();
                    }
                }
                // Ensure we have a value, default to 'instock' if still empty
                if ( empty( $stock_status ) ) {
                    $stock_status = 'instock';
                }
                return $stock_status;

            case 'brand':
                // Try common brand taxonomies/meta
                $brand = '';
                $taxonomies = array( 'product_brand', 'pa_brand', 'pwb-brand' );
                foreach ( $taxonomies as $tax ) {
                    $terms = get_the_terms( $product->get_id(), $tax );
                    if ( $terms && ! is_wp_error( $terms ) ) {
                        $brand = $terms[0]->name;
                        break;
                    }
                }
                if ( empty( $brand ) ) {
                    $brand = get_post_meta( $product->get_id(), '_brand', true );
                }
                return $brand;

            case 'gtin':
            case 'ean':
            case 'upc':
            case 'isbn':
                $val = get_post_meta( $product->get_id(), '_' . $meta_key, true );
                if ( empty( $val ) ) {
                    $val = get_post_meta( $product->get_id(), $meta_key, true );
                }
                return $val;

            case 'mpn':
                return get_post_meta( $product->get_id(), '_mpn', true );

            case 'condition':
                $condition = get_post_meta( $product->get_id(), '_condition', true );
                return $condition ?: 'new';

            default:
                // Try as post meta
                $value = get_post_meta( $product->get_id(), $meta_key, true );
                if ( empty( $value ) ) {
                    $value = get_post_meta( $product->get_id(), '_' . $meta_key, true );
                }
                return $value;
        }
    }

    /**
     * Parse the feed file to extract product data (ID and attributes).
     *
     * @since 7.4.60
     * @access protected
     * @param  int    $feed_id     The feed ID.
     * @param  string $feed_format The feed format.
     * @return array Array of products: array( ID => attributes_array )
     */
    protected function get_products_data_from_feed_file( $feed_id, $feed_format ) {
        $products_data = array();

        // Try to get feed file URL from meta
        $feed_url = get_post_meta( $feed_id, '_rex_feed_xml_file', true );
        if ( empty( $feed_url ) ) {
            $feed_url = get_post_meta( $feed_id, 'rex_feed_xml_file', true );
        }

        if ( ! empty( $feed_url ) ) {
            $upload_dir      = wp_upload_dir();
            $upload_base_url = $upload_dir['baseurl'];
            $upload_base_dir = $upload_dir['basedir'];

            // Handle protocol mismatches (http vs https) by stripping it
            $stripped_base_url = preg_replace( '(^https?:)', '', $upload_base_url );
            $stripped_feed_url = preg_replace( '(^https?:)', '', $feed_url );

            $feed_file = str_replace( $stripped_base_url, $upload_base_dir, $stripped_feed_url );
        } else {
            // Fallback: try to construct path from feed name
            $feed_name = get_post_meta( $feed_id, '_rex_feed_name', true );
            if ( empty( $feed_name ) ) {
                $feed_name = get_post_meta( $feed_id, 'rex_feed_name', true );
            }
            
            if ( empty( $feed_name ) ) {
                // Last resort: use default pattern
                $feed_name = 'feed-' . $feed_id;
            }

            $upload_dir = wp_upload_dir();
            $feed_file = $upload_dir['basedir'] . '/rex-feed/' . $feed_name . '.' . strtolower( $feed_format );
        }

        
        if ( ! file_exists( $feed_file ) ) {
            return $products_data;
        }

        try {
            switch ( strtoupper( $feed_format ) ) {
                case 'XML':
                    $products_data = $this->parse_xml_feed_for_data( $feed_file );
                    break;

                case 'CSV':
                case 'TSV':
                case 'TXT':
                case 'TEXT':
                    $products_data = $this->parse_csv_feed_for_data( $feed_file, $feed_format );
                    break;
                
                default:
                    break;
            }
        } catch ( Exception $e ) {
            error_log( 'Error parsing feed file for validation: ' . $e->getMessage() );
        }
        
        return $products_data;
    }

    /**
     * Parse XML feed file to extract product data.
     *
     * @since 7.4.60
     * @access protected
     * @param  string $feed_file Path to the XML feed file.
     * @return array Array of products: array( ID => attributes_array )
     */
    protected function parse_xml_feed_for_data( $feed_file ) {
        $products_data = array();

        // Load XML file with LIBXML_NOCDATA to automatically strip CDATA wrappers
        // This extracts the text content from <![CDATA[...]]> tags
        libxml_use_internal_errors( true );
        $xml = simplexml_load_file( $feed_file, 'SimpleXMLElement', LIBXML_NOCDATA );

        if ( $xml === false ) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            libxml_use_internal_errors( false );
            return $products_data;
        }

        // Register namespaces for Google Shopping feeds
        $namespaces = $xml->getNamespaces( true );
        
        foreach ( $namespaces as $prefix => $ns ) {
            if ( !empty( $prefix ) ) {
                $xml->registerXPathNamespace( $prefix, $ns );
            } elseif ( $ns === 'http://base.google.com/ns/1.0' ) {
                // If Google namespace is default, still register 'g' for XPath convenience
                $xml->registerXPathNamespace( 'g', $ns );
            }
        }
        
        // Common paths for product entries
        $product_paths = array(
            '//g:item',            // Google namespace (now registered)
            '//item',              // RSS feed format
            '//entry',             // Atom feed format
            '//product',           // Generic XML format
            '//offer',             // Yandex feed format
        );

        // Try to find products using XPath
        foreach ( $product_paths as $path ) {
            $products = $xml->xpath( $path );
            
            if ( ! empty( $products ) ) {
                foreach ( $products as $product ) {
                    $id = $this->extract_id_from_xml_product( $product, $namespaces );
                    
                    if ( $id ) {
                        // Get namespaces from the product element itself, not just root
                        $product_namespaces = $product->getNamespaces( true );
                        // Merge with root namespaces
                        $all_namespaces = array_merge( $namespaces, $product_namespaces );
                        
                        $attributes = $this->extract_attributes_from_xml_product( $product, $all_namespaces );
                        $products_data[ intval( $id ) ] = $attributes;
                    }
                }
                break;
            }
        }

        // If XPath didn't work, try direct children for common structures
        if ( empty( $products_data ) ) {
            // Check for channel/item structure (RSS)
            if ( isset( $xml->channel ) && isset( $xml->channel->item ) ) {
                foreach ( $xml->channel->item as $item ) {
                    $id = $this->extract_id_from_xml_product( $item, $namespaces );
                    if ( $id ) {
                        // Get namespaces from the item element itself
                        $item_namespaces = $item->getNamespaces( true );
                        $all_namespaces = array_merge( $namespaces, $item_namespaces );
                        
                        $attributes = $this->extract_attributes_from_xml_product( $item, $all_namespaces );
                        $products_data[ intval( $id ) ] = $attributes;
                    }
                }
            }
            // Check for direct items/products
            elseif ( isset( $xml->item ) ) {
                foreach ( $xml->item as $item ) {
                    $id = $this->extract_id_from_xml_product( $item, $namespaces );
                    if ( $id ) {
                        $item_namespaces = $item->getNamespaces( true );
                        $all_namespaces = array_merge( $namespaces, $item_namespaces );
                        
                        $attributes = $this->extract_attributes_from_xml_product( $item, $all_namespaces );
                        $products_data[ intval( $id ) ] = $attributes;
                    }
                }
            }
            elseif ( isset( $xml->product ) ) {
                foreach ( $xml->product as $product ) {
                    $id = $this->extract_id_from_xml_product( $product, $namespaces );
                    if ( $id ) {
                        $product_namespaces = $product->getNamespaces( true );
                        $all_namespaces = array_merge( $namespaces, $product_namespaces );
                        
                        $attributes = $this->extract_attributes_from_xml_product( $product, $all_namespaces );
                        $products_data[ intval( $id ) ] = $attributes;
                    }
                }
            }
        }

        // Reset libxml error handling to avoid affecting other code
        libxml_clear_errors();
        libxml_use_internal_errors( false );

        return $products_data;
    }

    /**
     * Extract attributes from an XML product element.
     *
     * @since 7.4.60
     * @access protected
     * @param  SimpleXMLElement $product    The product XML element.
     * @param  array            $namespaces Array of namespaces.
     * @return array Array of attributes.
     */
    protected function extract_attributes_from_xml_product( $product, $namespaces ) {
        $attributes = array();
        $multi_value_fields = array( 'additional_image_link' ); // Fields that can have multiple values

        // 0. Extract XML attributes from the element itself (e.g., <offer id="123" available="true">)
        foreach ( $product->attributes() as $attr_name => $attr_value ) {
            $internal_name = $this->map_feed_tag_to_validator_attribute( (string) $attr_name );
            $attributes[ $internal_name ] = trim( (string) $attr_value );
        }

        // 1. Get all namespaced elements systematically
        foreach ( $namespaces as $prefix => $ns ) {
            $children = $product->children( $ns );
            
            foreach ( $children as $name => $value ) {
                // Map the tag name. Try with prefix first, then without if not mapped
                $key_with_prefix = ( !empty($prefix) ) ? $prefix . ':' . $name : $name;
                $internal_name = $this->map_feed_tag_to_validator_attribute( $key_with_prefix );
                
                if ( $internal_name === $name && !empty($prefix) ) {
                    // Try mapping just 'g:' + name if prefix is something else but it's the google namespace
                    if ( $ns === 'http://base.google.com/ns/1.0' ) {
                        $internal_name = $this->map_feed_tag_to_validator_attribute( 'g:' . $name );
                    }
                }

                // Handle multi-value fields (can appear multiple times)
                if ( in_array( $internal_name, $multi_value_fields, true ) ) {
                    if ( ! isset( $attributes[ $internal_name ] ) ) {
                        $attributes[ $internal_name ] = array();
                    }
                    $trimmed_value = trim( (string) $value );
                    if ( ! empty( $trimmed_value ) ) {
                        $attributes[ $internal_name ][] = $trimmed_value;
                    }
                } else {
                    $attributes[ $internal_name ] = trim( (string) $value );
                }
            }
        }

        // 2. Get regular elements
        foreach ( $product->children() as $name => $value ) {
            $internal_name = $this->map_feed_tag_to_validator_attribute( (string) $name );
            
            // Handle multi-value fields (can appear multiple times)
            if ( in_array( $internal_name, $multi_value_fields, true ) ) {
                if ( ! isset( $attributes[ $internal_name ] ) ) {
                    $attributes[ $internal_name ] = array();
                }
                $trimmed_value = trim( (string) $value );
                if ( ! empty( $trimmed_value ) ) {
                    $attributes[ $internal_name ][] = $trimmed_value;
                }
            } else {
                // Only overwrite if not already set by namespaced version or if namespaced version is empty
                if ( ! isset( $attributes[ $internal_name ] ) || empty( $attributes[ $internal_name ] ) ) {
                    $attributes[ $internal_name ] = trim( (string) $value );
                }
            }
        }

        // Convert single-item arrays to strings for multi-value fields (for backward compatibility)
        foreach ( $multi_value_fields as $field ) {
            if ( isset( $attributes[ $field ] ) && is_array( $attributes[ $field ] ) ) {
                if ( count( $attributes[ $field ] ) === 1 ) {
                    $attributes[ $field ] = $attributes[ $field ][0];
                } elseif ( empty( $attributes[ $field ] ) ) {
                    $attributes[ $field ] = '';
                }
            }
        }

        // Debug: Log extracted attributes for troubleshooting
        if ( isset( $attributes['id'] ) || isset( $attributes['sku_id'] ) ) {
            $product_id = $attributes['id'] ?? $attributes['sku_id'] ?? 'unknown';
        }

        return $attributes;
    }

    /**
     * Map feed tags (with or without namespace) to validator attribute names.
     *
     * @since 7.4.60
     * @access protected
     * @param  string $tag The tag name (e.g., 'g:image_link' or 'price').
     * @return string The internal attribute name.
     */
    protected function map_feed_tag_to_validator_attribute( $tag ) {
        $mapping = array(
            'id'                       => 'id',
            'title'                    => 'title',
            'description'              => 'description',
            'link'                     => 'link',
            'image_link'               => 'image_link',
            'additional_image_link'    => 'additional_image_link',
            'condition'                => 'condition',
            'availability'             => 'availability',
            'price'                    => 'price',
            'google_product_category'  => 'google_product_category',
            'brand'                    => 'brand',
            'gtin'                     => 'gtin',
            'mpn'                      => 'mpn',
            'identifier_exists'        => 'identifier_exists',
            'product_type'             => 'product_type',
            'shipping'                 => 'shipping',
            'tax'                      => 'tax',
            'sale_price'               => 'sale_price',
            'item_group_id'            => 'item_group_id',
            'color'                    => 'color',
            'size'                     => 'size',
            'gender'                   => 'gender',
            'age_group'                => 'age_group',
            'material'                 => 'material',
            'pattern'                  => 'pattern',
            'fb_product_category'      => 'fb_product_category',
            
            // Common Aliases
            'image_url'                => 'image_link',
            'ean'                      => 'gtin',
            'upc'                      => 'gtin',
            'isbn'                     => 'gtin',
            'regular_price'            => 'price',
            'regularprice'             => 'price',
            'regular-price'            => 'price',
            'regular price'            => 'price',
            'saleprice'                => 'sale_price',
            'sale-price'               => 'sale_price',
            'sale price'               => 'sale_price',
            'promotion_price'          => 'sale_price',
            'discount_price'           => 'sale_price',
            
            // Yandex YML Feed specific mappings
            'url'                      => 'url',            // Yandex URL (keep as-is, don't convert to 'link')
            'name'                     => 'name',           // Yandex product name
            'categoryid'               => 'categoryid',     // Yandex category ID
            'currencyid'               => 'currencyid',     // Yandex currency
            'available'                => 'available',      // Yandex availability
            'picture'                  => 'picture',        // Yandex picture
            'vendor'                   => 'vendor',         // Yandex vendor/brand
            'vendorcode'               => 'vendorcode',     // Yandex vendor code/SKU
            'model'                    => 'model',          // Yandex model
            'barcode'                  => 'barcode',        // Yandex barcode
            'delivery'                 => 'delivery',       // Yandex delivery options
            'oldprice'                 => 'oldprice',       // Yandex old price (compare at)
            'sku_id'                   => 'sku_id',         // TikTok SKU ID
        );

        // Strip any namespace prefix (e.g., 'g:', 'fb:') and normalize
        $clean_tag = strtolower( trim( $tag ) );
        if ( strpos( $clean_tag, ':' ) !== false ) {
            $parts = explode( ':', $clean_tag );
            $clean_tag = end( $parts );
        }

        if ( isset( $mapping[ $clean_tag ] ) ) {
            return $mapping[ $clean_tag ];
        }

        return $clean_tag;
    }

    /**
     * Extract product ID from an XML product element.
     *
     * @since 7.4.58
     * @access protected
     * @param  SimpleXMLElement $product    The product XML element.
     * @param  array            $namespaces Array of namespaces.
     * @return int|null Product ID or null if not found.
     */
    protected function extract_id_from_xml_product( $product, $namespaces ) {
        // Common ID field names in different feeds
        $id_fields = array( 'g:id', 'id', 'product_id', 'sku', 'sku_id', 'g:sku_id', 'g:item_id', 'item_id' );

        // Check namespaced elements first (e.g., g:id, g:sku_id)
        foreach ( $namespaces as $prefix => $ns ) {
            $children = $product->children( $ns );
            // Try id first
            if ( isset( $children->id ) ) {
                $id = $this->sanitize_product_id( (string) $children->id );
                if ( $id ) {
                    return $id;
                }
            }
            // Try sku_id (TikTok)
            if ( isset( $children->sku_id ) ) {
                $id = $this->sanitize_product_id( (string) $children->sku_id );
                if ( $id ) {
                    return $id;
                }
            }
            // Try sku
            if ( isset( $children->sku ) ) {
                $id = $this->sanitize_product_id( (string) $children->sku );
                if ( $id ) {
                    return $id;
                }
            }
        }

        // Check regular elements
        foreach ( $id_fields as $field ) {
            $field_name = str_replace( array( 'g:', 'fb:', 'tiktok:' ), '', $field );
            
            // Check as element
            if ( isset( $product->{$field_name} ) ) {
                $id = $this->sanitize_product_id( (string) $product->{$field_name} );
                if ( $id ) {
                    return $id;
                }
            }
            
            // Check as attribute
            if ( isset( $product[$field_name] ) ) {
                $id = $this->sanitize_product_id( (string) $product[$field_name] );
                if ( $id ) {
                    return $id;
                }
            }
        }

        return null;
    }

    /**
     * Sanitize and extract numeric product ID.
     *
     * Product IDs might be prefixed (e.g., "wc_post_123" or "variant_456").
     *
     * @since 7.4.58
     * @access protected
     * @param  string $id_value The ID value from the feed.
     * @return int|null Numeric product ID or null.
     */
    protected function sanitize_product_id( $id_value ) {
        if ( empty( $id_value ) ) {
            return null;
        }

        // If it's purely numeric, return as-is
        if ( is_numeric( $id_value ) ) {
            return intval( $id_value );
        }

        // Try to extract numeric ID from prefixed formats
        // Common patterns: "wc_post_123", "variant_123", "product_123", "123_suffix"
        if ( preg_match( '/(\d+)/', $id_value, $matches ) ) {
            return intval( $matches[1] );
        }
        
        // If it's a SKU (non-numeric), try to find product by SKU
        $product_id = wc_get_product_id_by_sku( $id_value );
        if ( $product_id ) {
            return intval( $product_id );
        }
        
        return null;
    }

    /**
     * Parse CSV/TSV feed file to extract product data.
     *
     * @since 7.4.60
     * @access protected
     * @param  string $feed_file   Path to the CSV feed file.
     * @param  string $feed_format The feed format (CSV, TSV, TXT).
     * @return array Array of products: array( ID => attributes_array )
     */
    protected function parse_csv_feed_for_data( $feed_file, $feed_format ) {
        $products_data = array();

        // Determine delimiter
        $delimiter = ',';
        $feed_format_upper = strtoupper( $feed_format );
        if ( $feed_format_upper === 'TSV' || $feed_format_upper === 'TXT' || $feed_format_upper === 'TEXT' ) {
            $delimiter = "\t";
        }

        // Open file
        $handle = fopen( $feed_file, 'r' );
        if ( ! $handle ) {
            return $products_data;
        }

        // Read header row
        $header = fgetcsv( $handle, 0, $delimiter );
        if ( ! $header ) {
            fclose( $handle );
            return $products_data;
        }

        // Map header columns to internal attribute names
        $column_mapping = array();
        foreach ( $header as $index => $col_name ) {
            $column_mapping[ $index ] = $this->map_feed_tag_to_validator_attribute( $col_name );
        }

        // Find ID column index
        $id_column_names = array( 'id', 'product_id', 'g:id', 'item_id', 'sku' );
        $id_column_index = -1;

        foreach ( $id_column_names as $column_name ) {
            $index = array_search( strtolower( $column_name ), array_map( 'strtolower', $header ) );
            if ( $index !== false ) {
                $id_column_index = $index;
                break;
            }
        }

        if ( $id_column_index === -1 ) {
            fclose( $handle );
            return $products_data;
        }

        // Read data rows
        while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
            if ( ! isset( $row[ $id_column_index ] ) ) {
                continue;
            }

            $id_value = $row[ $id_column_index ];
            $id = $this->sanitize_product_id( $id_value );

            if ( $id ) {
                $attributes = array();
                foreach ( $row as $index => $value ) {
                    if ( isset( $column_mapping[ $index ] ) ) {
                        // Trim whitespace from values
                        $attributes[ $column_mapping[ $index ] ] = trim( $value );
                    }
                }
                $products_data[ intval( $id ) ] = $attributes;
            }
        }

        fclose( $handle );
        return $products_data;
    }

    /**
     * Enable validation.
     *
     * @since 7.4.58
     * @access public
     * @return void
     */
    public function enable_validation() {
        $this->validation_enabled = true;
    }

    /**
     * Disable validation.
     *
     * @since 7.4.58
     * @access public
     * @return void
     */
    public function disable_validation() {
        $this->validation_enabled = false;
    }

    /**
     * Check if validation is enabled.
     *
     * @since 7.4.58
     * @access public
     * @return bool
     */
    public function is_validation_enabled() {
        return $this->validation_enabled;
    }

    /**
     * Auto-clear validation results and auto-run validation after feed update.
     *
     * @since 7.4.59
     * @param int $feed_id The feed ID.
     */
    public function auto_clear_and_run_validation_on_feed_update( $feed_id ) {
        if ( ! $feed_id ) {
            return;
        }
        // Clear old validation results
        $results_handler = new Rex_Feed_Validation_Results( $feed_id );
        $results_handler->clear_results();
        // Also clear transient batch errors
        delete_transient( 'rex_feed_validation_' . $feed_id );
        // Auto-run validation
        $this->run_validation( $feed_id );
    }

    /**
     * Get Google Product Category value from category mapping.
     *
     * @since 7.4.60
     * @param  WC_Product $product The product.
     * @param  int        $feed_id The feed ID.
     * @return string
     */
    protected function get_category_mapping_value( $product, $feed_id ) {
        // Find which mapper is used for google_product_category in this feed
        $feed_config = get_post_meta( $feed_id, '_rex_feed_feed_config', true );
        $mapper_key = '';
        
        if ( is_array( $feed_config ) ) {
            foreach ( $feed_config as $config ) {
                if ( isset( $config['attr'] ) && $config['attr'] === 'google_product_category' && isset( $config['type'] ) && $config['type'] === 'meta' ) {
                    $meta_key = $config['meta_key'] ?? '';
                    // If meta_key starts with 'rex_product_cat_mapper_', it's a category mapping
                    if ( strpos( $meta_key, 'rex_product_cat_mapper_' ) === 0 ) {
                        $mapper_key = $meta_key;
                        break;
                    }
                }
            }
        }
        
        if ( empty( $mapper_key ) ) {
            return '';
        }
        
        $product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
        $cat_lists = get_the_terms( $product_id, 'product_cat' );
        $wpfm_category_map = get_option( 'rex-wpfm-category-mapping' );
        
        if ( $wpfm_category_map && isset( $wpfm_category_map[ $mapper_key ] ) && $cat_lists ) {
            $map        = $wpfm_category_map[ $mapper_key ];
            $map_config = $map[ 'map-config' ] ?? array();
            
            foreach ( $cat_lists as $term ) {
                $map_keys = is_array( $map_config ) && !empty( $map_config ) ? array_column( $map_config, 'map-key' ) : array();
                $map_index  = array_search( $term->term_id, $map_keys );
                
                if ( $map_index !== false ) {
                    $map_array = $map_config[ $map_index ];
                    $map_value = $map_array[ 'map-value' ] ?? '';
                    if ( !empty( $map_value ) ) {
                        // Extract ID from value like "5 (Apparel & Accessories)"
                        preg_match( "~^(\d+)~", $map_value, $m );
                        return isset( $m[1] ) ? $m[1] : $map_value;
                    }
                }
            }
        }
        
        return '';
    }

}

/**
 * Returns the main instance of Rex_Feed_Validator_Loader.
 *
 * @since 7.4.58
 * @return Rex_Feed_Validator_Loader
 */
function rex_feed_validator_loader() {
    return Rex_Feed_Validator_Loader::instance();
}