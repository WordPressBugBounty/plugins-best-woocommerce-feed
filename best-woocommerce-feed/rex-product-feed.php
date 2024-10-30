<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://rextheme.com
 * @since             1.0.0
 * @package           Rex_Product_Feed
 *
 * @wordpress-plugin
 * Plugin Name:       Product Feed Manager for WooCommerce
 * Plugin URI:        https://rextheme.com
 * Description:       Generate and maintain your WooCommerce product feed for Google Shopping, Social Catalogs, Yandex, Idealo, Vivino, Pinterest, eBay MIP, BestPrice, Skroutz, Fruugo, Bonanza & 180+ Merchants.
 * Version:           7.4.22
 * Author:            RexTheme
 * Author URI:        https://rextheme.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rex-product-feed
 * Domain Path:       /languages
 *
 * WP Requirement & Test
 * Requires at least: 5.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * WC Requirement & Test
 * WC requires at least: 5.6.0
 * WC tested up to: 8.8.3
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
if( !defined( 'WPFM_VERSION' ) ) {
    define( 'WPFM_VERSION', '7.4.22' );
}
if ( !defined( 'WPFM__FILE__' ) ) {
	define( 'WPFM__FILE__', __FILE__ );
}
if ( !defined( 'WPFM_PLUGIN_BASE' ) && defined( 'WPFM__FILE__' ) ) {
	define( 'WPFM_PLUGIN_BASE', plugin_basename( WPFM__FILE__ ) );
}
if ( !defined( 'WPFM_PLUGIN_DIR_URL' ) && defined( 'WPFM__FILE__' ) ) {
	define( "WPFM_PLUGIN_DIR_URL", plugin_dir_url( WPFM__FILE__ ) );
}
if ( !defined( 'WPFM_PLUGIN_DIR_PATH' ) && defined( 'WPFM__FILE__' ) ) {
	define( "WPFM_PLUGIN_DIR_PATH", plugin_dir_path( WPFM__FILE__ ) );
}
if ( !defined( 'WPFM_PLUGIN_ASSETS_FOLDER' ) && defined( 'WPFM_PLUGIN_DIR_URL' ) ) {
	define( "WPFM_PLUGIN_ASSETS_FOLDER", WPFM_PLUGIN_DIR_URL . 'admin/assets/' );
}
if ( !defined( 'WPFM_PLUGIN_ASSETS_FOLDER_PATH' ) && defined( 'WPFM_PLUGIN_DIR_PATH' ) ) {
	define( "WPFM_PLUGIN_ASSETS_FOLDER_PATH", WPFM_PLUGIN_DIR_PATH . 'admin/assets/' );
}
if( !defined( 'WPFM_PRO_REQUIRED_VERSION' ) ) {
    define( 'WPFM_PRO_REQUIRED_VERSION', '6.5.3' );
}
if ( !defined( 'WPFM_ETSY_REQUIRED_VERSION' ) ) {
	define( 'WPFM_ETSY_REQUIRED_VERSION', '1.0.1' );
}
if ( !defined( 'WPFM_PRO' ) ) {
	define( 'WPFM_PRO', '/best-woocommerce-feed-pro/rex-product-feed-pro.php' );
}
if ( !defined( 'WPFM_ETSY' ) ) {
	define( 'WPFM_ETSY', '/etsy-integration/etsy-integration.php' );
}
if ( !defined( 'WPFM_FREE_MAX_PRODUCT_LIMIT' ) ) {
	define( 'WPFM_FREE_MAX_PRODUCT_LIMIT', 200 );
}
if ( !defined( 'WPFM_SLUG' ) ) {
	define( 'WPFM_SLUG', 'best-woocommerce-feed' );
}
if ( !defined( 'WPFM_BASE' ) && defined( 'WPFM__FILE__' ) ) {
	define( 'WPFM_BASE', plugin_basename( WPFM__FILE__ ) );
}
if( !defined( 'HOURLY_SCHEDULE_HOOK' ) ) {
    define( 'HOURLY_SCHEDULE_HOOK', 'rex_feed_hourly_update' );
}
if( !defined( 'DAILY_SCHEDULE_HOOK' ) ) {
    define( 'DAILY_SCHEDULE_HOOK', 'rex_feed_daily_update' );
}
if( !defined( 'WEEKLY_SCHEDULE_HOOK' ) ) {
    define( 'WEEKLY_SCHEDULE_HOOK', 'rex_feed_weekly_update' );
}
if( !defined( 'SINGLE_SCHEDULE_HOOK' ) ) {
    define( 'SINGLE_SCHEDULE_HOOK', 'rex_feed_regenerate_feed_batch' );
}
if( !defined( 'WC_SINGLE_SCHEDULER' ) ) {
    define( 'WC_SINGLE_SCHEDULER', 'rex_feed_update_abandoned_child_list' );
}
if( !defined( 'WPFM_WEBHOOK_URL' ) ) {
    define( 'WPFM_WEBHOOK_URL', sanitize_url( 'https://rextheme.com/?mailmint=1&route=webhook&topic=contact&hash=7cec191a-2441-4b92-bb1b-e9deaa6023ba' ) );
}


