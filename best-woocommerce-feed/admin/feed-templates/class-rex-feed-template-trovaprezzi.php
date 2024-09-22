<?php
/**
 * The Uvinum Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      5.5
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for trovaprezzi feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Trovaprezzi
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Trovaprezzi extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'   => array(
				'Categories'    => 'Product Categories',
				'Image'         => 'Image Link',
				'Code'          => 'Code',
				'Link'          => 'Product URL',
				'Name'          => 'Product Name',
				'Price'         => 'Price',
				'OriginalPrice' => 'Original Price',
				'ShippingCost'  => 'Shipping Cost',
				'Brand'         => 'Brand',
				'Description'   => 'Description',
				'EanCode'       => 'Ean Code',
				'Weight'        => 'Weight',
				'PartNumber'    => 'Part Number',
				'Stock'         => 'Stock',
			),

			'Additional Information' => array(
				'MINSANCode' => 'MINSANCode',
				'Imag2'      => 'Image 2',
				'Imag3'      => 'Image 3',
				'Size'       => 'Size',
				'Color'      =>'Color',
				'Material'   => 'Material',
				'Gender'     => 'gender',
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
				'attr'     => 'Code',
				'type'     => 'meta',
				'meta_key' => 'id',
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
				'escape'   => 'cdata',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Categories',
				'type'     => 'meta',
				'meta_key' => 'product_cats',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Image',
				'type'     => 'meta',
				'meta_key' => 'featured_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Link',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'OriginalPrice',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Price',
				'type'     => 'meta',
				'meta_key' => 'current_price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => ' ',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'ShippingCost',
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
				'attr'     => 'EanCode',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'PartNumber',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'Weight',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'Stock',
				'type'     => 'meta',
				'meta_key' => 'quantity',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

		);
	}
}
