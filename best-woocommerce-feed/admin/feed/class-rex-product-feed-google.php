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

class Rex_Product_Feed_Google extends Rex_Product_Feed_Abstract_Generator {

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
	public function make_feed() {
		if ( !$this->is_google_content_api ) {
			//putting data in xml file
			GoogleShopping::$container = null;
			GoogleShopping::title( $this->title );
			GoogleShopping::link( $this->link );
			GoogleShopping::description( $this->desc );

			$this->generate_product_feed();

			$this->feed = $this->returnFinalProduct();
		}
		else {
			$rex_google = new Rex_Feed_Google_Shopping_Api();
			if ( !$rex_google->validate_auth() ) {
				return [ 'msg' => 'finish', 'error_msg' => esc_html__( 'Google Shopping API authentication failed. Please check your credentials and try again.', 'rex-product-feed' ) ];
			}
			$this->sync_products();
		}

		if ( $this->batch >= $this->tbatch ) {
			if ( !$this->is_google_content_api ) {
				$this->save_feed( $this->feed_format );
			}
			return [ 'msg' => 'finish', ];
		} else {
			return !$this->is_google_content_api ? $this->save_feed( $this->feed_format ) : 'true';
		}
	}

    /**
     * Generate feed
     */
    protected function generate_product_feed(){
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

        if($this->batch == 1) {
            $total_products = array(
                'total' => 0,
                'simple' => 0,
                'variable' => 0,
                'variable_parent' => 0,
                'group' => 0,
            );
        }

        foreach( $this->products as $productId ) {
            $product = wc_get_product( $productId );

            if ( ! is_object( $product ) ) {
                continue;
            }
            if ( $this->exclude_hidden_products ) {
                if ( !$product->is_visible() ) {
                    continue;
                }
            }

            if( !$this->include_zero_priced ) {
                $product_price = rex_feed_get_product_price($product);
                if( 0 == $product_price || '' == $product_price ) {
                    continue;
                }
            }
            if ( $product->is_type( 'variable' ) && $product->has_child() ) {
                if($this->variable_product && $this->is_out_of_stock( $product ) ) {
                    $variable_parent[] = $productId;
                    $variable_product = new WC_Product_Variable($productId);
                    $this->add_to_feed( $variable_product, $product_meta_keys );
                }

                if( $this->product_scope === 'product_cat' || $this->product_scope === 'product_tag' || $this->custom_filter_var_exclude ) {
                    if ( $this->exclude_hidden_products ) {
                        $variations = $product->get_visible_children();
                    }
                    else {
                        $variations = $product->get_children();
                    }

                    if( $variations ) {
                        foreach ($variations as $variation) {
                            if($this->variations) {
                                $variation_product = wc_get_product( $variation );
                                if ( $this->is_out_of_stock( $variation_product ) ) {
                                    $variation_products[] = $variation;
                                    $this->add_to_feed( $variation_product, $product_meta_keys, 'variation' );
                                }
                            }
                        }
                    }
                }
            }

            if ( $this->is_out_of_stock( $product ) ) {
                if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) || $product->is_type( 'composite' ) || $product->is_type( 'bundle' ) ) {
                    $simple_products[] = $productId;
                    $this->add_to_feed( $product, $product_meta_keys );
                }

                if ( $this->product_scope === 'all' || $this->product_scope === 'product_filter' || $this->custom_filter_option ) {
                    if ( $product->get_type() === 'variation' ) {
                        $variation_products[] = $productId;
                        $this->add_to_feed( $product, $product_meta_keys, 'variation' );
                    }
                }

                if ( $product->is_type( 'grouped' ) && $this->parent_product || $product->is_type( 'woosb' ) ) {
                    $group_products[] = $productId;
                    $this->add_to_feed( $product, $product_meta_keys );
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

        update_post_meta( $this->id, '_rex_feed_total_products', $total_products );
	    if ( $this->tbatch === $this->batch ) {
		    update_post_meta( $this->id, '_rex_feed_total_products_for_all_feed', $total_products[ 'total' ] );
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
    private function add_to_feed( $product, $meta_keys, $product_type = '' ) {
        $attributes = $this->get_product_data( $product, $meta_keys );

        if( ( $this->rex_feed_skip_product && empty( array_keys( $attributes, '') ) ) || !$this->rex_feed_skip_product ) {
	        if ( !$this->is_google_content_api ) {
		        $item = GoogleShopping::createItem();

		        if ( $product_type === 'variation' ) {
			        $check_item_group_id = 0;
		        }

		        foreach ( $attributes as $key => $value ) {
			        if( 'shipping' === $key ) {
				        if ( is_array( $value ) && !empty( $value ) ) {
					        foreach ( $value as $shipping ) {
						        $shipping_country = $shipping[ 'country' ] ?? '';
						        $shipping_region  = $shipping[ 'region' ] ?? '';
						        $shipping_service = $shipping[ 'service' ] ?? '';
						        $shipping_price   = $shipping[ 'shipping_cost' ] ?? '';

						        $item->$key( $shipping_country, $shipping_region, $shipping_service, $shipping_price );
					        }
				        }
			        }
			        elseif ( $key === 'tax' ) {
				        if ( is_array( $value ) && !empty( $value ) ) {
					        foreach ( $value as $tax ) {
						        $tax_country = isset( $tax->tax_rate_country ) ? $tax->tax_rate_country : '';
						        $tax_region = isset( $tax->tax_rate_state ) ? $tax->tax_rate_state : '';
						        $tax_postcode = isset( $tax->postcode ) && !empty( $tax->postcode ) ? implode( ', ', $tax->postcode ) : '';
						        $tax_rate = isset( $tax->tax_rate ) ? $tax->tax_rate : '';
						        $tax_ship = isset( $tax->tax_rate_shipping ) && $tax->tax_rate_shipping === '1' ? 'yes' : 'no';
						        $item->$key( $tax_country,$tax_region, $tax_postcode, $tax_rate, $tax_ship ); // invoke $key as method of $item object.
					        }
				        }
			        }
			        else {
				        if ( $this->rex_feed_skip_row && $this->feed_format === 'xml' ) {
					        if ( $value != '' ) {
						        $item->$key($value); // invoke $key as method of $item object.
					        }
				        }
				        else {
					        $item->$key($value); // invoke $key as method of $item object.
				        }
			        }

			        if( $product_type === 'variation' && 'item_group_id' == $key ) {
				        $check_item_group_id = 1;
			        }
		        }

		        if( $product_type === 'variation' && $check_item_group_id === 0){
			        $item->item_group_id($product->get_parent_id());
		        }
	        }
			else {
				$this->prepare_google_product( $attributes, $product_type );
			}
        }
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
        }
        return GoogleShopping::asRss();
    }

    public function footer_replace() {
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
	public function prepare_google_product( array $attributes, string $product_type = '' ) {
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
			'additional_image_link_1'   => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_additional_image_links( $google_product, $value ); },
			'additional_image_link_2'   => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_additional_image_links( $google_product, $value ); },
			'additional_image_link_3'   => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_additional_image_links( $google_product, $value ); },
			'additional_image_link_4'   => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_additional_image_links( $google_product, $value ); },
			'additional_image_link_5'   => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_additional_image_links( $google_product, $value ); },
			'additional_image_link_6'   => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_additional_image_links( $google_product, $value ); },
			'additional_image_link_7'   => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_additional_image_links( $google_product, $value ); },
			'additional_image_link_8'   => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_additional_image_links( $google_product, $value ); },
			'additional_image_link_9'   => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_additional_image_links( $google_product, $value ); },
			'additional_image_link_10'  => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_additional_image_links( $google_product, $value ); },
			'condition'                 => 'setCondition',
			'availability'              => 'setAvailability',
			'availability_date'         => 'setAvailabilityDate',
			'price'                     => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_price( $google_product, $value ); },
			'sale_price'                => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_sale_price( $google_product, $value ); },
			'sale_price_effective_date' => 'setSalePriceEffectiveDate',
			'cost_of_goods_sold'        => 'setCostOfGoodsSold',
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
			'tax'                       => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_taxes( $google_product, $value ); },
			'shipping'                  => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_shipping( $google_product, $value ); },
			'shipping_country'          => 'setShippingCountry',
			'shipping_region'           => 'setShippingRegion',
			'shipping_service'          => 'setShippingService',
			'shipping_price'            => 'setShippingPrice',
			'shipping_weight'           => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_shipping_weight( $google_product, $value ); },
			'shipping_length'           => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_shipping_length( $google_product, $value ); },
			'shipping_width'            => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_shipping_width( $google_product, $value ); },
			'shipping_height'           => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_shipping_height( $google_product, $value ); },
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
			'unit_pricing_base_measure' => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_unit_pricing_base_measure( $google_product, $value ); },
			'unit_pricing_measure'      => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_unit_pricing_measure( $google_product, $value ); },
			'energy_efficiency_class'   => 'setEnergyEfficiencyClass',
			'loyalty_points'            => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_loyalty_points( $google_product, $value ); },
			'installment'               => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_installment( $google_product, $value ); },
			'promotion_id'              => 'setPromotionIds',
			'product_highlight_1'       => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_product_highlights( $google_product, $value ); },
			'product_highlight_2'       => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_product_highlights( $google_product, $value ); },
			'product_highlight_3'       => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_product_highlights( $google_product, $value ); },
			'product_highlight_4'       => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_product_highlights( $google_product, $value ); },
			'product_highlight_5'       => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_product_highlights( $google_product, $value ); },
			'product_highlight_6'       => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_product_highlights( $google_product, $value ); },
			'product_highlight_7'       => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_product_highlights( $google_product, $value ); },
			'product_highlight_8'       => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_product_highlights( $google_product, $value ); },
			'product_highlight_9'       => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_product_highlights( $google_product, $value ); },
			'product_highlight_10'      => function ( Product &$google_product, $value ) { Rex_Feed_Handle_Google_Product::set_product_highlights( $google_product, $value ); },
		];

		foreach ( $attributes as $key => $value ) {
			if ( isset( $attribute_methods[ $key ] ) ) {
				$method = $attribute_methods[ $key ];
				if ( is_callable( $method ) ) {
					$method( $google_product, $value, $product_type );
				} else {
					$google_product->$method( $value );
				}
			}
		}

		$google_product->setTargetCountry( 'US' );
		$google_product->setContentLanguage( 'en' );
		$google_product->setChannel( 'online' );
		$batch_entry = $this->set_google_product( $google_product );
		$this->update_google_batch_entries( $batch_entry );
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
	public function set_google_product( Product $google_product ): ProductsCustomBatchRequestEntry {
		$batch_entry = new ProductsCustomBatchRequestEntry();
		$batch_entry->setBatchId( $this->google_batch_id ++ );
		$batch_entry->setMethod( 'insert' );
		$batch_entry->setProduct( $google_product );
		$batch_entry->setMerchantId( get_option( 'rex_google_merchant_id', '' ) );
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
	public function update_google_batch_entries( ProductsCustomBatchRequestEntry $batch_entry ) {
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
	public function sync_products() {
		$log        = wc_get_logger();
		$rex_google = new Rex_Feed_Google_Shopping_Api();
		$this->google_service       = new ShoppingContent( $rex_google->get_client() );
		$this->google_batch_request = new ProductsCustomBatchRequest();
		$this->generate_product_feed();
		$this->google_batch_request->setEntries( $this->google_batch_entries );

		try {
			$response = $this->google_service->products->custombatch( $this->google_batch_request );
			$entries  = $response->getEntries();

			if ( $this->is_logging_enabled ) {
				foreach ( $entries as $entry ) {
					if ( !empty( $entry['errors'] ) ) {
						$log->error( print_r( $entry[ 'errors' ], 1 ), [ 'source' => 'WPFMGoogleContentApiError' ] );
					}
				}
			}
		} catch ( Exception $e ) {
			$log->error( print_r( $e->getMessage(), 1 ), [ 'source' => 'WPFMGoogleContentApiError' ] );
		}
	}
}
