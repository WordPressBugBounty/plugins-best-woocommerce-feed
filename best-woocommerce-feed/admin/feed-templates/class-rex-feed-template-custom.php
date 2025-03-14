<?php
/**
 * The Custom Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Custom feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Custom
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Custom extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Basic Information'                   => array(
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
			),

			'Availability & Price'                => array(
				'availability'              => 'Stock Status [availability]',
				'price'                     => 'Regular Price [price]',
				'sale_price'                => 'Sale Price [sale_price]',
				'sale_price_effective_date' => 'Sale Price Effective Date [sale_price_effective_date]',
			),

			'Unique Product Identifiers'          => array(
				'brand'             => 'Manufacturer [brand]',
				'gtin'              => 'GTIN [gtin]',
				'mpn'               => 'MPN [mpn]',
				'identifier_exists' => 'Identifier Exist [identifier_exists]',
			),

			'Detailed Product Attributes'         => array(
				'item_group_id' => 'Item Group Id [item_group_id]',
				'color'         => 'Color [color]',
				'gender'        => 'Gender [gender]',
				'age_group'     => 'Age Group [age_group]',
				'material'      => 'Material [material]',
				'pattern'       => 'Pattern [pattern]',
				'size'          => 'Size of the item [size]',
				'size_type'     => 'Size Type [size_type]',
				'size_system'   => 'Size System [size_system]',
			),

			'Tax & Shipping'                      => array(
				'tax'              => 'Tax [tax]',
				'shipping_country' => 'Shipping Country',
				'shipping_region'  => 'Shipping Region',
				'shipping_service' => 'Shipping Service',
				'shipping_price'   => 'Shipping Price',
				'weight'           => 'Shipping Weight [shipping_weight]',
				'length'           => 'Shipping Length [shipping_length]',
				'width'            => 'Shipping Width [shipping_width]',
				'height'           => 'Shipping Height [shipping_height]',
				'shipping_label'   => 'Shipping Label [shipping_label]',
			),

			'Product Combinations'                => array(
				'multipack' => 'Multipack [multipack]',
				'is_bundle' => 'Is Bundle [is_bundle]',
			),

			'Adult Products'                      => array(
				'adult' => 'Adult [adult]',
			),

			'AdWord Attributes'                   => array(
				'adwords_redirect' => 'Adwords Redirect [adwords_redirect]',
			),

			'Custom Label Attributes'             => array(
				'custom_label_0' => 'Custom label 0 [custom_label_0]',
				'custom_label_1' => 'Custom label 1 [custom_label_1]',
				'custom_label_2' => 'Custom label 2 [custom_label_2]',
				'custom_label_3' => 'Custom label 3 [custom_label_3]',
				'custom_label_4' => 'Custom label 4 [custom_label_4]',
			),

			'Additional Attributes'               => array(
				'excluded_destination' => 'Excluded Destination [excluded_destination]',
				'expiration_date'      => 'Expiration Date [expiration_date]',
			),

			'Unit Prices (EU Countries and Switzerland Only)' => array(
				'unit_pricing_measure'      => 'Unit Pricing Measure [unit_pricing_measure]',
				'unit_pricing_base_measure' => 'Unit Pricing Base Measure [unit_pricing_base_measure]',
			),

			'Energy Labels'                       => array(
				'energy_efficiency_class' => 'Energy Efficiency Class [energy_efficiency_class]',
			),

			'Loyalty Points (Japan Only)'         => array(
				'loyalty_points' => 'loyalty_points [loyalty_points]',
			),

			'Multiple Installments (Brazil Only)' => array(
				'installment' => 'Installment [installment]',
			),

			'Merchant Promotions Attribute'       => array(
				'promotion_id' => 'Promotion Id [promotion_id]',
			),
		);

        for ( $i = 1; $i <= 10; $i++ ) {
            $this->attributes[ 'Params [param]' ][ "param_value_$i" ] = "Parameter Value {$i}";
            $this->attributes[ 'Params [param]' ][ "param_name_$i" ] = "Parameter Name {$i}";
        }

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

		);
	}
}
