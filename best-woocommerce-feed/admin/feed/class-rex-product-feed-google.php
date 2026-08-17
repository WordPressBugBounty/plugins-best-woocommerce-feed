<?php

/**
 * The file that generates xml feed for Google.
 *
 * A class definition that includes functions used for generating xml feed.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed_Google
 * @subpackage Rex_Product_Feed_Google/includes
 * @author     RexTheme <info@rextheme.com>
 */

use LukeSnowden\GoogleShoppingFeed\Containers\GoogleShopping;
use RexFeed\Google\Service\ShoppingContent;
use RexFeed\Google\Service\ShoppingContent\Product;
use RexFeed\Google\Service\ShoppingContent\ProductsCustomBatchRequest;
use RexFeed\Google\Service\ShoppingContent\ProductsCustomBatchRequestEntry;

class Rex_Product_Feed_Google extends Rex_Product_Feed_Abstract_Generator
{

	/**
	 * @var ShoppingContent $google_service
	 *
	 * This property holds an instance of the `ShoppingContent` class, which is used to interact with the Google Shopping API.
	 *
	 * @since 1.0.0
	 */
	protected ShoppingContent $google_service;

	/**
	 * @var ProductsCustomBatchRequest $google_batch_request
	 *
	 * This property holds an instance of the `ProductsCustomBatchRequest` class, which represents a batch request to the Google Shopping API.
	 *
	 * @since 1.0.0
	 */
	protected ProductsCustomBatchRequest $google_batch_request;

	/**
	 * @var Product $google_product
	 *
	 * This property holds an instance of the `Product` class, which represents a product in the Google Shopping API.
	 *
	 * @since 1.0.0
	 */
	protected Product $google_product;

	/**
	 * @var array $google_batch_entries
	 *
	 * This property is an array that stores multiple `ProductsCustomBatchRequestEntry` instances, representing the batch entries to be sent in a single request.
	 *
	 * @since 1.0.0
	 */
	protected array $google_batch_entries = [];

	/**
	 * When true, add_to_feed() collects products as plain arrays for Merchant API HTTP batch.
	 *
	 * @var bool
	 */
	protected bool $merchant_api_batch_mode = false;

	/**
	 * Plain-array products accumulated for Merchant API HTTP batch.
	 *
	 * @var array
	 */
	protected array $merchant_api_products = [];

	/**
	 * @var int $google_batch_id
	 *
	 * This property is an integer that keeps track of the batch ID for each entry in the batch request. It is incremented for each new entry.
	 *
	 * @since 1.0.0
	 */
	protected int $google_batch_id = 1;

	/**
	 * Create Feed for Google
	 *
	 * @return boolean
	 * @author
	 **/
	public function make_feed()
	{
		$product_sync_mode = $this->get_product_sync_mode();
		$log               = wc_get_logger();

		error_log(print_r(
			sprintf(
				'[Google Feed] make_feed start. feed_id=%d, batch=%d/%d, sync_mode=%s, is_google_content_api=%s',
				(int) $this->id,
				(int) $this->batch,
				(int) $this->tbatch,
				$product_sync_mode,
				$this->is_google_content_api ? 'yes' : 'no'
			),
			true
		));

		if ( $this->is_logging_enabled ) {
			$log->debug(
				sprintf(
					'[Google Feed] make_feed start. feed_id=%d, batch=%d/%d, sync_mode=%s, is_google_content_api=%s',
					(int) $this->id,
					(int) $this->batch,
					(int) $this->tbatch,
					$product_sync_mode,
					$this->is_google_content_api ? 'yes' : 'no'
				),
				array( 'source' => 'WPFM-google-merchant-api' )
			);
		}

		// ALWAYS generate the local product feed data.
		GoogleShopping::$container = null;
		GoogleShopping::title( $this->title );
		GoogleShopping::link( $this->link );
		GoogleShopping::description( $this->desc );
        
		$should_regenerate = true;
		// Use the helper to check if we should regenerate
		$should_regenerate = Rex_Feed_Generator_Helper::wpfm_should_regenerate_feed(
			$this->id,
			$this->batch,
			$this->bypass,
			$this->products,
			$this->feed
		);

		if ($should_regenerate) {
			// Generate feed for both simple and variable products
			$this->generate_product_feed();
			$this->feed = $this->returnFinalProduct();

			// Cache the feed using the helper
			Rex_Feed_Generator_Helper::wpfm_cache_feed(
				$this->id,
				$this->batch,
				$this->bypass,
				$this->products,
				$this->feed
			);
		}

		// Execute GMC synchronization in addition to local generation if enabled.
		$sync_error = null;
		if ( 'none' !== $product_sync_mode ) {
			if ( 'content' === $product_sync_mode ) {
				if ( $this->is_logging_enabled ) {
					$log->debug(
						sprintf( '[Google Feed] Content API product sync selected for feed_id=%d.', (int) $this->id ),
						array( 'source' => 'WPFMGoogleContentApiError' )
					);
				}
				$rex_google = new Rex_Feed_Google_Shopping_Api();
				if (!$rex_google->validate_auth()) {
					if ( $this->is_logging_enabled ) {
						$log->debug(
							sprintf( '[Google Feed] Content API auth failed for feed_id=%d.', (int) $this->id ),
							array( 'source' => 'WPFMGoogleContentApiError' )
						);
					}
					$sync_error = esc_html__('Google Shopping API authentication failed. Please check your credentials and try again.', 'rex-product-feed');
				}
			}

			if ( ! $sync_error ) {
				$sync_result = $this->sync_products();
				if ( isset( $sync_result['success'] ) && false === $sync_result['success'] ) {
					$sync_error = (string) ( $sync_result['message'] ?? esc_html__( 'Product sync failed.', 'rex-product-feed' ) );
					error_log(
						sprintf( '[Google Feed] Product sync error for feed_id=%d: %s', (int) $this->id, $sync_error )
					);
					if ( $this->is_logging_enabled ) {
						$log->error(
							sprintf( '[Google Feed] Product sync error for feed_id=%d: %s', (int) $this->id, $sync_error ),
							array( 'source' => 'WPFM-google-merchant-api' )
						);
					}
				}
			}
		}

		// ALWAYS write the XML/CSV feed file to disk.
		if ($this->batch >= $this->tbatch) {
			$this->save_feed($this->feed_format);
			return [
				'msg'       => 'finish',
				'error_msg' => $sync_error,
			];
		} else {
			$this->save_feed($this->feed_format);
			return 'true';
		}
	}

