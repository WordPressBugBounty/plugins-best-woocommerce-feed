<?php
/**
 * Class Rex_Feed_System_Status
 *
 * @package Product Feed Manager for WooCommerce
 */

use Automattic\WooCommerce\Utilities\RestApiUtil;

/**
 * This class is responsible for showing the system statuses in settings menu
 *
 * @package Product Feed Manager for WooCommerce
 */
class Rex_Feed_System_Status {
	/**
	 * Get Status Page Info
	 *
	 * @return array
	 */
	public static function get_all_system_status() {
		$status = wpfm_get_cached_data( 'system_status' );
		if ( !$status ) {
			$status = array(
				self::get_wpfm_version(), // Product Feed Manager for WooCommerce Version.
				self::get_wpfm_pro_version(), // Product Feed Manager for WooCommerce - Pro Version.
				self::get_woocommerce_version(), // WooCommerce Version.
				self::get_wordpress_cron_status(), // WordPress Cron Status.
				self::get_feed_file_directory(),
				self::get_total_wc_products(), // Total WooCommerce Product by Types.
			);
			$status = array_merge( $status, self::get_server_info() );
			wpfm_set_cached_data( 'system_status', $status );
		}
		return $status;
	}

	/**
	 * Get plugin info from wordpress.org
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return false|mixed
	 */
	private static function get_plugin_info( $slug ) {
		if ( empty( $slug ) ) {
			return false;
		}

		$transient_key = 'wpfm_plugin_info_' . md5( $slug );
		$cached_info = get_transient( $transient_key );
		if ( false !== $cached_info ) {
			return $cached_info;
		}

		$args     = (object) array(
			'slug'   => $slug,
			'fields' => array(
				'sections'    => false,
				'screenshots' => false,
				'versions'    => false,
			),
		);
		$request  = array(
			'action'  => 'plugin_information',
			'request' => serialize( $args ), //phpcs:ignore
		);
		$url      = 'http://api.wordpress.org/plugins/info/1.0/';
		$response = wp_remote_post( $url, array( 'body' => $request ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$info = unserialize( $response[ 'body' ] ); //phpcs:ignore
		set_transient( $transient_key, $info, 12 * HOUR_IN_SECONDS );
		
		return $info;
	}

	/**
	 * Get Product Feed Manager for WooCommerce Version Status
	 *
	 * @return array|false
	 */
	private static function get_wpfm_version() {
		$status = 'error';
		if ( defined( 'WPFM_VERSION' ) ) {
			$installed_version = WPFM_VERSION;
			$latest_version    = self::get_plugin_info( 'best-woocommerce-feed' );

			if ( version_compare( $latest_version->version, $installed_version, '>' ) ) {
				$message = $installed_version . " - You are not using the latest version of Product Feed Manager for WooCommerce. Update Product Feed Manager for WooCommerce plugin to its latest version: " . $latest_version->version;
			}
			else {
				$message = $installed_version . " - You are using the latest version of Product Feed Manager for WooCommerce.";
				$status  = 'success';
			}

			return array(
				'label'   => 'Product Feed Manager for WooCommerce Version',
				'message' => $message,
				'status'  => $status,
			);
		}
		return false;
	}

	/**
	 * Get the latest version of WPFM Pro with EDD API
	 *
	 * @return mixed|void
	 */
	private static function get_wpfm_pro_latest_version() {
		$license = trim( get_option( 'wpfm_pro_license_key' ) );

		$transient_key = 'wpfm_pro_latest_version_' . md5( $license );
		$cached_version = get_transient( $transient_key );
		if ( false !== $cached_version ) {
			return $cached_version;
		}

		// data to send in our API request.
		$api_params = array(
			'edd_action' => 'get_version',
			'license'    => $license,
			'item_id'    => WPFM_SL_ITEM_ID, // The ID of the item in EDD
			'url'        => home_url(),
		);

		$response = wp_remote_post(
			WPFM_SL_STORE_URL,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$message = ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.', 'rex-product-feed' );
			return false;
		} else {
			$data = json_decode( wp_remote_retrieve_body( $response ) );
			set_transient( $transient_key, $data, 12 * HOUR_IN_SECONDS );
			return $data;
		}
	}

	/**
	 * Get Product Feed Manager for WooCommerce - Pro Version Status
	 *
	 * @return array|false
	 */
	private static function get_wpfm_pro_version() {
		$status = 'error';
		if ( defined( 'REX_PRODUCT_FEED_PRO_VERSION' ) ) {
			$installed_version = defined( 'REX_PRODUCT_FEED_PRO_VERSION' ) ? REX_PRODUCT_FEED_PRO_VERSION : '1.0.0';
			$latest_version    = self::get_wpfm_pro_latest_version();

			if ( isset( $latest_version->stable_version ) && version_compare( $latest_version->stable_version, $installed_version, '>' ) ) {
				$message = $installed_version . " - You are not using the latest version of Product Feed Manager for WooCommerce - Pro. Update Product Feed Manager for WooCommerce - Pro plugin to its latest version: " . $latest_version->stable_version;
			}
			elseif ( isset( $latest_version->stable_version ) && version_compare( $latest_version->stable_version, $installed_version, '==' ) ) {
				$message = $installed_version . " - You are using the latest version of Product Feed Manager for WooCommerce - Pro.";
				$status  = 'success';
			}
			else {
				$message = $installed_version;
				$status  = 'success';
			}

			return array(
				'label'   => 'Product Feed Manager for WooCommerce - Pro Version',
				'message' => $message,
				'status'  => $status,
			);
		}
		return false;
	}

	/**
	 * Get WooCommerce Version Status
	 *
	 * @return array
	 */
	private static function get_woocommerce_version() {
		$status            = 'error';
		$installed_version = ( function_exists( 'WC' ) ) ? WC()->version : '1.0.0';
		$latest_version    = self::get_plugin_info( 'woocommerce' );

		if ( !empty( $latest_version->version ) && version_compare( $latest_version->version, $installed_version, '>' ) ) {
			$message = $installed_version . " - You are not using the latest version of WooCommerce. Update WooCommerce plugin to its latest version: " . $latest_version->version;
		}
		else {
			$message = $installed_version . " - You are using the latest version of WooCommerce.";
			$status  = 'success';
		}

		return array(
			'label'   => 'WooCommerce Version',
			'message' => $message,
			'status'  => $status,
		);
	}

	/**
	 * Gets WordPress cron status
	 *
	 * @return array
	 */
	private static function get_wordpress_cron_status() {
		$message = 'Enabled';
		$status  = 'success';
		if ( defined( 'DISABLE_WP_CRON' ) && true === DISABLE_WP_CRON ) {
			$message = "WordPress cron is disabled. The <b>Auto Feed Update</b> will not run if WordPress cron is Disabled.";
			$status  = 'error';
		}

		return array(
			'label'   => 'WP CRON',
			'message' => $message,
			'status'  => $status,
		);
	}

	/**
	 * Get Server Info
	 *
	 * @return array
	 */
	private static function get_server_info() {
		$report         = self::get_woocommerce_system_status_data();
		$environment    = $report[ 'environment' ];
		$theme          = $report[ 'theme' ];
		$active_plugins = $report[ 'active_plugins' ];
		$info           = array();

		if ( !empty( $environment ) ) {
			foreach ( $environment as $key => $value ) {
				if ( true === $value ) {
					$value = 'Yes';
				}
				elseif ( false === $value ) {
					$value = 'No';
				}

				if ( in_array( $key, array( 'wp_memory_limit', 'php_post_max_size', 'php_max_input_vars', 'max_upload_size' ) ) ) {
					$value = self::get_formated_bytes( $value );
				}

				$info[] = array(
					'label'   => ucwords( str_replace( array( '_', 'wp' ), array( ' ', 'WP' ), $key ) ),
					'message' => $value,
				);
			}
		}

		if ( !empty( $theme ) ) {
			$new_version = "";
			if ( version_compare( $theme[ 'version' ], $theme[ 'version_latest' ], '<' ) ) {
				$new_version = ' (Latest: ' . $theme[ 'version_latest' ] . ')';
			}

			$info[] = array(
				'label'   => 'Installed Theme',
				'message' => $theme[ 'name' ] . ' v' . $theme[ 'version' ] . $new_version,
			);
		}

		$info[] = array(
			'label'   => '',
			'status'  => '',
			'message' => "<h3>Installed Plugins</h3>",
		);

		if ( !empty( $active_plugins ) ) {
			foreach ( $active_plugins as $key => $plugin ) {
				if($plugin['name'] === 'Product Feed Manager for WooCommerce'){
					continue;
				}
				$slug = !empty( $plugin[ 'plugin' ] ) ? $plugin[ 'plugin' ] : '';
				$slug = explode( '/', $slug );

				$version_latest = array();
				if ( isset( $slug[ 0 ] ) ) {
					$version_latest = self::get_plugin_info( $slug[ 0 ] );
				}
				$new_version = '';
				$status      = 'success';

				if ( is_object( $version_latest ) && isset( $version_latest->version ) && version_compare( $plugin[ 'version' ], $version_latest->version, '<' ) ) {
					$new_version = ' (Latest: ' . $version_latest->version . ')';
					$status      = 'error';
				}

				$info[] = array(
					'label'   => $plugin[ 'name' ] . ' (' . $plugin[ 'author_name' ] . ')',
					'message' => $plugin[ 'version' ] . $new_version,
					'status'  => $status,
				);
			}
		}
		return $info;
	}

	/**
	 * Get grouped system status for the new UI
	 *
	 * @return array
	 */
	public static function get_grouped_system_status() {
		$groups = wpfm_get_cached_data( 'grouped_system_status' );
		if ( $groups ) {
			return $groups;
		}

		$report = self::get_woocommerce_system_status_data();
		$env = isset($report['environment']) ? $report['environment'] : array();
		$db = isset($report['database']) ? $report['database'] : array();
		
		$groups = array(
			'plugin_versions' => array(
				'label' => 'Plugin Versions',
				'icon' => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M9.0001 1.50003L16.5001 5.25003L9.0001 9.00003L1.5001 5.25003L9.0001 1.50003Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M1.5001 12.75L9.0001 16.5L16.5001 12.75M1.5001 9.00003L9.0001 12.75L16.5001 9.00003" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
				'items' => array()
			),
			'wordpress' => array(
				'label' => 'WordPress',
				'icon' => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M9.00008 16.5001C13.1422 16.5001 16.5001 13.1422 16.5001 9.00008C16.5001 4.85795 13.1422 1.50008 9.00008 1.50008C4.85795 1.50008 1.50008 4.85795 1.50008 9.00008C1.50008 13.1422 4.85795 16.5001 9.00008 16.5001Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M1.50008 9.00008H16.5001M9.00008 1.50008C10.8659 3.55189 11.9547 6.21639 12.0001 9.00008C11.9547 11.7838 10.8659 14.4483 9.00008 16.5001C7.1343 14.4483 6.04546 11.7838 6.00008 9.00008C6.04546 6.21639 7.1343 3.55189 9.00008 1.50008V1.50008Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
				'items' => array()
			),
			'server' => array(
				'label' => 'Server',
				'icon' => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M15 4.5H3C2.17157 4.5 1.5 5.17157 1.5 6V12C1.5 12.8284 2.17157 13.5 3 13.5H15C15.8284 13.5 16.5 12.8284 16.5 12V6C16.5 5.17157 15.8284 4.5 15 4.5Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M1.5 9H16.5M6 4.5V13.5M12 4.5V13.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
				'items' => array()
			),
			'php_config' => array(
				'label' => 'PHP Configuration',
				'icon' => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M5.25 12L2.25 9L5.25 6M12.75 6L15.75 9L12.75 12M10.5 4.5L7.5 13.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
				'items' => array()
			),
			'database' => array(
				'label' => 'Database',
				'icon' => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M15 4.5C15 5.74264 12.3137 6.75 9 6.75C5.68629 6.75 3 5.74264 3 4.5M15 4.5C15 3.25736 12.3137 2.25 9 2.25C5.68629 2.25 3 3.25736 3 4.5M15 4.5V13.5C15 14.7426 12.3137 15.75 9 15.75C5.68629 15.75 3 14.7426 3 13.5V4.5M3 9C3 10.2426 5.68629 11.25 9 11.25C12.3137 11.25 15 10.2426 15 9" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
				'items' => array()
			),
			'php_ext' => array(
				'label' => 'PHP Extensions',
				'icon' => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M9 13.5C11.4853 13.5 13.5 11.4853 13.5 9C13.5 6.51472 11.4853 4.5 9 4.5C6.51472 4.5 4.5 6.51472 4.5 9C4.5 11.4853 6.51472 13.5 9 13.5Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M12.182 5.81802L14.3033 3.6967M5.81802 12.182L3.6967 14.3033M5.81802 5.81802L3.6967 3.6967M12.182 12.182L14.3033 14.3033" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
				'items' => array()
			),
			'network' => array(
				'label' => 'Network & Remote Access',
				'icon' => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M9.00008 16.5001C13.1422 16.5001 16.5001 13.1422 16.5001 9.00008C16.5001 4.85795 13.1422 1.50008 9.00008 1.50008C4.85795 1.50008 1.50008 4.85795 1.50008 9.00008C1.50008 13.1422 4.85795 16.5001 9.00008 16.5001Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M1.50008 9.00008H16.5001M9.00008 1.50008C10.8659 3.55189 11.9547 6.21639 12.0001 9.00008C11.9547 11.7838 10.8659 14.4483 9.00008 16.5001C7.1343 14.4483 6.04546 11.7838 6.00008 9.00008C6.04546 6.21639 7.1343 3.55189 9.00008 1.50008V1.50008Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
				'items' => array()
			),
			'storage' => array(
				'label' => 'Feed Storage',
				'icon' => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M1.5 3C1.5 2.17157 2.17157 1.5 3 1.5H6.75L8.25 3.75H15C15.8284 3.75 16.5 4.42157 16.5 5.25V15C16.5 15.8284 15.8284 16.5 15 16.5H3C2.17157 16.5 1.5 15.8284 1.5 15V3Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
				'items' => array()
			),
		);

		$wpfm = self::get_wpfm_version();
		if ($wpfm) {
			$parts = explode(' - ', $wpfm['message']);
			$wpfm['message'] = $parts[0];
			$wpfm['sub_message'] = 'success' === $wpfm['status'] ? 'Latest version' : 'Consider upgrading to newer version';
			$groups['plugin_versions']['items'][] = $wpfm;
		}
		$wpfm_pro = self::get_wpfm_pro_version();
		if ($wpfm_pro) {
			$parts = explode(' - ', $wpfm_pro['message']);
			$wpfm_pro['message'] = $parts[0];
			$wpfm_pro['sub_message'] = 'success' === $wpfm_pro['status'] ? 'Latest version' : 'Consider upgrading to newer version';
			$groups['plugin_versions']['items'][] = $wpfm_pro;
		}
		$wc = self::get_woocommerce_version();
		if ($wc) {
			$parts = explode(' - ', $wc['message']);
			$wc['message'] = $parts[0];
			$wc['sub_message'] = 'success' === $wc['status'] ? 'Latest version' : 'Consider upgrading to newer version';
			$groups['plugin_versions']['items'][] = $wc;
		}

		$active_plugins = isset($report['active_plugins']) ? $report['active_plugins'] : array();
		foreach ( $active_plugins as $plugin ) {
			if ( $plugin['name'] === 'Product Feed Manager for WooCommerce' || $plugin['name'] === 'Product Feed Manager for WooCommerce - Pro' || $plugin['name'] === 'WooCommerce' ) {
				continue;
			}
			$slug = !empty( $plugin[ 'plugin' ] ) ? explode( '/', $plugin[ 'plugin' ] )[0] : '';
			$version_latest = $slug ? self::get_plugin_info( $slug ) : array();
			$sub_message = '';
			$status      = 'success';
			if ( is_object( $version_latest ) && isset( $version_latest->version ) && version_compare( $plugin[ 'version' ], $version_latest->version, '<' ) ) {
				$sub_message = 'Latest: ' . $version_latest->version;
				$status      = 'error';
			}
			$groups['plugin_versions']['items'][] = array(
				'label'   => $plugin[ 'name' ] . ( !empty($plugin['author_name']) ? ' (' . $plugin['author_name'] . ')' : '' ),
				'message' => $plugin[ 'version' ],
				'sub_message' => $sub_message,
				'status'  => $status,
			);
		}

		$groups['wordpress']['items'][] = array('label' => 'WP Version', 'message' => isset($env['wp_version']) ? $env['wp_version'] : '', 'status' => 'success');
		$groups['wordpress']['items'][] = array('label' => 'WP Multisite', 'message' => !empty($env['wp_multisite']) ? 'Yes' : 'No', 'status' => 'success');
		$groups['wordpress']['items'][] = array('label' => 'WP Memory Limit', 'message' => isset($env['wp_memory_limit']) ? self::get_formated_bytes($env['wp_memory_limit']) : '', 'status' => 'success');
		$groups['wordpress']['items'][] = array('label' => 'WP Debug Mode', 'message' => !empty($env['wp_debug_mode']) ? 'Yes' : 'No', 'status' => 'success');
		if (isset($env['environment'])) {
			$groups['wordpress']['items'][] = array('label' => 'WP Environment Type', 'message' => $env['environment'], 'status' => 'success');
		}
		
		$cron = self::get_wordpress_cron_status();
		if ($cron['status'] === 'error') {
			$cron['message'] = 'Disabled';
			$cron['sub_message'] = 'Auto Feed Update will not run.';
		}
		$groups['wordpress']['items'][] = $cron;
		
		$groups['wordpress']['items'][] = array('label' => 'Language', 'message' => isset($env['language']) ? $env['language'] : '', 'status' => 'success');
		
		$theme = isset($report['theme']) ? $report['theme'] : array();
		if (!empty($theme)) {
			$sub_message = '';
			$status = 'success';
			if ( isset($theme['version']) && isset($theme['version_latest']) && version_compare( $theme[ 'version' ], $theme[ 'version_latest' ], '<' ) ) {
				$sub_message = 'Latest: ' . $theme[ 'version_latest' ];
				$status = 'error';
			}
			$groups['wordpress']['items'][] = array(
				'label'   => 'Installed Theme',
				'message' => $theme[ 'name' ] . ' v' . $theme[ 'version' ],
				'sub_message' => $sub_message,
				'status'  => $status,
			);
		}

		$groups['server']['items'][] = array('label' => 'Server Info', 'message' => isset($env['server_info']) ? $env['server_info'] : '', 'status' => 'success');
		if (isset($env['server_architecture'])) {
			$groups['server']['items'][] = array('label' => 'Server Architecture', 'message' => $env['server_architecture'], 'status' => 'success');
		}
		$groups['server']['items'][] = array('label' => 'Home URL', 'message' => isset($env['home_url']) ? $env['home_url'] : '', 'status' => 'success');
		$groups['server']['items'][] = array('label' => 'Site URL', 'message' => isset($env['site_url']) ? $env['site_url'] : '', 'status' => 'success');
		if (isset($report['store']['store_id'])) {
			$groups['server']['items'][] = array('label' => 'Store ID', 'message' => $report['store']['store_id'], 'status' => 'success');
		}

		$php_v = isset($env['php_version']) ? $env['php_version'] : '';
		$php_status = version_compare($php_v, '7.4', '<') ? 'error' : 'success';
		$php_sub_message = $php_status === 'error' ? 'Consider upgrading to newer PHP version' : '';
		$groups['php_config']['items'][] = array('label' => 'PHP Version', 'message' => $php_v, 'sub_message' => $php_sub_message, 'status' => $php_status);
		$groups['php_config']['items'][] = array('label' => 'PHP Post Max Size', 'message' => isset($env['php_post_max_size']) ? self::get_formated_bytes($env['php_post_max_size']) : '', 'status' => 'success');
		$groups['php_config']['items'][] = array('label' => 'PHP Time Limit', 'message' => isset($env['php_max_execution_time']) ? $env['php_max_execution_time'] : '', 'status' => 'success');
		$groups['php_config']['items'][] = array('label' => 'PHP Max Input Vars', 'message' => isset($env['php_max_input_vars']) ? $env['php_max_input_vars'] : '', 'status' => 'success');
		$groups['php_config']['items'][] = array('label' => 'Max Upload Size', 'message' => isset($env['max_upload_size']) ? self::get_formated_bytes($env['max_upload_size']) : '', 'status' => 'success');
		$groups['php_config']['items'][] = array('label' => 'cURL Version', 'message' => isset($env['curl_version']) ? $env['curl_version'] : '', 'status' => 'success');
		$groups['php_config']['items'][] = array('label' => 'SUHOSIN Installed', 'message' => !empty($env['suhosin_installed']) ? 'Yes' : 'No', 'status' => 'success');
		$groups['php_config']['items'][] = array('label' => 'Default Timezone', 'message' => isset($env['default_timezone']) ? $env['default_timezone'] : '', 'status' => 'success');

		$db_version = isset($db['mysql_version']) ? $db['mysql_version'] : (isset($env['mysql_version']) ? $env['mysql_version'] : '');
		$db_string = isset($db['mysql_version_string']) ? $db['mysql_version_string'] : $db_version;
		$groups['database']['items'][] = array('label' => 'MySQL Version', 'message' => $db_version, 'status' => 'success');
		$groups['database']['items'][] = array('label' => 'MySQL Connect String', 'message' => $db_string, 'status' => 'success');

		$groups['php_ext']['items'][] = array('label' => 'Fsockopen / cURL enabled', 'message' => !empty($env['fsockopen_or_curl_enabled']) ? 'Yes' : 'No', 'status' => !empty($env['fsockopen_or_curl_enabled']) ? 'success' : 'error');
		$groups['php_ext']['items'][] = array('label' => 'SoapClient enabled', 'message' => !empty($env['soapclient_enabled']) ? 'Yes' : 'No', 'status' => !empty($env['soapclient_enabled']) ? 'success' : 'error');
		$groups['php_ext']['items'][] = array('label' => 'DOMDocument enabled', 'message' => !empty($env['domdocument_enabled']) ? 'Yes' : 'No', 'status' => !empty($env['domdocument_enabled']) ? 'success' : 'error');
		$groups['php_ext']['items'][] = array('label' => 'GZip enabled', 'message' => !empty($env['gzip_enabled']) ? 'Yes' : 'No', 'status' => !empty($env['gzip_enabled']) ? 'success' : 'error');
		$groups['php_ext']['items'][] = array('label' => 'Multibyte String', 'message' => !empty($env['mbstring_enabled']) ? 'Yes' : 'No', 'status' => !empty($env['mbstring_enabled']) ? 'success' : 'error');

		$groups['network']['items'][] = array('label' => 'Remote Post successful', 'message' => !empty($env['remote_post_successful']) ? 'Yes' : 'No', 'status' => !empty($env['remote_post_successful']) ? 'success' : 'error');
		$groups['network']['items'][] = array('label' => 'Remote Post Response', 'message' => isset($env['remote_post_response']) ? $env['remote_post_response'] : '', 'status' => 'success');
		$groups['network']['items'][] = array('label' => 'Remote Get successful', 'message' => !empty($env['remote_get_successful']) ? 'Yes' : 'No', 'status' => !empty($env['remote_get_successful']) ? 'success' : 'error');
		$groups['network']['items'][] = array('label' => 'Remote Get Response', 'message' => isset($env['remote_get_response']) ? $env['remote_get_response'] : '', 'status' => 'success');

		$storage = self::get_feed_file_directory();
		if ($storage['status'] === 'error') {
			$storage['sub_message'] = 'Not writable';
		}
		$groups['storage']['items'][] = $storage;
		$groups['storage']['items'][] = array('label' => 'Log Directory', 'message' => isset($env['log_directory']) ? $env['log_directory'] : '', 'status' => 'success');
		$groups['storage']['items'][] = array('label' => 'Log Directory Writable', 'message' => !empty($env['log_directory_writable']) ? 'Yes' : 'No', 'status' => !empty($env['log_directory_writable']) ? 'success' : 'error');
		
		$total_wc = self::get_total_wc_products();
		// Extract HTML and convert to simple string for sub_message
		$msg = strip_tags(str_replace('<br/>', ' | ', $total_wc['message']));
		$msg = trim($msg, ' | ');
		$msg = str_replace('✰ ', '', $msg);
		$total_wc['message'] = $msg;
		$groups['storage']['items'][] = $total_wc;

		wpfm_set_cached_data( 'grouped_system_status', $groups );

		return $groups;
	}

	/**
	 * Get Formatted bytes
	 *
	 * @param mixed $bytes Bytes.
	 * @param mixed $precision Precision.
	 *
	 * @return string
	 */
	private static function get_formated_bytes( $bytes, $precision = 2 ) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );

		$bytes = max( $bytes, 0 );
		$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow   = min( $pow, count( $units ) - 1 );

		// Uncomment one of the following alternatives.
		$bytes /= pow( 1024, $pow );

		return round( $bytes, $precision ) . ' ' . $units[ $pow ];
	}

