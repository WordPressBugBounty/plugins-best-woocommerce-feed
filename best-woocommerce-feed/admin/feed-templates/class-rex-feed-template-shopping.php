<?php
/**
 * The Shopping Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for shopping feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Shopping
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Shopping extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'    => array(
				'Category'           => 'Category',
				'Current_Price'      => 'Current Price',
				'Image_URL'          => 'Image URL',
				'Product_Name'       => 'Product Name',
				'Product_URL'        => 'Product_URL',
				'SKU'                => 'SKU',
				'Shipping_Rate'      => 'Shipping Rate',
				'Stock_Availability' => 'Stock Availability',
			),

			'Recommended Information' => array(
				'Age_Range'                     => 'Age Range',
				'Brand'                         => 'Brand',
				'Category_ID'                   => 'Category ID',
				'Colour'                        => 'Colour',
				'Condition'                     => 'Condition',
				'Coupon_Code'                   => 'Coupon Code',
				'Coupon_Code_Description'       => 'Coupon Code Description',
				'EAN'                           => 'EAN',
				'Estimated_Ship_Date'           => 'Estimated Ship Date',
				'Gender'                        => 'Gender',
				'ISBN'                          => 'ISBN',
				'MPN'                           => 'MPN',
				'Material'                      => 'Material',
				'Mobile_Phone_Service_Provider' => 'Mobile Phone Service Provider',
				'Parent_Name'                   => 'Parent Name',
				'Product_Description'           => 'Product_Description',
				'Product_Type'                  => 'Product_Type',
				'Size'                          => 'Size',
				'Size_Unit_Of_Measurement'      => 'Size Unit Of Measurement',
				'Stock_Description'             => 'Stock Description',
				'Top_Seller_Rank'               => 'Top Seller Rank',
				'UPC'                           => 'UPC',
			),

			'Optional Information'    => array(
				'Alternative_Image_URL_1' => 'Alternative Image URL 1',
				'Alternative_Image_URL_2' => 'Alternative Image URL 2',
				'Alternative_Image_URL_3' => 'Alternative Image URL 3',
				'Alternative_Image_URL_4' => 'Alternative Image URL 4',
				'Alternative_Image_URL_5' => 'Alternative Image URL 5',
				'Bundle'                  => 'Bundle',
				'Custom'                  => 'Custom',
				'Format'                  => 'Format',
				'Merchandising_Type'      => 'Merchandising Type',
				'Mobile_Phone_Plan_Type'  => 'Mobile Phone Plan Type',
				'Mobile_URL'              => 'Mobile URL',
				'Product_Bullet_Point_1'  => 'Product Bullet Point 1',
				'Product_Bullet_Point_2'  => 'Product Bullet Point 2',
				'Product_Bullet_Point_3'  => 'Product Bullet Point 3',
				'Product_Bullet_Point_4'  => 'Product Bullet Point 4',
				'Product_Bullet_Point_5'  => 'Product Bullet Point 5',
				'Product_Launch_Date'     => 'Product Launch Date',
				'Product_Weight'          => 'Product Weight',
				'Related_Products'        => 'Related Products',
				'Software_Platform'       => 'Software Platform',
				'Unit_Price'              => 'Unit_Price',
				'Watch_Display_Type'      => 'Watch Display Type',
				'Weight_Unit_Of_Measure'  => 'Weight Unit Of Measure',
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
				'attr'     => 'Current_Price',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Image_URL',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Product_Name',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'cdata',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Product_URL',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'SKU',
				'type'     => 'meta',
				'meta_key' => 'sku',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
		);
	}
}
