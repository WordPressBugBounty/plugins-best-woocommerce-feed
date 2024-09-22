<?php

/**
 * The file that generates xml feed for Zalando_stock_update.
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

class Rex_Product_Feed_Bing_image extends Rex_Product_Feed_Abstract_Generator
{

	private $product_data = array();

	/**
	 * Create Feed for Google
	 *
	 * @return boolean
	 * @author
	 **/
	public function make_feed()
	{

		RexShopping::$container = null;
		RexShopping::title( $this->title );
		RexShopping::link( $this->link );
		RexShopping::description( $this->desc );

		$this->generate_product_feed();

		if ( $this->batch >= $this->tbatch ) {
			$this->save_json_feed( 'json' );

			return array(
				'msg' => 'finish'
			);
		}
		else {
			return $this->save_json_feed( 'json' );
		}
	}


	/**
	 * Generate feed
	 */
	protected function generate_product_feed()
	{
        $total_products = get_post_meta( $this->id, '_rex_feed_total_products', true );
        $total_products = $total_products ?: get_post_meta( $this->id, 'rex_feed_total_products', true );
		$product_meta_keys  = Rex_Feed_Attributes::get_attributes();
		$simple_products    = [];
		$variation_products = [];
		$group_products     = [];
		$variable_parent    = [];
		$total_products     = $total_products ?: array(
			'total'           => 0,
			'simple'          => 0,
			'variable'        => 0,
			'variable_parent' => 0,
			'group'           => 0,
		);

		if ( $this->batch == 1 ) {
			$total_products = array(
				'total'           => 0,
				'simple'          => 0,
				'variable'        => 0,
				'variable_parent' => 0,
				'group'           => 0,
			);
		}

		$this->feed = array(
			'_type'         => "images",
			'name'          => get_bloginfo(),
			'url'           => get_site_url(),
			'language'      => $this->get_language( get_locale() ),
			'datePublished' => date( 'Y-m-d' ),
			'itemCount'     => 0,
			'images'        => array()
		);

		foreach ( $this->products as $productId ) {
			$product = wc_get_product( $productId );

			if ( !is_object( $product ) ) {
				continue;
			}

			if ( $this->exclude_hidden_products ) {
				if ( !$product->is_visible() ) {
					continue;
				}
			}

            if ( ( !$this->include_out_of_stock )
                && ( !$product->is_in_stock()
                    || $product->is_on_backorder()
                    || (is_integer($product->get_stock_quantity()) && 0 >= $product->get_stock_quantity())
                )
            ) {
                continue;
            }

            if( !$this->include_zero_priced ) {
                $product_price = rex_feed_get_product_price($product);
                if( 0 == $product_price || '' == $product_price ) {
                    continue;
                }
            }

			if ( $product->is_type( 'variable' ) && $product->has_child() ) {
				$variable_parent[] = $productId;
				$parent_atts       = $this->get_product_data( $product, $product_meta_keys );
				$item              = RexShopping::createItem();

				if ( $this->exclude_hidden_products ) {
					$variations = $product->get_visible_children();
				}
				else {
					$variations = $product->get_children();
				}
				$atts = array();
				if ( $variations ) {
					foreach ( $variations as $variation ) {
						if ( $this->variations ) {
							$variation_products[] = $variation;
							$variation_product    = wc_get_product( $variation );
							$atts[]               = $this->get_product_data( $variation_product, $product_meta_keys );
						}
					}
				}

				$json_array = $this->get_product_model( $parent_atts );

				foreach ( $atts as $att ) {
					$json_array = $this->get_product_model( $att );
				}
				$this->feed[ 'images' ][] = $json_array;
			}

			if ( $product->is_type( 'simple' ) ) {

				$simple_products[]        = $productId;
				$atts                     = $this->get_product_data( $product, $product_meta_keys );
				$json_array               = $this->get_product_model( $atts );
				$this->feed[ 'images' ][] = $json_array;
			}
		}

		$total_products = array(
			'total'           => (int) $total_products[ 'total' ] + (int) count( $simple_products ) + (int) count( $variation_products ) + (int) count( $group_products ) + (int) count( $variable_parent ),
			'simple'          => (int) $total_products[ 'simple' ] + (int) count( $simple_products ),
			'variable'        => (int) $total_products[ 'variable' ] + (int) count( $variation_products ),
			'variable_parent' => (int) $total_products[ 'variable_parent' ] + (int) count( $variable_parent ),
			'group'           => (int) $total_products[ 'group' ] + (int) count( $group_products ),
		);

		$this->feed[ 'itemCount' ] = $total_products[ 'total' ];
		update_post_meta( $this->id, '_rex_feed_total_products', $total_products );
		if ( $this->tbatch === $this->batch ) {
			update_post_meta( $this->id, '_rex_feed_total_products_for_all_feed', $total_products[ 'total' ] );
		}
	}

	/**
	 * @desc Get current site language
	 *
	 * @param $value
	 */
	private function get_language( $value )
	{
		$languages = array(
			'aa_DJ'  => 'Afar (Djibouti)',
			'aa_ER'  => 'Afar (Eritrea)',
			'aa_ET'  => 'Afar (Ethiopia)',
			'af_ZA'  => 'Afrikaans (South Africa)',
			'sq_AL'  => 'Albanian (Albania)',
			'sq_MK'  => 'Albanian (Macedonia)',
			'am_ET'  => 'Amharic (Ethiopia)',
			'ar_DZ'  => 'Arabic (Algeria)',
			'ar_BH'  => 'Arabic (Bahrain)',
			'ar_EG'  => 'Arabic (Egypt)',
			'ar_IN'  => 'Arabic (India)',
			'ar_IQ'  => 'Arabic (Iraq)',
			'ar_JO'  => 'Arabic (Jordan)',
			'ar_KW'  => 'Arabic (Kuwait)',
			'ar_LB'  => 'Arabic (Lebanon)',
			'ar_LY'  => 'Arabic (Libya)',
			'ar_MA'  => 'Arabic (Morocco)',
			'ar_OM'  => 'Arabic (Oman)',
			'ar_QA'  => 'Arabic (Qatar)',
			'ar_SA'  => 'Arabic (Saudi Arabia)',
			'ar_SD'  => 'Arabic (Sudan)',
			'ar_SY'  => 'Arabic (Syria)',
			'ar_TN'  => 'Arabic (Tunisia)',
			'ar_AE'  => 'Arabic (United Arab Emirates)',
			'ar_YE'  => 'Arabic (Yemen)',
			'an_ES'  => 'Aragonese (Spain)',
			'hy_AM'  => 'Armenian (Armenia)',
			'as_IN'  => 'Assamese (India)',
			'ast_ES' => 'Asturian (Spain)',
			'az_AZ'  => 'Azerbaijani (Azerbaijan)',
			'az_TR'  => 'Azerbaijani (Turkey)',
			'eu_FR'  => 'Basque (France)',
			'eu_ES'  => 'Basque (Spain)',
			'be_BY'  => 'Belarusian (Belarus)',
			'bem_ZM' => 'Bemba (Zambia)',
			'bn_BD'  => 'Bengali (Bangladesh)',
			'bn_IN'  => 'Bengali (India)',
			'ber_DZ' => 'Berber (Algeria)',
			'ber_MA' => 'Berber (Morocco)',
			'byn_ER' => 'Blin (Eritrea)',
			'bs_BA'  => 'Bosnian (Bosnia and Herzegovina)',
			'br_FR'  => 'Breton (France)',
			'bg_BG'  => 'Bulgarian (Bulgaria)',
			'my_MM'  => 'Burmese (Myanmar [Burma])',
			'ca_AD'  => 'Catalan (Andorra)',
			'ca_FR'  => 'Catalan (France)',
			'ca_IT'  => 'Catalan (Italy)',
			'ca_ES'  => 'Catalan (Spain)',
			'zh_CN'  => 'Chinese (China)',
			'zh_HK'  => 'Chinese (Hong Kong SAR China)',
			'zh_SG'  => 'Chinese (Singapore)',
			'zh_TW'  => 'Chinese (Taiwan)',
			'cv_RU'  => 'Chuvash (Russia)',
			'kw_GB'  => 'Cornish (United Kingdom)',
			'crh_UA' => 'Crimean Turkish (Ukraine)',
			'hr_HR'  => 'Croatian (Croatia)',
			'cs_CZ'  => 'Czech (Czech Republic)',
			'da_DK'  => 'Danish (Denmark)',
			'dv_MV'  => 'Divehi (Maldives)',
			'nl_AW'  => 'Dutch (Aruba)',
			'nl_BE'  => 'Dutch (Belgium)',
			'nl_NL'  => 'Dutch (Netherlands)',
			'dz_BT'  => 'Dzongkha (Bhutan)',
			'en_AG'  => 'English (Antigua and Barbuda)',
			'en_AU'  => 'English (Australia)',
			'en_BW'  => 'English (Botswana)',
			'en_CA'  => 'English (Canada)',
			'en_DK'  => 'English (Denmark)',
			'en_HK'  => 'English (Hong Kong SAR China)',
			'en_IN'  => 'English (India)',
			'en_IE'  => 'English (Ireland)',
			'en_NZ'  => 'English (New Zealand)',
			'en_NG'  => 'English (Nigeria)',
			'en_PH'  => 'English (Philippines)',
			'en_SG'  => 'English (Singapore)',
			'en_ZA'  => 'English (South Africa)',
			'en_GB'  => 'English (United Kingdom)',
			'en_US'  => 'English (United States)',
			'en_ZM'  => 'English (Zambia)',
			'en_ZW'  => 'English (Zimbabwe)',
			'eo'     => 'Esperanto',
			'et_EE'  => 'Estonian (Estonia)',
			'fo_FO'  => 'Faroese (Faroe Islands)',
			'fil_PH' => 'Filipino (Philippines)',
			'fi_FI'  => 'Finnish (Finland)',
			'fr_BE'  => 'French (Belgium)',
			'fr_CA'  => 'French (Canada)',
			'fr_FR'  => 'French (France)',
			'fr_LU'  => 'French (Luxembourg)',
			'fr_CH'  => 'French (Switzerland)',
			'fur_IT' => 'Friulian (Italy)',
			'ff_SN'  => 'Fulah (Senegal)',
			'gl_ES'  => 'Galician (Spain)',
			'lg_UG'  => 'Ganda (Uganda)',
			'gez_ER' => 'Geez (Eritrea)',
			'gez_ET' => 'Geez (Ethiopia)',
			'ka_GE'  => 'Georgian (Georgia)',
			'de_AT'  => 'German (Austria)',
			'de_BE'  => 'German (Belgium)',
			'de_DE'  => 'German (Germany)',
			'de_LI'  => 'German (Liechtenstein)',
			'de_LU'  => 'German (Luxembourg)',
			'de_CH'  => 'German (Switzerland)',
			'el_CY'  => 'Greek (Cyprus)',
			'el_GR'  => 'Greek (Greece)',
			'gu_IN'  => 'Gujarati (India)',
			'ht_HT'  => 'Haitian (Haiti)',
			'ha_NG'  => 'Hausa (Nigeria)',
			'iw_IL'  => 'Hebrew (Israel)',
			'he_IL'  => 'Hebrew (Israel)',
			'hi_IN'  => 'Hindi (India)',
			'hu_HU'  => 'Hungarian (Hungary)',
			'is_IS'  => 'Icelandic (Iceland)',
			'ig_NG'  => 'Igbo (Nigeria)',
			'id_ID'  => 'Indonesian (Indonesia)',
			'ia'     => 'Interlingua',
			'iu_CA'  => 'Inuktitut (Canada)',
			'ik_CA'  => 'Inupiaq (Canada)',
			'ga_IE'  => 'Irish (Ireland)',
			'it_IT'  => 'Italian (Italy)',
			'it_CH'  => 'Italian (Switzerland)',
			'ja_JP'  => 'Japanese (Japan)',
			'kl_GL'  => 'Kalaallisut (Greenland)',
			'kn_IN'  => 'Kannada (India)',
			'ks_IN'  => 'Kashmiri (India)',
			'csb_PL' => 'Kashubian (Poland)',
			'kk_KZ'  => 'Kazakh (Kazakhstan)',
			'km_KH'  => 'Khmer (Cambodia)',
			'rw_RW'  => 'Kinyarwanda (Rwanda)',
			'ky_KG'  => 'Kirghiz (Kyrgyzstan)',
			'kok_IN' => 'Konkani (India)',
			'ko_KR'  => 'Korean (South Korea)',
			'ku_TR'  => 'Kurdish (Turkey)',
			'lo_LA'  => 'Lao (Laos)',
			'lv_LV'  => 'Latvian (Latvia)',
			'li_BE'  => 'Limburgish (Belgium)',
			'li_NL'  => 'Limburgish (Netherlands)',
			'lt_LT'  => 'Lithuanian (Lithuania)',
			'nds_DE' => 'Low German (Germany)',
			'nds_NL' => 'Low German (Netherlands)',
			'mk_MK'  => 'Macedonian (Macedonia)',
			'mai_IN' => 'Maithili (India)',
			'mg_MG'  => 'Malagasy (Madagascar)',
			'ms_MY'  => 'Malay (Malaysia)',
			'ml_IN'  => 'Malayalam (India)',
			'mt_MT'  => 'Maltese (Malta)',
			'gv_GB'  => 'Manx (United Kingdom)',
			'mi_NZ'  => 'Maori (New Zealand)',
			'mr_IN'  => 'Marathi (India)',
			'mn_MN'  => 'Mongolian (Mongolia)',
			'ne_NP'  => 'Nepali (Nepal)',
			'se_NO'  => 'Northern Sami (Norway)',
			'nso_ZA' => 'Northern Sotho (South Africa)',
			'nb_NO'  => 'Norwegian Bokmål (Norway)',
			'nn_NO'  => 'Norwegian Nynorsk (Norway)',
			'oc_FR'  => 'Occitan (France)',
			'or_IN'  => 'Oriya (India)',
			'om_ET'  => 'Oromo (Ethiopia)',
			'om_KE'  => 'Oromo (Kenya)',
			'os_RU'  => 'Ossetic (Russia)',
			'pap_AN' => 'Papiamento (Netherlands Antilles)',
			'ps_AF'  => 'Pashto (Afghanistan)',
			'fa_IR'  => 'Persian (Iran)',
			'pl_PL'  => 'Polish (Poland)',
			'pt_BR'  => 'Portuguese (Brazil)',
			'pt_PT'  => 'Portuguese (Portugal)',
			'pa_IN'  => 'Punjabi (India)',
			'pa_PK'  => 'Punjabi (Pakistan)',
			'ro_RO'  => 'Romanian (Romania)',
			'ru_RU'  => 'Russian (Russia)',
			'ru_UA'  => 'Russian (Ukraine)',
			'sa_IN'  => 'Sanskrit (India)',
			'sc_IT'  => 'Sardinian (Italy)',
			'gd_GB'  => 'Scottish Gaelic (United Kingdom)',
			'sr_ME'  => 'Serbian (Montenegro)',
			'sr_RS'  => 'Serbian (Serbia)',
			'sid_ET' => 'Sidamo (Ethiopia)',
			'sd_IN'  => 'Sindhi (India)',
			'si_LK'  => 'Sinhala (Sri Lanka)',
			'sk_SK'  => 'Slovak (Slovakia)',
			'sl_SI'  => 'Slovenian (Slovenia)',
			'so_DJ'  => 'Somali (Djibouti)',
			'so_ET'  => 'Somali (Ethiopia)',
			'so_KE'  => 'Somali (Kenya)',
			'so_SO'  => 'Somali (Somalia)',
			'nr_ZA'  => 'South Ndebele (South Africa)',
			'st_ZA'  => 'Southern Sotho (South Africa)',
			'es_AR'  => 'Spanish (Argentina)',
			'es_BO'  => 'Spanish (Bolivia)',
			'es_CL'  => 'Spanish (Chile)',
			'es_CO'  => 'Spanish (Colombia)',
			'es_CR'  => 'Spanish (Costa Rica)',
			'es_DO'  => 'Spanish (Dominican Republic)',
			'es_EC'  => 'Spanish (Ecuador)',
			'es_SV'  => 'Spanish (El Salvador)',
			'es_GT'  => 'Spanish (Guatemala)',
			'es_HN'  => 'Spanish (Honduras)',
			'es_MX'  => 'Spanish (Mexico)',
			'es_NI'  => 'Spanish (Nicaragua)',
			'es_PA'  => 'Spanish (Panama)',
			'es_PY'  => 'Spanish (Paraguay)',
			'es_PE'  => 'Spanish (Peru)',
			'es_ES'  => 'Spanish (Spain)',
			'es_US'  => 'Spanish (United States)',
			'es_UY'  => 'Spanish (Uruguay)',
			'es_VE'  => 'Spanish (Venezuela)',
			'sw_KE'  => 'Swahili (Kenya)',
			'sw_TZ'  => 'Swahili (Tanzania)',
			'ss_ZA'  => 'Swati (South Africa)',
			'sv_FI'  => 'Swedish (Finland)',
			'sv_SE'  => 'Swedish (Sweden)',
			'tl_PH'  => 'Tagalog (Philippines)',
			'tg_TJ'  => 'Tajik (Tajikistan)',
			'ta_IN'  => 'Tamil (India)',
			'tt_RU'  => 'Tatar (Russia)',
			'te_IN'  => 'Telugu (India)',
			'th_TH'  => 'Thai (Thailand)',
			'bo_CN'  => 'Tibetan (China)',
			'bo_IN'  => 'Tibetan (India)',
			'tig_ER' => 'Tigre (Eritrea)',
			'ti_ER'  => 'Tigrinya (Eritrea)',
			'ti_ET'  => 'Tigrinya (Ethiopia)',
			'ts_ZA'  => 'Tsonga (South Africa)',
			'tn_ZA'  => 'Tswana (South Africa)',
			'tr_CY'  => 'Turkish (Cyprus)',
			'tr_TR'  => 'Turkish (Turkey)',
			'tk_TM'  => 'Turkmen (Turkmenistan)',
			'ug_CN'  => 'Uighur (China)',
			'uk_UA'  => 'Ukrainian (Ukraine)',
			'hsb_DE' => 'Upper Sorbian (Germany)',
			'ur_PK'  => 'Urdu (Pakistan)',
			'uz_UZ'  => 'Uzbek (Uzbekistan)',
			've_ZA'  => 'Venda (South Africa)',
			'vi_VN'  => 'Vietnamese (Vietnam)',
			'wa_BE'  => 'Walloon (Belgium)',
			'cy_GB'  => 'Welsh (United Kingdom)',
			'fy_DE'  => 'Western Frisian (Germany)',
			'fy_NL'  => 'Western Frisian (Netherlands)',
			'wo_SN'  => 'Wolof (Senegal)',
			'xh_ZA'  => 'Xhosa (South Africa)',
			'yi_US'  => 'Yiddish (United States)',
			'yo_NG'  => 'Yoruba (Nigeria)',
			'zu_ZA'  => 'Zulu (South Africa)'
		);

		return array_key_exists( $value, $languages ) ? $languages[ $value ] : '';
	}

	/**
	 * @param $parent_atts
	 * @return array
	 */
	private function get_product_model( $parent_atts )
	{
		$json_array = array(
			'@context' => array(
				'bing' => 'https://www.bing.com/images/api/imagefeed/v1.0/'
			),
			'@type'    => 'https://scheme.org/ImageObject'
		);

		if ( $parent_atts ) {
			foreach ( $parent_atts as $key => $value ) {
				if ( $key == 'hostPageUrl' ) {
					$json_array[ 'hostPageUrl' ] = $value;
				}
				elseif ( $key == 'contentUrl' ) {
					$json_array[ 'contentUrl' ] = $value;
				}
				elseif ( $key == 'image_height' ) {
					$json_array[ 'height' ] = $value;
				}
				elseif ( $key == 'image_width' ) {
					$json_array[ 'width' ] = $value;
				}
				elseif ( $key == 'datePublished' ) {
					$json_array[ 'datePublished' ] = $value;
				}
				elseif ( $key == 'image_name' ) {
					$json_array[ 'name' ] = $value;
				}
				elseif ( $key == 'image_author' ) {
					$json_array[ 'author' ][ 'alternateName' ] = $value;
					$json_array[ 'author' ][ 'url' ] = get_site_url() . 'author/' . $value . '/';
				}
				elseif ( $key == 'image_format' ) {
					$json_array[ 'encodingFormat' ] = $value;
				}
				elseif ( $key == 'image_size_bytes' ) {
					$json_array[ 'contentSize' ] = (string)$value;
				}
				elseif ( $key == 'image_thumbnail' ) {
					$json_array[ 'thumbnail' ][ 'url' ] = $value;
				}
				elseif ( $key == 'family_friendly' ) {
					$json_array[ 'bing:isFamilyFriendly' ] = $value;
				}
				elseif ( $key == 'keywords' ) {
					$json_array[ 'keywords' ] = $value;
				}
				elseif ( $key == 'category' ) {
					$json_array[ 'bing:category' ] = $value;
				}
				elseif ( $key == 'modified_date' ) {
					$json_array[ 'dateModified' ] = $value;
				}
			}
		}
		return $json_array;
	}

	/**
	 * save feed
	 *
	 * @param $format
	 * @return bool|string
	 */
	public function save_json_feed( $format )
	{
		$path    = wp_upload_dir();
		$baseurl = $path[ 'baseurl' ];
		$path    = $path[ 'basedir' ] . '/rex-feed';
		$file    = trailingslashit( $path ) . "feed-{$this->id}.json";

		update_post_meta( $this->id, '_rex_feed_xml_file', $baseurl . '/rex-feed' . "/feed-{$this->id}.json" );
		update_post_meta( $this->id, '_rex_feed_merchant', $this->merchant );

		if ( $this->batch == 1 ) {
			return file_put_contents( $file, json_encode( $this->feed ) ) ? 'true' : 'false';
		}
		else {
			$languageCulture = 'de-CH';
			$request         = wp_remote_get( "http://www.google.com/basepages/producttype/taxonomy." . $languageCulture . ".txt" );
			if ( is_wp_error( $request ) ) {
				return false;
			}
			$inp = wp_remote_retrieve_body( $request );

			$tempArray = explode( '>', $inp );
			$result    = array_merge( $tempArray, $this->feed );
			$jsonData  = json_encode( $result );
			return file_put_contents( $file, $jsonData ) ? 'true' : 'false';
		}
	}

	public function footer_replace() { }

	/**
	 * @param $atts
	 * @return array
	 */
	private function get_product_simples( $atts )
	{
		$json_array = array();
		foreach ( $atts as $key => $value ) {
			if ( $key == 'merchant_product_simple_id' ) {
				$json_array[ 'merchant_product_simple_id' ] = $value;
			}
			if ( $key == 'ean' ) {
				$json_array[ 'ean' ] = $value;
			}
		}
		return $json_array;
	}

}