	/**
	 * Get the feed file directory
	 *
	 * @return string[]
	 */
	private static function get_feed_file_directory() {
		$path = wp_upload_dir();
		$path = $path[ 'basedir' ] . '/rex-feed';
		if ( is_writable( $path ) ) {
			$status = 'success';
		}
		else {
			$status = 'error';
		}

		return array(
			'label'       => 'Product Feed Directory',
			'message'     => $path,
			'status'      => $status,
		);
	}

	/**
	 * Get system status as texts/strings
	 *
	 * @return string
	 */
	public static function get_system_status_text() {
		$grouped_status = self::get_grouped_system_status();
		$texts          = '';

		foreach ( $grouped_status as $group_id => $group ) {
			$texts .= "=== " . $group['label'] . " ===\n";
			foreach ( $group['items'] as $status ) {
				if ( isset( $status['label'] ) && '' !== $status['label'] && isset( $status['message'] ) && '' !== $status['message'] ) {
					$texts .= $status['label'] . ': ' . strip_tags($status['message']) . "\n";
				}
			}
			$texts .= "\n";
		}
		return $texts;
	}

	/**
	 * Get WooCommerce Total Products
	 *
	 * @return array
	 */
	private static function get_total_wc_products() {
		$status  = 'success';
		$message = '';

		// Product Totals by Product Type (WP Query)
		$type_totals = self::get_product_total_by_type();
		if ( !empty( $type_totals ) ) {
			foreach ( $type_totals as $type => $total ) {
				$message .= "✰ " . ucwords( $type ) . " Product: " . $total . "<br/>";
			}
		}

		// Total Product Variations (WP Query)
		$total_variations = self::get_total_product_variation();
		if ( $total_variations ) {
			$message .= "✰ Product Variations: " . $total_variations . "<br/>";
		}

		return array(
			'label'   => 'Total Products by Types',
			'status'  => $status,
			'message' => $message,
		);
	}

