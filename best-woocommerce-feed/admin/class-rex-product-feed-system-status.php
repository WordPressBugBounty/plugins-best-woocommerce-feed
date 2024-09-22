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
        return unserialize( $response[ 'body' ] ); //phpcs:ignore
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
		} else {
			return json_decode( wp_remote_retrieve_body( $response ) );
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
			$is_writable = "True";
		}
		else {
			$is_writable = "False";
		}

		return array(
			'label'       => 'Product Feed Directory',
			'message'     => $path,
			'is_writable' => $is_writable,
		);
	}

	/**
	 * Get system status as texts/strings
	 *
	 * @return string
	 */
	public static function get_system_status_text() {
		$system_status = self::get_all_system_status();
		$texts         = '';
		$index         = 1;
		foreach ( $system_status as $status ) {
			if ( isset( $status[ 'label' ] ) && '' !== $status[ 'label' ] && isset( $status[ 'message' ] ) && '' !== $status[ 'message' ] ) {
				$texts .= '#' . ( $index++ ) . ' ' . $status[ 'label' ] . ': ' . $status[ 'message' ] . "\n\n";
			}
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
