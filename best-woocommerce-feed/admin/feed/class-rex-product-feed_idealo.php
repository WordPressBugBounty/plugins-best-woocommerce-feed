<?php

/**
 * The file that generates xml feed for any merchant with custom configuration.
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

use RexTheme\RexShoppingFeedCustom\Idealo_de\Containers\Idealo_de;

class Rex_Product_Feed_Idealo extends Rex_Product_Feed_Abstract_Generator {

	/**
	 * Create Feed
	 *
	 * @return string|string[]
	 * @since 7.3.17
	 **/
	public function make_feed() {
		Idealo_de::$container = null;

		// Generate feed for both simple and variable products.
		$this->generate_product_feed();
		$this->feed = $this->returnFinalProduct();

		if ( $this->batch >= $this->tbatch ) {
			$this->save_feed( $this->feed_format );
			return [ 'msg' => 'finish' ];
		}
		else {
			return $this->save_feed( $this->feed_format );
		}
	}

	/**
	 * Generate feed data
	 *
	 * @return void
	 * @throws Exception
	 * @since 7.3.15
	 */
	protected function generate_product_feed() {
		$product_meta_keys  = Rex_Feed_Attributes::get_attributes();
		$total_products     = get_post_meta( $this->id, '_rex_feed_total_products', true );
		$total_products     = $total_products ?: get_post_meta( $this->id, 'rex_feed_total_products', true );
		$simple_products    = [];
		$variation_products = [];
		$variable_parent    = [];
		$group_products     = [];
		$total_products     = $total_products ?: array(
			'total'           => 0,
			'simple'          => 0,
			'variable'        => 0,
			'variable_parent' => 0,
			'group'           => 0,
		);

		if ( 1 == $this->batch ) {
			$total_products = array(
				'total'           => 0,
				'simple'          => 0,
				'variable'        => 0,
				'variable_parent' => 0,
				'group'           => 0,
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
			'total'           => (int)$total_products[ 'total' ] + count( $simple_products ) + count( $variation_products ) + count( $group_products ) + count( $variable_parent ),
			'simple'          => (int)$total_products[ 'simple' ] + count( $simple_products ),
			'variable'        => (int)$total_products[ 'variable' ] + count( $variation_products ),
			'variable_parent' => (int)$total_products[ 'variable_parent' ] + count( $variable_parent ),
			'group'           => (int)$total_products[ 'group' ] + count( $group_products ),
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
	 * @since 7.3.17
	 */
	private function add_to_feed( $product, $meta_keys ) {
		$attributes = $this->get_product_data( $product, $meta_keys );

		if ( ( $this->rex_feed_skip_product && is_array( $attributes ) && !empty( $attributes ) && empty( array_keys( $attributes, '' ) ) ) || !$this->rex_feed_skip_product ) {
			$item = Idealo_de::createItem();

			foreach( $attributes as $key => $value ) {
				$item->$key( $value ); // invoke $key as method of $item object.
			}
		}
	}

	/**
	 * Return Feed
	 *
	 * @return array|bool|string
	 * @since 7.3.17
	 */
	public function returnFinalProduct(){
		return Idealo_de::asCSVFeeds();
	}

	/**
	 * This method serves as a placeholder for replacing the footer content.
	 * Subclasses should extend this class and provide their own implementation
	 * to customize or replace the footer content as needed.
	 *
	 * @return void
	 * @since 7.3.17
	 */
	public function footer_replace() {}
}