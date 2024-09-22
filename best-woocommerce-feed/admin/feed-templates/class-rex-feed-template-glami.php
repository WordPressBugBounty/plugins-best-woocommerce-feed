<?php
/**
 * The Glami marketplace Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Glami marketplace feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Glami
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Glami extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'    => array(
				'ITEM_ID'      => 'ITEM_ID',
				'PRODUCTNAME'  => 'PRODUCTNAME',
				'URL'          => 'URL',
				'IMGURL'       => 'IMGURL',
				'PRICE_VAT'    => 'PRICE_VAT',
				'MANUFACTURER' => 'MANUFACTURER',
				'CATEGORYTEXT' => 'CATEGORYTEXT',
			),
			'Recommended Information' => array(
				'DESCRIPTION'        => 'DESCRIPTION',
				'EAN'                => 'EAN',
				'IMGURL_ALTERNATIVE' => 'IMGURL ALTERNATIVE',
				'ITEMGROUP_ID'       => 'ITEMGROUP_ID',
				'SIZE_SYSTEM'        => 'SIZE SYSTEM',
				'URL_SIZE'           => 'URL SIZE',
			),
			'Optional Information'    => array(
				'CATEGORY_ID'        => 'CATEGORY ID',
				'DELIVERY_ID'        => 'DELIVERY ID',
				'DELIVERY_PRICE'     => 'DELIVERY PRICE',
				'DELIVERY_PRICE_COD' => 'DELIVERY PRICE COD',
				'DELIVERY_DATE'      => 'DELIVERY DATE',
				'GLAMI_CPC '         => 'GLAMI CPC',
				'MATERIAL'           => 'MATERIAL',
				'PROMOTION_ID  	'    => 'PROMOTION ID',
			),
			'Params'                  => array(
				'PARAM_NAME_1'    => 'PARAM 1',
				'VALUE_1'         => 'Value 1',
				'PERCENTAGE_1'    => 'PERCENTAGE 1',
				'PARAM_NAME_2'    => 'PARAM 2',
				'VALUE_2'         => 'Value 2',
				'PERCENTAGE_2'    => 'PERCENTAGE 2',
				'PARAM_NAME_3'    => 'PARAM 3',
				'VALUE_3'         => 'Value 3',
				'PERCENTAGE_3'    => 'PERCENTAGE 3',
				'PARAM_NAME_4'    => 'PARAM 4',
				'VALUE_4'         => 'Value 4',
				'PERCENTAGE_4'    => 'PERCENTAGE 4',
				'PARAM_NAME_5'    => 'PARAM 5',
				'VALUE_5'         => 'Value 5',
				'PERCENTAGE_5'    => 'PERCENTAGE 5',
				'PARAM_NAME_6'    => 'PARAM 6',
				'VALUE_6'         => 'Value 6',
				'PERCENTAGE_6'    => 'PERCENTAGE 6',
				'PARAM_NAME_7'    => 'PARAM 7',
				'VALUE_7'         => 'Value 7',
				'PERCENTAGE_7'    => 'PERCENTAGE 7',
				'PARAM_NAME_8'    => 'PARAM 8',
				'VALUE_8'         => 'Value 8',
				'PERCENTAGE_8'    => 'PERCENTAGE 8',
				'PERCENTAGE_SIZE' => 'PARAM - SIZE',
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
				'attr'     => 'ITEM_ID',
				'type'     => 'meta',
				'meta_key' => 'id',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
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
				'attr'     => 'URL',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'cdata',
				'limit'    => 0,
			),
			array(
				'attr'     => 'IMGURL',
				'type'     => 'static',
				'meta_key' => '',
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
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'MANUFACTURER',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'CATEGORYTEXT',
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
