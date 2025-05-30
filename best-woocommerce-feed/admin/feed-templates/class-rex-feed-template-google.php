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
class Rex_Feed_Template_Google extends Rex_Feed_Abstract_Template {

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
				"availability_date"         => "Availability Date[availability_date]",
				'price'                     => 'Regular Price [price]',
				'sale_price'                => 'Sale Price [sale_price]',
				'sale_price_effective_date' => 'Sale Price Effective Date [sale_price_effective_date]',
				"cost_of_goods_sold"        => "Cost of Goods Sold[cost_of_goods_sold]",
				"expiration_date"           => "Expiration Date[expiration_date]",
				"inventory"                 => "Facebook Inventory[inventory]",
				"override"                  => "Facebook Override[override]",
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
				'shipping'         => 'Shipping [shipping]',
				'shipping_country' => 'Shipping Country',
				'shipping_region'  => 'Shipping Region',
				'shipping_service' => 'Shipping Service',
				'shipping_price'   => 'Shipping Price',
				'shipping_weight'  => 'Shipping Weight [shipping_weight]',
				'shipping_length'  => 'Shipping Length [shipping_length]',
				'shipping_width'   => 'Shipping Width [shipping_width]',
				'shipping_height'  => 'Shipping Height [shipping_height]',
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
				"included_destination" => "Included Destination[included_destination]",
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

			'Certificate Attributes'    => [
                'certification_authority_1'        => 'Certification Authority 1',
                'certification_name_1'     => 'Certification Name 1',
                'certification_code_1' => 'Certification Code 1',
                'certification_authority_2'        => 'Certification Authority 2',
                'certification_name_2'     => 'Certification Name 2',
                'certification_code_2' => 'Certification Code 2',
                'certification_authority_3'        => 'Certification Authority 3',
                'certification_name_3'     => 'Certification Name 3',
                'certification_code_3' => 'Certification Code 3',
                'certification_authority_4'        => 'Certification Authority 4',
                'certification_name_4'     => 'Certification Name 4',
                'certification_code_4' => 'Certification Code 4',
                'certification_authority_5'        => 'Certification Authority 5',
                'certification_name_5'     => 'Certification Name 5',
                'certification_code_5' => 'Certification Code 5',
            ]
		);

        for ( $i = 1; $i <= 10; $i++ ) {
            $this->attributes[ 'Product Highlights' ][ "product_highlight_$i" ] = "Product Highlight {$i}";
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
				'escape'   => array( 'strip_tags', 'remove_shortcodes' ),
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
				'attr'     => 'product_type',
				'type'     => 'meta',
				'meta_key' => 'product_cats',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'google_product_category',
				'type'     => 'meta',
				'meta_key' => 'wpfm_google_product_category_default',
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
				'attr'     => 'availability',
				'type'     => 'meta',
				'meta_key' => 'availability',
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
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'brand',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => wpfm_get_woocommerce_shop_name(),
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'mpn',
				'type'     => 'meta',
				'meta_key' => 'sku',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'identifier_exists',
				'type'     => 'meta',
				'meta_key' => 'identifier_exists',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
		);
	}
}
