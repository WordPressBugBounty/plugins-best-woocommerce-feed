<?php
/**
 * The vivino marketplace Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for vivino marketplace feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_vivino
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Vivino extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information' => array(
				'product-name'        => 'Product name',
				'price'               => 'Price',
				'quantity-is-minimum' => 'Quantity is minimum',
				'bottles-size'        => 'Bottles size',
				'bottles-quantity'    => 'Bottles quantity',
				'link'                => 'Link',
				'inventory-count'     => 'Inventory count',
				'product-id'          => 'Product id',
				'wine-name'           => 'Wine name',
			),
			'Optional Information' => array(
				'acidity'                 => 'Acidity (unit g/l)',
				'ageing'                  => 'Ageing',
				'alcohol'                 => 'Alcohol',
				'appellation'             => 'Appellation',
				'certified-biodynamic'    => 'Certified biodynamic',
				'certified-organic'       => 'Certified organic',
				'closure'                 => 'Closure',
				'color'                   => 'Color',
				'contains-added-sulfites' => 'Contains added sulfites',
				'contains-egg-allergens'  => 'Contains egg allergens',
				'contains-milk-allergens' => 'Contains milk allergens',
				'country'                 => 'Country',
				'decant-for'              => 'Decant for (unit hours)',
				'description'             => 'Description',
				'drinking-temperature'    => 'Drinking temperature (celsius)',
				'drinking-years-from'     => 'Drinking years from',
				'drinking-years-to'       => 'Drinking years to',
				'importer-address'        => 'Importer address',
				'kosher'                  => 'Kosher',
				'meshuval'                => 'Meshuval',
				'non-alcoholic'           => 'Non-alcoholic',
				'ph'                      => 'ph',
				'price-discounted-from'   => 'Price discounted from',
				'price-discounted-until'  => 'Price discounted until',
				'producer'                => 'Producer',
				'producer-address'        => 'Producer address',

				'production-size'         => 'Production size (unit bottle)',
				'residual-sugar'          => 'Residual sugar (unit g/l)',
				'sweetness'               => 'Sweetness',
				'varietal'                => 'Varietal',
				'vegan-friendly'          => 'Vegan friendly',
				'vintage'                 => 'Vintage',
				'winemaker'               => 'Winemaker',
				'ean'                     => 'Ean',
				'image'                   => 'Image',
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
				'attr'     => 'product-name',
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
				'suffix'   => ' ' . get_option( 'woocommerce_currency' ),
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'quantity-is-minimum',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'bottles-size',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'bottles-quantity',
				'type'     => 'meta',
				'meta_key' => 'quantity',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'link',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

			array(
				'attr'     => 'inventory-count',
				'type'     => 'meta',
				'meta_key' => 'quantity',
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
				'attr'     => 'wine-name',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),

		);
	}
}
