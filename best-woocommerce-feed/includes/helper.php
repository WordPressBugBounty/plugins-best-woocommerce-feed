<?php
if ( ! function_exists( 'wpfm_hierarchical_product_category_tree' ) ) {
	/**
	 * Print hierarchical product categories
	 *
	 * @param $cat
	 * @param array $config
	 */
	function wpfm_hierarchical_product_category_tree( $cat, $config = array() ) {
		$args = array(
			'parent'        => $cat,
			'hide_empty'    => false,
			'no_found_rows' => true,
		);

		$next      = get_terms( 'product_cat', $args );
		$separator = '';
		if ( $next ) :
			foreach ( $next as $cat ) :
				if ( $cat->parent !== 0 ) {
					$separator = '--';
				}
				$map_value = '';
				if ( !empty( $config ) ) {
					$key = array_search( $cat->term_id, array_column( $config, 'map-key' ) );
					if ( $key !== false ) {
						$map_value = $config[ $key ]['map-value'];
					}
				}

				ob_start();?>
				<div class='single-category'>
					<span class='label'><?php echo esc_html( $separator . $cat->name ) . ' (' . esc_html( $cat->count ) . ')'; ?></span>
					<div class='input-field'><input class='autocomplete category-suggest' type='text' name='category-<?php echo esc_attr( $cat->term_id ); ?>' value='<?php echo esc_attr( $map_value ); ?>' placeholder='Google Merchant Category'/></div>
				</div>
				<?php
				echo ob_get_clean();

				$separator = '';
				wpfm_hierarchical_product_category_tree( $cat->term_id, $config );
			endforeach;
		endif;
	}
}


if ( ! function_exists( 'is_wpfm_logging_enabled' ) ) {
	/**
	 * Check if logging is enabled or not
	 *
	 * @return bool
	 */
	function is_wpfm_logging_enabled() {
		$enable_log = get_option( 'wpfm_enable_log', 'no' ) == 'yes' ? true : false;
		return $enable_log;
	}
}


if ( !function_exists( 'wpfm_get_feed_list' ) ) {
	/**
	 * Get all feed lists
	 *
	 * @param $schedule
	 * @return int[]|WP_Post[]
	 */
	function wpfm_get_feed_list( $schedule ) {
		$args  = array(
			'post_type'      => 'product-feed',
			'post_status'    => array( 'publish' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => 'rex_feed_schedule',
					'value' => $schedule,
				),
			),
		);
		$query = new WP_Query( $args );
		return $query->get_posts();
	}
}


if ( !function_exists( 'wpfm_get_cached_data' ) ) {
	/**
	 * Get wpfm transient by key
	 *
	 * @param $key
	 * @return false|mixed
	 */
	function wpfm_get_cached_data( $key ) {
		if ( empty( $key ) ) {
			return false;
		}
		return get_transient( '_wpfm_cache_' . $key );
	}
}


if ( !function_exists( 'wpfm_set_cached_data' ) ) {
	/**
	 * set wpfm transient by key
	 *
	 * @param $key
	 * @param $value
	 * @param int   $expiration
	 * @return bool
	 */
	function wpfm_set_cached_data( $key, $value, $expiration = 0 ) {
		if ( empty( $key ) ) {
			return false;
		}
		if ( !$expiration ) {
			$expiration = get_option( 'wpfm_cache_ttl', 3 * HOUR_IN_SECONDS );
		}
		return set_transient( '_wpfm_cache_' . $key, $value, $expiration );
	}
}


