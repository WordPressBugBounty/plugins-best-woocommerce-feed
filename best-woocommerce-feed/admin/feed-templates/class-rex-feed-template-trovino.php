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
 * Defines the attributes and template for bing feed
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Trovino
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Trovino extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'   => array(
				'name'          => 'Name',
				'producer'      => 'Producer',
				'region'        => 'Region',
				'image_url'     => 'Image URL',
				'product_url'   => 'Product URL',
				'grape_variety' => 'Grape Variety',
			),

			'Additional Information' => array(
				'image_url'   => 'Image URL',
				'description' => 'Description',
				'producer'    => 'Producer',
				'wine_type'   => 'Wine Type',
				'region'      => 'Region',
				'appellation' => 'Appellation',
				'vintage'     => 'Vintage',
				'bottle_size' => 'Bottle Size',
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
				'attr'     => 'producer',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'region',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
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
				'attr'     => 'product_url',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'grape_variety',
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
