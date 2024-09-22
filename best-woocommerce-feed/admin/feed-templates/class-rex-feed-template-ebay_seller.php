<?php
/**
 * Class Rex_Feed_Template_Ebay_seller
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * The eBay seller Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */
class Rex_Feed_Template_Ebay_seller extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'   => array(
				'*Action'                        => '*Action',
				'*Category'                      => '*Category',
				'*Title'                         => '*Title',
				'*Description'                   => '*Description',
				'*ConditionID'                   => '*ConditionID',
				'PicURL'                         => 'PicURL',
				'*Quantity'                      => '*Quantity',
				'*Format'                        => '*Format',
				'*StartPrice'                    => '*StartPrice',
				'BuyItNowPrice'                  => 'BuyItNowPrice',
				'*Location'                      => '*Location',
				'*Duration'                      => '*Duration',
				'ShippingType'                   => 'ShippingType',
				'ShippingService-1:Option'       => 'ShippingService-1:Option',
				'ShippingService-1:Cost'         => 'ShippingService-1:Cost',
				'ShippingService-1:FreeShipping' => 'ShippingService-1:FreeShipping',
				'DispatchTimeMax'                => 'DispatchTimeMax',
				'ReturnsAcceptedOption'          => 'ReturnsAcceptedOption',
				'RefundOption'                   => 'RefundOption',
				'ReturnsWithinOption'            => 'ReturnsWithinOption',
				'AdditionalDetails'              => 'AdditionalDetails',
			),

			'Additional Information' => array(
				'Subtitle'                                => 'Subtitle',
				'Product:Brand'                           => 'Brand',
				'Product:MPN'                             => 'Product:MPN',
				'Product:UPC'                             => 'Product:UPC',
				'Product:EAN'                             => 'Product:EAN',
				'Product:ISBN'                            => 'Product:ISBN',
				'Product:EPID'                            => 'Product:EPID',
				'Product:IncludePreFilledItemInformation' => 'Product:IncludePreFilledItemInformation',
				'Product:IncludeStockPhotoURL'            => 'Product:IncludeStockPhotoURL',
				'Product:ReturnSearchResultsOnDuplicates' => 'Product:ReturnSearchResultsOnDuplicates',
				'ImmediatePayRequired'                    => 'ImmediatePayRequired',
				'PayPalAccepted'                          => 'PayPalAccepted',
				'PayPalEmailAddress'                      => 'PayPalEmailAddress',
				'PaymentInstructions'                     => 'PaymentInstructions',
				'StoreCategory'                           => 'StoreCategory',
				'BuyItNowPrice'                           => 'BuyItNowPrice',
				'ShippingDiscountProfileID'               => 'ShippingDiscountProfileID',
				'DomesticRateTable'                       => 'DomesticRateTable',
				'ShippingService-1:Priority'              => 'ShippingService-1:Priority',
				'ShippingService-1:ShippingSurcharge'     => 'ShippingService-1:ShippingSurcharge',
				'ShippingService-2:Option'                => 'ShippingService-2:Option',
				'ShippingService-2:Cost'                  => 'ShippingService-2:Cost',
				'ShippingService-2:Priority'              => 'ShippingService-2:Priority',
				'ShippingService-2:ShippingSurcharge'     => 'ShippingService-2:ShippingSurcharge',
				'GalleryType'                             => 'GalleryType',
				'CustomLabel'                             => 'CustomLabel',
				'ShippingCostPaidByOption'                => 'ShippingCostPaidByOption',
				'ShippingProfileName'                     => 'ShippingProfileName',
				'ReturnProfileName'                       => 'ReturnProfileName',
				'PaymentProfileName'                      => 'PaymentProfileName',
				'Attribute'                               => 'Attribute',
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
				'attr'     => '*Action',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*Category',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*Title',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*Description',
				'type'     => 'meta',
				'meta_key' => 'description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*ConditionID',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'PicURL',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*Quantity',
				'type'     => 'meta',
				'meta_key' => 'quantity',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*Format',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*StartPrice',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'BuyItNowPrice',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*Location',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*Duration',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'ShippingType',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'ShippingService-1:Option',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'ShippingService-1:Cost',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'DispatchTimeMax',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'ReturnsAcceptedOption',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'RefundOption',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'ReturnsWithinOption',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'AdditionalDetails',
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
