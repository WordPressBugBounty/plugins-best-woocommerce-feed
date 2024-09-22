<?php
/**
 * The Google_manufacturer_center Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Google_manufacturer_center feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Google_manufacturer_center
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Google_manufacturer_center extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'    => array(
				'id'          => 'ID',
				'title'       => 'Title',
				'description' => 'Description',
				'image_link'  => 'Image Link',
				'gtin'        => 'GTIN',
				'brand'       => 'Brand',
			),

			'Recommended Information' => array(
				'mpn'                   => 'MPN',
				'product_line'          => 'Product Line',
				'additional_image_link' => 'Additional Image Link',
			),

			'Additional Information'  => array(
				'product_name'           => 'Product Name',
				'product_type'           => 'Product Type',
				'product_highlights'     => 'Product Highlights',
				'feature_description'    => 'Feature Description',
				'video_link'             => 'Video Link',
				'product_page_url'       => 'Product Page url',
				'disclosure_date'        => 'Disclosure Date',
				'release_date'           => 'Release Date',
				'suggested_retail_price' => 'Suggested Retail Price',
				'rich_product_content'   => 'Rich Product Content',
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
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'image_link',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
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
				'attr'     => 'brand',
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
