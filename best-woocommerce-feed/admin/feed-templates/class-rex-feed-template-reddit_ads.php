<?php
/**
 * The Reddit Ads Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Reddit Ads feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Reddit_ads
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Reddit_ads extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'                => array(
				'id'          => 'Product Id [id]',
				'title'       => 'Product Title [title]',
				'description' => 'Product Description [description]',
				'link'        => 'Product URL [link]',
				'image_link'  => 'Main Image [image_link]',
				'price'       => 'Price [price]',
			),

			'Optional Information'                => array(
				'availability'             => 'Stock Status [availability]',
				'brand'                    => 'Brand [brand]',
				'condition'                => 'Condition [condition]',
				'sale_price'               => 'Sale Price [sale_price]',
				'sale_price_effective_date' => 'Sale Price Effective Date [sale_price_effective_date]',
				'additional_image_link_1'  => 'Additional Image 1 [additional_image_link]',
				'additional_image_link_2'  => 'Additional Image 2 [additional_image_link]',
				'additional_image_link_3'  => 'Additional Image 3 [additional_image_link]',
				'additional_image_link_4'  => 'Additional Image 4 [additional_image_link]',
				'additional_image_link_5'  => 'Additional Image 5 [additional_image_link]',
				'product_type'             => 'Product Categories [product_type]',
				'google_product_category'  => 'Google Product Category [google_product_category]',
				'gtin'                     => 'GTIN [gtin]',
				'mpn'                      => 'MPN [mpn]',
				'item_group_id'            => 'Item Group Id [item_group_id]',
				'color'                    => 'Color [color]',
				'size'                     => 'Size [size]',
				'gender'                   => 'Gender [gender]',
				'age_group'                => 'Age Group [age_group]',
				'material'                 => 'Material [material]',
				'pattern'                  => 'Pattern [pattern]',
				'shipping'                 => 'Shipping [shipping]',
				'shipping_weight'          => 'Shipping Weight [shipping_weight]',
				'tax'                      => 'Tax [tax]',
			),

			'Custom Attributes'                   => array(
				'custom_label_0' => 'Custom Label 0 [custom_label_0]',
				'custom_label_1' => 'Custom Label 1 [custom_label_1]',
				'custom_label_2' => 'Custom Label 2 [custom_label_2]',
				'custom_label_3' => 'Custom Label 3 [custom_label_3]',
				'custom_label_4' => 'Custom Label 4 [custom_label_4]',
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
				'meta_key' => 'id',
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
				'escape'   => 'strip_tags',
				'limit'    => 5000,
			),
			array(
				'attr'     => 'link',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'cdata',
				'limit'    => 0,
			),
			array(
				'attr'     => 'image_link',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'cdata',
				'limit'    => 0,
			),
			array(
				'attr'     => 'price',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'availability',
				'type'     => 'meta',
				'meta_key' => 'availability',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'brand',
				'type'     => 'meta',
				'meta_key' => 'brand',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'condition',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => 'new',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'product_type',
				'type'     => 'meta',
				'meta_key' => 'product_cats',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
		);
	}
}