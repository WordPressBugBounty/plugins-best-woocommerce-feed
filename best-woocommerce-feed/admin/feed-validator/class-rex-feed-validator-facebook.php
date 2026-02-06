<?php
/**
 * Facebook Marketplace Feed Validator
 *
 * Implements Facebook Marketplace specific validation rules.
 *
 * @link       https://rextheme.com
 * @since      7.4.64
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 * @author     RexTheme <info@rextheme.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Facebook Marketplace Feed Validator.
 *
 * This class implements all Facebook Marketplace specific validation rules including:
 * - Required attributes
 * - Character limits
 * - Accepted enum values
 * - Format rules
 * - Facebook-specific business rules
 *
 * Reference: https://www.facebook.com/business/help/120325381656392?id=725943027795860
 *
 * @since      7.4.64
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validator_Facebook extends Rex_Feed_Abstract_Validator {

	/**
	 * Constructor.
	 *
	 * @since 7.4.64
	 * @param int $feed_id The feed ID to validate.
	 */
	public function __construct( $feed_id = 0 ) {
		$this->merchant = 'facebook';
		parent::__construct( $feed_id );
	}

	/**
	 * Initialize Facebook Marketplace validation rules.
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
	 * Initialize required attributes for Facebook Marketplace.
	 * Based on Facebook Product Feed Specification:
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
				'description' => __( 'Main product image URL (min 500x500px)', 'rex-product-feed' ),
			),
			'brand'              => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product brand name', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Initialize character limits for Facebook Marketplace attributes.
	 * Based on Facebook Product Feed Specification:
	 * https://www.facebook.com/business/help/120325381656392?id=725943027795860
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_character_limits() {
		$this->character_limits = array(
			// Basic product data
			'id'                      => array(
				'max'      => 100,
				'severity' => self::SEVERITY_ERROR,
			),
			'title'                   => array(
				'min'      => 1,
				'max'      => 200,
				'severity' => self::SEVERITY_ERROR,
			),
			'description'             => array(
				'min'      => 1,
				'max'      => 9999,
				'severity' => self::SEVERITY_ERROR,
			),
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
			// Product identifiers
			'brand'                   => array(
				'max'      => 100,
				'severity' => self::SEVERITY_ERROR,
			),
			'gtin'                    => array(
				'min'      => 8,
				'max'      => 50,
				'severity' => self::SEVERITY_WARNING,
			),
			'mpn'                     => array(
				'max'      => 70,
				'severity' => self::SEVERITY_WARNING,
			),
			// Product category
			'google_product_category' => array(
				'max'      => 750,
				'severity' => self::SEVERITY_WARNING,
			),
			'fb_product_category'     => array(
				'max'      => 750,
				'severity' => self::SEVERITY_WARNING,
			),
			'product_type'            => array(
				'max'      => 750,
				'severity' => self::SEVERITY_WARNING,
			),
			// Detailed product attributes
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
			'item_group_id'           => array(
				'max'      => 100,
				'severity' => self::SEVERITY_ERROR,
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
	 * Initialize accepted enum values for Facebook Marketplace attributes.
	 * Based on Facebook Product Feed Specification:
	 * https://www.facebook.com/business/help/120325381656392?id=725943027795860
	 *
	 * @since 7.4.64
	 * @access protected
	 * @return void
	 */
	protected function init_enum_values() {
		$this->enum_values = array(
			// Availability
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
			// Condition
			'condition'    => array(
				'values'         => array(
					'new',
					'refurbished',
					'used',
				),
				'case_sensitive' => false,
				'severity'       => self::SEVERITY_ERROR,
			),
			// Age group
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
			// Gender
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
	 * Initialize format rules for Facebook Marketplace attributes.
	 * Based on Facebook Product Feed Specification:
	 * https://www.facebook.com/business/help/120325381656392?id=725943027795860
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
			'regular_price'         => array(
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
				'description' => __( 'Format: ISO 8601 date (e.g., 2024-01-01T00:00+00:00)', 'rex-product-feed' ),
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
	 * Run Facebook-specific custom validations.
	 * Based on Facebook Product Feed Specification:
	 * https://www.facebook.com/business/help/120325381656392?id=725943027795860
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

		// Validate identifier requirements (GTIN/MPN/Brand)
		$errors = array_merge( $errors, $this->validate_product_identifiers( $product_id, $product_title, $product_data ) );

		// Validate category requirements
		$errors = array_merge( $errors, $this->validate_category_requirements( $product_id, $product_title, $product_data ) );

		// Validate image requirements
		$errors = array_merge( $errors, $this->validate_image_requirements( $product_id, $product_title, $product_data ) );

		// Validate price requirements
		$errors = array_merge( $errors, $this->validate_price_requirements( $product_id, $product_title, $product_data ) );

		// Validate variant requirements
		$errors = array_merge( $errors, $this->validate_variant_requirements( $product_id, $product_title, $product_data ) );

		// Validate apparel attributes
		$errors = array_merge( $errors, $this->validate_apparel_requirements( $product_id, $product_title, $product_data ) );

		// Validate title quality
		$errors = array_merge( $errors, $this->validate_title_quality( $product_id, $product_title, $product_data ) );

		// Validate description quality
		$errors = array_merge( $errors, $this->validate_description_quality( $product_id, $product_title, $product_data ) );

		// Validate shipping and return policy
		$errors = array_merge( $errors, $this->validate_shipping_return_requirements( $product_id, $product_title, $product_data ) );

		// Add optimization suggestions
		$errors = array_merge( $errors, $this->generate_optimization_suggestions( $product_id, $product_title, $product_data ) );

		return $errors;
	}

	/**
	 * Validate category requirements.
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

		// At least one category is required
		if ( $this->is_empty_value( $google_category ) && $this->is_empty_value( $facebook_category ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'google_product_category',
				'missing_category',
				self::SEVERITY_WARNING,
				null,
				__( 'At least one category is required: google_product_category or fb_product_category. This helps Facebook categorize your products correctly.', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Validate image requirements.
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
			// Check for placeholder images using common helper
			if ( $this->is_placeholder_image( $image_link ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'image_link',
					'placeholder_image',
					self::SEVERITY_WARNING,
					$image_link,
					__( 'Image appears to be a placeholder. Use actual product images for better performance on Facebook.', 'rex-product-feed' )
				);
			}

			// Validate image URL quality using common helper
			$allowed_formats = array( 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff' );
			$errors = array_merge(
				$errors,
				$this->validate_image_url_quality( $product_id, $product_title, $image_link, $allowed_formats, 'Facebook' )
			);
		}

		return $errors;
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
		// Use common validation from parent class with Facebook branding
		return $this->validate_common_variant_requirements( $product_id, $product_title, $product_data, true, 'Facebook Marketplace' );
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
		// Facebook-specific apparel requirements (all are INFO level recommendations)
		$facebook_apparel_attrs = array(
			'gender' => array(
				'severity' => self::SEVERITY_INFO,
				'message'  => __( 'Gender is recommended for apparel products to improve visibility on Facebook.', 'rex-product-feed' ),
			),
			'color'  => array(
				'severity' => self::SEVERITY_INFO,
				'message'  => __( 'Color is recommended for apparel products.', 'rex-product-feed' ),
			),
			'size'   => array(
				'severity' => self::SEVERITY_INFO,
				'message'  => __( 'Size is recommended for apparel products.', 'rex-product-feed' ),
			),
		);

		return $this->validate_common_apparel_requirements( $product_id, $product_title, $product_data, $facebook_apparel_attrs );
	}

	/**
	 * Validate title quality.
	 * Extends parent implementation with Facebook-specific checks.
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
			// Check for excessive punctuation (Facebook-specific)
			if ( $this->has_excessive_punctuation( $title ) ) {
				$errors[] = $this->create_error_entry(
					$product_id,
					$product_title,
					'title',
					'excessive_punctuation',
					self::SEVERITY_INFO,
					$title,
					__( 'Avoid excessive punctuation in product titles.', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate description quality.
	 * Extends parent implementation with Facebook-specific checks.
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
					__( 'Description contains restricted HTML tags (script, iframe, object, embed). Facebook will reject these.', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}

	/**
	 * Validate shipping and return policy requirements.
	 *
	 * @since 7.4.64
	 * @access protected
	 * @param  int    $product_id    The product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The product data.
	 * @return array
	 */
	protected function validate_shipping_return_requirements( $product_id, $product_title, $product_data ) {
		$errors = array();

		$shipping      = $product_data['shipping'] ?? '';
		$return_policy = $product_data['return_policy'] ?? '';

		// Shipping info is recommended
		if ( $this->is_empty_value( $shipping ) ) {
			$errors[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'shipping',
				'missing_shipping_info',
				self::SEVERITY_INFO,
				null,
				__( 'Adding shipping information helps customers understand delivery costs and improves conversion rates.', 'rex-product-feed' )
			);
		}

		return $errors;
	}

	/**
	 * Generate optimization suggestions for better feed performance.
	 * Limited to max 3 suggestions per product to avoid information overload.
	 * Based on Facebook's best practices for Commerce Manager.
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

		// Get mapped attributes to avoid suggesting attributes that are already mapped but empty
		$mapped_attributes = $this->get_mapped_attributes();

		// Priority 1: Suggest adding additional images
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['additional_image_link'] ?? null ) && ! isset( $mapped_attributes['additional_image_link'] ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'additional_image_link',
				'no_additional_images',
				self::SEVERITY_INFO,
				null,
				__( 'Add additional product images to showcase your product from different angles. This significantly improves engagement on Facebook.', 'rex-product-feed' )
			);
		}

		// Priority 2: Suggest adding GTIN
		$gtin_suggestion_check = isset( $product_data['gtin'] ) ? $this->strip_cdata( $product_data['gtin'] ) : null;
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $gtin_suggestion_check ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'gtin',
				'missing_gtin_suggestion',
				self::SEVERITY_INFO,
				null,
				__( 'Adding GTIN (UPC, EAN, ISBN) significantly improves product matching and visibility on Facebook.', 'rex-product-feed' )
			);
		}

		// Priority 3: Suggest sale price (only if not already mapped in feed config)
		$price      = $product_data['price'] ?? '';
		$sale_price = $product_data['sale_price'] ?? '';
		if ( count( $suggestions ) < $max_suggestions && ! $this->is_empty_value( $price ) && $this->is_empty_value( $sale_price ) && ! isset( $mapped_attributes['sale_price'] ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'sale_price',
				'no_sale_price',
				self::SEVERITY_INFO,
				null,
				__( 'Adding sale prices can highlight discounts and improve click-through rates on Facebook.', 'rex-product-feed' )
			);
		}

		// Priority 4: Suggest product_type
		if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['product_type'] ?? null ) && ! isset( $mapped_attributes['product_type'] ) ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'product_type',
				'missing_product_type',
				self::SEVERITY_INFO,
				null,
				__( 'Adding product_type helps with better organization and filtering in Facebook Commerce Manager.', 'rex-product-feed' )
			);
		}

		// Priority 5: Short description
		$description = $product_data['description'] ?? '';
		if ( count( $suggestions ) < $max_suggestions && ! $this->is_empty_value( $description ) && strlen( $description ) < 100 ) {
			$suggestions[] = $this->create_error_entry(
				$product_id,
				$product_title,
				'description',
				'short_description',
				self::SEVERITY_INFO,
				substr( $description, 0, 50 ) . '...',
				__( 'Your description is short. Add more details about features, materials, and benefits (aim for 500+ characters).', 'rex-product-feed' )
			);
		}

		return $suggestions;
	}

}