/**
 * Check if WooCommerce is active
 **/
function rex_is_woocommerce_active() {
	$woocommerce = 'woocommerce/woocommerce.php';
	$is_active   = false;
	if( is_multisite() ) {
		$plugins = get_site_option( 'active_sitewide_plugins', [] );
		$is_active = isset( $plugins[ $woocommerce ] );
	}
	$plugins = get_option( 'active_plugins', [] );

	return $is_active ?: in_array( $woocommerce, $plugins );
}

/**
 * Check if WPFM Pro is compatible with new ui [version > 6.0.0]
 *
 * @return bool
 * @since 1.0.0
 */
function wpfm_pro_compatibility() {
	if ( defined( 'REX_PRODUCT_FEED_PRO_VERSION' ) && defined( 'WPFM_PRO_REQUIRED_VERSION' ) ) {
		return version_compare( REX_PRODUCT_FEED_PRO_VERSION, WPFM_PRO_REQUIRED_VERSION, '>=' );
	}
	return false;
}


/**
 * Check if WPFM ETSY is compatible with new ui [version > 6.0.0]
 *
 * @return bool
 */
function wpfm_etsy_compatibility() {
	if ( wpfm_get_plugin_version( WPFM_ETSY ) ) {
		return ( wpfm_get_plugin_version( WPFM_ETSY ) >= WPFM_ETSY_REQUIRED_VERSION );
	}
	return false;
}


/**
 * Gets plugin version
 *
 * @param $file
 * @return mixed|string
 */
function wpfm_get_plugin_version( $file ) {
	$plugin_file = WP_PLUGIN_DIR . $file;

	if ( file_exists( $plugin_file ) && function_exists( 'get_file_data' ) ) {
		$plugin_data = get_file_data( $plugin_file, array( 'Version' => 'Version' ), false );

		if ( $plugin_data && is_array( $plugin_data ) && isset( $plugin_data[ 'Version' ] ) ) {
			return $plugin_data[ 'Version' ];
		}
	}
	return false;
}


/**
 * Run dependency check and abort if required.
 **/
function rex_check_dependency() {
	$wpfm_pro_abs  = WP_PLUGIN_DIR . WPFM_PRO;
	$wpfm_etsy_abs = WP_PLUGIN_DIR . WPFM_ETSY;

	if ( ! rex_is_woocommerce_active() ) {
		add_action( 'admin_init', 'rex_product_feed_deactivate' );
		add_action( 'admin_notices', 'rex_product_feed_admin_notice' );
	}

	if ( defined( 'REX_PRODUCT_FEED_PRO_VERSION' ) && ( file_exists( $wpfm_pro_abs ) && ! wpfm_pro_compatibility() ) || ( file_exists( $wpfm_etsy_abs ) && ! wpfm_etsy_compatibility() ) ) {
		add_action( 'admin_notices', 'wpfm_pro_update_notice' );
	}
}


/**
 * Prints a notice to update WPFM Pro [version > 6.7.5]
 */
function wpfm_pro_update_notice() {
	$wpfm_pro_abs  = WP_PLUGIN_DIR . WPFM_PRO;
	$wpfm_etsy_abs = WP_PLUGIN_DIR . WPFM_ETSY;
	$wpfm_pro      = file_exists( $wpfm_pro_abs ) && ! wpfm_pro_compatibility() ? '<strong>WooCommerce Product Feed Manager Pro</strong>' : '';
	$wpfm_etsy     = file_exists( $wpfm_etsy_abs ) && ! wpfm_etsy_compatibility() ? '<strong>WooCommerce Product Feed Manager - Etsy Addon</strong>' : '';
	$and           = file_exists( $wpfm_pro_abs ) && ! wpfm_pro_compatibility() && file_exists( $wpfm_etsy_abs ) && ! wpfm_etsy_compatibility() ? ' and ' : '';

	$message = sprintf(
        esc_html__(
            'It looks like you have an older version of %1$s%2$s. Please update %1$s%2$s to the latest version to use Pro features properly.',
            'rex-product-feed'
        ),
        $wpfm_pro,
        $and,
        $wpfm_etsy
    );
    ?>
	<div class="error">
		<p>
			<?php echo $message; ?>
		</p>
	</div>
	<?php
}


/**
 * Display admin notice if WooCoomerce not activated
 **/
