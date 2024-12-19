<?php
/**
 * Class Rex_Feed_Template_Bing_local_inventory
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Bing_local_inventory
 * @author     RexTheme <info@rextheme.com>
 */

/**
 * Defines the attributes and template for Bing image feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Bing_local_inventory
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Bing_local_inventory extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = [
			'Required Attributes' => [
				'store_code' => 'Store Code [store_code]',
				'itemid'     => 'Item ID [itemid]',
				'quantity'   => 'Quantity [quantity]',
			],
			'Optional Attributes' => [
				'price'                     => 'Price [price]',
				'weeks_of_supply'           => 'Weeks of Supply [weeks_of_supply]',
				'pick_up_method'            => 'Pick-up Method [pick_up_method]',
				'pick_up_sla'               => 'Pick-up SLA [pick_up_sla]',
				'sale_price'                => 'Sale Price [sale_price]',
				'sale_price_effective_date' => 'Sale Price Effective Date [sale_price_effective_date]',
				'availability'              => 'Availability [availability]',
			],
		];
	}

	/**
	 * Define merchant's default attributes
	 *
	 * @return void
	 */
	protected function init_default_template_mappings() {
		$this->template_mappings = [
			[
				'attr'     => 'store_code',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'itemid',
				'type'     => 'meta',
				'meta_key' => 'id',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'price',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'quantity',
				'type'     => 'meta',
				'meta_key' => 'quantity',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'weeks_of_supply',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'pick_up_method',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'pick_up_sla',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
		];
	}
}
