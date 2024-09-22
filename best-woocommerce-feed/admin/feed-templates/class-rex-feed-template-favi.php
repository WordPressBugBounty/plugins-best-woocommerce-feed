<?php
/**
 * The Fashionchick Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.2.5
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for AdRoll feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Fashionchick
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Favi extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information' => array(
				'Identifier'    => 'Identifier',
				'Manufacturer'  => 'Manufacturer',
				'Name'          => 'Name',
				'Product_url'   => 'Product_url',
				'Price'         => 'Price',
				'Net_price'     => 'Net_price',
				'Image_url'     => 'Image_url',
				'Image_url_2'   => 'Image_url_2',
				'Image_url_3'   => 'Image_url_3',
				'Category'      => 'Category',
				'Description'   => 'Description',
				'Delivery_Time' => 'Delivery_Time',
				'Delivery_Cost' => 'Delivery_Cost',
				'EAN_code'      => 'EAN_code',
			),
			'Attributes'           => array(
				'Attribute_name_1'   => 'ATTRIBUTE 1',
				'Attribute_value_1'  => 'Value 1',
				'Attribute_name_2'   => 'ATTRIBUTE 2',
				'Attribute_value_2'  => 'Value 2',
				'Attribute_name_3'   => 'ATTRIBUTE 3',
				'Attribute_value_3'  => 'Value 3',
				'Attribute_name_4'   => 'ATTRIBUTE 4',
				'Attribute_value_4'  => 'Value 4',
				'Attribute_name_5'   => 'ATTRIBUTE 5',
				'Attribute_value_5'  => 'Value 5',
				'Attribute_name_6'   => 'ATTRIBUTE 6',
				'Attribute_value_6'  => 'Value 6',
				'Attribute_name_7'   => 'ATTRIBUTE 7',
				'Attribute_value_7'  => 'Value 7',
				'Attribute_name_8'   => 'ATTRIBUTE 8',
				'Attribute_value_8'  => 'Value 8',
				'Attribute_name_9'   => 'ATTRIBUTE 9',
				'Attribute_value_9'  => 'Value 9',
				'Attribute_name_10'  => 'ATTRIBUTE 10',
				'Attribute_value_10' => 'Value 10',
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
				'attr'     => 'Identifier',
				'type'     => 'meta',
				'meta_key' => 'id',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Name',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'cdata',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Product_url',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Price',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Image_url',
				'type'     => 'meta',
				'meta_key' => 'main_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Category',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'cdata',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Description',
				'type'     => 'meta',
				'meta_key' => 'description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'cdata',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Delivery_Time',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '3',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Manufacturer',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'cdata',
				'limit'    => 0,
			),
		);
	}
}
