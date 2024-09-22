<?php
/**
 * Class Rex_Product_Feed_Factory
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed_Factory
 * @subpackage Rex_Product_Feed_Factory/includes
 */

/**
 * The Rex_Product_Feed_Factory class file that
 * returns a feed generator object based on selected merchant.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed_Factory
 * @subpackage Rex_Product_Feed_Factory/includes
 */
class Rex_Product_Feed_Factory {

	/**
	 * Other merchants list
	 *
	 * @var array Other merchants list.
	 */
	private static $other_merchants;

	/**
	 * Google merchant list
	 *
	 * @var array Google merchant list.
	 */
	private static $google_format;

	/**
	 * Mirakl merchant list
	 *
	 * @var array Mirakl merchant list.
	 */
	private static $mirakl_format;

	/**
	 * Bestprice merchant list
	 *
	 * @var array Bestprice merchant list.
	 */
	private static $bestprice_format;

	/**
	 * DealsForU merchant list
	 *
	 * @var array DealsForU merchant list.
	 */
	private static $deals_for_u;

	/**
	 * SpartooFr merchant list
	 *
	 * @var array SpartooFr merchant list.
	 */
	private static $spartoo_fr;

	/**
	 * Build merchant class object
	 *
	 * @param array $config Feed Configuration.
	 * @param bool  $bypass If the process should be bypassed.
	 * @param array $product_ids Product Ids.
	 *
	 * @return mixed|Rex_Product_Feed_Bestprice|Rex_Product_Feed_DealsForU|Rex_Product_Feed_Facebook|Rex_Product_Feed_Google|Rex_Product_Feed_Gulog_gratis|Rex_Product_Feed_Mirakl|Rex_Product_Feed_Other|Rex_Product_Feed_Pinterest|Rex_Product_Feed_SpartooFr|Rex_Product_Feed_Yandex
	 * @throws Exception Exception.
	 */
	public static function build( $config, $bypass = false, $product_ids = array() ) {
		$log                    = wc_get_logger();
		self::$other_merchants  = apply_filters(
			'wpfm_merchant_custom',
			array(
				'adform',
				'adcrowd',
				'beslist',
				'cdiscount',
				'custom',
				'kieskeurig',
				'kleding',
				'ladenzeile',
				'skroutz',
				'winesearcher',
				'whiskymarketplace',
				'trovaprezzi',
				'nextag',
				'nextag',
				'pricegrabber',
				'cercavino',
				'kelkoo',
				'ebay',
				'become',
				'shopzilla',
				'google_Ad',
				'adroll',
				'admarkt',
				'pricerunner',
				'billiger',
				'vergelijk',
				'twenga',
				'tweakers',
				'koopkeus',
				'scoupz',
				'kelkoonl',
				'uvinum',
				'pricesearcher',
				'pricemasher',
				'google_dsa',
				'fashionchick',
				'choozen',
				'prisjkat',
				'crowdfox',
				'powerreviews',
				'otto',
				'sears',
				'ammoseek',
				'fnac',
				'pixmania',
				'coolblue',
				'shopmania',
				'preis',
				'walmart',
				'verizon',
				'kelkoo_group',
				'target',
				'pepperjam',
				'cj_affiliate',
				'guenstiger',
				'hood',
				'livingo',
				'jet',
				'bonanza',
				'adcell',
				'stylefruits',
				'medizinfuchs',
				'moebel',
				'restposten',
				'sparmedo',
				'newegg',
				'123i',
				'bikeexchange',
				'cenowarka',
				'cezigue',
				'check24',
				'clang',
				'cherchons',
				'boetiek',
				'comparer',
				'converto',
				'coolshop',
				'commerce_connector',
				'everysize',
				'encuentraprecios',
				'geizhals',
				'geizkragen',
				'giftboxx',
				'go_banana',
				'goed_geplaatst',
				'grosshandel',
				'hardware',
				'hatch',
				'hintaopas',
				'fyndiq',
				'fasha',
				'realde',
				'hintaseuranta',
				'family_blend',
				'hitmeister',
				'lazada',
				'get_price',
				'home_tiger',
				'jurkjes',
				'kiesproduct',
				'kiyoh',
				'kompario',
				'kwanko',
				'ledenicheur',
				'les_bonnes_bouilles',
				'lions_home',
				'locamo',
				'logicsale',
				'pronto',
				'awin',
				'google_dynamic_display_ads',
				'indeed',
				'incurvy',
				'jobbird',
				'job_board_io',
				'joblift',
				'kuantokusta',
				'kauftipp',
				'rakuten_advertising',
				'pricefalls',
				'google_hotel_ads',
				'facebook_dynamic_ads_travel',
				'clubic',
				'shopalike',
				'adtraction',
				'bloomville',
				'bipp',
				'datatrics',
				'deltaprojects',
				'drezzy',
				'domodi',
				'homebook',
				'homedeco',
				'imovelweb',
				'onbuy',
				'fashiola',
				'emag',
				'lyst',
				'listupp',
				'hertie',
				'pricepanda',
				'eytsy',
				'okazii',
				'webgains',
				'vidaXL',
				'mydeal',
				'trovino',
				'bol',
				'leguide',
				'connexity',
				'drm',
			)
		);
		self::$google_format    = array(
			'google',
			'ciao',
			'liveintent',
			'google_shopping_actions',
			'google_merchant_promotion',
			'google_express',
			'criteo',
			'compartner',
			'doofinder',
			'emarts',
			'epoq',
			'google_local_inventory_ads',
			'google_manufacturer_center',
			'bing_image',
			'rss',
            'facebook',
            'instagram',
            'facebook',
            'snapchat',
            'twitter',
            'bing'
		);
		self::$bestprice_format = array(
			'Bestprice',
		);
		self::$mirakl_format    = array(
			'mirakl',
		);
		self::$deals_for_u      = array(
			'DealsForU',
		);
		self::$spartoo_fr       = array(
			'spartooFr',
		);

		if ( in_array( $config[ 'merchant' ], self::$other_merchants ) ) {
			$class_name = 'Rex_Product_Feed_Other';
		}
		elseif ( in_array( $config[ 'merchant' ], self::$google_format ) ) {
			$class_name = 'Rex_Product_Feed_Google';
		}
		elseif ( in_array( $config[ 'merchant' ], self::$mirakl_format ) ) {
			$class_name = 'Rex_Product_Feed_Mirakl';
		}
		elseif ( in_array( $config[ 'merchant' ], self::$deals_for_u ) ) {
			$class_name = 'Rex_Product_Feed_DealsForU';
		}
		elseif ( in_array( $config[ 'merchant' ], self::$bestprice_format ) ) {
			$class_name = 'Rex_Product_Feed_Bestprice';
		}
		elseif ( in_array( $config[ 'merchant' ], self::$spartoo_fr ) ) {
			$class_name = 'Rex_Product_Feed_SpartooFr';
		}
		elseif ( 'admitad' === $config[ 'merchant' ] || 'ibud' === $config[ 'merchant' ] ) {
			$class_name = 'Rex_Product_Feed_Yandex';
		}
		elseif ( 'pinterest' === $config[ 'merchant' ] ) {
			$class_name = 'Rex_Product_Feed_Pinterest';
		}
		elseif ( 'gulog_gratis' === $config[ 'merchant' ] ) {
			$class_name = 'Rex_Product_Feed_Gulog_gratis';
		}
		elseif ( 'idealo' === $config[ 'merchant' ] || 'idealo_de' === $config[ 'merchant' ] ) {
			$class_name = 'Rex_Product_Feed_Idealo';
		}
		else {
			$class_name = 'Rex_Product_Feed_' . ucfirst( str_replace( ' ', '', $config[ 'merchant' ] ) );
		}

		if ( '' === $config || !class_exists( $class_name ) ) {
			if ( is_wpfm_logging_enabled() ) {
				$log->critical( __( 'Invalid Merchant.', 'rex-product-feed' ), array( 'source' => 'WPFM-Critical' ) );
			}
			throw new Exception( 'Invalid Merchant.' );
		}
		else {
			return new $class_name( $config, $bypass, $product_ids );
		}
	}
}
