<?php
/**
 * The Adtraction Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.1.4
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 * Defines the attributes and template for adtraction feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Adtraction
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Profit_share extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = [
			'Required Information' => [
				"Cod categorie"             => "Cod categorie",
				"Categorie"                 => "Categorie",
				"Categorie parinte"         => "Categorie parinte",
				"Producator"                => "Producator",
				"Cod producator"            => "Cod producator",
				"Model"                     => "Model",
				"Cod produs"                => "Cod produs",
				"Nume"                      => "Nume",
				"Descriere"                 => "Descriere",
				"Link produs"               => "Link produs",
				"Imagine produs"            => "Imagine produs",
				"Pret fara TVA"             => "Pret fara TVA",
				"Pret cu TVA"               => "Pret cu TVA",
				"Pret cu discount fara TVA" => "Pret cu discount fara TVA",
				"Moneda"                    => "Moneda",
				"Disponibilitate"           => "Disponibilitate",
				"Livrare gratuita"          => "Livrare gratuita",
				"Cadou inclus"              => "Cadou inclus",
				"Status"                    => "Status",
				"ID categorie parinte"      => "ID categorie parinte"
			]
		];
	}

	/**
	 * Define merchant's default attributes
	 *
	 * @return void
	 */
	protected function init_default_template_mappings() {
		$this->template_mappings = [
			[
				'attr'     => 'Cod categorie',
				'type'     => 'static',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Categorie',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Categorie parinte',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Producator',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Cod producator',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Model',
				'type'     => 'meta',
				'meta_key' => 'SKU',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Cod produs',
				'type'     => 'meta',
				'meta_key' => 'id',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Nume',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Descriere',
				'type'     => 'meta',
				'meta_key' => 'description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Link produs',
				'type'     => 'meta',
				'meta_key' => 'link',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Imagine produs',
				'type'     => 'meta',
				'meta_key' => 'main_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Pret fara TVA',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Pret cu TVA',
				'type'     => 'meta',
				'meta_key' => 'price_incl_tax',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Pret cu discount fara TVA',
				'type'     => 'meta',
				'meta_key' => 'sale_price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Moneda',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Disponibilitate',
				'type'     => 'meta',
				'meta_key' => 'availability',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Livrare gratuita',
				'type'     => 'static',
				'meta_key' => '1',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Cadou inclus',
				'type'     => 'static',
				'meta_key' => '0',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'Status',
				'type'     => 'static',
				'meta_key' => '1',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			],
			[
				'attr'     => 'ID categorie parinte',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			]
		];
	}
}
