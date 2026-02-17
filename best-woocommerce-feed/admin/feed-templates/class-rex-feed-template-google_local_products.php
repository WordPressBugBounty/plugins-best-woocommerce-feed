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
class Rex_Feed_Template_Google_local_products extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'    => array(
				'id'          => 'Product Id [id]',
				'title'       => 'Product Title [title]',
				'description' => 'Product Description [description]',
				'image_link'  => 'Image Link [image_link]',
			),
			'Recommended Information' => array(
				'gtin'                        => 'GTIN [gtin]',
				'brand'                       => 'Brand [brand]',
				'condition'                   => 'Condition [condition]',
				'energy_efficiency_class'     => 'Energy Efficiency Class',
				'min_energy_efficiency_class' => 'Min Energy Efficiency Class',
				'max_energy_efficiency_class' => 'Max Energy Efficiency Class',
				'excluded_destination'        => 'Excluded Destination [excluded_destination]',
			),
			'Optional'                => array(
				'link'                      => 'Product URL [link]',
				'price'                     => 'Price [price]',
				'sale_price'                => 'Sale Price [sale_price]',
				'sale_price_effective_date' => 'Sale Price Effective Date [sale_price_effective_date]',
				'unit_pricing_measure'      => 'Unit Pricing Measure [unit_pricing_measure]',
				'unit_pricing_base_measure' => 'Unit Pricing Base Measure [unit_pricing_base_measure]',
				'pickup_method'             => 'Pickup Method [pickup_method]',
				'pickup_sla'                => 'Pickup SLA [pickup_sla]',
				'link_template'             => 'Link Template [link_template]',
				'mobile_link_template'      => 'Mobile Link Template [mobile_link_template]',
				'ads_redirect'              => 'Ads Redirect [ads_redirect]',
			),
			'Apparel Items'           => array(
				'item_group_id' => 'Item Group ID [item_group_id]',
				'color'         => 'Color [color]',
				'size'          => 'Size [size]',
				'gender'        => 'Gender [gender]',
				'age_group'     => 'Age Group [age_group]',
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
		);
	}
}
