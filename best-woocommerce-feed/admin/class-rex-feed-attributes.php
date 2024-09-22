<?php
/**
 * Helper Class to retrieve Feed Attributes
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 */

/**
 *
 * Defines the attributes for feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Attributes
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Attributes {

	/**
	 * Get all attributes list
	 *
	 * @return mixed|void
	 */
	public static function get_attributes() {
		$attributes = [
			'Primary Attributes'           => self::get_primary_attributes(),
			'Price Attributes'             => self::get_price_attributes(),
			'Shipping Attributes'          => self::get_shipping_attributes(),
			'Tax Attributes'               => self::get_tax_attributes(),
			'Image Attributes'             => self::get_image_attributes(),
			'Product Attributes'           => self::get_product_attributes(),
			'Product Variation Attributes' => self::get_product_dynamic_attributes(),
			'Date Attributes'              => self::get_date_attributes(),
			'Glami Attributes'             => array( 'param_size' => 'Value - Size' )
		];

		$theme = wp_get_theme();

        if( ( !empty( $theme->name ) && 'Divi' === $theme->name ) || ( !empty( $theme->parent_theme ) && 'Divi' === $theme->parent_theme ) ) {
            $attributes = array_merge( $attributes, self::get_divi_builder_attributes() );
        }
        if( ( !empty( $theme->name ) && 'Woodmart' === $theme->name ) || ( !empty( $theme->parent_theme ) && 'Woodmart' === $theme->parent_theme ) ) {
            $attributes = array_merge( $attributes, self::get_woodmart_img_gallery_attributes() );
        }

		$plugins = get_option( 'active_plugins' );

		if ( in_array( 'perfect-woocommerce-brands/perfect-woocommerce-brands.php', $plugins, true ) ) {
			$attributes = array_merge( $attributes, self::get_perfect_brand_attributes() );
		}
		if ( in_array( 'brands-for-woocommerce/woocommerce-brand.php', $plugins, true ) ) {
			$attributes = array_merge( $attributes, self::get_brands_for_wc_attributes() );
		}
		if ( rexfeed_is_woocommerce_brand_active() || in_array( 'woo-brand/main.php', $plugins, true ) ) {
			$attributes = array_merge( $attributes, self::get_woocommerce_brand_attributes() );
		}
		if ( in_array( 'dropship-plugin/mantella.php', $plugins, true ) ) {
			$attributes = array_merge( $attributes, self::get_dropship_mantella_attributes() );
		}
		if ( wpfm_is_yoast_active() ) {
			$attributes = array_merge( $attributes, self::get_yoast_attributes() );
		}
		if ( in_array( 'seo-by-rank-math/rank-math.php', $plugins, true ) ) {
			$attributes = array_merge( $attributes, self::get_rankmath_attributes() );
		}
		if ( in_array( 'woo-discount-rules/woo-discount-rules.php', $plugins, true ) ) {
			$attributes = array_merge( $attributes, self::get_woo_discount_rules_attributes() );
		}
		if ( in_array( 'ean-for-woocommerce/ean-for-woocommerce.php', $plugins, true ) ) {
			$attributes = array_merge( $attributes, self::get_ean_by_woocommerce_attributes() );
		}
		if ( function_exists( 'wpfm_is_discount_rules_asana_plugins_active' ) && wpfm_is_discount_rules_asana_plugins_active() ) {
			$attributes = array_merge( $attributes, self::get_discounts_by_asana_plugins_attributes() );
		}
        if ( function_exists( 'wpfm_is_rex_dynamic_discount_active' ) && wpfm_is_rex_dynamic_discount_active() ) {
			$attributes = array_merge( $attributes, self::get_discounts_by_rex_dynamic_discount() );
		}
		if ( defined( 'ACF_VERSION' ) ) {
			$attributes = array_merge( $attributes, self::get_acf_fields() );
		}
		if ( apply_filters( 'wpfm_is_premium_activate', false ) ) {
			$attributes = array_merge( $attributes, self::get_wpfm_custom_attributes() );
		}
		if ( wpfm_aioseo_is_active() ) {
			$attributes = array_merge( $attributes, self::get_aioseo_attributes() );
		}

		// Get product custom attributes.
		$_custom_attributes                        = self::get_product_custom_attributes();
		$attributes[ 'Product Custom Attributes' ] = $_custom_attributes;

		// Get category map list.
		$cat_maps_array = array();
		$cat_maps       = get_option( 'rex-wpfm-category-mapping' );
		if ( $cat_maps ) {
			foreach ( $cat_maps as $key => $cat_map ) {
				$cat_maps_array[ $key ] = $cat_map[ 'map-name' ];
			}
		}

		$custom_taxonomies = array();
		$custom_taxonomies = apply_filters( 'wpfm_product_custom_taxonomy', $custom_taxonomies );

		if ( !empty( $custom_taxonomies ) ) {
			$attributes['Product Custom Taxonomies'] = $custom_taxonomies;
		}

		$attributes['Category Map'] = $cat_maps_array;
		return apply_filters( 'rex_wpfm_attributes', $attributes );
	}

	/**
	 * Gets Primary Attributes
	 *
	 * @return string[]
	 */
	public static function get_primary_attributes() {
		return [
			'id'                             => 'Product Id',
			'title'                          => 'Product Title',
			'description'                    => 'Product Description',
			'parent_desc'                    => 'Product Description [Parent]',
			'short_description'              => 'Product Short Description',
			'product_cat_ids'                => 'Product Category ID(s)',
			'product_cats'                   => 'Product Categories',
			'product_cats_path'              => 'Product Categories Path (with separator ">")',
			'product_cats_path_pipe'         => 'Product Categories Path (with separator "|")',
			'product_subcategory'            => 'Product Sub Categories Path (with separator ">")',
			'link'                           => 'Product URL',
			'checkout_link'                  => 'Product Checkout URL',
			'cart_link'                      => 'Product Cart URL',
			'parent_url'                     => 'Parent Product URL',
			'review_url'                     => 'Product Review URL',
			'condition'                      => 'Condition',
			'item_group_id'                  => 'Parent ID (Group ID)',
			'sku'                            => 'SKU',
			'parent_sku'                     => 'Parent SKU',
			'availability'                   => 'Availability',
			'availability_underscore'        => 'Availability (Without Underscore)',
			'availability_backorder_instock' => 'Availability (Backorder = in stock)',
			'availability_backorder'         => 'Availability (Backorder = backorder)',
			'availability_zero_three'        => 'Availability (0/3)',
			'availability_zero_one'          => 'Availability (0/1)',
			'quantity'                       => 'Quantity',
			'weight'                         => 'Weight',
			'width'                          => 'Width',
			'height'                         => 'Height',
			'length'                         => 'Length',
			'rating_total'                   => 'Total Rating',
			'rating_average'                 => 'Average Rating',
			'product_tags'                   => 'Tags',
			'identifier_exists'              => 'Identifier Exists Calculator',
			'in_stock'                       => 'In Stock (Y/N)',
			'promotion_id'                   => 'Promotion ID',
			'current_page'                   => 'Current Page',
			'author_name'                    => 'Author Name',
			'author_url'                     => 'Author URL',
		];
	}

	/**
	 * Gets price attributes
	 *
	 * @return string[]
	 */
	public static function get_price_attributes() {
		 return array(
			 'price'                  => 'Regular Price',
			 'current_price'          => 'Price',
			 'sale_price'             => 'Sale Price',
			 'price_with_tax'         => 'Regular price with tax',
			 'current_price_with_tax' => 'Price with tax',
			 'sale_price_with_tax'    => 'Sale price with tax',
			 'price_excl_tax'         => 'Regular price excl. tax',
			 'current_price_excl_tax' => 'Price excl. tax',
			 'sale_price_excl_tax'    => 'Sale price excl. tax',
			 'price_db'               => 'Regular Price (From DB)',
			 'current_price_db'       => 'Price (From DB)',
			 'sale_price_db'          => 'Sale Price (From DB)',
		 );
	}

	/**
	 * Gets image attributes
	 *
	 * @return string[]
	 */
	public static function get_image_attributes() {
		 return array(
			 'main_image'          => 'Main Image',
			 'featured_image'      => 'Featured Image',
			 'thumbnail_image'     => 'Thumbnail Image',
			 'all_image_array'     => 'All Images (raw data)',
			 'all_image'           => 'All Images (comma separated)',
			 'all_image_pipe'      => 'All Images (separated by "|")',
			 'variation_img'       => 'Variation Image',
			 'image_height'        => 'Height (main image)',
			 'image_width'         => 'Width (main image)',
			 'encoding_format'     => 'Encoding Format',
			 'keywords'            => 'Keywords',
			 'image_size'          => 'Image Size (bytes)',
			 'additional_image_1'  => 'Additional Image 1',
			 'additional_image_2'  => 'Additional Image 2',
			 'additional_image_3'  => 'Additional Image 3',
			 'additional_image_4'  => 'Additional Image 4',
			 'additional_image_5'  => 'Additional Image 5',
			 'additional_image_6'  => 'Additional Image 6',
			 'additional_image_7'  => 'Additional Image 7',
			 'additional_image_8'  => 'Additional Image 8',
			 'additional_image_9'  => 'Additional Image 9',
			 'additional_image_10' => 'Additional Image 10',
		 );
	}

	/**
	 * Gets date attributes
	 *
	 * @return string[]
	 */
	public static function get_date_attributes() {
		$attributes = array(
			'post_publish_date'         => 'Publish Date',
			'last_updated'              => 'Last Modified Date',
			'sale_price_dates_from'     => 'Sale Start Date',
			'sale_price_dates_to'       => 'Sale End Date',
			'sale_price_effective_date' => 'Sale Price Effective Date',
		);
		asort( $attributes );
		return $attributes;
	}

	/**
	 * Gets WoodMart Image Gallery attributes
	 *
	 * @return string[][]
	 */
	public static function get_woodmart_img_gallery_attributes() {
		return array(
			'Woodmart Image Gallery' => array(
				'woodmart_image_1'  => 'WM Variation Gallery Image 1',
				'woodmart_image_2'  => 'WM Variation Gallery Image 2',
				'woodmart_image_3'  => 'WM Variation Gallery Image 3',
				'woodmart_image_4'  => 'WM Variation Gallery Image 4',
				'woodmart_image_5'  => 'WM Variation Gallery Image 5',
				'woodmart_image_6'  => 'WM Variation Gallery Image 6',
				'woodmart_image_7'  => 'WM Variation Gallery Image 7',
				'woodmart_image_8'  => 'WM Variation Gallery Image 8',
				'woodmart_image_9'  => 'WM Variation Gallery Image 9',
				'woodmart_image_10' => 'WM Variation Gallery Image 10',
			),
		);
	}

    /**
     * Gets Divi Builder attributes
     *
     * @return string[][]
     * @since 7.2.32
     */
    public static function get_divi_builder_attributes() {
        return array(
            'Divi Builder' => array(
                'divi_pr_desc'        => 'Product Description',
                'divi_pr_parent_desc' => 'Product Description [Parent]',
            ),
        );
    }

	/**
	 * Gets Perfect Brand for WooCommerce attributes
	 *
	 * @return string[][]
	 */
	public static function get_perfect_brand_attributes() {
		 return array(
			 'Perfect Brand' => array(
				 'perfect_brand' => 'Product Brand',
			 ),
		 );
	}

	/**
	 * Gets Brands for WooCommerce attributes
	 *
	 * @return string[][]
	 */
	public static function get_brands_for_wc_attributes() {
		 return array(
			 'Brands for WooCommerce' => array(
				 'woocommerce_brand_berocket' => 'Brands for WooCommerce',
			 ),
		 );
	}

	/**
	 * Gets WooCommerce Brand attributes
	 *
	 * @return string[][]
	 */
	public static function get_woocommerce_brand_attributes() {
		 return array(
			 'Woocommerce Brand' => array(
				 'woocommerce_brand' => 'Woocommerce Brand',
			 ),
		 );
	}

	/**
	 * Retrieves ACF (Advanced Custom Fields) attributes from the WordPress database.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 *
	 * @return array Returns an associative array of ACF attributes (key-value pairs).
	 * @since 7.3.20
	 */
	public static function get_acf_fields() {
		$acf_attributes = wpfm_get_cached_data( 'acf_attributes' );
		if ( !is_array( $acf_attributes ) || empty( $acf_attributes ) ) {
			global $wpdb;
            $query = "SELECT child.post_excerpt AS `key`, child.post_title AS `value` FROM %1s AS child ";
            $query .= "JOIN %1s AS parent ON child.post_parent = parent.ID ";
            $query .= "WHERE parent.post_status=%s AND parent.post_type=%s ";
            $query .= "AND child.post_type=%s AND child.post_status=%s ";
            $query .= "GROUP BY child.post_excerpt";
			$query = $wpdb->prepare( $query, [ $wpdb->posts, $wpdb->posts, 'publish', 'acf-field-group', 'acf-field', 'publish' ] );
			$data  = $wpdb->get_results( $query, ARRAY_A );

			if ( is_array( $data ) && !empty( $data ) ) {
				foreach( $data as $item ) {
					if ( !empty( $item[ 'key' ] ) && !empty( $item[ 'value' ] ) ) {
						if ( !array_key_exists( $item[ 'key' ], self::get_primary_attributes() ) ) {
							$acf_attributes[ 'ACF Attributes' ][ $item[ 'key' ] ] = $item[ 'value' ];
						}
					}
				}
			}
			wpfm_set_cached_data( 'acf_attributes', $acf_attributes );
		}
		$acf_taxonomies = self::get_acf_taxonomy_fields();
		if ( !empty( $acf_taxonomies[ 'ACF Taxonomies' ] ) && is_array( $acf_taxonomies[ 'ACF Taxonomies' ] ) ) {
            if ( !is_array( $acf_attributes ) || empty( $acf_attributes ) ) {
                $acf_attributes = [];
            }
			$acf_attributes[ 'ACF Taxonomies' ] = $acf_taxonomies[ 'ACF Taxonomies' ];
		}
		return is_array( $acf_attributes ) && !empty( $acf_attributes ) ? $acf_attributes : [];
	}

	/**
	 * Retrieves ACF (Advanced Custom Fields) attributes from the WordPress database.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 *
	 * @return array Returns an associative array of ACF attributes (key-value pairs).
	 * @since 7.3.20
	 */
	public static function get_acf_taxonomy_fields() {
		$acf_taxonomies = wpfm_get_cached_data( 'acf_taxonomies' );
		if ( !is_array( $acf_taxonomies ) || empty( $acf_taxonomies ) ) {
			global $wpdb;
			$query = $wpdb->prepare( "SELECT `post_content` AS `settings` FROM %1s WHERE `post_type`=%s AND `post_status`=%s GROUP BY `post_content`", [ $wpdb->posts, 'acf-taxonomy', 'publish' ] );
			$data  = $wpdb->get_results( $query, ARRAY_A );

			if ( is_array( $data ) && !empty( $data ) ) {
				foreach( $data as $item ) {
					$settings = @unserialize($item[ 'settings' ]);
					if ( is_array( $settings[ 'object_type' ] ) && !empty( $settings[ 'object_type' ] ) && in_array( 'product', $settings[ 'object_type' ] ) && !empty( $settings[ 'taxonomy' ] ) ) {
						$acf_taxonomies[ 'ACF Taxonomies' ][ $settings[ 'taxonomy' ] ] = $settings[ 'labels' ][ 'menu_name' ] ?? $settings[ 'taxonomy' ];
					}
				}
			}
			wpfm_set_cached_data( 'acf_taxonomies', $acf_taxonomies );
		}
		return is_array( $acf_taxonomies ) && !empty( $acf_taxonomies ) ? $acf_taxonomies : [];
	}

	/**
	 * Gets Dropship by Mantella attributes
	 *
	 * @return string[][]
	 */
	public static function get_dropship_mantella_attributes() {
		 $attributes = array(
			 '_mantella_ean_number'               => 'EAN Code',
			 '_mantella_ean_number_show_in_front' => 'View on Frontend [EAN]',
			 '_mantella_brand'                    => 'Brand',
			 '_mantella_brand_show_in_front'      => 'Brand View on Frontend',
		 );
		 asort( $attributes );
		 return array(
			 'Dropship by Mantella' => $attributes,
		 );
	}

	/**
	 * Gets Yoast attributes
	 *
	 * @return string[][]
	 */
	public static function get_yoast_attributes() {
		 $attributes = array(
			 'yoast_title'              => 'Product Title [Yoast SEO]',
			 'yoast_primary_cat'        => 'Yoast Primary Category Name',
			 'yoast_primary_cat_id'     => 'Yoast Primary Category ID',
			 'yoast_meta_desc'          => 'Product Description [Yoast SEO]',
			 'yoast_primary_cats_path'  => 'Yoast Primary Category Path (with separator ">")',
			 'yoast_primary_cats_pipe'  => 'Yoast Primary Category Path (with separator "|")',
			 'yoast_primary_cats_comma' => 'Yoast Primary Category Path (with separator ",")',
		 );
		 asort( $attributes );
		 return array(
			 'YOAST Attributes' => $attributes,
		 );
	}

	/**
	 * Gets RankMath attributes
	 *
	 * @return string[][]
	 */
	public static function get_rankmath_attributes() {
		$attributes = array(
			'rankmath_primary_cat_id' => 'RankMath Primary Category ID',
			'rankmath_primary_cat'    => 'RankMath Primary Category Name',
		);
		return array(
			'RankMath Attributes' => $attributes,
		);
	}

	/**
	 * Gets Woo Discount Rules attributes
	 *
	 * @return string[][]
	 */
	public static function get_woo_discount_rules_attributes() {
		$attributes = array(
			'Woo Discount Rules' => array(
				'woo_discount_rules_price'       => 'Woo Discount Rules - Price',
				'woo_discount_rules_expire_date' => 'Woo Discount Rules - Expire Date',
			),
		);
		asort( $attributes );
		return $attributes;
	}

	/**
	 * Gets Custom Attributes by Product Feed for WooCommerce attributes
	 *
	 * @return string[][]
	 */
	public static function get_wpfm_custom_attributes() {
		$attributes = array(
			'custom_attributes__wpfm_product_brand'       => 'WPFM Product Brand',
			'custom_attributes__wpfm_product_gtin'        => 'WPFM Product GTIN',
			'custom_attributes__wpfm_product_mpn'         => 'WPFM Product MPN',
			'custom_attributes__wpfm_product_upc'         => 'WPFM Product UPC',
			'custom_attributes__wpfm_product_ean'         => 'WPFM Product EAN',
			'custom_attributes__wpfm_product_jan'         => 'WPFM Product JAN',
			'custom_attributes__wpfm_product_isbn'        => 'WPFM Product ISBN',
			'custom_attributes__wpfm_product_itf'         => 'WPFM Product ITF',
			'custom_attributes__wpfm_product_offer_price' => 'WPFM Product Offer Price',
			'custom_attributes__wpfm_product_offer_effective_date' => 'WPFM Product Offer Effective Date',
			'custom_attributes__wpfm_product_additional_info' => 'WPFM Product Additional Info',
			'custom_attributes__wpfm_product_color'       => 'WPFM Product Color',
			'custom_attributes__wpfm_product_size'        => 'WPFM Product Size',
			'custom_attributes__wpfm_product_pattern'     => 'WPFM Product Pattern',
			'custom_attributes__wpfm_product_material'    => 'WPFM Product Material',
			'custom_attributes__wpfm_product_age_group'   => 'WPFM Product Age Group',
			'custom_attributes__wpfm_product_gender'      => 'WPFM Product Gender',
			'custom_attributes__wpfm_product_item_type'   => 'WPFM Product Item Type'
		);
		asort( $attributes );
		return array(
			'WPFM Custom Attributes' => $attributes,
		);
	}

	/**
	 * Get product attributes
	 *
	 * @return array
	 */
	public static function get_product_attributes() {
		$taxonomies = wpfm_get_cached_data( 'product_attributes' ) ?: [];
		if ( empty( $taxonomies ) ) {
			// Load the main attributes.
			$global_attributes = wc_get_attribute_taxonomy_labels();
			if ( count( $global_attributes ) ) {
				foreach ( $global_attributes as $key => $value ) {
                    $taxonomies[ "bwf_attr_pa_{$key}" ] = $value;
				}
			}
			wpfm_set_cached_data( 'product_attributes', $taxonomies );
		}

		if ( is_array( $taxonomies ) ) {
			asort( $taxonomies );
		}
		return is_array( $taxonomies ) ? $taxonomies : [];
	}

	/**
	 * Get product dynamic attributes
	 *
	 * @return array
	 */
	public static function get_product_dynamic_attributes() {
		$attributes = wpfm_get_cached_data( 'product_dynamic_attributes' ) ?: [];
		if ( empty( $attributes ) ) {
			// Load the main attributes.
			$no_taxonomies   = array( 'category', 'post_tag', 'nav_menu', 'link_category', 'post_format', 'product_type', 'product_visibility', 'product_cat', 'product_shipping_class', 'product_tag' );
			$taxonomies      = get_taxonomies();
			$attr_name_clean = '';
			$attr_name       = '';
			$diff_taxonomies = array_diff( $taxonomies, $no_taxonomies );
			foreach ( $diff_taxonomies as $tax_diff ) {
				$taxonomy_details = get_taxonomy( $tax_diff );
				foreach ( $taxonomy_details as $kk => $vv ) {
					if ( 'name' === $kk ) {
						$attr_name = $vv;
					}
					if ( 'labels' === $kk ) {
						foreach ( $vv as $kw => $kv ) {
							if ( 'singular_name' === $kw ) {
								$attr_name_clean = ucfirst( $kv );
							}
						}
					}
				}
				$attributes[ $attr_name ] = $attr_name_clean;
			}
			wpfm_set_cached_data( 'product_dynamic_attributes', $attributes );
		}
		if ( array( $attributes ) ) {
			asort( $attributes );
		}
		return $attributes;
	}

	/**
	 * Get product custom attributes
	 *
	 * @return bool
	 */
	public static function get_product_custom_attributes() {
		$attributes = wpfm_get_cached_data( 'product_custom_attributes' ) ?: [];

		if ( empty( $attributes ) ) {
			global $wpdb;

			$sql = $wpdb->prepare(
				"SELECT `meta_key` AS name, `meta_value` AS value FROM %1s as postmeta
                INNER JOIN %1s AS posts
                ON postmeta.post_id = posts.id
                WHERE posts.post_type IN( 'product', 'product_variation' )
                AND posts.post_status = %s
                AND postmeta.meta_key != %s
                AND postmeta.meta_key NOT LIKE %s
                AND postmeta.meta_key NOT LIKE %s
                AND postmeta.meta_value NOT LIKE %s
                group by postmeta.meta_key
                ORDER BY postmeta.meta_key",
				$wpdb->postmeta,
				$wpdb->posts,
				'publish',
				'_product_attributess',
				'_wpfm_%',
				'pyre%',
				'field_%',
			);

			$data = $wpdb->get_results( $sql ); // phpcs:ignore

			if ( is_array( $data ) && !empty( $data ) ) {
				foreach ( $data as $value ) {
					if ( !empty( $inner_value[ 'name' ] ) && is_string( $inner_value[ 'name' ] ) ) {
						$value_display = str_replace( '_', ' ', $value->name );
					}
					else {
						$value_display = is_string( $value->name ) ? $value->name : '';
					}
                    $value_display                                    = trim( $value_display );
                    $attributes[ "custom_attributes_{$value->name}" ] = ucfirst( $value_display );
				}
			}

			$sql = $wpdb->prepare(
				'SELECT `meta_key` AS name, `meta_value` AS value FROM %1s AS postmeta
                INNER JOIN %1s AS posts
                ON postmeta.post_id = posts.id
                WHERE posts.post_type = %s
                AND posts.post_status = %s
                AND postmeta.meta_key = %s
                ORDER BY postmeta.meta_key',
				$wpdb->postmeta,
				$wpdb->posts,
				'product',
				'publish',
				'_product_attributes'
			);

			$data = $wpdb->get_results( $sql ); // phpcs:ignore

			if ( is_array( $data ) && !empty( $data ) ) {
				foreach ( $data as $value ) {
					$product_attributes = unserialize( $value->value );

					if ( !empty( $product_attributes ) ) {
						foreach ( $product_attributes as $inner_key => $inner_value ) {
							if ( !empty( $inner_value[ 'name' ] ) && is_string( $inner_value[ 'name' ] ) ) {
								$value_display = str_replace( '_', ' ', $inner_value[ 'name' ] );
							}
							else {
								$value_display = is_string( $inner_value ) ? $inner_value : '';
							}
							$attributes[ 'custom_attributes_' . $inner_key ] = ucfirst( $value_display );
						}
					}
				}
			}
			wpfm_set_cached_data( 'product_custom_attributes', $attributes );
		}

		if ( is_array( $attributes ) ) {
			asort( $attributes );
		} else {
			$attributes = array();
		}
		return $attributes;
	}

	/**
	 * Get Shipping Attributes
	 *
	 * @since 7.2.9
	 * @return string[]
	 */
	public static function get_shipping_attributes() {
		return [
			'shipping'          => 'Shipping (Google/Facebook Format)',
			'shipping_class'    => 'Shipping Class',
			'shipping_cost'     => 'Shipping Cost',
			'min_shipping_cost' => 'Lowest Shipping Cost'
		];
	}

	/**
	 * Get Tax Attributes
	 *
	 * @since 7.2.10
	 * @return string[]
	 */
	public static function get_tax_attributes() {
		return array(
			'tax'       => 'Tax (Google/Facebook Format)',
			'tax_class' => 'Tax Class',
		);
	}

	/**
	 * Gets attributes by EAN by WooCommerce
	 *
	 * @since 7.2.19
	 * @return string[][]
	 */
	public static function get_ean_by_woocommerce_attributes() {
		return array(
			'EAN by WooCommerce' => array(
				'_alg_ean' => get_option( 'alg_wc_ean_title', esc_html__( 'EAN', 'rex-product-feed' ) ),
			),
		);
	}

	/**
	 * Get price attributes by
	 * Discount Rules and Dynamic Pricing for WooCommerce
	 *
	 * @since 7.2.20
	 * @return string[][]
	 */
	public static function get_discounts_by_asana_plugins_attributes() {
		return [
            'Discounted Price - by Asana Plugins' => [
                'asana_price'                  => 'Regular Price',
                'asana_current_price'          => 'Price',
                'asana_sale_price'             => 'Sale Price',
                'asana_price_with_tax'         => 'Regular price with tax',
                'asana_current_price_with_tax' => 'Price with tax',
                'asana_sale_price_with_tax'    => 'Sale price with tax',
                'asana_price_excl_tax'         => 'Regular price excl. tax',
                'asana_current_price_excl_tax' => 'Price excl. tax',
                'asana_sale_price_excl_tax'    => 'Sale price excl. tax',
                'asana_price_db'               => 'Regular Price (From DB)',
                'asana_current_price_db'       => 'Price (From DB)',
                'asana_sale_price_db'          => 'Sale Price (From DB)'
            ]
        ];
	}

    /**
     * Retrieves discounts provided by the WooCommerce Dynamic Discount [by RexTheme] plugin.
     *
     * @return array Discount attributes provided by the WooCommerce Dynamic Discount[by RexTheme] plugin.
     *
     * @since 7.4.1
     */
    public static function get_discounts_by_rex_dynamic_discount() {
        return [
            'Product Based Discounted Price - by WooCommerce Dynamic Discount [by RexTheme]' => [
                'rexdd_price'                  => 'Discounted Regular Price',
                'rexdd_sale_price'             => 'Discounted Sale Price',
                'rexdd_current_price'          => 'Discounted Current Price',
                'rexdd_price_with_tax'         => 'Discounted Regular price with tax',
                'rexdd_current_price_with_tax' => 'Discounted Price with tax',
                'rexdd_sale_price_with_tax'    => 'Discounted Sale price with tax',
                'rexdd_price_excl_tax'         => 'Discounted Regular price excl. tax',
                'rexdd_current_price_excl_tax' => 'Discounted Price excl. tax',
                'rexdd_sale_price_excl_tax'    => 'Discounted Sale price excl. tax',
                'rexdd_price_db'               => 'Discounted Regular Price (From DB)',
                'rexdd_current_price_db'       => 'Discounted Price (From DB)',
                'rexdd_sale_price_db'          => 'Discounted Sale Price (From DB)'
            ]
        ];
    }

	/**
	 * Gets All In One SEO attributes
	 *
	 * @return string[][]
	 */
	public static function get_aioseo_attributes() {
		$attributes = array(
			'aioseo_primary_cat'        => 'All In One SEO Primary Category Name',
			'aioseo_primary_cat_id'     => 'All In One SEO Category ID',
		);
		asort( $attributes );
		return array(
			'AIO SEO Attributes' => $attributes,
		);
   }
}
