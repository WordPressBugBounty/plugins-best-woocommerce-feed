<?php
/**
 * Class Rex_Feed_Template_Ebay_seller_tickets
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * The eBay seller Feed Template class
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */
class Rex_Feed_Template_Ebay_seller_tickets extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'   => array(
				'*Action'                        => '*Action',
				'*C:Event'                       => '*C:Event',
				'*C:Venue'                       => '*C:Venue',
				'C:Time'                         => 'C:Time',
				'C:Event Type'                   => 'C:Event Type',
				'C:Venue City'                   => 'C:Venue City',
				'*C:Venue State/Province'        => '*C:Venue State/Province',
				'*C:Number of tickets'           => '*C:Number of tickets',
				'*Quantity'                      => '*Quantity',
				'*Cost'                          => '*Cost',
				'*Location'                      => '*Location',
				'Description'                    => 'Description',
				'PicURL'                         => 'PicURL',
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
				'C:Event Date'               => 'C:Event Date',
				'C:Section'                  => 'C:Section',
				'C:Row'                      => 'C:Row',
				'Subtitle'                   => 'Subtitle',
				'ImmediatePayRequired'       => 'ImmediatePayRequired',
				'GalleryType'                => 'GalleryType',
				'PayPalEmailAddress'         => 'PayPalEmailAddress',
				'PaymentInstructions'        => 'PaymentInstructions',
				'StoreCategory'              => 'StoreCategory',
				'ShippingDiscountProfileID'  => 'ShippingDiscountProfileID',
				'DomesticRateTable'          => 'DomesticRateTable',
				'ShippingService-1:Priority' => 'ShippingService-1:Priority',
				'CustomLabel'                => 'CustomLabel',
				'ShippingCostPaidByOption'   => 'ShippingCostPaidByOption',
				'ShippingProfileName'        => 'ShippingProfileName',
				'ReturnProfileName'          => 'ReturnProfileName',
				'PaymentProfileName'         => 'PaymentProfileName',
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
				'attr'     => '*C:Event',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*C:Venue',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'C:Time',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'C:Event Type',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'C:Venue City',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*C:Venue State/Province',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => '*C:Number of tickets',
				'type'     => 'static',
				'meta_key' => '',
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
				'attr'     => '*Cost',
				'type'     => 'meta',
				'meta_key' => 'price',
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
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Description',
				'type'     => 'meta',
				'meta_key' => 'description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
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
				'attr'     => 'ShippingService-1:FreeShipping',
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
