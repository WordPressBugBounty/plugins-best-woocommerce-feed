<?php
/**
 * The file that generates feed for Reddit Ads.
 *
 * A class definition that includes functions used for generating Reddit Ads feed.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed_Reddit_Ads
 * @subpackage Rex_Product_Feed_Reddit_Ads/admin/feed
 * @author     RexTheme <info@rextheme.com>
 */

use RexTheme\RexShoppingFeed\Containers\RexShopping;

/**
 * Class Rex_Product_Feed_Reddit_ads
 *
 * Generates product feeds for Reddit Ads platform following Reddit's catalog specifications.
 * Supports CSV, XML, TSV, and JSON formats.
 *
 * Reddit Ads Required Fields:
 * - id: Unique product identifier (max 128 chars)
 * - title: Product name (max 300 chars, plain text)
 * - description: Product description (max 5000 chars, plain text)
 * - link: Product landing page URL (max 2000 chars, HTTPS)
 * - image_link: Main product image URL (min 500x500px, max 20MB, JPG/PNG)
 * - price: Product price (format: <value> <ISO 4217 currency code>)
 *
 * @since 1.0.0
 */
class Rex_Product_Feed_Reddit_ads extends Rex_Product_Feed_Abstract_Generator {

	/**
	 * Feed configuration for Reddit Ads
	 *
	 * @var array
	 */
	private $reddit_config = array(
		'container'        => true,
		'item_wrapper'     => 'product',
		'items_wrapper'    => 'products',
		'namespace'        => null,
		'namespace_prefix' => '',
		'stand_alone'      => true,
		'version'          => '1.0',
		'wrapper_el'       => '',
		'wrapper'          => true,
		'datetime'         => false,
	);

	/**
	 * Create Feed for Reddit Ads
	 *
	 * Generates product feed in the specified format (XML, CSV, TSV, or JSON)
	 * following Reddit Ads catalog specifications.
	 *
	 * @return boolean|array
	 * @since 1.0.0
	 */
	public function make_feed() {
		// Initialize RexShopping for XML format
		if ( 'xml' === $this->feed_format && 1 === $this->batch ) {
			RexShopping::init(
				true,                    // wrapper
				'product',               // itemName
				null,                    // namespace
				'1.0',                   // version
				'products',              // rss (root element)
				true,                    // stand_alone
				'',                      // wrapperel
				''                       // namespace_prefix
			);
		}

		$should_regenerate = true;

		// Check if we should regenerate the feed
		$should_regenerate = Rex_Feed_Generator_Helper::wpfm_should_regenerate_feed(
			$this->id,
			$this->batch,
			$this->bypass,
			$this->products,
			$this->feed
		);

		if ( $should_regenerate ) {
			// Generate feed for both simple and variable products
			$this->generate_product_feed();
			$this->feed = $this->returnFinalProduct();

			// Cache the feed
			Rex_Feed_Generator_Helper::wpfm_cache_feed(
				$this->id,
				$this->batch,
				$this->feed,
				$this->products
			);
		}

		if ( $this->batch >= $this->tbatch ) {
			$this->save_feed( $this->feed_format );
			return array( 'msg' => 'finish' );
		}

		return $this->save_feed( $this->feed_format );
	}

	/**
	 * Return feed in appropriate format
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function returnFinalProduct() {
		if ( 'xml' === $this->feed_format ) {
			return $this->get_xml_feed();
		} elseif ( 'csv' === $this->feed_format ) {
			return $this->get_csv_feed();
		} elseif ( 'tsv' === $this->feed_format || 'txt' === $this->feed_format ) {
			return $this->get_tsv_feed();
		} elseif ( 'json' === $this->feed_format ) {
			return $this->get_json_feed();
		}

		return '';
	}

	/**
	 * Get XML feed
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_xml_feed() {
		// Return the XML string from RexShopping
		return RexShopping::asRss();
	}

	/**
	 * Get CSV feed
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_csv_feed() {
		return $this->processForCSV( $this->feed );
	}

	/**
	 * Get TSV feed
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_tsv_feed() {
		return $this->processForTSV( $this->feed );
	}

	/**
	 * Get JSON feed
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_json_feed() {
		return wp_json_encode( array( 'products' => $this->feed ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Process products for CSV format
	 *
	 * @param array $products Products array
	 * @return string CSV formatted string
	 * @since 1.0.0
	 */
	protected function processForCSV( $products ) {
		if ( empty( $products ) ) {
			return '';
		}

		$csv_data = array();
		$headers  = array_keys( reset( $products ) );
		$csv_data[] = $headers;

		foreach ( $products as $product ) {
			$row = array();
			foreach ( $headers as $header ) {
				$value = isset( $product[ $header ] ) ? $product[ $header ] : '';
				// Escape CSV special characters
				$value = str_replace( '"', '""', $value );
				$row[] = '"' . $value . '"';
			}
			$csv_data[] = $row;
		}

		$csv_string = '';
		foreach ( $csv_data as $row ) {
			$csv_string .= implode( ',', $row ) . "\n";
		}

		return $csv_string;
	}

