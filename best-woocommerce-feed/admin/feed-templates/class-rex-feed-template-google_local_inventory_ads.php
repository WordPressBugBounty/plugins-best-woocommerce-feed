<?php
/**
 * The Google Feed Template class.
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
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Google
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Google_local_inventory_ads extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'    => array(
				'id'          => 'Product id',
				'title'       => 'Product Title',
				'description' => 'Product Description',
				'image_link'  => 'Product Image Url',
				'condition'   => 'Condition',
				'gtin'        => 'GTIN',
				'brand'       => 'Product Brand',

			),
			'Recommended Information' => array(
				'energy_efficiency_class'     => 'Energy Efficiency Class',
				'min_energy_efficiency_class' => 'Min Energy Efficiency Class',
				'max_energy_efficiency_class' => 'Max Energy Efficiency Class',
				'excluded_destination'        => 'Excluded Destination',
			),
			'Optional'                => array(
				'mpn'                         => 'MPN',
				'price'                       => 'Price',
				'sale_price'                  => 'Sale Price [sale_price]',
				'sale_price_effective_date'   => 'Sale Price Effective Date [sale_price_effective_date]',
				'unit_pricing_measure'        => 'Unit Pricing Measure',
				'unit_pricing_base_measure'   => 'Unit Pricing Base Measure',
				'pickup method'               => 'pickup method',
				'pickup SLA'                  => 'pickup SLA',
				'pickup_link_template'        => 'Pickup Link Template',
				'mobile_pickup_link_template' => 'Mobile Pickup Link Template',
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
				'attr'     => 'condition',
				'type'     => 'meta',
				'meta_key' => 'condition',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'gtin',
				'type'     => 'meta',
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
