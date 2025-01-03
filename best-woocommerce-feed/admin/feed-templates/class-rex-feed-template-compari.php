<?php
/**
 * The Become Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.7
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for become feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Compari
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Compari extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Fields'   => array(
				'Manufacturer'     => 'Manufacturer',
				'Name'             => 'Product Title',
				'Category'         => 'Category',
				'ProductUrl'       => 'Product URL',
				'Price'            => 'Price',
				'Identifier'       => 'Identifier',
			),

			'Recommended Fields' => array(
				'ProductNumber'           => 'Product Number',
				'Description'             => 'Product Description',
				'ImageUrl'                => 'Primary Image URL',
				'ImageUrl2'               => 'Secondary Image URL',
				'DeliveryCost'            => 'Shipping Cost',
				'EanCode'                 => 'EAN/UPC Code',
				'ShoppingDisabled'        => 'Unavailable for Purchase',
				'EnergyEfficiencyA-G'     => 'Energy Efficiency Details',
				'EnergyLabelA-G'          => 'Additional Energy Label Info',
				'DetailedSpecificationA-G'=> 'Detailed Specifications',
				'MaxCPCMultiplier'        => 'Ad CPC Multiplier',
				'NetPrice'                => 'Net Price (Excluding Tax)',
				'Color'                   => 'Product Color',
				'Size'                    => 'Size Options',
				'GroupId'                 => 'Product Variant Group ID',
				'Attributes'              => 'Custom Attributes',
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
				'attr'     => 'Manufacturer',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Name',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Category',
				'type'     => 'meta',
				'meta_key' => 'product_cats',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'ProductUrl',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'cdata',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Price',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Identifier',
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