	/**
	 * Process products for TSV format
	 *
	 * @param array $products Products array
	 * @return string TSV formatted string
	 * @since 1.0.0
	 */
	protected function processForTSV( $products ) {
		if ( empty( $products ) ) {
			return '';
		}

		$tsv_data = array();
		$headers  = array_keys( reset( $products ) );
		$tsv_data[] = $headers;

		foreach ( $products as $product ) {
			$row = array();
			foreach ( $headers as $header ) {
				$value = isset( $product[ $header ] ) ? $product[ $header ] : '';
				// Escape TSV special characters
				$value = str_replace( "\t", ' ', $value );
				$value = str_replace( "\n", ' ', $value );
				$value = str_replace( "\r", ' ', $value );
				$row[] = $value;
			}
			$tsv_data[] = $row;
		}

		$tsv_string = '';
		foreach ( $tsv_data as $row ) {
			$tsv_string .= implode( "\t", $row ) . "\n";
		}

		return $tsv_string;
	}

	/**
	 * Generate product feed
	 *
	 * Processes all products and adds them to the feed
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function generate_product_feed() {
		$product_meta_keys = Rex_Feed_Attributes::get_attributes();
		$total_products    = get_post_meta( $this->id, '_rex_feed_total_products', true );
		$total_products    = $total_products ?: array(
			'total'           => 0,
			'simple'          => 0,
			'variable'        => 0,
			'variable_parent' => 0,
			'group'           => 0,
		);

		if ( 1 === $this->batch ) {
			$total_products = array(
				'total'           => 0,
				'simple'          => 0,
				'variable'        => 0,
				'variable_parent' => 0,
				'group'           => 0,
			);
		}

		$simple_products    = array();
		$variation_products = array();
		$variable_parent    = array();
		$group_products     = array();

		foreach ( $this->products as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! is_object( $product ) ) {
				continue;
			}

			// Skip hidden products if configured
			if ( $this->exclude_hidden_products && ! $product->is_visible() ) {
				continue;
			}

			// Skip zero-priced products if configured
			if ( ! $this->include_zero_priced ) {
				$product_price = rex_feed_get_product_price( $product );
				if ( 0 == $product_price || '' == $product_price ) {
					continue;
				}
			}

			// Handle variable products
			if ( $product->is_type( 'variable' ) && $product->has_child() ) {
				if ( $this->variable_product && $this->is_out_of_stock( $product ) ) {
					$variable_parent[] = $product_id;
					$this->add_to_feed( $product, $product_meta_keys );
				}

				// Process variations
				if ( $this->variations ) {
					$variations = $this->exclude_hidden_products ? $product->get_visible_children() : $product->get_children();

					if ( $variations ) {
						foreach ( $variations as $variation_id ) {
							$variation_product = wc_get_product( $variation_id );
							if ( $variation_product && $this->is_out_of_stock( $variation_product ) ) {
								$variation_products[] = $variation_id;
								$this->add_to_feed( $variation_product, $product_meta_keys, 'variation' );
							}
						}
					}
				}
			}

			// Handle simple products
			if ( $this->is_out_of_stock( $product ) ) {
				if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) || $product->is_type( 'composite' ) || $product->is_type( 'bundle' ) ) {
					$simple_products[] = $product_id;
					$this->add_to_feed( $product, $product_meta_keys );
				}

				// Handle grouped products
				if ( $product->is_type( 'grouped' ) && $this->parent_product ) {
					$group_products[] = $product_id;
					$this->add_to_feed( $product, $product_meta_keys );
				}
			}
		}

		// Update total products count
		$total_products = array(
			'total'           => (int) $total_products['total'] + count( $simple_products ) + count( $variation_products ) + count( $group_products ) + count( $variable_parent ),
			'simple'          => (int) $total_products['simple'] + count( $simple_products ),
			'variable'        => (int) $total_products['variable'] + count( $variation_products ),
			'variable_parent' => (int) $total_products['variable_parent'] + count( $variable_parent ),
			'group'           => (int) $total_products['group'] + count( $group_products ),
		);

		update_post_meta( $this->id, '_rex_feed_total_products', $total_products );
	}

	/**
	 * Get product data according to feed configuration
	 *
	 * @param WC_Product $product Product object
	 * @param array      $product_meta_keys Product meta keys
	 * @return array Product data array
	 * @since 1.0.0
	 */
	protected function get_product_data( $product, $product_meta_keys ) {
		$product_data = array();

		foreach ( $this->feed_config['attributes'] as $attr_key => $attr_config ) {
			// Use the data retriever to get attribute value
			$data_retriever = new Rex_Product_Data_Retriever();
			$data_retriever->set_product( $product );
			$data_retriever->set_feed_rules( array( $attr_key => $attr_config ) );
			$data_retriever->set_product_meta_keys( $product_meta_keys );

			$value = $data_retriever->get_all_data();
			$value = isset( $value[ $attr_key ] ) ? $value[ $attr_key ] : '';

			// Apply Reddit Ads specific validations
			$value = $this->validate_reddit_field( $attr_key, $value );

			if ( '' !== $value ) {
				$product_data[ $attr_key ] = $value;
			}
		}

		return $product_data;
	}

