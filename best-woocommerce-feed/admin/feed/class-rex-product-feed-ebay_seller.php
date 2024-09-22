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

class Rex_Product_Feed_Ebay_seller extends Rex_Product_Feed_Abstract_Generator {

    /**
     * @var $ebay_cat_id
     */
    protected $ebay_cat_id;

    /**
     * @var $ebay_seller_config
     */
    protected $ebay_seller_config;

    /**
     * Prepare the Products Query args for retrieving  products.
     * @param $args
     */
    protected function prepare_products_args($args) {
        $this->product_scope = $args['products_scope'];
        $post_types = array(
            'product'
        );

        if($this->custom_filter_option) {
            foreach ($this->feed_config_filter as $filter) {
                $if = $filter['if'];
                if($if == 'product_cats') {
                    unset($post_types[1]);
                }
                if($if == 'product_tags') {
                    unset($post_types[1]);
                }
            }
        }

        $this->products_args = array(
            'post_type'              => $post_types,
            'fields'                 => 'ids',
            'post_status'            => 'publish',
            'posts_per_page'         => $this->posts_per_page,
            'offset'                 => $this->offset,
            'orderby'                => 'ID',
            'order'                  => 'ASC',
            'update_post_term_cache' => true,
            'update_post_meta_cache' => true,
            'cache_results'          => false,
            'suppress_filters'       => false,
        );

        if ( $args['products_scope'] === 'product_cat' || $args['products_scope'] === 'product_tag') {
            $this->products_args['post_type'] = 'product';
            $terms = $args['products_scope'] === 'product_tag' ? 'tags' : 'cats';
            if(is_array($args[$terms])) {
                foreach ($args[$terms] as $term) {
                    $this->products_args['tax_query'][] = array(
                        'taxonomy' => $args['products_scope'],
                        'field'    => 'slug',
                        'terms'    => $term,
                    );
                }
                $this->products_args['tax_query']['relation'] = 'OR';

                if($this->batch == 1) {
                    wp_set_object_terms($this->id, $args[$terms], $args['products_scope']);
                }
            }
        }
    }


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
     * Initialize eBay seller config variables
     *
     * @param $ebay_cat_id
     */
    public function ebaySellerInit($config){
        $feed_config = array();

        if ( !is_array( $config ) ) {
            parse_str( $config, $feed_config );
        }

        $this->ebay_seller_config = array(
            'site_id'  => isset( $feed_config[ 'rex_feed_ebay_seller_site_id' ] ) ? (string) $feed_config[ 'rex_feed_ebay_seller_site_id' ] : '',
            'country'  => isset( $feed_config[ 'rex_feed_ebay_seller_country' ] ) ? (string) $feed_config[ 'rex_feed_ebay_seller_country' ] : '',
            'currency' => isset( $feed_config[ 'rex_feed_ebay_seller_currency' ] ) ? (string) $feed_config[ 'rex_feed_ebay_seller_currency' ] : '',
        );
    }


