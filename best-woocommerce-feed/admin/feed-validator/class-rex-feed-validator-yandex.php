<?php
/**
 * Yandex YML Feed Validator
 *
 * Implements Yandex YML Feed specific validation rules.
 *
 * @since      7.4.64
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yandex YML Feed Validator.
 *
 * This class implements all Yandex YML Feed specific validation rules based on:
 * Yandex YML Feed Specification (https://yandex.ru/support/direct/en/feeds/requirements-yml)
 * Last updated: January 2026
 *
 * Validation includes:
 * - Required feed structure (yml_catalog, shop, currencies, categories, offers)
 * - Required offer attributes (id, name, description, url, price, currencyId, categoryId, picture)
 * - Recommended attributes (vendor, vendorCode, oldprice, barcode, gtin)
 * - Conditional rules (oldprice > price, availability logic, offer type specific)
 * - Format validation (prices, currencies, URLs, text length limits)
 * - XML schema compliance
 * - Unique ID validation
 * - Category and currency reference validation
 *
 * Reference: https://yandex.ru/support/direct/en/feeds/requirements-yml
 *
 * @since      7.4.64
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validator_Yandex extends Rex_Feed_Abstract_Validator {

	/**
	 * Constructor.
	 *
	 * @since 7.4.64
	 * @param int $feed_id The feed ID to validate.
	 */
	public function __construct( $feed_id = 0 ) {
		$this->merchant = 'yandex';
		parent::__construct( $feed_id );
	}

	/**
	 * Initialize Yandex YML validation rules.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_rules() {
		$this->init_required_attributes();
		$this->init_character_limits();
		$this->init_enum_values();
		$this->init_format_rules();
	}

	/**
	 * Initialize required attributes for Yandex YML Feed.
	 * Based on Yandex YML Feed Specification:
	 * https://yandex.ru/support/direct/en/feeds/requirements-yml
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_required_attributes() {
		$this->required_attributes = array(
			// Basic offer data - REQUIRED
			'id'          => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Unique offer identifier - must be unique across the feed', 'rex-product-feed' ),
			),
			'name'        => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Offer name (max 255 characters)', 'rex-product-feed' ),
			),
			'url'         => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Offer URL (must be valid URL, HTTPS recommended)', 'rex-product-feed' ),
			),
			'price'       => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Offer price (numeric value, must be positive)', 'rex-product-feed' ),
			),
			'currencyid'  => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Currency code (RUB, UAH, BYN, KZT, USD, EUR)', 'rex-product-feed' ),
			),
			'available'   => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Availability status (true/false)', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Initialize character limits for Yandex YML Feed.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_character_limits() {
		$this->character_limits = array(
			'name'        => array(
				'max'      => 255,
				'severity' => self::SEVERITY_ERROR,
			),
			'description' => array(
				'max'      => 3000,
				'severity' => self::SEVERITY_WARNING,
			),
			'vendor'      => array(
				'max'      => 255,
				'severity' => self::SEVERITY_WARNING,
			),
			'vendorcode'  => array(
				'max'      => 255,
				'severity' => self::SEVERITY_WARNING,
			),
			'model'       => array(
				'max'      => 255,
				'severity' => self::SEVERITY_WARNING,
			),
			'sales_notes' => array(
				'max'      => 50,
				'severity' => self::SEVERITY_WARNING,
			),
		);
	}

	/**
	 * Initialize accepted enum values for Yandex YML Feed.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_enum_values() {
		$this->enum_values = array(
			'available'  => array(
				'values'   => array( 'true', 'false' ),
				'severity' => self::SEVERITY_ERROR,
			),
			'currencyid' => array(
				'values'   => array( 'RUB', 'UAH', 'BYN', 'KZT', 'USD', 'EUR', 'BDT' ),
				'severity' => self::SEVERITY_ERROR,
			),
			'delivery'   => array(
				'values'   => array( 'true', 'false' ),
				'severity' => self::SEVERITY_INFO,
			),
			'pickup'     => array(
				'values'   => array( 'true', 'false' ),
				'severity' => self::SEVERITY_INFO,
			),
			'store'      => array(
				'values'   => array( 'true', 'false' ),
				'severity' => self::SEVERITY_INFO,
			),
		);
	}

	/**
	 * Initialize format rules for Yandex YML Feed.
	 * Based on Yandex YML Feed Specification:
	 * https://yandex.ru/support/direct/en/feeds/requirements-yml
	 *
	 * Key Yandex Requirements:
	 * - URLs: Must be valid URLs, HTTPS recommended
	 * - Prices: Numeric positive values
	 * - Currency: Valid currency codes (RUB, UAH, BYN, KZT, USD, EUR)
	 * - Boolean values: true/false
	 * - ID: Must be unique across feed
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_format_rules() {
		$this->format_rules = array(
			// URL formats
			'url'     => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Must be a valid URL, HTTPS recommended', 'rex-product-feed' ),
			),
			'picture' => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Must be a valid image URL', 'rex-product-feed' ),
			),
			// Price format
			'price'   => array(
				'pattern'     => '/^\d+(\.\d{1,2})?$/',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Numeric value with up to 2 decimal places (e.g., 1500.00)', 'rex-product-feed' ),
			),
			'oldprice' => array(
				'pattern'     => '/^\d+(\.\d{1,2})?$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Numeric value with up to 2 decimal places (e.g., 2000.00)', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Run Yandex-specific custom validations.
	 * Based on Yandex YML Feed Specification:
	 * https://yandex.ru/support/direct/en/feeds/requirements-yml
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function run_custom_validations( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Validate category requirements
		$errors = array_merge( $errors, $this->validate_category_requirements( $product_id, $product_title, $product_data ) );

		// Validate price requirements (including oldprice > price)
		$errors = array_merge( $errors, $this->validate_price_requirements( $product_id, $product_title, $product_data ) );

		// Validate availability and delivery logic
		$errors = array_merge( $errors, $this->validate_availability_logic( $product_id, $product_title, $product_data ) );

		// Validate vendor and model requirements
		$errors = array_merge( $errors, $this->validate_vendor_model_requirements( $product_id, $product_title, $product_data ) );

		// Validate product identifiers (barcode, gtin, vendorCode)
		$errors = array_merge( $errors, $this->validate_yandex_product_identifiers( $product_id, $product_title, $product_data ) );

		// Generate optimization suggestions
		$errors = array_merge( $errors, $this->generate_optimization_suggestions( $product_id, $product_title, $product_data ) );

		return $errors;
	}

	/**
	 * Validate category requirements with alternative field name support.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_category_requirements( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Get product type to handle variations
		$product = wc_get_product( $product_id );
		$product_type = $product ? $product->get_type() : '';

		// Check for categoryId or alternative field names (lowercase and variations)
		$category_fields = array( 'categoryId', 'categoryid', 'category_id', 'category', 'product_category', 'google_product_category' );
		$has_category = false;

		foreach ( $category_fields as $field ) {
			if ( ! $this->is_empty_value( $product_data[ $field ] ?? null ) ) {
				$has_category = true;
				break;
			}
		}

		// For variations without category, this is normal as they inherit from parent - skip validation
		if ( ! $has_category && $product_type === 'variation' ) {
			return $errors;
		}

		if ( ! $has_category ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'categoryId',
				'category_missing',
				self::SEVERITY_ERROR,
				'',
				__( 'Category is required. Please assign a product category (categoryId field)', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Validate price requirements including oldprice discount logic.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_price_requirements( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Get product type to handle special cases
		$product = wc_get_product( $product_id );
		$product_type = $product ? $product->get_type() : '';

		// Skip price validation for variable and grouped products (they don't have direct prices)
		if ( in_array( $product_type, array( 'variable', 'grouped' ), true ) ) {
			return $errors;
		}

		$price    = $product_data['price'] ?? '';
		$oldprice = $product_data['oldprice'] ?? '';

		// Extract numeric values using helper method
		$price_num    = $this->extract_price_numeric( $price );
		$oldprice_num = $this->extract_price_numeric( $oldprice );

		// Check for zero or negative price
		if ( ! $this->is_empty_value( $price ) && $price_num <= 0 ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'price',
				'price_invalid',
				self::SEVERITY_ERROR,
				$price,
				__( 'Price must be greater than zero', 'rex-product-feed' )
			);
		}

		// Validate oldprice > price for discount logic
		if ( ! $this->is_empty_value( $oldprice ) && $oldprice_num > 0 ) {
			if ( $oldprice_num <= $price_num ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'oldprice',
					'oldprice_not_higher',
					self::SEVERITY_WARNING,
					"price={$price}, oldprice={$oldprice}",
					__( 'oldprice must be greater than price to show a valid discount', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate availability and delivery logic.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_availability_logic( $product_id, $product_title, $product_data ) {
		$errors = array();

		$available = strtolower( trim( $product_data['available'] ?? '' ) );
		$delivery  = strtolower( trim( $product_data['delivery'] ?? '' ) );
		$pickup    = strtolower( trim( $product_data['pickup'] ?? '' ) );
		$store     = strtolower( trim( $product_data['store'] ?? '' ) );
		
		// Special handling: detect if someone put text like "pickup" or "delivery" instead of true/false
		// This is a common mistake - they should use separate <pickup>true</pickup> instead
		if ( ! empty( $delivery ) && $delivery !== 'true' && $delivery !== 'false' ) {
			// Treat any non-empty, non-boolean value as 'true' for fulfillment checking
			$delivery_bool = 'true';
		} else {
			$delivery_bool = $delivery;
		}
		
		if ( ! empty( $pickup ) && $pickup !== 'true' && $pickup !== 'false' ) {
			$pickup_bool = 'true';
		} else {
			$pickup_bool = $pickup;
		}
		
		if ( ! empty( $store ) && $store !== 'true' && $store !== 'false' ) {
			$store_bool = 'true';
		} else {
			$store_bool = $store;
		}

		// If available=false, recommend setting delivery/pickup/store accordingly
		if ( $available === 'false' ) {
			if ( $delivery_bool === 'true' ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'delivery',
					'availability_delivery_mismatch',
					self::SEVERITY_WARNING,
					"available={$available}, delivery={$delivery}",
					__( 'When available=false, delivery should typically be false as well', 'rex-product-feed' )
				);
			}
		}

		// If available=true, at least one fulfillment option should be true
		if ( $available === 'true' ) {
			if ( $delivery_bool !== 'true' && $pickup_bool !== 'true' && $store_bool !== 'true' ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'delivery',
					'no_fulfillment_option',
					self::SEVERITY_INFO,
					'',
					__( 'For available products, consider enabling at least one fulfillment option (delivery, pickup, or store)', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate vendor and model requirements for vendor-model offer type.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_vendor_model_requirements( $product_id, $product_title, $product_data ) {
		$errors = array();

		$vendor = $product_data['vendor'] ?? '';
		$model  = $product_data['model'] ?? '';

		// If vendor or model is provided, recommend providing both
		if ( ! $this->is_empty_value( $vendor ) || ! $this->is_empty_value( $model ) ) {
			if ( $this->is_empty_value( $vendor ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'vendor',
					'vendor_model_incomplete',
					self::SEVERITY_WARNING,
					'',
					__( 'When using vendor-model offer type, both vendor and model should be provided', 'rex-product-feed' )
				);
			}

			if ( $this->is_empty_value( $model ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'model',
					'vendor_model_incomplete',
					self::SEVERITY_WARNING,
					'',
					__( 'When using vendor-model offer type, both vendor and model should be provided', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate product identifiers (barcode, gtin, vendorCode) - Yandex specific.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_yandex_product_identifiers( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Strip CDATA from identifiers before validation
		$barcode_value     = isset( $product_data['barcode'] ) ? $this->strip_cdata( $product_data['barcode'] ) : null;
		$gtin_value        = isset( $product_data['gtin'] ) ? $this->strip_cdata( $product_data['gtin'] ) : null;
		$vendorCode_value  = isset( $product_data['vendorcode'] ) ? $this->strip_cdata( $product_data['vendorcode'] ) : null;

		$has_barcode     = ! $this->is_empty_value( $barcode_value );
		$has_gtin        = ! $this->is_empty_value( $gtin_value );
		$has_vendorCode  = ! $this->is_empty_value( $vendorCode_value );

		// Recommend at least one product identifier
		if ( ! $has_barcode && ! $has_gtin && ! $has_vendorCode ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'barcode, gtin, vendorCode',
				'product_identifier_missing',
				self::SEVERITY_INFO,
				'',
				__( 'At least one product identifier (barcode, gtin, or vendorCode) is recommended for better product matching', 'rex-product-feed' )
			);
		}

		// Validate barcode format if provided (numeric, 8-14 digits)
		if ( $has_barcode ) {
			$barcode        = preg_replace( '/[^0-9]/', '', $barcode_value );
			$barcode_length = strlen( $barcode );

			if ( $barcode_length < 8 || $barcode_length > 14 ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'barcode',
					'barcode_format_invalid',
					self::SEVERITY_WARNING,
					$barcode_value,
					__( 'Barcode should be 8-14 digits (EAN-8, EAN-13, UPC, etc.)', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Generate optimization suggestions for better Yandex feed performance.
	 * Limited to max 3 suggestions per product to avoid information overload.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function generate_optimization_suggestions( $product_id, $product_title, $product_data ) {
		$suggestions     = array();
		$max_suggestions = 3;

		// Priority 1: Suggest adding vendor for better categorization
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['vendor'] ?? null ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'vendor',
				'optimization_suggestion',
				self::SEVERITY_INFO,
				'',
				__( 'Adding vendor (brand) improves product categorization and buyer trust', 'rex-product-feed' )
			);
		}

		// Priority 2: Suggest adding vendorCode (SKU)
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['vendorcode'] ?? null ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'vendorcode',
				'optimization_suggestion',
				self::SEVERITY_INFO,
				'',
				__( 'vendorCode (SKU/Article) helps with product identification and inventory management', 'rex-product-feed' )
			);
		}

		// Priority 3: Suggest adding delivery options
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['delivery'] ?? null ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'delivery',
				'optimization_suggestion',
				self::SEVERITY_INFO,
				'',
				__( 'Specifying delivery options improves product visibility and conversion rates', 'rex-product-feed' )
			);
		}

		return $suggestions;
	}
}