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
 * Defines the attributes and template for spartoo feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Spartoo
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_SpartooFr extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information'   => array(
				'reference_partenaire' => 'Product Reference',
				'manufacturers_name'   => 'Manufacturers Name',
				'product_sex'          => 'Product sex',
				'product_price'        => 'Product price',
				'product_style'        => 'Product style',
				'size_name'            => 'Size name',
				'size_quantity'        => 'Size quantity',
				'size_reference'       => 'Size reference',
				'ean'                  => 'EAN',
				'photo'                => 'Picture',
			),

			'Additional Information' => array(
				'product_name'        => 'Product name',
				'color_id'            => 'Color Id',
				'product_description' => 'Product description',
				'product_color'       => 'Product color',
				'product_quantity'    => 'Product quantity',
				'country_origin'      => 'Country origin',
				'country_hs'          => 'Country hs',
				'heel_height'         => 'Heel height',

				'product_composition' => 'Product composition',
				'voering_composition' => 'Voering composition',
				'first_composition'   => 'First composition',
				'zool_composition'    => 'Zool composition',
				'startdate'           => 'Start date',
				'stopdate'            => 'Stop date',
				'price_discount'      => 'Price discount',
				'rate'                => 'Rate',
				'sales'               => 'Sales',
				'extra_info_id_1'     => 'Extra Info Id 1',
				'extra_info_value_1'  => 'Extra Info Value 1',
				'extra_info_id_2'     => 'Extra Info Id 2',
				'extra_info_value_2'  => 'Extra Info Value 2',
				'extra_info_id_3'     => 'Extra Info Id 3',
				'extra_info_value_3'  => 'Extra Info Value 3',
				'extra_info_id_4'     => 'Extra Info Id 4',
				'extra_info_value_4'  => 'Extra Info Value 4',
				'extra_info_id_5'     => 'Extra Info Id 5',
				'extra_info_value_5'  => 'Extra Info Value 5',
				'selection_1'         => 'Selection 1',
				'selection_2'         => 'Selection 2',
				'selection_3'         => 'Selection 3',
				'selection_4'         => 'Selection 4',
				'selection_5'         => 'Selection 5',
				'size_name_2'         => 'Size name 2',
				'size_quantity_2'     => 'Size quantity 2',
				'size_reference_2'    => 'Size reference 2',
				'ean_2'               => 'EAN 2',
				'size_name_3'         => 'Size name 3',
				'size_quantity_3'     => 'Size quantity 3',
				'size_reference_3'    => 'Size reference 3',
				'ean_3'               => 'EAN 3',
				'size_name_4'         => 'Size name 4',
				'size_quantity_4'     => 'Size quantity 4',
				'size_reference_4'    => 'Size reference 4',
				'ean_4'               => 'EAN 4',
				'size_name_5'         => 'Size name 5',
				'size_quantity_5'     => 'Size quantity 5',
				'size_reference_5'    => 'Size reference 5',
				'ean_5'               => 'EAN 5',

				'photo_2'             => 'Picture 2',
				'photo_3'             => 'Picture 3',
				'photo_4'             => 'Picture 4',
				'photo_5'             => 'Picture 5',
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
				'attr'     => 'reference_partenaire',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'manufacturers_name',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'product_sex',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'product_price',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'product_style',
				'type'     => 'meta',
				'meta_key' => 'product_cats',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'size_name',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'size_quantity',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'size_reference',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'ean',
				'type'     => 'meta',
				'meta_key' => 'sku',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'photo',
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
