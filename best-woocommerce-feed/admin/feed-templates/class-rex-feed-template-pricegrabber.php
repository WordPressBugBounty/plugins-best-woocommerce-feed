<?php
/**
 * The Pricegrabber Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for pricegrabber feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Pricegrabber
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Pricegrabber extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = [
			'Required Information' => [
				'Retsku'               => 'Retsku',
				'Product Title'        => 'Product Title',
				'Detailed Description' => 'Detailed Description',
				'Product URL'          => 'Product URL',
				'Primary Image URL'    => 'Primary Image URL',
				'Regular Price'        => 'Regular Price',
				'Selling Price'        => 'Selling Price',
				'Condition'            => 'Condition',
				'Manufacturer Name'    => 'Manufacturer Name',
			],

			'Additional Information' => [
				'Categorization	'       => 'Categorization',
				'UPC'                      => 'UPC',
				'EAN'                      => 'EAN',
				'Manufacturer Part Number' => 'Manufacturer Part Number',
				'Alternate Image URL 1'    => 'Alternate Image URL',
				'ISBN'                     => 'ISBN',
				'Availability'             => 'Availability',
				'Video URL'                => 'Video URL',
				'Color'                    => 'Color',
				'Size'                     => 'Size',
				'Gender'                   => 'Gender',
				'Age'                      => 'Age',
				'Material'                 => 'Material',
				'Shipping Cost'            => 'Shipping Cost',
				'Weight'                   => 'Weight',
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
				'attr'     => 'Retsku',
				'type'     => 'meta',
				'meta_key' => 'sku',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Product Title',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Detailed Description',
				'type'     => 'meta',
				'meta_key' => 'description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Regular Price',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Selling Price',
				'type'     => 'meta',
				'meta_key' => 'sale_price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Product URL',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'cdata',
				'limit'    => 0,
			],
			[
				'attr'     => 'Primary Image URL',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Manufacturer Name',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Condition',
				'type'     => 'meta',
				'meta_key' => 'condition',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
		];
	}
}
