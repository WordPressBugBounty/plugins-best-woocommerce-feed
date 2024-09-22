<?php
/**
 * The Twitter (X) Ads Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Twitter (X) Ads feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Twitter
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Twitter extends Rex_Feed_Abstract_Template {

    /**
     * Define merchant's required and optional/additional attributes
     *
     * @return void
     */
    protected function init_atts() {
        $this->attributes = array(
            'Required Attributes' => [
                'id'           => 'Product Id [id]',
                'title'        => 'Product Title [title]',
                'description'  => 'Product Description [description]',
                'availability' => 'Stock Status [availability]',
                'condition'    => 'Condition [condition]',
                'price'        => 'Regular Price [price]',
                'link'         => 'Product URL [link]',
                'image_link'   => 'Main Image [image_link]'
            ],
            'Unique Product Identifiers' => [
                'brand'             => 'Manufacturer [brand]',
                'gtin'              => 'GTIN [gtin]',
                'mpn'               => 'MPN [mpn]',
                'identifier_exists' => 'Identifier Exist [identifier_exists]'
            ],
            'Optional Attributes' => [
                "mobile_link"               => "Mobile Link [mobile_link]",
                "additional_image_link"     => "Additional Image Links (comma separated) [additional_image_link]",
                "google_product_category"   => "Google Product Category [google_product_category]",
                'product_type'              => 'Product Type [product_type]',
                'inventory'                 => 'Inventory [inventory]',
                "sale_price"                => "Sale Price [sale_price]",
                "sale_price_effective_date" => "Sale Price Effective Date [sale_price_effective_date]",
                'item_group_id'             => 'Item Group Id [item_group_id]',
                "gender"                    => "Gender [gender]",
                "color"                     => "Color [color]",
                "size"                      => "Size [size]",
                "age_group"                 => "Age Group [age_group]"
            ],
            'Custom Labels' => [
                'custom_label_0' => 'Custom Label 0 [custom_label_0]',
                'custom_label_1' => 'Custom Label 1 [custom_label_1]',
                'custom_label_2' => 'Custom Label 2 [custom_label_2]',
                'custom_label_3' => 'Custom Label 3 [custom_label_3]',
                'custom_label_4' => 'Custom Label 4 [custom_label_4]'
            ]
        );
    }

    /**
     * Define merchant's default attributes
     *
     * @return void
     */
    protected function init_default_template_mappings() {
        $this->template_mappings = [
            [
                'attr'     => 'id',
                'type'     => 'meta',
                'meta_key' => 'id',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'title',
                'type'     => 'meta',
                'meta_key' => 'title',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'description',
                'type'     => 'meta',
                'meta_key' => 'description',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => array( 'strip_tags', 'remove_shortcodes' ),
                'limit'    => 0
            ],
            [
                'attr'     => 'availability',
                'type'     => 'meta',
                'meta_key' => 'availability',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'condition',
                'type'     => 'meta',
                'meta_key' => 'condition',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'price',
                'type'     => 'meta',
                'meta_key' => 'price',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'link',
                'type'     => 'meta',
                'meta_key' => 'link',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'image_link',
                'type'     => 'meta',
                'meta_key' => 'featured_image',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'brand',
                'type'     => 'static',
                'meta_key' => '',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ]
        ];
    }
}