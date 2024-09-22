<?php
/**
 * Class Rex_Feed_Template_Skroutz
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Skroutz
 * @author     RexTheme <info@rextheme.com>
 */

/**
 * Defines the attributes and template for skroutz feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Skroutz
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Skroutz extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'   => array(
				'name'         => 'Product Name',
				'id'           => 'Unique ID',
				'link'         => 'Product Link',
				'image'        => 'Image Link',
				'category'     => 'Category Name',
				'price'        => 'Price',
				'availability' => 'Availability',
				'manufacturer' => 'Manufacturer',
				'mpn'          => 'MPN/ISBN',
			),

			'Additional Information' => array(
				'ean'                    => 'EAN/Barcode',
				'size'                   => 'Size',
				'weight'                 => 'Weight',
				'shippingcosts'          => 'Shipping Costs',
				'color'                  => 'Color',
				'additional_imageurl_1'  => 'Additional Image Link 1',
				'additional_imageurl_2'  => 'Additional Image Link 2',
				'additional_imageurl_3'  => 'Additional Image Link 3',
				'additional_imageurl_4'  => 'Additional Image Link 4',
				'additional_imageurl_5'  => 'Additional Image Link 5',
				'additional_imageurl_6'  => 'Additional Image Link 6',
				'additional_imageurl_7'  => 'Additional Image Link 7',
				'additional_imageurl_8'  => 'Additional Image Link 8',
				'additional_imageurl_9'  => 'Additional Image Link 9',
				'additional_imageurl_10' => 'Additional Image Link 10',
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
				'attr'     => 'name',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
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
				'attr'     => 'link',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'image',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'category',
				'type'     => 'meta',
				'meta_key' => 'product_cats',
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
				'attr'     => 'manufacturer',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'mpn',
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