	/**
	 * Determine product sync route for the current feed.
	 *
	 * @return string One of: merchant, content, none.
	 */
	private function get_product_sync_mode(): string {
		if ( ! $this->is_google_content_api ) {
			if ( $this->is_logging_enabled ) {
				$log = wc_get_logger();
				$log->debug(
					sprintf( '[Google Feed] Product sync mode resolved to NONE for feed_id=%d (Sync Products to GMC disabled)', (int) $this->id ),
					array( 'source' => 'WPFM-google-merchant-api' )
				);
			}
			return 'none';
		}

		$data_source_id = get_post_meta( $this->id, '_rex_feed_google_data_source_id', true );
		if ( ! empty( $data_source_id ) ) {
			if ( $this->is_logging_enabled ) {
				$log = wc_get_logger();
				$log->debug(
					sprintf( '[Google Feed] Product sync mode resolved to MERCHANT API for feed_id=%d (data_source_id=%s)', (int) $this->id, (string) $data_source_id ),
					array( 'source' => 'WPFM-google-merchant-api' )
				);
			}
			return 'merchant';
		}

		$data_feed_id = get_post_meta( $this->id, '_rex_feed_google_data_feed_id', true ) ?: get_post_meta( $this->id, 'rex_feed_google_data_feed_id', true );
		if ( ! empty( $data_feed_id ) || $this->is_google_content_api ) {
			if ( $this->is_logging_enabled ) {
				$log = wc_get_logger();
				$log->debug(
					sprintf( '[Google Feed] Product sync mode resolved to CONTENT API (legacy) for feed_id=%d (data_feed_id=%s)', (int) $this->id, (string) $data_feed_id ),
					array( 'source' => 'WPFMGoogleContentApiError' )
				);
			}
			return 'content';
		}

		if ( $this->is_logging_enabled ) {
			$log = wc_get_logger();
			$log->debug(
				sprintf( '[Google Feed] Product sync mode resolved to MERCHANT API (default for new feeds) for feed_id=%d', (int) $this->id ),
				array( 'source' => 'WPFM-google-merchant-api' )
			);
		}
		return 'merchant';
	}

	/**
	 * Determine if this feed should execute product sync.
	 *
	 * @return bool
	 */
	public function should_run_product_sync(): bool {
		return 'none' !== $this->get_product_sync_mode();
	}

	/**
	 * Generate feed
	 */
	protected function generate_product_feed()
	{
		$product_meta_keys = Rex_Feed_Attributes::get_attributes();
		$total_products = get_post_meta($this->id, '_rex_feed_total_products', true);
		$total_products = $total_products ?: get_post_meta($this->id, 'rex_feed_total_products', true);
		$simple_products = [];
		$variation_products = [];
		$variable_parent = [];
		$group_products = [];
		$total_products = $total_products ?: array(
			'total' => 0,
			'simple' => 0,
			'variable' => 0,
			'variable_parent' => 0,
			'group' => 0,
		);

		if ($this->batch == 1) {
			$total_products = array(
				'total' => 0,
				'simple' => 0,
				'variable' => 0,
				'variable_parent' => 0,
				'group' => 0,
			);
		}

		foreach ($this->products as $productId) {
			$product = wc_get_product($productId);

			if (! is_object($product)) {
				continue;
			}
			if ($this->exclude_hidden_products) {
				if (!$product->is_visible()) {
					continue;
				}
			}

			if (!$this->include_zero_priced) {
				$product_price = rex_feed_get_product_price($product);
				if (0 == $product_price || '' == $product_price) {
					continue;
				}
			}
			if ($product->is_type('variable') && $product->has_child()) {
				if ($this->variable_product && $this->is_out_of_stock($product)) {
					$variable_parent[] = $productId;
					$variable_product = new WC_Product_Variable($productId);
					$this->add_to_feed($variable_product, $product_meta_keys);
				}

			if ($this->should_process_parent_variations() || $this->product_scope === 'product_cat' || $this->product_scope === 'product_tag' || $this->product_scope === 'product_brand' || $this->custom_filter_var_exclude) {
					if ($this->exclude_hidden_products) {
						$variations = $product->get_visible_children();
					} else {
						$variations = $product->get_children();
					}

					if ($variations) {
						foreach ($variations as $variation_id) {
							$variation_product = wc_get_product($variation_id);
							if ($variation_product && $this->should_include_variation($variation_product, $variation_id)) {
								$variation_products[] = $variation_id;
								$this->add_to_feed($variation_product, $product_meta_keys, 'variation');
							}
						}
					}
				}
			}

			if ($this->is_out_of_stock($product)) {
				if ($product->is_type('simple') || $product->is_type('external') || $product->is_type('composite') || $product->is_type('bundle') || $product->is_type('yith_bundle') || $product->is_type('yith-composite')) {
					if ( $this->exclude_simple_products ) {
                        continue;
                    }
					$simple_products[] = $productId;
					$this->add_to_feed($product, $product_meta_keys);
				}

				if ($this->product_scope === 'all' || $this->product_scope === 'product_filter' || $this->custom_filter_option) {
					if ($product->get_type() === 'variation') {
						if ($this->should_include_variation($product, $productId)) {
							$variation_products[] = $productId;
							$this->add_to_feed($product, $product_meta_keys, 'variation');
						}
					}
				}

				if ($product->is_type('grouped') && $this->parent_product || $product->is_type('woosb')) {
					$group_products[] = $productId;
					$this->add_to_feed($product, $product_meta_keys);
				}
			}
		}

		$total_products = array(
			'total' => (int) $total_products['total'] + (int) count($simple_products) + (int) count($variation_products) + (int) count($group_products) + (int) count($variable_parent),
			'simple' => (int) $total_products['simple'] + (int) count($simple_products),
			'variable' => (int) $total_products['variable'] + (int) count($variation_products),
			'variable_parent' => (int) $total_products['variable_parent'] + (int) count($variable_parent),
			'group' => (int) $total_products['group'] + (int) count($group_products),
		);

		update_post_meta($this->id, '_rex_feed_total_products', $total_products);
		if ($this->tbatch === $this->batch) {
			update_post_meta($this->id, '_rex_feed_total_products_for_all_feed', $total_products['total']);
		}
	}


	/**
	 * Adding items to feed
	 *
	 * @param $product
	 * @param $meta_keys
	 * @param string $product_type
	 * @since 7.0.1
	 */
	private function add_to_feed($product, $meta_keys, $product_type = '')
	{
		$attributes = $this->get_product_data($product, $meta_keys);

		// Ensure item_group_id is consistently present in non-xml formats to maintain same column count
		if ( $this->feed_format !== 'xml' && ! isset( $attributes['item_group_id'] ) && ( $this->variations || $this->default_variation || $this->highest_variation || $this->cheapest_variation || $this->first_variation || $this->last_variation ) ) {
			$attributes['item_group_id'] = ( $product_type === 'variation' ) ? $product->get_parent_id() : '';
		}

		if (($this->rex_feed_skip_product && empty(array_keys($attributes, ''))) || !$this->rex_feed_skip_product) {
			// ALWAYS create the XML item for local feed generation!
			$item = GoogleShopping::createItem();

			$check_item_group_id = 0;

			$product_details    = $this->normalize_product_detail_entries( $attributes, $product->get_id() );
			$grouped_attributes = array();

			if ( in_array( $this->feed_format, array( 'xml', 'text', 'tsv', 'csv' ), true ) ) {
				foreach ( $this->get_grouped_attribute_definitions() as $attribute_name => $sub_attributes ) {
					$grouped_attributes[ $attribute_name ] = $this->normalize_grouped_attribute_entries(
						$attributes,
						$attribute_name,
						$sub_attributes,
						$product->get_id()
					);
				}
			}

			foreach ($attributes as $key => $value) {
				// Skip product_detail and grouped sub-fields — handled separately below.
				if ( $this->is_product_detail_mapping_key( $key ) || $this->is_grouped_attribute_mapping_key( $key ) ) {
					continue;
				}

				if ('shipping' === $key) {
					if (is_array($value) && !empty($value)) {
						foreach ($value as $shipping) {
							$shipping_country = $shipping['country'] ?? '';
							$shipping_region  = $shipping['region'] ?? '';
							$shipping_service = $shipping['service'] ?? '';
							$shipping_price   = $shipping['shipping_cost'] ?? '';

							$item->$key($shipping_country, $shipping_region, $shipping_service, $shipping_price);
						}
					}
				} elseif ('checkout_eligibility' === $key) {
					if ($this->feed_format === 'xml' && $value !== '') {
						$item->native_commerce($value);
					}
				} elseif ($key === 'tax') {
					if (is_array($value) && !empty($value)) {
						foreach ($value as $tax) {
							$tax_country = isset($tax->tax_rate_country) ? $tax->tax_rate_country : '';
							$tax_region = isset($tax->tax_rate_state) ? $tax->tax_rate_state : '';
							$tax_postcode = isset($tax->postcode) && !empty($tax->postcode) ? implode(', ', $tax->postcode) : '';
							$tax_rate = isset($tax->tax_rate) ? $tax->tax_rate : '';
							$tax_ship = isset($tax->tax_rate_shipping) && $tax->tax_rate_shipping === '1' ? 'yes' : 'no';
							$item->$key($tax_country, $tax_region, $tax_postcode, $tax_rate, $tax_ship); // invoke $key as method of $item object.
						}
					}
				} else {
					if ($this->rex_feed_skip_row && $this->feed_format === 'xml') {
						if ($value != '') {
							$item->$key($value); // invoke $key as method of $item object.
						}
					} else {
						$item->$key($value); // invoke $key as method of $item object.
					}
				}

				if ($product_type === 'variation' && 'item_group_id' == $key) {
					$check_item_group_id = 1;
				}
			}

			// Output structured product_detail entries.
			foreach ( $product_details as $detail ) {
				$item->product_detail( $detail['section_name'], $detail['attribute_name'], $detail['attribute_value'] );
			}

			// Output structured grouped attribute entries.
			foreach ( $grouped_attributes as $attribute_name => $entries ) {
				foreach ( $entries as $entry ) {
					call_user_func_array( array( $item, $attribute_name ), array_values( $entry ) );
				}
			}

			if ( $product_type === 'variation' && $check_item_group_id === 0 && isset( $attributes['item_group_id'] ) ) {
				$item->item_group_id( $product->get_parent_id() );
			}

			// ALSO collect/prepare product details for GMC API sync if enabled.
			if ( $this->is_google_content_api ) {
				if ( $this->merchant_api_batch_mode ) {
					// Merchant API v1 path: collect as plain array for HTTP multipart batch.
					$this->merchant_api_products[] = $this->prepare_merchant_api_product( $attributes, $product_type );
				} else {
					$this->prepare_google_product($attributes, $product_type);
				}
			}
		}
	}

