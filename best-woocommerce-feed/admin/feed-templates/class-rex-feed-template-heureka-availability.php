<?php
/**
 * The Rex_Feed_Template_Heureka_availability Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      7.2.12
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Heureka Availability feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Heureka_availability
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Heureka_availability extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information' => array(
				'stock_quantity' => 'Stock Quantity [stock_quantity]',
				'orderDeadline'  => 'Order Deadline [delivery_time]',
			),
			'Depot Attributes'     => array(
				'depot_id_1'       => 'Depot ID 1',
				'stock_quantity_1' => 'Stock Quantity 1',
				'orderDeadline_1'  => 'Order Deadline 1 [pickup_time]',
				'depot_id_2'       => 'Depot ID 2',
				'stock_quantity_2' => 'Stock Quantity 2',
				'orderDeadline_2'  => 'Order Deadline 2 [pickup_time]',
				'depot_id_3'       => 'Depot ID 3',
				'stock_quantity_3' => 'Stock Quantity 3',
				'orderDeadline_3'  => 'Order Deadline 3 [pickup_time]',
				'depot_id_4'       => 'Depot ID 4',
				'stock_quantity_4' => 'Stock Quantity 4',
				'orderDeadline_4'  => 'Order Deadline 4 [pickup_time]',
				'depot_id_5'       => 'Depot ID 5',
				'stock_quantity_5' => 'Stock Quantity 5',
				'orderDeadline_5'  => 'Order Deadline 5 [pickup_time]',
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
				'attr'     => 'stock_quantity',
				'type'     => 'meta',
				'meta_key' => 'quantity',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'orderDeadline',
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