	/**
	 * Count products by type
	 *
	 * @return array
	 */
	private static function get_product_total_by_type() {
		$product_types = get_terms( 'product_type' );
		$product_count = array();
		$args          = array(
			'posts_per_page'         => -1,
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'order'                  => 'DESC',
			'fields'                 => 'ids',
			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters'       => false,
		);
		if ( !empty( $product_types ) ) {
			foreach ( $product_types as $product_type ) {
				$args[ 'tax_query' ]                  = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'product_type',
						'field'    => 'name',
						'terms'    => $product_type->name,
					),
				);
				$product_count[ $product_type->name ] = ( new WP_Query( $args ) )->post_count;
			}
		}

		return $product_count;
	}

	/**
	 * Count total product variations
	 *
	 * @return int
	 */
	private static function get_total_product_variation() {
		$args = array(
			'posts_per_page'         => -1,
			'post_type'              => 'product_variation',
			'post_status'            => 'publish',
			'order'                  => 'DESC',
			'fields'                 => 'ids',
			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters'       => false,
		);

		return ( new WP_Query( $args ) )->post_count;
	}

    /**
     * Retrieve the WooCommerce system status data.
     *
     * This method checks if WooCommerce is installed and active, then retrieves the system status data
     * based on the WooCommerce version. If WooCommerce version is 9.0.1 or higher, it uses the new REST API.
     * Otherwise, it uses the legacy API.
     *
     * @return array|null The system status data if WooCommerce is active, otherwise null.
     * @since 7.4.13
     */
    private static function get_woocommerce_system_status_data(){
        if ( class_exists( 'WooCommerce' ) && defined( 'WC_VERSION' ) ) {
            if ( version_compare( WC_VERSION, '9.0.1', '>=' ) ) {
                return wc_get_container()->get( RestApiUtil::class )->get_endpoint_data( '/wc/v3/system_status' );
            } else {
                return WC()->api->get_endpoint_data( '/wc/v3/system_status' );
            }
        }
        return null;
    }
}
