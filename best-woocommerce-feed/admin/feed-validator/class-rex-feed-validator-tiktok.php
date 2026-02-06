<?php
/**
 * TikTok Catalog Feed Validator
 *
 * Implements TikTok Ads Catalog specific validation rules.
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
 * TikTok Catalog Feed Validator.
 *
 * This class implements all TikTok Catalog specific validation rules based on:
 * TikTok Catalog Product Parameters (https://ads.tiktok.com/help/article/catalog-product-parameters)
 * Last updated: January 2026
 *
 * Validation includes:
 * - Required attributes (id, title, description, link, image_link, price, availability, condition, brand, currency)
 * - Recommended attributes for better ad performance
 * - Character limits per TikTok specs
 * - URL format validation (http/https protocol)
 * - Image format and size validation
 * - Price format with currency validation
 * - Variant product validation (item_group_id consistency)
 * - Apparel-specific attributes (color, size, gender, age_group)
 * - Product identifier requirements (GTIN, MPN, Brand)
 * - Conditional validation logic
 * - Accepted enum values for all choice fields
 *
 * Reference: https://ads.tiktok.com/help/article/catalog-product-parameters
 *
 * @since      7.4.64
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validator_TikTok extends Rex_Feed_Abstract_Validator {

	/**
	 * Constructor.
	 *
	 * @since 7.4.64
	 * @param int $feed_id The feed ID to validate.
	 */
	public function __construct( $feed_id = 0 ) {
		$this->merchant = 'tiktok';
		parent::__construct( $feed_id );
	}

	/**
	 * Initialize TikTok Catalog validation rules.
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
	 * Initialize required attributes for TikTok Catalog.
	 * Based on TikTok Catalog Product Parameters:
	 * https://ads.tiktok.com/help/article/catalog-product-parameters
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_required_attributes() {
		$this->required_attributes = array(
			// Basic product data - REQUIRED
			'id'          => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Unique product identifier (max 100 characters)', 'rex-product-feed' ),
			),
			'title'       => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product title (max 150 characters)', 'rex-product-feed' ),
			),
			'description' => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product description (max 5000 characters)', 'rex-product-feed' ),
			),
			'link'        => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product landing page URL (must start with http:// or https://)', 'rex-product-feed' ),
			),
			'image_link'  => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Main product image URL (JPEG, PNG, WebP format, max 8MB)', 'rex-product-feed' ),
			),
			// Price and availability - REQUIRED
			'price'       => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product price with currency (e.g., 15.00 USD or USD 15.00)', 'rex-product-feed' ),
			),
			'availability' => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product availability: in stock, out of stock, preorder', 'rex-product-feed' ),
			),
			// Product attributes - REQUIRED
			'condition'   => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product condition: new, refurbished, used', 'rex-product-feed' ),
			),
			'brand'       => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product brand (max 70 characters)', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Initialize character limits for TikTok Catalog attributes.
	 * Based on TikTok Catalog Product Parameters specification.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_character_limits() {
		$this->character_limits = array(
			// Basic product data
			'id'                  => array(
				'max'      => 100,
				'severity' => self::SEVERITY_ERROR,
			),
			'title'               => array(
				'min'      => 1,
				'max'      => 150,
				'severity' => self::SEVERITY_ERROR,
			),
			'description'         => array(
				'min'      => 1,
				'max'      => 5000,
				'severity' => self::SEVERITY_ERROR,
			),
			'link'                => array(
				'max'      => 2000,
				'severity' => self::SEVERITY_ERROR,
			),
			'image_link'          => array(
				'max'      => 2000,
				'severity' => self::SEVERITY_ERROR,
			),
			// Additional attributes
			'brand'               => array(
				'max'      => 70,
				'severity' => self::SEVERITY_ERROR,
			),
			'item_group_id'       => array(
				'max'      => 100,
				'severity' => self::SEVERITY_ERROR,
			),
			'color'               => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			'size'                => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			'material'            => array(
				'max'      => 200,
				'severity' => self::SEVERITY_INFO,
			),
			'pattern'             => array(
				'max'      => 100,
				'severity' => self::SEVERITY_INFO,
			),
			'product_type'        => array(
				'max'      => 750,
				'severity' => self::SEVERITY_INFO,
			),
			'google_product_category' => array(
				'max'      => 750,
				'severity' => self::SEVERITY_INFO,
			),
			'gtin'                => array(
				'max'      => 50,
				'severity' => self::SEVERITY_WARNING,
			),
			'mpn'                 => array(
				'max'      => 70,
				'severity' => self::SEVERITY_WARNING,
			),
		);
	}

	/**
	 * Initialize accepted enum values for TikTok Catalog.
	 *
	 * @since 7.4.64
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
				'severity' => self::SEVERITY_ERROR,
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
	 * Initialize format rules for TikTok Catalog.
	 * Based on TikTok Catalog Product Parameters:
	 * https://ads.tiktok.com/help/article/catalog-product-parameters
	 *
	 * Key TikTok Requirements:
	 * - URLs: Must start with http:// or https://
	 * - Images: JPEG (.jpg/.jpeg), PNG (.png), WebP (.webp), max 8MB
	 * - Prices: Numeric value with up to 2 decimal places
	 * - Currency: ISO 4217 codes (USD, EUR, GBP, etc.)
	 * - Dates: ISO 8601 format for sale_price_effective_date
	 * - GTINs: 8, 12, 13, or 14 digits (UPC, EAN, JAN, ISBN)
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_format_rules() {
		$this->format_rules = array(
			// URL formats
			'link'              => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Must be a valid URL starting with http:// or https://', 'rex-product-feed' ),
			),
			'image_link'        => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Must be a valid image URL starting with http:// or https://', 'rex-product-feed' ),
			),
			'additional_image_link' => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be a valid image URL starting with http:// or https://', 'rex-product-feed' ),
			),
			// Price format (with currency)
			'price'             => array(
				'pattern'     => '/^(\d+(\.\d{1,2})?\s+[A-Z]{3}|[A-Z]{3}\s+\d+(\.\d{1,2})?)$/',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Price with currency code (e.g., 49.99 USD or USD 49.99)', 'rex-product-feed' ),
			),
			'sale_price'        => array(
				'pattern'     => '/^(\d+(\.\d{1,2})?\s+[A-Z]{3}|[A-Z]{3}\s+\d+(\.\d{1,2})?)$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Price with currency code (e.g., 39.99 USD or USD 39.99)', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Run TikTok-specific custom validations.
	 * Based on TikTok Catalog Product Parameters:
	 * https://ads.tiktok.com/help/article/catalog-product-parameters
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

		// Validate product identifiers (GTIN/MPN/Brand)
		$errors = array_merge( $errors, $this->validate_identifier_requirements( $product_id, $product_title, $product_data ) );

		// Validate price requirements
		$errors = array_merge( $errors, $this->validate_price_requirements( $product_id, $product_title, $product_data ) );

		// Validate apparel attributes
		$errors = array_merge( $errors, $this->validate_apparel_requirements( $product_id, $product_title, $product_data ) );

		// Validate variant attributes
		$errors = array_merge( $errors, $this->validate_variant_requirements( $product_id, $product_title, $product_data ) );

		// Add optimization suggestions
		$errors = array_merge( $errors, $this->generate_optimization_suggestions( $product_id, $product_title, $product_data ) );

		return $errors;
	}

	/**
	 * Validate product identifier requirements (GTIN, MPN, Brand).
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_identifier_requirements( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Strip CDATA from identifiers before validation
		$gtin_value  = isset( $product_data['gtin'] ) ? $this->strip_cdata( $product_data['gtin'] ) : null;
		$mpn_value   = isset( $product_data['mpn'] ) ? $this->strip_cdata( $product_data['mpn'] ) : null;
		$brand_value = isset( $product_data['brand'] ) ? $this->strip_cdata( $product_data['brand'] ) : null;

		$has_gtin  = ! $this->is_empty_value( $gtin_value );
		$has_mpn   = ! $this->is_empty_value( $mpn_value );
		$has_brand = ! $this->is_empty_value( $brand_value );

		// Recommend at least one product identifier for better product matching
		if ( ! $has_gtin && ! $has_mpn && ! $has_brand ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'gtin, mpn, brand',
				'product_identifier_missing',
				self::SEVERITY_WARNING,
				'',
				__( 'At least one product identifier (GTIN, MPN, or Brand) should be provided for better product matching on TikTok', 'rex-product-feed' )
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
	 * Validate price requirements.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	/**
	 * Validate price requirements.
	 * Extends parent implementation with TikTok-specific sale price date check.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	/**
	 * Validate price requirements.
	 * Extends parent implementation with TikTok-specific sale price date check.
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

		// If sale price exists, recommend sale_price_effective_date (TikTok-specific)
		if ( ! $this->is_empty_value( $sale_price ) && $this->extract_price_numeric( $sale_price ) > 0 ) {
			if ( $this->is_empty_value( $product_data['sale_price_effective_date'] ?? null ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'sale_price_effective_date',
					'conditional_sale_price_date',
					self::SEVERITY_WARNING,
					'',
					__( 'When sale_price is provided, sale_price_effective_date should be included to specify when the sale is valid', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate apparel-specific requirements.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_apparel_requirements( $product_id, $product_title, $product_data ) {
		// TikTok-specific apparel requirements
		$tiktok_apparel_attrs = array(
			'color'     => array(
				'severity' => self::SEVERITY_WARNING,
				'message'  => __( 'Color attribute is recommended for apparel products for better categorization', 'rex-product-feed' ),
			),
			'size'      => array(
				'severity' => self::SEVERITY_WARNING,
				'message'  => __( 'Size attribute is recommended for apparel products for better categorization', 'rex-product-feed' ),
			),
			'gender'    => array(
				'severity' => self::SEVERITY_WARNING,
				'message'  => __( 'Gender attribute helps with targeted advertising for apparel', 'rex-product-feed' ),
			),
			'age_group' => array(
				'severity' => self::SEVERITY_WARNING,
				'message'  => __( 'Age group helps with audience targeting for apparel', 'rex-product-feed' ),
			),
		);

		return $this->validate_common_apparel_requirements( $product_id, $product_title, $product_data, $tiktok_apparel_attrs );
	}

	/**
	 * Validate variant requirements.
	 *
	 * @since 7.4.64
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
	 * Generate optimization suggestions for better TikTok ad performance.
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
		$suggestions    = array();
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
				__( 'Google product category improves product discovery and ad targeting on TikTok', 'rex-product-feed' )
			);
		}

		// Priority 2: Suggest adding additional images
		// Check if additional_image_link has valid content (handle both string and array formats)
		$additional_images = $product_data['additional_image_link'] ?? null;
		$has_additional_images = false;
		
		if ( ! is_null( $additional_images ) ) {
			if ( is_array( $additional_images ) ) {
				// Check if array has any non-empty values
				foreach ( $additional_images as $img ) {
					$clean_img = $this->strip_cdata( $img );
					if ( ! $this->is_empty_value( $clean_img ) ) {
						$has_additional_images = true;
						break;
					}
				}
			} else {
				// Handle single value
				$clean_value = $this->strip_cdata( $additional_images );
				$has_additional_images = ! $this->is_empty_value( $clean_value );
			}
		}
		
		if ( count( $suggestions ) < $max_suggestions && ! $has_additional_images ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'additional_image_link',
				'optimization_suggestion',
				self::SEVERITY_INFO,
				'',
				__( 'Additional product images improve ad engagement and conversion rates', 'rex-product-feed' )
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
				__( 'Product type helps with categorization and ad campaign organization', 'rex-product-feed' )
			);
		}

		return $suggestions;
	}
}