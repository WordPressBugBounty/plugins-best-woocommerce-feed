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
class Rex_Feed_Template_Fruugo extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(

			'Required Information'   => array(
				'ProductId'             => 'Product ID',
				'SkuId'                 => 'SKU ID',
				'EAN'                   => 'GTINs (EAN / UPC)',
				'Brand'                 => 'Brand',
				'Category'              => 'Category',
				'Imageurl1'             => 'Image URL 1',
				'StockStatus'           => 'Stock Status',
				'StockQuantity'         => 'Quantity in Stock',
				'Title'                 => 'Title',
				'Description'           => 'Description',
				'NormalPriceWithoutVAT' => 'Normal Price Without VAT',
				'NormalPriceWithVAT'    => 'Normal Price With VAT',
				'VATRate'               => 'VAT Rate',
			),

			'Additional Information' => array(
				'Imageurl2'               => 'Image url 2',
				'Imageurl3'               => 'Image url 3',
				'Imageurl4'               => 'Image url 4',
				'Imageurl5'               => 'Image url 5',
				'Language'                => 'Language',
				'AttributeSize'           => 'Attribute Size',
				'AttributeColor'          => 'Attribute Color',
				'Currency'                => 'Currency',
				'DiscountPriceWithoutVAT' => 'Discount Price Without VAT',
				'DiscountPriceWithVAT'    => 'Discount Price With VAT',
			),
			'Optional Fields'        => array(
				'ISBN'                   => 'ISBN',
				'Manufacturer'           => 'Manufacturer',
				'RestockDate'            => 'Restock Date',
				'LeadTime'               => 'Lead Time',
				'PackageWeight'          => 'Package Weight',
				'Attribute1'             => 'Attribute 1',
				'Attribute2'             => 'Attribute 2',
				'Attribute3'             => 'Attribute 3',
				'Attribute4'             => 'Attribute 4',
				'Attribute5'             => 'Attribute 5',
				'Attribute6'             => 'Attribute 6',
				'Attribute7'             => 'Attribute 7',
				'Attribute8'             => 'Attribute 8',
				'Attribute9'             => 'Attribute 9',
				'Attribute10'            => 'Attribute 10',
				'Country'                => 'Country',
				'DiscountPriceStartDate' => 'Discount Price Start Date',
				'DiscountPriceEndDate'   => 'Discount Price End Date',
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
				'attr'     => 'ProductId',
				'type'     => 'meta',
				'meta_key' => 'id',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'SkuId',
				'type'     => 'meta',
				'meta_key' => 'sku',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'EAN',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Brand',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Category',
				'type'     => 'meta',
				'meta_key' => 'product_cats',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Imageurl1',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'StockStatus',
				'type'     => 'meta',
				'meta_key' => 'availability',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'StockQuantity',
				'type'     => 'meta',
				'meta_key' => 'quantity',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Title',
				'type'     => 'meta',
				'meta_key' => 'title',
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
				'attr'     => 'NormalPriceWithVAT',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'NormalPriceWithoutVAT',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'VATRate',
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
