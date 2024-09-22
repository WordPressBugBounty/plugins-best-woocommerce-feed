<?php

/**
 * The file that generates xml feed for any merchant with custom configuration.
 *
 * A class definition that includes functions used for generating xml feed.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed_Google
 * @subpackage Rex_Product_Feed_Google/includes
 * @author     RexTheme <info@rextheme.com>
 */

use RexTheme\RexShoppingFeed\Containers\RexShopping;

class Rex_Product_Feed_Other extends Rex_Product_Feed_Abstract_Generator {

    private $variation_products = [];
    private $feed_merchants = array(
        "123i"                => array(
            'container'        => true,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'Carga',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => 'Imoveis',
            'wrapper'          => true,
            'datetime'         => false,
        ),
        "adcrowd"             => array(
            'container'        => true,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'rss',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '2.0',
            'wrapper_el'       => 'channel',
            'wrapper'          => true,
            'datetime'         => false,
        ),
        "adform"              => array(
            'container'        => true,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "adtraction"          => array(
            'container'        => true,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'feed',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "beslist"             => array(
            'container'        => true,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => true,
            'version'          => '1.0',
            'wrapper_el'       => '',
            'wrapper'          => true,
            'datetime'         => true,
        ),
        "bloomville"          => array(
            'container'        => true,
            'item_wrapper'     => 'CourseTemplate',
            'items_wrapper'    => 'CourseTemplates',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "cdiscount"           => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "clubic"              => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "custom"              => array(
            'container'        => true,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => true,
            'version'          => '1.0',
            'wrapper_el'       => '',
            'wrapper'          => true,
            'datetime'         => true,
        ),
        "drm"                 => array(
            'container'        => true,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "datatrics"           => array(
            'container'        => true,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "deltaprojects"       => array(
            'container'        => true,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "become"              => array(
            'container'        => true,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "adroll"              => array(
            'container'        => true,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "domodi"              => array(
            'container'        => true,
            'item_wrapper'     => 'SHOPITEM',
            'items_wrapper'    => 'SHOP',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "drezzy"              => array(
            'container'        => true,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "ebay_mip"            => array(
            'container'        => true,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => true,
            'version'          => '1.0',
            'wrapper_el'       => '',
            'wrapper'          => true,
            'datetime'         => true,
        ),
        "bonanza"             => array(
            'container'        => true,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => true,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => true,
            'datetime'         => true,
        ),
        "cercavino"           => array(
            'container'        => true,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => true,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => true,
            'datetime'         => true,
        ),
        "trovino"             => array(
            'container'        => true,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => true,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => true,
            'datetime'         => true,
        ),
        "emag"                => array(
            'container'        => true,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'shop',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "eytsy"               => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "fashiola"            => array(
            'container'        => true,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "glami"               => array(
            'container'        => true,
            'item_wrapper'     => 'SHOPITEM',
            'items_wrapper'    => 'SHOP',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "homebook"            => array(
            'container'        => false,
            'item_wrapper'     => 'offer',
            'items_wrapper'    => 'offers',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "homedeco"            => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "hertie"              => array(
            'container'        => false,
            'item_wrapper'     => 'Artikel',
            'items_wrapper'    => 'Katalog',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "incurvy"             => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'produkte',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "indeed"              => array(
            'container'        => false,
            'item_wrapper'     => 'job',
            'items_wrapper'    => 'source',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "jobbird"             => array(
            'container'        => false,
            'item_wrapper'     => 'job',
            'items_wrapper'    => 'jobs',
            'version'          => '1.0',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,

        ),
        "joblift"             => array(
            'container'        => false,
            'item_wrapper'     => 'job',
            'items_wrapper'    => 'feed',
            'version'          => '1',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "job_board_io"        => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "kieskeurig"          => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "kauftipp"            => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "kuantokusta"         => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "kleding"             => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "kelkoo"              => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "billiger"            => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "kelkoonl"            => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "lyst"                => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'channel',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "listupp"             => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "ladenzeile"          => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "mydeal"              => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "prisjkat"            => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "pricefalls"          => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "pricerunner"         => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "nextag"              => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "rakuten_advertising" => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "rss"                 => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "shopalike"           => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "shopee"              => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "skroutz"             => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'mywebstore',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => 'products',
            'wrapper'          => true,
            'datetime'         => false,
        ),
        "trovaprezzi"         => array(
            'container'        => false,
            'item_wrapper'     => 'Offer',
            'items_wrapper'    => 'Products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "pricegrabber"        => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "vidaXL"              => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "vivino"              => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'vivino-product-list',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "winesearcher"        => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "whiskymarketplace"   => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "shopmania"           => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "shopzilla"           => array(
            'container'        => true,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'items',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "favi"                => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "webgains"            => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'feed',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "awin"                => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "gulog_gratis"        => array(
            'container'        => false,
            'item_wrapper'     => 'ad',
            'items_wrapper'    => 'ads',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "leguide"             => array(
            'container'        => false,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        ),
        "zap_co_il"           => array(
            'container'        => false,
            'item_wrapper'     => 'PRODUCT',
            'items_wrapper'    => 'STORE',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => 'PRODUCTS',
            'wrapper'          => true,
            'datetime'         => false,
        ),
        "hotline"             => array(
            'container'        => true,
            'item_wrapper'     => 'item',
            'items_wrapper'    => 'price',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => true,
            'datetime'         => true,
        ),
        "rozetka"             => array(
            'container'        => false,
            'item_wrapper'     => 'offer',
            'items_wrapper'    => 'yml_catalog',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => true,
            'datetime'         => false,
        ),
        "check24"             => array(
            'container'        => false,
            'item_wrapper'     => 'product',
            'items_wrapper'    => 'products',
            'namespace'        => null,
            'namespace_prefix' => '',
            'stand_alone'      => false,
            'version'          => '',
            'wrapper_el'       => '',
            'wrapper'          => false,
            'datetime'         => false,
        )
    );

    public function __construct( $config, $bypass = false, $product_ids = array() ) {
        parent::__construct( $config, $bypass, $product_ids );

        if( isset( $this->feed_merchants[ 'custom' ] ) ) {
            if( $this->custom_wrapper ) {
                $this->feed_merchants[ 'custom' ][ 'item_wrapper' ] = $this->custom_wrapper;
            }
            if( $this->custom_items_wrapper ) {
                $this->feed_merchants[ 'custom' ][ 'items_wrapper' ] = $this->custom_items_wrapper;
            }
            if( $this->custom_wrapper_el ) {
                $this->feed_merchants[ 'custom' ][ 'wrapper_el' ] = $this->custom_wrapper_el;
            }
        }
    }

    /**
     * Check if the merchants is valid or not
     * @param $feed_merchants
     * @return bool
     */
    public function is_valid_merchant(){
        return array_key_exists($this->merchant, $this->feed_merchants)? true : false;
    }
    /**
     * Get version
     *
     * @return string
     */
    public function get_version() {
        return $this->is_valid_merchant()? $this->feed_merchants[$this->merchant]['version'] : '';
    }

    /**
     * @return string
     */
    public function get_item_wrapper(){
        return $this->is_valid_merchant()? $this->feed_merchants[$this->merchant]['item_wrapper'] : 'product';
    }


    /**
     * @return string
     */
    public function get_items_wrapper(){
        return $this->is_valid_merchant()? $this->feed_merchants[$this->merchant]['items_wrapper'] : 'products';
    }

    /**
     * @return |null
     */
    public function get_namespace() {
        return $this->is_valid_merchant()? $this->feed_merchants[$this->merchant]['namespace'] : null;
    }


    /**
     * @return bool
     */
    public function get_stand_alone() {
        return $this->is_valid_merchant()? $this->feed_merchants[$this->merchant]['stand_alone'] : false;
    }

    /**
     * @return string
     */
    public function get_wrapper_el() {
        return $this->is_valid_merchant()? $this->feed_merchants[$this->merchant]['wrapper_el'] : '';
    }

    /**
     * @return bool
     */
    public function get_wrapper() {
        return $this->is_valid_merchant()? $this->feed_merchants[$this->merchant]['wrapper'] : true;
    }


    /**
     * check if date is true/false
     *
     * @return bool
     */
    public function is_datetime() {
        return $this->feed_merchants[$this->merchant]['datetime'] ?? '';
    }


    /**
     * Get namespace prefix
     *
     * @return string
     */
    public function get_namespace_prefix() {
        return $this->feed_merchants[$this->merchant]['namespace_prefix'] ?? '';
    }

    /**
     * Create Feed
     *
     * @return boolean
     * @author
     **/
    public function make_feed() {
        $items_wrapper = $this->get_items_wrapper();
        $wrapper_el = $this->get_wrapper_el();
        $item_wrapper = $this->get_item_wrapper();

        RexShopping::$container = null;
        RexShopping::init($this->get_wrapper(), $item_wrapper, $this->get_namespace(),  $this->get_version(), $items_wrapper, $this->get_stand_alone(), $wrapper_el, $this->get_namespace_prefix() );

        if( 'include' === $this->custom_xml_header ) {
            RexShopping::title( $this->title );
            RexShopping::link( $this->link );
            RexShopping::description( $this->desc );
            if( $this->is_datetime() ) {
                RexShopping::datetime( date( "Y-m-d h:i:s" ) );
            }
        }

        // Generate feed for both simple and variable products.
        $this->generate_product_feed();

        $this->feed = $this->returnFinalProduct();
        
        if ($this->batch >= $this->tbatch ) {
            $this->save_feed($this->feed_format);
            return array(
                'msg' => 'finish'
            );
        }else {
            return $this->save_feed($this->feed_format);
        }
    }

    /**
     * Generate feed
     */
    protected function generate_product_feed(){
        $product_meta_keys = Rex_Feed_Attributes::get_attributes();
        $total_products = get_post_meta($this->id, '_rex_feed_total_products', true);
        $total_products = $total_products ?: get_post_meta($this->id, 'rex_feed_total_products', true);
        $simple_products = [];
        $variable_parent = [];
        $group_products = [];
        $total_products = $total_products ?: array(
            'total' => 0,
            'simple' => 0,
            'variable' => 0,
            'variable_parent' => 0,
            'group' => 0,
        );

        if($this->batch == 1) {
            $total_products = array(
                'total' => 0,
                'simple' => 0,
                'variable' => 0,
                'variable_parent' => 0,
                'group' => 0,
            );
        }

        foreach( $this->products as $productId ) {
            $product = wc_get_product( $productId );

            if ( ! is_object( $product ) ) {
                continue;
            }
            if ( $this->exclude_hidden_products ) {
                if ( !$product->is_visible() ) {
                    continue;
                }
            }

            if( !$this->include_zero_priced ) {
                $product_price = rex_feed_get_product_price($product);
                if( 0 == $product_price || '' == $product_price ) {
                    continue;
                }
            }
            if ( $product->is_type( 'variable' ) && $product->has_child() ) {
                if($this->variable_product && $this->is_out_of_stock( $product ) ) {
                    $variable_parent[] = $productId;
                    $variable_product = new WC_Product_Variable($productId);
                    $this->add_to_feed( $variable_product, $product_meta_keys );
                }

                if( $this->product_scope === 'product_cat' || $this->product_scope === 'product_tag' || $this->custom_filter_var_exclude ) {
                    if ( $this->exclude_hidden_products ) {
                        $variations = $product->get_visible_children();
                    }
                    else {
                        $variations = $product->get_children();
                    }

                    if( $variations ) {
                        foreach ($variations as $variation) {
                            if($this->variations) {
                                $variation_product = wc_get_product( $variation );
                                if ( $this->is_out_of_stock( $variation_product ) ) {
                                    $variation_products[] = $variation;
                                    $this->add_to_feed( $variation_product, $product_meta_keys, 'variation' );
                                }
                            }
                        }
                    }
                }
            }

            if ( $this->is_out_of_stock( $product ) ) {
                if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) || $product->is_type( 'composite' ) || $product->is_type( 'bundle' ) ) {
                    $simple_products[] = $productId;
                    $this->add_to_feed( $product, $product_meta_keys );
                }

                if ( $this->product_scope === 'all' || $this->product_scope === 'product_filter' || $this->custom_filter_option ) {
                    if ( $product->get_type() === 'variation' ) {
                        $variation_products[] = $productId;
                        $this->add_to_feed( $product, $product_meta_keys, 'variation' );
                    }
                }

                if ( $product->is_type( 'grouped' ) && $this->parent_product || $product->is_type( 'woosb' ) ) {
                    $group_products[] = $productId;
                    $this->add_to_feed( $product, $product_meta_keys );
                }
            }
        }
     
        $total_products = [
            'total' => (int) $total_products['total'] + count($simple_products) + count($this->variation_products) + count($group_products) + count($variable_parent),
            'simple' => (int) $total_products['simple'] + count($simple_products),
            'variable' => (int) $total_products['variable'] + count($this->variation_products),
            'variable_parent' => (int) $total_products['variable_parent'] + count($variable_parent),
            'group' => (int) $total_products['group'] + count($group_products)
        ];
        update_post_meta( $this->id, '_rex_feed_total_products', $total_products );
	    if ( $this->tbatch === $this->batch ) {
		    update_post_meta( $this->id, '_rex_feed_total_products_for_all_feed', $total_products[ 'total' ] );
	    }
    }


    /**
     * Adding items to feed
     *
     * @param $product
     * @param $meta_keys
     * @param string $product_type
     */
    private function add_to_feed( $product, $meta_keys, $product_type = '' ) {
        $attributes = $this->get_product_data( $product, $meta_keys );

        if( 'variable' === $product_type && 'skroutz' === $this->merchant && $this->variations ) {
            $attributes = $this->get_variation_attributes( $product, $meta_keys, $attributes );
        }

        if( ( !empty( $attributes ) && is_array( $attributes ) )
            && ( ( $this->rex_feed_skip_product
                    && empty( array_keys( $attributes, '' ) ) )
                || !$this->rex_feed_skip_product )
        ) {
            $item = RexShopping::createItem();

            foreach( $attributes as $key => $value ) {
                $value = $this->get_value_for_kelkoo_group( $key, $value );
                if( $this->rex_feed_skip_row && 'xml' === $this->feed_format ) {
                    if( $value != '' ) {
                        $item->$key( $value ); // invoke $key as method of $item object.
                    }
                }
                else {
                    $item->$key( $value ); // invoke $key as method of $item object.
                }
            }
        }
    }

    /**
     * Get variation products and their attribute data following the feed meta keys
     *
     * @param WC_Product $product WooCommerce product object.
     * @param array $meta_keys Feed meta keys.
     * @param array $attributes Feed attributes.
     *
     * @return array
     * @since 7.3.7
     */
    private function get_variation_attributes( $product, $meta_keys, $attributes = [] ) {
        if( $this->exclude_hidden_products ) {
            $variations = $product->get_visible_children();
        }
        else {
            $variations = $product->get_children();
        }

        if( is_array( $variations ) && !empty( $variations ) ) {
            sort( $variations, 1 );
            foreach( $variations as $variation_id ) {
                $this->variation_products[] = $variation_id;
                $variation_product            = wc_get_product( $variation_id );
                if(
                    ( !$this->include_out_of_stock )
                    && ( !$variation_product->is_in_stock()
                        || $variation_product->is_on_backorder()
                        || ( is_integer( $variation_product->get_stock_quantity() ) && 0 >= $variation_product->get_stock_quantity() )
                    )
                ) {
                    continue;
                }
                $attributes[ 'variations' ][] = $this->get_product_data( $variation_product, $meta_keys );
            }
        }
        return $attributes;
    }

    /**
     * Modifies and returns a value based on specific conditions for the "KelkooGroup" merchant.
     *
     * This private method is used to modify and return a value based on certain conditions specific to the "KelkooGroup"
     * merchant. It primarily adjusts the format of the value based on the provided key. For example, if the key is
     * "availability", the value will be capitalized. If the key is "landing_page_url", the query string part will be removed.
     *
     * @param string $key The key representing the type of data.
     * @param mixed  $value The value to be modified.
     *
     * @return mixed The modified value based on the provided conditions.
     * @since 7.3.10
     */
    private function get_value_for_kelkoo_group( $key, $value ) {
        if( 'kelkoo_group' === $this->merchant ) {
            if( 'availability' === $key ) {
                $value = ucfirst( $value );
            }
            if( 'landing_page_url' === $key ) {
                $modified_value = strstr( $value, '?', true);
                return false !== $modified_value ? $modified_value : $value;
            }
        }
        return $value;
    }

    /**
     * Return Feed
     *
     * @return array|bool|string
     */
    public function returnFinalProduct() {
        if( 'text' === $this->feed_format || 'tsv' === $this->feed_format ) {
            if( 'pipe' === $this->feed_separator ) {
                return RexShopping::asTxtPipe();
            }
            return RexShopping::asTxt();
        }
        elseif( 'csv' === $this->feed_format ) {
            return RexShopping::asCsv();
        }
        return RexShopping::asRss();
    }

    /**
     * Replace footer in XML feeds
     *
     * @return void
     */
    public function footer_replace() {
        if( $this->merchant === 'trovaprezzi' ) {
            $this->feed = str_replace( '</Products>', '', $this->feed );
        }
        else if( $this->merchant === 'zbozi' ) {
            $this->feed = str_replace( '</SHOP>', '', $this->feed );
        }
        else if( $this->merchant === 'skroutz' ) {
            $this->feed = str_replace( '</products></mywebstore>', '', $this->feed );
        }
        else if(
            $this->merchant === 'datatrics'
            || $this->merchant === 'homedeco'
            || $this->merchant === 'listupp'
            || $this->merchant === 'whiskymarketplace'
        ) {
            $this->feed = str_replace( '</items>', '', $this->feed );
        }
        else if( $this->merchant === 'domodi' ) {
            $this->feed = str_replace( '</SHOP>', '', $this->feed );
        }
        else if( $this->merchant === 'drezzy' || $this->merchant === 'fashiola' || $this->merchant === 'clubic' ) {
            $this->feed = str_replace( '</items>', '', $this->feed );
        }
        else if( $this->merchant === 'homebook' ) {
            $this->feed = str_replace( '</offers>', '', $this->feed );
        }
        else if( $this->merchant === 'emag' ) {
            $this->feed = str_replace( '</shop>', '', $this->feed );
        }
        else if( $this->merchant === 'lyst' ) {
            $this->feed = str_replace( '</channel>', '', $this->feed );
        }
        else if( $this->merchant === 'hertie' ) {
            $this->feed = str_replace( '</Katalog>', '', $this->feed );
        }
        else if(
            $this->merchant === 'beslist' || $this->merchant === 'cdiscount'
            || $this->merchant === 'kieskeurig' || $this->merchant === 'kauftipp'
            || $this->merchant === 'kuantokusta' || $this->merchant === 'kelkoonl'
            || $this->merchant === 'mydeal' || $this->merchant === 'prisjkat'
            || $this->merchant === 'pricefalls' || $this->merchant === 'pricerunner'
            || $this->merchant === 'nextag' || $this->merchant === 'rakuten_advertising'
            || $this->merchant === 'shopee' || $this->merchant === 'vidaXL'
            || $this->merchant === 'rss' || $this->merchant === 'pricegrabber'
            || $this->merchant === 'google_dsa' || $this->merchant === 'google_Ad'
            || $this->merchant === 'shopmania' || $this->merchant === 'favi'
            || $this->merchant === 'deltaprojects'
            || $this->merchant === 'kelkoo' || $this->merchant === 'billiger'
            || $this->merchant === 'bonanza' || $this->merchant === 'become'
            || $this->merchant === 'adroll' || $this->merchant === 'awin'
            || $this->merchant === 'leguide' || $this->merchant === 'vergelijk'
            || $this->merchant === 'twenga' || $this->merchant === 'tweakers'
            || $this->merchant === 'koopkeus' || $this->merchant === 'scoupz'
            || $this->merchant === 'uvinum' || $this->merchant === 'pricesearcher'
            || $this->merchant === 'pricemasher' || $this->merchant === 'fashionchick'
            || $this->merchant === 'choozen' || $this->merchant === 'powerreviews'
            || $this->merchant === 'otto' || $this->merchant === 'sears'
            || $this->merchant === 'ammoseek' || $this->merchant === 'fnac'
            || $this->merchant === 'pixmania' || $this->merchant === 'coolblue'
            || $this->merchant === 'verizon' || $this->merchant === 'kelkoo_group'
            || $this->merchant === 'target' || $this->merchant === 'pepperjam'
            || $this->merchant === 'cj_affiliate'
        ) {
            $this->feed = str_replace( '</products>', '', $this->feed );
        }
        else if( $this->merchant === '123i' ) {
            $this->feed = str_replace( '</Imoveis></Carga>', '', $this->feed );
        }
        else if( $this->merchant === 'adcrowd' ) {
            $this->feed = str_replace( '</channel></rss>', '', $this->feed );
        }
        else if( $this->merchant === 'adform' || $this->merchant === 'drm' || $this->merchant === 'drezzy' ) {
            $this->feed = str_replace( '</items>', '', $this->feed );
        }
        else if( $this->merchant === 'adtraction' ) {
            $this->feed = str_replace( '</feed>', '', $this->feed );
        }
        else if( $this->merchant === 'bloomville' ) {
            $this->feed = str_replace( '</CourseTemplates>', '', $this->feed );
        }
        else if(
            $this->merchant === 'drm' || $this->merchant === 'job_board_io'
            || $this->merchant === 'ladenzeile' || $this->merchant === 'shopalike'
            || $this->merchant === 'whiskymarketplace'
        ) {
            $this->feed = str_replace( '</items>', '', $this->feed );
        }
        else if( $this->merchant === 'domodi' ) {
            $this->feed = str_replace( '</SHOPITEM>', '', $this->feed );
        }
        else if( $this->merchant === 'incurvy' ) {
            $this->feed = str_replace( '</produkte>', '', $this->feed );
        }
        else if( $this->merchant === 'indeed' ) {
            $this->feed = str_replace( '</source>', '', $this->feed );
        }
        else if( $this->merchant === 'jobbird' ) {
            $this->feed = str_replace( '</jobs>', '', $this->feed );
        }
        else if( $this->merchant === 'joblift' || $this->merchant === 'webgains' ) {
            $this->feed = str_replace( '</feed>', '', $this->feed );
        }
        else if( $this->merchant === 'kleding' || $this->merchant === 'winesearcher' ) {
            $this->feed = str_replace( '</items>', '', $this->feed );
        }
        else if( $this->merchant === 'vivino' ) {
            $this->feed = str_replace( '</vivino-product-list>', '', $this->feed );
        }
        else if( $this->merchant === 'gulog_gratis' ) {
            $this->feed = str_replace( '</ads>', '', $this->feed );
        }
        else if( $this->merchant === 'custom' ) {
            $search = "</{$this->get_items_wrapper()}>";
            if( $this->custom_wrapper_el ) {
                $search = "</{$this->get_wrapper_el()}>{$search}";
            }
            $this->feed = str_replace( $search, '', $this->feed );
        }
        else {
            $this->feed = str_replace( '</products>', '', $this->feed );
        }
    }
}
