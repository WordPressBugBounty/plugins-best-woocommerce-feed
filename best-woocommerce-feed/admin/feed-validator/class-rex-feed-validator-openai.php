<?php
/**
 * OpenAI Commerce Feed Validator
 *
 * Implements OpenAI Commerce Feed specific validation rules.
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
 * OpenAI Commerce Feed Validator.
 *
 * This class implements all OpenAI Commerce Feed specific validation rules based on:
 * OpenAI Product Feed Specification (https://developers.openai.com/commerce/specs/feed/)
 * Last updated: January 2026
 *
 * Validation includes:
 * - Required attributes (id, title, description, link, image_link, price, availability, enable_search, enable_checkout)
 * - Recommended attributes (gtin, mpn, brand, additional_image_link, sale_price, etc.)
 * - Conditional rules (enable_checkout requires enable_search=true, sale_price requires sale_price_effective_date)
 * - Format validation (URLs, prices, dates, ISO codes)
 * - Character limits per OpenAI specs
 * - Policy compliance checks
 *
 * @since      7.4.64
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validator_OpenAI extends Rex_Feed_Abstract_Validator {

	/**
	 * Constructor.
	 *
	 * @since 7.4.64
	 * @param int $feed_id The feed ID to validate.
	 */
	public function __construct( $feed_id = 0 ) {
		$this->merchant = 'openai';
		parent::__construct( $feed_id );
	}

	/**
	 * Initialize OpenAI Commerce validation rules.
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
	 * Initialize required attributes for OpenAI Commerce Feed.
	 * Based on OpenAI Product Feed Specification:
	 * https://developers.openai.com/commerce/specs/feed/
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_required_attributes() {
		$this->required_attributes = array(
			// Basic product data - REQUIRED
			'id'                 => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Unique product identifier - must be unique across the feed', 'rex-product-feed' ),
			),
			'title'              => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product title (max 200 characters recommended)', 'rex-product-feed' ),
			),
			'description'        => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product description (max 5000 characters)', 'rex-product-feed' ),
			),
			'link'               => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product landing page URL (must be HTTPS preferred)', 'rex-product-feed' ),
			),
			'image_link'         => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Main product image URL (must be HTTPS preferred)', 'rex-product-feed' ),
			),
			// Price and availability - REQUIRED
			'price'              => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product price with ISO 4217 currency code (e.g., 15.00 USD)', 'rex-product-feed' ),
			),
			'availability'       => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product availability: in_stock, out_of_stock, preorder, backorder', 'rex-product-feed' ),
			),
			// OpenAI-specific merchant eligibility - REQUIRED
			'enable_search'      => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Enable product for search and discovery (true/false)', 'rex-product-feed' ),
			),
			'enable_checkout'    => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Enable direct checkout functionality (true/false, requires enable_search=true)', 'rex-product-feed' ),
			),
			// Product identifiers - RECOMMENDED (WARNING level)
			'brand'              => array(
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Product brand name (recommended for better discovery)', 'rex-product-feed' ),
			),
			'gtin'               => array(
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Global Trade Item Number - UPC, EAN, ISBN (recommended for product matching)', 'rex-product-feed' ),
			),
			'mpn'                => array(
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Manufacturer Part Number (recommended if GTIN not available)', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Initialize character limits for OpenAI Commerce Feed attributes.
	 * Based on OpenAI Product Feed Specification:
	 * https://developers.openai.com/commerce/specs/feed/
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_character_limits() {
		$this->character_limits = array(
			// Basic product data
			'id'                      => array(
				'max'      => 255,
				'severity' => self::SEVERITY_ERROR,
			),
			'title'                   => array(
				'min'      => 1,
				'max'      => 500,
				'severity' => self::SEVERITY_WARNING, // Warning at 500, recommended is 200
			),
			'description'             => array(
				'min'      => 1,
				'max'      => 5000,
				'severity' => self::SEVERITY_ERROR,
			),
			// URLs
			'link'                    => array(
				'max'      => 2048,
				'severity' => self::SEVERITY_ERROR,
			),
			'image_link'              => array(
				'max'      => 2048,
				'severity' => self::SEVERITY_ERROR,
			),
			'additional_image_link'   => array(
				'max'      => 2048,
				'severity' => self::SEVERITY_WARNING,
			),
			'merchant_url'            => array(
				'max'      => 2048,
				'severity' => self::SEVERITY_WARNING,
			),
			// Product identifiers
			'gtin'                    => array(
				'min'      => 8,
				'max'      => 14,
				'severity' => self::SEVERITY_WARNING,
			),
			'mpn'                     => array(
				'max'      => 70,
				'severity' => self::SEVERITY_WARNING,
			),
			'brand'                   => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			// Additional attributes
			'product_category'        => array(
				'max'      => 750,
				'severity' => self::SEVERITY_WARNING,
			),
			'color'                   => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			'size'                    => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			'material'                => array(
				'max'      => 200,
				'severity' => self::SEVERITY_WARNING,
			),
			'pattern'                 => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			// Inventory and expiration
			'inventory_quantity'      => array(
				'max'      => 10,
				'severity' => self::SEVERITY_WARNING,
			),
			// Policy URLs
			'return_policy_url'       => array(
				'max'      => 2048,
				'severity' => self::SEVERITY_WARNING,
			),
			'privacy_policy_url'      => array(
				'max'      => 2048,
				'severity' => self::SEVERITY_WARNING,
			),
			'terms_of_service_url'    => array(
				'max'      => 2048,
				'severity' => self::SEVERITY_WARNING,
			),
		);
	}

	/**
	 * Initialize accepted enum values for OpenAI Commerce Feed attributes.
	 * Based on OpenAI Product Feed Specification:
	 * https://developers.openai.com/commerce/specs/feed/
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_enum_values() {
		$this->enum_values = array(
			// Availability - REQUIRED enum
			'availability'       => array(
				'values'         => array(
					'in_stock',
					'in stock',
					'out_of_stock',
					'out of stock',
					'preorder',
					'backorder',
				),
				'case_sensitive' => false,
				'severity'       => self::SEVERITY_ERROR,
			),
			// Condition - RECOMMENDED
			'condition'          => array(
				'values'         => array(
					'new',
					'refurbished',
					'used',
				),
				'case_sensitive' => false,
				'severity'       => self::SEVERITY_WARNING,
			),
			// Boolean fields - OpenAI specific
			'enable_search'      => array(
				'values'         => array(
					'true',
					'false',
					'1',
					'0',
					'yes',
					'no',
				),
				'case_sensitive' => false,
				'severity'       => self::SEVERITY_ERROR,
			),
			'enable_checkout'    => array(
				'values'         => array(
					'true',
					'false',
					'1',
					'0',
					'yes',
					'no',
				),
				'case_sensitive' => false,
				'severity'       => self::SEVERITY_ERROR,
			),
			// Age group - for apparel
			'age_group'          => array(
				'values'         => array(
					'newborn',
					'infant',
					'toddler',
					'kids',
					'adult',
				),
				'case_sensitive' => false,
				'severity'       => self::SEVERITY_WARNING,
			),
			// Gender - for apparel
			'gender'             => array(
				'values'         => array(
					'male',
					'female',
					'unisex',
				),
				'case_sensitive' => false,
				'severity'       => self::SEVERITY_WARNING,
			),
			// Adult content flag
			'adult'              => array(
				'values'         => array(
					'true',
					'false',
					'yes',
					'no',
				),
				'case_sensitive' => false,
				'severity'       => self::SEVERITY_WARNING,
			),
		);
	}

	/**
	 * Initialize format rules for OpenAI Commerce Feed attributes.
	 * Based on OpenAI Product Feed Specification:
	 * https://developers.openai.com/commerce/specs/feed/
	 *
	 * Key OpenAI Requirements:
	 * - URLs: Must be valid, HTTPS strongly preferred
	 * - Prices: ISO 4217 currency codes (e.g., "15.00 USD")
	 * - Dates: ISO 8601 format (e.g., "2024-01-01T00:00:00Z")
	 * - Region codes: ISO 3166-1 alpha-2 country codes
	 * - Images: Standard formats (JPEG, PNG, WebP, GIF)
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_format_rules() {
		$this->format_rules = array(
			// URL formats - HTTPS preferred
			'link'                    => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Must be a valid URL starting with http:// or https:// (HTTPS preferred)', 'rex-product-feed' ),
			),
			'image_link'              => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Must be a valid image URL starting with http:// or https:// (HTTPS preferred)', 'rex-product-feed' ),
			),
			'additional_image_link'   => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be a valid image URL starting with http:// or https://', 'rex-product-feed' ),
			),
			'merchant_url'            => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be a valid URL to merchant homepage', 'rex-product-feed' ),
			),
			'return_policy_url'       => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be a valid URL to return policy page', 'rex-product-feed' ),
			),
			'privacy_policy_url'      => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be a valid URL to privacy policy page', 'rex-product-feed' ),
			),
			'terms_of_service_url'    => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be a valid URL to terms of service page', 'rex-product-feed' ),
			),
			// Price formats (ISO 4217)
			'price'                   => array(
				'pattern'     => '/^([A-Z]{3}\s*)?\d+(\.\d{1,2})?(\s*[A-Z]{3})?$/',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Format: number with ISO 4217 currency code (e.g., 15.00 USD or USD 15.00)', 'rex-product-feed' ),
			),
			'sale_price'              => array(
				'pattern'     => '/^([A-Z]{3}\s*)?\d+(\.\d{1,2})?(\s*[A-Z]{3})?$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Format: number with ISO 4217 currency code (e.g., 8.00 USD or USD 8.00)', 'rex-product-feed' ),
			),
			// Date formats (ISO 8601)
			'sale_price_effective_date' => array(
				'pattern'     => '/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:\d{2}|Z)?)?(\s*\/\s*\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:\d{2}|Z)?)?)?$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'ISO 8601 date or date range (e.g., 2024-01-01T00:00:00Z / 2024-12-31T23:59:59Z)', 'rex-product-feed' ),
			),
			'expiration_date'         => array(
				'pattern'     => '/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:\d{2}|Z)?)?$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'ISO 8601 date format (e.g., 2024-12-31T23:59:59Z)', 'rex-product-feed' ),
			),
			'availability_date'       => array(
				'pattern'     => '/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:\d{2}|Z)?)?$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'ISO 8601 date format for preorder/backorder availability', 'rex-product-feed' ),
			),
			// GTIN format (8, 12, 13, or 14 digits)
			'gtin'                    => array(
				'pattern'     => '/^\d{8}$|^\d{12}$|^\d{13}$|^\d{14}$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be 8, 12, 13, or 14 digits (UPC, EAN, ISBN, ITF-14)', 'rex-product-feed' ),
			),
			// Inventory quantity (numeric)
			'inventory_quantity'      => array(
				'pattern'     => '/^\d+$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be a non-negative integer', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Run OpenAI-specific custom validations.
	 * Implements business rules and conditional validations specific to OpenAI Commerce Feed.
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

		// 1. Validate enable_search and enable_checkout dependency
		$errors = array_merge( $errors, $this->validate_eligibility_flags( $product_id, $product_title, $product_data ) );

		// 2. Validate unique product ID
		$errors = array_merge( $errors, $this->validate_unique_product_id( $product_id, $product_title, $product_data ) );

		// 3. Validate sale_price requires sale_price_effective_date
		$errors = array_merge( $errors, $this->validate_sale_price_requirements( $product_id, $product_title, $product_data ) );

		// 4. Validate preorder/backorder availability date
		$errors = array_merge( $errors, $this->validate_availability_date( $product_id, $product_title, $product_data ) );

		// 5. Validate HTTPS preference for URLs
		$errors = array_merge( $errors, $this->validate_https_preference( $product_id, $product_title, $product_data ) );

		// 6. Validate geo pricing consistency
		$errors = array_merge( $errors, $this->validate_geo_pricing( $product_id, $product_title, $product_data ) );

		// 7. Validate product identifiers (GTIN, MPN, Brand)
		$errors = array_merge( $errors, $this->validate_openai_product_identifiers( $product_id, $product_title, $product_data ) );

		// 8. Validate future dates
		$errors = array_merge( $errors, $this->validate_future_dates( $product_id, $product_title, $product_data ) );

		// 9. Validate policy compliance
		$errors = array_merge( $errors, $this->validate_policy_compliance( $product_id, $product_title, $product_data ) );

		// 10. Generate optimization suggestions
		$errors = array_merge( $errors, $this->generate_optimization_suggestions( $product_id, $product_title, $product_data ) );

		return $errors;
	}

	/**
	 * Validate eligibility flags (enable_search and enable_checkout).
	 * Rule: enable_checkout requires enable_search to be true.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_eligibility_flags( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Get raw values and strip CDATA using helper method
		$enable_search_raw   = isset( $product_data['enable_search'] ) ? $product_data['enable_search'] : '';
		$enable_checkout_raw = isset( $product_data['enable_checkout'] ) ? $product_data['enable_checkout'] : '';

		if ( is_string( $enable_search_raw ) ) {
			$enable_search_raw = $this->strip_cdata( $enable_search_raw );
		}
		if ( is_string( $enable_checkout_raw ) ) {
			$enable_checkout_raw = $this->strip_cdata( $enable_checkout_raw );
		}

		// Skip validation if either value is empty (will be caught by required attribute check)
		if ( $this->is_empty_value( $enable_search_raw ) || $this->is_empty_value( $enable_checkout_raw ) ) {
			return $errors;
		}

		$enable_search   = $this->normalize_boolean( $enable_search_raw );
		$enable_checkout = $this->normalize_boolean( $enable_checkout_raw );

		// If enable_checkout is true, enable_search must also be true
		if ( $enable_checkout && ! $enable_search ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'enable_checkout',
				'checkout_requires_search',
				self::SEVERITY_ERROR,
				'enable_search=' . $enable_search_raw . ', enable_checkout=' . $enable_checkout_raw,
				__( 'enable_checkout requires enable_search to be true. A product must be searchable before it can support checkout.', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Validate unique product ID across the feed.
	 * Note: This performs a basic check. Full uniqueness is validated during feed generation.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_unique_product_id( $product_id, $product_title, $product_data ) {
		$errors = array();

		$product_feed_id = $product_data['id'] ?? '';

		if ( $this->is_empty_value( $product_feed_id ) ) {
			return $errors; // Already caught by required attribute check
		}

		// Check for potentially problematic characters in ID
		if ( preg_match( '/[^a-zA-Z0-9_\-]/', $product_feed_id ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'id',
				'invalid_id_characters',
				self::SEVERITY_WARNING,
				$product_feed_id,
				__( 'Product ID contains special characters. Use only alphanumeric characters, hyphens, and underscores for maximum compatibility.', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Validate sale_price requirements.
	 * Rule: If sale_price is provided, sale_price_effective_date should be provided.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_sale_price_requirements( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Get product type to handle special cases
		$product = wc_get_product( $product_id );
		$product_type = $product ? $product->get_type() : '';

		// Skip price validation for variable and grouped products (they don't have direct prices)
		if ( in_array( $product_type, array( 'variable', 'grouped' ), true ) ) {
			return $errors;
		}

		$sale_price               = $product_data['sale_price'] ?? '';
		$sale_price_effective_date = $product_data['sale_price_effective_date'] ?? '';

		// If sale_price exists, recommend sale_price_effective_date
		if ( ! $this->is_empty_value( $sale_price ) && $this->is_empty_value( $sale_price_effective_date ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'sale_price_effective_date',
				'sale_price_missing_date',
				self::SEVERITY_WARNING,
				$sale_price,
				__( 'sale_price_effective_date is recommended when sale_price is provided. Specify the date range for the sale.', 'rex-product-feed' )
			);
		}

		// Validate sale price is lower than regular price
		if ( ! $this->is_empty_value( $sale_price ) ) {
			$regular_price_value = $this->extract_price_numeric( $product_data['price'] ?? '' );
			$sale_price_value    = $this->extract_price_numeric( $sale_price );

			if ( $regular_price_value > 0 && $sale_price_value > 0 && $sale_price_value >= $regular_price_value ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'sale_price',
					'sale_price_not_lower',
					self::SEVERITY_WARNING,
					'price=' . $product_data['price'] . ', sale_price=' . $sale_price,
					__( 'sale_price should be lower than regular price to indicate a discount.', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate availability_date for preorder and backorder products.
	 * Rule: preorder and backorder availability should include availability_date.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_availability_date( $product_id, $product_title, $product_data ) {
		$errors = array();

		$availability      = strtolower( trim( $product_data['availability'] ?? '' ) );
		$availability_date = $product_data['availability_date'] ?? '';

		// For preorder/backorder, recommend availability_date
		if ( in_array( $availability, array( 'preorder', 'backorder' ), true ) && $this->is_empty_value( $availability_date ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'availability_date',
				'preorder_missing_date',
				self::SEVERITY_WARNING,
				$availability,
				__( 'availability_date is recommended for preorder and backorder products. Specify when the product will be available.', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Validate HTTPS preference for URLs.
	 * OpenAI prefers HTTPS for security and trust.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_https_preference( $product_id, $product_title, $product_data ) {
		$errors = array();

		$url_fields = array( 'link', 'image_link', 'merchant_url', 'return_policy_url', 'privacy_policy_url', 'terms_of_service_url' );

		foreach ( $url_fields as $field ) {
			$url = $product_data[ $field ] ?? '';

			if ( ! $this->is_empty_value( $url ) && preg_match( '/^http:\/\//i', $url ) ) {
				$severity = in_array( $field, array( 'link', 'image_link' ), true ) ? self::SEVERITY_WARNING : self::SEVERITY_INFO;

				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					$field,
					'http_instead_of_https',
					$severity,
					substr( $url, 0, 100 ),
					sprintf(
						/* translators: %s: field name */
						__( 'HTTPS is strongly recommended for %s. HTTP URLs may reduce trust and user security.', 'rex-product-feed' ),
						$field
					)
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate geo pricing consistency.
	 * If geo pricing is used, validate region codes and price consistency.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_geo_pricing( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Check for geo-specific pricing attributes
		$geo_price = $product_data['geo_price'] ?? '';
		$geo_region = $product_data['geo_region'] ?? '';

		if ( ! $this->is_empty_value( $geo_price ) && $this->is_empty_value( $geo_region ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'geo_region',
				'geo_price_missing_region',
				self::SEVERITY_WARNING,
				$geo_price,
				__( 'geo_region is required when geo_price is provided. Specify ISO 3166-1 alpha-2 country code (e.g., US, GB, DE).', 'rex-product-feed' )
			);
		}

		// Validate ISO 3166-1 alpha-2 format for geo_region
		if ( ! $this->is_empty_value( $geo_region ) && ! preg_match( '/^[A-Z]{2}$/', $geo_region ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'geo_region',
				'invalid_region_code',
				self::SEVERITY_WARNING,
				$geo_region,
				__( 'geo_region must be a valid ISO 3166-1 alpha-2 country code (2 uppercase letters, e.g., US, GB, DE).', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Validate product identifiers (GTIN, MPN, Brand).
	 * At least one of GTIN or MPN + Brand should be provided for better product matching.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_openai_product_identifiers( $product_id, $product_title, $product_data ) {
		$errors = array();

		$gtin  = $product_data['gtin'] ?? '';
		$mpn   = $product_data['mpn'] ?? '';
		$brand = $product_data['brand'] ?? '';

		// Strip CDATA if present using helper method
		$gtin  = $this->strip_cdata( $gtin );
		$mpn   = $this->strip_cdata( $mpn );
		$brand = $this->strip_cdata( $brand );

		// Check if we have at least GTIN or (MPN + Brand)
		$has_gtin       = ! $this->is_empty_value( $gtin );
		$has_mpn        = ! $this->is_empty_value( $mpn );
		$has_brand      = ! $this->is_empty_value( $brand );
		$has_mpn_brand  = $has_mpn && $has_brand;

		if ( ! $has_gtin && ! $has_mpn_brand ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'gtin',
				'missing_product_identifiers',
				self::SEVERITY_WARNING,
				null,
				__( 'Product should have either GTIN or both MPN and Brand for optimal product matching and discovery.', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Validate future dates for preorder, expiration, and sale dates.
	 * Ensure dates are logically valid (future dates for preorder, etc.).
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_future_dates( $product_id, $product_title, $product_data ) {
		$errors = array();

		$availability      = strtolower( trim( $product_data['availability'] ?? '' ) );
		$availability_date = $product_data['availability_date'] ?? '';
		$expiration_date   = $product_data['expiration_date'] ?? '';

		// For preorder, availability_date should be in the future
		if ( 'preorder' === $availability && ! $this->is_empty_value( $availability_date ) ) {
			$date_timestamp = strtotime( $availability_date );
			if ( $date_timestamp && $date_timestamp < time() ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'availability_date',
					'preorder_date_in_past',
					self::SEVERITY_WARNING,
					$availability_date,
					__( 'availability_date for preorder products should be in the future.', 'rex-product-feed' )
				);
			}
		}

		// Expiration date should be in the future
		if ( ! $this->is_empty_value( $expiration_date ) ) {
			$expiry_timestamp = strtotime( $expiration_date );
			if ( $expiry_timestamp && $expiry_timestamp < time() ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'expiration_date',
					'expiration_date_in_past',
					self::SEVERITY_WARNING,
					$expiration_date,
					__( 'expiration_date is in the past. Update or remove expired products from the feed.', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate policy compliance.
	 * Recommend including policy URLs for better merchant trust.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_policy_compliance( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Only check once per feed, not per product (optimization)
		static $policy_check_done = false;
		if ( $policy_check_done ) {
			return $errors;
		}
		$policy_check_done = true;

		$return_policy_url     = $product_data['return_policy_url'] ?? '';
		$privacy_policy_url    = $product_data['privacy_policy_url'] ?? '';
		$terms_of_service_url  = $product_data['terms_of_service_url'] ?? '';

		// All policy URLs are recommended for merchant trust
		if ( $this->is_empty_value( $return_policy_url ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'return_policy_url',
				'missing_return_policy',
				self::SEVERITY_INFO,
				null,
				__( 'return_policy_url is recommended to build customer trust and improve conversion rates.', 'rex-product-feed' )
			);
		}

		if ( $this->is_empty_value( $privacy_policy_url ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'privacy_policy_url',
				'missing_privacy_policy',
				self::SEVERITY_INFO,
				null,
				__( 'privacy_policy_url is recommended for compliance and customer trust.', 'rex-product-feed' )
			);
		}

		if ( $this->is_empty_value( $terms_of_service_url ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'terms_of_service_url',
				'missing_terms_of_service',
				self::SEVERITY_INFO,
				null,
				__( 'terms_of_service_url is recommended for legal clarity and customer protection.', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Generate optimization suggestions for better feed performance.
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

		// Priority 1: Suggest adding additional images
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['additional_image_link'] ?? null ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'additional_image_link',
				'no_additional_images',
				self::SEVERITY_INFO,
				null,
				__( 'Add additional product images to increase engagement. Multiple angles and lifestyle images improve click-through rates.', 'rex-product-feed' )
			);
		}

		// Priority 2: Suggest product_category for better organization
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['product_category'] ?? null ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'product_category',
				'missing_product_category',
				self::SEVERITY_INFO,
				null,
				__( 'Adding product_category helps with product discovery and improves search relevance.', 'rex-product-feed' )
			);
		}

		// Priority 3: Suggest inventory_quantity for stock management
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['inventory_quantity'] ?? null ) ) {
			$availability = strtolower( trim( $product_data['availability'] ?? '' ) );
			if ( 'in_stock' === $availability || 'in stock' === $availability ) {
				$suggestions[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'inventory_quantity',
					'missing_inventory_quantity',
					self::SEVERITY_INFO,
					null,
					__( 'Providing inventory_quantity helps OpenAI understand stock levels and improves availability accuracy.', 'rex-product-feed' )
				);
			}
		}

		// Priority 4: Suggest condition attribute
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['condition'] ?? null ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'condition',
				'missing_condition',
				self::SEVERITY_INFO,
				null,
				__( 'Specify product condition (new, refurbished, used) for better customer expectations and reduced returns.', 'rex-product-feed' )
			);
		}

		return $suggestions;
	}

}
