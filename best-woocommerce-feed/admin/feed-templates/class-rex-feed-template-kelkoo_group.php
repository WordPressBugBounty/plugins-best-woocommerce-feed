<?php
/**
 * The Kelkoo Group Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Kelkoo Group feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Kelkoo_group
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Kelkoo_group extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
    protected function init_atts() {
        $this->attributes = array(
            'Required Information'   => array(
                'id'                 => 'Product ID',
                'title'              => 'Product Title',
                'product_url'        => 'Product URL',
                'landing_page_url'   => 'Product Landing Page URL',
                'image_url'          => 'Image Url',
                'price'              => 'Price',
                'brand'              => 'Brand',
                'description'        => 'Description',
                'ean'                => 'EAN',
                'availability'       => 'Availability',
                'merchant_category'  => 'Product Type',
                'delivery_cost'      => 'Delivery Cost',
                'condition'          => 'Condition',
                'ecotax'             => 'Ecotax',
                'gtin'               => 'GTIN'

            ),
            'Additional Information' => array(
                'price_no_rebate'         => 'Price (excl. Rebate)',
                'stock_quantity'          => 'Stock Quantity',
                'unit_price'              => 'Unit Price',
                'unit_price_measure'      => 'Unit Price Measure',
                'unit_price_base_measure' => 'Unit Price Base Measure',
                'on_sales'                => 'On Sale',
                'mpn'                     => 'MPN',
                'google_product_category' => 'Google Product Category',
                'delivery_time'           => 'Delivery Time',
                'age_group'               => 'Age Group',
                'mobile-url'              => 'Mobile URL',
                'color'                   => 'Color',
                'gender'                  => 'Gender',
                'material'                => 'Material',
                'size'                    => 'Size',
                'wine_region'             => 'Wine Region',
                'made_in'                 => 'Made In',
                'year'                    => 'Year',
                'offer-type'              => 'Offer Type',
                'merchant-info'           => 'Merchant Info',
                'image_url_2'             => 'Image URL-2',
                'image_url_3'             => 'Image URL-3',
                'image_url_4'             => 'Image URL-4',
                'green-product'           => 'Green Product',
                'green-label'             => 'Green Label',
                'sales-rank'              => 'Sales Rank',
                'unit-quantity'           => 'Unit Quantity',
                'occasion'                => 'Occasion',
                'keywords'                => 'Keywords',
                'shipping-method'         => 'Shipping Method',
                'delivery-cost-2'         => 'Delivery Cost-2',
                'delivery-cost-3'         => 'Delivery Cost-3',
                'delivery-cost-4'         => 'Delivery Cost-4',
                'shipping-method-2'       => 'Shipping Method-2',
                'shipping-method-3'       => 'Shipping Method-3',
                'shipping-method-4'       => 'Shipping Method-4',
                'zip-code'                => 'Zip Code',
                'stock-quantity'          => 'Stock Quantity',
                'shipping_weight'         => 'Shipping Weight',
                'payment-methods'         => 'Payment Methods',
                'voucher-title'           => 'Voucher Title',
                'voucher-description'     => 'Voucher Description',
                'voucher-url'             => 'Voucher URL',
                'voucher-code'            => 'Voucher Code',
                'voucher-start-date'      => 'Voucher Start Date',
                'voucher-end-date'        => 'Voucher End Date',
                'percentage-promo'        => 'Percentage Promo',
                'promo-start-date'        => 'Promo Start Date',
                'vpromo-end-date'         => 'Promo End Date',
                'user-rating'             => 'User Rating',
                'nb-reviews'              => 'NB Reviews',
                'user-review-link'        => 'User Review Link',
                'video-link'              => 'Video Link',
                'video-title'             => 'Video Title',
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
                'attr'     => 'id',
                'type'     => 'meta',
                'meta_key' => 'sku',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'title',
                'type'     => 'meta',
                'meta_key' => 'title',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'description',
                'type'     => 'meta',
                'meta_key' => 'description',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'product_url',
                'type'     => 'meta',
                'meta_key' => 'link',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'cdata',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'landing_page_url',
                'type'     => 'meta',
                'meta_key' => 'link',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'cdata',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'image_url',
                'type'     => 'meta',
                'meta_key' => 'featured_image',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'price',
                'type'     => 'meta',
                'meta_key' => 'price',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'merchant_category',
                'type'     => 'meta',
                'meta_key' => 'product_subcategory',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'delivery_cost',
                'type'     => 'meta',
                'meta_key' => 'shipping_cost',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'brand',
                'type'     => 'static',
                'meta_key' => '',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'gtin',
                'type'     => 'static',
                'meta_key' => '',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'availability',
                'type'     => 'meta',
                'meta_key' => 'availability_underscore',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'condition',
                'type'     => 'meta',
                'meta_key' => 'condition',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'ecotax',
                'type'     => 'static',
                'meta_key' => '',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            )
        );
    }
}
