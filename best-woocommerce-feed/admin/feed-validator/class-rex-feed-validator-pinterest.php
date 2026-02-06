<?php
/**
 * Pinterest Catalog Feed Validator
 *
 * Implements Pinterest Catalog Feed specific validation rules.
 *
 * @link       https://rextheme.com
 * @since      7.4.58
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 * @author     RexTheme <info@rextheme.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pinterest Catalog Feed Validator.
 *
 * This class implements all Pinterest Catalog Feed specific validation rules based on:
 * Pinterest Catalog Feed Specification (https://help.pinterest.com/en-gb/business/article/before-you-get-started-with-catalogs)
 * Last updated: January 2026
 *
 * Validation includes:
 * - Required attributes (id, title, description, link, image_link, price, availability)
 * - Recommended attributes (brand, product_type, google_product_category, sale_price, etc.)
 * - Conditional rules (item_group_id for variants, apparel attributes, sale price dates)
 * - Format validation (URLs, prices, dates, enums, character limits)
 * - Variant product validation
 * - Product identifier requirements
 * - Apparel-specific attributes
 *
 * Reference: https://help.pinterest.com/en-gb/business/article/before-you-get-started-with-catalogs
 *
 * @since      7.4.58
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validator_Pinterest extends Rex_Feed_Abstract_Validator {

	/**
	 * Constructor.
	 *
	 * @since 7.4.58
	 * @param int $feed_id The feed ID to validate.
	 */
	public function __construct( $feed_id = 0 ) {
		$this->merchant = 'pinterest';
		parent::__construct( $feed_id );
	}

	/**
	 * Initialize Pinterest Catalog validation rules.
	 *
	 * @since 7.4.58
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
	 * Initialize required attributes for Pinterest Catalog Feed.
	 * Based on Pinterest Catalog Feed Specification:
	 * https://help.pinterest.com/en-gb/business/article/before-you-get-started-with-catalogs
	 *
	 * @since 7.4.58
	 * @access protected
	 * @return void
	 */
	protected function init_required_attributes() {
		$this->required_attributes = array(
			// Basic product data - REQUIRED
			'id'          => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Unique product identifier - must be unique across the feed', 'rex-product-feed' ),
			),
			'title'       => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product title (max 500 characters recommended)', 'rex-product-feed' ),
			),
			'description' => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product description (max 10000 characters)', 'rex-product-feed' ),
			),
			'link'        => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product landing page URL (must be valid URL)', 'rex-product-feed' ),
			),
			'image_link'  => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Main product image URL (must be valid URL)', 'rex-product-feed' ),
			),
			// Price and availability - REQUIRED
			'price'       => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product price with currency code (e.g., 15.00 USD)', 'rex-product-feed' ),
			),
			'availability' => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product availability (in stock, out of stock, preorder)', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Initialize character limits for Pinterest Catalog Feed.
	 *
	 * @since 7.4.58
	 * @access protected
	 * @return void
	 */
	protected function init_character_limits() {
		$this->character_limits = array(
			'title'       => array(
				'max'      => 500,
				'severity' => self::SEVERITY_WARNING,
			),
			'description' => array(
				'max'      => 10000,
				'severity' => self::SEVERITY_WARNING,
			),
			'brand'       => array(
				'max'      => 255,
				'severity' => self::SEVERITY_WARNING,
			),
		);
	}

	/**
	 * Initialize accepted enum values for Pinterest Catalog Feed.
	 *
	 * @since 7.4.58
	 * @access protected
	 * @return void
	 */
	protected function init_enum_values() {
		$this->enum_values = array(
			'availability' => array(
				'values'   => array( 'in stock', 'out of stock', 'preorder' ),
				'severity' => self::SEVERITY_ERROR,
			),
			'condition'    => array(
				'values'   => array( 'new', 'refurbished', 'used' ),
				'severity' => self::SEVERITY_WARNING,
			),
			'gender'       => array(
				'values'   => array( 'male', 'female', 'unisex' ),
				'severity' => self::SEVERITY_WARNING,
			),
			'age_group'    => array(
				'values'   => array( 'newborn', 'infant', 'toddler', 'kids', 'adult' ),
				'severity' => self::SEVERITY_WARNING,
			),
		);
	}

	/**
	 * Initialize format rules for Pinterest Catalog Feed.
	 * Based on Pinterest Catalog Feed Specification:
	 * https://help.pinterest.com/en-gb/business/article/before-you-get-started-with-catalogs
	 *
	 * Key Pinterest Requirements:
	 * - URLs: Must be valid and accessible
	 * - Prices: Format with currency code (e.g., 15.00 USD)
	 * - Images: Valid image URLs
	 * - Dates: ISO 8601 format for sale_price_effective_date
	 *
	 * @since 7.4.58
	 * @access protected
	 * @return void
	 */
	protected function init_format_rules() {
		$this->format_rules = array(
			// URL formats
			'link'                  => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Must be a valid URL', 'rex-product-feed' ),
			),
			'image_link'            => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Must be a valid image URL', 'rex-product-feed' ),
			),
			'additional_image_link' => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be a valid image URL', 'rex-product-feed' ),
			),
			// Price format
			'price'                 => array(
				'pattern'     => '/^\d+(\.\d{1,2})?\s+[A-Z]{3}$/',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Format: 15.00 USD (price with currency code)', 'rex-product-feed' ),
			),
			'sale_price'            => array(
				'pattern'     => '/^\d+(\.\d{1,2})?\s+[A-Z]{3}$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Format: 12.00 USD (price with currency code)', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Run Pinterest-specific custom validations.
	 * Based on Pinterest Catalog Feed Specification:
	 * https://help.pinterest.com/en-gb/business/article/before-you-get-started-with-catalogs
	 *
	 * @since 7.4.58
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function run_custom_validations( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Validate price requirements
		$errors = array_merge( $errors, $this->validate_price_requirements( $product_id, $product_title, $product_data ) );

		// Validate variant requirements (item_group_id)
		$errors = array_merge( $errors, $this->validate_variant_requirements( $product_id, $product_title, $product_data ) );

		// Validate apparel-specific attributes
		$errors = array_merge( $errors, $this->validate_apparel_requirements( $product_id, $product_title, $product_data ) );

		// Validate product identifiers
		$errors = array_merge( $errors, $this->validate_pinterest_product_identifiers( $product_id, $product_title, $product_data ) );

		// Generate optimization suggestions
		$errors = array_merge( $errors, $this->generate_optimization_suggestions( $product_id, $product_title, $product_data ) );

		return $errors;
	}

	/**
	 * Validate price requirements.
	 * Extends parent implementation with Pinterest-specific sale price date check.
	 *
	 * @since 7.4.58
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_price_requirements( $product_id, $product_title, $product_data ) {
		// Get base validation errors from parent
		$errors = parent::validate_price_requirements( $product_id, $product_title, $product_data );

		$sale_price = $product_data['sale_price'] ?? '';

		// If sale price exists, recommend sale_price_effective_date (Pinterest-specific)
		if ( ! $this->is_empty_value( $sale_price ) && $this->extract_price_numeric( $sale_price ) > 0 ) {
			if ( $this->is_empty_value( $product_data['sale_price_effective_date'] ?? null ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'sale_price_effective_date',
					'conditional_sale_price_date',
					self::SEVERITY_WARNING,
					'',
					__( 'When sale_price is provided, sale_price_effective_date is recommended to specify when the sale is valid', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate variant requirements.
	 *
	 * @since 7.4.58
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_variant_requirements( $product_id, $product_title, $product_data ) {
		$errors = array();

		$has_color      = ! $this->is_empty_value( $product_data['color'] ?? null );
		$has_size       = ! $this->is_empty_value( $product_data['size'] ?? null );
		$has_pattern    = ! $this->is_empty_value( $product_data['pattern'] ?? null );
		$has_material   = ! $this->is_empty_value( $product_data['material'] ?? null );
		$item_group_id  = $product_data['item_group_id'] ?? null;
		$has_group_id   = ! $this->is_empty_value( $item_group_id );

		// If product has variant attributes, it should have item_group_id
		if ( ( $has_color || $has_size || $has_pattern || $has_material ) && ! $has_group_id ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'item_group_id',
				'conditional_variant_group_id',
				self::SEVERITY_WARNING,
				'',
				__( 'Products with variant attributes (color, size, material, pattern) should include item_group_id to group variants together', 'rex-product-feed' )
			);
		}

		// If item_group_id exists but no variant attributes
		if ( $has_group_id && ! $has_color && ! $has_size && ! $has_pattern && ! $has_material ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'item_group_id',
				'variant_attribute_missing',
				self::SEVERITY_INFO,
				$item_group_id,
				__( 'Product has item_group_id but no variant attributes (color, size, material, pattern). Consider adding variant attributes.', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Validate apparel-specific requirements.
	 *
	 * @since 7.4.58
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_apparel_requirements( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Check if this is an apparel product
		if ( ! $this->is_apparel_product( $product_data ) ) {
			return $errors;
		}

		// For apparel products, recommend specific attributes
		$apparel_attributes = array(
			'color'     => __( 'Color attribute is recommended for apparel products for better categorization', 'rex-product-feed' ),
			'size'      => __( 'Size attribute is recommended for apparel products for better categorization', 'rex-product-feed' ),
			'gender'    => __( 'Gender attribute helps with targeted advertising for apparel', 'rex-product-feed' ),
			'age_group' => __( 'Age group helps with audience targeting for apparel', 'rex-product-feed' ),
		);

		foreach ( $apparel_attributes as $attr => $message ) {
			if ( $this->is_empty_value( $product_data[ $attr ] ?? null ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					$attr,
					'conditional_apparel_attribute',
					self::SEVERITY_WARNING,
					'',
					$message
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate product identifiers (brand, gtin, mpn) - Pinterest specific.
	 *
	 * @since 7.4.58
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_pinterest_product_identifiers( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Strip CDATA from identifiers before validation
		$brand_value = isset( $product_data['brand'] ) ? $this->strip_cdata( $product_data['brand'] ) : null;
		$gtin_value  = isset( $product_data['gtin'] ) ? $this->strip_cdata( $product_data['gtin'] ) : null;
		$mpn_value   = isset( $product_data['mpn'] ) ? $this->strip_cdata( $product_data['mpn'] ) : null;

		$has_brand = ! $this->is_empty_value( $brand_value );
		$has_gtin  = ! $this->is_empty_value( $gtin_value );
		$has_mpn   = ! $this->is_empty_value( $mpn_value );

		// Recommend at least one product identifier for better product matching
		if ( ! $has_brand && ! $has_gtin && ! $has_mpn ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'brand, gtin, mpn',
				'product_identifier_missing',
				self::SEVERITY_INFO,
				'',
				__( 'At least one product identifier (brand, GTIN, or MPN) is recommended for better product matching on Pinterest', 'rex-product-feed' )
			);
		}

		// Validate GTIN format if provided
		if ( $has_gtin ) {
			$gtin        = preg_replace( '/[^0-9]/', '', $gtin_value );
			$gtin_length = strlen( $gtin );

			if ( ! in_array( $gtin_length, array( 8, 12, 13, 14 ), true ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'gtin',
					'gtin_format_invalid',
					self::SEVERITY_WARNING,
					$gtin_value,
					__( 'GTIN must be 8, 12, 13, or 14 digits (UPC, EAN, JAN, ISBN, ITF-14)', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Generate optimization suggestions for better Pinterest catalog performance.
	 * Limited to max 3 suggestions per product to avoid information overload.
	 *
	 * @since 7.4.58
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function generate_optimization_suggestions( $product_id, $product_title, $product_data ) {
		$suggestions     = array();
		$max_suggestions = 3;

		// Priority 1: Suggest adding google_product_category for better visibility
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['google_product_category'] ?? null ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'google_product_category',
				'optimization_suggestion',
				self::SEVERITY_INFO,
				'',
				__( 'Google product category improves product discovery on Pinterest', 'rex-product-feed' )
			);
		}

		// Priority 2: Suggest adding additional images
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['additional_image_link'] ?? null ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'additional_image_link',
				'optimization_suggestion',
				self::SEVERITY_INFO,
				'',
				__( 'Additional product images improve engagement and conversion rates on Pinterest', 'rex-product-feed' )
			);
		}

		// Priority 3: Suggest product_type for better organization
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['product_type'] ?? null ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'product_type',
				'optimization_suggestion',
				self::SEVERITY_INFO,
				'',
				__( 'Product type helps with categorization and improves discoverability', 'rex-product-feed' )
			);
		}

		return $suggestions;
	}
}
