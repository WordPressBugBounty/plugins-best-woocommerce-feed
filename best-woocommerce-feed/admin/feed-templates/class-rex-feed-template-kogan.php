<?php
/**
 * The Kogan Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for the Kogan feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Kogan
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Kogan extends Rex_Feed_Abstract_Template {

    /**
     * Define merchant's required and optional/additional attributes
     *
     * @return void
     */
    protected function init_atts() {
        $this->attributes = array(
            'Required Information'   => array(
                'PRODUCT_SKU'           => 'Product SKU',
                'PRODUCT_TITLE'         => 'Product Title',
                'PRODUCT_DESCRIPTION'   => 'Product Description',
                'STOCK'                 => 'Stock',
                'PRICE'                 => 'Price',
                'IMAGES'                => 'Images',
            ),

            'Additional Information' => array(
                'BRAND'                 => 'Brand',
                'CATEGORY'              => 'Category',
                'SHIPPING'              => 'Shipping',
                'product_subtitle'      => 'Product Subtitle',
                'product_inbox'         => 'Product Inbox',
                'product_gtin'          => 'Product GTIN',
                'rrp'                   => 'RRP',
                'handling_days'         => 'Handling Days',
                'product_location'      => 'Product Location',
            ),
        );
    }

    /**
     * Define merchant's default attributes
     *
     * @return void
     */
    protected function init_default_template_mappings() {
        $this->template_mappings = array(
            array(
                'attr'     => 'PRODUCT_SKU',
                'type'     => 'meta',
                'meta_key' => 'sku',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),

            array(
                'attr'     => 'PRODUCT_TITLE',
                'type'     => 'meta',
                'meta_key' => 'title',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),

            array(
                'attr'     => 'PRODUCT_DESCRIPTION',
                'type'     => 'meta',
                'meta_key' => 'description',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),

            array(
                'attr'     => 'STOCK',
                'type'     => 'meta',
                'meta_key' => 'quantity',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),

            array(
                'attr'     => 'PRICE',
                'type'     => 'meta',
                'meta_key' => 'price',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
                'escape'   => 'default',
                'limit'    => 0,
            ),

            array(
                'attr'     => 'IMAGES',
                'type'     => 'meta',
                'meta_key' => 'featured_image',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
        );
    }
}
?>
