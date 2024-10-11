<?php
/**
 * The admarkt Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      4.1
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for admarkt feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Marktplaats
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Marktplaats extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = [
			'Required Fields' => [
				'vendorId'        => 'Vendor ID',
				'title'           => 'Product Title',
				'description'     => 'Description',
				'categoryId'      => 'Category Id',
				'url'             => 'Url',
				'vanityUrl'       => 'Vanity Url',
				'price'           => 'Product Price',
				'priceType'       => 'Price Type',
				'media'           => 'Media',
				'emailAdvertiser' => 'Email Advertiser'
			],
			'Budget'          => [
				'totalBudget' => 'Total Budget',
				'dailyBudget' => 'Daily Budget',
				'cpc'         => 'Cost Per Click'
			],
			'Shipping Options' => [
				'shippingType_1' => 'Shipping Type - 1',
				'cost_1'         => 'Shipping Cost - 1',
				'time_1'         => 'Shipping Time - 1',
				'location_1'     => 'Shipping Location - 1',
				'shippingType_2' => 'Shipping Type - 2',
				'cost_2'         => 'Shipping Cost - 2',
				'time_2'         => 'Shipping Time - 2',
				'location_2'     => 'Shipping Location - 2',
				'shippingType_3' => 'Shipping Type - 3',
				'cost_3'         => 'Shipping Cost - 3',
				'time_3'         => 'Shipping Time - 3',
				'location_3'     => 'Shipping Location - 3',
				'shippingType_4' => 'Shipping Type - 4',
				'cost_4'         => 'Shipping Cost - 4',
				'time_4'         => 'Shipping Time - 4',
				'location_4'     => 'Shipping Location - 4',
				'shippingType_5' => 'Shipping Type - 5',
				'cost_5'         => 'Shipping Cost - 5',
				'time_5'         => 'Shipping Time - 5',
				'location_5'     => 'Shipping Location - 5',
			],
            'Attributes' => [
                'attributeName_1' => 'Attribute Name - 1',
                'attributeLocale_1' => 'Attribute Locale - 1',
                'attributeLabel_1' => 'Attribute Label - 1',
                'attributeValue_1' => 'Attribute Value - 1',
                'attributeName_2' => 'Attribute Name - 2',
                'attributeLocale_2' => 'Attribute Locale - 2',
                'attributeLabel_2' => 'Attribute Label - 2',
                'attributeValue_2' => 'Attribute Value - 2',
                'attributeName_3' => 'Attribute Name - 3',
                'attributeLocale_3' => 'Attribute Locale - 3',
                'attributeLabel_3' => 'Attribute Label - 3',
                'attributeValue_3' => 'Attribute Value - 3',
                'attributeName_4' => 'Attribute Name - 4',
                'attributeLocale_4' => 'Attribute Locale - 4',
                'attributeLabel_4' => 'Attribute Label - 4',
                'attributeValue_4' => 'Attribute Value - 4',
                'attributeName_5' => 'Attribute Name - 5',
                'attributeLocale_5' => 'Attribute Locale - 5',
                'attributeLabel_5' => 'Attribute Label - 5',
                'attributeValue_5' => 'Attribute Value - 5',
            ],
		];
	}

	/**
	 * Define merchant's default attributes
	 *
	 * @return void
	 */
	protected function init_default_template_mappings() {
		$this->template_mappings = array(
			array(
				'attr'     => 'vendorId',
				'type'     => 'meta',
				'meta_key' => 'id',
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
				'attr'     => 'vanityUrl',
				'type'     => 'static',
				'meta_key' => '',
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
				'attr'     => 'priceType',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'media',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'totalBudget',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'dailyBudget',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'cpc',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'emailAdvertiser',
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