function rex_product_feed_admin_notice() {
	echo '<div class="error"><p><strong>WooCcommerce Product Feed Manager</strong> has been <strong>deactivated</strong>. Please install and activate <b>WooCoommerce</b> before activating this plugin.</p></div>';
	$activate = filter_input( INPUT_GET, 'activate', FILTER_SANITIZE_STRING );

	if ( $activate ) {
		unset( $_GET['activate'] );
	}
}


/**
 * Force deactivate the plugin.
 **/
function rex_product_feed_deactivate() {
	deactivate_plugins( plugin_basename( __FILE__ ) );
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rex-product-feed-activator.php
 */
function activate_rex_product_feed() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rex-product-feed-activator.php';
	if ( !rex_is_woocommerce_active() ) {
		// Stop activation redirect and show error
		wp_die( 'Sorry, but this plugin requires the WooCommerce Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>' );
	}
	else {
		Rex_Product_Feed_Activator::activate();
	}
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rex-product-feed-deactivator.php
 */
function deactivate_rex_product_feed() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rex-product-feed-deactivator.php';
	Rex_Product_Feed_Deactivator::deactivate();
}
register_activation_hook( __FILE__, 'activate_rex_product_feed' );
register_deactivation_hook( __FILE__, 'deactivate_rex_product_feed' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rex-product-feed.php';
require plugin_dir_path( __FILE__ ) . 'includes/helper.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rex_product_feed() {
	$plugin = new Rex_Product_Feed();
	$plugin->run();

	rex_check_dependency();
}
run_rex_product_feed();


/**
 * Initialize the tracker
 *
 * @return void
 */
function appsero_init_tracker_bwfm() {
	$client = new Appsero\Client( '5fab4a18-aaf4-4565-816a-47858011d96f', 'Product Feed Manager for WooCommerce', __FILE__ );

	// Active insights
	$client->insights()->init();
}

appsero_init_tracker_bwfm();


/**
 * is_edit_page
 * function to check if the current page is a post edit page
 *
 * @param  string $new_edit what page to check for accepts new - new post page ,edit - edit post page, null for either
 * @return boolean
 */
function is_edit_page( $new_edit = null ) {
	global $pagenow;
	if ( !is_admin() ) {
		return false;
	}
	if ( $new_edit == "edit" ) {
		return in_array( $pagenow, array( 'post.php' ) );
	} elseif ( $new_edit == "new" ) { // check for new post page
		return in_array( $pagenow, array( 'post-new.php' ) );
	} else {
		return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
	}
}

/**
 * @param $pages
 * @return mixed
 */
function wpfm_top_pages_modify( $pages ) {
	global $typenow;
	if ( ( is_edit_page( 'edit' ) && "product-feed" === $typenow ) || ( is_edit_page( 'new' ) && "product-feed" === $typenow ) ) {
		unset( $pages[0] );
		unset( $pages[1] );
	}
	return $pages;
}
add_filter( 'themify_top_pages', 'wpfm_top_pages_modify' );

/**
 * Display a custom upgrade notice message for a major plugin update.
 *
 * This function is responsible for displaying a custom message in the WordPress admin dashboard when a major update is available for a specific plugin. The message informs users to back up their data before proceeding with the upgrade.
 */
function wpfm_plugin_major_update_message( $data ) {
	if ( !empty( $data['upgrade_notice'] ) ) {
		$msg = str_replace( [ '<p>', '</p>' ], [ '<div>', '</div>' ], $data[ 'upgrade_notice' ] );
		?>
		<hr class="e-major-update-warning__separator" />
		<div class="e-major-update-warning rex-feed-major-update-warning">
			<div class="e-major-update-warning__icon">
				<i class="eicon-info-circle"></i>
			</div>
			<div>
				<div class="e-major-update-warning__message">
					<?php
					printf( wp_kses_post( wpautop( $msg ) ) );
					?>
				</div>
			</div>
		</div>
	<?php
	}
}
add_action( 'in_plugin_update_message-best-woocommerce-feed/rex-product-feed.php', 'wpfm_plugin_major_update_message' );

function rex_feed_redirect_after_activation( $plugin ) {
    if ( $plugin === plugin_basename( __FILE__ ) ) {
        $query_args = [
            'page' => 'setup-wizard',
            'plugin_activated' => 1
        ];
        $url = add_query_arg( urlencode_deep( $query_args ), esc_url( admin_url( 'edit.php?post_type=product-feed' ) ) );
        exit( wp_redirect( $url ) );
    }
}
//add_action( 'activated_plugin', 'rex_feed_redirect_after_activation' );

/**
 * Declare plugin's compatibility with WooCommerce HPOS
 *
 * @return void
 * @since 7.2.31
 */
function rex_feed_wc_hpos_compatibility() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
}
add_action( 'before_woocommerce_init', 'rex_feed_wc_hpos_compatibility' );
