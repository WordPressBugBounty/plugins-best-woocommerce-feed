<?php
/**
 * The Wish Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 *
 * Defines the attributes and template for eBay feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Ebay
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Wish extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'   => array(
				'Unique Id'      => 'Unique Id',
				'Price'          => 'Price',
				'Product Name'   => 'Product Name',
				'Quantity'       => 'Quantity',
				'Shipping'       => 'Shipping',
				'Main Image URL' => 'Main Image URL',
				'Description'    => 'Description',

			),

			'Additional Information' => array(
				'Parent Unique Id'           => 'Parent Unique Id',
				'Size'                       => 'Size',
				'Color'                      => 'Color',
				'MSRP'                       => 'MSRP',
				'Brand'                      => 'Brand',
				'Landing Page URL'           => 'Landing Page URL',
				'Variation Image URL'        => 'Variation Image URL',
				'Clean Image URL'            => 'Clean Image URL',
				'Extra Image URL'            => 'Extra Image URL',
				'UPC'                        => 'UPC',
				'Tags'                       => 'Tags',
				'GTIN'                       => 'GTIN',
				'Is LTL'                     => 'Is LTL',
				'Shipping Time'              => 'Shipping Time',
				'Extra Image URL 1'          => 'Extra Image URL 1',
				'Extra Image URL 2'          => 'Extra Image URL 2',
				'Extra Image URL 3'          => 'Extra Image URL 3',
				'Extra Image URL 4'          => 'Extra Image URL 4',
				'Extra Image URL 5'          => 'Extra Image URL 5',
				'Extra Image URL 6'          => 'Extra Image URL 6',
				'Extra Image URL 7'          => 'Extra Image URL 7',
				'Extra Image URL 8'          => 'Extra Image URL 8',
				'Extra Image URL 9'          => 'Extra Image URL 9',
				'Extra Image URL 10'         => 'Extra Image URL 10',
				'Max Quantity'               => 'Max Quantity',
				'Product Brand Name'         => 'Product Brand Name',
				'Requested Product Brand ID' => 'Requested Product Brand ID',
				'Local Currency Code'        => 'Local Currency Code',
				'Localized Price'            => 'Localized Price',
				'Localized Shipping'         => 'Localized Shipping',
				'Localized Cost'             => 'Localized Cost',
				'Localized Shipping Cost'    => 'Localized Shipping Cost',
				'Demo Video Asset URL'       => 'Demo Video Asset URL',
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
				'attr'     => 'Unique Id',
				'type'     => 'meta',
				'meta_key' => 'sku',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Price',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Product Name',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Main Image URL',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Description',
				'type'     => 'meta',
				'meta_key' => 'description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Quantity',
				'type'     => 'meta',
				'meta_key' => 'quantity',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Shipping',
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
