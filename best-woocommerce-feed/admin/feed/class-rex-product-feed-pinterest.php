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

use Rex\Pinterest\Containers\Pinterest;

class Rex_Product_Feed_Pinterest extends Rex_Product_Feed_Abstract_Generator {

    /**
     * Create Feed for Google
     *
     * @return boolean
     * @author
     **/
    public function make_feed() {

        //putting data in xml file
        Pinterest::$container = null;
        Pinterest::title($this->title);
        Pinterest::link($this->link);
        Pinterest::description($this->desc);

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
            'total' => (int) $total_products['total'] + count($simple_products) + count($variation_products) + count($group_products) + count($variable_parent),
            'simple' => (int) $total_products['simple'] + count($simple_products),
            'variable' => (int) $total_products['variable'] + count($variation_products),
            'variable_parent' => (int) $total_products['variable_parent'] + count($variable_parent),
            'group' => (int) $total_products['group'] + count($group_products),
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
        $attributes = $this->process_attributes_for_shipping_tax( $attributes );

        if( ( $this->rex_feed_skip_product && empty( array_keys($attributes, '') ) ) || !$this->rex_feed_skip_product ) {
            $item = Pinterest::createItem();

            if ( $product_type === 'variation' ) {
                $check_item_group_id = 0;
            }

            foreach ($attributes as $key => $value) {
                if($key == 'shipping') {
                    $item->$key($value['shipping_country'], $value['shipping_service'], $value['shipping_price'], $value['shipping_region']); // invoke $key as method of $item object.
                }
                elseif ($key == 'tax') {
                    $item->$key($value['tax_country'], $value['tax_ship'], $value['tax_rate'], $value['tax_region']); // invoke $key as method of $item object.
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
            }

            if( $product_type === 'variation' && $check_item_group_id === 0){
                $item->item_group_id($product->get_parent_id());
            }
        }
    }


    /**
     * @param $atts
     * @return array
     */
    private function process_attributes_for_shipping_tax($atts) {
        $shipping_attr = array('shipping_country', 'shipping_region', 'shipping_service', 'shipping_price');
        $default_shipping_values = array(
            'shipping_country' => '',
            'shipping_service' => '',
            'shipping_price' => '',
            'shipping_region' => '',
        );

        $tax_attr = array('tax_country', 'tax_region', 'tax_rate', 'tax_ship');
        $default_tax_values = array(
            'tax_country' => '',
            'tax_ship' => '',
            'tax_rate' => '',
            'tax_region' => '',
        );

        foreach ($atts as $key => $value) {
            if(in_array($key, $shipping_attr)) {
                $atts['shipping'][$key] = $value;
                unset($atts[$key]);
            }

            if(in_array($key, $tax_attr)) {
                $atts['tax'][$key] = $value;
                unset($atts[$key]);
            }
        }
        return $atts;
    }


    /**
     * Return Feed
     *
     * @return array|bool|string
     */
    public function returnFinalProduct()
    {
        if ($this->feed_format === 'xml') {
            return Pinterest::asRss();
        } elseif ($this->feed_format === 'text' || $this->feed_format === 'tsv') {
            return Pinterest::asTxt();
        } elseif ($this->feed_format === 'csv') {
            return Pinterest::asCsv();
        }
        return Pinterest::asRss();
    }

    public function footer_replace() {
        $this->feed = str_replace('</channel></rss>', '', $this->feed);

    }

}