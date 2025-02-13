<?php
/**
 * The Pinterest Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.2.5
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Pinterest feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Pinterest
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Pinterest extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = [
			'Basic Information' => [
				'id'                       => 'Product Id [id]',
				'title'                    => 'Product Title [title]',
				'description'              => 'Product Description [description]',
				'link'                     => 'Product URL [link]',
				'mobile_link'              => 'Product URL [mobile_link]',
				'product_type'             => 'Product Categories [product_type] ',
				'google_product_category'  => 'Google Product Category [google_product_category]',
				'image_link'               => 'Main Image [image_link]',
				'additional_image_link_1'  => 'Additional Image 1 [additional_image_link]',
				'additional_image_link_2'  => 'Additional Image 2 [additional_image_link]',
				'additional_image_link_3'  => 'Additional Image 3 [additional_image_link]',
				'additional_image_link_4'  => 'Additional Image 4 [additional_image_link]',
				'additional_image_link_5'  => 'Additional Image 5 [additional_image_link]',
				'additional_image_link_6'  => 'Additional Image 6 [additional_image_link]',
				'additional_image_link_7'  => 'Additional Image 7 [additional_image_link]',
				'additional_image_link_8'  => 'Additional Image 8 [additional_image_link]',
				'additional_image_link_9'  => 'Additional Image 9 [additional_image_link]',
				'additional_image_link_10' => 'Additional Image 10 [additional_image_link]',
				'condition'                => 'Condition [condition]',
			],
			'Availability & Price' => [
				'availability'              => 'Stock Status [availability]',
				"availability_date"         => "Availability Date[availability_date]",
				'price'                     => 'Regular Price [price]',
				'sale_price'                => 'Sale Price [sale_price]',
				'sale_price_effective_date' => 'Sale Price Effective Date [sale_price_effective_date]',
				"cost_of_goods_sold"        => "Cost of Goods Sold[cost_of_goods_sold]",
				"expiration_date"           => "Expiration Date[expiration_date]",
				"inventory"                 => "Facebook Inventory[inventory]",
				"override"                  => "Facebook Override[override]",
			],
			'Unique Product Identifiers' => [
				'brand'             => 'Manufacturer [brand]',
				'gtin'              => 'GTIN [gtin]',
				'mpn'               => 'MPN [mpn]',
				'identifier_exists' => 'Identifier Exist [identifier_exists]',
			],
			'Detailed Product Attributes' => [
				'item_group_id' => 'Item Group Id [item_group_id]',
				'color'         => 'Color [color]',
				'gender'        => 'Gender [gender]',
				'age_group'     => 'Age Group [age_group]',
				'material'      => 'Material [material]',
				'pattern'       => 'Pattern [pattern]',
				'size'          => 'Size of the item [size]',
				'size_type'     => 'Size Type [size_type]',
				'size_system'   => 'Size System [size_system]',
			],
			'Tax & Shipping' => [
				'tax'              => 'Tax [tax]',
				'shipping'         => 'Shipping [shipping]',
				"tax_country"      => "Tax Country[tax_country]",
				"tax_region"       => "Tax Region[tax_region]",
				"tax_rate"         => "Tax Rate[tax_rate]",
				"tax_ship"         => "Tax Ship[tax_ship]",
				"tax_category"     => "Tax[tax_category]",
				'shipping_country' => 'Shipping Country',
				'shipping_region'  => 'Shipping Region',
				'shipping_service' => 'Shipping Service',
				'shipping_price'   => 'Shipping Price',
				'shipping_weight'  => 'Shipping Weight [shipping_weight]',
				'shipping_length'  => 'Shipping Length [shipping_length]',
				'shipping_width'   => 'Shipping Width [shipping_width]',
				'shipping_height'  => 'Shipping Height [shipping_height]',
				'shipping_label'   => 'Shipping Label [shipping_label]',
			],
			'Product Combinations' => [
				'multipack' => 'Multipack [multipack]',
				'is_bundle' => 'Is Bundle [is_bundle]',
			],
			'Adult Products' => [
				'adult' => 'Adult [adult]',
			],
			'AdWord Attributes' => [
				'adwords_redirect' => 'Adwords Redirect [adwords_redirect]',
			],
			'Custom Label Attributes' => [
				'custom_label_0' => 'Custom label 0 [custom_label_0]',
				'custom_label_1' => 'Custom label 1 [custom_label_1]',
				'custom_label_2' => 'Custom label 2 [custom_label_2]',
				'custom_label_3' => 'Custom label 3 [custom_label_3]',
				'custom_label_4' => 'Custom label 4 [custom_label_4]',
			],
			'Additional Attributes' => [
				'excluded_destination' => 'Excluded Destination [excluded_destination]',
				"included_destination" => "Included Destination[included_destination]",
				'expiration_date'      => 'Expiration Date [expiration_date]',
			],
			'Unit Prices (EU Countries and Switzerland Only)' => [
				'unit_pricing_measure'      => 'Unit Pricing Measure [unit_pricing_measure]',
				'unit_pricing_base_measure' => 'Unit Pricing Base Measure [unit_pricing_base_measure]',
			],
			'Energy Labels' => [
				'energy_efficiency_class' => 'Energy Efficiency Class [energy_efficiency_class]',
			],
			'Loyalty Points (Japan Only)' => [
				'loyalty_points' => 'loyalty_points [loyalty_points]',
			],
			'Multiple Installments (Brazil Only)' => [
				'installment' => 'Installment [installment]',
			],
			'Merchant Promotions Attribute' => [
				'promotion_id' => 'Promotion Id [promotion_id]',
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
				'attr'     => 'id',
				'type'     => 'meta',
				'meta_key' => 'id',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'title',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'description',
				'type'     => 'meta',
				'meta_key' => 'description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => [ 'remove_tab', 'strip_tags', 'remove_shortcodes' ],
				'limit'    => 0,
			],
			[
				'attr'     => 'link',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'image_link',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
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
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'availability',
				'type'     => 'meta',
				'meta_key' => 'availability_underscore',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
		];
	}
}
