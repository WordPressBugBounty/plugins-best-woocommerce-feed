<?php
/**
 * The Bing Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Ibud feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Mirakl
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Mirakl extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
    protected function init_atts() {
        $this->attributes = [
            'Required Attributes' => [
                'sku'                   => 'SKU',
                'product-id'            => 'Product Id',
                'product-id-type'       => 'Product Id type',
                'description'           => 'Description',
                'internal-description'  => 'Internal description',
                'price'                 => 'Price',
                'price-additional-info' => 'Price additional info',
                'quantity'              => 'Quantity',
                'min-quantity-alert'    => 'Min quantity alert',
                'state'                 => 'State',
                'available-start-date'  => 'Available Start date',
                'available-end-date'    => 'Available End date',
                'discount-start-date'   => 'Discount Start date',
                'discount-end-date'     => 'Discount End date',
                'discount-price'        => 'Discount Price',
                'update-delete'         => 'Update delete',
            ],
            'Additional Attributes' => [
                'logistic-class'   => 'Logistic Class',
                'leadtime-to-ship' => 'Lead Time to Ship',
            ],
        ];

        for( $index = 1; $index <= 5; $index++ ) {
            $this->attributes[ "Channel {$index} Prices" ] = [
                "channel-code-{$index}"   => 'Channel Code',
                "price-{$index}" => 'Price',
                "discount-price-{$index}" => 'Discount Price',
                "discount-start-date-{$index}" => 'Discount Start Date',
                "discount-end-date-{$index}" => 'Discount End Date',
            ];
        }

        for( $index = 1; $index <= 15; $index++ ) {
            $this->attributes[ 'Offer Additional Attributes' ][ "offer_additional_field_code_{$index}" ]  = "Offer Additional Field Code {$index}";
            $this->attributes[ 'Offer Additional Attributes' ][ "offer_additional_field_value_{$index}" ] = "Offer Additional Field Value {$index}";
            $this->attributes[ 'Product Attributes' ][ "attribute_code_{$index}" ]                        = "Attribute Code {$index}";
            $this->attributes[ 'Product Attributes' ][ "attribute_value_{$index}" ]                       = "Attribute Value {$index}";
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
				'attr'     => 'product-id',
				'type'     => 'meta',
				'meta_key' => 'id',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'product-id-type',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'description',
				'type'     => 'meta',
				'meta_key' => 'description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'internal-description',
				'type'     => 'meta',
				'meta_key' => '',
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
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'price-additional-info',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'quantity',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'min-quantity-alert',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'state',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'available-start-date',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'available-end-date',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'discount-start-date',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'discount-end-date',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'discount-price',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'update-delete',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),

		);
	}
}