if ( ! function_exists( 'wpfm_purge_cached_data' ) ) {
	/**
	 * Purge cached data stored as WordPress options.
	 *
	 * This function deletes cached data stored as WordPress options, with the option to delete either exact matches or use a LIKE query to match option names.
	 *
	 * @param string|null $key  The cache key or part of the key to be purged.
	 * @param bool        $like Set to true to use a LIKE query, or false to perform an exact match.
	 * @since 7.3.0
	 */
	function wpfm_purge_cached_data( $key = null, $like = false ) {
		global $wpdb;

		if ( !empty( $key ) ) {
			if ( $like ) {
				// Use a LIKE query to delete transient options matching the key pattern.
				$query = "DELETE FROM $wpdb->options WHERE `option_name` LIKE %s OR `option_name` LIKE %s";
				$wpdb->query( $wpdb->prepare( $query, "_transient_timeout__wpfm_cache_{$key}%", "_transient__wpfm_cache_{$key}%" ) );
			} else {
				// Perform an exact match to delete transient options with the key.
				$query = "DELETE FROM $wpdb->options WHERE `option_name` = %s OR `option_name` = %s";
				$wpdb->query( $wpdb->prepare( $query, "_transient_timeout__wpfm_cache_{$key}", "_transient__wpfm_cache_{$key}" ) );
			}
		} else {
			// Purge all cached data when no specific key is provided.
			$query = "DELETE FROM $wpdb->options WHERE ({$wpdb->options}.option_name LIKE %s) OR ({$wpdb->options}.option_name LIKE %s)";
			$query = $wpdb->prepare( $query, '_transient_timeout__wpfm_cache%', '_transient__wpfm_cache_%' );
			$wpdb->query( $query ); // Delete transients matching the specified pattern.
		}
	}
}


if ( ! function_exists( 'wpfm_replace_special_char' ) ) {
	function wpfm_replace_special_char( $feed ) {
		return str_replace(
			array( '&#8226;', '&#8221;', '&#8220;', '&#8217;', '&#8216;', '&trade;', '&amp;trade;', '&reg;', '&amp;reg;', '&deg;', '&amp;deg;', '&#xA9;', '' ),
			array( '•', '”', '“', '’', '‘', '™', '™', '®', '®', '°', '°', '©', "\n" ),
			$feed
		);
	}
}


if ( ! function_exists( 'wpfm_is_aelia_active' ) ) {
	/**
	 * @desc check if aelia is active.
	 *
	 * @return bool
	 * @since 7.0.0
	 */
	function wpfm_is_aelia_active() {
		$active_plugings         = get_option( 'active_plugins' );
		$aelia_plugin            = 'woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php';
		$aelia_foundation_plugin = 'wc-aelia-foundation-classes/wc-aelia-foundation-classes.php';

		return in_array( $aelia_plugin, $active_plugings ) && in_array( $aelia_foundation_plugin, $active_plugings );
	}
}

if ( ! function_exists( 'wpfm_is_wpml_active' ) ) {
	/**
	 * @desc check if `WPML Multilingual CMS` is active.
	 *
	 * @return bool
	 * @since 7.0.0
	 */
	function wpfm_is_wpml_active() {
		return defined( 'ICL_SITEPRESS_VERSION' );
	}
}

if ( ! function_exists( 'wpfm_is_polylang_active' ) ) {
	/**
	 * @desc check if Polylang is active.
	 *
	 * @return bool
	 * @since 7.0.1
	 */
	function wpfm_is_polylang_active() {
		return defined( 'POLYLANG_VERSION' ) || defined( 'POLYLANG_PRO' );
	}
}


if ( ! function_exists( 'wpfm_is_yoast_active' ) ) {
	/**
	 * @desc check if YOAST is active.
	 *
	 * @return bool
	 * @since 7.0.0
	 */
	function wpfm_is_yoast_active() {
		$active_plugings = get_option( 'active_plugins' );
		$yoast           = 'wordpress-seo/wp-seo.php';

		return in_array( $yoast, $active_plugings );
	}
}


if ( ! function_exists( 'wpfm_is_wmc_active' ) ) {
	/**
	 * @desc check if WooCommerce Multicurrency plugin is active.
	 *
	 * @return bool
	 * @since 7.0.0
	 */
	function wpfm_is_wmc_active() {
		$active_plugings = get_option( 'active_plugins' );
		$wmc             = 'woocommerce-multi-currency/woocommerce-multi-currency.php';
		$wmc_params      = get_option( 'woo_multi_currency_params', array() );
		return in_array( $wmc, $active_plugings ) && !empty( $wmc_params ) && isset( $wmc_params[ 'enable' ] ) && $wmc_params[ 'enable' ];
	}
}


