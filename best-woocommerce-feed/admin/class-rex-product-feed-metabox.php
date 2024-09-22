<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines all the Metaboxes for Products
 *
 * @package    Rex_Product_Metabox
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Product_Metabox
{
    private $prefix = 'rex_feed_';


    /**
     * Register all metaboxes.
     *
     * @since    1.0.0
     */
    public function register_metaboxes()
    {
        $data      = function_exists( 'rex_feed_get_sanitized_get_post' ) ? rex_feed_get_sanitized_get_post() : [];
        $data      = isset( $data[ 'get' ] ) ? $data[ 'get' ] : [];
        $post_id   = isset( $data[ 'post' ] ) ? sanitize_text_field( $data[ 'post' ] ) : '';
        $post_type = $post_id !== '' ? get_post_type( $post_id ) : '';

        add_action( 'add_meta_boxes', array( $this, 'rex_feed_filter_settings_section' ) );

        $feed_url = get_post_meta( $post_id, '_rex_feed_xml_file', true ) || get_post_meta( $post_id, 'rex_feed_xml_file', true );
        if ( $this->rex_feed_is_google_merchant() && $feed_url ) {
            add_action( 'add_meta_boxes', array( $this, 'rex_feed_google_merchant_section' ) );
        }

        add_action( 'add_meta_boxes', array( $this, 'rex_feed_feed_config_section' ) );
        add_action( 'add_meta_boxes', array( $this, 'rex_feed_product_settings_section' ) );
        add_action( 'add_meta_boxes', array( $this, 'rex_feed_product_filters_section' ) );
        add_action( 'add_meta_boxes', array( $this, 'rex_feed_feed_file_section' ) );

        if ( $post_type === 'product-feed' ) {
            $this->rex_feed_trigger_based_review_helper();
        }

        add_action( 'add_meta_boxes', array($this, 'rex_feed_upgrade_notice_section'));
        add_action( 'admin_notices', array( $this, 'rex_feed_feed_filter_settings_warning_popup' ) );
    }


    /**
     * Check if current merchant is google
     */
    private function rex_feed_is_google_merchant()
    {
        $data = function_exists( 'rex_feed_get_sanitized_get_post' ) ? rex_feed_get_sanitized_get_post() : [];
        $data = isset( $data[ 'get' ] ) ? $data[ 'get' ] : [];
        $feed_id = isset( $data['post'] ) ? sanitize_text_field($data['post']) : '';
        $merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true ) ?: get_post_meta( $feed_id, 'rex_feed_merchant', true );
        return 'google' === $merchant;
    }


    /**
     * Adding metabox for Filter & Settings button section
     */
    public function rex_feed_filter_settings_section()
    {
        add_meta_box(
            $this->prefix . 'head_btn',
            'Add New Feed',
            array( $this, 'rex_feed_generate_filter_settings_section' ),
            'product-feed',
            'normal',
            'high'
        );
    }


    /**
     * Generates the Add New Feed Heading
     * and Additional Filter & Settings Button
     */
    public function rex_feed_generate_filter_settings_section()
    {
        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-filter-settings-body-content.php';
    }


    /**
     * Adding metaboxes for merchant, feed format
     * and feed separator dropdown list section
     * & also feed config table section
     */
    public function rex_feed_feed_config_section()
    {
        add_meta_box(
            $this->prefix . 'conf',
            'Feed Configuration',
            array( $this, 'rex_feed_generate_merchant_dropdown_section' ),
            'product-feed',
            'normal',
            'core'
        );
        add_meta_box(
            $this->prefix . 'progress_bar',
            'Progress Bar',
            array( $this, 'progress_config_cb' ),
            'product-feed',
            'normal',
            'core'
        );
        add_meta_box(
            $this->prefix . 'config_heading',
            'Configure Feed Attributes and their values',
            array( $this, 'rex_feed_generate_config_table' ),
            'product-feed',
            'normal',
            'core'
        );
    }


    /**
     * Generates the feed merchant, feed format and separator dropdown lists section
     */
    public function rex_feed_generate_merchant_dropdown_section()
    {
        $saved_merchant   = get_post_meta( get_the_ID(), '_rex_feed_merchant', true ) ?: get_post_meta( get_the_ID(), 'rex_feed_merchant', true );
        $file_format      = get_post_meta( get_the_ID(), '_rex_feed_feed_format', true ) ?: get_post_meta( get_the_ID(), 'rex_feed_feed_format', true );

        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-merchant-dropdown-section.php';

        $countries = array(
            'AT' => 'Austria',
            'AU' => 'Australia',
            'CH' => 'Switzerland',
            'DE' => 'Germany',
            'CA' => 'Canada',
            'ES' => 'Spain',
            'FR' => 'France',
            'BE' => 'Belgium',
            'GB' => 'UK',
            'HK' => 'Hong Kong',
            'IE' => 'Ireland',
            'IN' => 'India',
            'IT' => 'Italy',
            'MY' => 'Malaysia',
            'NL' => 'Netherlands',
            'PH' => 'Philippines',
            'PL' => 'Poland',
            'SG' => 'Singapore',
            'US' => 'United States',
        );
        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-ebay-seller-sections.php';

        if ( wpfm_pro_compatibility() ) {
            do_action( 'wpfm_merchant_settings_fields', $this->prefix );
        }
    }


    /**
     * Generates the feed config table section
     */
    public function rex_feed_generate_config_table()
    {
        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-config-table.php';
    }


    /**
     * Adding metaboxes for product settings section
     */
    public function rex_feed_product_settings_section()
    {
        add_meta_box(
            $this->prefix . 'product_settings',
            __('Settings', 'rex-product-feed'),
            array( $this, 'rex_feed_generates_product_settings_section' ),
            'product-feed',
            'normal',
            'core'
        );
    }


    /**
     * Generates the product settings section
     */
    public function rex_feed_generates_product_settings_section()
    {
        $schedules = apply_filters(
            'wpfm_option_schedules', array(
            'no'     => __( 'No Interval', 'rex-product-feed' ),
            'hourly' => __( 'Hourly', 'rex-product-feed' ),
            'daily'  => __( 'Daily', 'rex-product-feed' ),
            'weekly' => __( 'Weekly', 'rex-product-feed' )
        ) );
        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-product-settings-section.php';
    }


    /**
     * Adding metaboxes for product filters section
     */
    public function rex_feed_product_filters_section()
    {
        add_meta_box(
            $this->prefix . 'product_filters',
            'Filters',
            array( $this, 'rex_feed_generates_product_filters_section' ),
            'product-feed',
            'normal',
            'core'
        );
    }


    /**
     * Generates the product filters section
     */
    public function rex_feed_generates_product_filters_section()
    {
        $options = array(
            'all'            => __( 'All Published Products', 'rex-product-feed' ),
            'featured'       => __( 'All Featured Products', 'rex-product-feed' ),
            'product_cat'    => __( 'Category Filters', 'rex-product-feed' ),
            'product_tag'    => __( 'Tag Filters', 'rex-product-feed' )
        );

        if ( wpfm_pro_compatibility() ) {
            $options = apply_filters( 'wpfm_product_filter_options', $options );
        }

        include_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-product-filter-header-section.php';
        // rex-contnet-filter__header end

        include_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-filter-products-dropdown-section.php';

        include_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-filter-description-body.php';

        do_action( 'rex_feed_before_taxonomy_fields', $this->prefix );

        $this->rex_feed_custom_filter_section();

        // rex-content-filter__area end
        $this->rex_feed_product_taxonomies();

        if ( wpfm_pro_compatibility() ) {
            do_action( 'wpfm_product_filter_fields', $this->prefix );
        }
    }


    /**
     * Generates custom filters in product filter section
     **/
    public function rex_feed_custom_filter_section()
    {
        include_once plugin_dir_path( __FILE__ ) . 'partials/feed-config-metabox-display-filter.php';
    }


    /**
     * Generates product categories and tags in product filter section
     **/
    public function rex_feed_product_taxonomies()
    {
        require plugin_dir_path(__FILE__) . 'partials/loading-spinner.php';
        echo '<div id="rex-feed-product-taxonomies" class="rex-feed-product-taxonomies">';
        echo '<div class="fil">';

        echo '</div>';
        echo '</div>';
    }


    /**
     * Adding metaboxes for feed file section
     */
    public function rex_feed_feed_file_section()
    {
        $feed_url = get_post_meta( get_the_ID(), '_' . $this->prefix . 'xml_file', true ) ?: get_post_meta( get_the_ID(), $this->prefix . 'xml_file', true );

        if ( strlen( $feed_url ) > 0 ) {
            add_meta_box(
                $this->prefix . 'file_link',
                'Feed URL',
                array( $this, 'rex_feed_generate_feed_file_section' ),
                'product-feed',
                'side',
                'core'
            );
        }
    }


    /**
     * Adding metaboxes for settings & filters saving warning popup
     *
     * @since 7.3.1
     */
    public function rex_feed_feed_filter_settings_warning_popup()
    {
        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-save-filters-changes-warning-popup.php';
        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-save-settings-changes-warning-popup.php';
        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-product-popup-pricing.php';
    }


    /**
     * Generates the feed file section
     */
    public function rex_feed_generate_feed_file_section()
    {
        $feed_url = get_post_meta( get_the_ID(), '_' . $this->prefix . 'xml_file', true ) ?: get_post_meta( get_the_ID(), $this->prefix . 'xml_file', true );
        $feed_url = esc_url( $feed_url );

        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-feed-file-section-content.php';
    }


    /**
     * Helper function to decide if review request
     * metabox needs to be generated.
     **/
    private function rex_feed_trigger_based_review_helper()
    {
        $show_review_request = get_option( 'rex_feed_review_request' );

        if ( ! empty( $show_review_request ) && isset( $show_review_request[ 'show' ] ) && $show_review_request[ 'show' ] ) {

            if ( isset( $show_review_request[ 'frequency' ] ) ) {
                if ( $show_review_request[ 'frequency' ] == 'immediate' ) {
                    add_action( 'admin_notices', array( $this, 'rex_feed_generate_review_request_section' ) );
                }
                elseif ( $show_review_request[ 'frequency' ] == 'one_week' ) {
                    $last_shown_date = $show_review_request[ 'time' ];
                    $current_date    = time();
                    $current_date    = new DateTime( date( 'Y-m-d', $current_date ) );
                    $last_shown_date = new DateTime( date( 'Y-m-d', $last_shown_date ) );
                    $date_diff       = $last_shown_date->diff( $current_date );

                    if ( $date_diff->d > 7 ) {
                        add_action( 'admin_notices', array( $this, 'rex_feed_generate_review_request_section' ) );
                    }
                }
            }
        }
    }


    /**
     * Generates body contents for trigger based review request section
     **/
    public function rex_feed_generate_review_request_section()
    {
        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-review-request-body-content.php';
    }

    /**
     * Adding metaboxe for google merchant section
     */
    public function rex_feed_google_merchant_section() {
        add_meta_box(
            $this->prefix . 'google_merchant',
            esc_html__( 'Send to Google Merchant', 'rex-product-feed' ),
            array( $this, 'rex_feed_generate_google_merchant_section' ),
            'product-feed',
            'normal',
            'core'
        );
    }

    /**
     * Generates google merchant section
     **/
    public function rex_feed_generate_google_merchant_section()
    {
        echo '<h2>' . esc_attr__( 'Send to Google Merchant', 'rex-product-feed' ) . '</h2>';

        $schedules = array(
            'monthly' => __( 'Monthly', 'rex-product-feed' ),
            'weekly'  => __( 'Weekly', 'rex-product-feed' ),
            'hourly'  => __( 'Hourly', 'rex-product-feed' ),
        );

        $month_array = range( 1, 31 );
        array_unshift( $month_array, "" );
        unset( $month_array[ 0 ] );

        $weeks = array(
            'monday'    => 'Monday',
            'tuesday'   => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday'  => 'Thursday',
            'friday'    => 'Friday',
            'saturday'  => 'Saturday',
            'sunday'    => 'Sunday',
        );

        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-google-merchant.php';

    }


    /**
     * Adding metaboxe for upgrade notice for pro section
     */
    public function rex_feed_upgrade_notice_section()
    {
        add_meta_box(
            $this->prefix . 'upgrade_notice',
            esc_html__( 'Upgrade Notice', 'rex-product-feed' ),
            array( $this, 'rex_feed_generate_upgrade_notice_section' ),
            'product-feed',
            'side',
            'core'
        );
    }

    /**
     * Generates upgrade notice for pro section
     **/
    public function rex_feed_generate_upgrade_notice_section()
    {
        require_once plugin_dir_path( __FILE__ ) . 'partials/rex-feed-upgrade-to-pro-notice-section.php';
    }

    //  ==================================================

    /**
     * Display Feed Config Metabox.
     *
     * @return void
     * @author RexTheme
     **/
    public function progress_config_cb()
    {

        echo '<div id="rex-feed-progress" class="rex-feed-progress">';
        require_once plugin_dir_path( __FILE__ ) . 'partials/progress-bar.php';
        echo '</div>';
    }
}