<?php
/**
 * The Google Dynamic Search Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for google feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Google_dsa
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Gulog_gratis extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information' => array(
				'last_updated'   => 'Last Updated [last_updated]',
				'headline'       => 'Title [headline]',
				'text'           => 'Description [text]',
				'price'          => 'Price [price]',
				'type'           => 'Ad Type [type]',
				'category'       => 'Category [category]',
				'categoryfields' => 'Category Fields [categoryfields]',
			),
			'Optional Information' => array(
				'price_text' => 'Price Text [price_text]',
				'images'     => 'Images [images]',
				'link'       => 'Ad Link [link]',
			),
			'Address Information'  => array(
				'road'        => 'Road [road]',
				'housenumber' => 'House Number [housenumber]',
				'floor'       => 'Floor [floor]',
				'door'        => 'Flat No. [door]',
				'zipcode'     => 'Zip Code [zipcode]',
				'city'        => 'City [city]',
				'countrycode' => 'Country Code [countrycode]',
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
				'attr'     => 'last_updated',
				'type'     => 'meta',
				'meta_key' => 'last_updated',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'headline',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'text',
				'type'     => 'meta',
				'meta_key' => 'description',
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
				'attr'     => 'type',
				'type'     => 'static',
				'meta_key' => '',
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
				'attr'     => 'categoryfields',
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
