<?php
/**
 * Instagram Shopping Feed Validator
 *
 * Implements Instagram Shopping (Meta Commerce) specific validation rules.
 * Instagram Shopping uses the same catalog feed structure as Facebook,
 * therefore validation rules comply with Meta's official product feed requirements.
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
 * Instagram Shopping Feed Validator.
 *
 * This class implements all Instagram Shopping (Meta Commerce) specific validation rules including:
 * - Required attributes for Instagram Shopping
 * - Character limits
 * - Accepted enum values
 * - Format rules
 * - Instagram-specific business rules and policy compliance
 *
 * Reference: https://www.facebook.com/business/help/120325381656392?id=725943027795860
 *            https://help.instagram.com/1627591223954487
 *
 * @since      7.4.64
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validator_Instagram extends Rex_Feed_Abstract_Validator {

	/**
	 * Constructor.
	 *
	 * @since 7.4.64
	 * @param int $feed_id The feed ID to validate.
	 */
	public function __construct( $feed_id = 0 ) {
		$this->merchant = 'instagram';
		parent::__construct( $feed_id );
	}

	/**
	 * Initialize Instagram Shopping validation rules.
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
	 * Initialize required attributes for Instagram Shopping.
	 * Based on Meta (Facebook & Instagram) Product Feed Specification:
	 * https://www.facebook.com/business/help/120325381656392?id=725943027795860
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
				'description' => __( 'Unique product identifier (max 100 characters)', 'rex-product-feed' ),
			),
			'title'              => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product title (max 200 characters)', 'rex-product-feed' ),
			),
			'description'        => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product description (max 9999 characters)', 'rex-product-feed' ),
			),
			'availability'       => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product availability: in stock, out of stock, preorder, available for order, discontinued', 'rex-product-feed' ),
			),
			'condition'          => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product condition: new, refurbished, used', 'rex-product-feed' ),
			),
			'price'              => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product price with ISO 4217 currency code (e.g., 15.00 USD)', 'rex-product-feed' ),
			),
			'link'               => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product landing page URL', 'rex-product-feed' ),
			),
			'image_link'         => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Main product image URL', 'rex-product-feed' ),
			),
			'brand'              => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product brand name (required for Instagram Shopping)', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Initialize character limits for Instagram Shopping attributes.
	 * Based on Meta Product Feed Specification.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_character_limits() {
		$this->character_limits = array(
			// Product Information
			'id'                      => array(
				'max'      => 100,
				'severity' => self::SEVERITY_ERROR,
			),
			'title'                   => array(
				'max'      => 200,
				'severity' => self::SEVERITY_WARNING,
			),
			'description'             => array(
				'max'      => 9999,
				'severity' => self::SEVERITY_WARNING,
			),
			'link'                    => array(
				'max'      => 2048,
				'severity' => self::SEVERITY_WARNING,
			),
			'mobile_link'             => array(
				'max'      => 2048,
				'severity' => self::SEVERITY_WARNING,
			),
			'image_link'              => array(
				'max'      => 2000,
				'severity' => self::SEVERITY_WARNING,
			),
			'additional_image_link'   => array(
				'max'      => 2000,
				'severity' => self::SEVERITY_WARNING,
			),
			// Product identifiers
			'brand'                   => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			'gtin'                    => array(
				'max'      => 70,
				'severity' => self::SEVERITY_WARNING,
			),
			'mpn'                     => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			// Product categorization
			'google_product_category' => array(
				'max'      => 750,
				'severity' => self::SEVERITY_WARNING,
			),
			'product_type'            => array(
				'max'      => 750,
				'severity' => self::SEVERITY_WARNING,
			),
			// Variant attributes
			'item_group_id'           => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			'color'                   => array(
				'max'      => 200,
				'severity' => self::SEVERITY_WARNING,
			),
			'size'                    => array(
				'max'      => 200,
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
			// Custom labels
			'custom_label_0'          => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			'custom_label_1'          => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			'custom_label_2'          => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			'custom_label_3'          => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
			'custom_label_4'          => array(
				'max'      => 100,
				'severity' => self::SEVERITY_WARNING,
			),
		);
	}

	/**
	 * Initialize accepted enum values for Instagram Shopping attributes.
	 * Based on Meta Product Feed Specification.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_enum_values() {
		$this->enum_values = array(
			// Availability - REQUIRED
			'availability' => array(
				'values'         => array(
					'in stock',
					'out of stock',
					'preorder',
					'available for order',
					'discontinued',
				),
				'case_sensitive' => false,
				'severity'       => self::SEVERITY_ERROR,
			),
			// Condition - REQUIRED
			'condition'    => array(
				'values'         => array(
					'new',
					'refurbished',
					'used',
				),
				'case_sensitive' => false,
				'severity'       => self::SEVERITY_ERROR,
			),
			// Age group - RECOMMENDED for apparel
			'age_group'    => array(
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
			// Gender - RECOMMENDED for apparel
			'gender'       => array(
				'values'         => array(
					'male',
					'female',
					'unisex',
				),
				'case_sensitive' => false,
				'severity'       => self::SEVERITY_WARNING,
			),
		);
	}

	/**
	 * Initialize format rules for Instagram Shopping attributes.
	 * Based on Meta Product Feed Specification.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_format_rules() {
		$this->format_rules = array(
			// URL formats
			'link'                  => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Must be a valid URL starting with http:// or https://', 'rex-product-feed' ),
			),
			'mobile_link'           => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be a valid URL starting with http:// or https://', 'rex-product-feed' ),
			),
			'image_link'            => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Must be a valid image URL (JPG, PNG, GIF, BMP, TIFF)', 'rex-product-feed' ),
			),
			'additional_image_link' => array(
				'type'        => 'url',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be a valid image URL (JPG, PNG, GIF, BMP, TIFF)', 'rex-product-feed' ),
			),
			// Price format (numeric with currency)
			'price'                 => array(
				'pattern'     => '/^([A-Z]{3}\s*)?\d+(\.\d{1,2})?(\s*[A-Z]{3})?$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Format: number with ISO 4217 currency code (e.g., 15.00 USD or USD 15.00)', 'rex-product-feed' ),
			),
			'sale_price'            => array(
				'pattern'     => '/^([A-Z]{3}\s*)?\d+(\.\d{1,2})?(\s*[A-Z]{3})?$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Format: number with ISO 4217 currency code (e.g., 8.00 USD or USD 8.00)', 'rex-product-feed' ),
			),
			// GTIN format
			'gtin'                  => array(
				'pattern'     => '/^[\d\s-]{8,50}$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Must be 8, 12, 13, or 14 digits (UPC, EAN, JAN, ISBN)', 'rex-product-feed' ),
			),
			// Date formats (ISO 8601)
			'availability_date'     => array(
				'pattern'     => '/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:?\d{2}|Z)?)?$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Format: ISO 8601 date (e.g., 2026-01-01T00:00+00:00)', 'rex-product-feed' ),
			),
			'expiration_date'       => array(
				'pattern'     => '/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:?\d{2}|Z)?)?$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Format: ISO 8601 date', 'rex-product-feed' ),
			),
			'sale_price_effective_date' => array(
				'pattern'     => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:?\d{2}|Z)?\/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:?\d{2}|Z)?$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Format: ISO 8601 date range with "/" separator', 'rex-product-feed' ),
			),
			// Shipping weight
			'shipping_weight'       => array(
				'pattern'     => '/^\d+(\.\d+)?\s*(lb|oz|g|kg)$/i',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Format: number followed by unit (lb, oz, g, kg)', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Run Instagram-specific custom validations.
	 * Based on Meta Product Feed Specification and Instagram Shopping policies.
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

		// Validate identifier requirements (Brand/GTIN/MPN) - stricter for Instagram
		$errors = array_merge( $errors, $this->validate_instagram_identifier_requirements( $product_id, $product_title, $product_data ) );

		// Validate category requirements
		$errors = array_merge( $errors, $this->validate_category_requirements( $product_id, $product_title, $product_data ) );

		// Validate image requirements (critical for Instagram Shopping)
		$errors = array_merge( $errors, $this->validate_image_requirements( $product_id, $product_title, $product_data ) );

		// Validate price requirements
		$errors = array_merge( $errors, $this->validate_price_requirements( $product_id, $product_title, $product_data ) );

		// Validate variant requirements
		$errors = array_merge( $errors, $this->validate_variant_requirements( $product_id, $product_title, $product_data ) );

		// Validate apparel attributes (important for Instagram fashion products)
		$errors = array_merge( $errors, $this->validate_apparel_requirements( $product_id, $product_title, $product_data ) );

		// Validate title quality
		$errors = array_merge( $errors, $this->validate_title_quality( $product_id, $product_title, $product_data ) );

		// Validate description quality
		$errors = array_merge( $errors, $this->validate_description_quality( $product_id, $product_title, $product_data ) );

		// Validate Instagram Shopping policy compliance
		$errors = array_merge( $errors, $this->validate_instagram_policy_compliance( $product_id, $product_title, $product_data ) );

		// Add optimization suggestions
		$errors = array_merge( $errors, $this->generate_optimization_suggestions( $product_id, $product_title, $product_data ) );

		return $errors;
	}

	/**
	 * Validate Instagram-specific identifier requirements.
	 * Instagram requires brand, and recommends GTIN/MPN for better visibility.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_instagram_identifier_requirements( $product_id, $product_title, $product_data ) {
		$errors = array();

		$gtin  = $product_data['gtin'] ?? '';
		$mpn   = $product_data['mpn'] ?? '';

		// Note: Brand validation is handled by required_attributes check in abstract class
		// No need to duplicate it here

		// GTIN or MPN highly recommended for better product matching
		if ( $this->is_empty_value( $gtin ) && $this->is_empty_value( $mpn ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'gtin',
				'missing_product_identifiers',
				self::SEVERITY_WARNING,
				null,
				__( 'Providing GTIN or MPN is highly recommended for Instagram Shopping. Product identifiers improve discoverability and reduce disapprovals.', 'rex-product-feed' )
			);
		}

		// Validate GTIN checksum if present
		if ( ! $this->is_empty_value( $gtin ) ) {
			$clean_gtin = preg_replace( '/[^0-9]/', '', $gtin );
			if ( ! $this->validate_gtin_checksum( $clean_gtin ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'gtin',
					'invalid_gtin_checksum',
					self::SEVERITY_WARNING,
					$gtin,
					__( 'GTIN checksum validation failed. Verify this is a valid UPC, EAN, JAN, or ISBN code.', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate category requirements.
	 * Instagram Shopping requires proper categorization.
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

		$google_category   = $product_data['google_product_category'] ?? '';
		$facebook_category = $product_data['fb_product_category'] ?? '';
		$product_type      = $product_data['product_type'] ?? '';

		// At least one category is strongly recommended
		if ( $this->is_empty_value( $google_category ) && $this->is_empty_value( $facebook_category ) && $this->is_empty_value( $product_type ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'google_product_category',
				'missing_all_categories',
				self::SEVERITY_WARNING,
				null,
				__( 'At least one category is required: google_product_category, fb_product_category, or product_type. Proper categorization is essential for Instagram Shopping visibility.', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Validate image requirements.
	 * Instagram Shopping has strict image requirements.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_image_requirements( $product_id, $product_title, $product_data ) {
		$errors = array();

		$image_link = $product_data['image_link'] ?? '';

		if ( ! $this->is_empty_value( $image_link ) ) {
			// Check for placeholder images
			if ( $this->is_placeholder_image( $image_link ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'image_link',
					'placeholder_image',
					self::SEVERITY_ERROR,
					$image_link,
					__( 'Placeholder images are not allowed on Instagram Shopping. Use high-quality, actual product images.', 'rex-product-feed' )
				);
			}

			// Validate image URL quality
			$allowed_formats = array( 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff' );
			$errors = array_merge(
				$errors,
				$this->validate_image_url_quality( $product_id, $product_title, $image_link, $allowed_formats, 'Instagram Shopping' )
			);

			// Instagram-specific image quality recommendations
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'image_link',
				'instagram_image_quality_reminder',
				self::SEVERITY_INFO,
				substr( $image_link, 0, 100 ),
				__( 'Instagram Shopping image requirements: Minimum 500x500px (1024x1024px recommended), square or portrait aspect ratio, product should occupy at least 75% of image, no watermarks or promotional text. High-quality images perform better.', 'rex-product-feed' )
			);
		}

		// Recommend additional images for better engagement
		$additional_images = $product_data['additional_image_link'] ?? '';
		if ( $this->is_empty_value( $additional_images ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'additional_image_link',
				'missing_additional_images',
				self::SEVERITY_INFO,
				null,
				__( 'Adding multiple product images (additional_image_link) is recommended for Instagram Shopping. Multiple images increase engagement and conversion rates.', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Validate price requirements.
	 * Instagram Shopping requires valid pricing.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_price_requirements( $product_id, $product_title, $product_data ) {
		$errors = parent::validate_price_requirements( $product_id, $product_title, $product_data );

		$sale_price = $product_data['sale_price'] ?? '';
		$sale_price_effective_date = $product_data['sale_price_effective_date'] ?? '';

		// If sale_price is provided, sale_price_effective_date is required
		if ( ! $this->is_empty_value( $sale_price ) && $this->is_empty_value( $sale_price_effective_date ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'sale_price_effective_date',
				'missing_sale_price_effective_date',
				self::SEVERITY_WARNING,
				null,
				__( 'When sale_price is provided, sale_price_effective_date is required to specify when the sale is active on Instagram Shopping.', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Validate variant requirements.
	 * Instagram Shopping requires proper variant handling.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_variant_requirements( $product_id, $product_title, $product_data ) {
		// Use common validation from parent class with Instagram branding
		return $this->validate_common_variant_requirements( $product_id, $product_title, $product_data, true, 'Instagram Shopping' );
	}

	/**
	 * Validate apparel-specific requirements.
	 * Instagram Shopping emphasizes apparel and fashion products.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_apparel_requirements( $product_id, $product_title, $product_data ) {
		// Instagram-specific apparel requirements (stricter than Facebook)
		$instagram_apparel_attrs = array(
			'gender'    => array(
				'severity' => self::SEVERITY_WARNING,
				'message'  => __( 'Gender is strongly recommended for apparel products on Instagram Shopping for better targeting.', 'rex-product-feed' ),
			),
			'color'     => array(
				'severity' => self::SEVERITY_WARNING,
				'message'  => __( 'Color is strongly recommended for apparel products on Instagram Shopping.', 'rex-product-feed' ),
			),
			'size'      => array(
				'severity' => self::SEVERITY_WARNING,
				'message'  => __( 'Size is strongly recommended for apparel products on Instagram Shopping.', 'rex-product-feed' ),
			),
			'age_group' => array(
				'severity' => self::SEVERITY_INFO,
				'message'  => __( 'Age group helps with better categorization on Instagram Shopping.', 'rex-product-feed' ),
			),
		);

		return $this->validate_common_apparel_requirements( $product_id, $product_title, $product_data, $instagram_apparel_attrs );
	}

	/**
	 * Validate title quality.
	 * Instagram Shopping has specific title requirements.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_title_quality( $product_id, $product_title, $product_data ) {
		// Get base validation errors from parent
		$errors = parent::validate_title_quality( $product_id, $product_title, $product_data );

		$title = $this->strip_cdata( $product_data['title'] ?? '' );

		if ( ! $this->is_empty_value( $title ) ) {
			// Check for excessive punctuation
			if ( $this->has_excessive_punctuation( $title ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'title',
					'excessive_punctuation',
					self::SEVERITY_WARNING,
					substr( $title, 0, 50 ) . '...',
					__( 'Avoid excessive punctuation in titles for Instagram Shopping. Use clear, concise product names.', 'rex-product-feed' )
				);
			}

			// Check for promotional language using common helper
			if ( $this->has_promotional_language( $title ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'title',
					'promotional_language_in_title',
					self::SEVERITY_WARNING,
					substr( $title, 0, 50 ) . '...',
					__( 'Avoid promotional language in product titles on Instagram Shopping. Focus on product name, brand, and key attributes.', 'rex-product-feed' )
				);
			}

			// Check for all caps using common helper
			if ( $this->is_all_caps( $title ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'title',
					'all_caps_title',
					self::SEVERITY_WARNING,
					substr( $title, 0, 50 ) . '...',
					__( 'Avoid using all capital letters in titles. Use proper capitalization for better readability on Instagram Shopping.', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate description quality.
	 * Instagram Shopping requires detailed, policy-compliant descriptions.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_description_quality( $product_id, $product_title, $product_data ) {
		// Get base validation errors from parent
		$errors = parent::validate_description_quality( $product_id, $product_title, $product_data );

		$description = $this->strip_cdata( $product_data['description'] ?? '' );

		if ( ! $this->is_empty_value( $description ) ) {
			// Check for restricted HTML tags using common helper
			if ( $this->has_restricted_html( $description ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'description',
					'restricted_html_in_description',
					self::SEVERITY_ERROR,
					substr( $description, 0, 100 ) . '...',
					__( 'Description contains restricted HTML tags (script, iframe, object, embed). Instagram Shopping will reject these.', 'rex-product-feed' )
				);
			}

			// Recommend detailed descriptions
			if ( strlen( $description ) < 200 ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'description',
					'short_description',
					self::SEVERITY_INFO,
					substr( $description, 0, 100 ) . '...',
					__( 'Consider adding a more detailed description (200+ characters recommended). Include features, materials, dimensions, and benefits for better Instagram Shopping performance.', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate Instagram Shopping policy compliance.
	 * Check for common policy violations.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_instagram_policy_compliance( $product_id, $product_title, $product_data ) {
		$errors = array();

		$title       = $this->strip_cdata( $product_data['title'] ?? '' );
		$description = $this->strip_cdata( $product_data['description'] ?? '' );

		// Combine title and description for policy checks
		$combined_text = strtolower( $title . ' ' . $description );

		// Check for prohibited content keywords
		$prohibited_keywords = array(
			'tobacco',
			'cigarette',
			'vape',
			'e-cigarette',
			'weapon',
			'firearm',
			'ammunition',
			'adult content',
			'prescription drug',
			'illegal',
			'counterfeit',
			'replica',
		);

		foreach ( $prohibited_keywords as $keyword ) {
			if ( strpos( $combined_text, $keyword ) !== false ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'description',
					'prohibited_content_detected',
					self::SEVERITY_ERROR,
					$keyword,
					sprintf(
						/* translators: %s: prohibited keyword */
						__( 'Potential policy violation detected: "%s". Review Instagram Commerce Policies to ensure this product is allowed.', 'rex-product-feed' ),
						$keyword
					)
				);
				break; // Only report once per product
			}
		}

		// Check for excessive emoji usage
		if ( preg_match_all( '/[\x{1F300}-\x{1F9FF}]/u', $title, $matches ) ) {
			if ( count( $matches[0] ) > 2 ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'title',
					'excessive_emoji',
					self::SEVERITY_INFO,
					substr( $title, 0, 50 ) . '...',
					__( 'Consider reducing emoji usage in title. While emojis are allowed on Instagram Shopping, excessive use may affect readability.', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Generate optimization suggestions for Instagram Shopping.
	 * Instagram Shopping-specific recommendations.
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
		$max_suggestions = 5; // Limit suggestions to avoid overwhelming users

		// Priority 1: Missing GTIN for products with brand
		$brand = $product_data['brand'] ?? '';
		$gtin  = $product_data['gtin'] ?? '';
		if ( count( $suggestions ) < $max_suggestions && ! $this->is_empty_value( $brand ) && $this->is_empty_value( $gtin ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'gtin',
				'missing_gtin',
				self::SEVERITY_INFO,
				null,
				__( 'Adding GTIN is highly recommended for branded products on Instagram Shopping. It improves product matching and reduces disapprovals.', 'rex-product-feed' )
			);
		}

		// Priority 2: Missing google_product_category
		$google_category = $product_data['google_product_category'] ?? '';
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $google_category ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'google_product_category',
				'missing_google_category',
				self::SEVERITY_INFO,
				null,
				__( 'Adding google_product_category helps with better organization and filtering in Instagram Shopping.', 'rex-product-feed' )
			);
		}

		// Priority 3: Missing product_type
		$product_type = $product_data['product_type'] ?? '';
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_type ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'product_type',
				'missing_product_type',
				self::SEVERITY_INFO,
				null,
				__( 'Adding product_type helps with better organization and collection features on Instagram Shopping.', 'rex-product-feed' )
			);
		}

		// Priority 4: Short description (less detailed)
		$description = $product_data['description'] ?? '';
		if ( count( $suggestions ) < $max_suggestions && ! $this->is_empty_value( $description ) && strlen( $description ) < 200 ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'description',
				'enhance_description',
				self::SEVERITY_INFO,
				substr( $description, 0, 50 ) . '...',
				__( 'Consider expanding your description with more details about features, materials, dimensions, and benefits. Detailed descriptions improve conversion rates on Instagram Shopping.', 'rex-product-feed' )
			);
		}

		// Priority 5: Missing mobile_link (if different from link)
		$link        = $product_data['link'] ?? '';
		$mobile_link = $product_data['mobile_link'] ?? '';
		if ( count( $suggestions ) < $max_suggestions && ! $this->is_empty_value( $link ) && $this->is_empty_value( $mobile_link ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'mobile_link',
				'missing_mobile_link',
				self::SEVERITY_INFO,
				null,
				__( 'If you have a mobile-optimized version of your product page, add mobile_link. Most Instagram Shopping users browse on mobile devices.', 'rex-product-feed' )
			);
		}

		return $suggestions;
	}
}