    /**
     * generate feed based on the
     * feed config
     *
     */
    private function generate_product_feed(){
        $product_meta_keys = Rex_Feed_Attributes::get_attributes();
        $simple_products = [];
        $variation_products = [];
        $variable_parent = [];
        $group_products = [];
        $total_products = get_post_meta( $this->id, '_rex_feed_total_products', true );
        $total_products = $total_products ?: get_post_meta( $this->id, 'rex_feed_total_products', true );
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
                $variable_parent[] = $productId;
                $variable_product = new WC_Product_Variable($productId);
                $attributes = $this->get_product_data( $variable_product, $product_meta_keys );
                
                if( ( $this->rex_feed_skip_product && empty( array_keys($attributes, '') ) ) || !$this->rex_feed_skip_product ) {
                    if (preg_match('#(\d+)$#', $this->ebay_cat_id, $matches)) {
                        $attributes = array_slice($attributes, 0, 1, true) +
                                      array("Category" => $matches[1]) +
                                      array_slice($attributes, 1, count($attributes) - 1, true) ;
                    }

                    // get the variation attributes of
                    // this product
                    $relationshipDetails = '';
                    $_variation_atts = $variable_product->get_variation_attributes();
                    end($_variation_atts);
                    $_key = key($_variation_atts);

                    foreach( $_variation_atts as $attr_name => $attr ){
                        $relationshipDetails .= wc_attribute_label( $attr_name ).'='.implode(';', $attr);
                        if($attr_name !== $_key) $relationshipDetails .= '|';
                    }

                    $attributes['Relationship'] = '';
                    $attributes['RelationshipDetails'] = $relationshipDetails;

                    $item = RexShoppingCustom::createItem();

                    // add all attributes for each product.
                    $variable_common_fields = array(
                        '*Quantity',
                        '*StartPrice',
                        'BuyItNowPrice',
                        '*ConditionID',
                        'ReturnsAcceptedOption',
                        'RefundOption',
                        'ReturnsWithinOption',
                    );

                    foreach ($attributes as $key => $value) {
                        if ( $this->rex_feed_skip_row && $this->feed_format === 'xml' ) {
                            if ( $value != '' ) {
                                $item->$key( $value ); // invoke $key as method of $item object.
                            }
                        }
                        else {
                            if(in_array( $key, $variable_common_fields )) {
                                $item->$key(''); // invoke $key as method of $item object.
                            }
                            else {
                                $item->$key( $value ); // invoke $key as method of $item object.
                            }
                        }
                    }
                }


                if ( $this->exclude_hidden_products ) {
                    $variations = $product->get_visible_children();
                }else {
                    $variations = $product->get_children();
                }

                if($variations) {
                    foreach ($variations as $variation) {
                        if($this->variations) {
                            $variation_products[] = $variation;
                            $variation_product = wc_get_product( $variation );

                            $attributes = $this->get_product_data( $variation_product, $product_meta_keys );

                            if( ( $this->rex_feed_skip_product && empty( array_keys($attributes, '') ) ) || !$this->rex_feed_skip_product ) {
                                // get the variation attributes of
                                // this product
                                $vrelationshipDetails = '';
                                $_variation_atts = $variation_product->get_attributes();
                                end($_variation_atts);
                                $_key = key($_variation_atts);

                                $item = RexShoppingCustom::createItem();

                                foreach( $_variation_atts as $attr_name => $attr ){
                                    $vrelationshipDetails .= wc_attribute_label( $attr_name ).'='.$attr;
                                    if($attr_name !== $_key) $vrelationshipDetails .= '|';
                                }

                                $attributes['Relationship'] = 'Variation';
                                $attributes['RelationshipDetails'] = $vrelationshipDetails;

                                foreach ($attributes as $key => $value) {
                                    if ( $this->rex_feed_skip_row && $this->feed_format === 'xml' ) {
                                        if ( $value != '' ) {
                                            $item->$key( $value ); // invoke $key as method of $item object.
                                        }
                                    }
                                    else {
                                        if(in_array( $key, $variable_common_fields )) {
                                            $item->$key(''); // invoke $key as method of $item object.
                                        }
                                        else {
                                            $item->$key( $value ); // invoke $key as method of $item object.
                                        }
                                    }
                                }
                            }
                        }
                    }

                }
            }
            if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) || $product->is_type( 'composite' ) || $product->is_type( 'bundle' )|| $product->is_type('yith_bundle') || $product->is_type('yith-composite')) {
                $simple_products[] = $productId;
                $this->add_to_feed( $product, $product_meta_keys );
            }

            if( $product->is_type( 'grouped' ) && $this->parent_product ){
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
            $item = RexShoppingCustom::createItem();

            if (preg_match('#(\d+)$#', $this->ebay_cat_id, $matches)) {
                $attributes = array_slice($attributes, 0, 1, true) +
                              array("Category" => $matches[1]) +
                              array_slice($attributes, 1, count($attributes) - 1, true) ;
            }
            $attributes['Relationship'] = '';
            $attributes['RelationshipDetails'] = '';

            // add all attributes for each product.
            foreach ($attributes as $key => $value) {
                if ( $this->rex_feed_skip_row && $this->feed_format === 'xml' ) {
                    if ( $value != '' ) {
                        $item->$key( $value ); // invoke $key as method of $item object.
                    }
                }
                else {
                    $item->$key( $value ); // invoke $key as method of $item object.
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
    }


    /**
     * Return Feed
     * @return array|bool|string
     */
    public function returnFinalProduct(){
        return RexShoppingCustom::asCsv();
    }

    public function footer_replace() {}
}