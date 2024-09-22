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
 * Defines the attributes and template for idealo feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Idealo
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Idealo_de extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'          => array(
				'sku'                    => 'SKU [sku]',
				'title'                  => 'Title [title]',
				'price'                  => 'Price [price]',
				'deliveryTime'           => 'Delivery Time [deliveryTime]',
				'url'                    => 'Product URL [url]',
				'checkout'               => 'Checkout [checkout]',
				'fulfillmentType'        => 'Fulfillment Type [fulfillmentType]',
				'checkoutLimitPerPeriod' => 'Checkout Limit [checkoutLimitPerPeriod]',
				'maxOrderProcessingTime' => 'Processing Time [Max] [maxOrderProcessingTime]',
			),
			'Optional Information'          => array(
				'eans'              => 'EANS [eans]',
				'hans'              => 'HANS [hans]',
				'brand'             => 'Brand [brand]',
				'imageUrls'         => 'Image URL [imageUrls]',
				'colour'            => 'Colour [colour]',
				'size'              => 'Size [size]',
				'basePrice'         => 'Base Price [basePrice]',
				'categoryPath'      => 'Category [categoryPath]',
				'description'       => 'Description [description]',
				'deliveryComment'   => 'Delivery Comment [deliveryComment]',
				'voucherCode'       => 'Voucher Code [voucherCode]',
				'discountPrice'     => 'Discount Price [discountPrice]',
				'freeReturnDays'    => 'Free Return Days [freeReturnDays]',
				'minimumPrice'      => 'Minimum Price [minimumPrice]',
				'twoManHandlingFee' => 'Handling Fee [Two Man] [twoManHandlingFee]',
				'disposalFee'       => 'Disposal Fee [disposalFee]',
				'gender'            => 'Gender [gender]',
				'material'          => 'Material [material]',
				'eec'               => 'EEC [eec]',
				'merchantName'      => 'Merchant Name [merchantName]',
				'merchantId'        => 'Merchant ID [merchantId]',
				'formerPrice'       => 'Former Price [formerPrice]',
				'deposit'           => 'Deposit [deposit]',
				'quantityPerOrder'  => 'Quantity [Per Order] [quantityPerOrder]',
				'packagingUnit'     => 'Packaging Unit [packagingUnit]',
				'used'              => 'Used [used]',
				'download'          => 'Download [download]',
				'replica'           => 'Replica [replica]',
			),
			'Energy Label Information'      => array(
				'EEC_efficiencyClass' => 'EEC Efficiency Class [EEC_efficiencyClass]',
				'EEC_spectrum'        => 'EEC Spectrum [EEC_spectrum]',
				'EEC_labelUrl'        => 'EEC Label URL [EEC_labelUrl]',
				'EEC_dataSheetUrl'    => 'EEC Datasheet URL [EEC_dataSheetUrl]',
				'EEC_version'         => 'EEC Version [EEC_version]',
			),
			'Car Part Specific Information' => array(
				'oens' => 'OENS [oens]',
				'kbas' => 'KBAS [kbas]',
			),
			'Lenses Specific Information'   => array(
				'diopter'   => 'Diopter [diopter]',
				'baseCurve' => 'Base Curve [baseCurve]',
				'diameter'  => 'Diameter [diameter]',
				'axis'      => 'Axis [axis]',
				'addition'  => 'Addition [addition]',
				'cylinder'  => 'Cylinder [cylinder]',
			),
			'Speaker Specific Information'  => array(
				'quantity' => 'Quantity [quantity]',
			),
			'Wine Specific Information'     => array(
				'alcoholicContent'    => 'Alcoholic Content [alcoholicContent]',
				'allergenInformation' => 'Allergen Information [allergenInformation]',
				'countryOfOrigin'     => 'Country of Origin [countryOfOrigin]',
				'quantity'            => 'Quantity [quantity]',
				'bottler'             => 'Bottler [bottler]',
				'importer'            => 'Importer [importer]',
			),
			'Tyre Specific Information'     => array(
				'fuelEfficiencyClass'       => 'Fuel Efficiency Class [fuelEfficiencyClass]',
				'wetGripClass'              => 'Wet Grip Class [wetGripClass]',
				'externalRollingNoise'      => 'External Rolling Noise [externalRollingNoise]',
				'externalRollingNoiseClass' => 'External Rolling Noise Class [externalRollingNoiseClass]',
				'iceGrip'                   => 'Ice Grip [iceGrip]',
				'snowGrip'                  => 'Snow Grip [snowGrip]',
				'EEC_labelUrl'              => 'EEC Label URL [EEC_labelUrl]',
				'EEC_dataSheetUrl'          => 'EEC Datasheet URL [EEC_dataSheetUrl]',
			),
			'Medical Specific Information'  => array(
				'pzns' => 'PZNS [pzns]',
			),
			'Payment Costs Information'     => array(
				'paymentCosts_paypal'                    => 'Payment Costs [Paypal] [paymentCosts_paypal]',
				'paymentCosts_credit_card'               => 'Payment Costs [Credit Card] [paymentCosts_credit_card]',
				'paymentCosts_cash_in_advance'           => 'Payment Costs [Cash in Advance] [paymentCosts_cash_in_advance]',
				'paymentCosts_cash_on_delivery'          => 'Payment Costs [Cash on Delivery] [paymentCosts_cash_on_delivery]',
				'paymentCosts_cash_direct_debit'         => 'Payment Costs [Direct Debit] [paymentCosts_cash_direct_debit]',
				'paymentCosts_giropay'                   => 'Payment Costs [Giropay] [paymentCosts_giropay]',
				'paymentCosts_google_checkout'           => 'Payment Costs [Google Checkout] [paymentCosts_google_checkout]',
				'paymentCosts_google_invoice'            => 'Payment Costs [Invoice] [paymentCosts_google_invoice]',
				'paymentCosts_google_postal_order'       => 'Payment Costs [Postal Order] [paymentCosts_google_postal_order]',
				'paymentCosts_google_paysafecard'        => 'Payment Costs [Paysafecard] [paymentCosts_google_paysafecard]',
				'paymentCosts_google_sofortueberweisung' => 'Payment Costs [Sofortueberweisung] [paymentCosts_google_sofortueberweisung]',
				'paymentCosts_google_amazon_payment'     => 'Payment Costs [Amazon payment] [paymentCosts_google_amazon_payment]',
				'paymentCosts_electronical_payment_standard' => 'Payment Costs [Electronical Payment Standard] [paymentCosts_electronical_payment_standard]',
				'paymentCosts_ecotax'                    => 'Payment Costs [Ecotax] [paymentCosts_ecotax]',
			),
			'Delivery Costs Information'    => array(
				'deliveryCosts_dhl'                      => 'Delivery Costs [DHL] [deliveryCosts_dhl]',
				'deliveryCosts_dhl_go_green'             => 'Delivery Costs [DHL Go Green] [deliveryCosts_dhl_go_green]',
				'deliveryCosts_fedex'                    => 'Delivery Costs [FedEx] [deliveryCosts_fedex]',
				'deliveryCosts_deutsche_post'            => 'Delivery Costs [Deutsche Post] [deliveryCosts_deutsche_post]',
				'deliveryCosts_download'                 => 'Delivery Costs [download] [deliveryCosts_download]',
				'deliveryCosts_dpd'                      => 'Delivery Costs [DPD] [deliveryCosts_dpd]',
				'deliveryCosts_german_express_logistics' => 'Delivery Costs [German Express Logistics] [deliveryCosts_german_express_logistics]',
				'deliveryCosts_gls'                      => 'Delivery Costs [GLS] [deliveryCosts_gls]',
				'deliveryCosts_gls_think_green'          => 'Delivery Costs [GLS Think Green] [deliveryCosts_gls_think_green]',
				'deliveryCosts_hermes'                   => 'Delivery Costs [Hermes] [deliveryCosts_hermes]',
				'deliveryCosts_pick_point'               => 'Delivery Costs [Pick Point] [deliveryCosts_pick_point]',
				'deliveryCosts_spedition'                => 'Delivery Costs [Spedition] [deliveryCosts_spedition]',
				'deliveryCosts_tnt'                      => 'Delivery Costs [TNT] [deliveryCosts_tnt]',
				'deliveryCosts_trans_o_flex'             => 'Delivery Costs [Trans O Flex] [deliveryCosts_trans_o_flex]',
				'deliveryCosts_ups'                      => 'Delivery Costs [UPS] [deliveryCosts_ups]',
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
				'attr'     => 'sku',
				'type'     => 'meta',
				'meta_key' => 'sku',
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
				'attr'     => 'deliveryTime',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'deliveryCosts_dhl',
				'type'     => 'static',
				'meta_key' => '',
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
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'eans',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'hans',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'brand',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'imageUrls',
				'type'     => 'meta',
				'meta_key' => 'main_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'colour',
				'type'     => 'meta',
				'meta_key' => 'bwf_attr_pa_color',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'size',
				'type'     => 'meta',
				'meta_key' => 'bwf_attr_pa_size',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'basePrice',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'checkout',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'fulfillmentType',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'checkoutLimitPerPeriod',
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
