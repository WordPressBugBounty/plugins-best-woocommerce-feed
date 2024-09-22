<?php

/**
 * The file that generates xml feed for any merchant with custom configuration.
 *
 * A class definition that includes functions used for generating xml feed.
 *
 * @link       https://rextheme.com
 * @since      7.2.12
 * @author     RexTheme <info@rextheme.com>
 */

use RexTheme\RexHeurekaAvailability\Containers\RexHeurekaAvailability;

class Rex_Product_Feed_Heureka_availability extends Rex_Product_Feed_Abstract_Generator {

    /**
     * @desc Create Feed
     * @since 7.2.12
     * @return bool|string|string[]
     * @throws Exception
     */
    public function make_feed() {
        RexHeurekaAvailability::init( false, 'item', null, '', 'item_list' );
        // Generate feed for both simple and variable products.
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

    /**
     * @desc Generate feed
     * @since 7.2.12
     * @return void
     * @throws Exception
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
    }


    /**
     * @desc Adding items to feed
     * @since 7.2.12
     * @param $product
     * @param $meta_keys
     * @param string $product_type
     */
    private function add_to_feed( $product, $meta_keys, $product_type = '' )
    {
        $attributes = $this->get_product_data( $product, $meta_keys );

        if( is_array( $attributes ) && isset( $attributes[ 'stock_quantity' ] ) && 0 < $attributes[ 'stock_quantity' ] ) {
            $attributes = $this->process_depot_attributes( $attributes );

            if( ( is_array( $attributes ) && !empty( $attributes ) && $this->rex_feed_skip_product && empty( array_keys( $attributes, '' ) ) ) || !$this->rex_feed_skip_product ) {
                $item = RexHeurekaAvailability::createItem();
                $item->id( $product->get_id() );

                foreach( $attributes as $key => $value ) {
                    if( $this->rex_feed_skip_row && $this->feed_format === 'xml' ) {
                        if( $value != '' ) {
                            $item->$key( $value ); // invoke $key as method of $item object.
                        }
                    }
                    else {
                        $item->$key( $value ); // invoke $key as method of $item object.
                    }
                }
            }
        }
    }


    /**
     * @desc process depot attribute parameters
     * @since 7.2.17
     * @param $attributes
     * @return mixed
     */
    private function process_depot_attributes( $attributes ) {
        for( $index = 1; $index <= 5; $index++ ) {
            if( isset( $attributes[ 'depot_id_' . $index ] ) ) {
                $attributes[ 'depot_' . $index ][ 'id' ] = $attributes[ 'depot_id_' . $index ];
                unset( $attributes[ 'depot_id_' . $index ] );
            }
            if( isset( $attributes[ 'stock_quantity_' . $index ] ) ) {
                $attributes[ 'depot_' . $index ][ 'stock_quantity' ] = $attributes[ 'stock_quantity_' . $index ];
                unset( $attributes[ 'stock_quantity_' . $index ] );
            }
            if( isset( $attributes[ 'orderDeadline_' . $index ] ) ) {
                $attributes[ 'depot_' . $index ][ 'orderDeadline' ] = $attributes[ 'orderDeadline_' . $index ];
                unset( $attributes[ 'orderDeadline_' . $index ] );
            }
        }
        return $attributes;
    }


    /**
     * @desc Return Feed
     * @since 7.2.12
     * @return array|bool|string
     */
    public function returnFinalProduct(){

        if ($this->feed_format === 'xml') {
            return RexHeurekaAvailability::asRss();
        }
        return RexHeurekaAvailability::asRss();
    }

    /**
     * @desc Replace Footer for xml feed
     * @since 7.2.12
     * @return void
     */
    public function footer_replace()
    {
        $this->feed = str_replace( '</item_list>', '', $this->feed );
    }
}