	/**
	 * Build a plain PHP array compatible with the Merchant API v1 ProductInput JSON schema.
	 *
	 * Price fields are converted to `amountMicros` (price × 1,000,000 integer) + `currencyCode`.
	 *
	 * @param array  $attributes  Mapped feed attributes (key → value).
	 * @param string $product_type
	 * @return array
	 */
	private function prepare_merchant_api_product( array $attributes, string $product_type = '' ): array {
		$currency = $this->get_feed_currency();

		// Simple 1-to-1 camelCase mappings for Merchant API productAttributes string fields.
		$attr_map = array(
			'title'                     => 'title',
			'description'               => 'description',
			'link'                      => 'link',
			'mobile_link'               => 'mobileLink',
			'link_template'             => 'linkTemplate',
			'mobile_link_template'      => 'mobileLinkTemplate',
			'pickup_link_template'      => 'pickupLinkTemplate',
			'pickup_method'             => 'pickupMethod',
			'pickup_sla'                => 'pickupSla',
			'image_link'                => 'imageLink',
			'availability'              => 'availability',
			'availability_date'         => 'availabilityDate',
			'condition'                 => 'condition',
			'brand'                     => 'brand',
			'mpn'                       => 'mpn',
			'item_group_id'             => 'itemGroupId',
			'color'                     => 'color',
			'gender'                    => 'gender',
			'age_group'                 => 'ageGroup',
			'material'                  => 'material',
			'pattern'                   => 'pattern',
			'size'                      => 'size',
			'google_product_category'   => 'googleProductCategory',
			'custom_label_0'            => 'customLabel0',
			'custom_label_1'            => 'customLabel1',
			'custom_label_2'            => 'customLabel2',
			'custom_label_3'            => 'customLabel3',
			'custom_label_4'            => 'customLabel4',
			'adwords_redirect'          => 'adsRedirect',
			'expiration_date'           => 'expirationDate',
			'sale_price_effective_date' => 'salePriceEffectiveDate',
			'shipping_label'            => 'shippingLabel',
			'energy_efficiency_class'   => 'energyEfficiencyClass',
		);

		$attrs = array();

		foreach ( $attr_map as $feed_key => $api_key ) {
			if ( ! empty( $attributes[ $feed_key ] ) && is_string( $attributes[ $feed_key ] ) && '' !== trim( $attributes[ $feed_key ] ) ) {
				$attrs[ $api_key ] = trim( $attributes[ $feed_key ] );
			}
		}

		// Boolean attribute: adult.
		if ( isset( $attributes['adult'] ) && '' !== (string) $attributes['adult'] && false !== $attributes['adult'] ) {
			$val = strtolower( trim( (string) $attributes['adult'] ) );
			if ( in_array( $val, array( 'yes', 'true', '1' ), true ) ) {
				$attrs['adult'] = true;
			} elseif ( in_array( $val, array( 'no', 'false', '0' ), true ) ) {
				$attrs['adult'] = false;
			}
		}

		// Boolean attribute: is_bundle.
		if ( isset( $attributes['is_bundle'] ) && '' !== (string) $attributes['is_bundle'] && false !== $attributes['is_bundle'] ) {
			$val = strtolower( trim( (string) $attributes['is_bundle'] ) );
			if ( in_array( $val, array( 'yes', 'true', '1' ), true ) ) {
				$attrs['isBundle'] = true;
			} elseif ( in_array( $val, array( 'no', 'false', '0' ), true ) ) {
				$attrs['isBundle'] = false;
			}
		}

		// Integer attribute: multipack.
		if ( ! empty( $attributes['multipack'] ) && is_numeric( $attributes['multipack'] ) && (int) $attributes['multipack'] > 0 ) {
			$attrs['multipack'] = (int) $attributes['multipack'];
		}

		// Array attribute: gtin (repeated string).
		if ( ! empty( $attributes['gtin'] ) && false !== $attributes['gtin'] ) {
			$gtin_arr   = is_array( $attributes['gtin'] ) ? $attributes['gtin'] : array( (string) $attributes['gtin'] );
			$gtin_clean = array_values( array_filter( array_map( function( $v ) {
				return is_string( $v ) ? trim( $v ) : ( is_numeric( $v ) ? (string) $v : '' );
			}, $gtin_arr ), function( $v ) {
				return '' !== $v;
			} ) );
			if ( ! empty( $gtin_clean ) ) {
				$attrs['gtin'] = $gtin_clean;
			}
		}

		// Array attribute: productTypes (repeated string).
		if ( ! empty( $attributes['product_type'] ) && false !== $attributes['product_type'] ) {
			$pt_arr   = is_array( $attributes['product_type'] ) ? $attributes['product_type'] : array( (string) $attributes['product_type'] );
			$pt_clean = array_values( array_filter( array_map( function( $v ) {
				return is_string( $v ) ? trim( $v ) : '';
			}, $pt_arr ), function( $v ) {
				return '' !== $v;
			} ) );
			if ( ! empty( $pt_clean ) ) {
				$attrs['productTypes'] = $pt_clean;
			}
		}

		// Array attribute: promotionIds (repeated string).
		if ( ! empty( $attributes['promotion_id'] ) && false !== $attributes['promotion_id'] ) {
			$promo_arr   = is_array( $attributes['promotion_id'] ) ? $attributes['promotion_id'] : array( (string) $attributes['promotion_id'] );
			$promo_clean = array_values( array_filter( array_map( function( $v ) {
				return is_string( $v ) ? trim( $v ) : '';
			}, $promo_arr ), function( $v ) {
				return '' !== $v;
			} ) );
			if ( ! empty( $promo_clean ) ) {
				$attrs['promotionIds'] = $promo_clean;
			}
		}

		// Boolean attribute: identifierExists.
		if ( isset( $attributes['identifier_exists'] ) && '' !== (string) $attributes['identifier_exists'] && false !== $attributes['identifier_exists'] ) {
			$val = strtolower( trim( (string) $attributes['identifier_exists'] ) );
			$attrs['identifierExists'] = !( 'no' === $val || 'false' === $val || '0' === $val );
		}

		if ( empty( $currency ) ) {
			if ( isset( $attributes['price'] ) && preg_match( '/[A-Z]{3}/', (string) $attributes['price'], $c_matches ) ) {
				$currency = $c_matches[0];
			} else {
				$currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';
			}
		}

		// Price conversion: string ("18.00 USD" / "18.00") → amountMicros.
		$raw_price     = isset( $attributes['price'] ) ? (string) $attributes['price'] : '';
		$price_numeric = (float) preg_replace( '/[^0-9.]/', '', str_replace( ',', '.', $raw_price ) );
		if ( $price_numeric > 0 ) {
			$attrs['price'] = array(
				'amountMicros' => (string) ( (int) round( $price_numeric * 1_000_000 ) ),
				'currencyCode' => $currency,
			);
		}

		// Sale price — only when > 0.
		$raw_sale_price     = isset( $attributes['sale_price'] ) ? (string) $attributes['sale_price'] : '';
		$sale_price_numeric = (float) preg_replace( '/[^0-9.]/', '', str_replace( ',', '.', $raw_sale_price ) );
		if ( $sale_price_numeric > 0 ) {
			$attrs['salePrice'] = array(
				'amountMicros' => (string) ( (int) round( $sale_price_numeric * 1_000_000 ) ),
				'currencyCode' => $currency,
			);
		}

		// Additional image links.
		$additional_images = array();
		for ( $i = 1; $i <= 10; $i++ ) {
			if ( ! empty( $attributes[ "additional_image_link_{$i}" ] ) && is_string( $attributes[ "additional_image_link_{$i}" ] ) && '' !== trim( $attributes[ "additional_image_link_{$i}" ] ) ) {
				$additional_images[] = trim( $attributes[ "additional_image_link_{$i}" ] );
			}
		}
		if ( ! empty( $additional_images ) ) {
			$attrs['additionalImageLinks'] = $additional_images;
		}

		// Product highlights.
		$highlights = array();
		for ( $i = 1; $i <= 10; $i++ ) {
			if ( ! empty( $attributes[ "product_highlight_{$i}" ] ) && is_string( $attributes[ "product_highlight_{$i}" ] ) && '' !== trim( $attributes[ "product_highlight_{$i}" ] ) ) {
				$highlights[] = trim( $attributes[ "product_highlight_{$i}" ] );
			}
		}
		if ( ! empty( $highlights ) ) {
			$attrs['productHighlights'] = $highlights;
		}

		// Shipping array.
		if ( ! empty( $attributes['shipping'] ) && is_array( $attributes['shipping'] ) ) {
			$attrs['shipping'] = array();
			foreach ( $attributes['shipping'] as $ship ) {
				$attrs['shipping'][] = array(
					'country' => $ship['country'] ?? '',
					'region'  => $ship['region'] ?? '',
					'service' => $ship['service'] ?? '',
					'price'   => array(
						'amountMicros' => (string) ( (int) round( (float) ( $ship['shipping_cost'] ?? 0 ) * 1_000_000 ) ),
						'currencyCode' => $currency,
					),
				);
			}
		}

		// item_group_id fallback for variations.
		if ( 'variation' === $product_type && empty( $attrs['itemGroupId'] ) && ! empty( $attributes['item_group_id'] ) ) {
			$attrs['itemGroupId'] = (string) $attributes['item_group_id'];
		}

		return array(
			'offerId'           => (string) ( $attributes['id'] ?? '' ),
			'feedLabel'         => $this->google_api_target_country,
			'contentLanguage'   => $this->google_api_target_language,
			'productAttributes' => $attrs,
		);
	}

