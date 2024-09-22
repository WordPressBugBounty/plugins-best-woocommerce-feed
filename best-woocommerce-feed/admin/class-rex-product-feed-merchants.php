<?php
/**
 * Class Rex_Feed_Merchants
 *
 * @link       https://rextheme.com
 * @since      7.3.0
 *
 * @package    Rex_Product_Feed
 */

/**
 * Helper Class to retrieve Feed Merchants
 *
 * @link       https://rextheme.com
 * @since      7.3.0
 *
 * @package    Rex_Product_Feed
 */
class Rex_Feed_Merchants {

	/**
	 * Retrieves all the merchant lists [free and pro]
	 *
	 * @return mixed|void
	 * @since 7.3.0
	 */
	public static function get_merchants() {
		$popular = array(
			'custom'    => array(
				'free'    => true,
				'name'    => 'Custom',
				'formats' => array( 'xml', 'csv', 'text', 'tsv' ),
			),
			'google'    => array(
				'free'    => true,
				'name'    => 'Google Shopping',
				'formats' => array( 'xml', 'text' ),
			),
			'facebook'  => array(
				'free'           => true,
				'name'           => 'Facebook Catalog',
				'formats'        => array( 'xml', 'csv' ),
				'csv_separators' => array( 'comma', 'semi_colon' ),
			),
			'tiktok'  => array(
				'free'           => true,
				'name'           => 'TikTok Ads',
				'formats'        => array( 'xml', 'csv', 'tsv' ),
				'csv_separators' => array( 'comma', 'semi_colon' ),
			),
			'instagram' => array(
				'free'    => true,
				'name'    => 'Instagram (by Facebook)',
				'formats' => array( 'xml', 'csv', 'tsv' ),
			),
			'twitter' => array(
				'free'    => true,
				'name'    => 'X (Twitter) Shopping',
				'formats' => array( 'xml', 'csv', 'tsv' ),
			),
			'pinterest' => array(
				'free'    => true,
				'name'    => 'Pinterest',
				'formats' => array( 'xml', 'csv', 'tsv' ),
			),
			'snapchat'  => array(
				'free'    => true,
				'name'    => 'Snapchat',
				'formats' => array( 'csv' ),
			),
			'bing'      => array(
				'free'    => true,
				'name'    => 'Bing',
				'formats' => array( 'xml', 'text' ),
			),
			'yandex'    => array(
				'free'    => true,
				'name'    => 'Yandex',
				'formats' => array( 'xml' ),
			),
			'rakuten'   => array(
				'free'    => true,
				'name'    => 'Rakuten',
				'formats' => array( 'xml', 'csv', 'tsv' ),
			),
			'vivino'    => array(
				'free'    => true,
				'name'    => 'Vivino',
				'formats' => array( 'xml', 'csv' ),
			),
		);
		$pro     = array(
			'google_review' => array(
				'free'    => false,
				'name'    => 'Google Review',
				'formats' => array( 'xml' ),
			),
			'ebay_mip'      => array(
				'free'           => false,
				'name'           => 'eBay (MIP)',
				'formats'        => array( 'csv' ),
				'csv_separators' => array( 'comma', 'semi_colon' ),
			),
			'drm'           => array(
				'free'    => false,
				'name'    => 'Google Remarketing (DRM)',
				'formats' => array(),
			),
			'leguide'       => array(
				'free'           => false,
				'name'           => 'Leguide',
				'formats'        => array( 'xml', 'csv' ),
				'csv_separators' => array( 'comma', 'semi_colon' ),
			),
		);
		$free    = array(
			'google_custom_search_ads'        => array(
				'free'    => true,
				'name'    => 'Google Custom Search Ads',
				'formats' => array( 'csv' ),
			),
			'google_Ad'                       => array(
				'free'    => true,
				'name'    => 'Google Dynamic Display Ads',
				'formats' => array( 'xml' ),
			),
			'google_local_products'           => array(
				'free'    => true,
				'name'    => 'Google Local Products',
				'formats' => array( 'xml', 'text', 'csv' ),
			),
			'google_local_products_inventory' => array(
				'free'    => true,
				'name'    => 'Google Local Products Inventory',
				'formats' => array( 'xml', 'text' ),
			),
			'google_merchant_promotion'       => array(
				'free'    => true,
				'name'    => 'Google Merchant Promotion Feed',
				'formats' => array(),
			),
			'google_dsa'                      => array(
				'free'    => true,
				'name'    => 'Google Dynamic Search Ads',
				'formats' => array(),
			),
			'google_shopping_actions'         => array(
				'free'    => true,
				'name'    => 'Google Shopping Actions',
				'formats' => array( 'xml' ),
			),
			'adroll'                          => array(
				'free'    => true,
				'name'    => 'AdRoll',
				'formats' => array(),
			),
			'nextag'                          => array(
				'free'    => true,
				'name'    => 'Nextag',
				'formats' => array( 'xml', 'text' ),
			),
			'pricegrabber'                    => array(
				'free'    => true,
				'name'    => 'PriceGrabber',
				'formats' => array( 'xml', 'csv', 'tsv' ),
			),
			'cercavino'                       => array(
				'free'           => true,
				'name'           => 'Cercavino',
				'formats'        => array( 'text' ),
				'csv_separators' => array( 'pipe' ),
			),
			'trovino'                         => array(
				'free'           => true,
				'name'           => 'Trovino',
				'formats'        => array( 'text' ),
				'csv_separators' => array( 'pipe' ),
			),
			'bing_image'                      => array(
				'free'    => true,
				'name'    => 'Bing Image',
				'formats' => array( 'xml' ),
			),
			'kelkoo'                          => array(
				'free'    => true,
				'name'    => 'Kelkoo',
				'formats' => array(),
			),
			'become'                          => array(
				'free'    => true,
				'name'    => 'Become',
				'formats' => array(),
			),
			'shopzilla'                       => array(
				'free'    => true,
				'name'    => 'ShopZilla',
				'formats' => array( 'text' ),
			),
			'shopping'                        => array(
				'free'    => true,
				'name'    => 'Shopping',
				'formats' => array( 'xml', 'csv', 'tsv', 'text' ),
			),
			'pricerunner'                     => array(
				'free'    => true,
				'name'    => 'PriceRunner',
				'formats' => array( 'xml', 'text' ),
			),
			'billiger'                        => array(
				'free'    => true,
				'name'    => 'Billiger',
				'formats' => array( 'csv', 'text' ),
			),
			'vergelijk'                       => array(
				'free'    => true,
				'name'    => 'Vergelijk',
				'formats' => array( 'xml', 'csv' ),
			),
			'marktplaats'                     => array(
				'free'    => true,
				'name'    => 'Marktplaats',
				'formats' => array(),
			),
			'beslist'                         => array(
				'free'    => true,
				'name'    => 'Beslist',
				'formats' => array( 'xml', 'csv', 'text' ),
			),
			'daisycon'                        => array(
				'free'    => true,
				'name'    => 'Daisycon',
				'formats' => array( 'xml' ),
			),
			'twenga'                          => array(
				'free'    => true,
				'name'    => 'Twenga',
				'formats' => array( 'xml', 'csv', 'text' ),
			),
			'kieskeurig'                      => array(
				'free'    => true,
				'name'    => 'Kieskeurig.nl',
				'formats' => array( 'xml', 'csv' ),
			),
			'spartoo'                         => array(
				'free'    => true,
				'name'    => 'Spartoo.nl',
				'formats' => array( 'xml' ),
			),
			'spartooFr'                       => array(
				'free'    => true,
				'name'    => 'SpartooFr',
				'formats' => array( 'xml', 'csv' ),
			),
			'tweakers'                        => array(
				'free'    => true,
				'name'    => 'Tweakers.nl',
				'formats' => array( 'xml' ),
			),
			'sooqr'                           => array(
				'free'    => true,
				'name'    => 'Sooqr',
				'formats' => array( 'xml', 'csv' ),
			),
			'koopkeus'                        => array(
				'free'    => true,
				'name'    => 'Koopkeus',
				'formats' => array( 'xml' ),
			),
			'scoupz'                          => array(
				'free'    => true,
				'name'    => 'Scoupz',
				'formats' => array( 'xml', 'csv' ),
			),
			'cdiscount'                       => array(
				'free'    => true,
				'name'    => 'Cdiscount',
				'formats' => array(),
			),
			'kelkoonl'                        => array(
				'free'    => true,
				'name'    => 'Kelkoo.nl',
				'formats' => array(),
			),
			'uvinum'                          => array(
				'free'    => true,
				'name'    => 'Uvinum / DrinsksAndCo',
				'formats' => array(),
			),
			'idealo'                          => array(
				'free'    => true,
				'name'    => 'Idealo',
				'formats' => array( 'csv' ),
			),
			'pricesearcher'                   => array(
				'free'    => true,
				'name'    => 'Pricesearcher',
				'formats' => array( 'xml' ),
			),
			'pricemasher'                     => array(
				'free'    => true,
				'name'    => 'Pricemasher',
				'formats' => array(),
			),
			'fashionchick'                    => array(
				'free'    => true,
				'name'    => 'Fashionchick',
				'formats' => array( 'xml', 'csv', 'text' ),
			),
			'ceneo'                           => array(
				'free'    => true,
				'name'    => 'Ceneo',
				'formats' => array( 'xml' ),
			),
			'choozen'                         => array(
				'free'    => true,
				'name'    => 'Choozen',
				'formats' => array( 'xml', 'csv', 'text' ),
			),
			'rss'                             => array(
				'free'    => true,
				'name'    => 'RSS',
				'formats' => array( 'rss' ),
			),
			'ciao'                            => array(
				'free'    => true,
				'name'    => 'Ciao',
				'formats' => array( 'xml' ),
			),
			'prisjkat'                        => array(
				'free'    => true,
				'name'    => 'Pricespy/Prisjkat',
				'formats' => array( 'xml', 'tsv' ),
			),
			'crowdfox'                        => array(
				'free'    => true,
				'name'    => 'Crowdfox',
				'formats' => array( 'csv' ),
			),
			'powerreviews'                    => array(
				'free'    => true,
				'name'    => 'PowerReviews',
				'formats' => array( 'xml', 'csv', 'text' ),
			),
			'trovaprezzi'                     => array(
				'free'    => true,
				'name'    => 'Trovaprezzi',
				'formats' => array( 'xml', 'csv' ),
			),
			'zbozi'                           => array(
				'free'    => true,
				'name'    => 'Zbozi',
				'formats' => array( 'xml' ),
			),
			'liveintent'                      => array(
				'free'    => true,
				'name'    => 'LiveIntent',
				'formats' => array( 'xml' ),
			),
			'skroutz'                         => array(
				'free'    => true,
				'name'    => 'Skroutz',
				'formats' => array( 'xml' ),
			),
			'otto'                            => array(
				'free'    => true,
				'name'    => 'Otto',
				'formats' => array(),
			),
			'sears'                           => array(
				'free'    => true,
				'name'    => 'Sears',
				'formats' => array( 'xml' ),
			),
			'ammoseek'                        => array(
				'free'    => true,
				'name'    => 'AmmoSeek',
				'formats' => array( 'xml' ),
			),
			'fnac'                            => array(
				'free'    => true,
				'name'    => 'Fnac',
				'formats' => array( 'xml', 'csv' ),
			),
			'zalando'                         => array(
				'free'    => true,
				'name'    => 'Zalando',
				'formats' => array( 'csv' ),
			),
			'zalando_stock_update'            => array(
				'free'    => true,
				'name'    => 'Zalando Stock Update',
				'formats' => array( 'csv' ),
			),
			'pixmania'                        => array(
				'free'    => true,
				'name'    => 'Pixmania',
				'formats' => array(),
			),
			'coolblue'                        => array(
				'free'    => true,
				'name'    => 'Coolblue',
				'formats' => array(),
			),
			'shopmania'                       => array(
				'free'    => true,
				'name'    => 'ShopMania',
				'formats' => array( 'xml', 'csv', 'text' ),
			),
			'kleding'                         => array(
				'free'    => true,
				'name'    => 'Kleding',
				'formats' => array(),
			),
			'ladenzeile'                      => array(
				'free'    => true,
				'name'    => 'Ladenzeile',
				'formats' => array(),
			),
			'preis'                           => array(
				'free'    => true,
				'name'    => 'Preis',
				'formats' => array( 'csv' ),
			),
			'winesearcher'                    => array(
				'free'    => true,
				'name'    => 'Winesearcher',
				'formats' => array( 'xml', 'text' ),
			),
			'walmart'                         => array(
				'free'    => true,
				'name'    => 'Walmart',
				'formats' => array( 'csv' ),
			),
			'verizon'                         => array(
				'free'    => true,
				'name'    => 'Yahoo/Verizon Dynamic Product Ads',
				'formats' => array( 'xml' ),
			),
			'kelkoo_group'                    => array(
				'free'    => true,
				'name'    => 'KelkooGroup',
				'formats' => array( 'xml', 'text' ),
			),
			'target'                          => array(
				'free'    => true,
				'name'    => 'Target',
				'formats' => array(),
			),
			'pepperjam'                       => array(
				'free'    => true,
				'name'    => 'Pepperjam',
				'formats' => array( 'xml' ),
			),
			'cj_affiliate'                    => array(
				'free'    => true,
				'name'    => 'CJ Affiliate',
				'formats' => array(),
			),
			'guenstiger'                      => array(
				'free'    => true,
				'name'    => 'Guenstiger',
				'formats' => array( 'xml', 'csv', 'text' ),
			),
			'hood'                            => array(
				'free'    => true,
				'name'    => 'Hood',
				'formats' => array(),
			),
			'livingo'                         => array(
				'free'    => true,
				'name'    => 'Livingo',
				'formats' => array(),
			),
			'jet'                             => array(
				'free'    => true,
				'name'    => 'Jet',
				'formats' => array(),
			),
			'bonanza'                         => array(
				'free'    => true,
				'name'    => 'Bonanza',
				'formats' => array(),
			),
			'adcell'                          => array(
				'free'    => true,
				'name'    => 'Adcell',
				'formats' => array(),
			),
			'adform'                          => array(
				'free'    => true,
				'name'    => 'Adform',
				'formats' => array(),
			),
			'stylefruits'                     => array(
				'free'    => true,
				'name'    => 'Stylefruits',
				'formats' => array(),
			),
			'medizinfuchs'                    => array(
				'free'    => true,
				'name'    => 'Medizinfuchs',
				'formats' => array(),
			),
			'moebel'                          => array(
				'free'    => true,
				'name'    => 'Moebel',
				'formats' => array( 'csv' ),
			),
			'restposten'                      => array(
				'free'    => true,
				'name'    => 'Restposten',
				'formats' => array(),
			),
			'sparmedo'                        => array(
				'free'    => true,
				'name'    => 'Sparmedo',
				'formats' => array(),
			),
			'whiskymarketplace'               => array(
				'free'    => true,
				'name'    => 'Whiskymarketplace',
				'formats' => array(),
			),
			'newegg'                          => array(
				'free'    => true,
				'name'    => 'NewEgg',
				'formats' => array( 'xml', 'csv', 'text' ),
			),
			'123i'                            => array(
				'free'    => true,
				'name'    => '123i',
				'formats' => array(),
			),
			'adcrowd'                         => array(
				'free'    => true,
				'name'    => 'Adcrowd',
				'formats' => array( 'xml' ),
			),
			'bikeexchange'                    => array(
				'free'    => true,
				'name'    => 'Bike Exchange',
				'formats' => array(),
			),
			'cenowarka'                       => array(
				'free'    => true,
				'name'    => 'Cenowarka',
				'formats' => array( 'xml', 'csv' ),
			),
			'cezigue'                         => array(
				'free'    => true,
				'name'    => 'Cezigue',
				'formats' => array(),
			),
			'check24'                         => array(
				'free'    => true,
				'name'    => 'Check24',
				'formats' => array(),
			),
			'clang'                           => array(
				'free'    => true,
				'name'    => 'Clang',
				'formats' => array(),
			),
			'cherchons'                       => array(
				'free'    => true,
				'name'    => 'Cherchons',
				'formats' => array(),
			),
			'boetiek'                         => array(
				'free'    => true,
				'name'    => 'Boetiek B.V',
				'formats' => array(),
			),
			'comparer'                        => array(
				'free'    => true,
				'name'    => 'Comparer',
				'formats' => array( 'xml' ),
			),
			'converto'                        => array(
				'free'    => true,
				'name'    => 'Converto',
				'formats' => array(),
			),
			'coolshop'                        => array(
				'free'    => true,
				'name'    => 'Coolshop',
				'formats' => array(),
			),
			'commerce_connector'              => array(
				'free'    => true,
				'name'    => 'Commerce Connector',
				'formats' => array( 'csv' ),
			),
			'everysize'                       => array(
				'free'    => true,
				'name'    => 'Everysize',
				'formats' => array(),
			),
			'encuentraprecios'                => array(
				'free'    => true,
				'name'    => 'Encuentraprecios',
				'formats' => array(),
			),
			'geizhals'                        => array(
				'free'    => true,
				'name'    => 'Geizhals',
				'formats' => array( 'xml', 'csv' ),
			),
			'geizkragen'                      => array(
				'free'    => true,
				'name'    => 'Geizkragen',
				'formats' => array(),
			),
			'giftboxx'                        => array(
				'free'    => true,
				'name'    => 'Giftboxx',
				'formats' => array(),
			),
			'go_banana'                       => array(
				'free'    => true,
				'name'    => 'Go Banana',
				'formats' => array(),
			),
			'goed_geplaatst'                  => array(
				'free'    => true,
				'name'    => 'Goed Geplaatst',
				'formats' => array(),
			),
			'grosshandel'                     => array(
				'free'    => true,
				'name'    => 'Grosshandel',
				'formats' => array(),
			),
			'hardware'                        => array(
				'free'    => true,
				'name'    => 'Hardware.info',
				'formats' => array( 'csv' ),
			),
			'hatch'                           => array(
				'free'    => true,
				'name'    => 'Hatch',
				'formats' => array(),
			),
			'hintaopas'                       => array(
				'free'    => true,
				'name'    => 'Hintaopas',
				'formats' => array(),
			),
			'fyndiq'                          => array(
				'free'    => true,
				'name'    => 'Fyndiq.se',
				'formats' => array( 'csv' ),
			),
			'fasha'                           => array(
				'free'    => true,
				'name'    => 'Fasha',
				'formats' => array(),
			),
			'realde'                          => array(
				'free'    => true,
				'name'    => 'Real.de',
				'formats' => array(),
			),
			'hintaseuranta'                   => array(
				'free'    => true,
				'name'    => 'Hintaseuranta',
				'formats' => array(),
			),
			'family_blend'                    => array(
				'free'    => true,
				'name'    => 'Family Blend',
				'formats' => array(),
			),
			'hitmeister'                      => array(
				'free'    => true,
				'name'    => 'Hitmeister',
				'formats' => array(),
			),
			'lazada'                          => array(
				'free'    => true,
				'name'    => 'Lazada',
				'formats' => array( 'csv' ),
			),
			'get_price'                       => array(
				'free'    => true,
				'name'    => 'GetPrice.com.au',
				'formats' => array( 'xml', 'csv', 'text' ),
			),
			'home_tiger'                      => array(
				'free'    => true,
				'name'    => 'HomeTiger',
				'formats' => array(),
			),
			'jurkjes'                         => array(
				'free'    => true,
				'name'    => 'Jurkjes.nl',
				'formats' => array(),
			),
			'kiesproduct'                     => array(
				'free'    => true,
				'name'    => 'Kiesproduct',
				'formats' => array(),
			),
			'kiyoh'                           => array(
				'free'    => true,
				'name'    => 'Kiyoh',
				'formats' => array( 'xml' ),
			),
			'kompario'                        => array(
				'free'    => true,
				'name'    => 'Kompario',
				'formats' => array(),
			),
			'kwanko'                          => array(
				'free'    => true,
				'name'    => 'Kwanko',
				'formats' => array(),
			),
			'ledenicheur'                     => array(
				'free'    => true,
				'name'    => 'Le Dénicheur',
				'formats' => array( 'xml', 'csv', 'text' ),
			),
			'les_bonnes_bouilles'             => array(
				'free'    => true,
				'name'    => 'Les Bonnes Bouilles',
				'formats' => array(),
			),
			'lions_home'                      => array(
				'free'    => true,
				'name'    => 'Lions Home',
				'formats' => array(),
			),
			'locamo'                          => array(
				'free'    => true,
				'name'    => 'Locamo',
				'formats' => array(),
			),
			'logicsale'                       => array(
				'free'    => true,
				'name'    => 'Logicsale',
				'formats' => array(),
			),
			'google_manufacturer_center'      => array(
				'free'    => true,
				'name'    => 'Google Manufacturer Center',
				'formats' => array( 'xml', 'tsv' ),
			),
			'google_express'                  => array(
				'free'    => true,
				'name'    => 'Google Express',
				'formats' => array( 'xml' ),
			),
			'pronto'                          => array(
				'free'    => true,
				'name'    => 'Pronto',
				'formats' => array(),
			),
			'awin'                            => array(
				'free'    => true,
				'name'    => 'Awin',
				'formats' => array( 'xml', 'csv', 'tsv' ),
			),
			'indeed'                          => array(
				'free'    => true,
				'name'    => 'Indeed',
				'formats' => array(),
			),
			'incurvy'                         => array(
				'free'    => true,
				'name'    => 'Incurvy',
				'formats' => array(),
			),
			'jobbird'                         => array(
				'free'    => true,
				'name'    => 'Jobbird',
				'formats' => array(),
			),
			'job_board_io'                    => array(
				'free'    => true,
				'name'    => 'JobBoard.io',
				'formats' => array(),
			),
			'joblift'                         => array(
				'free'    => true,
				'name'    => 'Joblift',
				'formats' => array(),
			),
			'kuantokusta'                     => array(
				'free'    => true,
				'name'    => 'KuantoKusta',
				'formats' => array(),
			),
			'kauftipp'                        => array(
				'free'    => true,
				'name'    => 'Kauftipp',
				'formats' => array(),
			),
			'rakuten_advertising'             => array(
				'free'    => true,
				'name'    => 'Rakuten Advertising',
				'formats' => array( 'csv', 'tsv', 'text' ),
			),
			'pricefalls'                      => array(
				'free'    => true,
				'name'    => 'Pricefalls Feed',
				'formats' => array( 'csv', 'text' ),
			),
			'clubic'                          => array(
				'free'    => true,
				'name'    => 'Clubic',
				'formats' => array(),
			),
			'criteo'                          => array(
				'free'    => true,
				'name'    => 'Criteo',
				'formats' => array( 'xml', 'csv', 'tsv' ),
			),
			'shopalike'                       => array(
				'free'    => true,
				'name'    => 'Shopalike',
				'formats' => array(),
			),
			'compartner'                      => array(
				'free'    => true,
				'name'    => 'Compartner',
				'formats' => array( 'xml' ),
			),
			'adtraction'                      => array(
				'free'    => true,
				'name'    => 'Adtraction',
				'formats' => array(),
			),
			'admitad'                         => array(
				'free'    => true,
				'name'    => 'Admitad',
				'formats' => array( 'xml', 'csv' ),
			),
			'bloomville'                      => array(
				'free'    => true,
				'name'    => 'Bloomville',
				'formats' => array(),
			),
			'datatrics'                       => array(
				'free'    => true,
				'name'    => 'Datatrics',
				'formats' => array(),
			),
			'deltaprojects'                   => array(
				'free'    => true,
				'name'    => 'Delta Projects',
				'formats' => array(),
			),
			'drezzy'                          => array(
				'free'    => true,
				'name'    => 'Drezzy',
				'formats' => array(),
			),
			'domodi'                          => array(
				'free'    => true,
				'name'    => 'Domodi',
				'formats' => array( 'xml' ),
			),
			'doofinder'                       => array(
				'free'    => true,
				'name'    => 'Doofinder',
				'formats' => array( 'xml' ),
			),
			'homebook'                        => array(
				'free'    => true,
				'name'    => 'Homebook.pl',
				'formats' => array(),
			),
			'homedeco'                        => array(
				'free'    => true,
				'name'    => 'Home Deco',
				'formats' => array(),
			),
			'glami'                           => array(
				'free'    => true,
				'name'    => 'Glami',
				'formats' => array( 'xml' ),
			),
			'fashiola'                        => array(
				'free'    => true,
				'name'    => 'Fashiola',
				'formats' => array(),
			),
			'emarts'                          => array(
				'free'    => true,
				'name'    => 'Emarts',
				'formats' => array( 'xml' ),
			),
			'epoq'                            => array(
				'free'    => true,
				'name'    => 'Epoq',
				'formats' => array( 'xml' ),
			),
			'grupo_zap'                       => array(
				'free'    => true,
				'name'    => 'Grupo Zap',
				'formats' => array( 'xml' ),
			),
			'emag'                            => array(
				'free'    => true,
				'name'    => 'Emag',
				'formats' => array(),
			),
			'lyst'                            => array(
				'free'    => true,
				'name'    => 'Lyst',
				'formats' => array(),
			),
			'listupp'                         => array(
				'free'    => true,
				'name'    => 'Listupp',
				'formats' => array(),
			),
			'hertie'                          => array(
				'free'    => true,
				'name'    => 'Hertie',
				'formats' => array(),
			),
			'webgains'                        => array(
				'free'    => true,
				'name'    => ' Webgains',
				'formats' => array( 'xml', 'csv', 'text' ),
			),
			'vidaXL'                          => array(
				'free'    => true,
				'name'    => 'VidaXL',
				'formats' => array( 'xml', 'csv' ),
			),
			'mydeal'                          => array(
				'free'    => true,
				'name'    => 'My Deal',
				'formats' => array(),
			),
			'idealo_de'                       => array(
				'free'    => true,
				'name'    => 'Idealo.de',
				'formats' => array( 'csv' ),
			),
			'favi'                            => array(
				'free'    => true,
				'name'    => 'Favi - Compari & Árukereső',
				'formats' => array(),
			),
			'ibud'                            => array(
				'free'    => true,
				'name'    => 'Ibud',
				'formats' => array( 'xml' ),
			),
			'google_local_inventory_ads'      => array(
				'free'    => true,
				'name'    => 'Google Local Inventory Ads',
				'formats' => array( 'xml', 'text' ),
			),
			'DealsForU'                       => array(
				'free'    => true,
				'name'    => 'Deals4u.gr',
				'formats' => array( 'xml' ),
			),
			'Bestprice'                       => array(
				'free'    => true,
				'name'    => 'Bestprice',
				'formats' => array( 'xml' ),
			),
			'mirakl'                          => array(
				'free'    => true,
				'name'    => 'Mirakl',
				'formats' => array( 'xml' ),
			),
			'lesitedumif'                     => array(
				'free'    => true,
				'name'    => 'Lesitedumif',
				'formats' => array( 'csv' ),
			),
			'shopee'                          => array(
				'free'    => true,
				'name'    => 'Shopee',
				'formats' => array( 'csv' ),
			),
			'gulog_gratis'                    => array(
				'free'    => true,
				'name'    => 'GulogGratis.dk',
				'formats' => array( 'xml' ),
			),
			'ebay_seller'                     => array(
				'free'    => true,
				'name'    => 'eBay Seller Center',
				'formats' => array( 'csv' ),
			),
			'ebay_seller_tickets'             => array(
				'free'    => true,
				'name'    => 'eBay Seller Center (Event tickets)',
				'formats' => array( 'csv' ),
			),
			'fruugo'                          => array(
				'free'    => true,
				'name'    => 'Fruugo',
				'formats' => array( 'csv' ),
			),
			'bol'                             => array(
				'free'    => true,
				'name'    => 'Bol.com',
				'formats' => array( 'csv' ),
			),
			'connexity'                       => array(
				'free'    => true,
				'name'    => 'Connexity',
				'formats' => array( 'csv', 'text' ),
			),
			'heureka'                         => array(
				'free'    => true,
				'name'    => 'Heureka',
				'formats' => array( 'xml' ),
			),
			'heureka_availability'            => array(
				'free'    => true,
				'name'    => 'Heureka (Availability)',
				'formats' => array( 'xml' ),
			),
			'wish'                            => array(
				'free'    => true,
				'name'    => 'Wish.com',
				'formats' => array( 'csv', 'text' ),
			),
			'zap_co_il'                       => array(
				'free'    => true,
				'name'    => 'Zap.co.il',
				'formats' => array(),
			),
			'hotline'                         => array(
				'free'    => true,
				'name'    => 'Hotline',
				'formats' => array( 'xml' ),
			),
			'rozetka'                         => array(
				'free'    => true,
				'name'    => 'Rozetka',
				'formats' => array( 'xml' ),
			),
		);

		$merchants[ 'popular' ]        = $popular;
		$merchants[ 'pro_merchants' ]  = $pro;
		$merchants[ 'free_merchants' ] = $free;

		return apply_filters( 'rex_wpfm_all_merchant', $merchants );
	}

