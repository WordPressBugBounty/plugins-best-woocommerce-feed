<?php
/**
 * Abstract Rex Feed Validator
 *
 * An abstract class definition that defines the structure for feed validation.
 *
 * @link       https://rextheme.com
 * @since 7.4.58
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 * @author     RexTheme <info@rextheme.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Abstract class for feed validation.
 *
 * This class provides the foundation for merchant-specific feed validators.
 * Each merchant (Google, Facebook, etc.) should extend this class and implement
 * their specific validation rules.
 *
 * @since 7.4.58
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
abstract class Rex_Feed_Abstract_Validator {

    /**
     * Severity level constants.
     */
    const SEVERITY_ERROR   = 'error';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_INFO    = 'info';

    /**
     * The merchant identifier.
     *
     * @since 7.4.58
     * @access protected
     * @var    string
     */
    protected $merchant;

    /**
     * The feed ID being validated.
     *
     * @since 7.4.58
     * @access protected
     * @var    int
     */
    protected $feed_id;

    /**
     * Collection of validation errors.
     *
     * @since 7.4.58
     * @access protected
     * @var    array
     */
    protected $validation_errors = array();

    /**
     * The validation rules configuration.
     *
     * @since 7.4.58
     * @access protected
     * @var    array
     */
    protected $rules = array();

    /**
     * Required attributes for the merchant.
     *
     * @since 7.4.58
     * @access protected
     * @var    array
     */
    protected $required_attributes = array();

    /**
     * Attribute character limits.
     *
     * @since 7.4.58
     * @access protected
     * @var    array
     */
    protected $character_limits = array();

    /**
     * Accepted enum values for attributes.
     *
     * @since 7.4.58
     * @access protected
     * @var    array
     */
    protected $enum_values = array();

    /**
     * Formatting rules for attributes.
     *
     * @since 7.4.58
     * @access protected
     * @var    array
     */
    protected $format_rules = array();

    /**
     * Feed configuration/mapping.
     *
     * @since 7.4.58
     * @access protected
     * @var    array
     */
    protected $feed_config = array();

    /**
     * Constructor.
     *
     * @since 7.4.58
     * @param int $feed_id The feed ID to validate.
     */
    public function __construct( $feed_id = 0 ) {
        $this->feed_id = $feed_id;
        $this->init_rules();
        $this->load_feed_config();
    }

    /**
     * Initialize validation rules.
     * Must be implemented by child classes.
     *
     * @since 7.4.58
     * @access protected
     * @return void
     */
    abstract protected function init_rules();

    /**
     * Get the merchant identifier.
     *
     * @since 7.4.58
     * @return string
     */
    public function get_merchant() {
        return $this->merchant;
    }

    /**
     * Load feed configuration from database.
     *
     * @since 7.4.58
     * @access protected
     * @return void
     */
    protected function load_feed_config() {
        if ( $this->feed_id > 0 ) {
            $this->feed_config = get_post_meta( $this->feed_id, '_rex_feed_feed_config', true );
            if ( ! is_array( $this->feed_config ) ) {
                $this->feed_config = array();
            }
        }
    }

    /**
     * Validate mapping-level configuration.
     * Checks if required attributes are mapped correctly.
     *
     * @since 7.4.58
     * @return array Array of mapping-level validation errors.
     */
    public function validate_mapping() {
        $mapping_errors = array();

        // Get mapped attributes from feed config
        $mapped_attributes = $this->get_mapped_attributes();

        // Check required attributes
        foreach ( $this->required_attributes as $attr => $config ) {
            if ( ! isset( $mapped_attributes[ $attr ] ) || empty( $mapped_attributes[ $attr ] ) ) {
                $mapping_errors[] = array(
                    'attribute'    => $attr,
                    'rule'         => 'required_attribute_missing',
                    'severity'     => $config['severity'] ?? self::SEVERITY_ERROR,
                    'message'      => sprintf(
                        /* translators: %s: attribute name */
                        __( 'Required attribute "%s" is not mapped in feed configuration.', 'rex-product-feed' ),
                        $attr
                    ),
                    'raw_value'    => null,
                    'expected'     => $config['description'] ?? '',
                );
            }
        }

        return $mapping_errors;
    }

    /**
     * Get mapped attributes from feed config.
     *
     * @since 7.4.58
     * @access protected
     * @return array
     */
    protected function get_mapped_attributes() {
        $mapped = array();

        if ( ! empty( $this->feed_config ) && is_array( $this->feed_config ) ) {
            foreach ( $this->feed_config as $config ) {
                if ( isset( $config['attr'] ) && ! empty( $config['attr'] ) ) {
                    $has_value = false;

                    if ( isset( $config['type'] ) ) {
                        if ( 'static' === $config['type'] && ! empty( $config['st_value'] ) ) {
                            $has_value = true;
                        } elseif ( 'meta' === $config['type'] && ! empty( $config['meta_key'] ) ) {
                            $has_value = true;
                        }
                    }

                    $mapped[ $config['attr'] ] = $has_value;
                }
            }
        }

        return $mapped;
    }

    /**
     * Validate a single product's data.
     *
     * @since 7.4.58
     * @param  int    $product_id     The product ID.
     * @param  array  $product_data   The normalized product data.
     * @param  string $display_title  Optional display title for error reporting.
     * @return array Array of validation errors for this product.
     */
    public function validate_product( $product_id, $product_data, $display_title = '' ) {
        $errors = array();
        $product = wc_get_product( $product_id );
        
        // Get product title for error reporting
        // Priority: 1) passed display_title, 2) product data title, 3) product object title, 4) fallback
        $product_title = '';
        
        if ( ! empty( $display_title ) ) {
            $product_title = $display_title;
        } elseif ( $product ) {
            $product_title = $product->get_title();
            
            // For variations (child products), include parent title and child ID
            if ( $product->is_type( 'variation' ) ) {
                $parent_id = $product->get_parent_id();
                $parent_product = wc_get_product( $parent_id );
                $parent_title = $parent_product ? $parent_product->get_name() : $product_title;
                $product_title = sprintf( '%s - Variation | Child ID: #%d', $parent_title, $product_id );
            }
        }
        
        if ( empty( $product_title ) && isset( $product_data['title'] ) ) {
            $product_title = $product_data['title'];
        }
        if ( empty( $product_title ) ) {
            $product_title = sprintf( __( 'Product #%d', 'rex-product-feed' ), $product_id );
        }

        // Check required attributes
        $errors = array_merge( $errors, $this->check_required_attributes( $product_id, $product_title, $product_data ) );

        // Check character limits
        $errors = array_merge( $errors, $this->check_character_limits( $product_id, $product_title, $product_data ) );

        // Check enum values
        $errors = array_merge( $errors, $this->check_enum_values( $product_id, $product_title, $product_data ) );

        // Check format rules
        $errors = array_merge( $errors, $this->check_format_rules( $product_id, $product_title, $product_data ) );

        // Run merchant-specific validations
        $errors = array_merge( $errors, $this->run_custom_validations( $product_id, $product_title, $product_data ) );

        return $errors;
    }

    /**
     * Check required attributes for a product.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array
     */
    protected function check_required_attributes( $product_id, $product_title, $product_data ) {
        $errors = array();

        // Get product type to handle special cases
        $product = wc_get_product( $product_id );
        $product_type = $product ? $product->get_type() : '';

        // For variations, check parent product data for inherited attributes
        $parent_data = array();
        if ( $product_type === 'variation' && $product ) {
            $parent_id = $product->get_parent_id();
            if ( $parent_id ) {
                $parent_product = wc_get_product( $parent_id );
                if ( $parent_product ) {
                    // Get parent data for common inherited attributes
                    $inherited_attrs = array( 'picture', 'image_link', 'available', 'availability', 'categoryId', 'categoryid', 'category_id', 'category', 'product_category' );
                    foreach ( $inherited_attrs as $inherited_attr ) {
                        if ( isset( $product_data[ $inherited_attr ] ) ) {
                            $parent_data[ $inherited_attr ] = $product_data[ $inherited_attr ];
                        }
                    }
                }
            }
        }

        foreach ( $this->required_attributes as $attr => $config ) {
            // Skip price validation for variable and grouped products (they don't have direct prices)
            if ( in_array( $attr, array( 'price', 'sale_price' ), true ) && in_array( $product_type, array( 'variable', 'grouped' ), true ) ) {
                continue;
            }

            $value = isset( $product_data[ $attr ] ) ? $product_data[ $attr ] : null;

            // For variations, check parent data if variation data is empty
            if ( $this->is_empty_value( $value ) && $product_type === 'variation' && isset( $parent_data[ $attr ] ) ) {
                $value = $parent_data[ $attr ];
            }

            if ( $this->is_empty_value( $value ) ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    $attr,
                    'required_attribute_empty',
                    $config['severity'] ?? self::SEVERITY_ERROR,
                    $value,
                    sprintf(
                        /* translators: %s: attribute name */
                        __( 'Required attribute "%s" is empty or missing.', 'rex-product-feed' ),
                        $attr
                    )
                );
            }
        }

        return $errors;
    }

    /**
     * Check character limits for attributes.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array
     */
    protected function check_character_limits( $product_id, $product_title, $product_data ) {
        $errors = array();
        foreach ( $this->character_limits as $attr => $limit_config ) {
            if ( ! isset( $product_data[ $attr ] ) ) {
                continue;
            }

            $value  = $product_data[ $attr ];
            $length = is_string( $value ) ? mb_strlen( $value ) : 0;

            // Skip if value is empty
            if ( $this->is_empty_value( $value ) ) {
                continue;
            }

            // Check minimum length
            if ( isset( $limit_config['min'] ) && $length < $limit_config['min'] ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    $attr,
                    'character_limit_min',
                    $limit_config['severity'] ?? self::SEVERITY_WARNING,
                    $value,
                    sprintf(
                        /* translators: 1: attribute name, 2: minimum characters, 3: actual length */
                        __( 'Attribute "%1$s" should have at least %2$d characters. Current length: %3$d.', 'rex-product-feed' ),
                        $attr,
                        $limit_config['min'],
                        $length
                    )
                );
            }

            // Check maximum length
            if ( isset( $limit_config['max'] ) && $length > $limit_config['max'] ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    $attr,
                    'character_limit_max',
                    $limit_config['severity'] ?? self::SEVERITY_ERROR,
                    $value,
                    sprintf(
                        /* translators: 1: attribute name, 2: maximum characters, 3: actual length */
                        __( 'Attribute "%1$s" exceeds maximum length of %2$d characters. Current length: %3$d.', 'rex-product-feed' ),
                        $attr,
                        $limit_config['max'],
                        $length
                    )
                );
            }
        }

        return $errors;
    }

    /**
     * Check enum values for attributes.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array
     */
    protected function check_enum_values( $product_id, $product_title, $product_data ) {
        $errors = array();

        foreach ( $this->enum_values as $attr => $enum_config ) {
            if ( ! isset( $product_data[ $attr ] ) || $this->is_empty_value( $product_data[ $attr ] ) ) {
                continue;
            }

            $value          = $product_data[ $attr ];
            $allowed_values = $enum_config['values'] ?? array();
            $case_sensitive = $enum_config['case_sensitive'] ?? false;

            // Strip CDATA if present and trim whitespace
            $clean_value = $this->strip_cdata( $value );
            
            // Skip validation if CDATA markers are still present (malformed)
            if (strpos($clean_value, '<![CDATA[') !== false || strpos($clean_value, ']]>') !== false) {
                continue;
            }

            // Special handling for availability to normalize common variations
            if ( 'availability' === $attr ) {
                $normalized_availability = $this->normalize_availability_value( $clean_value );
                if ( $normalized_availability ) {
                    // Valid availability found after normalization, skip validation
                    continue;
                }
            }

            $normalized_value = $case_sensitive ? $clean_value : strtolower( $clean_value );
            $found            = false;

            foreach ( $allowed_values as $allowed ) {
                $check_against = $case_sensitive ? $allowed : strtolower( $allowed );
                if ( $normalized_value === $check_against ) {
                    $found = true;
                    break;
                }
            }

            if ( ! $found ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    $attr,
                    'invalid_enum_value',
                    $enum_config['severity'] ?? self::SEVERITY_ERROR,
                    $clean_value,
                    sprintf(
                        /* translators: 1: attribute name, 2: value, 3: allowed values */
                        __( 'Invalid value "%2$s" for attribute "%1$s". Allowed values: %3$s.', 'rex-product-feed' ),
                        $attr,
                        $clean_value,
                        implode( ', ', $allowed_values )
                    )
                );
            }
        }

        return $errors;
    }

    /**
     * Check format rules for attributes.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array
     */
    protected function check_format_rules( $product_id, $product_title, $product_data ) {
        $errors = array();

        foreach ( $this->format_rules as $attr => $format_config ) {
            if ( ! isset( $product_data[ $attr ] ) || $this->is_empty_value( $product_data[ $attr ] ) ) {
                continue;
            }

            $value = $product_data[ $attr ];

            // Get merchant-specific price attributes (can be overridden by child classes)
            $price_attributes = $this->get_price_attributes();

            // Special handling for price attributes - if price exists, only warn about format
            if ( in_array( $attr, $price_attributes, true ) ) {
                // Strip everything except digits and decimal point
                $numeric_value = preg_replace( '/[^0-9.]/', '', (string) $value );

                // Check price availability (exists + numeric + > 0)
                if ( ! is_numeric( $numeric_value ) || (float) $numeric_value <= 0 ) {
                    $errors[] = $this->create_error_entry(
                        $product_id,
                        $product_title,
                        $attr,
                        'price_missing_or_invalid',
                        self::SEVERITY_WARNING,
                        $value,
                        sprintf(
                            /* translators: %s: attribute name */
                            __( 'The "%s" attribute must contain a valid price greater than 0.', 'rex-product-feed' ),
                            $attr
                        )
                    );
                }

                // No currency / format validation — stop here
                continue;
            }

            // Check regex pattern
            if ( isset( $format_config['pattern'] ) ) {
                // Strip CDATA if present and trim whitespace for pattern matching
                $clean_value = $this->strip_cdata( $value );
                
                if ( ! preg_match( $format_config['pattern'], $clean_value ) ) {
                    $errors[] = $this->create_error_entry(
                        $product_id,
                        $product_title,
                        $attr,
                        'invalid_format',
                        $format_config['severity'] ?? self::SEVERITY_ERROR,
                        $clean_value,
                        sprintf(
                            /* translators: 1: attribute name, 2: expected format */
                            __( 'Invalid format for attribute "%1$s". Expected format: %2$s.', 'rex-product-feed' ),
                            $attr,
                            $format_config['description'] ?? $format_config['pattern']
                        )
                    );
                }
            }

            // Check URL format
            if ( isset( $format_config['type'] ) && 'url' === $format_config['type'] ) {
                // Strip CDATA if present and trim whitespace
                $clean_value = $this->strip_cdata( $value );
                
                // Ensure clean_value is a string before using strpos
                if ( ! is_string( $clean_value ) ) {
                    continue;
                }

                // Skip validation if CDATA markers are still present or if value is empty
                if (strpos($clean_value, '<![CDATA[') === false && strpos($clean_value, ']]>') === false && !$this->is_empty_value($clean_value)) {
                    if ( ! filter_var( $clean_value, FILTER_VALIDATE_URL ) ) {
                        $errors[] = $this->create_error_entry(
                            $product_id,
                            $product_title,
                            $attr,
                            'invalid_url',
                            $format_config['severity'] ?? self::SEVERITY_ERROR,
                            $clean_value,
                            sprintf(
                                /* translators: %s: attribute name */
                                __( 'Invalid URL format for attribute "%s".', 'rex-product-feed' ),
                                $attr
                            )
                        );
                    }
                }
            }

            // Check numeric format
            if ( isset( $format_config['type'] ) && 'numeric' === $format_config['type'] ) {
                if ( ! is_numeric( $value ) ) {
                    $errors[] = $this->create_error_entry(
                        $product_id,
                        $product_title,
                        $attr,
                        'invalid_numeric',
                        $format_config['severity'] ?? self::SEVERITY_ERROR,
                        $value,
                        sprintf(
                            /* translators: %s: attribute name */
                            __( 'Attribute "%s" must be a numeric value.', 'rex-product-feed' ),
                            $attr
                        )
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * Get list of price-related attributes for format validation.
     * Child classes can override to specify merchant-specific price attributes.
     *
     * @since 7.4.58
     * @access protected
     * @return array List of price attribute names.
     */
    protected function get_price_attributes() {
        return array( 'price', 'sale_price', 'regular_price' );
    }

    /**
     * Run custom validations specific to the merchant.
     * Can be overridden by child classes.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array
     */
    protected function run_custom_validations( $product_id, $product_title, $product_data ) {
        return array();
    }

    /**
     * Normalize product data before validation.
     *
     * @since 7.4.58
     * @param  array $product_data Raw product data.
     * @return array Normalized product data.
     */
    public function normalize_product_data( $product_data ) {
        $normalized = array();

        foreach ( $product_data as $key => $value ) {
            // Trim string values
            if ( is_string( $value ) ) {
                $value = trim( $value );
            }

            // Normalize attribute key to lowercase
            $normalized_key = strtolower( $key );
            $normalized[ $normalized_key ] = $value;

            // Keep original key as well for case-sensitive lookups
            $normalized[ $key ] = $value;
        }

        return $normalized;
    }

    /**
     * Check if a value is considered empty.
     *
     * @since 7.4.58
     * @access protected
     * @param  mixed $value The value to check.
     * @return bool
     */
    protected function is_empty_value( $value ) {
        if ( is_null( $value ) ) {
            return true;
        }

        if ( is_string( $value ) && '' === trim( $value ) ) {
            return true;
        }

        if ( is_array( $value ) ) {
            // Empty array
            if ( empty( $value ) ) {
                return true;
            }
            
            // Check if all elements in array are empty
            foreach ( $value as $item ) {
                // If any item is not empty (recursively check), array is not empty
                if ( ! $this->is_empty_value( $item ) ) {
                    return false;
                }
            }
            
            // All elements are empty
            return true;
        }

        return false;
    }

    /**
     * Create a standardized error entry.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  string $attribute     The attribute that failed.
     * @param  string $rule          The violated rule.
     * @param  string $severity      The severity level.
     * @param  mixed  $raw_value     The raw value that failed.
     * @param  string $message       The error message.
     * @return array
     */
    protected function create_error_entry( $product_id, $product_title, $attribute, $rule, $severity, $raw_value, $message = '' ) {
        // Get product object to check if it's a variation
        $product = wc_get_product( $product_id );
        $is_variation = $product && $product->is_type( 'variation' );
        $parent_id = $is_variation ? $product->get_parent_id() : 0;
        
        return array(
            'product_id'    => $product_id,
            'product_title' => $product_title,
            'attribute'     => $attribute,
            'rule'          => $rule,
            'severity'      => $severity,
            'raw_value'     => $raw_value,
            'message'       => $message,
            'merchant'      => $this->merchant,
            'timestamp'     => current_time( 'mysql' ),
            'is_variation'  => $is_variation,
            'parent_id'     => $parent_id,
        );
    }

    /**
     * Get all validation errors.
     *
     * @since 7.4.58
     * @return array
     */
    public function get_validation_errors() {
        return $this->validation_errors;
    }

    /**
     * Clear all validation errors.
     *
     * @since 7.4.58
     * @return void
     */
    public function clear_validation_errors() {
        $this->validation_errors = array();
    }

    /**
     * Add validation error.
     *
     * @since 7.4.58
     * @param  array $error The error to add.
     * @return void
     */
    public function add_validation_error( $error ) {
        $this->validation_errors[] = $error;
    }

    /**
     * Get validation summary.
     *
     * @since 7.4.58
     * @param  array $errors Optional array of errors to summarize. If not provided, uses internal validation_errors.
     * @return array
     */
    public function get_validation_summary( $errors = null ) {
        $summary = array(
            'total_errors'   => 0,
            'total_warnings' => 0,
            'total_info'     => 0,
            'by_attribute'   => array(),
            'by_rule'        => array(),
        );

        // Use provided errors array or fall back to internal validation_errors
        $errors_to_process = is_array( $errors ) ? $errors : $this->validation_errors;

        foreach ( $errors_to_process as $error ) {
            switch ( $error['severity'] ) {
                case self::SEVERITY_ERROR:
                    $summary['total_errors']++;
                    break;
                case self::SEVERITY_WARNING:
                    $summary['total_warnings']++;
                    break;
                case self::SEVERITY_INFO:
                    $summary['total_info']++;
                    break;
            }

            // Group by attribute
            $attr = $error['attribute'];
            if ( ! isset( $summary['by_attribute'][ $attr ] ) ) {
                $summary['by_attribute'][ $attr ] = 0;
            }
            $summary['by_attribute'][ $attr ]++;

            // Group by rule
            $rule = $error['rule'];
            if ( ! isset( $summary['by_rule'][ $rule ] ) ) {
                $summary['by_rule'][ $rule ] = 0;
            }
            $summary['by_rule'][ $rule ]++;
        }

        return $summary;
    }

    /**
     * Strip CDATA wrapper from a value.
     * This is a common utility method used across all validators.
     *
     * @since 7.4.58
     * @access protected
     * @param  string $value The value that may contain CDATA wrapper.
     * @return string The value without CDATA wrapper, trimmed.
     */
    protected function strip_cdata( $value ) {
        if ( ! is_string( $value ) ) {
            return $value;
        }
        
        $clean_value = preg_replace( '/<!\[CDATA\[(.*)\]\]>/s', '$1', $value );
        return $clean_value !== null ? trim( $clean_value ) : '';
    }

    /**
     * Extract numeric value from a price string.
     * Removes currency codes and other non-numeric characters.
     *
     * @since 7.4.58
     * @access protected
     * @param  string $price The price string.
     * @return float The numeric price value.
     */
    protected function extract_price_numeric( $price ) {
        return (float) preg_replace( '/[^0-9.]/', '', (string) $price );
    }

    /**
     * Validate GTIN checksum.
     * This method validates GTINs (8, 12, 13, or 14 digits) using the standard checksum algorithm.
     *
     * @since 7.4.58
     * @access protected
     * @param  string $gtin The GTIN to validate.
     * @return bool True if valid, false otherwise.
     */
    protected function validate_gtin_checksum( $gtin ) {
        // Remove any non-numeric characters
        $gtin = preg_replace( '/[^0-9]/', '', $gtin );
        $length = strlen( $gtin );
        
        // Valid GTIN lengths: 8 (EAN-8), 12 (UPC-A), 13 (EAN-13), 14 (ITF-14)
        if ( ! in_array( $length, array( 8, 12, 13, 14 ), true ) || ! ctype_digit( $gtin ) ) {
            return false;
        }

        // For GTINs shorter than 14 digits, pad with zeros on the left
        $gtin = str_pad( $gtin, 14, '0', STR_PAD_LEFT );

        $sum = 0;
        for ( $i = 0; $i < 13; $i++ ) {
            $sum += (int) $gtin[ $i ] * ( ( $i % 2 === 0 ) ? 3 : 1 );
        }

        $check_digit = ( 10 - ( $sum % 10 ) ) % 10;

        return $check_digit === (int) $gtin[13];
    }

    /**
     * Normalize boolean value from various string representations.
     *
     * @since 7.4.58
     * @access protected
     * @param  mixed $value The value to normalize.
     * @return bool|null True, false, or null if value is empty.
     */
    protected function normalize_boolean( $value ) {
        if ( $this->is_empty_value( $value ) ) {
            return null;
        }

        $value = strtolower( trim( (string) $value ) );

        $true_values = array( 'true', '1', 'yes', 'y', 'on' );
        $false_values = array( 'false', '0', 'no', 'n', 'off' );

        if ( in_array( $value, $true_values, true ) ) {
            return true;
        }

        if ( in_array( $value, $false_values, true ) ) {
            return false;
        }

        return null;
    }

    /**
     * Validate product identifiers (GTIN/MPN/Brand combination).
     * This is a common validation used across multiple merchants.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @param  bool   $strict        Whether to apply strict validation.
     * @return array Array of validation errors.
     */
    protected function validate_product_identifiers( $product_id, $product_title, $product_data, $strict = false ) {
        $errors = array();
        
        // Strip CDATA from GTIN, MPN, and Brand before validation
        $gtin_value  = isset( $product_data['gtin'] ) ? $this->strip_cdata( $product_data['gtin'] ) : null;
        $mpn_value   = isset( $product_data['mpn'] ) ? $this->strip_cdata( $product_data['mpn'] ) : null;
        $brand_value = isset( $product_data['brand'] ) ? $this->strip_cdata( $product_data['brand'] ) : null;
        
        $has_gtin  = ! $this->is_empty_value( $gtin_value );
        $has_mpn   = ! $this->is_empty_value( $mpn_value );
        $has_brand = ! $this->is_empty_value( $brand_value );
        $identifier_exists = strtolower( $product_data['identifier_exists'] ?? '' );

        // If identifier_exists is not set to 'no' or 'false', then GTIN or (MPN + Brand) is required
        if ( ! in_array( $identifier_exists, array( 'no', 'false' ), true ) ) {
            if ( ! $has_gtin && ! ( $has_mpn && $has_brand ) ) {
                $severity = $strict ? self::SEVERITY_ERROR : self::SEVERITY_WARNING;
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'gtin',
                    'identifier_requirement',
                    $severity,
                    null,
                    __( 'Products should have either a GTIN, or both MPN and Brand. Set identifier_exists to "no" if these are not available.', 'rex-product-feed' )
                );
            }
        }

        // Validate GTIN checksum if provided
        if ( $has_gtin ) {
            $gtin = preg_replace( '/[^0-9]/', '', $gtin_value );
            $gtin_length = strlen( $gtin );
            
            // Check if GTIN has a valid length
            if ( ! in_array( $gtin_length, array( 8, 12, 13, 14 ), true ) ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'gtin',
                    'invalid_gtin_length',
                    self::SEVERITY_WARNING,
                    $gtin_value,
                    __( 'GTIN should be 8, 12, 13, or 14 digits long (UPC, EAN, JAN, ISBN, or ITF-14).', 'rex-product-feed' )
                );
            } elseif ( ! $this->validate_gtin_checksum( $gtin ) ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'gtin',
                    'invalid_gtin_checksum',
                    self::SEVERITY_WARNING,
                    $gtin_value,
                    __( 'The GTIN checksum appears to be invalid. Please verify the GTIN is correct.', 'rex-product-feed' )
                );
            }
        }

        return $errors;
    }

    /**
     * Validate price requirements (common logic across merchants).
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array Array of validation errors.
     */
    protected function validate_price_requirements( $product_id, $product_title, $product_data ) {
        $errors = array();

        // Get product type to handle special cases
        $product = wc_get_product( $product_id );
        $product_type = $product ? $product->get_type() : '';

        // Skip price validation for variable and grouped products
        if ( in_array( $product_type, array( 'variable', 'grouped' ), true ) ) {
            return $errors;
        }

        $price      = $product_data['price'] ?? '';
        $sale_price = $product_data['sale_price'] ?? '';

        // Extract numeric values
        $price_num      = $this->extract_price_numeric( $price );
        $sale_price_num = $this->extract_price_numeric( $sale_price );

        // Check for zero or negative price
        if ( ! $this->is_empty_value( $price ) && $price_num <= 0 ) {
            $errors[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'price',
                'invalid_price_value',
                self::SEVERITY_ERROR,
                $price,
                __( 'Price must be greater than zero.', 'rex-product-feed' )
            );
        }

        // Check if sale price is higher than regular price
        if ( ! $this->is_empty_value( $sale_price ) && $sale_price_num > 0 ) {
            if ( $sale_price_num >= $price_num ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'sale_price',
                    'sale_price_higher_than_price',
                    self::SEVERITY_ERROR,
                    $sale_price,
                    __( 'Sale price must be lower than the regular price.', 'rex-product-feed' )
                );
            }
        }

        return $errors;
    }

    /**
     * Validate title quality (common checks across merchants).
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array Array of validation errors.
     */
    protected function validate_title_quality( $product_id, $product_title, $product_data ) {
        $errors = array();

        $title = $this->strip_cdata( $product_data['title'] ?? '' );

        if ( ! $this->is_empty_value( $title ) ) {
            // Check for all caps
            if ( preg_match( '/^[A-Z\s\d\W]+$/', $title ) && strlen( $title ) > 10 ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'title',
                    'title_all_caps',
                    self::SEVERITY_WARNING,
                    $title,
                    __( 'Avoid using all capital letters in the title.', 'rex-product-feed' )
                );
            }

            // Check for promotional text
            $promo_patterns = array(
                '/free shipping/i',
                '/\d+%\s*off/i',
                '/sale/i',
                '/best price/i',
                '/cheapest/i',
                '/buy now/i',
                '/limited time/i',
            );

            foreach ( $promo_patterns as $pattern ) {
                if ( preg_match( $pattern, $title ) ) {
                    $errors[] = $this->create_error_entry(
                        $product_id,
                        $product_title,
                        'title',
                        'promotional_text_in_title',
                        self::SEVERITY_WARNING,
                        $title,
                        __( 'Avoid promotional text in product titles (e.g., "Free Shipping", "Sale").', 'rex-product-feed' )
                    );
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * Validate description quality (common checks across merchants).
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @return array Array of validation errors.
     */
    protected function validate_description_quality( $product_id, $product_title, $product_data ) {
        $errors = array();

        $description = $this->strip_cdata( $product_data['description'] ?? '' );

        if ( ! $this->is_empty_value( $description ) ) {
            // Check for promotional text
            $promo_patterns = array(
                '/free shipping/i',
                '/\d+%\s*off/i',
                '/best price/i',
                '/buy now/i',
                '/limited time/i',
                '/order now/i',
                '/click here/i',
            );

            foreach ( $promo_patterns as $pattern ) {
                if ( preg_match( $pattern, $description ) ) {
                    $errors[] = $this->create_error_entry(
                        $product_id,
                        $product_title,
                        'description',
                        'promotional_text_in_description',
                        self::SEVERITY_WARNING,
                        substr( $description, 0, 100 ) . '...',
                        __( 'Avoid promotional text in product descriptions (e.g., "Free Shipping", "Buy Now").', 'rex-product-feed' )
                    );
                    break;
                }
            }

            // Check for all caps
            if ( preg_match( '/^[A-Z\s\d\W]+$/', $description ) && strlen( $description ) > 50 ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'description',
                    'description_all_caps',
                    self::SEVERITY_WARNING,
                    substr( $description, 0, 100 ) . '...',
                    __( 'Avoid using all capital letters in the description.', 'rex-product-feed' )
                );
            }
        }

        return $errors;
    }

    /**
     * Normalize availability values to handle common variations across different merchants.
     * Supports multiple formats: Google (in_stock, out_of_stock), Facebook (in stock, out of stock), etc.
     *
     * @since 7.4.58
     * @access protected
     * @param  string $value The availability value to normalize.
     * @param  string $format The output format: 'google' (default), 'facebook', or 'original'.
     * @return string|false Normalized value or false if not valid.
     */
    protected function normalize_availability_value( $value, $format = 'google' ) {
        $normalized = strtolower( trim( $value ) );

        // Map common variations to their normalized forms
        $availability_map = array(
            'instock'            => array(
                'google'   => 'in_stock',
                'facebook' => 'in stock',
            ),
            'in-stock'           => array(
                'google'   => 'in_stock',
                'facebook' => 'in stock',
            ),
            'in stock'           => array(
                'google'   => 'in_stock',
                'facebook' => 'in stock',
            ),
            'in_stock'           => array(
                'google'   => 'in_stock',
                'facebook' => 'in stock',
            ),
            'outofstock'         => array(
                'google'   => 'out_of_stock',
                'facebook' => 'out of stock',
            ),
            'out-of-stock'       => array(
                'google'   => 'out_of_stock',
                'facebook' => 'out of stock',
            ),
            'out of stock'       => array(
                'google'   => 'out_of_stock',
                'facebook' => 'out of stock',
            ),
            'out_of_stock'       => array(
                'google'   => 'out_of_stock',
                'facebook' => 'out of stock',
            ),
            'preorder'           => array(
                'google'   => 'preorder',
                'facebook' => 'preorder',
            ),
            'pre-order'          => array(
                'google'   => 'preorder',
                'facebook' => 'preorder',
            ),
            'pre order'          => array(
                'google'   => 'preorder',
                'facebook' => 'preorder',
            ),
            'backorder'          => array(
                'google'   => 'backorder',
                'facebook' => 'available for order',
            ),
            'back-order'         => array(
                'google'   => 'backorder',
                'facebook' => 'available for order',
            ),
            'back order'         => array(
                'google'   => 'backorder',
                'facebook' => 'available for order',
            ),
            'onbackorder'        => array(
                'google'   => 'backorder',
                'facebook' => 'available for order',
            ),
            'on backorder'       => array(
                'google'   => 'backorder',
                'facebook' => 'available for order',
            ),
            'on back order'      => array(
                'google'   => 'backorder',
                'facebook' => 'available for order',
            ),
            'onback order'       => array(
                'google'   => 'backorder',
                'facebook' => 'available for order',
            ),
            'available for order' => array(
                'google'   => 'backorder',
                'facebook' => 'available for order',
            ),
            'availablefororder'  => array(
                'google'   => 'backorder',
                'facebook' => 'available for order',
            ),
            'discontinued'       => array(
                'google'   => 'out_of_stock',
                'facebook' => 'discontinued',
            ),
        );

        if ( isset( $availability_map[ $normalized ] ) ) {
            return $availability_map[ $normalized ][ $format ] ?? false;
        }

        return false;
    }

    /**
     * Check if image URL is a placeholder.
     * Common validation across multiple merchants.
     *
     * @since 7.4.58
     * @access protected
     * @param  string $image_url The image URL to check.
     * @return bool True if placeholder, false otherwise.
     */
    protected function is_placeholder_image( $image_url ) {
        $placeholder_patterns = array(
            '/placeholder/i',
            '/no-image/i',
            '/noimage/i',
            '/default\.jpg/i',
            '/default\.png/i',
        );

        foreach ( $placeholder_patterns as $pattern ) {
            if ( preg_match( $pattern, $image_url ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate image URL quality (protocol, HTTPS recommendation, format).
     * Common validation logic across multiple merchants.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id      The product ID.
     * @param  string $product_title   The product title.
     * @param  string $image_url       The image URL to validate.
     * @param  array  $allowed_formats Array of allowed image formats (e.g., ['jpg', 'png', 'gif']).
     * @param  string $merchant_name   Optional merchant name for custom messaging.
     * @return array Array of validation errors.
     */
    protected function validate_image_url_quality( $product_id, $product_title, $image_url, $allowed_formats = array(), $merchant_name = '' ) {
        $errors = array();
        
        // Strip CDATA if present
        $clean_image_link = $this->strip_cdata( $image_url );
        
        // Skip validation if CDATA markers are still present (malformed CDATA) or if value is empty
        if ( strpos( $clean_image_link, '<![CDATA[' ) !== false || strpos( $clean_image_link, ']]>' ) !== false || $this->is_empty_value( $clean_image_link ) ) {
            return $errors;
        }

        // Check image protocol
        if ( ! preg_match( '/^https?:\/\//i', $clean_image_link ) ) {
            $errors[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'image_link',
                'invalid_image_protocol',
                self::SEVERITY_ERROR,
                $clean_image_link,
                __( 'Image URL must start with http:// or https://', 'rex-product-feed' )
            );
        }
        
        // Recommend HTTPS
        if ( preg_match( '/^http:\/\//i', $clean_image_link ) ) {
            $message = $merchant_name 
                ? sprintf(
                    /* translators: %s: merchant name */
                    __( '%s recommends using HTTPS for image URLs for better security.', 'rex-product-feed' ),
                    $merchant_name
                )
                : __( 'Consider using HTTPS for image URLs for better security.', 'rex-product-feed' );
                
            $errors[] = $this->create_error_entry(
                $product_id,
                $product_title,
                'image_link',
                'non_https_image',
                self::SEVERITY_INFO,
                $clean_image_link,
                $message
            );
        }
        
        // Check for valid image formats if specified
        if ( ! empty( $allowed_formats ) ) {
            $format_pattern = '/\.(' . implode( '|', array_map( 'preg_quote', $allowed_formats ) ) . ')(?:\?|$)/i';
            if ( ! preg_match( $format_pattern, $clean_image_link ) ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'image_link',
                    'invalid_image_format',
                    self::SEVERITY_WARNING,
                    $clean_image_link,
                    sprintf(
                        /* translators: %s: allowed formats */
                        __( 'Image should be in one of these formats: %s.', 'rex-product-feed' ),
                        strtoupper( implode( ', ', $allowed_formats ) )
                    )
                );
            }
        }

        return $errors;
    }

    /**
     * Validate variant/item group requirements.
     * Common validation for products with variant attributes (color, size, pattern, material).
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @param  bool   $strict        Whether to check product type (variation).
     * @param  string $merchant_name Optional merchant name for custom messaging.
     * @return array Array of validation errors.
     */
    protected function validate_common_variant_requirements( $product_id, $product_title, $product_data, $strict = true, $merchant_name = '' ) {
        $errors = array();

        $has_color     = ! $this->is_empty_value( $product_data['color'] ?? null );
        $has_size      = ! $this->is_empty_value( $product_data['size'] ?? null );
        $has_pattern   = ! $this->is_empty_value( $product_data['pattern'] ?? null );
        $has_material  = ! $this->is_empty_value( $product_data['material'] ?? null );
        $item_group_id = $product_data['item_group_id'] ?? null;
        $has_group_id  = ! $this->is_empty_value( $item_group_id );

        // If product has variant attributes, it should have item_group_id
        if ( ( $has_color || $has_size || $has_pattern || $has_material ) && ! $has_group_id ) {
            // Check if this is a variation product (strict mode)
            $should_warn = true;
            if ( $strict ) {
                $product = wc_get_product( $product_id );
                $should_warn = $product && $product->is_type( 'variation' );
            }

            if ( $should_warn ) {
                $message = $merchant_name
                    ? sprintf(
                        /* translators: %s: merchant name */
                        __( 'Product variants should have an item_group_id to group them together on %s.', 'rex-product-feed' ),
                        $merchant_name
                    )
                    : __( 'Product variants should have an item_group_id to group them together.', 'rex-product-feed' );

                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    'item_group_id',
                    'missing_item_group_id',
                    self::SEVERITY_WARNING,
                    null,
                    $message
                );
            }
        }

        return $errors;
    }

    /**
     * Check if product is an apparel/fashion product.
     * More comprehensive check than is_apparel_category - checks product_type, category, and title.
     *
     * @since 7.4.58
     * @access protected
     * @param  array $product_data The product data.
     * @return bool True if apparel product, false otherwise.
     */
    protected function is_apparel_product( $product_data ) {
        $apparel_keywords = array(
            'apparel', 'clothing', 'shirt', 'pants', 'dress', 'shoes', 'jacket',
            'sweater', 'hoodie', 'jeans', 'shorts', 'skirt', 'blouse', 'coat',
            'suit', 't-shirt', 'footwear', 'sneakers', 'boots', 'sandals',
            'accessories', 'fashion', 'wear'
        );

        // Check product_type
        if ( ! empty( $product_data['product_type'] ) ) {
            $product_type = strtolower( $product_data['product_type'] );
            foreach ( $apparel_keywords as $keyword ) {
                if ( strpos( $product_type, $keyword ) !== false ) {
                    return true;
                }
            }
        }

        // Check google_product_category
        if ( ! empty( $product_data['google_product_category'] ) ) {
            $category = strtolower( $product_data['google_product_category'] );
            foreach ( $apparel_keywords as $keyword ) {
                if ( strpos( $category, $keyword ) !== false ) {
                    return true;
                }
            }
        }

        // Check fb_product_category
        if ( ! empty( $product_data['fb_product_category'] ) ) {
            $category = strtolower( $product_data['fb_product_category'] );
            foreach ( $apparel_keywords as $keyword ) {
                if ( strpos( $category, $keyword ) !== false ) {
                    return true;
                }
            }
        }

        // Check title as last resort
        $title = $this->strip_cdata( $product_data['title'] ?? '' );
        if ( ! empty( $title ) ) {
            $title_lower = strtolower( $title );
            foreach ( $apparel_keywords as $keyword ) {
                if ( strpos( $title_lower, $keyword ) !== false ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Validate apparel-specific attribute requirements.
     * Common validation for apparel products across different merchants.
     *
     * @since 7.4.58
     * @access protected
     * @param  int    $product_id    The product ID.
     * @param  string $product_title The product title.
     * @param  array  $product_data  The product data.
     * @param  array  $required_attrs Array of required attributes with their config.
     *                               Format: ['attr_name' => ['severity' => 'warning', 'message' => 'Custom message']]
     * @return array Array of validation errors.
     */
    protected function validate_common_apparel_requirements( $product_id, $product_title, $product_data, $required_attrs = array() ) {
        $errors = array();

        // Check if this is an apparel product
        if ( ! $this->is_apparel_product( $product_data ) ) {
            return $errors;
        }

        // Default apparel attributes if none specified
        if ( empty( $required_attrs ) ) {
            $required_attrs = array(
                'gender'    => array(
                    'severity' => self::SEVERITY_WARNING,
                    'message'  => __( 'Gender is recommended for apparel products.', 'rex-product-feed' ),
                ),
                'color'     => array(
                    'severity' => self::SEVERITY_WARNING,
                    'message'  => __( 'Color is recommended for apparel products.', 'rex-product-feed' ),
                ),
                'size'      => array(
                    'severity' => self::SEVERITY_WARNING,
                    'message'  => __( 'Size is recommended for apparel products.', 'rex-product-feed' ),
                ),
                'age_group' => array(
                    'severity' => self::SEVERITY_INFO,
                    'message'  => __( 'Age group helps with audience targeting for apparel.', 'rex-product-feed' ),
                ),
            );
        }

        // Check each required attribute
        foreach ( $required_attrs as $attr => $config ) {
            if ( $this->is_empty_value( $product_data[ $attr ] ?? null ) ) {
                $errors[] = $this->create_error_entry(
                    $product_id,
                    $product_title,
                    $attr,
                    'apparel_missing_' . $attr,
                    $config['severity'] ?? self::SEVERITY_WARNING,
                    null,
                    $config['message'] ?? sprintf(
                        /* translators: %s: attribute name */
                        __( '%s is recommended for apparel products.', 'rex-product-feed' ),
                        ucfirst( str_replace( '_', ' ', $attr ) )
                    )
                );
            }
        }

        return $errors;
    }

    /**
     * Check for excessive punctuation in text.
     * Common helper to detect multiple exclamation marks, question marks, or ellipsis.
     *
     * @since 7.4.58
     * @access protected
     * @param  string $text The text to check.
     * @return bool True if excessive punctuation found, false otherwise.
     */
    protected function has_excessive_punctuation( $text ) {
        return preg_match( '/[!?]{2,}/', $text ) || preg_match( '/\.{3,}/', $text );
    }

    /**
     * Check for promotional language in text.
     * Common helper to detect promotional phrases in titles/descriptions.
     *
     * @since 7.4.65
     * @access protected
     * @param  string $text The text to check.
     * @return bool True if promotional language found, false otherwise.
     */
    protected function has_promotional_language( $text ) {
        return preg_match( '/\b(free shipping|sale|discount|% off|limited time|buy now|shop now)\b/i', $text );
    }

    /**
     * Check if text is in all capital letters.
     * Common helper to detect all-caps text (which is often discouraged).
     *
     * @since 7.4.65
     * @access protected
     * @param  string $text          The text to check.
     * @param  int    $min_length    Minimum length to consider (default 5 chars).
     * @return bool True if all caps, false otherwise.
     */
    protected function is_all_caps( $text, $min_length = 5 ) {
        return strlen( $text ) > $min_length && $text === strtoupper( $text );
    }

    /**
     * Check for restricted HTML tags in description.
     * Common helper to detect script, iframe, object, embed tags.
     *
     * @since 7.4.65
     * @access protected
     * @param  string $text The text to check.
     * @return bool True if restricted HTML found, false otherwise.
     */
    protected function has_restricted_html( $text ) {
        return preg_match( '/<script|<iframe|<object|<embed/i', $text );
    }
}