	/**
	 * Check whether an attribute key belongs to product_detail grouped mappings.
	 *
	 * @param string $attribute_key Attribute mapping key.
	 * @return bool
	 */
	private function is_product_detail_mapping_key( $attribute_key ) {
		return (bool) preg_match( '/^product_detail_(section_name|attribute_name|attribute_value)_\d+$/', $attribute_key );
	}

	/**
	 * Check whether an attribute key belongs to a repeated group mapping.
	 *
	 * @param string $attribute_key Attribute mapping key.
	 * @return bool
	 */
	private function is_grouped_attribute_mapping_key( $attribute_key ) {
		foreach ( $this->get_grouped_attribute_definitions() as $attribute_name => $sub_attributes ) {
			$pattern = '/^' . preg_quote( $attribute_name, '/' ) . '_(' . implode( '|', array_map( 'preg_quote', $sub_attributes ) ) . ')_\d+$/';
			if ( preg_match( $pattern, $attribute_key ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Normalize mapped product_detail sub-fields into a sequential list.
	 *
	 * @param array $attributes Product attributes from mapped feed fields.
	 * @param int   $product_id Product ID for warning context.
	 * @return array
	 */
	private function normalize_product_detail_entries( $attributes, $product_id = 0 ) {
		$grouped_entries = array();
		$normalized      = array();
		$invalid_indexes = array();

		foreach ( $attributes as $key => $value ) {
			if ( preg_match( '/^product_detail_(section_name|attribute_name|attribute_value)_(\d+)$/', $key, $matches ) && isset( $matches[1], $matches[2] ) ) {
				$index = (int) $matches[2];
				$field = $matches[1];
				if ( is_scalar( $value ) || null === $value ) {
					$grouped_entries[ $index ][ $field ] = (string) $value;
				}
			}
		}

		if ( empty( $grouped_entries ) ) {
			return $normalized;
		}

		ksort( $grouped_entries );

		foreach ( $grouped_entries as $index => $entry ) {
			$attribute_name  = isset( $entry['attribute_name'] ) ? trim( (string) $entry['attribute_name'] ) : '';
			$attribute_value = isset( $entry['attribute_value'] ) ? trim( (string) $entry['attribute_value'] ) : '';

			if ( '' === $attribute_name || '' === $attribute_value ) {
				$invalid_indexes[] = (string) $index;
				continue;
			}

			$normalized[] = array(
				'section_name'    => isset( $entry['section_name'] ) ? (string) $entry['section_name'] : '',
				'attribute_name'  => $attribute_name,
				'attribute_value' => $attribute_value,
			);
		}

		if ( ! empty( $invalid_indexes ) && function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->warning(
				sprintf(
					'Skipping malformed product_detail entries. Feed ID: %d, Product ID: %d, Indexes: %s',
					(int) $this->id,
					(int) $product_id,
					implode( ',', $invalid_indexes )
				),
				array( 'source' => 'wpfm-google-feed' )
			);
		}

		return $normalized;
	}

	/**
	 * Define supported repeated group attributes and their required sub-attributes.
	 *
	 * @return array
	 */
	private function get_grouped_attribute_definitions() {
		return array(
			'question_and_answer' => array( 'question', 'answer' ),
			'variant_option'      => array( 'name', 'value' ),
			'related_product'     => array( 'relationship_type', 'identifier_type', 'identifier' ),
		);
	}

	/**
	 * Normalize mapped repeated group sub-fields into a sequential list.
	 *
	 * @param array  $attributes Product attributes from mapped feed fields.
	 * @param string $attribute_name Group attribute name.
	 * @param array  $sub_attributes Required sub-attribute names.
	 * @param int    $product_id Product ID for warning context.
	 * @return array
	 */
	private function normalize_grouped_attribute_entries( $attributes, $attribute_name, $sub_attributes, $product_id = 0 ) {
		$grouped_entries = array();
		$normalized      = array();
		$invalid_indexes = array();
		$pattern         = '/^' . preg_quote( $attribute_name, '/' ) . '_(' . implode( '|', array_map( 'preg_quote', $sub_attributes ) ) . ')_(\d+)$/';

		foreach ( $attributes as $key => $value ) {
			if ( preg_match( $pattern, $key, $matches ) && isset( $matches[1], $matches[2] ) ) {
				$index = (int) $matches[2];
				$field = $matches[1];
				if ( is_scalar( $value ) || null === $value ) {
					$grouped_entries[ $index ][ $field ] = (string) $value;
				}
			}
		}

		if ( empty( $grouped_entries ) ) {
			return $normalized;
		}

		ksort( $grouped_entries );

		foreach ( $grouped_entries as $index => $entry ) {
			$normalized_entry = array();
			foreach ( $sub_attributes as $sub_attribute ) {
				$value = isset( $entry[ $sub_attribute ] ) ? trim( (string) $entry[ $sub_attribute ] ) : '';
				if ( '' === $value ) {
					$invalid_indexes[] = (string) $index;
					continue 2;
				}
				$normalized_entry[ $sub_attribute ] = $value;
			}

			$normalized[] = $normalized_entry;
		}

		if ( ! empty( $invalid_indexes ) && function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->warning(
				sprintf(
					'Skipping malformed %s entries. Feed ID: %d, Product ID: %d, Indexes: %s',
					$attribute_name,
					(int) $this->id,
					(int) $product_id,
					implode( ',', $invalid_indexes )
				),
				array( 'source' => 'wpfm-google-feed' )
			);
		}

		return $normalized;
	}


	/**
	 * Return Feed
	 *
	 * @return array|bool|string
	 */
	public function returnFinalProduct()
	{
		if ($this->feed_format === 'xml') {
			return GoogleShopping::asRss();
		} elseif ($this->feed_format === 'text' || $this->feed_format === 'tsv') {
			return GoogleShopping::asTxt();
		} elseif ($this->feed_format === 'csv') {
			return GoogleShopping::asCsv();
		} elseif ($this->feed_format === 'json') {
			return GoogleShopping::asJSON();
		}
		return GoogleShopping::asRss();
	}

	public function footer_replace()
	{
		$this->feed = str_replace('</channel></rss>', '', $this->feed);
	}

	/**
	 * Prepare Google product with given attributes.
	 *
	 * This method initializes a new `Product` object and sets its attributes based on the provided array.
	 * It uses a mapping of attribute keys to methods or closures that set the corresponding values on the `Product` object.
	 * After setting all attributes, it sets the target country, content language, and channel for the product.
	 * Finally, it creates a batch entry for the product and updates the batch entries.
	 *
	 * @param array $attributes An associative array of product attributes and their values.
	 * @param string $product_type The type of the product (e.g., 'variation').
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public function prepare_google_product(array $attributes, string $product_type = '')
	{
		$google_product = new Product();
		$attribute_methods = [
			'id'                        => 'setOfferId',
			'title'                     => 'setTitle',
			'description'               => 'setDescription',
			'link'                      => 'setLink',
			'mobile_link'               => 'setMobileLink',
			'product_type'              => 'setProductTypes',
			'google_product_category'   => 'setGoogleProductCategory',
			'image_link'                => 'setImageLink',
			'additional_image_link_1'   => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_additional_image_links($google_product, $value);
			},
			'additional_image_link_2'   => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_additional_image_links($google_product, $value);
			},
			'additional_image_link_3'   => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_additional_image_links($google_product, $value);
			},
			'additional_image_link_4'   => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_additional_image_links($google_product, $value);
			},
			'additional_image_link_5'   => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_additional_image_links($google_product, $value);
			},
			'additional_image_link_6'   => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_additional_image_links($google_product, $value);
			},
			'additional_image_link_7'   => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_additional_image_links($google_product, $value);
			},
			'additional_image_link_8'   => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_additional_image_links($google_product, $value);
			},
			'additional_image_link_9'   => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_additional_image_links($google_product, $value);
			},
			'additional_image_link_10'  => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_additional_image_links($google_product, $value);
			},
			'condition'                 => 'setCondition',
			'availability'              => 'setAvailability',
			'availability_date'         => 'setAvailabilityDate',
			'price'                     => function (Product &$google_product, $value) {
				$feed_currency = $this->get_feed_currency();
				Rex_Feed_Handle_Google_Product::set_price($google_product, (float)$value, $feed_currency);
			},
			'sale_price'                => function (Product &$google_product, $value) {
				// Only process sale price if the value is not empty, numeric, and greater than 0
				// This prevents setting sale price as 0 for variable products that don't have a sale price
				if (!empty($value) && is_numeric($value) && (float)$value > 0) {
					$feed_currency = $this->get_feed_currency();
					Rex_Feed_Handle_Google_Product::set_sale_price($google_product, (float)$value, $feed_currency);
				}
			},
			'sale_price_effective_date' => 'setSalePriceEffectiveDate',
			'cost_of_goods_sold'        => function (Product &$google_product, $value) {
				$feed_currency = $this->get_feed_currency();
				Rex_Feed_Handle_Google_Product::set_cost_of_goods_sold($google_product, (float)$value, $feed_currency);
			},
			'expiration_date'           => 'setExpirationDate',
			'inventory'                 => 'setInventory',
			'override'                  => 'setOverride',
			'brand'                     => 'setBrand',
			'gtin'                      => 'setGtin',
			'mpn'                       => 'setMpn',
			'identifier_exists'         => 'setIdentifierExists',
			'item_group_id'             => 'setItemGroupId',
			'color'                     => 'setColor',
			'gender'                    => 'setGender',
			'age_group'                 => 'setAgeGroup',
			'material'                  => 'setMaterial',
			'pattern'                   => 'setPattern',
			'size'                      => 'setSize',
			'size_type'                 => 'setSizeType',
			'size_system'               => 'setSizeSystem',
			'tax'                       => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_taxes($google_product, $value);
			},
			'shipping'                  => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_shipping($google_product, $value);
			},
			'shipping_country'          => 'setShippingCountry',
			'shipping_region'           => 'setShippingRegion',
			'shipping_service'          => 'setShippingService',
			'shipping_price'            => 'setShippingPrice',
			'shipping_weight'           => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_shipping_weight($google_product, $value);
			},
			'shipping_length'           => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_shipping_length($google_product, $value);
			},
			'shipping_width'            => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_shipping_width($google_product, $value);
			},
			'shipping_height'           => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_shipping_height($google_product, $value);
			},
			'shipping_label'            => 'setShippingLabel',
			'multipack'                 => 'setMultipack',
			'is_bundle'                 => 'setIsBundle',
			'adult'                     => 'setAdult',
			'adwords_redirect'          => 'setAdwordsRedirect',
			'custom_label_0'            => 'setCustomLabel0',
			'custom_label_1'            => 'setCustomLabel1',
			'custom_label_2'            => 'setCustomLabel2',
			'custom_label_3'            => 'setCustomLabel3',
			'custom_label_4'            => 'setCustomLabel4',
			'excluded_destination'      => 'setExcludedDestinations',
			'included_destination'      => 'setIncludedDestinations',
			'unit_pricing_base_measure' => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_unit_pricing_base_measure($google_product, $value);
			},
			'unit_pricing_measure'      => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_unit_pricing_measure($google_product, $value);
			},
			'energy_efficiency_class'   => 'setEnergyEfficiencyClass',
			'loyalty_points'            => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_loyalty_points($google_product, $value);
			},
			'installment'               => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_installment($google_product, $value);
			},
			'promotion_id'              => 'setPromotionIds',
			'product_highlight_1'       => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_product_highlights($google_product, $value);
			},
			'product_highlight_2'       => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_product_highlights($google_product, $value);
			},
			'product_highlight_3'       => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_product_highlights($google_product, $value);
			},
			'product_highlight_4'       => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_product_highlights($google_product, $value);
			},
			'product_highlight_5'       => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_product_highlights($google_product, $value);
			},
			'product_highlight_6'       => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_product_highlights($google_product, $value);
			},
			'product_highlight_7'       => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_product_highlights($google_product, $value);
			},
			'product_highlight_8'       => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_product_highlights($google_product, $value);
			},
			'product_highlight_9'       => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_product_highlights($google_product, $value);
			},
			'product_highlight_10'      => function (Product &$google_product, $value) {
				Rex_Feed_Handle_Google_Product::set_product_highlights($google_product, $value);
			},
		];

		foreach ($attributes as $key => $value) {
			if (isset($attribute_methods[$key])) {
				$method = $attribute_methods[$key];
				if (is_callable($method)) {
					$method($google_product, $value, $product_type);
				} else if (method_exists($google_product, $method)) {
					$google_product->$method($value);
				}
			}
		}

		$google_product->setTargetCountry($this->google_api_target_country);
		$google_product->setContentLanguage($this->google_api_target_language);
		$google_product->setChannel('online');
		$batch_entry = $this->set_google_product($google_product);
		$this->update_google_batch_entries($batch_entry);
	}

	/**
	 * Set google product.
	 *
	 * This method creates a new batch entry for the Google Shopping API and sets the product details in the entry.
	 *
	 * @param Product $google_product The Google product object to set in the batch entry.
	 *
	 * @return ProductsCustomBatchRequestEntry The batch entry with the Google product details set.
	 *
	 * @since 1.0.0
	 */
	public function set_google_product(Product $google_product): ProductsCustomBatchRequestEntry
	{
		$batch_entry = new ProductsCustomBatchRequestEntry();
		$batch_entry->setBatchId($this->google_batch_id++);
		$batch_entry->setMethod('insert');
		$batch_entry->setProduct($google_product);
		$batch_entry->setMerchantId(get_option('rex_google_merchant_id', ''));
		return $batch_entry;
	}

	/**
	 * Update google batch entries.
	 *
	 * This method adds a new batch entry to the array of Google batch entries.
	 *
	 * @param ProductsCustomBatchRequestEntry $batch_entry The batch entry to add to the array.
	 *
	 * @since 1.0.0
	 */
	public function update_google_batch_entries(ProductsCustomBatchRequestEntry $batch_entry)
	{
		$this->google_batch_entries[] = $batch_entry;
	}

	/**
	 * Sync products.
	 *
	 * This method synchronizes the products in the WooCommerce store with the Google Shopping API.
	 * It prepares the products, creates a batch request, and sends the request to the API.
	 *
	 * @since 1.0.0
	 */
	public function get_feed_currency() {
		if ( defined( 'WOOCS_VERSION' ) && !empty( $this->woocs_currency ) ) {
			return $this->woocs_currency;
		}
		if ( wpfm_is_curcy_active() && !empty( $this->curcy_currency ) ) {
			return $this->curcy_currency;
		}
		if ( wpfm_is_aelia_active() && !empty( $this->aelia_currency ) ) {
			return $this->aelia_currency;
		}
		if ( wpfm_is_wmc_active() && !empty( $this->wmc_currency ) ) {
			return $this->wmc_currency;
		}
		if ( wpfm_is_wcml_active() && !empty( $this->wcml_currency ) ) {
			return $this->wcml_currency;
		}
		return get_woocommerce_currency();
	}

	/**
	 * Sync products.
	 *
	 * This method synchronizes the products in the WooCommerce store with the Google Shopping API.
	 * It prepares the products, creates a batch request, and sends the request to the API.
	 *
	 * @since 1.0.0
	 */
	public function sync_products(): array
	{
		$log            = wc_get_logger();
		$data_source_id = get_post_meta( $this->id, '_rex_feed_google_data_source_id', true );
		$data_feed_id   = get_post_meta( $this->id, '_rex_feed_google_data_feed_id', true ) ?: get_post_meta( $this->id, 'rex_feed_google_data_feed_id', true );

		if ( $this->is_logging_enabled ) {
			$log->debug(
				sprintf(
					'[Google Feed] sync_products start. feed_id=%d, has_data_source=%s, has_data_feed=%s, total_products_in_batch=%d',
					(int) $this->id,
					empty( $data_source_id ) ? 'no' : 'yes',
					empty( $data_feed_id ) ? 'no' : 'yes',
					is_array( $this->products ) ? count( $this->products ) : 0
				),
				array( 'source' => 'WPFM-google-merchant-api' )
			);
		}

		// Merchant API v1 path: if DataSource ID exists OR if it's a new feed (no data_feed_id), execute via Merchant API.
		if ( $data_source_id || ! $data_feed_id ) {
			$merchant_client = Rex_Feed_Merchant_API_Client::from_stored_credentials();
			if ( ! $merchant_client ) {
				if ( wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development' ) {
					if ( ! $data_source_id ) {
						$data_source_id = 'accounts/123456789/dataSources/mock_' . $this->id;
						update_post_meta( $this->id, '_rex_feed_google_data_source_id', $data_source_id );
					}
					if ( $this->is_logging_enabled ) {
						$log->debug(
							sprintf( '[Local Mock] Bypassed background sync execution for feed_id=%d, DataSource ID: %s', (int) $this->id, $data_source_id ),
							array( 'source' => 'WPFM-google-merchant-api' )
						);
					}
					return array(
						'success' => true,
						'message' => esc_html__( 'Local sync mocked successfully.', 'rex-product-feed' ),
					);
				}
				if ( $this->is_logging_enabled ) {
					$log->debug(
						sprintf( '[Google Feed] Merchant API credentials missing for feed_id=%d.', (int) $this->id ),
						array( 'source' => 'WPFM-google-merchant-api' )
					);
				}
				return array(
					'success' => false,
					'message' => esc_html__( 'GMC credentials are missing. Please configure Merchant Settings first.', 'rex-product-feed' ),
				);
			}

			// If DataSource ID does not exist yet on a new feed, auto-create it via Merchant API v1.
			if ( ! $data_source_id ) {
				try {
					$merchant_id = get_option( 'rex_google_merchant_id', '' );
					$feed_title  = get_the_title( $this->id );
					$country     = get_post_meta( $this->id, '_rex_feed_google_target_country', true ) ?: 'US';
					$language    = get_post_meta( $this->id, '_rex_feed_google_target_language', true ) ?: 'en';

					$data_source_obj = ( new \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\DataSource() )
						->setDisplayName( $feed_title )
						->setPrimaryProductDataSource(
							( new \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\PrimaryProductDataSource() )
								->setCountries( array( $country ) )
								->setContentLanguage( $language )
								->setFeedLabel( $country )
						);

					$ds_client      = $merchant_client->get_datasources_client();
					$create_request = ( new \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\CreateDataSourceRequest() )
						->setParent( "accounts/{$merchant_id}" )
						->setDataSource( $data_source_obj );

					$response       = $ds_client->createDataSource( $create_request );
					$data_source_id = $response->getName();
					update_post_meta( $this->id, '_rex_feed_google_data_source_id', $data_source_id );

					// Allow Google backend to finish indexing the newly created DataSource resource.
					sleep( 2 );

					if ( $this->is_logging_enabled ) {
						$log->debug(
							sprintf( '[Google Feed] Auto-created Merchant API DataSource ID=%s for feed_id=%d', $data_source_id, (int) $this->id ),
							array( 'source' => 'WPFM-google-merchant-api' )
						);
					}
				} catch ( \RexFeed\Vendor\Google\ApiCore\ApiException $e ) {
					$normalized = Rex_Feed_Merchant_API_Client::normalize_api_error( $e );
					if ( 'project_not_registered' === ( $normalized['error_type'] ?? '' ) ) {
						$developer_email = $merchant_client->get_google_email() ?: ( wp_get_current_user()->user_email ?: get_bloginfo( 'admin_email' ) );
						$reg_result      = $merchant_client->register_gcp( $merchant_id, $developer_email );

						if ( isset( $reg_result['success'] ) && true === $reg_result['success'] ) {
							$human_msg = __( 'Google Cloud project registration has been submitted to Google. Google requires up to 5 minutes for permissions to activate. Please wait 5 minutes and try again.', 'rex-product-feed' );
							$log->info(
								sprintf( '[Google Feed] GCP project auto-registered for feed_id=%d', (int) $this->id ),
								array( 'source' => 'WPFM-google-merchant-api' )
							);
							return array(
								'success' => false,
								'message' => $human_msg,
							);
						}

						// If automatic registration returned an error or requirement:
						$reg_msg = ! empty( $reg_result['message'] ) ? $reg_result['message'] : ( $normalized['message'] ?? $e->getMessage() );
						$log->error(
							sprintf( '[Google Feed] GCP project registration failed for feed_id=%d: %s', (int) $this->id, $reg_msg ),
							array( 'source' => 'WPFM-google-merchant-api' )
						);

						$help_url = ! empty( $normalized['action_url'] ) ? $normalized['action_url'] : 'https://developers.google.com/merchant/api/guides/quickstart/direct-api-calls#step_1_register_as_a_developer';
						return array(
							'success' => false,
							'message' => sprintf(
								__( 'GCP Project Registration Error: %s. Please ensure your Google Merchant Center account has developer access enabled (%s).', 'rex-product-feed' ),
								$reg_msg,
								$help_url
							),
						);
					}

					$user_msg = ! empty( $normalized['message'] ) ? $normalized['message'] : $e->getMessage();
					$log->error(
						sprintf( '[Google Feed] Auto-creation of DataSource failed for feed_id=%d: %s', (int) $this->id, $user_msg ),
						array( 'source' => 'WPFM-google-merchant-api' )
					);
					return array(
						'success' => false,
						'message' => sprintf( __( 'Google Merchant API error: %s', 'rex-product-feed' ), $user_msg ),
					);
				} catch ( \Throwable $e ) {
					$log->error(
						sprintf( '[Google Feed] Auto-creation of DataSource failed for feed_id=%d: %s', (int) $this->id, $e->getMessage() ),
						array( 'source' => 'WPFM-google-merchant-api' )
					);
					return array(
						'success' => false,
						'message' => sprintf( __( 'Failed to auto-create Merchant API DataSource: %s', 'rex-product-feed' ), $e->getMessage() ),
					);
				}
			}

			$this->merchant_api_batch_mode = true;
			$this->merchant_api_products   = array();
			$this->generate_product_feed();

			if ( $this->is_logging_enabled ) {
				$log->debug(
					sprintf(
						'[Google Feed] Merchant payload prepared. feed_id=%d, product_inputs=%d',
						(int) $this->id,
						count( $this->merchant_api_products )
					),
					array( 'source' => 'WPFM-google-merchant-api' )
				);
			}

			$chunks = array_chunk( $this->merchant_api_products, 100 );
			if ( $this->is_logging_enabled ) {
				$log->debug(
					sprintf( '[Google Feed] Merchant chunking. feed_id=%d, chunks=%d', (int) $this->id, count( $chunks ) ),
					array( 'source' => 'WPFM-google-merchant-api' )
				);
			}
			foreach ( $chunks as $chunk ) {
				$batch_result = $this->send_merchant_api_batch( $chunk, $data_source_id, $merchant_client );
				if ( isset( $batch_result['success'] ) && false === $batch_result['success'] ) {
					if ( $this->is_logging_enabled ) {
						$log->debug(
							sprintf( '[Google Feed] Merchant chunk failed. feed_id=%d, message=%s', (int) $this->id, (string) ( $batch_result['message'] ?? '' ) ),
							array( 'source' => 'WPFM-google-merchant-api' )
						);
					}
					return $batch_result;
				}
			}

			if ( $this->is_logging_enabled ) {
				$log->debug(
					sprintf( '[Google Feed] Merchant sync complete. feed_id=%d', (int) $this->id ),
					array( 'source' => 'WPFM-google-merchant-api' )
				);
			}
			return array( 'success' => true );
		}

		// Legacy Content API path — used only while DataSource ID absent and old feed ID present.
		$rex_google                 = new Rex_Feed_Google_Shopping_Api();
		$this->google_service       = new ShoppingContent( $rex_google->get_client() );
		$this->google_batch_request = new ProductsCustomBatchRequest();
		$this->generate_product_feed();
		$this->google_batch_request->setEntries( $this->google_batch_entries );

		if ( $this->is_logging_enabled ) {
			$log->debug(
				sprintf( '[Google Feed] Content API batch prepared. feed_id=%d, entries=%d', (int) $this->id, count( $this->google_batch_entries ) ),
				array( 'source' => 'WPFMGoogleContentApiError' )
			);
		}

		try {
			$response = $this->google_service->products->custombatch( $this->google_batch_request );
			$entries  = $response->getEntries();

			if ( $this->is_logging_enabled ) {
				$log->debug(
					sprintf( '[Google Feed] Content API batch response received. feed_id=%d, entries=%d', (int) $this->id, is_array( $entries ) ? count( $entries ) : 0 ),
					array( 'source' => 'WPFMGoogleContentApiError' )
				);
			}

			if ( $this->is_logging_enabled ) {
				foreach ( $entries as $entry ) {
					if ( ! empty( $entry['errors'] ) ) {
						$log->error( print_r( $entry['errors'], 1 ), array( 'source' => 'WPFMGoogleContentApiError' ) );
					}
				}
			}
		} catch ( Exception $e ) {
			$log->error( print_r( $e->getMessage(), 1 ), array( 'source' => 'WPFMGoogleContentApiError' ) );
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}

		return array( 'success' => true );
	}

	/**
	 * Send a chunk of products to Merchant API v1 via HTTP multipart/mixed batch.
	 *
	 * @param array  $chunk        Array of product arrays (max 100).
	 * @param string $data_source_id  Merchant API DataSource resource name.
	 */
	private function send_merchant_api_batch( array $chunk, string $data_source_id, Rex_Feed_Merchant_API_Client $merchant_client, bool $retry_attempt = false ): array {
		$log         = wc_get_logger();
		$merchant_id = get_option( 'rex_google_merchant_id', '' );
		$boundary    = 'batch_wpfm_' . wp_generate_uuid4();
		$body        = '';

		if ( $this->is_logging_enabled ) {
			$log->debug(
				sprintf( '[Merchant API] Preparing batch request. feed_id=%d, chunk_size=%d, data_source_id=%s', (int) $this->id, count( $chunk ), (string) $data_source_id ),
				array( 'source' => 'WPFM-google-merchant-api' )
			);
		}

		// Derive the DataSource ID number from the resource name (accounts/{id}/dataSources/{ds_id}).
		$ds_param = $data_source_id;

		foreach ( $chunk as $index => $product ) {
			$part_id = $index + 1;
			$json    = wp_json_encode( $product );

			$body .= "--{$boundary}\r\n";
			$body .= "Content-Type: application/http\r\n";
			$body .= "Content-ID: <product~{$part_id}>\r\n\r\n";
			$body .= "POST /products/v1/accounts/{$merchant_id}/productInputs:insert?dataSource=" . rawurlencode( $ds_param ) . "\r\n";
			$body .= "Content-Type: application/json\r\n\r\n";
			$body .= $json . "\r\n";
		}
		$body .= "--{$boundary}--\r\n";

		$access_token = $merchant_client->get_access_token() ?: '';

		if ( ! $access_token ) {
			$log->error( '[Merchant API] send_merchant_api_batch: no access token', array( 'source' => 'WPFM-google-merchant-api' ) );
			return array(
				'success' => false,
				'message' => esc_html__( 'Unable to obtain Merchant API access token. Please re-authorize your Google Merchant connection.', 'rex-product-feed' ),
			);
		}

		$response = wp_remote_post(
			'https://merchantapi.googleapis.com/batch/products/v1',
			array(
				'timeout' => 60,
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => "multipart/mixed; boundary={$boundary}",
				),
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			$log->error(
				'[Merchant API] batch request failed: ' . $response->get_error_message(),
				array( 'source' => 'WPFM-google-merchant-api' )
			);
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$http_code     = (int) wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$content_type  = wp_remote_retrieve_header( $response, 'content-type' );

		error_log(
			sprintf( '[Merchant API] Batch HTTP response start. feed_id=%d, http_code=%d, content_type=%s', (int) $this->id, $http_code, (string) $content_type )
		);

		preg_match( '/boundary=([^\s;]+)/', $content_type, $matches );
		$response_boundary = $matches[1] ?? '';

		if ( $response_boundary ) {
			$results              = self::parse_batch_response( $response_body, $response_boundary );
			$failed               = 0;
			$first_error          = '';
			$datasource_not_found = false;

			foreach ( $results as $content_id => $result ) {
				if ( ! empty( $result['error'] ) ) {
					$failed++;
					if ( empty( $first_error ) ) {
						$first_error = $result['error'];
					}
					if ( false !== strpos( $result['error'], 'Data source with id' ) && false !== strpos( $result['error'], 'was not found' ) ) {
						$datasource_not_found = true;
					}
					if ( $this->is_logging_enabled ) {
						$log->error(
							sprintf( '[Merchant API] product %s failed: %s', $content_id, $result['error'] ),
							array( 'source' => 'WPFM-google-merchant-api' )
						);
					}
				}
			}

			// If Google returns "Data source was not found" due to eventual consistency, wait and retry once.
			if ( $datasource_not_found && ! $retry_attempt ) {
				if ( $this->is_logging_enabled ) {
					$log->debug(
						sprintf( '[Merchant API] DataSource not yet propagated on Google backend for feed_id=%d. Waiting 3 seconds to retry...', (int) $this->id ),
						array( 'source' => 'WPFM-google-merchant-api' )
					);
				}
				sleep( 3 );
				return $this->send_merchant_api_batch( $chunk, $data_source_id, $merchant_client, true );
			}

			error_log(
				sprintf(
					'[Merchant API] Batch response parsed. feed_id=%d, http_code=%d, total_parts=%d, failed=%d, raw_body=%s',
					(int) $this->id,
					$http_code,
					count( $results ),
					$failed,
					$response_body
				)
			);
			if ( $this->is_logging_enabled ) {
				$log->debug(
					sprintf( '[Merchant API] Batch parsed. feed_id=%d, parts=%d, failed=%d', (int) $this->id, count( $results ), $failed ),
					array( 'source' => 'WPFM-google-merchant-api' )
				);
			}

			if ( $failed > 0 ) {
				return array(
					'success' => false,
					'message' => sprintf(
						/* translators: 1: failed items count, 2: total items count, 3: first error message */
						esc_html__( 'Failed to sync products to Google Merchant Center (%1$d of %2$d items failed). Error: %3$s', 'rex-product-feed' ),
						$failed,
						count( $results ),
						$first_error
					),
				);
			}
		} else {
			error_log(
				sprintf(
					'[Merchant API] Batch response (raw). feed_id=%d, http_code=%d, body=%s',
					(int) $this->id,
					$http_code,
					$response_body
				)
			);
			if ( $this->is_logging_enabled ) {
				$log->debug(
					sprintf( '[Merchant API] Batch response has no multipart boundary. feed_id=%d, content_type=%s', (int) $this->id, (string) $content_type ),
					array( 'source' => 'WPFM-google-merchant-api' )
				);
			}
			if ( $http_code >= 400 ) {
				return array(
					'success' => false,
					'message' => sprintf( esc_html__( 'Google Merchant API HTTP error %1$d: %2$s', 'rex-product-feed' ), $http_code, wp_strip_all_tags( $response_body ) ),
				);
			}
		}

		return array( 'success' => true );
	}

	/**
	 * Parse an HTTP multipart/mixed batch response from Merchant API.
	 *
	 * @param string $body      Raw response body.
	 * @param string $boundary  MIME boundary string from Content-Type header.
	 * @return array  Array keyed by Content-ID, each with 'status' (int) and optionally 'error' (string).
	 */
	public static function parse_batch_response( string $body, string $boundary ): array {
		$results = array();
		$parts   = explode( '--' . $boundary, $body );

		foreach ( $parts as $part ) {
			$part = trim( $part );
			if ( '' === $part || '--' === $part ) {
				continue;
			}

			// Extract Content-ID from part headers.
			if ( preg_match( '/Content-ID:\s*<([^>]+)>/i', $part, $id_match ) ) {
				$content_id = $id_match[1];
			} else {
				continue;
			}

			// Extract inner HTTP status line (e.g. "HTTP/1.1 200 OK").
			if ( preg_match( '/HTTP\/[\d.]+ (\d+)/', $part, $status_match ) ) {
				$status = (int) $status_match[1];
			} else {
				$status = 0;
			}

			$result = array( 'status' => $status );

			if ( $status >= 400 ) {
				// Extract error message from JSON body if present.
				$json_start = strpos( $part, '{' );
				if ( false !== $json_start ) {
					$json = json_decode( substr( $part, $json_start ), true );
					$result['error'] = $json['error']['message'] ?? 'HTTP ' . $status;
				} else {
					$result['error'] = 'HTTP ' . $status;
				}
			}

			$results[ $content_id ] = $result;
		}

		return $results;
	}
}
