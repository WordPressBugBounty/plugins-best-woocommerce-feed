<?php
/**
 * The PriceRunner Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      3.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for Pricerunner feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Pricerunner
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Pricerunner extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
        $this->attributes = array(
            'Required Information'   => [
                'ProductId'    => 'Product ID [ProductId]',
                'ProductName'  => 'Product name [ProductName]',
                'Price'        => 'Price [Price]',
                'ShippingCost' => 'Shipping Cost [ShippingCost]',
                'StockStatus'  => 'Stock Status [StockStatus]',
                'LeadTime'     => 'Delivery Time [LeadTime]',
                'Brand'        => 'Manufacturer [Brand]',
                'Msku'         => 'Manufacturer SKU [Msku]',
                'Ean'          => 'GTIN/EAN [Ean]',
                'Url'          => 'Product URL [Url]',
                'ImageUrl'     => 'Image URL [ImageUrl]',
                'Category'     => 'Product Category [Category]',
                'Description'  => 'Description [Description]'
            ],
            'Additional Information' => [
                'AdultContent'          => 'Adult Content [AdultContent]',
                'AgeGroup'              => 'Age Group [AgeGroup]',
                'Bundled'               => 'Bundled [Bundled]',
                'Color'                 => 'Color [Color]',
                'EnergyEfficiencyClass' => 'Energy Efficiency Class [EnergyEfficiencyClass]',
                'Gender'                => 'Gender [Gender]',
                'Condition'             => 'Condition [Condition]',
                'GroupId'               => 'Group ID [GroupId]',
                'Material'              => 'Material [Material]',
                'Multipack'             => 'Multipack [Multipack]',
                'Pattern'               => 'Pattern [Pattern]',
                'Size'                  => 'Size [Size]',
                'SizeSystem'            => 'Size System [SizeSystem]'
            ],
        );
	}

	/**
	 * Define merchant's default attributes
	 *
	 * @return void
	 */
	protected function init_default_template_mappings() {
		$this->template_mappings = [
            [
                'attr'     => 'ProductId',
                'type'     => 'meta',
                'meta_key' => 'id',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'ProductName',
                'type'     => 'meta',
                'meta_key' => 'title',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'cdata',
                'limit'    => 0
            ],
            [
                'attr'     => 'Price',
                'type'     => 'meta',
                'meta_key' => 'price',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'ShippingCost',
                'type'     => 'static',
                'meta_key' => '',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'StockStatus',
                'type'     => 'meta',
                'meta_key' => 'availability_underscore',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'LeadTime',
                'type'     => 'static',
                'meta_key' => '1-3 days',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'Brand',
                'type'     => 'static',
                'meta_key' => '',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'Msku',
                'type'     => 'meta',
                'meta_key' => 'sku',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'Ean',
                'type'     => 'meta',
                'meta_key' => '',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'Url',
                'type'     => 'meta',
                'meta_key' => 'link',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => ' ',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'ImageUrl',
                'type'     => 'meta',
                'meta_key' => 'featured_image',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0
            ],
            [
                'attr'     => 'Category',
                'type'     => 'meta',
                'meta_key' => 'product_cats',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'cdata',
                'limit'    => 0
            ],
            [
                'attr'     => 'Description',
                'type'     => 'meta',
                'meta_key' => 'description',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'cdata',
                'limit'    => 0
            ]
        ];
	}
}
