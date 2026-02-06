<?php
/**
 * Google Feed Validator
 *
 * Implements Google Shopping specific validation rules.
 *
 * @since 7.4.58
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Google Shopping Feed Validator.
 *
 * This class implements all Google Shopping specific validation rules based on:
 * Google Product Data Specification (https://support.google.com/merchants/answer/7052112)
 * Last updated: January 2026
 *
 * Validation includes:
 * - Required attributes (id, title, description, link, image_link, availability, price)
 * - Character limits per Google specs (id: 50, title: 150, description: 5000, etc.)
 * - URL format validation (RFC 2396/RFC 1738 compliance, http/https protocol)
 * - Image format validation (JPEG, WebP, PNG, GIF, BMP, TIFF)
 * - CDATA handling (strips CDATA wrappers before validation)
 * - Price format (ISO 4217 currency codes)
 * - Date format (ISO 8601)
 * - Product identifier requirements (GTIN, MPN, Brand)
 * - Apparel-specific attributes (size, color, gender, age_group)
 * - Accepted enum values for all choice fields
 *
 * @since 7.4.58
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validator_Google extends Rex_Feed_Abstract_Validator {

    /**
     * Constructor.
     *
     * @since 7.4.58
     * @param int $feed_id The feed ID to validate.
     */
    public function __construct( $feed_id = 0 ) {
        $this->merchant = 'google';
        parent::__construct( $feed_id );
    }

    /**
     * Initialize Google Shopping validation rules.
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
     * Initialize required attributes for Google Shopping.
     * Based on Google Product Data Specification:
     * https://support.google.com/merchants/answer/7052112
     *
     * @since 7.4.58
     * @access protected
     * @return void
     */
    protected function init_required_attributes() {
        $this->required_attributes = array(
            // Basic product data - REQUIRED
            'id' => array(
                'severity'    => self::SEVERITY_ERROR,
                'description' => __( 'Unique product identifier (max 50 characters)', 'rex-product-feed' ),
            ),
            'title' => array(
                'severity'    => self::SEVERITY_ERROR,
                'description' => __( 'Product title (max 150 characters)', 'rex-product-feed' ),
            ),
            'description' => array(
                'severity'    => self::SEVERITY_ERROR,
                'description' => __( 'Product description (max 5000 characters)', 'rex-product-feed' ),
            ),
            'link' => array(
                'severity'    => self::SEVERITY_ERROR,
                'description' => __( 'Product landing page URL', 'rex-product-feed' ),
            ),
            'image_link' => array(
                'severity'    => self::SEVERITY_ERROR,
                'description' => __( 'Main product image URL', 'rex-product-feed' ),
            ),
            // Price and availability - REQUIRED
            'availability' => array(
                'severity'    => self::SEVERITY_ERROR,
                'description' => __( 'Product availability: in_stock, out_of_stock, preorder, backorder', 'rex-product-feed' ),
            ),
            'price' => array(
                'severity'    => self::SEVERITY_ERROR,
                'description' => __( 'Product price with ISO 4217 currency code (e.g., 15.00 USD)', 'rex-product-feed' ),
            ),
            // Product identifiers - Required for most products
            'brand' => array(
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Product brand (required for all new products except movies, books, musical recordings)', 'rex-product-feed' ),
            ),
            // Detailed product description - Required if used/refurbished
            'condition' => array(
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Product condition: new, refurbished, used (required if product is used or refurbished)', 'rex-product-feed' ),
            ),
        );
    }

    /**
     * Initialize character limits for Google Shopping attributes.
     * Based on Google Product Data Specification:
     * https://support.google.com/merchants/answer/7052112
     *
     * @since 7.4.58
     * @access protected
     * @return void
     */
    protected function init_character_limits() {
        $this->character_limits = array(
            // Basic product data
            'id' => array(
                'max'      => 50,
                'severity' => self::SEVERITY_ERROR,
            ),
            'title' => array(
                'min'      => 1,
                'max'      => 150,
                'severity' => self::SEVERITY_ERROR,
            ),
            'description' => array(
                'min'      => 1,
                'max'      => 5000,
                'severity' => self::SEVERITY_ERROR,
            ),
            'link' => array(
                'max'      => 2000,
                'severity' => self::SEVERITY_ERROR,
            ),
            'image_link' => array(
                'max'      => 2000,
                'severity' => self::SEVERITY_ERROR,
            ),
            'additional_image_link' => array(
                'max'      => 2000,
                'severity' => self::SEVERITY_WARNING,
            ),
            'mobile_link' => array(
                'max'      => 2000,
                'severity' => self::SEVERITY_WARNING,
            ),
            'virtual_model_link' => array(
                'max'      => 2000,
                'severity' => self::SEVERITY_WARNING,
            ),
            // Product identifiers
            'brand' => array(
                'max'      => 70,
                'severity' => self::SEVERITY_ERROR,
            ),
            'gtin' => array(
                'min'      => 8,
                'max'      => 50, // Max 50 characters, max 14 per value
                'severity' => self::SEVERITY_ERROR,
            ),
            'mpn' => array(
                'max'      => 70,
                'severity' => self::SEVERITY_ERROR,
            ),
            // Product category
            'google_product_category' => array(
                'max'      => 750,
                'severity' => self::SEVERITY_WARNING,
            ),
            'product_type' => array(
                'max'      => 750,
                'severity' => self::SEVERITY_WARNING,
            ),
            // Detailed product description
            'color' => array(
                'max'      => 100, // Max 40 characters per color
                'severity' => self::SEVERITY_WARNING,
            ),
            'size' => array(
                'max'      => 100,
                'severity' => self::SEVERITY_WARNING,
            ),
            'material' => array(
                'max'      => 200,
                'severity' => self::SEVERITY_WARNING,
            ),
            'pattern' => array(
                'max'      => 100,
                'severity' => self::SEVERITY_WARNING,
            ),
            'item_group_id' => array(
                'max'      => 50,
                'severity' => self::SEVERITY_ERROR,
            ),
            'product_highlight' => array(
                'max'      => 150,
                'severity' => self::SEVERITY_WARNING,
            ),
            // Shopping campaigns
            'custom_label_0' => array(
                'max'      => 100,
                'severity' => self::SEVERITY_WARNING,
            ),
            'custom_label_1' => array(
                'max'      => 100,
                'severity' => self::SEVERITY_WARNING,
            ),
            'custom_label_2' => array(
                'max'      => 100,
                'severity' => self::SEVERITY_WARNING,
            ),
            'custom_label_3' => array(
                'max'      => 100,
                'severity' => self::SEVERITY_WARNING,
            ),
            'custom_label_4' => array(
                'max'      => 100,
                'severity' => self::SEVERITY_WARNING,
            ),
            'promotion_id' => array(
                'max'      => 50,
                'severity' => self::SEVERITY_WARNING,
            ),
            'ads_redirect' => array(
                'max'      => 2000,
                'severity' => self::SEVERITY_WARNING,
            ),
            'lifestyle_image_link' => array(
                'max'      => 2000,
                'severity' => self::SEVERITY_WARNING,
            ),
            'short_title' => array(
                'max'      => 150, // Recommended: 5-65 characters
                'severity' => self::SEVERITY_WARNING,
            ),
            // Marketplaces
            'external_seller_id' => array(
                'min'      => 1,
                'max'      => 50,
                'severity' => self::SEVERITY_ERROR,
            ),
            // Shipping
            'shipping_label' => array(
                'max'      => 100,
                'severity' => self::SEVERITY_WARNING,
            ),
        );
    }

    /**
     * Initialize accepted enum values for Google Shopping attributes.
     * Based on Google Product Data Specification:
     * https://support.google.com/merchants/answer/7052112
     *
     * @since 7.4.58
     * @access protected
     * @return void
     */
    protected function init_enum_values() {
        $this->enum_values = array(
            // Price and availability
            'availability' => array(
                'values' => array(
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
            // Detailed product description
            'condition' => array(
                'values' => array(
                    'new',
                    'refurbished',
                    'used',
                ),
                'case_sensitive' => false,
                'severity'       => self::SEVERITY_ERROR,
            ),
            'adult' => array(
                'values' => array(
                    'yes',
                    'no',
                    'true',
                    'false',
                ),
                'case_sensitive' => false,
                'severity'       => self::SEVERITY_ERROR,
            ),
            'is_bundle' => array(
                'values' => array(
                    'yes',
                    'no',
                    'true',
                    'false',
                ),
                'case_sensitive' => false,
                'severity'       => self::SEVERITY_ERROR,
            ),
            'identifier_exists' => array(
                'values' => array(
                    'yes',
                    'no',
                    'true',
                    'false',
                ),
                'case_sensitive' => false,
                'severity'       => self::SEVERITY_ERROR,
            ),
            // Age group - Required for apparel
            'age_group' => array(
                'values' => array(
                    'newborn',   // 0-3 months old
                    'infant',    // 3-12 months old
                    'toddler',   // 1-5 years old
                    'kids',      // 5-13 years old
                    'adult',     // Teens or older
                ),
                'case_sensitive' => false,
                'severity'       => self::SEVERITY_ERROR,
            ),
            // Gender - Required for apparel
            'gender' => array(
                'values' => array(
                    'male',
                    'female',
                    'unisex',
                ),
                'case_sensitive' => false,
                'severity'       => self::SEVERITY_ERROR,
            ),
            // Size type - Optional for apparel
            'size_type' => array(
                'values' => array(
                    'regular',
                    'petite',
                    'maternity',
                    'big',
                    'tall',
                    'plus',
                ),
                'case_sensitive' => false,
                'severity'       => self::SEVERITY_WARNING,
            ),
            // Size system - Optional for apparel
            'size_system' => array(
                'values' => array(
                    'US', 'UK', 'EU', 'DE', 'FR', 'JP', 'CN', 'IT', 'BR', 'MEX', 'AU',
                ),
                'case_sensitive' => true,
                'severity'       => self::SEVERITY_WARNING,
            ),
            // Energy efficiency - Switzerland, Norway, UK
            'energy_efficiency_class' => array(
                'values' => array(
                    'A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
                ),
                'case_sensitive' => true,
                'severity'       => self::SEVERITY_WARNING,
            ),
            'min_energy_efficiency_class' => array(
                'values' => array(
                    'A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
                ),
                'case_sensitive' => true,
                'severity'       => self::SEVERITY_WARNING,
            ),
            'max_energy_efficiency_class' => array(
                'values' => array(
                    'A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
                ),
                'case_sensitive' => true,
                'severity'       => self::SEVERITY_WARNING,
            ),
            // Destinations
            'excluded_destination' => array(
                'values' => array(
                    'Shopping_ads',
                    'Buy_on_Google_listings',
                    'Display_ads',
                    'Local_inventory_ads',
                    'Free_listings',
                    'Free_local_listings',
                    'YouTube_Shopping',
                ),
                'case_sensitive' => true,
                'severity'       => self::SEVERITY_WARNING,
            ),
            'included_destination' => array(
                'values' => array(
                    'Shopping_ads',
                    'Buy_on_Google_listings',
                    'Display_ads',
                    'Local_inventory_ads',
                    'Free_listings',
                    'Free_local_listings',
                    'YouTube_Shopping',
                ),
                'case_sensitive' => true,
                'severity'       => self::SEVERITY_WARNING,
            ),
            'pause' => array(
                'values' => array(
                    'ads',
                ),
                'case_sensitive' => false,
                'severity'       => self::SEVERITY_WARNING,
            ),
        );
    }

    /**
     * Initialize format rules for Google Shopping attributes.
     * Based on Google Product Data Specification:
     * https://support.google.com/merchants/answer/7052112
     *
     * Key Google Requirements:
     * - URLs: Must start with http:// or https://, comply with RFC 2396 or RFC 1738
     * - Images: JPEG (.jpg/.jpeg), WebP (.webp), PNG (.png), GIF (.gif), BMP (.bmp), TIFF (.tif/.tiff)
     * - CDATA: Values wrapped in CDATA are automatically stripped before validation
     * - Prices: ISO 4217 currency codes (e.g., "15.00 USD")
     * - Dates: ISO 8601 format (e.g., "2024-01-01T00:00+00:00")
     * - GTINs: 8, 12, 13, or 14 digits (UPC, EAN, JAN, ISBN, ITF-14)
     *
     * @since 7.4.58
     * @access protected
     * @return void
     */
    protected function init_format_rules() {
        $this->format_rules = array(
            // URL formats
            'link' => array(
                'type'        => 'url',
                'severity'    => self::SEVERITY_ERROR,
                'description' => __( 'Must be a valid URL starting with http:// or https://', 'rex-product-feed' ),
            ),
            'mobile_link' => array(
                'type'        => 'url',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Must be a valid URL starting with http:// or https://', 'rex-product-feed' ),
            ),
            'image_link' => array(
                'type'        => 'url',
                'severity'    => self::SEVERITY_ERROR,
                'description' => __( 'Must be a valid image URL starting with http:// or https://', 'rex-product-feed' ),
            ),
            'additional_image_link' => array(
                'type'        => 'url',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Must be a valid image URL starting with http:// or https://', 'rex-product-feed' ),
            ),
            'ads_redirect' => array(
                'type'        => 'url',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Must be a valid URL with same registered domain as link', 'rex-product-feed' ),
            ),
            'virtual_model_link' => array(
                'type'        => 'url',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Must be a valid URL pointing to a .gltf or .glb file', 'rex-product-feed' ),
            ),
            'lifestyle_image_link' => array(
                'type'        => 'url',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Must be a valid URL starting with http:// or https://', 'rex-product-feed' ),
            ),
            // Price formats (ISO 4217)
            'price' => array(
                'pattern'     => '/^([A-Z]{3}\s*)?\d+(\.\d{1,2})?(\s*[A-Z]{3})?$/',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number with ISO 4217 currency code (e.g., 15.00 USD or USD 15.00)', 'rex-product-feed' ),
            ),
            'sale_price' => array(
                'pattern'     => '/^([A-Z]{3}\s*)?\d+(\.\d{1,2})?(\s*[A-Z]{3})?$/',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number with ISO 4217 currency code (e.g., 8.00 USD or USD 8.00)', 'rex-product-feed' ),
            ),
            'cost_of_goods_sold' => array(
                'pattern'     => '/^([A-Z]{3}\s*)?\d+(\.\d{1,2})?(\s*[A-Z]{3})?$/',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number with ISO 4217 currency code (e.g., 5.00 USD or USD 5.00)', 'rex-product-feed' ),
            ),
            'auto_pricing_min_price' => array(
                'pattern'     => '/^([A-Z]{3}\s*)?\d+(\.\d{1,2})?(\s*[A-Z]{3})?$/',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number with ISO 4217 currency code', 'rex-product-feed' ),
            ),
            // GTIN format (8-14 digits)
            'gtin' => array(
                'pattern'     => '/^[\d\s-]{8,50}$/',
                'severity'    => self::SEVERITY_ERROR,
                'description' => __( 'Must be 8, 12, 13, or 14 digits (UPC, EAN, JAN, ISBN, or ITF-14)', 'rex-product-feed' ),
            ),
            // Date formats (ISO 8601)
            'availability_date' => array(
                'pattern'     => '/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:?\d{2}|Z)?)?$/',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: ISO 8601 date (e.g., 2024-01-01T00:00+00:00)', 'rex-product-feed' ),
            ),
            'expiration_date' => array(
                'pattern'     => '/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:?\d{2}|Z)?)?$/',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: ISO 8601 date, max 30 days in future', 'rex-product-feed' ),
            ),
            'sale_price_effective_date' => array(
                'pattern'     => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:?\d{2}|Z)?\/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:?\d{2}|Z)?$/',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: ISO 8601 date range with "/" separator (e.g., 2024-01-01T00:00+00:00/2024-01-31T23:59+00:00)', 'rex-product-feed' ),
            ),
            // Numeric formats
            'multipack' => array(
                'type'        => 'numeric',
                'severity'    => self::SEVERITY_ERROR,
                'description' => __( 'Must be a positive integer', 'rex-product-feed' ),
            ),
            'max_handling_time' => array(
                'type'        => 'numeric',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Must be an integer >= 0 (business days)', 'rex-product-feed' ),
            ),
            'min_handling_time' => array(
                'type'        => 'numeric',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Must be an integer >= 0 (business days)', 'rex-product-feed' ),
            ),
            // Weight/dimension formats
            'shipping_weight' => array(
                'pattern'     => '/^\d+(\.\d+)?\s*(lb|oz|g|kg)$/i',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number followed by unit (lb, oz, g, kg)', 'rex-product-feed' ),
            ),
            'product_weight' => array(
                'pattern'     => '/^\d+(\.\d+)?\s*(lb|oz|g|kg)$/i',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number followed by unit (lb, oz, g, kg). Range: 0-2000', 'rex-product-feed' ),
            ),
            'shipping_length' => array(
                'pattern'     => '/^\d+(\.\d+)?\s*(in|cm)$/i',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number followed by unit (in or cm). Range: 1-150 in or 1-400 cm', 'rex-product-feed' ),
            ),
            'shipping_width' => array(
                'pattern'     => '/^\d+(\.\d+)?\s*(in|cm)$/i',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number followed by unit (in or cm). Range: 1-150 in or 1-400 cm', 'rex-product-feed' ),
            ),
            'shipping_height' => array(
                'pattern'     => '/^\d+(\.\d+)?\s*(in|cm)$/i',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number followed by unit (in or cm). Range: 1-150 in or 1-400 cm', 'rex-product-feed' ),
            ),
            'product_length' => array(
                'pattern'     => '/^\d+(\.\d+)?\s*(in|cm)$/i',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number followed by unit (in or cm). Range: 1-3000', 'rex-product-feed' ),
            ),
            'product_width' => array(
                'pattern'     => '/^\d+(\.\d+)?\s*(in|cm)$/i',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number followed by unit (in or cm). Range: 1-3000', 'rex-product-feed' ),
            ),
            'product_height' => array(
                'pattern'     => '/^\d+(\.\d+)?\s*(in|cm)$/i',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number followed by unit (in or cm). Range: 1-3000', 'rex-product-feed' ),
            ),
            // Unit pricing
            'unit_pricing_measure' => array(
                'pattern'     => '/^\d+(\.\d+)?\s*(oz|lb|mg|g|kg|floz|pt|qt|gal|ml|cl|l|cbm|in|ft|yd|cm|m|sqft|sqm)$/i',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: number followed by unit (weight: oz, lb, mg, g, kg; volume: floz, pt, qt, gal, ml, cl, l, cbm; length: in, ft, yd, cm, m; area: sqft, sqm)', 'rex-product-feed' ),
            ),
            'unit_pricing_base_measure' => array(
                'pattern'     => '/^(1|10|100|2|4|8)\s*(oz|lb|mg|g|kg|floz|pt|qt|gal|ml|cl|l|cbm|in|ft|yd|cm|m|sqft|sqm)$/i',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: integer (1, 10, 100, 2, 4, 8) followed by unit. Must use same unit as unit_pricing_measure', 'rex-product-feed' ),
            ),
            // ISO country codes
            'ships_from_country' => array(
                'pattern'     => '/^[A-Z]{2}$/',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Must be a 2-character ISO 3166-1 alpha-2 country code (e.g., US, DE)', 'rex-product-feed' ),
            ),
            'shopping_ads_excluded_country' => array(
                'pattern'     => '/^[A-Z]{2}$/',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Must be a 2-character ISO 3166-1 alpha-2 country code', 'rex-product-feed' ),
            ),
            // Certification (EU/EFTA/UK)
            'certification' => array(
                'pattern'     => '/^(EC|European_Commission):(EPREL):\d+$/',
                'severity'    => self::SEVERITY_WARNING,
                'description' => __( 'Format: authority:name:code (e.g., EC:EPREL:123456)', 'rex-product-feed' ),
            ),
        );
    }

    /**
     * Run Google-specific custom validations.
     * Based on Google Product Data Specification:
     * https://support.google.com/merchants/answer/7052112
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

        // Validate GTIN/MPN/Brand combination
        $errors = array_merge( $errors, $this->validate_product_identifiers( $product_id, $product_title, $product_data ) );

        // Validate image requirements
        $errors = array_merge( $errors, $this->validate_image_requirements( $product_id, $product_title, $product_data ) );

        // Validate price requirements
        $errors = array_merge( $errors, $this->validate_price_requirements( $product_id, $product_title, $product_data ) );

        // Validate availability requirements
        $errors = array_merge( $errors, $this->validate_availability_requirements( $product_id, $product_title, $product_data ) );

        // Validate apparel attributes
        $errors = array_merge( $errors, $this->validate_apparel_requirements( $product_id, $product_title, $product_data ) );

        // Validate variant attributes
        $errors = array_merge( $errors, $this->validate_variant_requirements( $product_id, $product_title, $product_data ) );

        // Validate title quality
        $errors = array_merge( $errors, $this->validate_title_quality( $product_id, $product_title, $product_data ) );

        // Validate description quality
        $errors = array_merge( $errors, $this->validate_description_quality( $product_id, $product_title, $product_data ) );

        // Validate shipping attributes
        $errors = array_merge( $errors, $this->validate_shipping_requirements( $product_id, $product_title, $product_data ) );

        // Add optimization suggestions
        $errors = array_merge( $errors, $this->generate_optimization_suggestions( $product_id, $product_title, $product_data ) );

        return $errors;
    }

    /**
     * Validate availability requirements.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array
     */
    protected function validate_availability_requirements( $product_id, $product_title, $product_data ) {
        $errors = array();

        $availability = strtolower( $product_data['availability'] ?? '' );
        $availability_date = $product_data['availability_date'] ?? '';

        // If availability is preorder or backorder, availability_date is required
        if ( in_array( $availability, array( 'preorder', 'backorder' ), true ) ) {
            if ( $this->is_empty_value( $availability_date ) ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'availability_date',
                    'missing_availability_date',
                    self::SEVERITY_ERROR,
                    null,
                    __( 'Availability date is required when availability is set to preorder or backorder.', 'rex-product-feed' )
                );
            }
        }

        return $errors;
    }

    /**
     * Validate description quality.
     * Extends parent implementation with Google-specific checks.
     *
     * @since 7.4.58
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
            // Check for HTML tags (should be plain text) - Google-specific
            if ( preg_match( '/<[^>]+>/', $description ) ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'description',
                    'html_in_description',
                    self::SEVERITY_WARNING,
                    substr( $description, 0, 100 ) . '...',
                    __( 'Description should be plain text. Remove HTML tags for better display.', 'rex-product-feed' )
                );
            }
        }

        return $errors;
    }

    /**
     * Validate shipping requirements.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array
     */
    protected function validate_shipping_requirements( $product_id, $product_title, $product_data ) {
        $errors = array();

        $shipping_length = $product_data['shipping_length'] ?? '';
        $shipping_width = $product_data['shipping_width'] ?? '';
        $shipping_height = $product_data['shipping_height'] ?? '';

        // If any shipping dimension is provided, all should be provided
        $has_length = ! $this->is_empty_value( $shipping_length );
        $has_width = ! $this->is_empty_value( $shipping_width );
        $has_height = ! $this->is_empty_value( $shipping_height );

        if ( ( $has_length || $has_width || $has_height ) && ! ( $has_length && $has_width && $has_height ) ) {
            $errors[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'shipping_length',
                'incomplete_shipping_dimensions',
                self::SEVERITY_WARNING,
                null,
                __( 'If you submit shipping dimensions, include all three: shipping_length, shipping_width, and shipping_height.', 'rex-product-feed' )
            );
        }

        // Check min/max handling time consistency
        $min_handling = $product_data['min_handling_time'] ?? '';
        $max_handling = $product_data['max_handling_time'] ?? '';

        if ( ! $this->is_empty_value( $min_handling ) && ! $this->is_empty_value( $max_handling ) ) {
            if ( (int) $min_handling > (int) $max_handling ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'min_handling_time',
                    'invalid_handling_time_range',
                    self::SEVERITY_ERROR,
                    $min_handling . ' - ' . $max_handling,
                    __( 'Minimum handling time cannot be greater than maximum handling time.', 'rex-product-feed' )
                );
            }
        }

        return $errors;
    }

    /**
     * Generate optimization suggestions for better feed performance.
     * Limited to max 3 suggestions per product to avoid information overload.
     * Based on Google's best practices for Shopping ads and free listings.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array
     */
    protected function generate_optimization_suggestions( $product_id, $product_title, $product_data ) {
        $suggestions = array();
        $max_suggestions = 3; // Limit suggestions per product

        // Priority 1: Suggest adding Google Product Category (most important for visibility)
        if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['google_product_category'] ?? null ) ) {
            $suggestions[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'google_product_category',
                'missing_google_category',
                self::SEVERITY_INFO,
                null,
                __( 'Adding a Google Product Category improves product visibility and helps Google match your products to the right queries.', 'rex-product-feed' )
            );
        }

        // Priority 2: Suggest adding GTIN for better visibility
        $gtin_suggestion_check = isset( $product_data['gtin'] ) ? $this->strip_cdata( $product_data['gtin'] ) : null;
        if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $gtin_suggestion_check ) ) {
            $suggestions[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'gtin',
                'missing_gtin_suggestion',
                self::SEVERITY_INFO,
                null,
                __( 'Adding GTIN (UPC, EAN, ISBN) significantly improves product matching and visibility in Google Shopping results.', 'rex-product-feed' )
            );
        }

        // Priority 3: Suggest adding additional images (up to 10 allowed)
        if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['additional_image_link'] ?? null ) ) {
            $suggestions[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'additional_image_link',
                'no_additional_images',
                self::SEVERITY_INFO,
                null,
                __( 'Add up to 10 additional product images to increase click-through rates. Images can show the product in use or from different angles.', 'rex-product-feed' )
            );
        }

        // Priority 4: Suggest product_type for better campaign organization
        if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['product_type'] ?? null ) ) {
            $suggestions[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'product_type',
                'missing_product_type',
                self::SEVERITY_INFO,
                null,
                __( 'Adding product_type helps organize bidding and reporting in Google Ads Shopping campaigns.', 'rex-product-feed' )
            );
        }

        // Priority 5: Short description optimization
        $description = $product_data['description'] ?? '';
        if ( count( $suggestions ) < $max_suggestions && ! $this->is_empty_value( $description ) && strlen( $description ) < 150 ) {
            $suggestions[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'description',
                'short_description',
                self::SEVERITY_INFO,
                substr( $description, 0, 50 ) . '...',
                __( 'Your description is short. Add more details about features, materials, and benefits (aim for 500-1000 characters).', 'rex-product-feed' )
            );
        }

        // Priority 6: Suggest using product highlights
        if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['product_highlight'] ?? null ) ) {
            $suggestions[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'product_highlight',
                'missing_product_highlights',
                self::SEVERITY_INFO,
                null,
                __( 'Add 2-10 product highlights to showcase key features and benefits (max 150 characters each).', 'rex-product-feed' )
            );
        }

        // Priority 7: Suggest sale price for products with regular price only
        $price = $product_data['price'] ?? '';
        $sale_price = $product_data['sale_price'] ?? '';
        if ( count( $suggestions ) < $max_suggestions && ! $this->is_empty_value( $price ) && $this->is_empty_value( $sale_price ) ) {
            $suggestions[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'sale_price',
                'no_sale_price',
                self::SEVERITY_INFO,
                null,
                __( 'Consider adding sale prices to highlight discounts. Sale prices display prominently in Shopping results.', 'rex-product-feed' )
            );
        }

        // Priority 8: Suggest custom labels for campaign organization
        $has_custom_labels = ! $this->is_empty_value( $product_data['custom_label_0'] ?? null ) ||
                             ! $this->is_empty_value( $product_data['custom_label_1'] ?? null ) ||
                             ! $this->is_empty_value( $product_data['custom_label_2'] ?? null );
        if ( count( $suggestions ) < $max_suggestions && ! $has_custom_labels ) {
            $suggestions[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'custom_label_0',
                'missing_custom_labels',
                self::SEVERITY_INFO,
                null,
                __( 'Use custom labels (0-4) to organize products by season, bestseller status, price range, or margin for better campaign management.', 'rex-product-feed' )
            );
        }

        // Priority 9: Suggest shipping information
        if ( count( $suggestions ) < $max_suggestions && $this->is_empty_value( $product_data['shipping'] ?? null ) && $this->is_empty_value( $product_data['shipping_weight'] ?? null ) ) {
            $suggestions[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'shipping',
                'missing_shipping_info',
                self::SEVERITY_INFO,
                null,
                __( 'Adding shipping information helps customers understand total cost and improves ad performance.', 'rex-product-feed' )
            );
        }

        return $suggestions;
    }



    /**
     * Validate image requirements.
     *
     * @since 7.4.58
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
                    __( 'Image appears to be a placeholder. Use actual product images for better performance.', 'rex-product-feed' )
                );
            }

            // Validate image URL quality using common helper
            $allowed_formats = array( 'jpg', 'jpeg', 'webp', 'png', 'gif', 'bmp', 'tiff' );
            $errors = array_merge(
                $errors,
                $this->validate_image_url_quality( $product_id, $product_title, $image_link, $allowed_formats )
            );
        }

        return $errors;
    }

    /**
     * Validate price requirements.
     * Uses parent implementation - no Google-specific overrides needed.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array
     */
    // Method inherited from parent class

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

        $category = strtolower( $product_data['google_product_category'] ?? '' );

        // Check if this is an apparel product
        $apparel_keywords = array( 'apparel', 'clothing', 'shoes', 'accessories' );
        $is_apparel       = false;

        foreach ( $apparel_keywords as $keyword ) {
            if ( strpos( $category, $keyword ) !== false ) {
                $is_apparel = true;
                break;
            }
        }

        if ( $is_apparel ) {
            // Gender is required for apparel
            if ( $this->is_empty_value( $product_data['gender'] ?? null ) ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'gender',
                    'apparel_missing_gender',
                    self::SEVERITY_WARNING,
                    null,
                    __( 'Gender is required for apparel products.', 'rex-product-feed' )
                );
            }

            // Age group is required for apparel
            if ( $this->is_empty_value( $product_data['age_group'] ?? null ) ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'age_group',
                    'apparel_missing_age_group',
                    self::SEVERITY_WARNING,
                    null,
                    __( 'Age group is required for apparel products.', 'rex-product-feed' )
                );
            }

            // Color is required for apparel
            if ( $this->is_empty_value( $product_data['color'] ?? null ) ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'color',
                    'apparel_missing_color',
                    self::SEVERITY_WARNING,
                    null,
                    __( 'Color is required for apparel products.', 'rex-product-feed' )
                );
            }

            // Size is required for apparel (except accessories)
            if ( strpos( $category, 'accessories' ) === false ) {
                if ( $this->is_empty_value( $product_data['size'] ?? null ) ) {
                    $errors[] = $this->create_error_entry(
                        $product_id,
                        $product_title,
                        'size',
                        'apparel_missing_size',
                        self::SEVERITY_WARNING,
                        null,
                        __( 'Size is required for apparel products (except accessories).', 'rex-product-feed' )
                    );
                }
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
        // Use common validation from parent class
        return $this->validate_common_variant_requirements( $product_id, $product_title, $product_data, true );
    }

    /**
     * Validate title quality.
     * Extends parent implementation with Google-specific checks.
     *
     * @since 7.4.58
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
            // Check for excessive punctuation (Google-specific)
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
     * Override format rules check to handle Google-specific price validation.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array
     */
    /**
     * Get Google Shopping specific price attributes.
     *
     * @since 7.4.58
     * @access protected
     * @return array List of price attribute names.
     */
    protected function get_price_attributes() {
        return array( 'price', 'sale_price', 'cost_of_goods_sold', 'auto_pricing_min_price' );
    }
}