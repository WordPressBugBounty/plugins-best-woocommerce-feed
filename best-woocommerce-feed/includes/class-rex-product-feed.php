<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/includes
 * @author     RexTheme <info@rextheme.com>
 */


class Rex_Product_Feed {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Rex_Product_Feed_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'rex-product-feed';

		if ( defined( 'WPFM_VERSION' ) ) {
			$this->version = WPFM_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Rex_Product_Feed_Loader. Orchestrates the hooks of the plugin.
	 * - Rex_Product_Feed_i18n. Defines internationalization functionality.
	 * - Rex_Product_Feed_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Get Composer Autoloader.
		 */
		$autoload_file_array = apply_filters( 'wpfm_autoload_file_array', array( plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php' ) );

		foreach ( $autoload_file_array as $file ) {
			require_once $file;
		}

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rex-product-feed-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rex-product-feed-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rex-product-feed-public.php';
		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rex-product-feed-setup-wizard.php';
		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rex-product-feed-create-contact.php';

		$this->loader = new Rex_Product_Feed_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Rex_Product_Feed_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Rex_Product_Feed_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
    private function define_admin_hooks() {
	    $plugin_admin   = new Rex_Product_Feed_Admin( $this->get_plugin_name(), $this->get_version() );
	    $feed_actions   = new Rex_Product_Feed_Actions();
	    $cpt            = new Rex_Product_CPT();
	    $ajax           = new Rex_Product_Feed_Ajax();
	    $metabox        = new Rex_Product_Metabox();
	    $rollback       = new Rex_Feed_Rollback();
	    $appsero_data   = new Rex_Product_Appsero_Data();
	    $scheduler      = new Rex_Feed_Scheduler();
        $special_banner = new Rex_Feed_Special_Occasion_Banner(
            'eid_ul_adha_2024',
            '2024-06-14 00:00:00',
            '2024-06-25 00:00:00'
        ); // Date format: YYYY-MM-DD HH:MM:SS

        if ( !defined( 'REX_PRODUCT_FEED_PRO_VERSION' ) && 'no' === get_option( 'rexfeed_hide_sales_notification_bar', 'no' ) ) {
            new Rex_Feed_Sales_Notification_Bar();
        }

	    $this->loader->add_action( 'admin_init', $plugin_admin, 'register_setup_wizard_page' );
	    $this->loader->add_action( 'admin_init', $plugin_admin, 'admin_redirects' );


	    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
	    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	    $this->loader->add_action( 'init', $cpt, 'register_cpt' );

	    $this->loader->add_action( 'admin_init', $metabox, 'register_metaboxes' );

        $this->loader->add_action( 'admin_init', 'Rex_Product_Feed_Ajax', 'init' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'load_admin_pages' );
        $this->loader->add_action( 'enter_title_here', $plugin_admin, 'change_feed_title_placeholder' );
        $this->loader->add_action( 'admin_footer', $plugin_admin, 'rex_admin_footer_style' );

        $this->loader->add_action( 'post_submitbox_start', $feed_actions, 'register_purge_button' );
        // remove bulk edit and quick edit for our feed cpt.
        $this->loader->add_filter( 'bulk_actions-edit-product-feed', $feed_actions, 'remove_bulk_edit' );
        $this->loader->add_filter( 'post_row_actions', $feed_actions, 'remove_quick_edit' );
        // Trigger review request on new feed publish.
        $this->loader->add_action( 'publish_product-feed', $feed_actions, 'show_review_request_markups', 99999 );
        $this->loader->add_action( 'draft_product-feed', $feed_actions, 'save_draft_feed_meta', 99999, 2 );
        $this->loader->add_action( 'after_delete_post', $feed_actions, 'delete_feed_files' );
        $this->loader->add_action( 'admin_init', $feed_actions, 'remove_logs' );
        $this->loader->add_action( 'admin_notices', $feed_actions, 'render_xml_error_message' );
        // Duplicate feed item.
        $this->loader->add_action( 'admin_action_wpfm_duplicate_post_as_draft', $feed_actions, 'duplicate_feed_as_draft' );
        $this->loader->add_filter( 'page_row_actions', $feed_actions, 'duplicate_feed_link', 10, 2 );
        $this->loader->add_action( 'wp_footer', $feed_actions, 'enable_facebook_pixel' );

        // Custom ajax for data base update.
        $this->loader->add_action( 'wp_ajax_rex_wpfm_database_update', 'Rex_Product_Feed_Ajax', 'database_update' );

        $this->loader->add_action( 'wp_ajax_nopriv_check_for_missing_attributes', $ajax, 'check_for_missing_attributes' );
        $this->loader->add_action( 'wp_ajax_check_for_missing_attributes', $ajax, 'check_for_missing_attributes' );

        $this->loader->add_action( 'admin_post_rex_feed_rollback', $rollback, 'feeds_rollback' );

        $this->loader->add_filter( 'best-woocommerce-feed_tracker_data', $appsero_data, 'send_merchant_info' );

        $this->loader->add_action( 'init', $scheduler, 'register_background_schedulers' );
        $this->loader->add_action( HOURLY_SCHEDULE_HOOK, $scheduler, 'hourly_cron_handler' );
        $this->loader->add_action( DAILY_SCHEDULE_HOOK, $scheduler, 'daily_cron_handler' );
        $this->loader->add_action( DAILY_SCHEDULE_HOOK, $scheduler, 'register_wc_abandoned_child_update_scheduler' );
        $this->loader->add_action( WC_SINGLE_SCHEDULER, $scheduler, 'update_wc_abandoned_child_list' );
        $this->loader->add_action( WEEKLY_SCHEDULE_HOOK, $scheduler, 'weekly_cron_handler' );
        $this->loader->add_action( SINGLE_SCHEDULE_HOOK, $scheduler, 'regenerate_feed_batch' );

	    $this->loader->add_action( 'woocommerce_update_non_option_setting', $plugin_admin, 'delete_shipping_transient', 99 );

	    $this->loader->add_action( 'admin_init', $special_banner, 'init' );
	    $this->loader->add_filter( 'post_updated_messages', $plugin_admin, 'post_updated_messages' );

	    $this->loader->add_filter( 'rex_feed_product_price_before_formatting', $feed_actions, 'update_price_compatibility_with_wpml', 10, 4 );
        $this->loader->add_filter( 'rex_feed_product_price_before_formatting', $feed_actions, 'get_converted_price_by_wmc', 10, 4 );
        $this->loader->add_filter( 'rex_feed_product_price_before_formatting', $feed_actions, 'get_converted_price_by_aelia', 10, 4 );
        $this->loader->add_filter( 'rex_feed_product_price_before_formatting', $feed_actions, 'get_converted_price_by_woocs', 10, 4 );

        //setup wizard ajax
        $this->loader->add_action( 'wp_ajax_create_contact', $ajax, 'create_contact' );
        $this->loader->add_action( 'wp_ajax_nopriv_create_contact', $ajax, 'create_contact' );
    }


	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Rex_Product_Feed_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_wpfm_add_to_cart', $plugin_public, 'wpfm_add_to_cart' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpfm_add_to_cart', $plugin_public, 'wpfm_add_to_cart' );
		$this->loader->add_action( 'init', $plugin_public, 'clear_woocommerce_cart_data' );
	}

    /**
     * Run the loader to execute all the hooks with WordPress.
     *
     * @since    1.0.0
     * @return void
     */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Rex_Product_Feed_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
