<?php
/**
 * The Yandex Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.7
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for yandex feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Yandex
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Yandex extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Yandex Information' => array(
				'name'                  => 'Product name',
				'id'                    => 'Product ID',
				'url'                   => 'Product URL',
				'price'                 => 'Product price',
				'currencyId'            => 'Currency Id',
				'categoryId'            => 'Category Id',
				'picture'               => 'Product Image',
				'delivery'              => 'Delivery',
				'type'                  => 'Type',
				'available'             => 'Available',
				'model'                 => 'Model',
				'vendor'                => 'Vendor',
				'vendorCode'            => 'Vendor Code',
				'bid'                   => 'bid',
				'cbid'                  => 'cbid',
				'market_category'       => 'Market category',
				'oldprice'              => 'Old price',
				'pickup'                => 'Pickup',
				'store'                 => 'Store',
				'outlets'               => 'Outlets',
				'description'           => 'Description',
				'sales_notes'           => 'Sales notes',
				'manufacturer_warranty' => 'Manufacturer warranty',
				'country_of_origin'     => 'country of origin',
				'adult'                 => 'Adult',
				'age'                   => 'Age',
				'barcode'               => 'Barcode',
				'cpa'                   => 'CPA',
				'expiry'                => 'Expiry',
				'weight'                => 'Weight',
				'dimensions'            => 'Dimensions',
				'downloadable'          => 'Downloadable',
				'item_group_id'         => 'Group id',
			),
			'Product Parameters' => $this->generate_param_attributes( 100 ),
		);
	}

	/**
	 * Generate param attributes dynamically
	 *
	 * @param int $count Number of param sets to generate.
	 * @return array
	 * @since 7.4.58
	 */
	private function generate_param_attributes( $count = 100 ) {
		$params = array();
		for ( $i = 1; $i <= $count; $i++ ) {
			$params[ "Param_name_{$i}" ]  = "Param {$i} Name (Static)";
			$params[ "Param_value_{$i}" ] = "Param {$i} Value (Attribute)";
			$params[ "Param_unit_{$i}" ]  = "Param {$i} Unit (Static)";
		}
		return $params;
	}

	/**
	 * Define merchant's default attributes
	 *
	 * @return void
	 */
	protected function init_default_template_mappings() {
		$this->template_mappings = array(
			array(
				'attr'     => 'name',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
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
				'attr'     => 'url',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'cdata',
				'limit'    => 0,
			),
			array(
				'attr'     => 'price',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'categoryId',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'picture',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'model',
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
