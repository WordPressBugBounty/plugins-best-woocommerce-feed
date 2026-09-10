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
     * Issue weights used by feed health calculations.
     *
     * @since 7.4.58
     * @access const
     * @var array
     */
    const HEALTH_ISSUE_WEIGHTS = array(
        'error'   => 10,
        'warning' => 3,
        'info'    => 1,
    );

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
     * Per-feed validation settings and error-product index.
     *
     * @since 7.4.58
     */
    const META_KEY_VALIDATION_DISABLED = '_rex_feed_validation_disabled';
    const META_KEY_EXCLUDE_ERRORS      = '_rex_feed_exclude_error_products';
    const META_KEY_ERROR_PRODUCT_IDS   = '_rex_feed_validation_error_product_ids';

    /**
     * The feed ID.
     *
     * @since 7.4.58
     * @access protected
     * @var    int
     */
    protected $feed_id;

    /**
     * In-memory cache for results and summary to avoid redundant DB queries and recursion.
     *
     * @since 7.4.58
     * @access protected
     */
    protected $cached_results = null;
    protected $cached_summary = null;
    protected $is_computing_summary = false;

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
     * Check whether validation is disabled for this feed.
     *
     * @since 7.4.58
     * @return bool
     */
    public function is_validation_disabled() {
        return 'yes' === get_post_meta( $this->feed_id, self::META_KEY_VALIDATION_DISABLED, true );
    }

    /**
     * Check whether products with validation errors should be excluded.
     * The option defaults to enabled to match the validation menu's initial state.
     *
     * @since 7.4.58
     * @return bool
     */
    public function is_error_product_exclusion_enabled() {
        $value = get_post_meta( $this->feed_id, self::META_KEY_EXCLUDE_ERRORS, true );

        return 'yes' === $value;
    }

    /**
     * Get every unique product ID affected by an error.
     *
     * @since 7.4.58
     * @return int[]
     */
    public function get_error_product_ids() {
        $product_ids = get_post_meta( $this->feed_id, self::META_KEY_ERROR_PRODUCT_IDS, true );

        if ( ! is_array( $product_ids ) ) {
            $product_ids = self::extract_error_product_ids( $this->get_results() );
        }

        return array_values( array_unique( array_filter( array_map( 'absint', $product_ids ) ) ) );
    }

    /**
     * Extract unique error-product IDs from validation issues.
     *
     * @since 7.4.58
     * @param  array $issues Validation issues.
     * @return int[]
     */
    public static function extract_error_product_ids( $issues ) {
        $product_ids = array();

        foreach ( (array) $issues as $issue ) {
            if ( 'error' !== ( $issue['severity'] ?? '' ) ) {
                continue;
            }

            $product_id = absint( $issue['product_id'] ?? 0 );
            if ( $product_id ) {
                $product_ids[ $product_id ] = $product_id;
            }
        }

        return array_values( $product_ids );
    }

    /**
     * Calculate one product's health from all of its validation issues.
     *
     * @since 7.4.58
     * @access public
     * @param  array $issues Validation issues for one product.
     * @return int Product health from 0 to 100.
     */
    public static function calculate_product_health( $issues ) {
        $issue_score = 0;

        foreach ( (array) $issues as $issue ) {
            $severity = $issue['severity'] ?? '';

            if ( isset( self::HEALTH_ISSUE_WEIGHTS[ $severity ] ) ) {
                $issue_score += self::HEALTH_ISSUE_WEIGHTS[ $severity ];
            }
        }

        return max( 0, 100 - $issue_score );
    }

    /**
     * Calculate feed health by averaging every validated product's health,
     * then applying an error penalty based on the percentage of unique products with >= 1 error.
     *
     * Error Penalty (%) = (Number of Unique Products with >= 1 Error / Total Number of Products) * 100
     * Final Feed Health = MAX(0, Current Feed Health - Error Penalty)
     *
     * @since 7.4.58
     * @access public
     * @param  array          $issues            Validation issues for the feed.
     * @param  int            $total_products    Total number of validated products.
     * @param  array|int|null $error_product_ids Optional unique error-product IDs or count.
     * @return int Feed health from 0 to 100.
     */
    public static function calculate_feed_health( $issues, $total_products, $error_product_ids = null ) {
        $product_issue_scores     = array();
        $unique_error_product_ids = array();

        foreach ( (array) $issues as $issue ) {
            $product_id = absint( $issue['product_id'] ?? 0 );
            $severity   = strtolower( (string) ( $issue['severity'] ?? '' ) );

            if ( ! $product_id ) {
                continue;
            }

            if ( 'error' === $severity ) {
                $unique_error_product_ids[ $product_id ] = true;
            }

            if ( ! isset( self::HEALTH_ISSUE_WEIGHTS[ $severity ] ) ) {
                continue;
            }

            if ( ! isset( $product_issue_scores[ $product_id ] ) ) {
                $product_issue_scores[ $product_id ] = 0;
            }

            $product_issue_scores[ $product_id ] += self::HEALTH_ISSUE_WEIGHTS[ $severity ];
        }

        $total_products = max( absint( $total_products ), count( $product_issue_scores ) );

        if ( 0 === $total_products ) {
            return 100;
        }

        $total_health = ( $total_products - count( $product_issue_scores ) ) * 100;

        foreach ( $product_issue_scores as $issue_score ) {
            $total_health += max( 0, 100 - $issue_score );
        }

        // Severity-based current feed health
        $current_feed_health = $total_health / $total_products;

        // Resolve unique products with >= 1 error
        if ( is_array( $error_product_ids ) ) {
            $error_product_count = count( array_unique( array_filter( array_map( 'absint', $error_product_ids ) ) ) );
        } elseif ( is_numeric( $error_product_ids ) ) {
            $error_product_count = absint( $error_product_ids );
        } else {
            $error_product_count = count( $unique_error_product_ids );
        }

        // Error Penalty (%) = (Number of Unique Products with >= 1 Error / Total Number of Products) * 100
        $error_penalty = ( $error_product_count / $total_products ) * 100;

        // Final Feed Health = MAX(0, Current Feed Health - Error Penalty)
        $final_feed_health = max( 0, $current_feed_health - $error_penalty );

        return max( 0, min( 100, (int) round( $final_feed_health ) ) );
    }

    /**
     * Save validation results.
     *
     * @since 7.4.58
     * @access public
     * @param  array $errors  Array of error entries.
     * @param  array $summary Validation summary.
     * @param  int[] $error_product_ids Complete unique error-product IDs.
     * @return bool
     */
    public function save_results( $errors, $summary = array(), $error_product_ids = null ) {
        // Clear old validation results first to free memory
        $this->clear_results();
        
        // Force garbage collection before processing
        if ( function_exists( 'gc_collect_cycles' ) ) {
            gc_collect_cycles();
        }
        
        // Store every issue. Pagination is applied only when results are read.
        $original_count = count( $errors );
        $summary['total_issues'] = $original_count;
        unset( $summary['truncated'], $summary['total_issues_found'], $summary['truncation_reason'] );

        $this->cached_results       = $errors;
        $this->cached_summary       = null;
        $this->is_computing_summary = false;

        $result1 = update_post_meta( $this->feed_id, self::META_KEY_RESULTS, $errors );

        // Calculate cumulative affected product counts for summary
        $cumulative = $this->get_cumulative_summary();
        $summary['total_errors']   = $cumulative['total_errors'];
        $summary['total_warnings'] = $cumulative['total_warnings'];
        $summary['total_info']     = $cumulative['total_info'];
        $summary['total_issues']   = $cumulative['total_issues'];
        $this->cached_summary      = $summary;

        $result2 = update_post_meta( $this->feed_id, self::META_KEY_SUMMARY, $summary );
        $result3 = update_post_meta( $this->feed_id, self::META_KEY_LAST_VALIDATED, current_time( 'mysql' ) );
        $error_product_ids = is_array( $error_product_ids )
            ? $error_product_ids
            : self::extract_error_product_ids( $errors );
        update_post_meta(
            $this->feed_id,
            self::META_KEY_ERROR_PRODUCT_IDS,
            array_values( array_unique( array_filter( array_map( 'absint', $error_product_ids ) ) ) )
        );

        if ( class_exists( 'Rex_Feed_Quick_Fix' ) ) {
            Rex_Feed_Quick_Fix::prune_resolved_attempts( $this->feed_id, $errors );
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
        if ( null !== $this->cached_results ) {
            return $this->cached_results;
        }
        $results = get_post_meta( $this->feed_id, self::META_KEY_RESULTS, true );
        $this->cached_results = is_array( $results ) ? $results : array();
        return $this->cached_results;
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
        $raw_summary = get_post_meta( $this->feed_id, self::META_KEY_SUMMARY, true );
        if ( is_array( $raw_summary ) && isset( $raw_summary['total_products'] ) ) {
            return absint( $raw_summary['total_products'] );
        }

        // Fallback: count from product IDs in feed
        $product_ids = get_post_meta( $this->feed_id, '_rex_feed_product_ids', true );
        if ( is_array( $product_ids ) ) {
            return count( $product_ids );
        }

        return 0;
    }

    /**
     * Get validation summary.
     *
     * @since 7.4.58
     * @access public
     * @return array
     */
    public function get_summary() {
        if ( null !== $this->cached_summary ) {
            return $this->cached_summary;
        }

        $summary = get_post_meta( $this->feed_id, self::META_KEY_SUMMARY, true );
        if ( ! is_array( $summary ) ) {
            $summary = array();
        }

        if ( ! $this->is_computing_summary && $this->has_results() ) {
            $this->is_computing_summary = true;
            $cumulative = $this->get_cumulative_summary();
            $summary['total_errors']   = $cumulative['total_errors'];
            $summary['total_warnings'] = $cumulative['total_warnings'];
            $summary['total_info']     = $cumulative['total_info'];
            $summary['total_issues']   = $cumulative['total_issues'];
            $this->is_computing_summary = false;
        }

        // Apply error penalty to health_score if not already applied
        if ( isset( $summary['health_score'] ) && ! isset( $summary['error_penalty'] ) ) {
            $total_products = absint( $summary['total_products'] ?? $this->get_total_products_validated() );
            if ( $total_products > 0 ) {
                $error_product_ids   = $this->get_error_product_ids();
                $error_product_count = count( $error_product_ids );
                $error_penalty       = ( $error_product_count / $total_products ) * 100;
                $raw_health_score    = (float) ( $summary['raw_health_score'] ?? $summary['health_score'] );
                $summary['raw_health_score'] = $raw_health_score;
                $summary['error_penalty']    = $error_penalty;
                $summary['health_score']     = max( 0, min( 100, (int) round( max( 0, $raw_health_score - $error_penalty ) ) ) );
            }
        }

        $this->cached_summary = $summary;
        return $this->cached_summary;
    }

    /**
     * Calculate cumulative affected product summary from grouped results.
     *
     * @since 7.4.58
     * @param  array $filters Optional filters.
     * @return array
     */
    public function get_cumulative_summary( $filters = array() ) {
        $results = $this->get_results();

        if ( ! empty( $filters ) ) {
            $results = $this->apply_filters( $results, $filters );
        }

        $grouped = $this->group_results_by_attribute( $results );

        $summary = array(
            'total_errors'   => 0,
            'total_warnings' => 0,
            'total_info'     => 0,
            'total_issues'   => 0,
        );

        foreach ( $grouped as $group ) {
            $count    = absint( $group['product_count'] ?? 0 );
            $severity = strtolower( (string) ( $group['severity'] ?? '' ) );

            switch ( $severity ) {
                case 'error':
                    $summary['total_errors'] += $count;
                    break;
                case 'warning':
                    $summary['total_warnings'] += $count;
                    break;
                case 'info':
                    $summary['total_info'] += $count;
                    break;
            }
        }

        $summary['total_issues'] = $summary['total_errors'] + $summary['total_warnings'] + $summary['total_info'];

        return $summary;
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

        $total_filtered = count( $results );
        $cumulative     = $this->get_cumulative_summary( $filters );

        $summary = array(
            'total_errors'         => $cumulative['total_errors'],
            'total_warnings'       => $cumulative['total_warnings'],
            'total_info'           => $cumulative['total_info'],
            'total_issues'         => $cumulative['total_issues'],
            'is_display_truncated' => false,
            'total_filtered'       => $total_filtered,
            'display_limit'        => $total_filtered,
        );

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
        $this->cached_results       = null;
        $this->cached_summary       = null;
        $this->is_computing_summary = false;
        delete_post_meta( $this->feed_id, self::META_KEY_RESULTS );
        delete_post_meta( $this->feed_id, self::META_KEY_SUMMARY );
        delete_post_meta( $this->feed_id, self::META_KEY_LAST_VALIDATED );
        delete_post_meta( $this->feed_id, self::META_KEY_ERROR_PRODUCT_IDS );
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
     * Group product-level issues by severity, attribute, and validator rule.
     *
     * @since 7.4.58
     * @access protected
     * @param  array $results Product-level validation results.
     * @return array Issue-grouped validation results.
     */
    protected function group_results_by_attribute( $results ) {
        $groups = array();

        foreach ( (array) $results as $item ) {
            $attribute = trim( (string) ( $item['attribute'] ?? '' ) );
            $attribute = '' !== $attribute ? $attribute : 'General';
            $severity  = $item['severity'] ?? 'info';
            $rule      = trim( (string) ( $item['rule'] ?? 'general' ) );
            $group_key = strtolower( $severity . '|' . $attribute . '|' . $rule );

            if ( ! isset( $groups[ $group_key ] ) ) {
                $groups[ $group_key ] = $item;
                $groups[ $group_key ]['attribute']    = $attribute;
                $groups[ $group_key ]['product_count'] = 0;
                $groups[ $group_key ]['issue_count']   = 0;
                $groups[ $group_key ]['_product_ids']  = array();
                $groups[ $group_key ]['raw_value']     = '';
            }

            $product_id = absint( $item['product_id'] ?? 0 );

            if ( $product_id ) {
                $groups[ $group_key ]['_product_ids'][ $product_id ] = true;
            } else {
                $groups[ $group_key ]['_is_feed_level'] = true;
            }

            $groups[ $group_key ]['issue_count']++;
        }

        $total_feed_products = $this->get_total_products_validated();

        foreach ( $groups as &$group ) {
            if ( ! empty( $group['_is_feed_level'] ) && empty( $group['_product_ids'] ) ) {
                $group['product_count'] = $total_feed_products;
            } else {
                $group['product_count'] = count( $group['_product_ids'] );
            }
            unset( $group['_product_ids'], $group['_is_feed_level'] );
        }
        unset( $group );

        return array_values( $groups );
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

        $results = $this->group_results_by_attribute( $results );

        $total    = count( $results );
        $offset   = ( $page - 1 ) * $per_page;
        $items    = array_slice( $results, $offset, $per_page );

        return array(
            'items'                  => $items,
            'total'                  => $total,
            'page'                   => $page,
            'per_page'               => $per_page,
            'total_pages'            => ceil( $total / $per_page ),
            'is_display_truncated'   => false,
            'total_before_limit'     => $total,
            'display_limit'          => $total,
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
        $grouped    = $this->group_results_by_attribute( $results );
        $attributes = array();

        foreach ( $grouped as $item ) {
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

            $count    = absint( $item['product_count'] ?? 0 );
            $severity = strtolower( (string) ( $item['severity'] ?? 'info' ) );

            $attributes[ $attr ]['total'] += $count;
            if ( isset( $attributes[ $attr ][ $severity ] ) ) {
                $attributes[ $attr ][ $severity ] += $count;
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
     * Get affected products for a specific issue.
     *
     * @since 7.4.58
     * @access public
     * @param string $attribute Attribute name.
     * @param string $rule Rule name.
     * @param string $severity Severity level.
     * @param int $page Page number.
     * @param int $per_page Products per page.
     * @return array
     */
    public function get_issue_products( $attribute = '', $rule = '', $severity = '', $page = 1, $per_page = 10 ) {
        $results = $this->get_results();
        $target_attr = strtolower( trim( (string) $attribute ) );
        $target_rule = strtolower( trim( (string) $rule ) );
        $target_sev  = strtolower( trim( (string) $severity ) );

        $products = array();
        $seen = array();
        $is_feed_level = false;

        foreach ( $results as $item ) {
            $item_attr = strtolower( trim( (string) ( $item['attribute'] ?? '' ) ) );
            $item_rule = strtolower( trim( (string) ( $item['rule'] ?? 'general' ) ) );
            $item_sev  = strtolower( trim( (string) ( $item['severity'] ?? 'info' ) ) );

            $match_attr = ( '' === $target_attr || $item_attr === $target_attr );
            $match_rule = ( '' === $target_rule || $item_rule === $target_rule );
            $match_sev  = ( '' === $target_sev || $item_sev === $target_sev );

            if ( $match_attr && $match_rule && $match_sev ) {
                $product_id = absint( $item['product_id'] ?? 0 );
                if ( $product_id ) {
                    if ( ! isset( $seen[ $product_id ] ) ) {
                        $seen[ $product_id ] = true;
                        $products[] = $this->format_issue_product( $product_id, $item['product_title'] ?? '' );
                    }
                } else {
                    $is_feed_level = true;
                }
            }
        }

        // If feed level and no specific product items found, fallback to feed products
        if ( empty( $products ) && $is_feed_level ) {
            $feed_product_ids = get_post_meta( $this->feed_id, '_rex_feed_product_ids', true );
            if ( is_array( $feed_product_ids ) ) {
                foreach ( $feed_product_ids as $pid ) {
                    $pid = absint( $pid );
                    if ( $pid && ! isset( $seen[ $pid ] ) ) {
                        $seen[ $pid ] = true;
                        $products[] = $this->format_issue_product( $pid );
                    }
                }
            }
        }

        // Max error product data shown capped at 500
        $max_error_products = 500;
        if ( count( $products ) > $max_error_products ) {
            $products = array_slice( $products, 0, $max_error_products );
        }

        $total = count( $products );
        $page = max( 1, absint( $page ) );
        $per_page = max( 1, absint( $per_page ) );
        $total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;
        if ( $page > $total_pages ) {
            $page = $total_pages;
        }
        $offset = ( $page - 1 ) * $per_page;
        $paged_products = array_slice( $products, $offset, $per_page );

        return array(
            'products'    => $paged_products,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $per_page,
            'total_pages' => $total_pages,
        );
    }

    /**
     * Format product details for issue products list.
     *
     * Resolves variation name/attributes, enforces 70-character cap,
     * and sets edit URL.
     *
     * @since 7.4.58
     * @access private
     * @param int    $product_id Product or variation ID.
     * @param string $initial_title Stored product title if available.
     * @return array Product information array.
     */
    private function format_issue_product( $product_id, $initial_title = '' ) {
        $wc_product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
        $title = ! empty( $initial_title ) ? $initial_title : get_the_title( $product_id );
        $target_edit_id = $product_id;

        if ( $wc_product && $wc_product->is_type( 'variation' ) ) {
            $parent_id = $wc_product->get_parent_id();
            if ( $parent_id ) {
                $target_edit_id = $parent_id;
            }

            // Retrieve variation attributes string
            $variation_str = '';
            if ( function_exists( 'wc_get_formatted_variation' ) ) {
                $variation_str = wc_get_formatted_variation( $wc_product, true );
            }
            if ( empty( $variation_str ) && method_exists( $wc_product, 'get_attribute_summary' ) ) {
                $variation_str = $wc_product->get_attribute_summary();
            }
            if ( empty( $variation_str ) ) {
                $var_attrs = $wc_product->get_variation_attributes();
                if ( ! empty( $var_attrs ) && is_array( $var_attrs ) ) {
                    $attr_parts = array();
                    foreach ( $var_attrs as $attr_key => $attr_val ) {
                        if ( '' !== $attr_val ) {
                            $clean_label = function_exists( 'wc_attribute_label' )
                                ? wc_attribute_label( str_replace( 'attribute_', '', $attr_key ), $wc_product )
                                : str_replace( 'attribute_', '', $attr_key );
                            $attr_parts[] = $clean_label . ': ' . $attr_val;
                        }
                    }
                    $variation_str = implode( ', ', $attr_parts );
                }
            }

            $variation_str = trim( wp_strip_all_tags( (string) $variation_str ) );

            $parent_title = $parent_id ? get_the_title( $parent_id ) : '';
            $base_title   = ! empty( $parent_title ) ? $parent_title : $title;
            if ( empty( $base_title ) ) {
                $base_title = sprintf( __( 'Product #%d', 'rex-product-feed' ), $parent_id ? $parent_id : $product_id );
            }

            if ( ! empty( $variation_str ) ) {
                if ( false === strpos( $base_title, $variation_str ) ) {
                    $title = $base_title . ' - ' . $variation_str;
                } else {
                    $title = $base_title;
                }
            } else {
                $wc_name = $wc_product->get_name();
                if ( ! empty( $wc_name ) && $wc_name !== $base_title ) {
                    $title = $wc_name;
                } else {
                    $title = sprintf( '%s - #%d', $base_title, $product_id );
                }
            }
        } elseif ( $wc_product ) {
            $wc_name = $wc_product->get_name();
            if ( ! empty( $wc_name ) ) {
                $title = $wc_name;
            }
        }

        if ( empty( $title ) ) {
            $title = sprintf( __( 'Product #%d', 'rex-product-feed' ), $product_id );
        }

        $full_title = $title;

        // Cap character length at most 70 characters
        if ( function_exists( 'mb_strimwidth' ) ) {
            if ( mb_strlen( $title, 'UTF-8' ) > 70 ) {
                $title = mb_strimwidth( $title, 0, 70, '...', 'UTF-8' );
            }
        } elseif ( strlen( $title ) > 70 ) {
            $title = substr( $title, 0, 67 ) . '...';
        }

        $edit_url = get_edit_post_link( $target_edit_id, 'raw' );
        if ( ! $edit_url ) {
            $edit_url = admin_url( 'post.php?post=' . $target_edit_id . '&action=edit' );
        }

        $view_url = get_permalink( $product_id );
        if ( ! $view_url ) {
            $view_url = $edit_url;
        }

        return array(
            'id'         => $product_id,
            'title'      => $title,
            'full_title' => $full_title,
            'edit_url'   => $edit_url,
            'view_url'   => $edit_url,
        );
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
