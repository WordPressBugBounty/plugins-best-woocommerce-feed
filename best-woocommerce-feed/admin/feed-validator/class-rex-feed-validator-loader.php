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
     * In-memory buffered health states during feed generation.
     *
     * @var array
     */
    protected static $buffered_health = array();

    /**
     * In-memory buffered validation errors during feed generation.
     *
     * @var array
     */
    protected static $buffered_errors = array();

    /**
     * Counter of products processed since last flush per feed.
     *
     * @var array
     */
    protected static $unflushed_counts = array();

    /**
     * Flag indicating whether shutdown flush hook is registered.
     *
     * @var bool
     */
    protected static $shutdown_registered = false;

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

        // Load validation Quick Fix matcher.
        require_once $validator_path . 'class-rex-feed-quick-fix.php';

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

        // Load generic fallback validator
        require_once $validator_path . 'class-rex-feed-validator-generic.php';
        // Load ChatGPT Ads validator
        require_once $validator_path . 'class-rex-feed-validator-chatgpt-ads.php';
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
        add_action( 'wp_ajax_rex_feed_get_validation_issue_products', array( $this, 'ajax_get_validation_issue_products' ) );
        add_action( 'wp_ajax_rex_feed_clear_validation_results', array( $this, 'ajax_clear_validation_results' ) );
        add_action( 'wp_ajax_rex_feed_export_validation_results', array( $this, 'ajax_export_validation_results' ) );
        add_action( 'wp_ajax_rex_feed_save_validation_settings', array( $this, 'ajax_save_validation_settings' ) );
        add_action( 'wp_ajax_rex_feed_get_quick_fix', array( $this, 'ajax_get_quick_fix' ) );
        add_action( 'wp_ajax_rex_feed_delete_validation_fix_rule', array( $this, 'ajax_delete_validation_fix_rule' ) );
        add_action( 'wp_ajax_rex_feed_apply_validation_fix_rule', array( $this, 'ajax_apply_validation_fix_rule' ) );
        add_action( 'wp_ajax_rex_feed_apply_validation_mapping_fix', array( $this, 'ajax_apply_validation_mapping_fix' ) );
        add_action( 'wp_ajax_rex_feed_revert_validation_mapping_fix', array( $this, 'ajax_revert_validation_mapping_fix' ) );

        // Integration with feed generation
        add_action( 'rex_feed_after_product_processed', array( $this, 'validate_product_during_generation' ), 10, 4 );
        add_action( 'rex_feed_after_generation_complete', array( $this, 'finalize_validation' ), 10, 2 );

        // Auto-validate when scheduled feed generation completes
        add_action( 'rex_product_feed_scheduler_generate', array( $this, 'auto_validate_on_schedule' ), 10, 1 );

        // Admin menu/UI integration
        add_filter( 'rex_feed_product_feed_tabs', array( $this, 'add_validation_tab' ) );
        add_action( 'rex_feed_after_feed_updated', array( $this, 'auto_clear_and_run_validation_on_feed_update' ), 10, 1 );

        // Background validation — fires when the scheduled single event runs.
        add_action( 'rex_feed_validate_scheduled', array( $this, 'run_validation' ), 10, 1 );
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
        if ( ! $feed_id || $this->is_feed_validation_disabled( $feed_id ) ) {
            return;
        }

        $merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );
        if ( empty( $merchant ) ) {
            $merchant = get_post_meta( $feed_id, 'rex_feed_merchant', true );
        }

        if ( empty( $merchant ) ) {
            return;
        }
      
        // Only auto-validate for supported merchants
        $supported_merchants = array( 'google', 'google_shopping', 'google_local', 'google_local_inventory', 'facebook', 'facebook_marketplace', 'instagram', 'instagram_shopping', 'openai', 'openai_commerce', 'chatgpt_ads' );
        $merchant_normalized = strtolower( str_replace( ' ', '_', $merchant ) );
        
        if ( ! in_array( $merchant_normalized, $supported_merchants, true ) ) {
            return;
        }

        // Schedule validation as a separate cron event so it runs in its own
        // PHP process — the feed-generation process still holds the product
        // batch in memory and running validation here would risk OOM.
        if ( ! wp_next_scheduled( 'rex_feed_validate_scheduled', array( $feed_id ) ) ) {
            wp_schedule_single_event( time() + 30, 'rex_feed_validate_scheduled', array( $feed_id ) );
        }
    }

    /**
     * Merge validation summary counts without losing issues omitted from storage.
     *
     * @since 7.4.58
     * @access protected
     * @param  array $summary    Accumulated summary.
     * @param  array $additional Summary for the current product.
     * @return array
     */
    protected function merge_validation_summaries( $summary, $additional ) {
        foreach ( array( 'total_errors', 'total_warnings', 'total_info' ) as $count_key ) {
            $summary[ $count_key ] = absint( $summary[ $count_key ] ?? 0 ) + absint( $additional[ $count_key ] ?? 0 );
        }

        foreach ( array( 'by_attribute', 'by_rule' ) as $group_key ) {
            if ( ! isset( $summary[ $group_key ] ) || ! is_array( $summary[ $group_key ] ) ) {
                $summary[ $group_key ] = array();
            }

            foreach ( (array) ( $additional[ $group_key ] ?? array() ) as $key => $count ) {
                $summary[ $group_key ][ $key ] = absint( $summary[ $group_key ][ $key ] ?? 0 ) + absint( $count );
            }
        }

        return $summary;
    }

    /**
     * Finalize and calculate health score for the validation summary.
     *
     * Applies error penalty:
     * Error Penalty (%) = (Number of Unique Products with >= 1 Error / Total Number of Products) * 100
     * Final Feed Health = MAX(0, Current Feed Health - Error Penalty)
     *
     * @since 7.4.58
     * @access protected
     * @param  array     $summary              Validation summary.
     * @param  int|float $total_product_health Sum of all product health scores.
     * @param  int       $total_products       Total products analyzed.
     * @param  int       $stored_issue_count   Number of issue rows retained for display.
     * @param  array|int $error_product_ids    Unique error product IDs or count.
     * @return array
     */
    protected function finalize_validation_summary( $summary, $total_product_health, $total_products, $stored_issue_count, $error_product_ids = array() ) {
        $total_products = absint( $total_products );
        $total_issues   = absint( $summary['total_errors'] ?? 0 )
            + absint( $summary['total_warnings'] ?? 0 )
            + absint( $summary['total_info'] ?? 0 );

        $summary['total_products'] = $total_products;
        $summary['total_issues']   = $total_issues;

        if ( $total_products > 0 ) {
            $current_health = (float) $total_product_health / $total_products;

            if ( is_array( $error_product_ids ) ) {
                $unique_error_count = count( array_unique( array_filter( array_map( 'absint', $error_product_ids ) ) ) );
            } elseif ( is_numeric( $error_product_ids ) ) {
                $unique_error_count = absint( $error_product_ids );
            } else {
                $unique_error_count = 0;
            }

            $error_penalty = ( $unique_error_count / $total_products ) * 100;
            $final_health  = max( 0, $current_health - $error_penalty );

            $summary['health_score']     = max( 0, min( 100, (int) round( $final_health ) ) );
            $summary['raw_health_score'] = $current_health;
            $summary['error_penalty']    = $error_penalty;
        } else {
            $summary['health_score'] = 100;
        }

        return $summary;
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
        if ( $this->is_feed_validation_disabled( $feed_id ) ) {
            return false;
        }

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
        $summary = $validator->get_validation_summary( array() );
        $total_product_health = 0;
        $processed_count = 0;
        $error_product_ids = array();

        // Validate mapping-level configuration and anomalies
        $mapping_errors = $validator->validate_mapping();
        if ( ! empty( $mapping_errors ) ) {
            foreach ( $mapping_errors as $mapping_error ) {
                $all_errors[] = $mapping_error;
            }
            $summary = $this->merge_validation_summaries(
                $summary,
                $validator->get_validation_summary( $mapping_errors )
            );
        }

        foreach ( $products_data as $product_data ) {
            $errors = $validator->validate_product(
                $product_data['product_id'],
                $product_data['attributes'],
                $product_data['title']
            );

            $summary = $this->merge_validation_summaries(
                $summary,
                $validator->get_validation_summary( $errors )
            );
            $total_product_health += Rex_Feed_Validation_Results::calculate_product_health( $errors );
            $processed_count++;
            $error_product_ids = array_merge(
                $error_product_ids,
                Rex_Feed_Validation_Results::extract_error_product_ids( $errors )
            );

            // Use direct push instead of array_merge to avoid allocating a
            // new array on every iteration.
            if ( is_array( $errors ) && !empty( $errors ) ) {
                foreach ( $errors as $error ) {
                    $all_errors[] = $error;
                }
            }
            unset( $errors );

        }
        unset( $products_data );

        $summary = $this->finalize_validation_summary(
            $summary,
            $total_product_health,
            $processed_count,
            count( $all_errors ),
            $error_product_ids
        );

        // Save results
        $results_handler = new Rex_Feed_Validation_Results( $feed_id );
        $results_handler->save_results( $all_errors, $summary, $error_product_ids );

        $merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true ) ?: get_post_meta( $feed_id, 'rex_feed_merchant', true );
        do_action( 'rex_product_feed_validation_completed', $feed_id, $merchant, $summary, 'cron' );

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

        if ( $this->is_feed_validation_disabled( $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Feed validation is disabled.', 'rex-product-feed' ) ) );
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
                    ucwords(str_replace('_', ' ', $merchant))
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
        $summary = $validator->get_validation_summary( array() );
        $total_product_health = 0;
        $error_product_ids = array();

        // Validate mapping-level configuration and anomalies
        $mapping_errors = $validator->validate_mapping();
        if ( ! empty( $mapping_errors ) ) {
            foreach ( $mapping_errors as $mapping_error ) {
                $all_errors[] = $mapping_error;
            }
            $summary = $this->merge_validation_summaries(
                $summary,
                $validator->get_validation_summary( $mapping_errors )
            );
        }

        foreach ( $products_data as $product_data ) {
            $errors = $validator->validate_product(
                $product_data['product_id'],
                $product_data['attributes'],
                $product_data['title'] // Pass display title (includes variation info)
            );

            $summary = $this->merge_validation_summaries(
                $summary,
                $validator->get_validation_summary( $errors )
            );
            $total_product_health += Rex_Feed_Validation_Results::calculate_product_health( $errors );
            $error_product_ids = array_merge(
                $error_product_ids,
                Rex_Feed_Validation_Results::extract_error_product_ids( $errors )
            );
            foreach ( (array) $errors as $error ) {
                $all_errors[] = $error;
            }
            $processed_count++;
        }


        $summary = $this->finalize_validation_summary(
            $summary,
            $total_product_health,
            $processed_count,
            count( $all_errors ),
            $error_product_ids
        );
        
        
        // Save results
        $results_handler = new Rex_Feed_Validation_Results( $feed_id );
        $results_handler->save_results( $all_errors, $summary, $error_product_ids );
        $saved_summary = $results_handler->get_summary();

        do_action( 'rex_product_feed_validation_completed', $feed_id, $merchant, $summary, 'manual' );

        wp_send_json_success( array(
            'message'       => __( 'Validation complete.', 'rex-product-feed' ),
            'summary'       => $saved_summary,
            'total_errors'  => $saved_summary['total_errors'] ?? count( $all_errors ),
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
     * AJAX handler to get affected products for a specific validation issue.
     *
     * @since 7.4.58
     * @access public
     * @return void
     */
    public function ajax_get_validation_issue_products() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $feed_id   = isset( $_POST['feed_id'] ) ? absint( $_POST['feed_id'] ) : 0;
        $attribute = isset( $_POST['attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['attribute'] ) ) : '';
        $rule      = isset( $_POST['rule'] ) ? sanitize_text_field( wp_unslash( $_POST['rule'] ) ) : '';
        $severity  = isset( $_POST['severity'] ) ? sanitize_text_field( wp_unslash( $_POST['severity'] ) ) : '';
        $page      = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page  = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;

        if ( ! $feed_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
        }

        $results_handler = new Rex_Feed_Validation_Results( $feed_id );
        $data = $results_handler->get_issue_products( $attribute, $rule, $severity, $page, $per_page );

        wp_send_json_success( $data );
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
        if ( ! $this->validation_enabled || $this->is_feed_validation_disabled( $feed_id ) ) {
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

        $health_transient_key = 'rex_feed_validation_health_' . $feed_id;
        $transient_key        = 'rex_feed_validation_' . $feed_id;

        // Initialize in-memory buffers on first call for this feed
        if ( ! isset( self::$buffered_health[ $feed_id ] ) ) {
            $health_state = get_transient( $health_transient_key );
            self::$buffered_health[ $feed_id ] = is_array( $health_state ) ? $health_state : array(
                'summary'              => $validator->get_validation_summary( array() ),
                'total_product_health' => 0,
                'total_products'       => 0,
                'error_product_ids'    => array(),
            );
        }

        if ( ! isset( self::$buffered_errors[ $feed_id ] ) ) {
            $stored_errors = get_transient( $transient_key );
            self::$buffered_errors[ $feed_id ] = is_array( $stored_errors ) ? $stored_errors : array();
        }

        if ( ! self::$shutdown_registered ) {
            register_shutdown_function( array( __CLASS__, 'flush_buffered_validation_data' ) );
            self::$shutdown_registered = true;
        }

        self::$buffered_health[ $feed_id ]['summary'] = $this->merge_validation_summaries(
            (array) ( self::$buffered_health[ $feed_id ]['summary'] ?? array() ),
            $validator->get_validation_summary( $errors )
        );
        self::$buffered_health[ $feed_id ]['total_product_health'] = absint( self::$buffered_health[ $feed_id ]['total_product_health'] ?? 0 )
            + Rex_Feed_Validation_Results::calculate_product_health( $errors );
        self::$buffered_health[ $feed_id ]['total_products'] = absint( self::$buffered_health[ $feed_id ]['total_products'] ?? 0 ) + 1;
        self::$buffered_health[ $feed_id ]['error_product_ids'] = array_merge(
            (array) ( self::$buffered_health[ $feed_id ]['error_product_ids'] ?? array() ),
            Rex_Feed_Validation_Results::extract_error_product_ids( $errors )
        );

        foreach ( (array) $errors as $error ) {
            self::$buffered_errors[ $feed_id ][] = $error;
        }

        self::$unflushed_counts[ $feed_id ] = ( self::$unflushed_counts[ $feed_id ] ?? 0 ) + 1;

        // Periodic flush every 100 products to prevent memory spikes while eliminating 99% of transient I/O
        if ( self::$unflushed_counts[ $feed_id ] >= 100 ) {
            self::flush_feed_validation( $feed_id );
        }
    }

    /**
     * Flush buffered validation data for a feed to transients.
     *
     * @since 7.5.0
     * @param int $feed_id Feed ID.
     * @return void
     */
    public static function flush_feed_validation( $feed_id ) {
        $health_transient_key = 'rex_feed_validation_health_' . $feed_id;
        $transient_key        = 'rex_feed_validation_' . $feed_id;

        if ( isset( self::$buffered_health[ $feed_id ] ) ) {
            set_transient( $health_transient_key, self::$buffered_health[ $feed_id ], HOUR_IN_SECONDS );
        }

        if ( isset( self::$buffered_errors[ $feed_id ] ) ) {
            set_transient( $transient_key, self::$buffered_errors[ $feed_id ], HOUR_IN_SECONDS );
        }

        self::$unflushed_counts[ $feed_id ] = 0;
    }

    /**
     * Flush all buffered validation data across feeds on shutdown.
     *
     * @since 7.5.0
     * @return void
     */
    public static function flush_buffered_validation_data() {
        foreach ( array_keys( self::$buffered_health ) as $feed_id ) {
            self::flush_feed_validation( $feed_id );
        }
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
        $transient_key        = 'rex_feed_validation_' . $feed_id;
        $health_transient_key = 'rex_feed_validation_health_' . $feed_id;

        if ( $this->is_feed_validation_disabled( $feed_id ) ) {
            delete_transient( $transient_key );
            delete_transient( $health_transient_key );
            unset( self::$buffered_errors[ $feed_id ], self::$buffered_health[ $feed_id ], self::$unflushed_counts[ $feed_id ] );
            return;
        }

        $errors = isset( self::$buffered_errors[ $feed_id ] )
            ? self::$buffered_errors[ $feed_id ]
            : get_transient( $transient_key );

        $health_state = isset( self::$buffered_health[ $feed_id ] )
            ? self::$buffered_health[ $feed_id ]
            : get_transient( $health_transient_key );

        unset( self::$buffered_errors[ $feed_id ], self::$buffered_health[ $feed_id ], self::$unflushed_counts[ $feed_id ] );

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

        // Validate mapping-level configuration and anomalies
        $mapping_errors = $validator ? $validator->validate_mapping() : array();
        if ( ! empty( $mapping_errors ) ) {
            foreach ( $mapping_errors as $mapping_error ) {
                $errors[] = $mapping_error;
            }
            if ( $validator ) {
                $summary = $this->merge_validation_summaries(
                    $summary,
                    $validator->get_validation_summary( $mapping_errors )
                );
            }
        }

        if ( is_array( $health_state ) && ! empty( $health_state['total_products'] ) ) {
            $summary = $this->finalize_validation_summary(
                (array) ( $health_state['summary'] ?? $summary ),
                absint( $health_state['total_product_health'] ?? 0 ),
                absint( $health_state['total_products'] ),
                count( $errors ),
                (array) ( $health_state['error_product_ids'] ?? array() )
            );
        } else {
            $results_handler = new Rex_Feed_Validation_Results( $feed_id );
            $total_products  = $results_handler->get_total_products_validated();
            $summary['total_products'] = $total_products;
            $summary['health_score']   = Rex_Feed_Validation_Results::calculate_feed_health(
                $errors,
                $total_products,
                $results_handler->get_error_product_ids()
            );
        }

        // Save results
        $results_handler = new Rex_Feed_Validation_Results( $feed_id );
        $error_product_ids = is_array( $health_state )
            ? (array) ( $health_state['error_product_ids'] ?? array() )
            : Rex_Feed_Validation_Results::extract_error_product_ids( $errors );
        $results_handler->save_results( $errors, $summary, $error_product_ids );

        // Clean up transient
        delete_transient( $transient_key );
        delete_transient( $health_transient_key );
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
            foreach ( $parsed_products as $product_id => $attributes ) {
                // The feed file already contains the product title — use it
                // directly instead of calling wc_get_product() for every item.
                $product_title = ! empty( $attributes['title'] )
                    ? $attributes['title']
                    : ( is_numeric( $product_id ) ? sprintf( 'Product #%d', $product_id ) : ( ! empty( $attributes['id'] ) ? sprintf( 'Product (%s)', $attributes['id'] ) : 'Product' ) );

                $products_data[] = array(
                    'product_id' => is_numeric( $product_id ) ? intval( $product_id ) : 0,
                    'title'      => $product_title,
                    'attributes' => $attributes,
                );
            }
            unset( $parsed_products );
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
        $products_data    = array();
        // Known product-level element names across all supported feed formats.
        $product_elements = array( 'item', 'entry', 'product', 'offer' );

        // Use XMLReader so the file is parsed as a stream — only one product
        // element is in memory at a time.  The previous simplexml_load_file()
        // approach loaded the entire XML tree into memory first, which OOMed
        // PHP for feeds with thousands of products.
        $reader = new XMLReader();
        libxml_use_internal_errors( true );

        if ( ! $reader->open( $feed_file ) ) {
            libxml_clear_errors();
            libxml_use_internal_errors( false );
            return $products_data;
        }

        // Collect xmlns:* declarations from container/root elements so we can
        // re-inject them into individual product XML strings if needed.
        // (e.g. xmlns:g="http://base.google.com/ns/1.0" lives on <rss>, not <item>)
        $root_ns = array();

        while ( $reader->read() ) {
            if ( $reader->nodeType !== XMLReader::ELEMENT ) {
                continue;
            }

            $local = strtolower( $reader->localName );

            // Harvest namespace declarations from every start element we visit.
            if ( $reader->hasAttributes ) {
                $reader->moveToFirstAttribute();
                do {
                    $attr_name = $reader->name;
                    if ( strpos( $attr_name, 'xmlns' ) === 0 ) {
                        $prefix              = ( strpos( $attr_name, ':' ) !== false ) ? substr( $attr_name, 6 ) : '';
                        $root_ns[ $prefix ]  = $reader->value;
                    }
                } while ( $reader->moveToNextAttribute() );
                $reader->moveToElement(); // restore position to the element
            }

            if ( ! in_array( $local, $product_elements, true ) ) {
                continue;
            }

            // readOuterXml() returns the XML for this element plus its subtree.
            // libxml re-declares any in-scope namespace inherited from ancestors,
            // so the string is self-contained.  It also advances the reader past
            // the closing tag.
            $outer_xml = $reader->readOuterXml();
            if ( empty( $outer_xml ) ) {
                continue;
            }

            // Safety: inject any root-level namespace declarations that libxml
            // may not have re-declared (e.g. older PHP/libxml versions).
            foreach ( $root_ns as $prefix => $uri ) {
                $decl = empty( $prefix ) ? 'xmlns' : 'xmlns:' . $prefix;
                if ( strpos( $outer_xml, $decl . '=' ) === false ) {
                    $outer_xml = preg_replace(
                        '/^(<[^\s>\/]+)/',
                        '$1 ' . $decl . '="' . htmlspecialchars( $uri, ENT_QUOTES, 'UTF-8' ) . '"',
                        $outer_xml,
                        1
                    );
                }
            }

            // Parse just this one product element — tiny memory footprint.
            $node = @simplexml_load_string(
                '<?xml version="1.0" encoding="UTF-8"?>' . $outer_xml,
                'SimpleXMLElement',
                LIBXML_NOCDATA
            );

            if ( $node !== false ) {
                $ns = $node->getNamespaces( true );
                $id = $this->extract_id_from_xml_product( $node, $ns );
                $attributes = $this->extract_attributes_from_xml_product( $node, $ns );
                $key = $id ? intval( $id ) : 'item_' . count( $products_data );
                $products_data[ $key ] = $attributes;
            }

            unset( $node, $outer_xml, $ns );

        }

        $reader->close();
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

        // Read every data row. Pagination is applied to validation results later.
        while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
            $id_value = ( $id_column_index !== -1 && isset( $row[ $id_column_index ] ) ) ? $row[ $id_column_index ] : '';
            $id = $this->sanitize_product_id( $id_value );

            $attributes = array();
            foreach ( $row as $index => $value ) {
                if ( isset( $column_mapping[ $index ] ) ) {
                    // Trim whitespace from values
                    $attributes[ $column_mapping[ $index ] ] = trim( $value );
                }
            }
            $key = $id ? intval( $id ) : 'item_' . count( $products_data );
            $products_data[ $key ] = $attributes;
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
     * Check the persistent validation state for one feed.
     *
     * @since 7.4.58
     * @param  int $feed_id Feed ID.
     * @return bool
     */
    protected function is_feed_validation_disabled( $feed_id ) {
        $results_handler = new Rex_Feed_Validation_Results( $feed_id );
        return $results_handler->is_validation_disabled();
    }

    /**
     * Save validation menu settings for one feed.
     *
     * @since 7.4.58
     * @return void
     */
    public function ajax_save_validation_settings() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        $feed_id = isset( $_POST['feed_id'] ) ? absint( $_POST['feed_id'] ) : 0;

        if ( ! $feed_id || 'product-feed' !== get_post_type( $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
        }

        if ( ! current_user_can( 'edit_post', $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $validation_disabled = isset( $_POST['validation_disabled'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['validation_disabled'] ) );
        $exclude_errors      = isset( $_POST['exclude_error_products'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['exclude_error_products'] ) );

        update_post_meta(
            $feed_id,
            Rex_Feed_Validation_Results::META_KEY_VALIDATION_DISABLED,
            $validation_disabled ? 'yes' : 'no'
        );
        update_post_meta(
            $feed_id,
            Rex_Feed_Validation_Results::META_KEY_EXCLUDE_ERRORS,
            $exclude_errors ? 'yes' : 'no'
        );

        wp_send_json_success( array(
            'validation_disabled'  => $validation_disabled,
            'exclude_error_products' => $exclude_errors,
        ) );
    }

    /**
     * Find and stage next Quick Fix rule for one exact validation issue.
     *
     * @since 7.4.58
     * @return void
     */
    public function ajax_get_quick_fix() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        $feed_id = isset( $_POST['feed_id'] ) ? absint( $_POST['feed_id'] ) : 0;

        if ( ! $feed_id || 'product-feed' !== get_post_type( $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
        }

        if ( ! current_user_can( 'edit_post', $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $feed_status = get_post_meta( $feed_id, '_rex_feed_status', true );

        if ( in_array( $feed_status, array( 'processing', 'In queue' ), true ) ) {
            wp_send_json_error( array( 'message' => __( 'Wait for the current feed generation to finish.', 'rex-product-feed' ) ) );
        }

        $attribute = isset( $_POST['attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['attribute'] ) ) : '';
        $rule      = isset( $_POST['rule'] ) ? sanitize_key( wp_unslash( $_POST['rule'] ) ) : '';
        $severity  = isset( $_POST['severity'] ) ? sanitize_key( wp_unslash( $_POST['severity'] ) ) : '';

        if ( '' === $attribute || '' === $rule || ! in_array( $severity, array( 'error', 'warning', 'info' ), true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid validation issue.', 'rex-product-feed' ) ) );
        }

        $quick_fix = new Rex_Feed_Quick_Fix();
        $result    = $quick_fix->suggest_and_save( $feed_id, $attribute, $rule, $severity );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * Delete one staged validation Feed Rule.
     *
     * @since 7.4.58
     * @return void
     */
    public function ajax_delete_validation_fix_rule() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        $feed_id = isset( $_POST['feed_id'] ) ? absint( $_POST['feed_id'] ) : 0;

        if ( ! $feed_id || 'product-feed' !== get_post_type( $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
        }

        if ( ! current_user_can( 'edit_post', $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $feed_status = get_post_meta( $feed_id, '_rex_feed_status', true );

        if ( in_array( $feed_status, array( 'processing', 'In queue' ), true ) ) {
            wp_send_json_error( array( 'message' => __( 'Wait for the current feed generation to finish.', 'rex-product-feed' ) ) );
        }

        $attribute = isset( $_POST['attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['attribute'] ) ) : '';

        if ( '' === $attribute ) {
            wp_send_json_error( array( 'message' => __( 'Invalid validation attribute.', 'rex-product-feed' ) ) );
        }

        $quick_fix = new Rex_Feed_Quick_Fix();
        $target    = $quick_fix->resolve_rule_target( $feed_id, $attribute );

        if ( ! Rex_Feed_Quick_Fix::delete_feed_rule( $feed_id, $target['value'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Feed Rule was not found.', 'rex-product-feed' ) ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Feed Rule deleted.', 'rex-product-feed' ),
        ) );
    }

    /**
     * Add or update one Feed Rule from a grouped validation issue.
     *
     * One validation fix rule is stored per affected attribute. Existing
     * rules for other attributes remain unchanged.
     *
     * @since 7.4.58
     * @return void
     */
    public function ajax_apply_validation_fix_rule() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        $feed_id = isset( $_POST['feed_id'] ) ? absint( $_POST['feed_id'] ) : 0;

        if ( ! $feed_id || 'product-feed' !== get_post_type( $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
        }

        if ( ! current_user_can( 'edit_post', $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $feed_status = get_post_meta( $feed_id, '_rex_feed_status', true );

        if ( in_array( $feed_status, array( 'processing', 'In queue' ), true ) ) {
            wp_send_json_error( array( 'message' => __( 'Wait for the current feed generation to finish.', 'rex-product-feed' ) ) );
        }

        $attribute    = isset( $_POST['attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['attribute'] ) ) : '';
        $condition    = isset( $_POST['condition'] ) ? sanitize_key( wp_unslash( $_POST['condition'] ) ) : '';
        $find         = isset( $_POST['find'] ) ? sanitize_text_field( wp_unslash( $_POST['find'] ) ) : '';
        $replace      = isset( $_POST['replace'] ) ? sanitize_text_field( wp_unslash( $_POST['replace'] ) ) : '';
        $static_value = isset( $_POST['static_value'] ) ? sanitize_text_field( wp_unslash( $_POST['static_value'] ) ) : '';
        $is_static    = isset( $_POST['is_static'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['is_static'] ) );

        $allowed_conditions = array(
            'find_and_replace',
            'contain',
            'dn_contain',
            'equal_to',
            'nequal_to',
            'greater_than',
            'greater_than_equal',
            'less_than',
            'less_than_equal',
            'any',
        );

        if ( '' === $attribute || ! in_array( $condition, $allowed_conditions, true ) ) {
            wp_send_json_error( array( 'message' => __( 'Select a valid condition.', 'rex-product-feed' ) ) );
        }

        if ( ( $is_static && '' === $static_value ) || ( ! $is_static && '' === $replace ) ) {
            wp_send_json_error( array( 'message' => __( 'Select or enter a replacement value.', 'rex-product-feed' ) ) );
        }

        $validation_results = get_post_meta( $feed_id, Rex_Feed_Validation_Results::META_KEY_RESULTS, true );
        $has_attribute_issue = false;

        foreach ( (array) $validation_results as $validation_issue ) {
            $issue_attribute = isset( $validation_issue['attribute'] )
                ? sanitize_text_field( $validation_issue['attribute'] )
                : '';

            if ( $attribute === $issue_attribute ) {
                $has_attribute_issue = true;
                break;
            }
        }

        if ( ! $has_attribute_issue ) {
            wp_send_json_error( array( 'message' => __( 'This validation issue is no longer available.', 'rex-product-feed' ) ) );
        }

        $quick_fix = new Rex_Feed_Quick_Fix();
        $target    = $quick_fix->resolve_rule_target( $feed_id, $attribute );

        if ( ! $target['supported'] ) {
            wp_send_json_error( array( 'message' => __( 'This output attribute has no mapped source that Feed Rules can target.', 'rex-product-feed' ) ) );
        }

        $new_rule  = array(
            'rules_if'             => $target['value'],
            'rules_condition'      => $condition,
            'rules_find'           => $find,
            'rules_then'           => $target['value'],
            'rules_static_replace' => $is_static ? $static_value : '',
            'rules_replace'        => $is_static ? '' : $replace,
            'quick_fix'            => true,
            'source'               => 'validation_quick_fix',
        );

        if ( $is_static ) {
            $new_rule['rules_static'] = 'on';
        }

        if ( Rex_Feed_Quick_Fix::rule_exists( $feed_id, $new_rule ) ) {
            wp_send_json_error( array(
                'code'    => 'rule_already_exists',
                'message' => __( 'This rule has already been added. Please configure a different rule.', 'rex-product-feed' ),
            ) );
        }

        Rex_Feed_Quick_Fix::upsert_feed_rule( $feed_id, $new_rule );

        wp_send_json_success( array(
            'message'   => __( 'Rule added. Add more rules or click Fix Feed.', 'rex-product-feed' ),
            'attribute' => $attribute,
        ) );
    }

    /**
     * AJAX handler to update main feed attribute mapping from validation quick fix.
     *
     * @since 7.4.58
     * @return void
     */
    public function ajax_apply_validation_mapping_fix() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        $feed_id = isset( $_POST['feed_id'] ) ? absint( $_POST['feed_id'] ) : 0;

        if ( ! $feed_id || 'product-feed' !== get_post_type( $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
        }

        if ( ! current_user_can( 'edit_post', $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $feed_status = get_post_meta( $feed_id, '_rex_feed_status', true );
        if ( in_array( $feed_status, array( 'processing', 'In queue' ), true ) ) {
            wp_send_json_error( array( 'message' => __( 'Wait for the current feed generation to finish.', 'rex-product-feed' ) ) );
        }

        $attribute    = isset( $_POST['attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['attribute'] ) ) : '';
        $replace      = isset( $_POST['replace'] ) ? sanitize_text_field( wp_unslash( $_POST['replace'] ) ) : '';
        $static_value = isset( $_POST['static_value'] ) ? sanitize_text_field( wp_unslash( $_POST['static_value'] ) ) : '';
        $is_static    = isset( $_POST['is_static'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['is_static'] ) );

        if ( '' === $attribute ) {
            wp_send_json_error( array( 'message' => __( 'Invalid attribute parameter.', 'rex-product-feed' ) ) );
        }

        if ( ( $is_static && '' === $static_value ) || ( ! $is_static && '' === $replace ) ) {
            wp_send_json_error( array( 'message' => __( 'Select or enter a replacement value.', 'rex-product-feed' ) ) );
        }

        $type  = $is_static ? 'static' : 'meta';
        $value = $is_static ? $static_value : $replace;

        $updated = Rex_Feed_Quick_Fix::update_feed_mapping( $feed_id, $attribute, $type, $value );

        if ( ! $updated ) {
            wp_send_json_error( array( 'message' => __( 'Could not find matching attribute in feed configuration.', 'rex-product-feed' ) ) );
        }

        wp_send_json_success( array(
            'message'        => __( 'Feed mapping updated. Click Fix Feed to regenerate.', 'rex-product-feed' ),
            'attribute'      => $attribute,
            'type'           => $type,
            'value'          => $value,
            'original_type'  => is_array( $updated ) ? ( $updated['original_type'] ?? 'meta' ) : 'meta',
            'original_value' => is_array( $updated ) ? ( $updated['original_value'] ?? '' ) : '',
        ) );
    }

    /**
     * Revert one applied validation feed mapping fix.
     *
     * @since 7.4.60
     * @return void
     */
    public function ajax_revert_validation_mapping_fix() {
        check_ajax_referer( 'rex-wpfm-ajax', 'security' );

        $feed_id = isset( $_POST['feed_id'] ) ? absint( $_POST['feed_id'] ) : 0;

        if ( ! $feed_id || 'product-feed' !== get_post_type( $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'rex-product-feed' ) ) );
        }

        if ( ! current_user_can( 'edit_post', $feed_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $feed_status = get_post_meta( $feed_id, '_rex_feed_status', true );
        if ( in_array( $feed_status, array( 'processing', 'In queue' ), true ) ) {
            wp_send_json_error( array( 'message' => __( 'Wait for the current feed generation to finish.', 'rex-product-feed' ) ) );
        }

        $attribute      = isset( $_POST['attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['attribute'] ) ) : '';
        $original_type  = isset( $_POST['original_type'] ) ? sanitize_text_field( wp_unslash( $_POST['original_type'] ) ) : 'meta';
        $original_value = isset( $_POST['original_value'] ) ? sanitize_text_field( wp_unslash( $_POST['original_value'] ) ) : '';

        if ( '' === $attribute ) {
            wp_send_json_error( array( 'message' => __( 'Invalid attribute parameter.', 'rex-product-feed' ) ) );
        }

        $type  = 'static' === $original_type ? 'static' : 'meta';
        $value = $original_value;

        $updated = Rex_Feed_Quick_Fix::update_feed_mapping( $feed_id, $attribute, $type, $value );

        if ( ! $updated ) {
            wp_send_json_error( array( 'message' => __( 'Could not find matching attribute in feed configuration.', 'rex-product-feed' ) ) );
        }

        wp_send_json_success( array(
            'message'   => __( 'Mapping change unstaged.', 'rex-product-feed' ),
            'attribute' => $attribute,
            'type'      => $type,
            'value'     => $value,
        ) );
    }

    /**
     * Auto-clear validation results and auto-run validation after feed update.
     *
     * @since 7.4.59
     * @param int $feed_id The feed ID.
     */
    public function auto_clear_and_run_validation_on_feed_update( $feed_id ) {
        if ( ! $feed_id || $this->is_feed_validation_disabled( $feed_id ) ) {
            return;
        }
        // Clear old validation results so the template renders a clean slate
        // on the next page load (the JS will then auto-trigger validation via
        // a fresh AJAX request — see checkAutoTriggerValidation in JS).
        $results_handler = new Rex_Feed_Validation_Results( $feed_id );
        $results_handler->clear_results();
        delete_transient( 'rex_feed_validation_' . $feed_id );

        // For WP-Cron scheduled generation there is no browser/JS to trigger
        // the AJAX validation, so schedule a deferred cron event instead.
        // For manual/UI generation, the JS checkAutoTriggerValidation handles
        // it via AJAX in a separate PHP process (no scheduling needed here).
        if ( wp_doing_cron() ) {
            if ( ! wp_next_scheduled( 'rex_feed_validate_scheduled', array( $feed_id ) ) ) {
                wp_schedule_single_event( time() + 30, 'rex_feed_validate_scheduled', array( $feed_id ) );
            }
        }
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
