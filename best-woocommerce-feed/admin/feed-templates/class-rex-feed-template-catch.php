<?php
/**
 * The Catch Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for the Catch feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Catch
 * @author     RexTheme
 */
class Rex_Feed_Template_Catch extends Rex_Feed_Abstract_Template {

    /**
     * Define merchant's required and optional/additional attributes
     *
     * @return void
     */
    protected function init_atts() {
        $this->attributes = array(
            'Required Information'   => array(
                'PRODUCT_ID'           => 'Product ID',
                'TITLE'                => 'Product Title',
                'IMAGE_1'              => 'Primary Image',
                'PRODUCT_DESCRIPTION'  => 'Product Description',
                'PRICE'                => 'Price',
                'QUANTITY'             => 'Stock Quantity',
                'STATE'                => 'Product State',
            ),

            'Additional Information' => array(
                'CATEGORY'             => 'Category',
                'BRAND'                => 'Brand',
                'DISCOUNT_PRICE'       => 'Discount Price',
                'SHIPPING'             => 'Shipping Details',
                'GTIN'                 => 'Global Trade Item Number',
                'LEAD_TIME_TO_SHIP'    => 'Lead Time to Ship',
                'LOGISTIC_CLASS'       => 'Logistic Class',
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
                'attr'     => 'PRODUCT_ID',
                'type'     => 'meta',
                'meta_key' => 'id',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),

            array(
                'attr'     => 'TITLE',
                'type'     => 'meta',
                'meta_key' => 'title',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),

            array(
                'attr'     => 'IMAGE_1',
                'type'     => 'meta',
                'meta_key' => 'featured_image',
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
                'attr'     => 'PRICE',
                'type'     => 'meta',
                'meta_key' => 'price',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => ' ' . get_option('woocommerce_currency'),
                'escape'   => 'default',
                'limit'    => 0,
            ),

            array(
                'attr'     => 'QUANTITY',
                'type'     => 'meta',
                'meta_key' => 'quantity',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),

            array(
                'attr'     => 'STATE',
                'type'     => 'static',
                'meta_key' => '',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
        );
    }
}
