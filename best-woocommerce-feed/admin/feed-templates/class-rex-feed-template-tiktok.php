<?php
/**
 * The TikTok Catalog Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for TikTok Catalog feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Tiktok
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Tiktok extends Rex_Feed_Abstract_Template {

    /**
     * Define merchant's required and optional/additional attributes
     *
     * @return void
     */
    protected function init_atts() {
        $this->attributes = [
            'Mandatory Fields'           => [
                'sku_id'                       => 'SKU ID [sku_id]',
                'title'                    => 'Product Title [title]',
                'description'              => 'Product Description [description]',
                'availability'              => 'Stock Status [availability]',
                'condition'                => 'Product Condition [condition]',
                'price'                     => 'Product Price [price]',
                'link'                     => 'Product URL [link]',
                'image_link'               => 'Main Image [image_link]',
                'brand'             => 'Manufacturer [brand]'
            ],
            'Optional Attributes'  => [
                'product_type'             => 'Product Categories [product_type] ',
                'video_link' => 'Video URL [video_link]',
                'additional_image_link' => 'Additional Image URL [additional_image_link]',
                'sale_price'                => 'Sale Price [sale_price]',
                'sale_price_effective_date' => 'Sale Price Effective Date [sale_price_effective_date]',
                'color'                      => 'Color [color]',
                'gender'                     => 'Gender [gender]',
                'age_group'                  => 'Age Group [age_group]',
                'size'                       => 'Size of the item [size]',
                'material'                   => 'Material [material]',
                'pattern'                    => 'Pattern [pattern]',
                'gtin'              => 'GTIN [gtin]',
                'mpn'               => 'MPN [mpn]',
                'ios_url'               => 'iOS URL [ios_url]',
                'android_url'               => 'Android URL [ios_url]',
                'merchant_brand'               => 'Merchant Brand [merchant_brand]',
                'google_product_category'  => 'Google Product Category [google_product_category]',
                'item_group_id'              => 'Item Group Id [item_group_id]',
                'identifier_exists' => 'Identifier Exist [identifier_exists]',
                'productHisEval' => 'Product Purchase Count [productHisEval]'
            ],
            'Tax & Shipping'              => [
                'tax'              => 'Tax [tax]',
                'shipping'         => 'Shipping [shipping]',
                'shipping_weight'  => 'Shipping Weight [shipping_weight]'
            ],
            'Custom Label Attributes'     => [
                'custom_label_0' => 'Custom label 0 [custom_label_0]',
                'custom_label_1' => 'Custom label 1 [custom_label_1]',
                'custom_label_2' => 'Custom label 2 [custom_label_2]',
                'custom_label_3' => 'Custom label 3 [custom_label_3]',
                'custom_label_4' => 'Custom label 4 [custom_label_4]'
            ]
        ];
    }

    /**
     * Define merchant's default attributes
     *
     * @return void
     */
    protected function init_default_template_mappings() {
        $this->template_mappings = [
            [
                'attr'     => 'sku_id',
                'type'     => 'meta',
                'meta_key' => 'sku',
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
                'meta_key' => 'availability_underscore',
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
                'st_value' => wpfm_get_woocommerce_shop_name(),
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'google_product_category',
                'type'     => 'meta',
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