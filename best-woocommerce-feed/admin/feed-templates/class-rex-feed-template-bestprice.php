<?php
/**
 * The Bestprice Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.2.5
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Bestprice feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Bestprice
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Bestprice extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information' => array(
				'productId'     => 'Product Id',
				'title'         => 'Product title',
				'productURL'    => 'Product URL',
				'imagesURL_'    => 'Image URL',
				'price'         => 'Price',
				'category_path' => 'Category Path',
				'availability'  => 'Availability',
				'brand'         => 'Brand',
				'ean'           => 'EAN',
				'isbn'          => 'ISBN',
			),

			'Optional Information' => array(
				'category_id'       => 'Category id',
				'image'             => 'Image',
				'stock'             => 'Stock',
				'size'              => 'Size',
				'color'             => 'color',
				'manufacturer'      => 'manufacturer',
				'warranty_provider' => 'Warranty Provider',
				'warranty_duration' => 'Warranty Duration',
				'isBundle'          => 'isBundle',
				'feature_1'         => 'Feature 1',
				'feature_2'         => 'Feature 2',
				'feature_3'         => 'Feature 3',
				'feature_4'         => 'Feature 4',
				'feature_5'         => 'Feature 5',
				'feature_6'         => 'Feature 6',
				'feature_7'         => 'Feature 7',
				'feature_8'         => 'Feature 8',
				'feature_9'         => 'Feature 9',
				'feature_10'        => 'Feature 10',
				'weight'            => 'Weight',
				'shipping'          => 'Shipping',
				'imagesURL_2'       => 'Image URL 2',
				'imagesURL_3'       => 'Image URL 3',
				'imagesURL_4'       => 'Image URL 4',
				'imagesURL_5'       => 'Image URL 5',
				'imagesURL_6'       => 'Image URL 6',
				'imagesURL_7'       => 'Image URL 7',
				'imagesURL_8'       => 'Image URL 8',
				'imagesURL_9'       => 'Image URL 9',
				'imagesURL_10'      => 'Image URL 10',
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
				'attr'     => 'productId',
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
				'escape'   => 'cdata',
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
				'attr'     => 'imagesURL_',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'productURL',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'category_path',
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
				'attr'     => 'ean',
				'type'     => 'meta',
				'meta_key' => 'sku',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'isbn',
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