	/**
	 * Retrieves Supported Feed Formats
	 * for a Specific Merchant
	 *
	 * @param string $merchant Merchant name.
	 * @return mixed|string[]
	 * @since 7.3.0
	 */
	public static function get_feed_formats( $merchant ) {
		$merchants = self::get_merchants();

		if ( isset( $merchants[ 'popular' ][ $merchant ][ 'formats' ] ) && !empty( $merchants[ 'popular' ][ $merchant ][ 'formats' ] ) ) {
			return $merchants[ 'popular' ][ $merchant ][ 'formats' ];
		}
		elseif ( isset( $merchants[ 'pro_merchants' ][ $merchant ][ 'formats' ] ) && !empty( $merchants[ 'pro_merchants' ][ $merchant ][ 'formats' ] ) ) {
			return $merchants[ 'pro_merchants' ][ $merchant ][ 'formats' ];
		}
		elseif ( isset( $merchants[ 'free_merchants' ][ $merchant ][ 'formats' ] ) && !empty( $merchants[ 'free_merchants' ][ $merchant ][ 'formats' ] ) ) {
			return $merchants[ 'free_merchants' ][ $merchant ][ 'formats' ];
		}
		return array( 'xml', 'csv', 'text', 'tsv' );
	}

	/**
	 * Retrieves Supported Separators for CSV Format
	 *
	 * @param string $merchant Merchant name.
	 *
	 * @return mixed|string[]
	 * @since 7.3.0
	 */
	public static function get_csv_feed_separators( $merchant ) {
		$merchants = self::get_merchants();

		if ( isset( $merchants[ 'popular' ][ $merchant ][ 'csv_separators' ] ) && !empty( $merchants[ 'popular' ][ $merchant ][ 'csv_separators' ] ) ) {
			return $merchants[ 'popular' ][ $merchant ][ 'csv_separators' ];
		}
		elseif ( isset( $merchants[ 'pro_merchants' ][ $merchant ][ 'csv_separators' ] ) && !empty( $merchants[ 'pro_merchants' ][ $merchant ][ 'csv_separators' ] ) ) {
			return $merchants[ 'pro_merchants' ][ $merchant ][ 'csv_separators' ];
		}
		elseif ( isset( $merchants[ 'free_merchants' ][ $merchant ][ 'csv_separators' ] ) && !empty( $merchants[ 'free_merchants' ][ $merchant ][ 'csv_separators' ] ) ) {
			return $merchants[ 'free_merchants' ][ $merchant ][ 'csv_separators' ];
		}
		return array( 'comma', 'semi_colon', 'pipe' );
	}

