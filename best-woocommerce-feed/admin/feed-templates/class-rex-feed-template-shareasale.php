<?php
/**
 * The ShareASale Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for ShareASale feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Shareasale
 * @author     RexTheme
 */
class Rex_Feed_Template_Shareasale extends Rex_Feed_Abstract_Template {

    /**
     * Define merchant's required and optional/additional attributes
     *
     * @return void
     */
    protected function init_atts() {
        $this->attributes = array(
            'Feed Fields' => array(
                'SKU'               => 'SKU',
                'Name'              => 'Name',
                'URL'               => 'URL',
                'Price'             => 'Price',
                'RetailPrice'       => 'RetailPrice',
                'FullImage'         => 'FullImage',
                'ThumbnailImage'    => 'ThumbnailImage',
                'Commission'        => 'Commission',
                'Category'          => 'Category',
                'Subcategory'       => 'Subcategory',
                'Description'       => 'Description',
                'SearchTerms'       => 'SearchTerms',
                'Status'            => 'Status',
                'MerchantID'        => 'MerchantID',
                'Custom1'           => 'Custom1',
                'Custom2'           => 'Custom2',
                'Custom3'           => 'Custom3',
                'Custom4'           => 'Custom4',
                'Custom5'           => 'Custom5',
                'Manufacturer'      => 'Manufacturer',
                'PartNumber'        => 'PartNumber',
                'MerchantCategory'  => 'MerchantCategory',
                'MerchantSubcategory' => 'MerchantSubcategory',
                'ShortDescription'  => 'ShortDescription',
                'ISBN'              => 'ISBN',
                'UPC'               => 'UPC',
                'CrossSell'         => 'CrossSell',
                'MerchantGroup'     => 'MerchantGroup',
                'MerchantSubGroup'  => 'MerchantSubGroup',
                'CompatibleWith'    => 'CompatibleWith',
                'CompareTo'         => 'CompareTo',
                'QuantityDiscount'  => 'QuantityDiscount',
                'Bestseller'        => 'Bestseller',
                'AddToCartURL'      => 'AddToCartURL',
                'ReviewRSSURL'      => 'ReviewRSSURL',
                'Option1'           => 'Option1',
                'Option2'           => 'Option2',
                'Option3'           => 'Option3',
                'Option4'           => 'Option4',
                'Option5'           => 'Option5',
                'customCommissions' => 'customCommissions',
                'customCommissionIsFlatRate' => 'customCommissionIsFlatRate',
                'customCommissionNewCustomerMultiplier' => 'customCommissionNewCustomerMultiplier',
                'mobileURL'         => 'mobileURL',
                'mobileImage'       => 'mobileImage',
                'mobileThumbnail'   => 'mobileThumbnail',
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
                'attr'     => 'Name',
                'type'     => 'meta',
                'meta_key' => 'title',
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
            array(
                'attr'     => 'URL',
                'type'     => 'meta',
                'meta_key' => 'link',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'Price',
                'type'     => 'meta',
                'meta_key' => 'price',
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
                'attr'     => 'Subcategory',
                'type'     => 'static',
                'meta_key' => '',
                'st_value' => '',
                'prefix'   => '',
                'suffix'   => '',
                'escape'   => 'default',
                'limit'    => 0,
            ),
            array(
                'attr'     => 'MerchantID',
                'type'     => 'static',
                'meta_key' => '',
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
        );
    }
}
?>
