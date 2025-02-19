<?php
/**
 * The MediaMarkt Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      7.4.30
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for mediamarkt feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Mediamarkt
 * @author     RexTheme <info@rextheme.com>
 * @since     7.4.30
 */
class Rex_Feed_Template_Mediamarkt extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
     * @since 7.4.30
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information' => array(
				'id'          => 'Product ID',
				'title'       => 'Product Title',
				'short_description' => 'Short Description',
                'long_description' => 'Long Description',
				'link'        => 'Product URL',
				'image_link'  => 'Image Link',
				'price'       => 'Price',
				'categories'  => 'Categories',
				'brand'       => 'Brand',
                'energy_efficiency_class' => 'Energy Efficiency Class',
                'additional_image_link_1'  => 'Additional Image 1 [additional_image_link]',
                'additional_image_link_2'  => 'Additional Image 2 [additional_image_link]',
			)
		);
	}

	/**
	 * Define merchant's default attributes
	 *
	 * @return void
     * @since 7.4.30
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
				'attr'     => 'short_description',
				'type'     => 'meta',
				'meta_key' => 'short_description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
            array(
                'attr'     => 'long_description',
                'type'     => 'meta',
                'meta_key' => 'description',
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
				'attr'     => 'image_link',
				'type'     => 'meta',
				'meta_key' => 'main_image',
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
				'attr'     => 'categories',
				'type'     => 'meta',
				'meta_key' => 'product_cats',
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
