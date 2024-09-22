<?php
/**
 * The file that generates xml feed for any merchant with custom configuration.
 *
 * A class definition that includes functions used for generating xml feed.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed_Rakuten
 * @subpackage Rex_Product_Feed_Rakuten/includes
 * @author     RexTheme <info@rextheme.com>
 */ 
use RexTheme\RexShoppingRakuten\Containers\RexShopping;

class Rex_Product_Feed_Rakuten extends Rex_Product_Feed_Abstract_Generator
{
    /**
     * Create Feed
     *
     * @return boolean
     * @author
     **/
    public function make_feed()
    {
        RexShopping::$container = null;
        RexShopping::init(false, $this->setItemWrapper(), '', '', $this->setItemsWrapper());

        $this->generate_product_feed();

        $this->feed = $this->returnFinalProduct();

        if ($this->batch >= $this->tbatch) {

            $this->save_feed($this->feed_format);
            return array(
                'msg' => 'finish'
            );
        } else {
            return $this->save_feed($this->feed_format);
        }
    }

    
    private function generate_product_feed()
    {
        $product_meta_keys = Rex_Feed_Attributes::get_attributes();
        $simple_products = [];
        $variation_products = [];
        $variable_parent = [];
        $group_products = [];
        $total_products = get_post_meta($this->id, '_rex_feed_total_products', true);
        $total_products = $total_products ?: get_post_meta($this->id, 'rex_feed_total_products', true);
        $total_products = $total_products?: array(
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
            'total' => (int)$total_products['total'] + (int)count($simple_products) + (int)count($variation_products) + (int)count($group_products) + (int)count($variable_parent),
            'simple' => (int)$total_products['simple'] + (int)count($simple_products),
            'variable' => (int)$total_products['variable'] + (int)count($variation_products),
            'variable_parent' => (int)$total_products['variable_parent'] + (int)count($variable_parent),
            'group' => (int)$total_products['group'] + (int)count($group_products),
        );

        update_post_meta($this->id, '_rex_feed_total_products', $total_products);
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
        $attributes = $this->process_attributes_for_delivery( $attributes );
        $attributes = $this->process_attributes_for_param( $attributes );

        if( ( $this->rex_feed_skip_product && empty( array_keys($attributes, '') ) ) || !$this->rex_feed_skip_product ) {
            $item = RexShopping::createItem();

            foreach ($attributes as $key => $value) {
                if ($key == 'delivery') {
                    $item->$key($value['DELIVERY_ID'], $value['DELIVERY_PRICE'], $value['DELIVERY_PRICE_COD']); // invoke $key as method of $item object.
                } elseif ($key === 'param') {
                    $item->$key($key, $value);
                } else {
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
    }

    /**
     * Check if the merchants is valid or not
     * @param $feed_merchants
     * @return bool
     */
    public function is_valid_merchant()
    {
        return true;
    }


    /**
     * @return string
     */
    public function setItemWrapper()
    {
        return 'SHOPITEM';
    }

    public function setItemsWrapper()
    {
        return 'SHOP';
    }


    /**
     * @param $atts
     * @return array
     */
    private function process_attributes_for_delivery($atts)
    {
        $shipping_attr = array('DELIVERY_ID', 'DELIVERY_PRICE', 'DELIVERY_PRICE_COD');
        $default_delivery_atts = array(
            'DELIVERY_ID' => '',
            'DELIVERY_PRICE' => '',
            'DELIVERY_PRICE_COD' => ''
        );

        foreach ($atts as $key => $value) {
            if (in_array($key, $shipping_attr)) {
                $atts['delivery'][$key] = $value;
                unset($atts[$key]);
            }
        }
        if (array_key_exists('delivery', $atts)) {
            $atts['delivery'] += $default_delivery_atts;
        }
        return $atts;
    }


    /**
     * process atts for param attribute
     *
     * @param $atts
     * @return mixed
     *
     * @since 6.3.2
     */
    private function process_attributes_for_param($atts) {
        foreach ($atts as $key => $value) {
            if(preg_match('/^PARAM/im', $key)) {
                $param_no = preg_replace('/[^0-9]/', '', $key);
                $atts['param'][] = array(
                    'key'           => $key,
                    'name'          => $value,
                    'value'         => isset($atts['VALUE_'.$param_no]) ? $atts['VALUE_'.$param_no] : '',
                    'percentage'    => isset($atts['PERCENTAGE_'.$param_no]) ? $atts['PERCENTAGE_'.$param_no] : '',
                );
            }
        }
        foreach ($atts as $key => $value) {
            if(preg_match('/^PARAM/im', $key)) {
                $param_no = preg_replace('/[^0-9]/', '', $key);
                unset($atts['VALUE_' . $param_no]);
                unset($atts['PERCENTAGE_' . $param_no]);
                unset($atts['PARAM_NAME_' . $param_no]);
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
            return RexShopping::asRss();
        } elseif ($this->feed_format === 'text' || $this->feed_format === 'tsv') {
            return RexShopping::asTxt();
        } elseif ($this->feed_format === 'csv') {
            return RexShopping::asCsv();
        }
        return RexShopping::asRss();
    }

    //replace footer of feed
    public function footer_replace()
    {
        $this->feed = str_replace('</SHOP>', '', $this->feed);
    }
}
