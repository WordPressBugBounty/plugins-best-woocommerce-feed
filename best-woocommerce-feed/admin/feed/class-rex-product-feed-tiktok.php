<?php

/**
 * The file that generates xml feed for TikTok Catalog.
 *
 * A class definition that includes functions used for generating xml feed.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed_Tiktok
 * @subpackage Rex_Product_Feed_Google/includes
 * @author     RexTheme <info@rextheme.com>
 */

use LukeSnowden\GoogleShoppingFeed\Containers\GoogleShopping;

class Rex_Product_Feed_Tiktok extends Rex_Product_Feed_Google {

    /**
     * Adding items to feed
     *
     * @param $product
     * @param $meta_keys
     * @param string $product_type
     */
    protected function add_to_feed( $product, $meta_keys, $product_type = '' )
    {
        $attributes = $this->get_product_data( $product, $meta_keys );

        if( ( $this->rex_feed_skip_product && empty( array_keys( $attributes, '' ) ) ) || !$this->rex_feed_skip_product ) {
            $item = GoogleShopping::createItem();

            if( $product_type === 'variation' ) {
                $check_item_group_id = 0;
            }

            foreach( $attributes as $key => $value ) {
                if( 'shipping' === $key ) {
                    if ( is_array( $value ) && !empty( $value ) ) {
                        $shipping_vals = [];
                        foreach ( $value as $shipping ) {
                            $shipping_vals[] = implode( ':', $shipping );
                        }
                        $item->$key( null, null, null, null, implode( ';', $shipping_vals ) );
                    }
                }
                elseif ( 'tax' === $key ) {
                    $item->$key( null, null, null, null, null, $value );
                }
                else {
                    if( $this->rex_feed_skip_row && $this->feed_format === 'xml' ) {
                        if( $value != '' ) {
                            $item->$key( $value ); // invoke $key as method of $item object.
                        }
                    }
                    else {
                        $item->$key( $value ); // invoke $key as method of $item object.
                    }
                }

                if( $product_type === 'variation' && 'item_group_id' == $key ) {
                    $check_item_group_id = 1;
                }
            }

            if( $product_type === 'variation' && $check_item_group_id === 0 ) {
                $item->item_group_id( $product->get_parent_id() );
            }
        }
    }
}