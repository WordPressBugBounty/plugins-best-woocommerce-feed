<?php
/**
 * The shopalike marketplace Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for shopee marketplace feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Shopee
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Shopee extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information' => array(
				'Variation Integration No.' => 'Variation Integration No.',
				'Weight'                    => 'Weight',
				'Images'                    => 'Images',
			),
			'Optional Information' => array(
				'Product Name'        => 'Product Name',
				'Product Description' => 'Product Description',
				'Price'               => 'Price',
				'Cover Image'         => 'Cover Image',
				'Parent SKU'          => 'Parent SKU',
				'SKU'                 => 'SKU',
				'Dimensions'          => 'Dimensions',
				'Pre-Order DTS'       => 'Pre-Order DTS',
				'Stock'               => 'Stock',
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
				'attr'     => 'Variation Integration No.',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Weight',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Images',
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
