<?php

/**
 * The file that generates xml feed for any merchant with custom configuration.
 *
 * A class definition that includes functions used for generating xml feed.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed_Ceneo
 * @subpackage Rex_Product_Feed_Ceneo/includes
 * @author     RexTheme <info@rextheme.com>
 */

use RexTheme\RexShoppingCeneo\Containers\RexShoppingCeneo;

class Rex_Product_Feed_Ceneo extends Rex_Product_Feed_Abstract_Generator {

    private $feed_merchants = array(
        "nextag" => array(
            'item_wrapper'  => 'product',
            'items_wrapper' => 'products',
        ),
    );

    /**
     * Create Feed
     *
     * @return boolean
     * @author
     **/
    public function make_feed() {
        RexShoppingCeneo::$container = null;
        RexShoppingCeneo::init(false, $this->setItemWrapper(), null, '1', $this->setItemsWrapper());
        RexShoppingCeneo::title($this->title);
        RexShoppingCeneo::link($this->link);
        RexShoppingCeneo::description($this->desc);

        $this->generate_product_feed();

        $this->feed = $this->returnFinalProduct();

        if ($this->batch >= $this->tbatch ) {
            $this->save_feed($this->feed_format);
            return array(
                'msg' => 'finish'
            );
        }else {
            return $this->save_feed($this->feed_format);
        }
    }

    private function generate_product_feed(){

        //get feed rules filter if value
        $feed_if_val = '';
        if( $this->feed_config_filter ) {
            foreach ($this->feed_config_filter as $key => $value) {
                foreach ($value as $sub_key => $sub_val) {
                    if($sub_key === 'if')
                    {
                        $feed_if_val = $sub_val;
                    }
                    break;
                }
            }
        }

        $total_products = get_post_meta($this->id, '_rex_feed_total_products', true);
        $total_products = $total_products ?: get_post_meta($this->id, 'rex_feed_total_products', true);
        $product_meta_keys = Rex_Feed_Attributes::get_attributes();
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

            if ( ( !$this->include_out_of_stock )
                && ( !$product->is_in_stock()
                    || $product->is_on_backorder()
                    || (is_integer($product->get_stock_quantity()) && 0 >= $product->get_stock_quantity())
                )
            ) {
                continue;
            }

            if( !$this->include_zero_priced ) {
                $product_price = rex_feed_get_product_price($product);
                if( 0 == $product_price || '' == $product_price ) {
                    continue;
                }
            }


            if ( $product->is_type( 'variable' ) && $product->has_child() ) {

                if($this->variable_product) {
                    $variable_parent[] = $productId;
                    $variable_product = new WC_Product_Variable($productId);
                    $this->add_to_feed( $variable_product, $product_meta_keys );
                }

                if($feed_if_val!== 'id' || $feed_if_val!== 'price' || $feed_if_val!== 'sale_price'){
                    if($this->product_scope === 'product_cat' || $this->product_scope === 'product_tag' || $this->custom_filter_var_exclude || $this->custom_filter_option) {
                        if ( $this->exclude_hidden_products ) {
                            $variations = $product->get_visible_children();
                        }
                        else {
                            $variations = $product->get_children();
                        }
                        if( $variations ) {
                            foreach ($variations as $variation) {
                                if($this->variations) {
                                    $variation_products[] = $variation;
                                    $variation_product = wc_get_product( $variation );
                                    if ( ( !$this->include_out_of_stock )
                                        && ( !$variation_product->is_in_stock()
                                            || $variation_product->is_on_backorder()
                                            || (is_integer($variation_product->get_stock_quantity()) && 0 >= $variation_product->get_stock_quantity())
                                        )
                                    ) {
                                        continue;
                                    }
                                    $this->add_to_feed( $variation_product, $product_meta_keys, 'variation' );
                                }
                            }
                        }
                    }
                }else{
                    if( $this->product_scope === 'product_cat' || $this->product_scope === 'product_tag' || $this->custom_filter_var_exclude ) {
                        if ( $this->exclude_hidden_products ) {
                            $variations = $product->get_visible_children();
                        }else {
                            $variations = $product->get_children();
                        }
                        if( $variations ) {
                            foreach ($variations as $variation) {
                                if($this->variations) {
                                    $variation_products[] = $variation;
                                    $variation_product = wc_get_product( $variation );
                                    if ( ( !$this->include_out_of_stock )
                                        && ( !$variation_product->is_in_stock()
                                            || $variation_product->is_on_backorder()
                                            || (is_integer($variation_product->get_stock_quantity()) && 0 >= $variation_product->get_stock_quantity())
                                        )
                                    ) {
                                        continue;
                                    }
                                    $this->add_to_feed( $variation_product, $product_meta_keys, 'variation' );
                                }
                            }
                        }
                    }
                }
            }

            if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) || $product->is_type( 'composite' ) || $product->is_type( 'bundle' ) || $product->is_type('yith_bundle') || $product->is_type('yith-composite')) {
                $simple_products[] = $productId;
                $this->add_to_feed( $product, $product_meta_keys );
            }

            if($feed_if_val!== 'id' || $feed_if_val!== 'price'||$feed_if_val!== 'sale_price'){
                if( $this->product_scope === 'all' || $this->product_scope === 'product_filter' || $this->custom_filter_option ) {
                    if ( $product->get_type() === 'variation' ) {
                        $variation_products[] = $productId;
                        $this->add_to_feed( $product, $product_meta_keys, 'variation' );
                    }
                }
            }


            if( $product->is_type( 'grouped' ) && $this->parent_product ){
                $group_products[] = $productId;
                $this->add_to_feed( $product, $product_meta_keys );
            }
            if( $product->is_type( 'woosb' ) ){
                $group_products[] = $productId;
                $this->add_to_feed( $product, $product_meta_keys );
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
     */
    private function add_to_feed( $product, $meta_keys, $product_type = '' ) {
        $attributes = $this->get_product_data( $product, $meta_keys );

        if( ( $this->rex_feed_skip_product && empty( array_keys($attributes, '') ) ) || !$this->rex_feed_skip_product ) {
            $item = RexShoppingCeneo::createItem();
            // add all attributes for each product.
            foreach ($attributes as $key => $value) {
                if ( $this->rex_feed_skip_row && $this->feed_format === 'xml' ) {
                    if ( $value != '' ) {
                        $item->$key($value); // invoke $key as method of $item object.
                    }
                }
                else {
                    $item->$key($value); // invoke $key as method of $item object.
                }
            }
        }
    }


    /**
     * Check if the merchants is valid or not
     * @param $feed_merchants
     * @return bool
     */
    public function is_valid_merchant(){
        return array_key_exists($this->merchant, $this->feed_merchants)? true : false;
    }


    /**
     * @return string
     */
    public function setItemWrapper()
    {
        return 'o';
    }

    public function setItemsWrapper()
    {
        return 'offers';
    }

    /**
     * Return Feed
     *
     * @return array|bool|string
     */
    public function returnFinalProduct()
    {
        if ($this->feed_format === 'xml') {
            return RexShoppingCeneo::asRss();
        } elseif ($this->feed_format === 'text' || $this->feed_format === 'tsv') {
            return RexShoppingCeneo::asTxt();
        } elseif ($this->feed_format === 'csv') {
            return RexShoppingCeneo::asRss();
        }
        return false;
    }

    public function footer_replace() {
        $this->feed = str_replace('</offers>', '', $this->feed);
    }

}
