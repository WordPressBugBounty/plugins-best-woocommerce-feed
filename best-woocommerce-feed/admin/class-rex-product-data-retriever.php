<?php
/**
 * Class Rex_Product_Data_Retriever
 *
 * @package    Rex_Product_Data_Retriever
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 */

use Wdr\App\Controllers\ManageDiscount;
use Wdr\App\Models\DBTable;

/**
 * Class for retrieving product data based on user selected feed configuration.
 *
 * Get the product data based on feed config selected by user.
 *
 * @package    Rex_Product_Data_Retriever
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Product_Data_Retriever {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	protected $feed_rules;

	/**
	 * Analytics parameter switcher.
	 *
	 * @var bool $analytics_switcher The UTM switcher.
	 */
	protected $analytics_switcher;

	/**
	 * Analytics parameters
	 *
	 * @var array $analytics_params The UTM params.
	 */
	protected $analytics_params;

	/**
	 * Contains all available meta keys for products.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array $product_meta_keys Product meta keys.
	 */
	protected $product_meta_keys;

	/**
	 * The data of product retrived by feed_rules.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array $data The current version of this plugin.
	 */
	protected $data;

	/**
	 * Metabox instance of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object $metabox The current metabox of this plugin.
	 */
	protected $product;

	/**
	 * Additional images of current product.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object $metabox The current metabox of this plugin.
	 */
	protected $additional_images = array();


	/**
	 * Append variation
	 *
	 * @since    3.2
	 * @access   private
	 * @var      object $append_variation
	 */
	protected $append_variation;

	/**
	 * Aelia currency
	 *
	 * @var string
	 */
    public $aelia_currency;

	/**
	 * Currency WC Multi-Currency
	 *
	 * @var string
	 */
	protected $wmc_currency;


	/**
	 * Check if debug is enabled
	 *
	 * @var Rex_Product_Data_Retriever $enable_log
	 */
	protected $is_logging_enabled;


	/**
	 * Product feed object
	 *
	 * @var Rex_Product_Feed_Abstract_Generator
	 */
	protected $feed;

	/**
	 * Language by WPML
	 *
	 * @var bool
	 */
	protected $wcml;

	/**
	 * Currency  by WPML
	 *
	 * @var mixed|string
	 */
	protected $wcml_currency;

	/**
	 * Feed Format
	 *
	 * @var string
	 */
	protected $feed_format;

	/**
	 * Variable for feed country
	 *
	 * @since 7.2.9
	 * @var string
	 */
	protected $feed_country;

	/**
	 * Variable for feed zip code
	 *
	 * @since 7.2.18
	 *
	 * @var mixed|string
	 */
	protected $feed_zip_codes;

    /**
     * @var bool
     */
    public $feed_rules_option;

    /**
     * @var array Feed configurations.
     */
    public $feed_config;

    /**
     * Currency by WOOCS
     *
     * @since 7.4.15
     * @var string
     */
    public $woocs_currency = '';

	/**
	 * Initialize the class and set its properties
	 * Rex_Product_Data_Retriever constructor
	 *
	 * @param WC_Product                          $product WooCommerce Product Object.
	 * @param Rex_Product_Feed_Abstract_Generator $feed Rex_Product_Feed_Abstract_Generator class object.
	 * @param array                               $product_meta_keys Feed attribute meta keys.
	 *
	 * @since 6.1.0
	 */
	public function __construct( WC_Product $product, Rex_Product_Feed_Abstract_Generator $feed, $product_meta_keys ) {
		$this->is_logging_enabled = is_wpfm_logging_enabled();
        $this->feed               = $feed;
        $this->product            = $product;
        $this->product_meta_keys  = $product_meta_keys;
        $this->analytics_switcher = $feed->analytics ?? false;
        $this->analytics_params   = $feed->analytics_params ?? [];
        $this->feed_config        = $feed->feed_config ?? [];
        $this->feed_rules         = $feed->feed_rules ?? [];
        $this->feed_rules_option  = $feed->feed_rules_option ?? false;
        $this->append_variation   = $feed->append_variation ?? false;
        $this->aelia_currency     = $feed->aelia_currency ?? '';
        $this->wmc_currency       = $feed->wmc_currency ?? '';
        $this->wcml_currency      = $feed->wcml_currency ?? '';
        $this->woocs_currency     = $feed->woocs_currency ?? '';
		$this->wcml               = $feed->wcml ?? false;
		$this->feed_format        = $feed->get_feed_format();
		$this->feed_country       = $feed->get_shipping();
		$this->feed_zip_codes     = $feed->get_zip_code();

		$log = wc_get_logger();
		if ( $this->is_logging_enabled ) {
			$log->info( '*************************', array( 'source' => 'WPFM' ) );
			$log->info( __( 'Start product processing.', 'rex-product-feed' ), array( 'source' => 'WPFM' ) );
			$log->info( 'Product ID: ' . $this->product->get_id(), array( 'source' => 'WPFM' ) );
			$log->info( 'Product Name: ' . $this->product->get_title(), array( 'source' => 'WPFM' ) );
		}

		$this->set_all_value();

		if ( $this->is_logging_enabled ) {
			$log->info( __( 'End product processing.', 'rex-product-feed' ), array( 'source' => 'WPFM' ) );
			$log->info( '*************************', array( 'source' => 'WPFM' ) );
		}
	}

	/**
	 * Retrieve and setup all data for every feed rules.
	 *
	 * @return void
	 * @throws Exception Exception.
	 * @since 1.0.0
	 */
	public function set_all_value() {
		$this->data = array();

		if( !empty( $this->feed_config ) ) {
            foreach ( $this->feed_config as $rule ) {
                $value = $this->set_val( $rule );
                $value = $this->maybe_processing_needed( $value, $rule );

                if ( array_key_exists( 'attr', $rule ) ) {
                    if ( $rule[ 'attr' ] ) {
                        if ( 'attributes' === $rule[ 'attr' ] ) {
                            $this->data[ $rule[ 'attr' ] ][] = array(
                                'name'  => str_replace( 'bwf_attr_pa_', '', $rule[ 'meta_key' ] ),
                                'value' => $value,
                            );
                        } else {
                            $google_shipping_attr = array( 'shipping_country', 'shipping_region', 'shipping_service', 'shipping_price' );
                            if ( in_array( $rule[ 'attr' ], $google_shipping_attr, true ) && 'google' === $this->feed->merchant ) {
                                $this->data[ $rule[ 'attr' ] ][] = $value;
                            } else {
                                $this->data[ $rule[ 'attr' ] ] = $value;
                            }
                        }
                    }
                } elseif ( array_key_exists( 'cust_attr', $rule ) ) {
                    if ( $rule[ 'cust_attr' ] ) {
                        $this->data[ $rule[ 'cust_attr' ] ] = $value;
                    }
                } else {
                    $this->data[ $rule[ 'attr' ] ] = $value;
                }
            }
        }
	}


	/**
	 * Set value for a single feed rule.
	 *
	 * @param array $rule Attribute rule.
	 *
	 * @return array|false|float|int|mixed|string|void|null
	 * @throws Exception Exception.
	 * @since 1.0.0
	 */
	protected function set_val( $rule ) {
		$val = '';
		
		if ( isset( $rule[ 'meta_key' ] ) && isset( $rule[ 'type' ] ) ) {
			if ( 'static' === $rule[ 'type' ] ) {
				$val = $rule[ 'st_value' ];
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_primary_attr( $rule[ 'meta_key' ] ) ) {
				$escape = !empty( $rule[ 'escape' ] ) ? $rule[ 'escape' ] : '';
				$val    = $this->set_pr_att( $rule[ 'meta_key' ], $escape );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_woodmart_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_woodmart_att( $rule[ 'meta_key' ] );
			} elseif( 'meta' === $rule[ 'type' ] && $this->is_divi_attr( $rule[ 'meta_key' ] ) ) {
                $val = $this->set_divi_att( $rule[ 'meta_key' ] );
            } elseif ( 'meta' === $rule[ 'type' ] && $this->is_price_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_price_attr( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_yoast_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_yoast_attr( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_rankmath_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_rankmath_attr( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_perfect_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_perfect_attr();
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_wc_brand_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_wc_brand_attr();
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_berocket_brand_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_berocket_brand_attr();
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_image_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_image_att( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_product_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_product_attr( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_acf_taxonomies( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_acf_taxonomies( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_product_dynamic_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_product_dynamic_attr( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_wpfm_custom_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_wpfm_custom_att( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_product_category_mapper_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_cat_mapper_att( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_glami_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_glami_att( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_dropship_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_dropship_att( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_date_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_date_attr( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_product_custom_tax( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_product_custom_tax( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_woo_discount_rules( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_woo_discount_rules( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_shipping_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_shipping_attr( $rule[ 'meta_key' ], $rule );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_tax_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_tax_attr( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_discount_price_by_asana_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_discount_price_by_asana_attr( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_ean_by_wc_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_ean_by_wc_attr( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_acf_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_acf_attr( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_product_custom_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_product_custom_att( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_rex_dynamic_discount_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_rex_dynamic_discount_attr( $rule[ 'meta_key' ] );
			} elseif ( 'meta' === $rule[ 'type' ] && $this->is_aioseo_attr( $rule[ 'meta_key' ] ) ) {
				$val = $this->set_aioseo_attr( $rule[ 'meta_key' ] );
			}
		}
		return $val;
	}


	/**
	 * Return all data.
	 *
	 * @return array
	 * @since    1.0.0
	 */
	public function get_all_data() {
		return $this->data;
	}

	/**
	 * Set a woodmart gallery attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @since    1.0.0
	 */
	protected function set_woodmart_att( $key ) {
		$key = str_replace( 'woodmart_', '', $key );
		$id  = substr( $key, strpos( $key, "_" ) + 1 );
		if ( 'image_' . $id === $key ) {
			return $this->get_woodmart_gallery( $id );
		}
		return '';
	}

    /**
     * Set a Divi attribute
     *
     * @param string $key Attribute key.
     *
     * @return mixed|string
     * @since 7.2.32
     */
    protected function set_divi_att( $key )
    {
        if( !$this->product ) {
            return '';
        }
        if( !defined( 'ET_BUILDER_WC_PRODUCT_LONG_DESC_META_KEY' ) ) {
            return '';
        }

        switch( $key ) {
            case 'divi_pr_desc':
                $product_id = $this->product->get_id();
                $desc       = $product_id ? get_post_meta( $product_id, ET_BUILDER_WC_PRODUCT_LONG_DESC_META_KEY, true ) : '';
                return $desc && '' !== $desc ? $desc : $this->set_pr_att( 'description' );

            case 'divi_pr_parent_desc':
                $product_id = $this->product->get_parent_id();
                $product_id = $product_id ?: $this->product->get_id();
                $desc       = $product_id ? get_post_meta( $product_id, ET_BUILDER_WC_PRODUCT_LONG_DESC_META_KEY, true ) : '';
                return $desc && '' !== $desc ? $desc : $this->set_pr_att( 'parent_desc' );
            default:
                return '';
        }
    }

	/**
	 * Set a YOAST attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @since    1.0.0
	 */
	protected function set_yoast_attr( $key ) {
		$attr_val = '';
		switch ( $key ) {
			case 'yoast_primary_cat':
				$attr_val = $this->get_seo_primary_cat( 'yoast' );
				break;

			case 'yoast_primary_cat_id':
				$attr_val = $this->get_seo_primary_cat( 'yoast', true );
				break;

			case 'yoast_title':
				$attr_val = preg_replace( '/\s+/', ' ', $this->get_yoast_seo_title() );
				break;

			case 'yoast_meta_desc':
				$attr_val = $this->get_yoast_meta_description();
				break;

			case 'yoast_primary_cats_path':
				$attr_val = $this->get_yoast_product_cats_with_seperator();
				break;

			case 'yoast_primary_cats_pipe':
				$attr_val = $this->get_yoast_product_cats_with_seperator( ' | ' );
				break;

			case 'yoast_primary_cats_comma':
				$attr_val = $this->get_yoast_product_cats_with_seperator( ', ' );
				break;

			default:
				return '';
		}
		return $attr_val;
	}

	/**
	 * Set a RankMath attribute
	 *
	 * @param string $key Attribute key.
	 *
	 * @return false|string
	 * @since 7.2.20
	 */
	protected function set_rankmath_attr( $key ) {
		switch ( $key ) {
			case 'rankmath_primary_cat':
				return $this->get_seo_primary_cat( 'rankmath' );

			case 'rankmath_primary_cat_id':
				return $this->get_seo_primary_cat( 'rankmath', true );

			default:
				return '';
		}
	}


	/**
	 * Get a Woodmart gallery attribute.
	 *
	 * @param int|string $id Image ID.
	 *
	 * @since    1.0.0
	 */
	public function get_woodmart_gallery( $id ) {
		$product_id = $this->product->get_id();
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			$parent_id   = $this->product->get_parent_id();
			$all_gallery = get_post_meta( $parent_id, 'woodmart_variation_gallery_data', true );
			if ( isset( $all_gallery[ $product_id ] ) ) {
				$image_ids = $all_gallery[ $product_id ];
				if ( $image_ids ) {
					$image_ids = explode( ',', $image_ids );
					if ( isset( $image_ids[ $id ] ) ) {
						$image_id = $image_ids[ $id ];
						if ( $image_id ) {
							return wp_get_attachment_url( $image_id );
						}
					}
				}
			}
		}
		return '';
	}

	/**
	 * Set Perfect woocommerce brand attribute
	 *
	 * @return string
	 */
	protected function set_perfect_attr() {
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			$brands = wp_get_post_terms( $this->product->get_parent_id(), 'pwb-brand', array( "fields" => "all" ) );
		} else {
			$brands = wp_get_post_terms( $this->product->get_id(), 'pwb-brand', array( "fields" => "all" ) );
		}

		$brnd = '';
		$i    = 0;
		foreach ( $brands as $brand ) {
			if ( 0 === $i ) {
				$brnd .= $brand->name;
			} else {
				$brnd .= ', ' . $brand->name;
			}
			$i++;
		}
		return $brnd;
	}


	/**
	 * Set woocommerce brand attribute
	 *
	 * @return string
	 */
	protected function set_wc_brand_attr() {
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			$brands = wp_get_post_terms( $this->product->get_parent_id(), 'product_brand', array( "fields" => "all" ) );
		} else {
			$brands = wp_get_post_terms( $this->product->get_ID(), 'product_brand', array( "fields" => "all" ) );
		}

		$brnd = '';
		if ( !empty( $brands ) ) {
			$i = 0;
			foreach ( $brands as $brand ) {
				if ( 0 === $i ) {
					$brnd .= $brand->name;
				} else {
					$brnd .= ', ' . $brand->name;
				}
				$i++;
			}
		}

		return $brnd;
	}


	/**
	 * Set woocommerce brand attribute
	 *
	 * @return string
	 */
	protected function set_berocket_brand_attr() {
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			$brands = wp_get_post_terms( $this->product->get_parent_id(), 'berocket_brand', array( "fields" => "all" ) );
		} else {
			$brands = wp_get_post_terms( $this->product->get_ID(), 'berocket_brand', array( "fields" => "all" ) );
		}
		$brnd = '';
		if ( !empty( $brands ) ) {
			$i = 0;
			foreach ( $brands as $brand ) {
				if ( 0 === $i ) {
					$brnd .= $brand->name;
				} else {
					$brnd .= ', ' . $brand->name;
				}
				$i++;
			}
		}

		return $brnd;
	}


	/**
	 * Set a primary attribute.
	 *
	 * @param string       $key Attribute key.
	 * @param array|string $rule Attribute rule.
	 *
	 * @return false|int|string|null
	 */
	protected function set_pr_att( $key, $rule = 'default' ) {
		switch ( $key ) {
			case 'id':
				return $this->product->get_id();

			case 'sku':
				return $this->product->get_sku();

			case 'parent_sku':
				if ( $this->product->is_type( 'variation' ) ) {
					$parent_id         = $this->product->get_parent_id();
					$wc_parent_product = wc_get_product( $parent_id );

					$pr_id = $wc_parent_product->get_sku();
				} else {
					$pr_id = $this->product->get_sku();
				}
				return $pr_id;

			case 'title':
				if ( !$this->append_variation ) {
					if ( $this->product->is_type( 'variation' ) ) {
						return $this->product->get_title();
					}

					return $this->product->get_name();
				} else {
					if ( $this->is_children() ) {
						$_product     = wc_get_product( $this->product );
						$attr_summary = $_product->get_attribute_summary();
						$attr_array   = explode( ",", $attr_summary );

						$each_child_attr = array();
						foreach ( $attr_array as $ata ) {
							$attr              = strpbrk( $ata, ":" );
							$each_child_attr[] = $attr;
						}
						$each_child_attr_two = array();
						foreach ( $each_child_attr as $eca ) {
							$each_child_attr_two[] = str_replace( ": ", " ", $eca );
						}

						$_title = $this->product->get_title() . " - ";
						return $_title . implode( ', ', $each_child_attr_two );
					} else {
						return $this->product->get_name();
					}
				}
			case 'description':
				if ( $this->is_children() ) {
					$description = $this->product->get_description();
					if ( empty( $description ) ) {
						$_product = wc_get_product( $this->product->get_parent_id() );
						if ( is_object( $_product ) ) {
							return $this->remove_short_codes( $_product->get_description() );
						}
					} else {
						return $this->remove_short_codes( $description );
					}
				} else {
					return $this->remove_short_codes( $this->product->get_description() );
				}

				break;

			case 'parent_desc':
				if ( $this->is_children() ) {
					$parent_product = wc_get_product( $this->product->get_parent_id() );

					if ( is_object( $parent_product ) ) {
						return $this->remove_short_codes( $parent_product->get_description() );
					}
				}

				return $this->product->get_description();

			case 'short_description':
				if ( $this->is_children() ) {
					$short_description = $this->product->get_short_description();
					if ( empty( $short_description ) ) {
						$_product = wc_get_product( $this->product->get_parent_id() );
						if ( is_object( $_product ) ) {
							return $this->remove_short_codes( $_product->get_short_description() );
						}
					} else {
						return $this->remove_short_codes( $short_description );
					}
				} else {
					return $this->remove_short_codes( $this->product->get_short_description() );
				}
				break;

			case 'product_cats':
				return $this->get_product_cats( 'product_cat' );

			case 'product_cat_ids':
				return $this->get_product_cat_ids( 'product_cat' );

			case 'product_cats_path':
				return $this->get_product_cats_with_seperator( 'product_cat' );

			case 'product_cats_path_pipe':
				return $this->get_product_cats_with_seperator( 'product_cat', ' | ' );

			case 'product_subcategory':
				return $this->get_product_subcategory();

			case 'product_tags':
				return $this->get_product_tags();

			case 'spartoo_product_cats':
				return $this->get_spartoo_product_cats();

			case 'sooqr_cats':
				return $this->get_product_cats_for_sooqr();

			case 'checkout_link':
				$product_link = wc_get_checkout_url();
				return add_query_arg( [
					'rexfeed-clear-cart' => true,
					'add-to-cart' => $this->product->get_id()
				], $product_link );

			case 'cart_link':
				$product_link = wc_get_cart_url();
				return add_query_arg( [
					'rexfeed-clear-cart' => true,
					'add-to-cart' => $this->product->get_id()
				], $product_link );

            case 'link':
            case 'review_url':
                $permalink = $this->product->get_permalink();
                if( function_exists( 'wpfm_is_wpml_active' ) && wpfm_is_wpml_active() ) {
                    $permalink = apply_filters( 'wpml_permalink', $permalink, $this->feed->wpml_language );
                }

                if(
                    $this->analytics_switcher &&
                    !empty( $this->analytics_params[ 'utm_source' ] ) &&
                    !empty( $this->analytics_params[ 'utm_medium' ] ) &&
                    !empty( $this->analytics_params[ 'utm_campaign' ] )
                ) {
                    if( is_array( $rule ) && in_array( 'decode_url', $rule, true ) ) {
                        $permalink = add_query_arg( array_filter( $this->analytics_params ), urldecode( $permalink ) );
                    }
                    else {
                        $permalink = $this->safe_char_encode_url( add_query_arg( array_filter( $this->analytics_params ), urldecode( $permalink ) ) );
                    }
                }
                elseif( is_array( $rule ) && in_array( 'decode_url', $rule, true ) ) {
                    $permalink = urldecode( $permalink );
                }
                else {
                    $permalink = $this->safe_char_encode_url( urldecode( $permalink ) );
                }
                /**
                 * Modify the product url before including in the feed.
                 *
                 * @param string $permalink Product url.
                 *
                 * @since 7.3.6
                 */
                return apply_filters( 'rex_feed_product_url', $permalink );

			case 'parent_url':
				$_pr = $this->product;
				if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
					$_pr = wc_get_product( $this->product->get_parent_id() );
				}
                $permalink = $_pr->get_permalink();
                if (
                    $this->analytics_switcher &&
                    !empty( $this->analytics_params[ 'utm_source' ] ) &&
                    !empty( $this->analytics_params[ 'utm_medium' ] ) &&
                    !empty( $this->analytics_params[ 'utm_campaign' ] )
                ) {
                    if ( is_array( $rule ) && in_array( 'decode_url', $rule, true ) ) {
                        $permalink = add_query_arg( array_filter( $this->analytics_params ), urldecode( $permalink ) );
                    }
                    else {
                        $permalink = $this->safe_char_encode_url( add_query_arg( array_filter( $this->analytics_params ), urldecode( $permalink ) ) );
                    }
                }
				elseif ( is_array( $rule ) && in_array( 'decode_url', $rule, true ) ) {
                    $permalink = urldecode( $permalink );
				}
                else {
                    $permalink = $this->safe_char_encode_url( urldecode( $permalink ) );
                }
                /**
                 * Modify the parent product url before including in the feed.
                 *
                 * @param string $permalink Parent product url.
                 *
                 * @since 7.3.6
                 */
				return apply_filters( 'rex_feed_product_parent_url', $permalink );

			case 'condition':
				return $this->get_condition();

			case 'item_group_id':
				return $this->get_item_group_id();

			case 'availability':
				return $this->get_availability();

			case 'availability_zero_three':
				$if_available = $this->get_availability();
				if ( 'out_of_stock' === $if_available ) {
					return '3';
				}
				return '0';

			case 'availability_zero_one':
				$if_available = $this->get_availability();
				if ( 'out_of_stock' === $if_available ) {
					return '0';
				}
				return '1';

			case 'availability_underscore':
				return $this->get_availability_underscore();

			case 'availability_backorder_instock':
				return $this->get_availability_backorder_instock();

			case 'availability_backorder':
				return $this->get_availability_backorder();

			case 'quantity':
				return $this->product->get_stock_quantity();

			case 'weight':
				return $this->product->get_weight();

			case 'width':
				return $this->product->get_width();

			case 'height':
				return $this->product->get_height();

			case 'length':
				return $this->product->get_length();

			case 'type':
				return $this->product->get_type();

			case 'in_stock':
				return $this->get_stock();

			case 'rating_average':
				return $this->product->get_average_rating();

			case 'rating_total':
				return $this->product->get_rating_count();

			case 'identifier_exists':
				return $this->calculate_identifier_exists( $this->data );

			case 'current_page':
				if ( $this->product->is_type( 'variation' ) ) {
					$product_id = $this->product->get_parent_id();
				} else {
					$product_id = $this->product->get_id();
				}
				return get_permalink( $product_id );

			case 'author_name':
				if ( $this->product->is_type( 'variation' ) ) {
					$author_id = get_post_field( 'post_author', $this->product->get_parent_id() );
				} else {
					$author_id = get_post_field( 'post_author', $this->product->get_id() );
				}
				return get_the_author_meta( 'display_name', $author_id );

			case 'author_url':
				if ( $this->product->is_type( 'variation' ) ) {
					$author_id = get_post_field( 'post_author', $this->product->get_parent_id() );
				} else {
					$author_id = get_post_field( 'post_author', $this->product->get_id() );
				}
				return get_author_posts_url( $author_id );

			default:
				return '';
		}

		return '';
	}


	/**
	 * Get shipping and tax attributes value
	 *
	 * @param string $key Attribute key.
	 * @param array  $rule Attribute rule.
	 *
	 * @return array|float|int|mixed|string
	 * @since 7.2.9
	 */
	protected function set_shipping_attr( $key, $rule ) {
		$rex_feed_shipping = new Rex_Product_Feed_Shipping( $this->feed_country, $this->product );
		$attr_val = '';
		switch ( $key ) {
			case 'shipping':
				$methods  = $rex_feed_shipping->get_shipping_zones();
				$attr_val = $this->add_class_no_class_cost( $methods, $rule );
				break;

			case 'shipping_class':
				if ( $this->product->get_shipping_class_id() ) {
					$shipping_class_term = get_term( (int) $this->product->get_shipping_class_id() );
					$attr_val            =  $shipping_class_term->slug ?? '';
				}
				break;

			case 'shipping_cost':
				$attr_val = $rex_feed_shipping->get_wc_shipping_cost( $this->product );
				break;

			case 'min_shipping_cost':
				$attr_val = $rex_feed_shipping->get_wc_shipping_cost( $this->product, true );
				break;

			default:
				return '';
		}
		return $attr_val;
	}


	/**
	 * Get tax attributes value
	 *
	 * @param string $key Attribute key.
	 *
	 * @return int|string
	 * @since 7.2.10
	 */
    protected function set_tax_attr( $key ) {
        $product = $this->product;
        if( 'variation' === $this->product->get_type() ) {
            $parent_id = $this->product->get_parent_id();
            $product = wc_get_product( $parent_id );
        }

        switch ( $key ) {
            case 'tax_class':
                return $product ? $product->get_tax_class() : '';

            case 'tax':
                $tax_class = $product ? $product->get_tax_class() : '';
                $tax_rates = wpfm_get_cached_data( 'wc_tax_rates_' . $tax_class );
                return $tax_rates ?: WC_Tax::get_rates_for_tax_class( $tax_class );
            default:
                return '';
        }
    }


	/**
	 * Get EAN attribute value by EAN by WooCommerce
	 *
	 * @param string $key Attribute key.
	 *
	 * @return mixed|string
	 * @since 7.2.19
	 */
	protected function set_ean_by_wc_attr( $key ) {
		if ( '_alg_ean' === $key && $this->product && !is_wp_error( $this->product ) ) {
			return get_post_meta( $this->product->get_id(), $key, true );
		}
		return '';
	}

	/**
	 * Retrieves a specific ACF (Advanced Custom Fields) attribute from the product post meta.
	 *
	 * @param string $key The key of the ACF attribute to retrieve.
	 *
	 * @return mixed|string Returns the value of the specified ACF attribute from the product's post meta if available; otherwise, an empty string.
	 *
     * @since 7.3.20
	 */
    protected function set_acf_attr( $key ) {
        if ( !empty( $this->product ) && !is_wp_error( $this->product ) ) {
            $product_id = 'variation' === $this->product->get_type() ? $this->product->get_parent_id() : $this->product->get_id();
            $value      = get_post_meta( $product_id, $key, true );
            if ( Rex_Product_Feed_Actions::is_acf_field_type( $key, 'image' ) ) {
                return wp_get_attachment_url( $value );
            }
            elseif ( Rex_Product_Feed_Actions::is_acf_field_type( $key, 'date_time_picker' )
                || Rex_Product_Feed_Actions::is_acf_field_type( $key, 'date_picker' )
                || Rex_Product_Feed_Actions::is_acf_field_type( $key, 'time_picker' )
            ) {
                $field = get_field_object( get_post_meta( $product_id, "_{$key}", true ) );
                $format = $field[ 'return_format' ] ?? '';
                return !empty( $format ) ? date( $format, strtotime( $value ) ) : $value;
            }
            return $value;
        }
        return '';
    }

	/**
	 * Retrieves a specific ACF (Advanced Custom Fields) attribute from the product post meta.
	 *
	 * @param string $key The key of the ACF attribute to retrieve.
	 *
	 * @return mixed|string Returns the value of the specified ACF attribute from the product's post meta if available; otherwise, an empty string.
	 * @since 7.3.20
	 */
	protected function set_acf_taxonomies( $key ) {
		return $this->get_product_tags( ', ', $key );
	}


	/**
	 * Get discounted price by Discount Rules and Dynamic Pricing for WooCommerce
	 *
	 * @param string $key Attribute key.
	 * @param array  $rule Attribute rule.
	 *
	 * @return float|string|void
	 * @throws Exception Exception.
	 * @since 7.2.20
	 */
	protected function set_discount_price_by_asana_attr( $key ) {
		if ( is_wp_error( $this->product ) || !$this->product ) {
			return '';
		}
        $key   = str_replace( 'asana_', '', $key );
		$price = $this->set_price_attr( $key );
		if ( $price ) {
			$price = Rex_Feed_Discount_Rules_Asana_Plugins::get_discounted_price( $this->product->get_id(), (float) $price );
			return $price ?: '';
		}
		return '';
	}

    /**
     * Sets the attribute for the WooCommerce Dynamic Discount plugin.
     *
     * @param string $key The key for the dynamic discount attribute.
     * @return string The discounted product price.
     *
     * @since 7.4.1
     */
    protected function set_rex_dynamic_discount_attr( $key ) {
        if ( is_wp_error( $this->product ) || !$this->product ) {
            return '';
        }
        $key   = str_replace( 'rexdd_', '', $key );
        $price = $this->set_price_attr( $key );
        if ( !empty( $price ) ) {
            $discounts = (new RexTheme\RexDynamicDiscount\Discounts\DiscountCalculator())->get_discount_price( $this->product );
            if ( !empty( $discounts[ 'product_base_discount' ][ 'discount_type' ] ) && !empty( $discounts[ 'product_base_discount' ][ 'discount_value' ] ) ) {
                if ( 'flat' === $discounts[ 'product_base_discount' ][ 'discount_type' ] ) {
                    $price = $price - $discounts[ 'product_base_discount' ][ 'discount_value' ];
                }
                else {
                    $price = $price - ( $price * $discounts[ 'product_base_discount' ][ 'discount_value' ] / 100 );
                }
            }
        }
        return apply_filters(
            'rex_feed_discounted_product_price',
            !empty( $price ) && $price > 0 ? wc_format_decimal( $price, wc_get_price_decimals() ) : ''
        );
    }

    /**
     * Retrieves the price of a product based on its type.
     *
     * This function handles various product types such as grouped, composite, variable,
     * bundle, and simple products, and retrieves their prices accordingly. It considers
     * different scenarios for each product type to determine the appropriate price
     * retrieval method.
     *
     * @param string $type Specifies the type of price to retrieve (e.g., regular price, sale price).
     * @return string The formatted price of the product, or an empty string if the price
     *               is not available or cannot be determined.
     * @since 7.4.0
     */
    private function get_product_price( $type = '_regular_price' ) {
        if ( $this->product->is_type( 'grouped' ) ) {
            $product_price = wc_format_decimal( rex_feed_get_grouped_price( $this->product, $type ), wc_get_price_decimals() );
        }
        elseif ( $this->product->is_type( 'composite' ) ) {
            $_product = class_exists( 'WC_Product_Composite' ) ? new WC_Product_Composite( $this->product->get_id() ) : null;
            if ( is_plugin_active( 'wpc-composite-products/wpc-composite-products.php' ) ) {
                $method = "get{$type}";
            }
            else {
                $method = "get_composite{$type}";
            }
            $product_price = method_exists( $_product, $method ) ? $_product->$method() : 0;
        }
        elseif ( $this->product->is_type( 'variable' ) ) {
            $default_attributes = rex_feed_get_default_variable_attributes( $this->product );

            if ( !empty( $default_attributes ) ) {
                $variation_id = rex_feed_find_matching_product_variation( $this->product, $default_attributes );
                if ( !empty( $variation_id ) ) {
                    $_variation_product = wc_get_product( $variation_id );
                    $method             = "get{$type}";
                    $product_price      = $_variation_product->$method();
                }
            }
            else {
                $method        = "get_variation{$type}";
                $product_price = $this->product->$method();
            }
        }
        elseif ( $this->product->is_type( 'bundle' ) ) {
            $product_price = $this->product->get_bundle_price();
        }
        else {
            $method        = "get{$type}";
            $product_price = $this->product->$method();
        }
        $product_price = !empty( $product_price ) && $product_price > 0 ? $product_price : '';

        /**
         * Filters the product price before it is returned.
         *
         * This hook allows developers to modify the product price before it is
         * returned by the function/method that uses it.
         *
         * @param string       $product_price The product price.
         * @param WC_Product   $product       The WooCommerce product object.
         * @param string       $type          The type of price being retrieved (e.g., regular price, sale price).
         * @return string                    The modified product price.
         * @since 7.4.0
         */
        return apply_filters( 'rex_feed_product_price_before_formatting', $product_price, $this->product, $type, $this );
    }

    /**
     * Retrieves the product price from the database and applies filters before returning.
     *
     * This method retrieves the product price from the database based on the specified type
     * (e.g., regular price, sale price) and applies filters to the price before returning it.
     *
     * @param string $type The type of price being retrieved (e.g., regular price, sale price).
     * @return string The product price after applying filters.
     * @since 7.4.0
     */
    private function get_product_price_from_db( $type = '_regular_price' ) {
        $product_price = get_post_meta( $this->product->get_id(), $type, true );
        $product_price = !empty( $product_price ) && $product_price > 0 ? $product_price : '';

        /**
         * See the filter documentation in `get_product_price` method.
         */
        return apply_filters( 'rex_feed_product_price_before_formatting', $product_price, $this->product, $type, $this );
    }

	/**
	 * Set a price attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return float|int|mixed|string|void
	 * @throws Exception Exception.
	 * @since 1.0.0
	 */
    protected function set_price_attr( $key ) {
        switch ( $key ) {
            case 'price':
                $product_price = $this->get_product_price();
                break;
            case 'current_price':
                $product_price = $this->get_product_price( '_price' );
                break;
            case 'sale_price':
                $product_price = $this->get_product_price( '_sale_price' );
                break;
            case 'price_with_tax':
                $_price        = $this->get_product_price();
                $tax_rate_id   = Rex_Product_Feed_Tax::get_wc_tax_rate_id( $this->product, $this->feed_country );
                $product_price = Rex_Product_Feed_Tax::get_price_with_tax( $_price, $tax_rate_id );
                break;
            case 'current_price_with_tax':
                $_price        = $this->get_product_price( '_price' );
                $tax_rate_id   = Rex_Product_Feed_Tax::get_wc_tax_rate_id( $this->product, $this->feed_country );
                $product_price = Rex_Product_Feed_Tax::get_price_with_tax( $_price, $tax_rate_id );
                break;
            case 'sale_price_with_tax':
                $_price        = $this->get_product_price( '_sale_price' );
                $tax_rate_id   = Rex_Product_Feed_Tax::get_wc_tax_rate_id( $this->product, $this->feed_country );
                $product_price = Rex_Product_Feed_Tax::get_price_with_tax( $_price, $tax_rate_id );
                break;
            case 'price_excl_tax':
                $_price        = $this->get_product_price();
                $tax_rate_id   = Rex_Product_Feed_Tax::get_wc_tax_rate_id( $this->product, $this->feed_country );
                $product_price = Rex_Product_Feed_Tax::get_price_without_tax( $_price, $tax_rate_id );
                break;
            case 'current_price_excl_tax':
                $_price        = $this->get_product_price( '_price' );
                $tax_rate_id   = Rex_Product_Feed_Tax::get_wc_tax_rate_id( $this->product, $this->feed_country );
                $product_price = Rex_Product_Feed_Tax::get_price_without_tax( $_price, $tax_rate_id );
                break;
            case 'sale_price_excl_tax':
                $_price        = $this->get_product_price( '_sale_price' );
                $tax_rate_id   = Rex_Product_Feed_Tax::get_wc_tax_rate_id( $this->product, $this->feed_country );
                $product_price = Rex_Product_Feed_Tax::get_price_without_tax( $_price, $tax_rate_id );
                break;
            case 'price_db':
                $type = '_regular_price';
                if ( $this->product->is_type( 'variable' ) || $this->product->is_type( 'grouped' ) ) {
                    $type = '_price';
                }
                $product_price = $this->get_product_price_from_db( $type );
                break;
            case 'current_price_db':
                $product_price = $this->get_product_price_from_db( '_price' );
                break;
            case 'sale_price_db':
                $product_price = $this->get_product_price_from_db( '_sale_price' );
                break;
            default:
                $product_price = '';
                break;
        }

        /**
         * Filters the product price before it is returned.
         *
         * This hook allows developers to modify the product price before it is
         * returned by the function/method that uses it.
         *
         * @param string       $product_price The product price.
         * @return string                    The modified product price.
         * @since 7.4.1
         */
        return apply_filters(
            'rex_feed_product_price',
            !empty( $product_price ) && $product_price > 0 ? wc_format_decimal( $product_price, wc_get_price_decimals() ) : ''
        );
    }

	/**
	 * Retrieves image metadata
	 *
	 * @return array|false
	 * @since 1.0.0
	 */
	protected function get_image_meta() {
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			$_pr = wc_get_product( $this->product->get_parent_id() );
			return $_pr ? wp_get_attachment_metadata( $_pr->get_image_id() ) : array();
		} else {
			return $this->product ? wp_get_attachment_metadata( $this->product->get_image_id() ) : array();
		}
	}

	/**
	 * Set a Image attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return string
	 * @since    1.0.0
	 */
	protected function set_image_att( $key ) {
		$attr_val = '';
		if ( $this->product && !is_wp_error( $this->product ) ) {
			switch ( $key ) {
				case 'main_image':
					if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
						$_pr      = wc_get_product( $this->product->get_parent_id() );
						$attr_val = $_pr ? wp_get_attachment_url( $_pr->get_image_id() ) : '';
					} else {
						$attr_val = wp_get_attachment_url( $this->product->get_image_id() );
					}
					break;

				case 'image_height':
					$image_src = $this->get_image_meta();
					$attr_val  = !empty( $image_src[ 'height' ] ) ? $image_src[ 'height' ] : '';
					break;

				case 'image_width':
					$image_src = $this->get_image_meta();
					$attr_val  = !empty( $image_src[ 'width' ] ) ? $image_src[ 'width' ] : '';
					break;

				case 'encoding_format':
					$image_src = $this->get_image_meta();
					$attr_val  = !empty( $image_src[ 'sizes' ][ 'woocommerce_thumbnail' ][ 'mime-type' ] ) ? $image_src[ 'sizes' ][ 'woocommerce_thumbnail' ][ 'mime-type' ] : '';
					break;

				case 'image_size':
					if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
						$_pr      = wc_get_product( $this->product->get_parent_id() );
						$attr_val = $_pr ? filesize( get_attached_file( $_pr->get_image_id() ) ) : 0;
					} else {
						$attr_val = $this->product ? filesize( get_attached_file( $this->product->get_image_id() ) ) : 0;
					}
					break;

				case 'keywords':
					$image_src = $this->get_image_meta();
					$attr_val  = isset( $image_src[ 'image_meta' ][ 'keywords' ] ) ? implode( ', ', $image_src[ 'image_meta' ][ 'keywords' ] ) : '';
					break;

				case 'thumbnail_image':
					if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
                        $product_id = $this->product->get_parent_id();
					} else {
                        $product_id = $this->product->get_id();
					}
                    $attr_val = get_the_post_thumbnail_url( $product_id, 'thumbnail' );
					break;

				case 'featured_image':
                    $attr_val = wp_get_attachment_url( $this->product->get_image_id() );
					break;

				case 'all_image_array':
					$attr_val = $this->get_all_image( '', true );
					break;

				case 'all_image':
					$attr_val = $this->get_all_image();
					break;

				case 'all_image_pipe':
					$attr_val = $this->get_all_image( '|' );
					break;

				case 'variation_img':
					$attr_val = wp_get_attachment_url( $this->product->get_image_id() );
					break;

				default:
					$key      = str_replace( 'additional_', '', $key );
					$attr_val = $this->get_additional_image( $key );
					break;
			}
		}
		return $attr_val;
	}

	/**
	 * Get all product images with separators
	 *
	 * @param string $sep Separator.
	 * @param bool   $return_array If return data should be array or string.
	 *
	 * @return array|string
	 * @since 7.2.19
	 */
	private function get_all_image( $sep = ',', $return_array = false ) {
		if ( !is_wp_error( $this->product ) && $this->product ) {
			$attachment_ids = $this->product->get_gallery_image_ids();
			$attachment_ids = array_merge( array( $this->product->get_image_id() ), $attachment_ids );
			$all_images     = array();

			foreach ( $attachment_ids as $val ) {
				$all_images[] = wp_get_attachment_url( $val );
			}
			if ( $return_array ) {
				return $all_images;
			}
			return implode( $sep, $all_images );
		}
		return '';
	}

	/**
	 * Set a Product attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function set_product_attr( $key ) {
		if ( $this->product && !is_wp_error( $this->product ) ) {
			$key = str_replace( 'bwf_attr_pa_', '', $key );

			if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
				$var_id = $this->product->get_parent_id();
				$var_pr = wc_get_product( $var_id );
				$value  = $var_pr ? $var_pr->get_attribute( $key ) : '';
			} else {
				$value = $this->product->get_attribute( $key );
			}

			if ( !empty( $value ) ) {
				$value = trim( $value );
			}
			return $value;
		}
		return '';
	}


	/**
	 * Set a Glami attribute
	 *
	 * @param string $key Attribute key.
	 *
	 * @return mixed
	 * @since    1.0.0
	 */
	protected function set_glami_att( $key ) {
		if ( 'WC_Product_Variation' !== get_class( $this->product ) ) {
			return '';
		}
		$key   = str_replace( 'param_', '', $key );
		$value = $this->product->get_attribute( $key );

		if ( !empty( $value ) ) {
			$value = trim( $value );
		}
		return $value;
	}


	/**
	 * Set a Dropship attribute
	 *
	 * @param string $key Attribute key.
	 *
	 * @return mixed
	 */
	protected function set_dropship_att( $key ) {
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			return get_post_meta( $this->product->get_parent_id(), $key, true );
		}
		return get_post_meta( $this->product->get_id(), $key, true );
	}


	/**
	 * Set a Date attributes.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function set_date_attr( $key ) {
		$attr_val = '';
		switch ( $key ) {
			case 'post_publish_date':
				if ( $this->product->is_type( 'variation' ) ) {
					$product_id = $this->product->get_parent_id();
				} else {
					$product_id = $this->product->get_id();
				}
				$attr_val = get_the_date( '', $product_id ) . 'T' . get_the_time( 'g:i:s', $product_id ) . 'Z';
				break;

			case 'last_updated':
				$attr_val = $this->product->get_date_modified()
										  ->date( 'Y-m-d' ) . 'T' . $this->product->get_date_modified()
																				  ->date( 'H:i:s' ) . 'Z';
				if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
					$_pr      = wc_get_product( $this->product->get_parent_id() );
					$attr_val = $_pr->get_date_modified()->date( 'Y-m-d' ) . 'T' . $_pr->get_date_modified()
																					   ->date( 'H:i:s' ) . 'Z';
				}
				break;

			case 'sale_price_dates_from':
				$date_starts = $this->product->get_date_on_sale_from();
				/**
				 * This filter allows users to modify the sale price start date format
				 *
				 * @param string $format Date format.
				 *
				 * @since 7.3.23
				 */
				$format      = apply_filters( 'rexfeed_sale_price_start_date_format', get_option( 'date_format' ) );
				$attr_val    = !$date_starts ? $date_starts : gmdate( $format, $date_starts->getTimestamp() );
				break;

			case 'sale_price_dates_to':
				$date_ends = $this->product->get_date_on_sale_to();
				/**
				 * This filter allows users to modify the sale price end date format
				 *
				 * @param string $format Date format.
				 *
				 * @since 7.3.23
				 */
				$format      = apply_filters( 'rexfeed_sale_price_end_date_format', get_option( 'date_format' ) );
				$attr_val  = !$date_ends ? $date_ends : gmdate( $format, $date_ends->getTimestamp() );
				break;

			case 'sale_price_effective_date':
				$date_to               = get_post_meta( $this->product->get_id(), '_sale_price_dates_to', true );
				$sale_price_dates_to   = $date_to ? date_i18n( 'Y-m-d', $date_to ) : '';
				$date_from             = get_post_meta( $this->product->get_id(), '_sale_price_dates_from', true );
				$sale_price_dates_from = $date_from ? date_i18n( 'Y-m-d', $date_from ) : '';

				if ( !empty( $sale_price_dates_to ) && !empty( $sale_price_dates_from ) ) {
					/**
					 * This filter allows users to modify the sale price effective date format
					 *
					 * @param string $format Date format [c].
					 *
					 * @since 7.3.23
					 */
					$format   = apply_filters( 'rexfeed_sale_price_effective_date_format', 'c' );
					$from     = gmdate( $format, strtotime( $sale_price_dates_from ) );
					$to       = gmdate( $format, strtotime( $sale_price_dates_to ) );
					$attr_val = $from . '/' . $to;
				}
				break;

			default:
				break;
		}
		return $attr_val;
	}


	/**
	 * Set the value for Custom Taxomonies.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return false|string
	 */
	protected function set_product_custom_tax( $key ) {
		return $this->get_product_cats( $key );
	}


	/**
	 * Set the value for WooDiscount Rules attributes
	 *
	 * @param string $key Attribute key.
	 *
	 * @return mixed|string|void
	 */
    protected function set_woo_discount_rules( $key ) {
        $attr_val        = '';
        $discount_manage = new ManageDiscount();
        if ( 'woo_discount_rules_price' === $key ) {
            $discounted_price = $discount_manage->calculateInitialAndDiscountedPrice( $this->product, 1 );
            $discounted_price = !empty( $discounted_price[ 'discounted_price' ] ) ? $discounted_price[ 'discounted_price' ] : '';

            $attr_val = !empty( $discounted_price ) ? $discounted_price : $this->product->get_price();
        } elseif ( 'woo_discount_rules_expire_date' === $key ) {
            $rules = DBTable::getRules();
            foreach ( $rules as $rule ) {
                if ( 'wdr_simple_discount' === $rule->discount_type ) {
                    $format   = "Y-m-d H:i";
                    $end_date = $rule->date_to;
                    $attr_val = $end_date && '' !== $end_date ? gmdate( $format, (int)$end_date ) : $end_date;
                }
            }
        }
        return $attr_val;
    }

	/**
	 * Set a Product Dynamic attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @since    1.0.0
	 */
	protected function set_product_dynamic_attr( $key ) {
		$val = '';
		if ( 'WC_Product_Simple' !== get_class( $this->product ) ) {
			$val = $this->product ? trim( $this->product->get_attribute( $key ) ) : '';
			if ( '' === $val ) {
				$val = $this->get_product_cats( $key );
			}
		}
		return $val;
	}

	/**
	 * Set a WPFM Custom attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @since    1.0.0
	 */
	protected function set_wpfm_custom_att( $key ) {
		$key = str_replace( 'custom_attributes_', '', $key );
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			$val = get_post_meta( $this->product->get_id(), $key, true );
			return $val && '' !== $val ? $val : get_post_meta( $this->product->get_parent_id(), $key, true );
		}
		return get_post_meta( $this->product->get_id(), $key, true );
	}

	/**
	 * Set a Product Custom attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @since    1.0.0
	 */
	protected function set_product_custom_att( $key ) {
		$new_key = str_replace( 'custom_attributes_', '', $key );

		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			$pr_id      = $this->product->get_parent_id();
			$meta_value = get_post_meta( $this->product->get_id(), $new_key, true );
			// need to check if these attributes value is assigned to the mother product
			if ( !$meta_value ) {
                $meta_value = get_post_meta( $pr_id, $new_key, true );
                if( $meta_value ) {
                    return $meta_value;
                }
				$list = $this->product->get_attributes();

				if ( array_key_exists( $new_key, $list ) ) {
					$meta_value = $list[ $new_key ];
				} else {
					$acf_field = get_post_meta( $this->product->get_parent_id(), '_' . $new_key, true );

					if ( '' !== $acf_field && preg_match( '/field_/', $acf_field ) ) {
						$meta_value = get_post_meta( $pr_id, $new_key, true );
					}
				}
			}
		} else {
			$pr_id      = $this->product->get_id();
			$meta_value = get_post_meta( $pr_id, $new_key, true );

			if ( 'rank_math_primary_product_cat' === $new_key ) {
				$meta_value = '' !== $meta_value ? get_the_category_by_ID( $meta_value ) : '';
			}
			if ( !$meta_value ) {
				$list = $this->get_product_attributes( $this->product->get_id() );

				if ( array_key_exists( $new_key, $list ) ) {
					$meta_value = str_replace( '|', ',', $list[ $new_key ] );
				}
			}
		}

		if ( '' === $meta_value ) {
			$pr_attr = get_post_meta( $pr_id, '_product_attributes', true );
			if ( isset( $pr_attr[ $new_key ][ 'value' ] ) ) {
				$meta_value = $pr_attr[ $new_key ][ 'value' ];
				$meta_value = explode( '|', $meta_value );
			}
		}

		if ( is_array( $meta_value ) && !empty( $meta_value ) ) {
			if ( 'wooco_components' === $new_key ) {
				$meta_temp = '';
				foreach ( $meta_value as $meta ) {
					$meta_temp  = implode( ':', $meta );
					$meta_temp .= '||';
				}
				$meta_value = rtrim( $meta_temp, '||' );
			} else {
				$meta_value = implode( ', ', $meta_value );
			}
		}
		return apply_filters( "product_custom_att_value_{$new_key}", $meta_value, $new_key, $this->product );
	}


	/**
	 * Get all the product attributes
	 *
	 * @param int|string $id Product ID.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function get_product_attributes( $id ) {
		global $wpdb;
		$list   = array();
		$sql    = 'SELECT `meta_key` AS name, `meta_value` AS value FROM %1s  as postmeta
                            INNER JOIN %1s AS posts
                            ON postmeta.post_id = posts.id
                            WHERE posts.post_type LIKE %s
                            AND postmeta.meta_key = %s
                            AND postmeta.post_id = %d';
		$result = $wpdb->get_results(
			$wpdb->prepare(
				$sql, //phpcs:ignore
				$wpdb->postmeta,
				$wpdb->posts,
				'%' . $wpdb->esc_like( 'product' ) . '%',
				'_product_attributes',
				$id
			)
		);

		if ( count( $result ) ) {
			foreach ( $result as $value ) {
				$value_display = str_replace( "_", " ", $value->name );
				if ( !preg_match( "/_product_attributes/i", $value->name ) ) {
					$list[ $value->name ] = ucfirst( $value_display );
				} else {
					$product_attributes = json_decode( $value->value );
					if ( !empty( $product_attributes ) ) {
						foreach ( $product_attributes as $k => $arr_value ) {
							$value_display = str_replace( "_", " ", $arr_value[ 'value' ] );
							$list[ $k ]    = ucfirst( $value_display );
						}
					}
				}
			}
		}
		return $list;
	}

	/**
	 * Set Product Category Map
	 *
	 * @param string $key Attribute key.
	 *
	 * @since    3.0
	 */
	protected function set_cat_mapper_att( $key ) {
		$attr_val = '';
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			$cat_lists = get_the_terms( $this->product->get_parent_id(), 'product_cat' );
		} else {
			$cat_lists = get_the_terms( $this->product->get_id(), 'product_cat' );
		}
		$wpfm_category_map = get_option( 'rex-wpfm-category-mapping' );

		if ( $wpfm_category_map ) {
			$map        = $wpfm_category_map[ $key ];
			$map_config = $map[ 'map-config' ];

			if ( $cat_lists ) {
				foreach ( $cat_lists as $term ) {
					$map_keys = is_array( $map_config ) && !empty( $map_config ) ? array_column( $map_config, 'map-key' ) : array();
					$map_key  = array_search( $term->term_id, $map_keys );

					if ( 0 === $map_key || $map_key ) {
						$map_array = $map_config[ $map_key ];
						$map_value = $map_array[ 'map-value' ];
						if ( !empty( $map_value ) ) {
							preg_match( "~^(\d+)~", $map_value, $m );
							if ( count( $m ) > 1 ) {
								if ( $m[ 1 ] ) {
									$attr_val = iconv("UTF-8", "ISO-8859-1", urldecode( $m[ 1 ] ));
								} else {
									$attr_val = $map_value;
								}
							} else {
								$attr_val = $map_value;
							}
						}
					}
				}
			}
		}
		return $attr_val;
	}


	/**
	 * Get yoast seo title
	 *
	 * @return string
	 */
	public function get_yoast_seo_title() {
		$title = '';
		if ( 'variation' === $this->product->get_type() ) {
			$product_id = $this->product->get_parent_id();
		} else {
			$product_id = $this->product->get_id();
		}
		if ( function_exists( 'wpseo_replace_vars' ) ) {
			$wpseo_title = get_post_meta( $product_id, '_yoast_wpseo_title', true );
			if ( $wpseo_title ) {
				$product_title_pattern = $wpseo_title;
			} else {
				$wpseo_titles          = get_option( 'wpseo_titles' );
				$product_title_pattern = $wpseo_titles[ 'title-product' ];
			}
			$title = wpseo_replace_vars( $product_title_pattern, get_post( $product_id ) );
		}
		if ( !empty( $title ) ) {
			return $title;
		} else {
			return $this->product->get_title();
		}
	}


	/**
	 * Get yoast meta descriptions
	 *
	 * @return string
	 */
	public function get_yoast_meta_description() {
		$description = '';
		if ( 'variation' === $this->product->get_type() ) {
			$product_id = $this->product->get_parent_id();
		} else {
			$product_id = $this->product->get_id();
		}
		if ( function_exists( 'wpseo_replace_vars' ) ) {
			$wpseo_meta_description = get_post_meta( $product_id, '_yoast_wpseo_metadesc', true );
			if ( $wpseo_meta_description ) {
				$product_meta_desc_pattern = $wpseo_meta_description;
			} else {
				$wpseo_titles              = get_option( 'wpseo_titles' );
				$product_meta_desc_pattern = $wpseo_titles[ 'metadesc-product' ];
			}
			$description = wpseo_replace_vars( $product_meta_desc_pattern, get_post( $product_id ) );
		}

		if ( !empty( $description ) ) {
			return $description;
		} else {
			return $this->product->get_description();
		}
	}


	/**
	 * Get additional image url by key.
	 *
	 * @param string $key Image key.
	 *
	 * @since    1.0.0
	 */
	protected function get_additional_image( $key ) {
		if ( empty( $this->additional_images ) ) {
			$this->set_additional_images();
		}

		if ( array_key_exists( $key, $this->additional_images ) ) {
			return $this->additional_images[ $key ];
		}

		return '';
	}

	/**
	 * Retrieve a product's categories as a list with specified format.
	 *
	 * @param string $taxonomy Optional. Taxonomy.
	 * @param string $sep Optional. Separate items using this.
	 *
	 * @return string|false
	 */
	protected function get_product_cats( $taxonomy, $sep = ', ' ) {
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			return $this->get_the_term_list( $this->product->get_parent_id(), $taxonomy, $sep );
		} else {
			return $this->get_the_term_list( $this->product->get_id(), $taxonomy, $sep );
		}
	}

	/**
	 * Retrieve a product's category ids with comma separated
	 *
	 * @param string $taxonomy Optional. Taxonomy.
	 * @param string $sep Optional. Separate items using this.
	 *
	 * @return string|false
	 * @since 7.2.18
	 */
	protected function get_product_cat_ids( $taxonomy, $sep = ', ' ) {
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			return $this->get_the_term_list( $this->product->get_parent_id(), $taxonomy, $sep, true );
		} else {
			return $this->get_the_term_list( $this->product->get_id(), $taxonomy, $sep, true );
		}
	}


	/**
	 * Get product category for Spartoo
	 *
	 * @return array
	 */
	protected function get_spartoo_product_cats() {
		$term_array = array();
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			$terms = get_the_terms( $this->product->get_parent_id(), 'product_cat' );
		} else {
			$terms = get_the_terms( $this->product->get_id(), 'product_cat' );
		}

		$count = 0;
		if ( $terms ) {
			$count = count( $terms );
		}
		if ( $count > 1 ) {
			foreach ( $terms as $term ) {
				$term_array[] = $term->name;
			}
		}
		return $term_array;
	}


	/**
	 * Retrieve a product's categories as a list with specified format.
	 *
	 * @param string $taxonomy Optional. Taxonomy.
	 * @param string $sep Optional. Separate items using this.
	 *
	 * @return string
	 */
	protected function get_product_cats_with_seperator( $taxonomy, $sep = ' > ' ) {
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			return $this->get_the_term_list_with_path( $this->product->get_parent_id(), $taxonomy, $sep );
		} else {
			return $this->get_the_term_list_with_path( $this->product->get_id(), $taxonomy, $sep );
		}
	}


	/**
	 * Retrieve a product's categories as a list with specified format.
	 *
	 * @param string $sep Optional. Separate items using this.
	 * @return string
	 */
	protected function get_yoast_product_cats_with_seperator( $sep = ' > ' ) {
		$pr_id = $this->product->get_id();
		if ( $this->product->is_type( 'variation' ) ) {
			$pr_id = $this->product->get_parent_id();
		}
		$primary_cat_id = get_post_meta( $pr_id, '_yoast_wpseo_primary_product_cat', true );
		$term_name      = array();
		if ( $primary_cat_id ) {
			$product_cat = get_term( $primary_cat_id, 'product_cat' );
			if ( isset( $product_cat->name ) ) {
				$term_name[]   = $product_cat->name;
				$term_name_arr = $this->get_cat_names_array( $pr_id, 'product_cat', $primary_cat_id, $term_name );
				if ( is_array( $term_name_arr ) ) {
					return implode( $sep, $term_name_arr );
				}
				return $this->get_product_cats( 'product_cat', '', $sep, '' );
			}
		}
		return $this->get_product_cats( 'product_cat', '', $sep, '' );
	}


	/**
	 * Retrieve a product's sub categories as a list with specified format.
	 *
	 * @param string $sep Optional. Separate items using this.
	 * @return string
	 */
	protected function get_product_subcategory( $sep = ' > ' ) {
		if ( $this->product && !is_wp_error( $this->product ) ) {
			if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
				$product_id = $this->product->get_parent_id();
			} else {
				$product_id = $this->product->get_id();
			}

			$terms = get_the_terms( $product_id, 'product_cat' );

			if ( is_array( $terms ) && !empty( $terms ) ) {
				rsort( $terms );
				$terms = array_reverse( $terms );
			}

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				return '';
			}
			$term_names = array();
			foreach ( $terms as $term ) {
				$term_names[] = $term->name;
			}
			return join( $sep, $term_names );
		}
		return '';
	}

	/**
	 * Retrieve a product's tags as a list with specified format.
	 *
	 * @param string $sep Optional. Separate items using this.
	 * @param string $taxonomy Optional. Taxonomy.
	 * @return string|false
	 */
	protected function get_product_tags( $sep = ', ', $taxonomy = 'product_tag' ) {
		$product_id = 'WC_Product_Variation' === get_class( $this->product ) ? $this->product->get_parent_id() : $this->product->get_id();
		return $this->get_the_term_list( $product_id, $taxonomy, $sep );
	}


	/**
	 * Get yoast primary category
	 *
	 * @param string $seo_name SEO name.
	 * @param bool   $return_id If id should be retruned.
	 *
	 * @return false|mixed|string
	 */
	public function get_seo_primary_cat( string $seo_name, bool $return_id = false ) {
		if ( is_wp_error( $this->product ) && !$this->product ) {
			return '';
		}
		$pr_id = $this->product->get_id();
		if ( $this->product->is_type( 'variation' ) ) {
			$pr_id = $this->product->get_parent_id();
		}
		$meta_key = '';
		if ( 'yoast' === $seo_name ) {
			$meta_key = '_yoast_wpseo_primary_product_cat';
		} elseif ( 'rankmath' === $seo_name ) {
			$meta_key = 'rank_math_primary_product_cat';
		} 

		if ( !$meta_key && 'aioseo' !== $seo_name ) {
			return '';
		}

		if('aioseo' === $seo_name ){
			$primary_cat_id = $this->get_aioseo_primary_category_id( $pr_id );
		} else{
			$primary_cat_id = get_post_meta( $pr_id, $meta_key, true );
		}
		if ( $return_id ) {
			return $primary_cat_id;
		}

		if ( $primary_cat_id ) {
			$product_cat = get_term( $primary_cat_id, 'product_cat' );
			if ( isset( $product_cat->name ) ) {
				return $product_cat->name;
			}
		}
		return $this->get_product_cats( 'product_cat' );
	}


	/**
	 * Get product category for SooQR
	 *
	 * @return array
	 */
	public function get_product_cats_for_sooqr() {
		$categories = array();
		if ( 'WC_Product_Variation' === get_class( $this->product ) ) {
			$product_id = $this->product->get_parent_id();
		} else {
			$product_id = $this->product->get_id();
		}

		$term_list = wp_get_post_terms( $product_id, 'product_cat' );
		foreach ( $term_list as $term ) {
			if ( $term->parent ) {
				$categories[ 'subcategories' ][] = $term->name;
			} else {
				$categories[ 'categories' ][] = $term->name;
			}
		}
		return $categories;
	}

	/**
	 * Retrieve a product's terms as a list with specified format.
	 *
	 * @param int    $id Product ID.
	 * @param string $taxonomy Taxonomy name.
	 * @param string $sep Optional. Separate items using this.
	 * @param bool   $return_ids Optional. If ids should be returned.
	 *
	 * @return string
	 */
	protected function get_the_term_list( $id, $taxonomy, $sep = ', ', $return_ids = false ) {
		$terms = wp_get_post_terms( $id, $taxonomy, [ 'hide_empty' => false, 'orderby' => 'term_id' ] );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}
		if ( $return_ids ) {
            $term_ids = is_array( $terms ) && !empty( $terms ) ? array_column( $terms, 'term_id' ) : [];
			return implode( $sep, $term_ids );
		}

		$child_terms  = array();
		$parent_terms = array();

		foreach ( $terms as $term ) {
			if ( $term->parent ) {
				$child_terms = $this->get_cat_names_array( $id, $taxonomy, $term->parent, $parent_terms );
			} else {
				$parent_terms[] = $term->name;
			}
		}
		$output = array_merge( $parent_terms, $child_terms );

		return implode( ', ', $output );
	}


	/**
	 * Get terms with specified path
	 *
	 * @param string|int $id Product ID.
	 * @param string     $taxonomy Taxonomy.
	 * @param string     $sep Term separator.
	 *
	 * @return string
	 */
	protected function get_the_term_list_with_path( $id, $taxonomy, $sep = '' ) {
		wpfm_switch_site_lang( $this->feed->wpml_language, $this->feed->wcml_currency );
		$terms = wp_get_post_terms(
			$id,
			$taxonomy,
			array(
				'hide_empty' => false,
				'orderby'    => 'term_id',
			)
		);

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}

		$terms_id = array();
		foreach ( $terms as $term ) {
			$terms_id[] = $term->term_id;
		}

		$output = array();

		foreach ( $terms as $term ) {
			$term_names   = array();
			$term->name   = htmlspecialchars_decode( $term->name );
			$term_names[] = $term->name;

			$term_name_arr = $this->get_cat_names_array( $id, $taxonomy, $term->term_id, $term_names );

			if ( !empty( array_diff( $term_name_arr, $term_names ) ) ) {
				foreach ( $term_name_arr as $t_name ) {
					$temp     = array();
					$temp[]   = $term->name;
					$temp[]   = $t_name;
					$output[] = implode( $sep, $temp );
				}
			} elseif ( ( ( 0 === $term->parent ) || ( is_array( $terms_id ) && !in_array( $term->parent, $terms_id ) ) ) && ( is_array( $term_name_arr ) ) ) {
				$output[] = implode( $sep, $term_name_arr );
			}
		}
		return implode( ', ', $output );
	}

	/**
	 * Get category names' array
	 *
	 * @param string|int $id Product ID.
	 * @param string     $taxonomy Taxonomy.
	 * @param string|int $parent Taxonomy parent ID.
	 * @param array      $term_name_array Term names.
	 *
	 * @return array
	 */
	protected function get_cat_names_array( $id, $taxonomy, $parent, $term_name_array ) {
		wpfm_switch_site_lang( $this->feed->wpml_language, $this->feed->wcml_currency );
		$terms = wp_get_post_terms(
			$id,
			$taxonomy,
			array(
				'hide_empty' => false,
				'parent'     => $parent,
				'orderby'    => 'term_id',
			)
		);

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return $term_name_array;
		}
		$term_arr = array();
		foreach ( $terms as $term ) {
			$term_name_array   = array();
			$term_name_array[] = $term->name;
			$term_name_array   = $this->get_cat_names_array( $id, $taxonomy, $term->term_id, $term_name_array );
			$term_arr[]        = $term_name_array[ 0 ];
		}
		return $term_arr;
	}


	/**
	 * Get product default attributes
	 *
	 * @param object|WC_Product $product WC Product.
	 *
	 * @return mixed
	 */
	protected function get_default_attributes( $product ) {
		if ( method_exists( $product, 'get_default_attributes' ) ) {
			return $product->get_default_attributes();
		} else {
			return $product->get_variation_default_attributes();
		}
	}


	/**
	 * Get matching variation
	 *
	 * @param object|WC_Product $product WC Product.
	 * @param array             $attributes Attributes.
	 *
	 * @return int Matching variation ID or 0.
	 * @throws Exception Exception.
	 */
	protected function find_matching_product_variation( $product, $attributes ) {
		foreach ( $attributes as $key => $value ) {
			if ( 0 === strpos( $key, 'attribute_' ) ) {
				continue;
			}
			unset( $attributes[ $key ] );
			$attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
		}
		if ( class_exists( 'WC_Data_Store' ) ) {
			$data_store = WC_Data_Store::load( 'product' );
			return $data_store->find_matching_product_variation( $product, $attributes );
		} else {
			return $product->get_matching_variation( $attributes );
		}
	}


	/**
	 * Set additional images url.
	 *
	 * @since    1.0.0
	 */
	protected function set_additional_images() {
		$_product = $this->product;
		if ( $this->product->is_type( 'variation' ) ) {
			$_product = wc_get_product( $this->product->get_parent_id() );
		}

		$img_ids = $_product->get_gallery_image_ids();

		$images = array();
		if ( !empty( $img_ids ) ) {
			foreach ( $img_ids as $key => $img_id ) {
				$img_key            = 'image_' . ( $key + 1 );
				$images[ $img_key ] = wp_get_attachment_url( $img_id );
			}
			// set images to the property
			$this->additional_images = $images;
		}
	}

	/**
	 * Helper to check if a attribute is a Primary Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_primary_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Primary Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Primary Attributes' ] );
	}

	/**
	 * Helper to check if an attribute is a Woodmart Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_woodmart_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Woodmart Image Gallery' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Woodmart Image Gallery' ] );
	}

    /**
     * Helper to check if an attribute is a Divi Attribute.
     *
     * @param string $key Attribute key.
     *
     * @return bool
     * @since 7.2.32
     */
    protected function is_divi_attr( $key )
    {
        return !empty( $this->product_meta_keys[ 'Divi Builder' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Divi Builder' ] );
    }

	/**
	 * Helper to check if a attribute is a YOAST Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_yoast_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'YOAST Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'YOAST Attributes' ] );
	}

	/**
	 * Helper to check if a attribute is a RankMath Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_rankmath_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'RankMath Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'RankMath Attributes' ] );
	}

	/**
	 * Helper to check if a attribute is a YOAST Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_price_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Price Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Price Attributes' ] );
	}

	/**
	 * Helper to check if a attribute is a WooCommerce Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_wc_brand_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Woocommerce Brand' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Woocommerce Brand' ] );
	}

	/**
	 * Helper to check if a attribute is a Brands for WooCommerce by BeRocket Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_berocket_brand_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Brands for WooCommerce' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Brands for WooCommerce' ] );
	}

	/**
	 * Helper to check if a attribute is a Perfect Brand Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_perfect_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Perfect Brand' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Perfect Brand' ] );
	}

	/**
	 * Helper to check if a attribute is a Image Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	protected function is_image_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Image Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Image Attributes' ] );
	}

	/**
	 * Helper to check if a attribute is a Product Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	protected function is_product_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Product Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Product Attributes' ] );
	}

	/**
	 * Helper to check if a attribute is a Glami Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	protected function is_glami_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Glami Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Glami Attributes' ] );
	}

	/**
	 * Helper to check if a attribute is a Dropship Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	protected function is_dropship_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Dropship by Mantella' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Dropship by Mantella' ] );
	}

	/**
	 * Helper to check if a attribute is a WPFM Custom Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_wpfm_custom_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'WPFM Custom Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'WPFM Custom Attributes' ] );
	}


	/**
	 * Helper to check if a attribute is a Product dynamic Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_product_dynamic_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Product Variation Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Product Variation Attributes' ] );
	}


	/**
	 * Helper to check if a attribute is a Product Custom Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_product_custom_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Product Custom Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Product Custom Attributes' ] );
	}

	/**
	 * Helper to check if a attribute is a Category Mapper.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_product_category_mapper_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Category Map' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Category Map' ] );
	}

	/**
	 * Helper to check if a attribute is a Category Mapper.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_date_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Date Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Date Attributes' ] );
	}

	/**
	 * Helper to check if a attribute is a Product custom taxonomy
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since 7.2.9
	 */
	protected function is_product_custom_tax( $key ) {
		return !empty( $this->product_meta_keys[ 'Product Custom Taxonomies' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Product Custom Taxonomies' ] );
	}

	/**
	 * Helper to check if a attribute is a WooDiscount Rules Attribute
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since 7.2.9
	 */
	protected function is_woo_discount_rules( $key ) {
		return !empty( $this->product_meta_keys[ 'Woo Discount Rules' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Woo Discount Rules' ] );
	}


	/**
	 * Helper to check if given attribute is a Shipping Attributes
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since 7.2.9
	 */
	protected function is_shipping_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Shipping Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Shipping Attributes' ] );
	}


	/**
	 * Helper to check if given attribute is a Shipping Attributes
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since 7.2.9
	 */
	protected function is_tax_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Tax Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'Tax Attributes' ] );
	}


	/**
	 * Helper to check if given attribute
	 * is an EAN by WooCommerce Attributes
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since 7.2.19
	 */
	protected function is_ean_by_wc_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'EAN by WooCommerce' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'EAN by WooCommerce' ] );
	}

	/**
	 * Checks if a specific Advanced Custom Fields (ACF) attribute exists within the product meta keys.
	 *
	 * @param string $key The key of the ACF attribute to check.
	 *
	 * @return bool Returns true if the specified ACF attribute exists in the product meta keys; otherwise, false.
	 * @since 7.3.20
	 */
	protected function is_acf_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'ACF Attributes' ][ $key ] );
	}

	/**
	 * Checks if a key exists within the 'ACF Taxonomies' array.
	 *
	 * @param string $key The key to check for within the 'ACF Taxonomies' array.
	 *
	 * @return bool True if the key exists within 'ACF Taxonomies', false otherwise.
	 * @since 7.3.20
	 */
	protected function is_acf_taxonomies( $key ) {
		return !empty( $this->product_meta_keys[ 'ACF Taxonomies' ][ $key ] );
	}


	/**
	 * Helper to check if given attribute
	 * is a Discount Rules and Dynamic Pricing for WooCommerce Attributes
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since 7.2.20
	 */
	protected function is_discount_price_by_asana_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Discounted Price - by Asana Plugins' ][ $key ] );
	}

    /**
     * Checks if the given key corresponds to a dynamic discount attribute by RexTheme.
     *
     * @param string $key The key to check.
     * @return bool True if the key corresponds to a dynamic discount attribute, false otherwise.
     *
     * @since 7.4.1
     */
	protected function is_rex_dynamic_discount_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'Product Based Discounted Price - by WooCommerce Dynamic Discount [by RexTheme]' ][ $key ] );
	}


	/**
	 * Helper to get condition of a product.
	 *
	 * @return string
	 * @since    1.0.0
	 */
	protected function get_condition() {
		return 'New';
	}


	/**
	 * Helper to get parent product id of a product.
	 *
	 * @return string|int
	 */
	protected function get_item_group_id() {
		if ( !$this->product ) {
			return '';
		}
		if ( $this->product->is_type( 'variation' ) ) {
			return $this->product->get_parent_id();
		}
		return '';
	}


	/**
	 * Helper to get availability of a product
	 *
	 * @return string
	 * @since    1.0.0
	 */
	protected function get_availability() {
		if ( !$this->product ) {
			return '';
		}
		if ( $this->product->is_on_backorder() ) {
			return apply_filters( 'wpfm_product_availability_backorder', 'out_of_stock' );
		} elseif ( $this->product->is_in_stock() ) {
			return apply_filters( 'wpfm_product_availability', 'in_stock' );
		} else {
			return apply_filters( 'wpfm_product_availability', 'out_of_stock' );
		}
	}

	/**
	 * Helper to get availability underscore of a product
	 *
	 * @return string
	 * @since    1.0.0
	 */
	protected function get_availability_underscore() {
		if ( !$this->product ) {
			return '';
		}
		if ( $this->product->is_on_backorder() ) {
			return apply_filters( 'wpfm_product_availability_backorder', 'out of stock' );
		} elseif ( $this->product->is_in_stock() ) {
			return apply_filters( 'wpfm_product_availability', 'in stock' );
		} else {
			return apply_filters( 'wpfm_product_availability', 'out of stock' );
		}
	}

	/**
	 * Helper to get availability underscore of a product
	 *
	 * @return string
	 * @since    1.0.0
	 */
	protected function get_availability_backorder_instock() {
		if ( !$this->product ) {
			return '';
		}
		if ( $this->product->is_on_backorder() ) {
			return apply_filters( 'wpfm_product_availability_backorder', 'in_stock' );
		} elseif ( $this->product->is_in_stock() ) {
			return apply_filters( 'wpfm_product_availability', 'in_stock' );
		} else {
			return apply_filters( 'wpfm_product_availability', 'out_of_stock' );
		}
	}

	/**
	 * Helper to get availability underscore of a product
	 *
	 * @return string
	 * @since    1.0.0
	 */
	protected function get_availability_backorder() {
		if ( !$this->product ) {
			return '';
		}
		if ( $this->product->is_on_backorder() ) {
			return apply_filters( 'wpfm_product_availability_backorder', 'on_backorder' );
		} elseif ( $this->product->is_in_stock() ) {
			return apply_filters( 'wpfm_product_availability', 'in_stock' );
		} else {
			return apply_filters( 'wpfm_product_availability', 'out_of_stock' );
		}
	}


	/**
	 * Get stock status
	 *
	 * @return string
	 */
	protected function get_stock() {
		if ( !$this->product ) {
			return '';
		}
		if ( $this->product->is_in_stock() ) {
			return 'Y';
		} else {
			return 'N';
		}
	}

	/**
	 * Add necessary prefix/suffix to a value.
	 *
	 * @param string $val Attribute value.
	 * @param array  $rule Atribute rules.
	 *
	 * @return string
	 */
	protected function maybe_add_prefix_suffix( $val, $rule ) {
		$prefix = $rule[ 'prefix' ];
		$suffix = $rule[ 'suffix' ];

		if ( !empty( $prefix ) ) {
			$val = $val ? $prefix . $val : '';
		}

		if ( !empty( $suffix ) ) {
			$val = $val ? $val . $suffix : '';
		}

		return $val;
	}

	/**
	 * Escape a value with specific escape method.
	 *
	 * @param string $val Attribute value.
	 * @param string $escape Escaping rule.
	 *
	 * @return array|float|int|mixed|string|string[]\
	 */
	protected function maybe_escape( $val, $escape ) {
		switch ( $escape ) {
			case 'strip_tags':
				$val            = preg_replace( '/(?:<|&lt;).*?(?:>|&gt;)/', '', $val );
				$striped_string = wp_strip_all_tags( $val );
				return trim( $striped_string );
			case 'utf_8_encode':
				return iconv('ISO-8859-1', 'UTF-8', $val );
			case 'htmlentities':
				return htmlentities( $val );
			case 'integer':
			case 'price':
				return intval( $val );
			case 'remove_space':
				return trim( preg_replace( '/\s+/', '', $val ) );
			case 'remove_tab':
				return trim( preg_replace( '/\t+/', '', $val ) );
			case 'remove_shortcodes_and_tags':
				$val            = preg_replace( '/(?:<|&lt;).*?(?:>|&gt;)/', '', $val );
				$striped_string = wp_strip_all_tags( $val );
				if ( ' ' === substr( $striped_string, -1 ) ) {
					$striped_string = preg_replace( '#\[[^\]]+\]#', '', $striped_string );
					return rtrim( strip_shortcodes( $striped_string ) );
				}

				$striped_string = preg_replace( '#\[[^\]]+\]#', '', $striped_string );
				return strip_shortcodes( $striped_string );

			case 'remove_shortcodes':
				$val = preg_replace( '#\[[^\]]+\]#', '', $val );
				return strip_shortcodes( $val );
			case 'remove_special':
				return filter_var( $val, FILTER_SANITIZE_STRING ); //phpcs:ignore
			case 'cdata':
				return $val && '' !== $val ? "<![CDATA[{$val}]]>" : $val;
			case 'cdata_without_space':
				return $val ? "CDATA$val" : $val;
			case 'remove_underscore':
				return str_replace( '_', ' ', $val );
			case 'remove_decimal':
				if ( $this->check_if_float( $val ) ) {
					$val = number_format( $val, 2, '.', '' );
					for ( $i = 0; $i < 2; $i++ ) {
						$val = $val * 10;
					}
				} else {
					return intval( $val ) * 100;
				}
				return $val;
			case 'add_two_decimal':
				return number_format( (float) $val, 2 );
			case 'remove_hyphen':
				return str_replace( '-', '', $val );
			case 'remove_hyphen_space':
				return str_replace( '-', ' ', $val );
			case 'replace_space_with_hyphen':
				return str_replace( ' ', '-', $val );
			case 'first_word_uppercase':
				return ucfirst( strtolower( $val ) );
			case 'each_word_uppercase':
				return ucwords( strtolower( $val ) );
			case 'comma_decimal':
				if ( is_numeric( $val ) ) {
					return number_format( $val, 2, ',', '' );
				}
				return $val;
			case 'replace_comma_with_backslash':
				return str_replace( ',', '/', str_replace( ', ', '/', $val ) );
			case 'replace_decimal_with_hyphen':
				return str_replace( '.', '-', str_replace( '. ', '-', $val ) );

			default:
				return $val;
		}
	}

	/**
	 * Replace tab if exists
	 *
	 * @param string $val Attribute value.
	 *
	 * @return string
	 */
	protected function tab_replace( $val ) {
		if ( 'text' === $this->feed_format ) {
			return preg_replace( '/[ ]{2,}|[\t]|[\n]/', ' ', trim( $val ) );
		}
		return $val;
	}


	/**
	 * Check if float
	 *
	 * @param string|int|float|bool $num Number.
	 *
	 * @return bool
	 */
	private function check_if_float( $num ) {
		return is_float( $num ) || is_numeric( $num ) && ( (float) $num != (int) $num ); //phpcs:ignore
	}


	/**
	 * Limit the output chars to specified length.
	 *
	 * @param string $val Attribute value.
	 * @param string $limit Limit.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function maybe_limit( $val, $limit ) {
		$limit = (int) $limit;
		if ( $limit > 0 ) {
			return substr( $val, 0, $limit );
		}
		return $val;
	}


	/**
	 * Remove shortcode
	 * from content
	 *
	 * @param string $content Shortcode.
	 *
	 * @return string
	 * @since    2.0.3
	 */
	public function remove_short_codes( $content ) {
		if ( empty( $content ) ) {
			return '';
		}
		$content = $this->remove_invalid_xml( $content );
		return strip_shortcodes( $content );
	}

	/**
	 * Removes invalid XML
	 *
	 * @param string $value Attribute value.
	 *
	 * @return string
	 */
	public function remove_invalid_xml( $value ) {
		$ret = '';
		if ( empty( $value ) ) {
			return $ret;
		}

		$length = strlen( $value );
		for ( $i = 0; $i < $length; $i++ ) {
			$current = ord( $value[ $i ] );
			if (
				( 0x9 === $current ) ||
				( 0xA === $current ) ||
				( 0xD === $current ) ||
				( ( 0x20 <= $current ) && ( 0xD7FF >= $current ) ) ||
				( ( 0xE000 <= $current ) && ( 0xFFFD >= $current ) ) ||
				( ( 0x10000 <= $current ) && ( 0x10FFFF >= $current ) )
			) {
				$ret .= chr( $current );
			} else {
				$ret .= " ";
			}
		}
		return $ret;
	}


	/**
	 * Calculate the value of identifier_exists
	 *
	 * @param array $data Attribute data.
	 *
	 * @return string
	 * @since    1.2.5
	 */
	public function calculate_identifier_exists( $data ) {
		if (
			( !empty( $data[ 'brand' ] ) && ( !empty( $data[ 'gtin' ] ) || !empty( $data[ 'mpn' ] ) ) )
			|| ( !empty( $data[ 'gtin' ] ) || !empty( $data[ 'mpn' ] ) )
		) {
			return 'yes';
		}
		return 'no';
	}

	/**
	 * Add shipping class price/ no class price with base class
	 *
	 * @param array $shipping_methods WC Shipping Methods.
	 * @param array $rule Attribute rule.
	 *
	 * @return array
	 * @since 7.2.20
	 */
	private function add_class_no_class_cost( $shipping_methods = array(), $rule = array() ) {
		if ( !is_wp_error( $this->product ) && $this->product && !empty( $shipping_methods ) ) {
			$methods = count( $shipping_methods );
			for ( $index = 0; $index < $methods; $index++ ) {
				if ( isset( $shipping_methods[ $index ][ 'instance' ] ) && !is_wp_error( $shipping_methods[ $index ][ 'instance' ] ) && is_array( $shipping_methods[ $index ][ 'instance' ] ) && isset( $shipping_methods[ $index ][ 'price' ] ) ) {
					if ( !empty( $shipping_methods[ $index ][ 'instance' ] ) ) {
						$class_id = $this->product->get_shipping_class_id();
						if ( isset( $shipping_methods[ $index ][ 'instance' ][ 'class_cost_' . $class_id ] ) && $shipping_methods[ $index ][ 'instance' ][ 'class_cost_' . $class_id ] ) {
							$shipping_methods[ $index ][ 'price' ] += $shipping_methods[ $index ][ 'instance' ][ 'class_cost_' . $class_id ];
						} elseif ( isset( $shipping_methods[ $index ][ 'instance' ][ 'no_class_cost' ] ) && $shipping_methods[ $index ][ 'instance' ][ 'no_class_cost' ] ) {
							$shipping_methods[ $index ][ 'price' ] += $shipping_methods[ $index ][ 'instance' ][ 'no_class_cost' ];
						}
					}
					unset( $shipping_methods[ $index ][ 'instance' ] );
					if ( isset( $rule[ 'prefix' ] ) ) {
						$shipping_methods[ $index ][ 'price' ] = $rule[ 'prefix' ] . $shipping_methods[ $index ][ 'price' ];
					}
					if ( isset( $rule[ 'suffix' ] ) ) {
						$shipping_methods[ $index ][ 'price' ] = $shipping_methods[ $index ][ 'price' ] . $rule[ 'suffix' ];
					}
				}
			}
		}
		return $shipping_methods;
	}

	/**
	 * Check if this product is child product or not
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	protected function is_children() {
		return (bool) $this->product->get_parent_id();
	}

	/**
	 * Replace decode url
	 *
	 * @param string $string URL.
	 *
	 * @return string
	 */
	private function safe_char_encode_url( $string ) {
		return str_replace(
			array( '%', '[', ']', '{', '}', '|', ' ', '"', '<', '>', '#', '\\', '^', '~', '`' ),
			array( '%25', '%5b', '%5d', '%7b', '%7d', '%7c', '%20', '%22', '%3c', '%3e', '%23', '%5c', '%5e', '%7e', '%60' ),
			$string
		);
	}


	/**
	 * Process attribute value if needed
	 *
	 * @param string $value Attribute value.
	 * @param array  $rule Attribute rule.
	 *
	 * @return string
	 * @since 7.2.8
	 */
	protected function maybe_processing_needed( $value, $rule ) {
		if ( !is_array( $value ) ) {
			// maybe escape.
			$escape = !empty( $rule[ 'escape' ] ) ? $rule[ 'escape' ] : '';

			if ( is_array( $escape ) ) {
				foreach ( $escape as $esc ) {
					$value = $this->maybe_escape( $value, $esc );
				}
			} else {
				$value = $this->maybe_escape( $value, $escape );
			}

			// maybe add prefix/suffix.
			$value = $this->maybe_add_prefix_suffix( $value, $rule );
			// maybe limit.
			$value = $this->maybe_limit( $value, isset( $rule[ 'limit' ] ) ? $rule[ 'limit' ] : '' );

			$value = $this->tab_replace( $value );
		}
		return $value;
	}

    /**
     * Checks if WPML Multilingual CMS is active.
     *
     * @return bool True if WPML Multilingual CMS is active, false otherwise.
     *
     * @since 7.4.1
     */
    public function is_wcml_active() {
        return $this->wcml;
    }

    /**
     * Retrieves the currency used by WPML Multilingual CMS.
     *
     * @return string The currency used by WPML Multilingual CMS.
     *
     * @since 7.4.1
     */
    public function get_wcml_currency() {
        return $this->wcml_currency;
    }

    /**
     * Retrieves the currency used by WooCommerce Multilingual (WooCommerce Multicurrency).
     *
     * @return string The currency used by WooCommerce Multilingual.
     *
     * @since 7.4.1
     */
    public function get_wmc_currency() {
        return $this->wmc_currency;
    }

	/**
	 * Helper to check if a attribute is a AIOSEO Attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @return bool
	 * @since    7.4.10
	 */
	protected function is_aioseo_attr( $key ) {
		return !empty( $this->product_meta_keys[ 'AIO SEO Attributes' ] ) && array_key_exists( $key, $this->product_meta_keys[ 'AIO SEO Attributes' ] );
	}

	/**
	 * Set AIO SEO attribute.
	 *
	 * @param string $key Attribute key.
	 *
	 * @since    7.4.10
	 */
	protected function set_aioseo_attr( $key ) {
		$attr_val = '';
		switch ( $key ) {
			case 'aioseo_primary_cat':
				$attr_val = $this->get_seo_primary_cat( 'aioseo' );
				break;

			case 'aioseo_primary_cat_id':
				$attr_val = $this->get_seo_primary_cat( 'aioseo', true );
				break;
			default:
				return '';
		}
		return $attr_val;
	}

	/**
	 * Retrieves the primary category ID for a given product ID from the aioseo_posts table.
	 *
	 * This function queries the aioseo_posts table to find the primary category term
	 * associated with the specified product ID. It then decodes the JSON data to extract
	 * the primary category ID
	 * @param int $product_id The ID of the product for which to retrieve the primary category ID.
	 * @return mixed The primary category ID if found, or an empty string if not found.
	 *
	 * @since 7.4.10
	 */
	public function get_aioseo_primary_category_id( $product_id ){
		global $wpdb;
		$table_name = $wpdb->prefix . 'aioseo_posts';
		$sql = $wpdb->prepare( "SELECT primary_term FROM $table_name WHERE post_id = %d", $product_id );
		$primary_cat = $wpdb->get_var( $sql );
		if ( !empty( $primary_cat ) ) {
			$category_data = json_decode( $primary_cat, true );
			return is_array( $category_data ) && !empty( $category_data['product_cat'] ) ? $category_data['product_cat'] : '';
		} else {
			return '';
		}
	}
}