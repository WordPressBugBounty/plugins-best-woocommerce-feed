<?php
/**
 * WhatsApp Business Catalog Feed Validator
 *
 * Implements WhatsApp Business Catalog specific validation rules.
 * WhatsApp Business Catalogs are powered by Meta Commerce Manager,
 * adhering to Meta's product data specifications with additional WhatsApp recommendations.
 *
 * @link       https://rextheme.com
 * @since      7.4.65
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 * @author     RexTheme <info@rextheme.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rex_Feed_Validator_Facebook' ) ) {
	require_once dirname( __FILE__ ) . '/class-rex-feed-validator-facebook.php';
}

/**
 * WhatsApp Business Catalog Feed Validator.
 *
 * Validates product catalog feeds for WhatsApp Business according to Meta Commerce Manager
 * guidelines and WhatsApp mobile display best practices.
 *
 * @since      7.4.65
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validator_WhatsApp extends Rex_Feed_Validator_Facebook {

	/**
	 * Cached store currency.
	 *
	 * @since 7.4.65
	 * @var string
	 */
	protected $currency = '';

	/**
	 * Constructor.
	 *
	 * @since 7.4.65
	 * @param int $feed_id The feed ID to validate.
	 */
	public function __construct( $feed_id = 0 ) {
		$this->merchant = 'whatsapp';
		$this->currency = (string) get_option( 'woocommerce_currency' );
		parent::__construct( $feed_id );
	}

	/**
	 * Run custom validations for WhatsApp.
	 *
	 * @since 7.4.65
	 * @access protected
	 * @param int    $product_id    The product ID.
	 * @param string $product_title The product title.
	 * @param array  $product_data  The product data.
	 * @return array
	 */
	protected function run_custom_validations( $product_id, $product_title, $product_data ) {
		$errors = parent::run_custom_validations( $product_id, $product_title, $product_data );

		// WhatsApp Mobile Display recommendation: warn if title > 150 chars (prevents chat card truncation)
		$title_len = isset( $product_data['title'] ) && is_string( $product_data['title'] ) ? mb_strlen( $product_data['title'] ) : 0;
		if ( $title_len > 150 && $title_len <= 200 ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'title',
				'whatsapp_title_length_recommendation',
				self::SEVERITY_WARNING,
				$product_data['title'],
				sprintf(
					/* translators: %d: current title length */
					__( 'Title length is %d characters. While Meta allows up to 200 characters, WhatsApp chat cards truncate titles over 150 characters on mobile screens.', 'rex-product-feed' ),
					$title_len
				)
			);
		}

		// Check regional compliance requirements (e.g. India Legal Metrology)
		$errors = array_merge( $errors, $this->validate_whatsapp_compliance( $product_id, $product_title, $product_data ) );

		return $errors;
	}

	/**
	 * Validate WhatsApp compliance rules.
	 *
	 * @since 7.4.65
	 * @access protected
	 * @param int    $product_id    The product ID.
	 * @param string $product_title The product title.
	 * @param array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_whatsapp_compliance( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Check if product is explicitly marked as exempt
		$compliance_category = $product_data['wa_compliance_category'] ?? '';
		if ( 'COUNTRY_ORIGIN_EXEMPT' === $compliance_category ) {
			return $errors;
		}

		// For businesses in India (INR currency), Meta requires origin_country to comply with Legal Metrology laws.
		if ( 'INR' === $this->currency && empty( $product_data['origin_country'] ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'origin_country',
				'compliance',
				self::SEVERITY_WARNING,
				'',
				__( 'For businesses operating in India, Meta and WhatsApp require the Country of Origin [origin_country] attribute to comply with Indian Legal Metrology and E-Commerce rules.', 'rex-product-feed' )
			);
		}

		return $errors;
	}
}
