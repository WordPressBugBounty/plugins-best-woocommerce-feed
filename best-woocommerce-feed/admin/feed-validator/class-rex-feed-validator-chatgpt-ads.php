<?php
/**
 * ChatGPT Ads Feed Validator
 *
 * Implements ChatGPT Ads Feed specific validation rules.
 *
 * @since      7.10.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ChatGPT Ads Feed Validator.
 *
 * This class implements all ChatGPT Ads Feed specific validation rules based on:
 * OpenAI Product Feeds Specification for ChatGPT Ads (https://developers.openai.com/ads/product-feeds)
 *
 * Validation includes:
 * - Required attributes (id, title, description, link, image_link, price, availability, is_ads_eligible)
 * - Recommended attributes (gtin, mpn, brand, custom_label_0..4, inventory_quantity, additional_image_link, sale_price)
 * - Critical Ads rules (is_ads_eligible must be true for ad participation)
 * - Format validation (HTTPS URLs, ISO 4217 currency codes, dates)
 * - Character limits per OpenAI Ads specs
 *
 * @since      7.10.0
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validator_Chatgpt_Ads extends Rex_Feed_Abstract_Validator {

	/**
	 * Constructor.
	 *
	 * @since 7.10.0
	 * @param int $feed_id The feed ID to validate.
	 */
	public function __construct( $feed_id = 0 ) {
		$this->merchant = 'chatgpt_ads';
		parent::__construct( $feed_id );
	}

	/**
	 * Initialize ChatGPT Ads validation rules.
	 *
	 * @since 7.10.0
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
	 * Initialize required attributes for ChatGPT Ads Feed.
	 *
	 * @since 7.10.0
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
				'description' => __( 'Product landing page URL (HTTPS required)', 'rex-product-feed' ),
			),
			'image_link'         => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Main product image URL (HTTPS required)', 'rex-product-feed' ),
			),
			// Price and availability - REQUIRED
			'price'              => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product price with ISO 4217 currency code (e.g., 15.00 USD)', 'rex-product-feed' ),
			),
			'availability'       => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Stock availability status (in_stock, out_of_stock, preorder, backorder)', 'rex-product-feed' ),
			),
			// ChatGPT Ads Specific - REQUIRED FLAG
			'is_ads_eligible'    => array(
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Ads eligibility flag - must be set to "true" for products to participate in ChatGPT Ads', 'rex-product-feed' ),
			),
			// Categorization & Identifiers - RECOMMENDED
			'google_product_category' => array(
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Google Product Category or taxonomy ID helps ChatGPT match search intent with ads', 'rex-product-feed' ),
			),
			'product_type'       => array(
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Store product category path for ad targeting and segmentation', 'rex-product-feed' ),
			),
			'brand'              => array(
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Product brand or manufacturer name', 'rex-product-feed' ),
			),
			'condition'          => array(
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Product condition (new, refurbished, used)', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Initialize character limits for ChatGPT Ads Feed attributes.
	 *
	 * @since 7.10.0
	 * @access protected
	 * @return void
	 */
	protected function init_character_limits() {
		$this->character_limits = array(
			'id'                      => array(
				'max'         => 100,
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'ID cannot exceed 100 characters', 'rex-product-feed' ),
			),
			'title'                   => array(
				'min'         => 1,
				'max'         => 200,
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Title must be between 1 and 200 characters', 'rex-product-feed' ),
			),
			'description'             => array(
				'min'         => 1,
				'max'         => 5000,
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Description must be between 1 and 5000 characters', 'rex-product-feed' ),
			),
			'link'                    => array(
				'max'         => 2000,
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Product URL cannot exceed 2000 characters', 'rex-product-feed' ),
			),
			'mobile_link'             => array(
				'max'         => 2000,
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Mobile URL cannot exceed 2000 characters', 'rex-product-feed' ),
			),
			'image_link'              => array(
				'max'         => 2000,
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Image URL cannot exceed 2000 characters', 'rex-product-feed' ),
			),
			'brand'                   => array(
				'max'         => 70,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Brand cannot exceed 70 characters', 'rex-product-feed' ),
			),
			'gtin'                    => array(
				'max'         => 70,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'GTIN cannot exceed 70 characters', 'rex-product-feed' ),
			),
			'mpn'                     => array(
				'max'         => 70,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'MPN cannot exceed 70 characters', 'rex-product-feed' ),
			),
			'item_group_id'           => array(
				'max'         => 100,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Item group ID cannot exceed 100 characters', 'rex-product-feed' ),
			),
			'product_type'            => array(
				'max'         => 750,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Product type cannot exceed 750 characters', 'rex-product-feed' ),
			),
			'google_product_category' => array(
				'max'         => 750,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Google product category cannot exceed 750 characters', 'rex-product-feed' ),
			),
			'custom_label_0'          => array(
				'max'         => 100,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Custom label 0 cannot exceed 100 characters', 'rex-product-feed' ),
			),
			'custom_label_1'          => array(
				'max'         => 100,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Custom label 1 cannot exceed 100 characters', 'rex-product-feed' ),
			),
			'custom_label_2'          => array(
				'max'         => 100,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Custom label 2 cannot exceed 100 characters', 'rex-product-feed' ),
			),
			'custom_label_3'          => array(
				'max'         => 100,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Custom label 3 cannot exceed 100 characters', 'rex-product-feed' ),
			),
			'custom_label_4'          => array(
				'max'         => 100,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Custom label 4 cannot exceed 100 characters', 'rex-product-feed' ),
			),
			'ads_metadata'            => array(
				'max'         => 1000,
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Ads metadata cannot exceed 1000 characters', 'rex-product-feed' ),
			),
		);

		for ( $i = 1; $i <= 10; $i++ ) {
			$this->character_limits[ "additional_image_link_$i" ] = array(
				'max'         => 2000,
				'severity'    => self::SEVERITY_WARNING,
				'description' => sprintf( __( 'Additional image %d URL cannot exceed 2000 characters', 'rex-product-feed' ), $i ),
			);
		}
	}

	/**
	 * Initialize accepted enum values for ChatGPT Ads Feed attributes.
	 *
	 * @since 7.10.0
	 * @access protected
	 * @return void
	 */
	protected function init_enum_values() {
		$this->enum_values = array(
			'availability'      => array(
				'values'      => array( 'in_stock', 'in stock', 'out_of_stock', 'out of stock', 'preorder', 'backorder' ),
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Availability must be: in_stock, out_of_stock, preorder, or backorder', 'rex-product-feed' ),
			),
			'condition'         => array(
				'values'      => array( 'new', 'refurbished', 'used' ),
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Condition must be: new, refurbished, or used', 'rex-product-feed' ),
			),
			'is_ads_eligible'   => array(
				'values'      => array( 'true', 'false', '1', '0', 1, 0, true, false ),
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'is_ads_eligible must be "true" or "false"', 'rex-product-feed' ),
			),
			'identifier_exists' => array(
				'values'      => array( 'yes', 'no', 'true', 'false', 1, 0, true, false ),
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'identifier_exists must be: yes, no, true, or false', 'rex-product-feed' ),
			),
			'gender'            => array(
				'values'      => array( 'male', 'female', 'unisex' ),
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Gender must be: male, female, or unisex', 'rex-product-feed' ),
			),
			'age_group'         => array(
				'values'      => array( 'newborn', 'infant', 'toddler', 'kids', 'adult' ),
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Age group must be: newborn, infant, toddler, kids, or adult', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Initialize format rules for ChatGPT Ads Feed attributes.
	 *
	 * @since 7.10.0
	 * @access protected
	 * @return void
	 */
	protected function init_format_rules() {
		$this->format_rules = array(
			'price'      => array(
				'pattern'     => '/^\d+(\.\d{1,2})?\s+[A-Z]{3}$/',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Price must be a number followed by 3-letter currency code (e.g., 19.99 USD)', 'rex-product-feed' ),
			),
			'sale_price' => array(
				'pattern'     => '/^\d+(\.\d{1,2})?\s+[A-Z]{3}$/',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Sale price must be a number followed by 3-letter currency code (e.g., 14.99 USD)', 'rex-product-feed' ),
			),
			'link'       => array(
				'pattern'     => '/^https?:\/\/.+/i',
				'severity'    => self::SEVERITY_ERROR,
				'description' => __( 'Link must be a valid absolute URL', 'rex-product-feed' ),
			),
			'image_link' => array(
				'pattern'     => '/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i',
				'severity'    => self::SEVERITY_WARNING,
				'description' => __( 'Image link should point to an image file (jpg, png, gif, webp)', 'rex-product-feed' ),
			),
		);
	}

	/**
	 * Run custom validation logic for ChatGPT Ads.
	 *
	 * @since 7.10.0
	 * @access protected
	 * @param  int    $product_id    The WooCommerce product ID.
	 * @param  string $product_title The product title.
	 * @param  array  $product_data  The mapped product data.
	 * @return array
	 */
	protected function custom_validation( $product_id, $product_title, $product_data ) {
		$errors = array();

		// Validate is_ads_eligible
		$is_ads_eligible = isset( $product_data['is_ads_eligible'] ) ? strtolower( trim( (string) $product_data['is_ads_eligible'] ) ) : '';
		if ( 'false' === $is_ads_eligible || '0' === $is_ads_eligible ) {
			$errors[] = $this->create_error(
				'is_ads_eligible',
				__( 'Product has is_ads_eligible set to "false" and will NOT participate in ChatGPT Ads campaigns.', 'rex-product-feed' ),
				self::SEVERITY_WARNING,
				$product_id,
				$product_title,
				$product_data['is_ads_eligible'],
				__( 'Set is_ads_eligible to "true" to include this product in ChatGPT Ads.', 'rex-product-feed' )
			);
		}

		// Validate Product Identifiers
		$brand             = isset( $product_data['brand'] ) ? trim( (string) $product_data['brand'] ) : '';
		$gtin              = isset( $product_data['gtin'] ) ? trim( (string) $product_data['gtin'] ) : '';
		$mpn               = isset( $product_data['mpn'] ) ? trim( (string) $product_data['mpn'] ) : '';
		$identifier_exists = isset( $product_data['identifier_exists'] ) ? strtolower( trim( (string) $product_data['identifier_exists'] ) ) : '';

		$has_identifier = ( ! empty( $gtin ) || ( ! empty( $brand ) && ! empty( $mpn ) ) );
		$explicit_no    = in_array( $identifier_exists, array( 'no', 'false', '0' ), true );

		if ( ! $has_identifier && ! $explicit_no ) {
			$errors[] = $this->create_error(
				'identifiers',
				__( 'ChatGPT Ads requires unique product identifiers: provide GTIN, or Brand + MPN, or set identifier_exists to "no".', 'rex-product-feed' ),
				self::SEVERITY_WARNING,
				$product_id,
				$product_title,
				'',
				__( 'Map GTIN or Brand and MPN to ensure proper ad indexing by OpenAI.', 'rex-product-feed' )
			);
		}

		// Validate HTTPS URLs
		foreach ( array( 'link', 'image_link' ) as $url_key ) {
			if ( ! empty( $product_data[ $url_key ] ) && 0 !== strpos( (string) $product_data[ $url_key ], 'https://' ) ) {
				$errors[] = $this->create_error(
					$url_key,
					sprintf( __( '%s is not using HTTPS. OpenAI requires secure HTTPS links.', 'rex-product-feed' ), $url_key ),
					self::SEVERITY_WARNING,
					$product_id,
					$product_title,
					$product_data[ $url_key ],
					__( 'Update your store URL or image links to use HTTPS.', 'rex-product-feed' )
				);
			}
		}

		return $errors;
	}
}
