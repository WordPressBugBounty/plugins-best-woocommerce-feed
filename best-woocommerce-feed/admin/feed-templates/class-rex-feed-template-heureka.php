<?php
/**
 * The eBay Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for eBay feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Ebay
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Heureka extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
    protected function init_atts()
    {
        $this->attributes = [
            'Required Information'   => [
                'ITEM_ID'       => 'ITEM ID',
                'PRODUCTNAME'   => 'Product name',
                'URL'           => 'Product URL',
                'IMGURL'        => 'Image URL',
                'PRICE_VAT'     => 'Price',
                'DESCRIPTION'   => 'Description',
                'DELIVERY_DATE' => 'delivery date'
            ],
            'Additional Information' => [
                'CATEGORYTEXT' => 'CATEGORY TEXT',
                'EAN'          => 'EAN',
                'ISBN'         => 'ISBN',
                'PRODUCTNO'    => 'PRODUCTNO',
                'ITEMGROUP_ID' => 'ITEMGROUP_ID',
                'MANUFACTURER' => 'MANUFACTURER',
                'EROTIC'       => 'EROTIC',
                'BRAND'        => 'BRAND',
                'PRODUCT'      => 'PRODUCT',
                'ITEM_TYPE'    => 'ITEM TYPE',
                'VIDEO_URL'    => 'PRODUCT',
                'SIZE'         => 'SIZE',
                'COLOR'        => 'COLOR',
                'GIFT'         => 'GIFT'
            ],
            'Attributes'             => [
                'Param_name_1'   => 'PARAM 1',
                'Param_value_1'  => 'Value 1',
                'Param_name_2'   => 'PARAM 2',
                'Param_value_2'  => 'Value 2',
                'Param_name_3'   => 'PARAM 3',
                'Param_value_3'  => 'Value 3',
                'Param_name_4'   => 'PARAM 4',
                'Param_value_4'  => 'Value 4',
                'Param_name_5'   => 'PARAM 5',
                'Param_value_5'  => 'Value 5',
                'Param_name_6'   => 'PARAM 6',
                'Param_value_6'  => 'Value 6',
                'Param_name_7'   => 'PARAM 7',
                'Param_value_7'  => 'Value 7',
                'Param_name_8'   => 'PARAM 8',
                'Param_value_8'  => 'Value 8',
                'Param_name_9'   => 'PARAM 9',
                'Param_value_9'  => 'Value 9',
                'Param_name_10'  => 'PARAM 10',
                'Param_value_10' => 'Value 10'
            ],
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
        ];

        for ( $i=1; $i<=20; $i++ ) {
            $this->attributes['Alternative Image URLs'][ "IMGURL_ALTERNATIVE_{$i}" ]  = "Alternative Image URL {$i}";
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
				'type'     => 'meta',
				'meta_key' => 'featured_image',
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
				'attr'     => 'DELIVERY_DATE',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			),

		);
	}
}
