<?php
/**
 * Feed Validation Results Handler
 *
 * Manages storage, retrieval, and display of validation results.
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
 * Feed Validation Results Handler.
 *
 * This class handles:
 * - Storing validation results in database
 * - Retrieving validation results
 * - Filtering and searching results
 * - Generating reports
 *
 * @since 7.4.58
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validation_Results {

    /**
     * The meta key for storing validation results.
     *
     * @since 7.4.58
     * @access const
     * @var    string
     */
    const META_KEY_RESULTS = '_rex_feed_validation_results';

    /**
     * The meta key for storing validation summary.
     *
     * @since 7.4.58
     * @access const
     * @var    string
     */
    const META_KEY_SUMMARY = '_rex_feed_validation_summary';

    /**
     * The meta key for storing last validation time.
     *
     * @since 7.4.58
     * @access const
     * @var    string
     */
    const META_KEY_LAST_VALIDATED = '_rex_feed_last_validated';

    /**
     * Maximum number of errors to store per feed.
     * Increased to 500 to show more representative data.
     *
     * @since 7.4.58
     * @access const
     * @var    int
     */
    const MAX_STORED_ERRORS = 500;

    /**
     * The feed ID.
     *
     * @since 7.4.58
     * @access protected
     * @var    int
     */
    protected $feed_id;

    /**
     * Constructor.
     *
     * @since 7.4.58
     * @param int $feed_id The feed ID.
     */
    public function __construct( $feed_id ) {
        $this->feed_id = absint( $feed_id );
    }

    /**
     * Save validation results.
     *
     * @since 7.4.58
     * @access public
     * @param  array $errors  Array of error entries.
     * @param  array $summary Validation summary.
     * @return bool
     */
    public function save_results( $errors, $summary = array() ) {
        // Clear old validation results first to free memory
        $this->clear_results();
        
        // Force garbage collection before processing
        if ( function_exists( 'gc_collect_cycles' ) ) {
            gc_collect_cycles();
        }
        
        // Store total count before limiting
        $original_count = count( $errors );
        
        // Limit the number of stored errors to prevent memory issues
        if ( count( $errors ) > self::MAX_STORED_ERRORS ) {
            // Prioritize errors over warnings and info
            $error_items = array();
            $warning_items = array();
            $info_items = array();
            
            // Use foreach to categorize by severity - more memory efficient
            foreach ( $errors as $item ) {
                $severity = $item['severity'] ?? '';
                if ( $severity === 'error' ) {
                    $error_items[] = $item;
                    // Stop collecting errors once we have enough
                    if ( count( $error_items ) >= (int) ( self::MAX_STORED_ERRORS * 0.6 ) ) {
                        break;
                    }
                }
            }
            
            // Only process warnings/info if we have space left
            if ( count( $error_items ) < self::MAX_STORED_ERRORS ) {
                foreach ( $errors as $item ) {
                    $severity = $item['severity'] ?? '';
                    if ( $severity === 'warning' && count( $warning_items ) < (int) ( self::MAX_STORED_ERRORS * 0.3 ) ) {
                        $warning_items[] = $item;
                    } elseif ( $severity === 'info' && count( $info_items ) < (int) ( self::MAX_STORED_ERRORS * 0.1 ) ) {
                        $info_items[] = $item;
                    }
                    
                    // Early exit if we have enough
                    if ( count( $error_items ) + count( $warning_items ) + count( $info_items ) >= self::MAX_STORED_ERRORS ) {
                        break;
                    }
                }
            }
            
            // Clear original array to free memory immediately
            unset( $errors );
            
            // Merge limited results
            $errors = array_merge( $error_items, $warning_items, $info_items );
            
            // Free memory
            unset( $error_items, $warning_items, $info_items );
            
            // Force garbage collection to free memory immediately
            if ( function_exists( 'gc_collect_cycles' ) ) {
                gc_collect_cycles();
            }
            
            $summary['truncated'] = true;
            $summary['total_issues_found'] = $original_count;
        }

        // Ensure we have the total count in summary
        if ( ! isset( $summary['total_issues'] ) ) {
            $summary['total_issues'] = $original_count;
        }

        $result1 = update_post_meta( $this->feed_id, self::META_KEY_RESULTS, $errors );
        $result2 = update_post_meta( $this->feed_id, self::META_KEY_SUMMARY, $summary );
        $result3 = update_post_meta( $this->feed_id, self::META_KEY_LAST_VALIDATED, current_time( 'mysql' ) );
        
        if ( ! $result1 || ! $result2 ) {
            // If still too large, try with even fewer errors
            if ( count( $errors ) > 100 ) {
                $errors = array_slice( $errors, 0, 100 );
                $summary['truncated'] = true;
                $summary['truncation_reason'] = 'Database storage limit';
                $result1 = update_post_meta( $this->feed_id, self::META_KEY_RESULTS, $errors );
                $result2 = update_post_meta( $this->feed_id, self::META_KEY_SUMMARY, $summary );
            }
        }

        return $result1 && $result2 && $result3;
    }

    /**
     * Get all validation results.
     *
     * @since 7.4.58
     * @access public
     * @return array
     */
    public function get_results() {
        $results = get_post_meta( $this->feed_id, self::META_KEY_RESULTS, true );
        return is_array( $results ) ? $results : array();
    }

    /**
     * Get total number of products validated.
     * This is stored in the feed meta when feed is generated.
     *
     * @since 7.4.58
     * @access public
     * @return int
     */
    public function get_total_products_validated() {
        // Try to get from summary first
        $summary = $this->get_summary();
        if ( isset( $summary['total_products'] ) ) {
            return absint( $summary['total_products'] );
        }

        // Fallback: count from product IDs in feed
        $product_ids = get_post_meta( $this->feed_id, '_rex_feed_product_ids', true );
        if ( is_array( $product_ids ) ) {
            return count( $product_ids );
        }

        // Last fallback: count unique products in results
        $results = $this->get_results();
        $product_ids = array_unique( array_column( $results, 'product_id' ) );
        return count( $product_ids );
    }

    /**
     * Get validation summary.
     *
     * @since 7.4.58
     * @access public
     * @return array
     */
    public function get_summary() {
        $summary = get_post_meta( $this->feed_id, self::META_KEY_SUMMARY, true );
        return is_array( $summary ) ? $summary : array();
    }

    /**
     * Get filtered validation summary.
     * Calculates summary based on filtered results.
     *
     * @since 7.4.58
     * @access public
     * @param  array $filters The filters to apply.
     * @return array
     */
    public function get_filtered_summary( $filters = array() ) {
        $results = $this->get_results();

        // Apply filters if any
        if ( ! empty( $filters ) ) {
            $results = $this->apply_filters( $results, $filters );
        }

        // Track if we're showing limited results
        $total_filtered = count( $results );
        $display_limit = 500;
        $is_display_truncated = false;
        
        if ( $total_filtered > $display_limit ) {
            $is_display_truncated = true;
        }

        // Calculate summary from filtered results (up to display limit)
        $summary = array(
            'total_errors'           => 0,
            'total_warnings'         => 0,
            'total_info'             => 0,
            'is_display_truncated'   => $is_display_truncated,
            'total_filtered'         => $total_filtered,
            'display_limit'          => $display_limit,
        );

        // Only count up to display limit for summary
        $results_to_count = $is_display_truncated ? array_slice( $results, 0, $display_limit ) : $results;

        foreach ( $results_to_count as $item ) {
            $severity = $item['severity'] ?? '';
            switch ( $severity ) {
                case 'error':
                    $summary['total_errors']++;
                    break;
                case 'warning':
                    $summary['total_warnings']++;
                    break;
                case 'info':
                    $summary['total_info']++;
                    break;
            }
        }

        return $summary;
    }

    /**
     * Get last validation timestamp.
     *
     * @since 7.4.58
     * @access public
     * @return string|null
     */
    public function get_last_validated() {
        return get_post_meta( $this->feed_id, self::META_KEY_LAST_VALIDATED, true );
    }

    /**
     * Check if feed has validation results.
     *
     * @since 7.4.58
     * @access public
     * @return bool
     */
    public function has_results() {
        return ! empty( $this->get_results() );
    }

    /**
     * Check if stored results are truncated.
     *
     * @since 7.4.58
     * @access public
     * @return bool
     */
    public function is_truncated() {
        $summary = $this->get_summary();
        return ! empty( $summary['truncated'] );
    }

    /**
     * Get total issues count from full summary.
     *
     * @since 7.4.58
     * @access public
     * @return int
     */
    public function get_total_issues_count() {
        $summary = $this->get_summary();
        return isset( $summary['total_issues_found'] ) ? absint( $summary['total_issues_found'] ) : count( $this->get_results() );
    }

    /**
     * Clear validation results.
     *
     * @since 7.4.58
     * @access public
     * @return bool
     */
    public function clear_results() {
        delete_post_meta( $this->feed_id, self::META_KEY_RESULTS );
        delete_post_meta( $this->feed_id, self::META_KEY_SUMMARY );
        delete_post_meta( $this->feed_id, self::META_KEY_LAST_VALIDATED );
        return true;
    }

    /**
     * Get results filtered by severity.
     *
     * @since 7.4.58
     * @access public
     * @param  string $severity The severity level (error, warning, info).
     * @return array
     */
    public function get_results_by_severity( $severity ) {
        $results = $this->get_results();
        return array_filter( $results, function( $item ) use ( $severity ) {
            return isset( $item['severity'] ) && $item['severity'] === $severity;
        });
    }

    /**
     * Get results filtered by attribute.
     *
     * @since 7.4.58
     * @access public
     * @param  string $attribute The attribute name.
     * @return array
     */
    public function get_results_by_attribute( $attribute ) {
        $results = $this->get_results();
        return array_filter( $results, function( $item ) use ( $attribute ) {
            return isset( $item['attribute'] ) && $item['attribute'] === $attribute;
        });
    }

    /**
     * Get results filtered by product ID.
     *
     * @since 7.4.58
     * @access public
     * @param  int $product_id The product ID.
     * @return array
     */
    public function get_results_by_product( $product_id ) {
        $results = $this->get_results();
        return array_filter( $results, function( $item ) use ( $product_id ) {
            return isset( $item['product_id'] ) && absint( $item['product_id'] ) === absint( $product_id );
        });
    }

    /**
     * Get results filtered by rule.
     *
     * @since 7.4.58
     * @access public
     * @param  string $rule The rule name.
     * @return array
     */
    public function get_results_by_rule( $rule ) {
        $results = $this->get_results();
        return array_filter( $results, function( $item ) use ( $rule ) {
            return isset( $item['rule'] ) && $item['rule'] === $rule;
        });
    }

    /**
     * Get paginated results.
     *
     * @since 7.4.58
     * @access public
     * @param  int   $page     Page number (1-indexed).
     * @param  int   $per_page Items per page.
     * @param  array $filters  Optional filters (severity, attribute, product_id, rule).
     * @return array
     */
    public function get_paginated_results( $page = 1, $per_page = 50, $filters = array() ) {
        $results = $this->get_results();

        // Apply filters
        if ( ! empty( $filters ) ) {
            $results = $this->apply_filters( $results, $filters );
        }

        $total_before_limit = count( $results );
        $display_limit = 500;
        $is_display_truncated = false;
        
        // Limit display to maximum 500 items for performance
        if ( $total_before_limit > $display_limit ) {
            $results = array_slice( $results, 0, $display_limit );
            $is_display_truncated = true;
        }

        $total    = count( $results );
        $offset   = ( $page - 1 ) * $per_page;
        $items    = array_slice( $results, $offset, $per_page );

        return array(
            'items'                  => $items,
            'total'                  => $total,
            'page'                   => $page,
            'per_page'               => $per_page,
            'total_pages'            => ceil( $total / $per_page ),
            'is_display_truncated'   => $is_display_truncated,
            'total_before_limit'     => $total_before_limit,
            'display_limit'          => $display_limit,
        );
    }

    /**
     * Apply filters to results.
     *
     * @since 7.4.58
     * @access protected
     * @param  array $results The results to filter.
     * @param  array $filters The filters to apply.
     * @return array
     */
    protected function apply_filters( $results, $filters ) {
        if ( ! empty( $filters['severity'] ) ) {
            $severity = $filters['severity'];
            $results = array_filter( $results, function( $item ) use ( $severity ) {
                return isset( $item['severity'] ) && $item['severity'] === $severity;
            });
        }

        if ( ! empty( $filters['attribute'] ) ) {
            $attribute = $filters['attribute'];
            $results = array_filter( $results, function( $item ) use ( $attribute ) {
                return isset( $item['attribute'] ) && $item['attribute'] === $attribute;
            });
        }

        if ( ! empty( $filters['product_id'] ) ) {
            $product_id = absint( $filters['product_id'] );
            $results = array_filter( $results, function( $item ) use ( $product_id ) {
                return isset( $item['product_id'] ) && absint( $item['product_id'] ) === $product_id;
            });
        }

        if ( ! empty( $filters['rule'] ) ) {
            $rule = $filters['rule'];
            $results = array_filter( $results, function( $item ) use ( $rule ) {
                return isset( $item['rule'] ) && $item['rule'] === $rule;
            });
        }

        if ( ! empty( $filters['search'] ) ) {
            $search = strtolower( $filters['search'] );
            $results = array_filter( $results, function( $item ) use ( $search ) {
                $searchable = strtolower(
                    ( $item['product_title'] ?? '' ) . ' ' .
                    ( $item['attribute'] ?? '' ) . ' ' .
                    ( $item['message'] ?? '' ) . ' ' .
                    ( $item['raw_value'] ?? '' )
                );
                return strpos( $searchable, $search ) !== false;
            });
        }

        return array_values( $results );
    }

    /**
     * Get unique attributes with error counts.
     *
     * @since 7.4.58
     * @access public
     * @return array
     */
    public function get_attribute_summary() {
        $results    = $this->get_results();
        $attributes = array();

        foreach ( $results as $item ) {
            $attr = $item['attribute'] ?? 'unknown';
            if ( ! isset( $attributes[ $attr ] ) ) {
                $attributes[ $attr ] = array(
                    'attribute' => $attr,
                    'total'     => 0,
                    'error'     => 0,
                    'warning'   => 0,
                    'info'      => 0,
                );
            }

            $attributes[ $attr ]['total']++;
            $severity = $item['severity'] ?? 'info';
            if ( isset( $attributes[ $attr ][ $severity ] ) ) {
                $attributes[ $attr ][ $severity ]++;
            }
        }

        // Sort by total descending
        uasort( $attributes, function( $a, $b ) {
            return $b['total'] - $a['total'];
        });

        return array_values( $attributes );
    }

    /**
     * Get unique rules with error counts.
     *
     * @since 7.4.58
     * @access public
     * @return array
     */
    public function get_rule_summary() {
        $results = $this->get_results();
        $rules   = array();

        foreach ( $results as $item ) {
            $rule = $item['rule'] ?? 'unknown';
            if ( ! isset( $rules[ $rule ] ) ) {
                $rules[ $rule ] = array(
                    'rule'     => $rule,
                    'total'    => 0,
                    'error'    => 0,
                    'warning'  => 0,
                    'info'     => 0,
                );
            }

            $rules[ $rule ]['total']++;
            $severity = $item['severity'] ?? 'info';
            if ( isset( $rules[ $rule ][ $severity ] ) ) {
                $rules[ $rule ][ $severity ]++;
            }
        }

        // Sort by total descending
        uasort( $rules, function( $a, $b ) {
            return $b['total'] - $a['total'];
        });

        return array_values( $rules );
    }

    /**
     * Get products with most issues.
     *
     * @since 7.4.58
     * @access public
     * @param  int $limit Maximum number of products to return.
     * @return array
     */
    public function get_top_problematic_products( $limit = 10 ) {
        $results  = $this->get_results();
        $products = array();

        foreach ( $results as $item ) {
            $product_id = $item['product_id'] ?? 0;
            if ( ! $product_id ) {
                continue;
            }

            if ( ! isset( $products[ $product_id ] ) ) {
                $products[ $product_id ] = array(
                    'product_id'    => $product_id,
                    'product_title' => $item['product_title'] ?? '',
                    'total'         => 0,
                    'error'         => 0,
                    'warning'       => 0,
                    'info'          => 0,
                );
            }

            $products[ $product_id ]['total']++;
            $severity = $item['severity'] ?? 'info';
            if ( isset( $products[ $product_id ][ $severity ] ) ) {
                $products[ $product_id ][ $severity ]++;
            }
        }

        // Sort by error count first, then warning, then total
        uasort( $products, function( $a, $b ) {
            if ( $a['error'] !== $b['error'] ) {
                return $b['error'] - $a['error'];
            }
            if ( $a['warning'] !== $b['warning'] ) {
                return $b['warning'] - $a['warning'];
            }
            return $b['total'] - $a['total'];
        });

        return array_slice( array_values( $products ), 0, $limit );
    }

    /**
     * Export results to CSV.
     *
     * @since 7.4.58
     * @access public
     * @param  array $filters Optional filters to apply.
     * @return string CSV content.
     */
    public function export_to_csv( $filters = array() ) {
        $results = $this->get_results();

        if ( ! empty( $filters ) ) {
            $results = $this->apply_filters( $results, $filters );
        }

        $csv_lines = array();

        // Header
        $csv_lines[] = array(
            'Product ID',
            'Product Title',
            'Attribute',
            'Rule',
            'Severity',
            'Raw Value',
            'Message',
            'Timestamp',
        );

        // Data rows
        foreach ( $results as $item ) {
            $csv_lines[] = array(
                $item['product_id'] ?? '',
                $item['product_title'] ?? '',
                $item['attribute'] ?? '',
                $item['rule'] ?? '',
                $item['severity'] ?? '',
                $item['raw_value'] ?? '',
                $item['message'] ?? '',
                $item['timestamp'] ?? '',
            );
        }

        // Convert to CSV string
        $output = '';
        foreach ( $csv_lines as $line ) {
            $output .= $this->array_to_csv_line( $line ) . "\n";
        }

        return $output;
    }

    /**
     * Convert array to CSV line.
     *
     * @since 7.4.58
     * @access protected
     * @param  array $array The array to convert.
     * @return string
     */
    protected function array_to_csv_line( $array ) {
        $escaped = array_map( function( $value ) {
            $value = str_replace( '"', '""', $value );
            if ( strpos( $value, ',' ) !== false || strpos( $value, '"' ) !== false || strpos( $value, "\n" ) !== false ) {
                $value = '"' . $value . '"';
            }
            return $value;
        }, $array );

        return implode( ',', $escaped );
    }

    /**
     * Export results to JSON.
     *
     * @since 7.4.58
     * @access public
     * @param  array $filters Optional filters to apply.
     * @return string JSON content.
     */
    public function export_to_json( $filters = array() ) {
        $results = $this->get_results();
        $summary = $this->get_summary();

        if ( ! empty( $filters ) ) {
            $results = $this->apply_filters( $results, $filters );
        }

        return wp_json_encode( array(
            'feed_id'        => $this->feed_id,
            'summary'        => $summary,
            'last_validated' => $this->get_last_validated(),
            'results'        => $results,
        ), JSON_PRETTY_PRINT );
    }
}