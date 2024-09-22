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

use RexTheme\RexShoppingFeedCustom\EbaySeller\Containers\RexShoppingCustom;

class Rex_Product_Feed_Ebay_seller_tickets extends Rex_Product_Feed_Abstract_Generator {

    /**
     * @var $ebay_cat_id
     */
    protected $ebay_cat_id;

    /**
     * @var $ebay_seller_config
     */
    protected $ebay_seller_config;


    /**
     * Create Feed
     *
     * @return boolean
     * @author
     **/
    public function make_feed() {

        $this->ebaySellerInit($this->config['feed_config']);

        // Generate feed for both simple and variable products.
        $this->generate_product_feed();
        $this->feed = $this->returnFinalProduct();
        $this->feed_format = 'csv';
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
     * @param $ebay_cat_id
     */
    public function ebaySellerInit($config){
	    $feed_config = array();
	    parse_str( $config, $feed_config );
	    $ebay_category            = isset( $feed_config[ 'rex_feed_ebay_seller_category' ] ) ? explode( " : ", (string) $feed_config[ 'rex_feed_ebay_seller_category' ] ) : '';
	    $this->ebay_seller_config = array(
		    'cat_id'   => isset( $feed_config[ 'rex_feed_ebay_seller_category' ] ) ?
			    ( explode( " : ", (string) $feed_config[ 'rex_feed_ebay_seller_category' ] ) ?
				    trim( end( $ebay_category ) ) : '' ) :
			    '',
		    'site_id'  => isset( $feed_config[ 'rex_feed_ebay_seller_site_id' ] ) ? (string) $feed_config[ 'rex_feed_ebay_seller_site_id' ] : '',
		    'country'  => isset( $feed_config[ 'rex_feed_ebay_seller_country' ] ) ? (string) $feed_config[ 'rex_feed_ebay_seller_country' ] : '',
		    'currency' => isset( $feed_config[ 'rex_feed_ebay_seller_currency' ] ) ? (string) $feed_config[ 'rex_feed_ebay_seller_currency' ] : '',
	    );
    }


    private function generate_product_feed(){
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
     */
    private function add_to_feed( $product, $meta_keys, $product_type = '' ) {
        $attributes = $this->get_product_data( $product, $meta_keys );

        if( ( $this->rex_feed_skip_product && empty( array_keys($attributes, '') ) ) || !$this->rex_feed_skip_product ) {
            $item = RexShoppingCustom::createItem();

            if (preg_match('#(\d+)$#', $this->ebay_cat_id, $matches)) {
                $attributes = array_slice($attributes, 0, 1, true) +
                        array("Category" => $matches[1]) +
                        array_slice($attributes, 1, count($attributes) - 1, true) ;
            }
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
     * Get Product data.
     * @param bool $id
     *
     * @return array
     */
    protected function get_product_data( WC_Product $product, $product_meta_keys ){
        $data = new Rex_Product_Ebay_Seller_Data_Retriever( $product, $this, $product_meta_keys, $this->ebay_seller_config );
        return $data->get_all_data();


        $include_analytics_params = get_post_meta($this->id, '_rex_feed_analytics_params_options', true);

        if($include_analytics_params == 'on') {
            $analytics_params = get_post_meta($this->id, '_rex_feed_analytics_params', true);
        }else {
            $analytics_params = null;
        }

        if ( function_exists('icl_object_id') ) {
            global $sitepress;
            $wpml = get_post_meta($this->id, '_rex_feed_wpml_language', true);
            if($wpml) {
                $sitepress->switch_lang($wpml);
                $data = new Rex_Product_Ebay_Seller_Data_Retriever( $product, $this->feed_config, $product_meta_keys, $this->ebay_seller_config, $analytics_params, $this->append_variation, 'ebay_seller_tickets');
            }
        }else{

            $data = new Rex_Product_Ebay_Seller_Data_Retriever( $product, $this->feed_config, $product_meta_keys, $analytics_params, $this->ebay_seller_config, $this->append_variation, 'ebay_seller_tickets');
        }
        return $data->get_all_data();
    }



    /**
     * Return Feed
     * @return array|bool|string
     */
    public function returnFinalProduct(){
        return RexShoppingCustom::asCsv();
    }
    //replace footer of feed
    public function footer_replace() {
        $this->feed = str_replace('</products>', '', $this->feed);
    }

}