	/**
	 * Validate field according to Reddit Ads specifications
	 *
	 * @param string $field_name Field name
	 * @param mixed  $value Field value
	 * @return mixed Validated value
	 * @since 1.0.0
	 */
	protected function validate_reddit_field( $field_name, $value ) {
		if ( empty( $value ) ) {
			return $value;
		}

		switch ( $field_name ) {
			case 'id':
				// Max 128 characters
				$value = substr( $value, 0, 128 );
				break;

			case 'title':
				// Max 300 characters, plain text only
				$value = wp_strip_all_tags( $value );
				$value = substr( $value, 0, 300 );
				break;

			case 'description':
				// Max 5000 characters, plain text only
				$value = wp_strip_all_tags( $value );
				$value = substr( $value, 0, 5000 );
				break;

			case 'link':
			case 'image_link':
				// Max 2000 characters, must be HTTPS
				$value = substr( $value, 0, 2000 );
				// Ensure HTTPS
				if ( strpos( $value, 'http://' ) === 0 ) {
					$value = str_replace( 'http://', 'https://', $value );
				}
				break;

			case 'price':
			case 'sale_price':
				// Ensure proper format: <value> <currency>
				if ( ! empty( $value ) && is_numeric( str_replace( array( ' ', get_woocommerce_currency() ), '', $value ) ) ) {
					$numeric_value = preg_replace( '/[^0-9.]/', '', $value );
					$currency      = get_woocommerce_currency();
					$value         = $numeric_value . ' ' . $currency;
				}
				break;

			case 'availability':
				// Normalize availability values
				$value = strtolower( $value );
				if ( in_array( $value, array( 'instock', 'in stock', 'available' ), true ) ) {
					$value = 'in stock';
				} elseif ( in_array( $value, array( 'outofstock', 'out of stock', 'unavailable' ), true ) ) {
					$value = 'out of stock';
				} elseif ( in_array( $value, array( 'preorder', 'pre-order' ), true ) ) {
					$value = 'preorder';
				}
				break;
		}

		return $value;
	}

	/**
	 * Add product data to feed
	 *
	 * @param WC_Product $product Product object
	 * @param array      $product_meta_keys Product meta keys
	 * @param string     $product_type Product type (simple, variation, etc.)
	 * @return void
	 * @since 1.0.0
	 */
	protected function add_to_feed( $product, $product_meta_keys, $product_type = '' ) {
		$attributes = $this->get_product_data( $product, $product_meta_keys );

		if ( ( $this->rex_feed_skip_product && empty( array_keys( $attributes, '' ) ) ) || ! $this->rex_feed_skip_product ) {
			if ( 'xml' === $this->feed_format ) {
				$item = RexShopping::createItem();
				foreach ( $attributes as $key => $value ) {
					if ( ! empty( $value ) ) {
						$item->$key( $value );
					}
				}
			} else {
				// For CSV, TSV, JSON formats - store in feed array
				$this->feed[] = $attributes;
			}
		}
	}

	/**
	 * Save feed to file
	 *
	 * @param string $format Feed format
	 * @return boolean|string
	 * @since 1.0.0
	 */
	protected function save_feed( $format ) {
		$file_name = $this->get_feed_file_name( $format );
		$file_path = $this->get_feed_directory() . $file_name;

		// Ensure directory exists
		$directory = $this->get_feed_directory();
		if ( ! file_exists( $directory ) ) {
			wp_mkdir_p( $directory );
		}

		// Save feed to file
		$result = file_put_contents( $file_path, $this->feed );

		if ( $result ) {
			// Update feed meta
			update_post_meta( $this->id, '_rex_feed_file', $file_name );
			update_post_meta( $this->id, '_rex_feed_last_generated', time() );
			update_post_meta( $this->id, '_rex_feed_total_products', count( $this->feed ) );
			return true;
		}

		return false;
	}

	/**
	 * Get feed file name
	 *
	 * @param string $format Feed format
	 * @return string File name
	 * @since 1.0.0
	 */
	protected function get_feed_file_name( $format ) {
		$feed_slug = get_post_field( 'post_name', $this->id );
		return $feed_slug . '.' . $format;
	}

	/**
	 * Get feed directory path
	 *
	 * @return string Directory path
	 * @since 1.0.0
	 */
	protected function get_feed_directory() {
		$upload_dir = wp_upload_dir();
		return trailingslashit( $upload_dir['basedir'] ) . 'rex-feed/';
	}
}
