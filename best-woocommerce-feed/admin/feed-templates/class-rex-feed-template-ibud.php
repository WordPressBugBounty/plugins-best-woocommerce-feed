<?php
/**
 * The Bing Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Ibud feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Ibud
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Ibud extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'   => array(
				'id'                 => 'Merchant Product ID',
				'article'            => 'Article',
				'name_uk'            => 'Category Name UK',
				'price'              => 'Price',
				'currency'           => 'Currency',
				'measure'            => 'Measure',
				'availability'       => 'Availability',
				'min_order'          => 'Min Order',
				'max_order'          => 'Max Order',
				'category'           => 'Merchant Category',
				'picture'            => 'Primary Image URL',
				'description_uk'     => 'Description UK',
				'producer_uk'        => 'Producer UK',
				'url'                => 'Product URL',
				'only_for_IM'        => 'Only For IM',
				'recommended'        => 'Recommended',
				'seo_title_uk'       => 'SEO Title UK',
				'seo_description_uk' => 'SEO Description UK',
				'seo_keywords_uk'    => 'SEO Keywords UK',

			),

			'Additional Information' => array(
				'parentId'       => 'Parent Id',
				'description_ru' => 'Category Description RU',
				'mpn'            => 'Manufacturer Part Number',
				'gtin'           => 'Global Trade Item Number',
				'brand'          => 'Brand',
				'upc'            => 'UPC / EAN',
				'condition'      => 'Condition',

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
				'attr'     => 'article',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'name_uk',
				'type'     => 'meta',
				'meta_key' => 'title',
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
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'currency',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'measure',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'availability',
				'type'     => 'meta',
				'meta_key' => 'availability',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'min_order',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'max_order',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'category',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'picture',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'description_uk',
				'type'     => 'meta',
				'meta_key' => 'description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'producer_uk',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'url',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'only_for_IM',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'recommended',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'seo_title_uk',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'seo_description_uk',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'seo_keywords_uk',
				'type'     => 'meta',
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