if ( ! function_exists( 'wpfm_generate_csv_feed' ) ) {
	/**
	 * Generates CSV format
	 *
	 * @param $feed
	 * @param $file
	 * @param $separator
	 * @param $batch
	 * @return string
	 * @since 7.0.0
	 */
	function wpfm_generate_csv_feed( $feed, $file, $separator, $batch ) {
		$list = $feed;
		$list = is_array( $list ) ? $list : array();

		if ( $batch == 1 ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
		else {
			array_shift( $list );
		}

		$file = fopen(
			$file,
			/**
			 * Allow developers to modify the file open mode for writing CSV files
			 *
			 * @param string $mode File open mode. (See: https://www.php.net/manual/en/function.fopen.php)
			 *
			 * @since 7.3.21
			 */
			apply_filters( 'rexfeed_csv_fopen_mode', 'a' )
		);

		foreach ( $list as $line ) {
			$lines = array();
			foreach ( $line as $l ) {
				$lines[] = wpfm_replace_special_char( $l );
			}

			if ( $separator === 'semi_colon' ) {
				fputcsv( $file, $lines, ';' );
			}
			elseif ( $separator === 'pipe' ) {
				fputcsv( $file, $lines, '|' );
			}
			else {
				fputcsv( $file, $lines );
			}
		}
		fclose( $file );

		return 'true';
	}
}


if ( ! function_exists( 'wpfm_purge_browser_cache' ) ) {
	/**
	 * Clear browser cache
	 *
	 * @since 7.0.0
	 */
	function wpfm_purge_browser_cache() {
		header( "Expires: Tue, 01 Jan 2000 00:00:00 GMT" );
		header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
		header( "Cache-Control: no-store, no-cache, must-revalidate, max-age=0" );
		header( "Cache-Control: post-check=0, pre-check=0", false );
		header( "Pragma: no-cache" );
	}
}


if ( ! function_exists( 'wpfm_switch_site_lang' ) ) {
	/**
	 * Switches the site language and WooCommerce Multilingual (WCML) currency if WPML and WCML are active.
	 *
	 * @param string $language      The language to switch to.
	 * @param string $wcml_currency The WCML currency to set.
	 *
	 * @since 7.3.26
	 */
	function wpfm_switch_site_lang( $language, $wcml_currency ) {
		if ( wpfm_is_wpml_active() ) {
			global $sitepress;
			$sitepress->switch_lang( $language );
		}

		if ( wpfm_is_wcml_active() ) {
			global $woocommerce_wpml;
			$woocommerce_wpml->multi_currency->set_client_currency($wcml_currency);

			/**
			 * Run your custom functions right after the client currency for the front end is switched via AJAX requests.
			 *
			 * @param string $wcml_currency The new currency code is switched to, e.g. “USD”, “EUR”, etc.
			 *
			 * @since 7.3.26
			 */
			do_action('wcml_switch_currency', $wcml_currency);
		}
	}
}


if ( ! function_exists( 'rex_feed_get_roll_back_versions' ) ) {
	/**
	 * get rollback version of WPFM
	 *
	 * @return array|mixed
	 *
	 * @src Inspired from Elementor roll back options
	 */
	function rex_feed_get_roll_back_versions() {
		$rollback_versions = get_transient( 'rex_feed_rollback_versions_' . WPFM_VERSION );
		if ( false === $rollback_versions ) {
			$max_versions = 5;
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$plugin_information = plugins_api(
				'plugin_information',
				array(
					'slug' => WPFM_SLUG,
				)
			);
			if ( empty( $plugin_information->versions ) || ! is_array( $plugin_information->versions ) ) {
				return array();
			}

			natsort( $plugin_information->versions );
			$plugin_information->versions = array_reverse( $plugin_information->versions );

			$rollback_versions = array();

			$current_index = 0;
			foreach ( $plugin_information->versions as $version => $download_link ) {
				if ( $max_versions <= $current_index ) {
					break;
				}

				$lowercase_version         = strtolower( $version );
				$is_valid_rollback_version = ! preg_match( '/(trunk|beta|rc|dev)/i', $lowercase_version );

				/**
				 * Is rollback version is valid.
				 *
				 * Filters the check whether the rollback version is valid.
				 *
				 * @param bool $is_valid_rollback_version Whether the rollback version is valid.
				 */
				$is_valid_rollback_version = apply_filters(
					'rex_feed_is_valid_rollback_version',
					$is_valid_rollback_version,
					$lowercase_version
				);

				if ( ! $is_valid_rollback_version ) {
					continue;
				}

				if ( version_compare( $version, WPFM_VERSION, '>=' ) ) {
					continue;
				}

				$current_index++;
				$rollback_versions[] = $version;
			}

			set_transient( 'rex_feed_rollback_versions_' . WPFM_VERSION, $rollback_versions, WEEK_IN_SECONDS );
		}

		return $rollback_versions;
	}
}


if ( ! function_exists( 'rex_feed_get_default_variable_attributes' ) ) {
	/**
	 * Get variable product default attributes
	 *
	 * @param $product
	 * @return mixed
	 */
	function rex_feed_get_default_variable_attributes( $product ) {
		if ( $product ) {
			if ( method_exists( $product, 'get_default_attributes' ) ) {
				return $product->get_default_attributes();
			}
			else {
				return $product->get_variation_default_attributes();
			}
		}
		return array();
	}
}


if ( ! function_exists( 'rex_feed_find_matching_product_variation' ) ) {
	/**
	 * Get matching variation
	 *
	 * @param $product
	 * @param $attributes
	 * @return mixed
	 * @throws Exception
	 */
	function rex_feed_find_matching_product_variation( $product, $attributes ) {
		foreach ( $attributes as $key => $value ) {
			if ( strpos( $key, 'attribute_' ) === 0 ) {
				continue;
			}
			unset( $attributes[ $key ] );
			$attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
		}
		if ( class_exists( 'WC_Data_Store' ) ) {
			$data_store = WC_Data_Store::load( 'product' );
			return $data_store->find_matching_product_variation( $product, $attributes );
		}
		else {
			return $product->get_matching_variation( $attributes );
		}
	}
}


if ( ! function_exists( 'rex_feed_get_product_price' ) ) {
	/**
	 * Gets product price
	 *
	 * @param $product
	 * @return int|mixed|string
	 * @throws Exception
	 */
	function rex_feed_get_product_price( $product ) {
		if ( $product && !is_wp_error( $product ) ) {
			if ( $product->is_type( 'variable' ) ) {
				$default_variations = rex_feed_get_default_variable_attributes( $product );
				if ( $default_variations ) {
					$variation_id = rex_feed_find_matching_product_variation( $product, $default_variations );
					if ( $variation_id ) {
						$_variation_product = wc_get_product( $variation_id );
						return $_variation_product->get_regular_price();
					}
				}
				else {
					return $product->get_variation_regular_price();
				}
			}
			elseif ( $product->is_type( 'grouped' ) ) {
				return rex_feed_get_grouped_price( $product, '_regular_price' );
			}
			elseif ( $product->is_type( 'composite' ) ) {
				return $product->get_composite_regular_price();
			}
			elseif ( $product->is_type( 'bundle' ) ) {
				return $product->get_bundle_price();
			}
			return $product->get_regular_price();
		}
		return '';
	}
}


if ( ! function_exists( 'rex_feed_get_grouped_price' ) ) {
	/**
	 * Get grouped price
	 *
	 * @since    2.0.3
	 */
	function rex_feed_get_grouped_price( $product, $type ) {
		if ( $product ) {
			$groupProductIds = $product->get_children();
			$price           = 99999999;

			if ( !empty( $groupProductIds ) ) {
				foreach ( $groupProductIds as $id ) {
					if ( get_post_meta( $id, $type, true ) !== '' ) {
						$price = $price > get_post_meta( $id, $type, true ) ? get_post_meta( $id, $type, true ) : $price;
					}
				}
				if ( $price === 99999999 ) {
					$price = '';
				}
			}
			return $price;
		}
		return '';
	}
}


if ( !function_exists( 'rex_feed_get_sanitized_get_post' ) ) {
	/**
	 * Gets sanitized $_GET and $_POST data or given data
	 *
	 * @return array
	 */
	function rex_feed_get_sanitized_get_post( $data = array() ) {
		if ( is_array( $data ) && !empty( $data ) ) {
			return filter_var_array( $data, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		}
		return array(
			'get'     => filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
			'post'    => filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
			'request' => filter_var_array( $_REQUEST, FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
		);
	}
}


if ( !function_exists( 'rex_feed_is_valid_xml' ) ) {
	/**
	 * @desc Check if a given xml file is valid.
	 * @since 7.2.9
	 * @param $file_url
	 * @return mixed|void
	 */
	function rex_feed_is_valid_xml( $file_url, $feed_id, $merchant_name ) {
		if ( 'marktplaats' === $merchant_name ) {
			$namespace = 'http://admarkt.marktplaats.nl/schemas/1.0';
		}
		else {
			$namespace = '';
		}

		libxml_use_internal_errors( true );
		$sxe        = simplexml_load_file( $file_url, 'SimpleXMLElement', 0, $namespace );
		$xml_errors = libxml_get_errors();
		return apply_filters( 'rex_feed_is_valid_xml', $sxe && empty( $xml_errors ), $sxe, $xml_errors, $feed_id );
	}
}


if ( !function_exists( 'rex_feed_is_wpfm_pro_active' ) ) {
	/**
	 * @desc Check if WPFM Pro is activated
	 * @since 7.2.20
	 * @return bool
	 */
	function rex_feed_is_wpfm_pro_active() {
		$active_plugings = get_option( 'active_plugins' );
		$wpfm_pro        = 'best-woocommerce-feed-pro/rex-product-feed-pro.php';
		return in_array( $wpfm_pro, $active_plugings ) || is_plugin_active_for_network( $wpfm_pro );
	}
}


if ( ! function_exists( 'wpfm_is_discount_rules_asana_plugins_active' ) ) {
    /**
     * @desc check if Discount Rules and Dynamic Pricing for WooCommerce
     * by Asana Plugins is active.
     *
     * @return bool
     * @since 7.2.20
     */
    function wpfm_is_discount_rules_asana_plugins_active() {
        $active_plugings  = get_option( 'active_plugins', [] );
        $asana_plugin     = 'easy-woocommerce-discounts/easy-woocommerce-discounts.php';
        $asana_plugin_pro = 'easy-woocommerce-discounts-pro/easy-woocommerce-discounts.php';

        return in_array( $asana_plugin, $active_plugings ) || in_array( $asana_plugin_pro, $active_plugings ) || is_plugin_active_for_network( $asana_plugin ) || is_plugin_active_for_network( $asana_plugin_pro );
    }
}


if ( ! function_exists( 'wpfm_get_abandoned_child' ) ) {
	/**
	 * @desc Get abandoned WooCommerce variation product ids
	 * @param $skip
	 * @param $offset
	 * @param $current_batch
	 * @param $per_batch
	 * @param $total_batch
	 * @param $products
	 * @return array|int[]|WP_Post[]
	 * @since 7.2.0
	 */
	function wpfm_get_abandoned_child( $skip = false, $offset = 0, $current_batch = 1, $per_batch = 0, $total_batch = 0, $products = array() ) {
        $child_ids = [];
		if ( !$skip ) {
			$product_info = Rex_Product_Feed_Ajax::get_product_number( array() );
			$total_batch  = $product_info[ 'total_batch' ];
			$per_batch    = get_option( 'rex-wpfm-product-per-batch', $per_batch );

            $query = 'SELECT child.ID FROM %1s AS child ';
            $query .= 'JOIN %1s AS parent ';
            $query .= 'ON child.post_parent = parent.ID ';
            $query .= 'WHERE child.post_type = %s ';
            $query .= 'AND parent.post_type = %s ';
            $query .= 'AND parent.post_status = %s';

            global $wpdb;
            $query = $wpdb->prepare( $query, $wpdb->posts, $wpdb->posts, 'product_variation', 'product', 'draft' );
            $child_ids = $wpdb->get_col( $query );
		}

		$args = array(
			'post_type'        => 'product_variation',
			'fields'           => 'ids',
			'post_parent'      => 0,
			'post_status'      => 'publish',
			'posts_per_page'   => $per_batch,
			'offset'           => $offset,
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'cache_results'    => false,
			'suppress_filters' => true,
		);

		$products = array_merge( get_posts( $args ), $products );
        $products = is_array( $child_ids ) && !empty( $child_ids ) ? array_merge( $child_ids, $products ) : $products;

		if ( $total_batch != $current_batch ) {
			$current_batch = (int) $current_batch + 1;
			$offset        = (int) $offset + (int) $per_batch;
			return wpfm_get_abandoned_child( true, $offset, $current_batch, $per_batch, $total_batch, $products );
		}
		return $products;
	}
}

if ( !function_exists( 'wpfm_get_woocommerce_shop_name' ) ) {
	/**
	 * @desc Get the WooCommerce shop name
	 * @return string
	 * @since 7.2.21
	 */
	function wpfm_get_woocommerce_shop_name() {
		$wc_shop_page_id = get_option( 'woocommerce_shop_page_id' );
		return get_the_title( $wc_shop_page_id );
	}
}

if( !function_exists( 'wpfm_get_woocommerce_shop_name' ) ) {
    /**
     * @desc Get the WooCommerce shop name
     * @return string
     * @since 7.2.21
     */
    function wpfm_get_woocommerce_shop_name() {
        $wc_shop_page_id = get_option( 'woocommerce_shop_page_id' );
        return get_the_title( $wc_shop_page_id );
    }
}

if( !function_exists( 'wpfm_restructure_custom_filter_args' ) ) {
    /**
     * Restructure old version custom filter args structure
     *
     * @param array $filters Custom filter options
     * @return array
     * @since 7.3.0
     */
    function wpfm_restructure_custom_filter_args( $filters ) {
        return isset( $filters[ 0 ][ 0 ] ) ? $filters : [ $filters ];
    }
}
if ( !function_exists( 'wpfm_get_the_term_path' ) ) {

	/**
	 * Get term path
	 *
	 * @param string|int $id ID
	 * @param string     $taxonomy Taxonomy
	 * @param string     $sep Separator
	 * @param bool       $is_visited If already visited
	 *
	 * @return array|string|WP_Error|WP_Term|null
	 */
	function wpfm_get_the_term_path( $id, $taxonomy, $sep = '', $is_visited = false ) {
		$term = get_term( $id, $taxonomy );
		if ( is_wp_error( $term ) ) {
			return $term;
		}
		$name = $term->name;
		if ( $is_visited ) {
			$path = '';
		}
		else {
			$path = 'Home';
		}
		if ( $term->parent && ( $term->parent != $term->term_id ) ) {
			$path .= function_exists( 'wpfm_get_the_term_path' ) ? wpfm_get_the_term_path( $term->parent, $taxonomy, $sep, true ) : '';
		}
		$path .= $sep . $name;
		return $path;
	}
}

if ( !function_exists( 'rex_feed_get_allowed_kseser' ) ) {

	/**
	 * @return array
	 */
	function rex_feed_get_allowed_kseser() {
		$allowed_html_post = wp_kses_allowed_html( 'post' );
		$allowed_html      = array(
			'option' => array(
				'value'    => true,
				'selected' => true,
			),
			'mark'   => array(
				'class' => true,
			),
			'span'   => array(
				'class' => true,
			),
		);

		return array_merge( $allowed_html_post, $allowed_html );
	}
}

if ( !function_exists( 'rexfeed_is_woocommerce_brand_active' ) ) {

	/**
	 * Helper function to check if WooCommerce brand plugin is active
	 *
	 * @return bool
	 * @since 7.3.5
	 */
	function rexfeed_is_woocommerce_brand_active() {
		$woocommerce_brand = 'woocommerce-brands/woocommerce-brands.php';
		$is_active   = false;
		if( is_multisite() ) {
			$plugins = get_site_option( 'active_sitewide_plugins', [] );
			$is_active = isset( $plugins[ $woocommerce_brand ] );
		}
		$plugins = get_option( 'active_plugins', [] );

		return $is_active ?: in_array( $woocommerce_brand, $plugins );
	}
}

if ( ! function_exists( 'wpfm_is_wcml_active' ) ) {
	/**
	 * Check if `WooCommerce Multilingual & Multicurrency` plugin is active
	 *
	 * @return bool
	 * @since 7.3.17
	 */
	function wpfm_is_wcml_active() {
		if ( defined( 'WCML_VERSION' ) ) {
			$wcml_settings = get_option( '_wcml_settings', [] );
			return !empty( $wcml_settings[ 'enable_multi_currency' ] );
		}
		return false;
	}
}

if ( ! function_exists( 'wpfm_is_rex_dynamic_discount_active' ) ) {
    /**
     * check if WooCommerce Dynamic Discount [by RexTheme] is active.
     *
     * @return bool
     * @since 7.4.1
     */
    function wpfm_is_rex_dynamic_discount_active() {
        return defined( 'REX_DYNAMIC_DISCOUNT_VERSION' );
    }
}

if( ! function_exists('wpfm_aioseo_is_active')){
	/**
	 * Check if All in One SEO Pack is active
	 *
	 * @return bool
	 * @since 7.4.10
	 */
	function wpfm_aioseo_is_active(){
		return function_exists( 'aioseo' );
	}

}