	/**
	 * Renders the merchant dropdown in feed
	 *
	 * @param string     $class Class name.
	 * @param string|int $id Dropdown id.
	 * @param string     $name Dropdown name.
	 * @param string     $selected Pre-selected value.
	 *
	 * @return void
	 * @since 7.3.0
	 */
	public static function render_merchant_dropdown( $class, $id, $name, $selected ) {
		$all_merchants[''] = array(
			'-1' => array(
				'free'   => true,
				'status' => 1,
				'name'   => __( 'Select your merchant', 'rex-product-feed' )
			),
		);
        $all_merchants = array_merge( $all_merchants, self::get_merchants() );
        $is_premium    = apply_filters( 'wpfm_is_premium', false );

		echo '<select class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '">';
		foreach ( $all_merchants as $group_label => $group ) {
			if ( !empty( $group_label ) ) {
				if ( 'popular' === $group_label ) {
					$group_label = __( 'Popular Merchants', 'rex-product-feed' );
				}
				elseif ( 'pro_merchants' === $group_label ) {
					$group_label = __( 'Pro Merchants', 'rex-product-feed' );
				}
				elseif ( 'free_merchants' === $group_label ) {
					$group_label = __( 'Others', 'rex-product-feed' );
				}
				$disabled = ( 'Pro Merchants' === $group_label && !$is_premium ) ? 'disabled' : '';
				ob_start();?>
				<optgroup label='<?php echo esc_html( $group_label ); ?>' <?php echo esc_html( $disabled ); ?>>
				<?php
				echo ob_get_clean(); //phpcs:ignore
			}

			foreach ( $group as $key => $item ) {
				$value = $item['name'];

				ob_start();
				if ( $selected === $key ) {
					?>
						<option value='<?php echo esc_attr( $key ); ?>' selected='selected'><?php echo esc_html( $value ); ?></option>
					<?php
				}
				else {
					?>
						<option value='<?php echo esc_attr( $key ); ?>'><?php echo esc_html( $value ); ?></option>
					<?php
				}
                echo ob_get_clean(); //phpcs:ignore
			}

			if ( !empty( $group_label ) ) {
				echo "</optgroup>";
			}
		}

		echo "</select>";
	}
}
