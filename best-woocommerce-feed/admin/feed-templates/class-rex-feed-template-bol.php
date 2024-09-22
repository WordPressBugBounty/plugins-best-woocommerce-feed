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
class Rex_Feed_Template_Bol extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'   => array(
				'Name'                   => 'Product Title',
				'EAN'                    => 'Merchant SKU',
				'Internal_Reference'     => 'Product ID',
				'Product_Classification' => 'Product Category',
				'Description'            => 'Product Description',
				'Cover_Image_URL'        => 'Image URL',
			),

			'Additional Information' => array(
				'EAN'                 => 'Merchant SKU',
				'Brand'               => 'Brand',
				'Product_Description' => 'Product Description',
				'productType'         => 'Product Type',
				'chunkID'             => 'Chunk ID',
				'label'               => 'Label',
				'attributes'          => 'Attributes',
				'attribute'           => 'Attribute',
				'multiValue'          => 'MultiValue',
				'enrichmentLevel'     => 'Enrichment Level',
				'fillingInstructions' => 'Filling Instructions',
				'possibleValues'      => 'Possible Values',
				'defaultUnit'         => 'Default Unit',
				'baseType'            => 'Base Type',
				'maxLength'           => 'Max Length',
				'minValue'            => 'Min Value',
				'maxValue'            => 'Max Value',
				'units'               => 'Units',
				'Category'            => 'Category',
				'Category_ID'         => 'Category ID',
				'Parent_SKU'          => 'Parent SKU',
				'Parent_Name'         => 'Parent Name',
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
				'attr'     => 'EAN',
				'type'     => 'meta',
				'meta_key' => 'sku',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Internal_Reference',
				'type'     => 'meta',
				'meta_key' => 'id',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

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
				'attr'     => 'Product_Classification',
				'type'     => 'meta',
				'meta_key' => 'product_cats',
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
				'attr'     => 'Cover_Image_URL',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
		);
	}
}
