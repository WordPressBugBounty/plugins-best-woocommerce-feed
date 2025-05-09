<?php
/**
 * The Uvinum Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.7
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 *
 * Defines the attributes and template for zbozi feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Zbozi
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Zbozi extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'    => array(
				'PRODUCTNAME'   => 'Product Name',
				'DESCRIPTION'   => 'Product Description',
				'URL'           => 'Product URL',
				'PRICE_VAT'     => 'Product Price',
				'DELIVERY_DATE' => 'Delivery Date',
			),
			'Recommended Information' => array(
				'CATEGORYTEXT'          => 'Product Category',
				'ITEM_ID'               => 'Product ID',
				'IMGURL'                => 'Product image URL',
				'EAN'                   => 'EAN',
				'ISBN'                  => 'ISBN',
				'PRODUCTNO'             => 'MPN',
				'ITEMGROUP_ID'          => 'Product Group ID',
				'MANUFACTURER'          => 'Product Manufacturer',
				'EROTIC'                => 'Special Offer',
				'EXTRA_MESSAGE'         => 'Additional Information',
                'IMGURL_ALTERNATIVE'    => 'Alternative product Image URL',
                'DELIVERY'              => 'Delivery item information',
                'PRICE_BEFORE_DISCOUNT' => 'Price before discount',
                'CONDITION'             => 'Product condition',
                'CONDITION_DESC'        => 'Product condition description',
                'WARRANTY'              => 'Product warranty',
                'PRODUCTNO'             => 'Product number',
			),

			'Additional Information'  => array(
				'CUSTOM_LABEL_0' => 'CUSTOM LABEL 0',
				'CUSTOM_LABEL_1' => 'CUSTOM LABEL 1',
				'CUSTOM_LABEL_2' => 'CUSTOM LABEL 2',
				'BRAND'          => 'Brand',
				'SHOP_DEPOTS'    => 'Delivery sites',
				'VISIBILITY'     => 'Product Visibility',
				'MAX_CPC'        => 'Maximum cost per click',
				'MAX_CPC_SEARCH' => 'Maximum CPC for Offers',
				'LIST_PRICE'     => 'Recommended retail Price',
				'RELEASE_DATE'   => 'Sale Date',
			),

            'Delivery Attributes'    => [
                'Delivery_id_1'        => 'Delivery ID 1',
                'Delivery_price_1'     => 'Delivery Price 1',
                'Delivery_price_cod_1' => 'Delivery Price COD 1',
                'Delivery_id_2'        => 'Delivery ID 2',
                'Delivery_price_2'     => 'Delivery Price 2',
                'Delivery_price_cod_2' => 'Delivery Price COD 2',
                'Delivery_id_3'        => 'Delivery ID 3',
                'Delivery_price_3'     => 'Delivery Price 3',
                'Delivery_price_cod_3' => 'Delivery Price COD 3',
                'Delivery_id_4'        => 'Delivery ID 4',
                'Delivery_price_4'     => 'Delivery Price 4',
                'Delivery_price_cod_4' => 'Delivery Price COD 4'
            ]

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
				'attr'     => 'PRODUCTNAME',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'DESCRIPTION',
				'type'     => 'meta',
				'meta_key' => 'description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'URL',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'PRICE_VAT',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'DELIVERY_DATE',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			)
		);
	}
}
