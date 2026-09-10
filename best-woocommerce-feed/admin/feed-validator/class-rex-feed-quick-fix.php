<?php
/**
 * Feed validation quick-fix suggestions.
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
 * Finds and stores the next confident replacement attribute for one issue.
 */
class Rex_Feed_Quick_Fix {

    /** Persistent candidate history, separated by feed and issue type. */
    const META_KEY_ATTEMPTS = '_rex_feed_validation_quick_fix_attempts';

    /** Minimum accepted semantic match. */
    const DEFAULT_CONFIDENCE_THRESHOLD = 0.75;

    /**
     * Find next candidate and save its Feed Rule without generating feed.
     *
     * @param int    $feed_id   Feed ID.
     * @param string $attribute Affected feed attribute.
     * @param string $rule      Validator rule.
     * @param string $severity  Validator severity.
     * @return array|WP_Error
     */
    public function suggest_and_save( $feed_id, $attribute, $rule, $severity ) {
        $issue = $this->find_issue( $feed_id, $attribute, $rule, $severity );

        if ( ! $issue ) {
            return new WP_Error( 'issue_missing', __( 'This validation issue is no longer available.', 'rex-product-feed' ) );
        }

        $condition = $this->get_condition_for_issue( $issue );
        $target    = $this->resolve_rule_target( $feed_id, $attribute );

        $is_anomaly = preg_match( '/wrongly_assigned|wrong_assigned|anomaly|invalid_mapping/', (string) $rule )
            || $this->is_feed_mapping_anomaly( $feed_id, $attribute );

        if ( $is_anomaly ) {
            $candidate = $this->find_best_candidate_for_mapping( $attribute, $feed_id, $issue );

            return array(
                'found'              => false, // Always open modal (no in-place auto fix)
                'is_mapping_anomaly' => true,
                'attribute'          => $attribute,
                'attribute_label'    => $this->format_label( $attribute ),
                'target_attribute'   => $target['value'] ?? $attribute,
                'target_label'       => $target['label'] ?? $this->format_label( $attribute ),
                'rule'               => $rule,
                'severity'           => $severity,
                'condition'          => 'any',
                'condition_label'    => __( 'Is Any', 'rex-product-feed' ),
                'find'               => '',
                'suggested_replace'  => $candidate ? $candidate['value'] : '',
                'suggested_label'    => $candidate ? $candidate['label'] : '',
                'confidence'         => $candidate ? (int) round( $candidate['score'] * 100 ) : 0,
                'manual_message'     => sprintf(
                    /* translators: 1: attribute name, 2: current assigned value, 3: suggested replacement */
                    __( 'Attribute "%1$s" is currently assigned to "%2$s". We suggest updating your feed mapping to "%3$s".', 'rex-product-feed' ),
                    $this->format_label( $attribute ),
                    $target['label'] ?: ( $target['value'] ?: $this->format_label( $attribute ) ),
                    $candidate ? $candidate['label'] : $this->format_label( $attribute )
                ),
            );
        }

        if ( ! $target['supported'] ) {
            return array(
                'found'            => false,
                'attribute'        => $attribute,
                'target_attribute' => '',
                'target_label'     => '',
                'manual_message'   => __( 'This output attribute has no mapped source that Feed Rules can safely target. Update its feed mapping manually.', 'rex-product-feed' ),
            );
        }

        if ( ! $condition['automatic'] ) {
            return array(
                'found'            => false,
                'attribute'        => $attribute,
                'target_attribute' => $target['value'],
                'target_label'     => $target['label'],
                'manual_message'   => __( 'No safe automatic condition is available for this issue type. Manual configuration is enabled.', 'rex-product-feed' ),
            );
        }

        $candidate = $this->find_best_candidate( $attribute, $feed_id, $issue, $target['value'] );

        if ( ! $candidate ) {
            return array(
                'found'              => false,
                'is_mapping_anomaly' => false,
                'attribute'          => $attribute,
                'attribute_label'    => $this->format_label( $attribute ),
                'target_attribute'   => $target['value'],
                'target_label'       => $target['label'],
                'rule'               => $rule,
                'severity'           => $severity,
                'condition'          => $condition['condition'],
                'condition_label'    => $condition['label'],
                'find'               => $condition['find'],
                'manual_message'     => __( 'No probable attribute met the confidence threshold. Manual configuration is enabled.', 'rex-product-feed' ),
            );
        }

        return array(
            'found'              => false, // Always open modal (no in-place auto fix)
            'is_mapping_anomaly' => false,
            'attribute'          => $attribute,
            'attribute_label'    => $this->format_label( $attribute ),
            'target_attribute'   => $target['value'],
            'target_label'       => $target['label'],
            'rule'               => $rule,
            'severity'           => $severity,
            'condition'          => $condition['condition'],
            'condition_label'    => $condition['label'],
            'find'               => $condition['find'],
            'candidate'          => $candidate['value'],
            'candidate_label'    => $candidate['label'],
            'suggested_replace'  => $candidate['value'],
            'suggested_label'    => $candidate['label'],
            'confidence'         => (int) round( $candidate['score'] * 100 ),
            'manual_message'     => '',
        );
    }

    /**
     * Resolve merchant output attribute to source key used by Feed Rules.
     *
     * @param int    $feed_id   Feed ID.
     * @param string $attribute Merchant output attribute.
     * @return array{value:string,label:string,supported:bool}
     */
    public function resolve_rule_target( $feed_id, $attribute ) {
        $feed_config = get_post_meta( $feed_id, '_rex_feed_feed_config', true );

        if ( ! is_array( $feed_config ) ) {
            $feed_config = get_post_meta( $feed_id, 'rex_feed_feed_config', true );
        }

        $target    = $attribute;
        $supported = false;

        foreach ( (array) $feed_config as $config ) {
            if ( ! is_array( $config ) ) {
                continue;
            }

            $config_attr = (string) ( $config['attr'] ?? '' );
            $matches     = ( $attribute === $config_attr )
                || ( preg_replace( '/^g:/i', '', $attribute ) === preg_replace( '/^g:/i', '', $config_attr ) );

            if ( ! $matches ) {
                continue;
            }

            if ( 'meta' === (string) ( $config['type'] ?? '' ) && ! empty( $config['meta_key'] ) ) {
                $target    = (string) $config['meta_key'];
                $supported = true;
            }
            break;
        }

        return array(
            'value'     => $target,
            'label'     => $this->get_attribute_label( $target ),
            'supported' => $supported,
        );
    }

    /**
     * Add or replace the single validation Feed Rule for an attribute.
     *
     * @param int   $feed_id  Feed ID.
     * @param array $new_rule Normalized Feed Rule.
     * @return void
     */
    public static function upsert_feed_rule( $feed_id, $new_rule ) {
        $attribute             = isset( $new_rule['rules_if'] ) ? (string) $new_rule['rules_if'] : '';
        $new_rule['quick_fix'] = true;
        $new_rule['source']    = 'validation_quick_fix';
        $stored_rules          = self::get_stored_rules( $feed_id );
        $updated               = array();
        $replaced              = false;

        foreach ( $stored_rules as $stored_rule ) {
            if ( ! is_array( $stored_rule ) ) {
                continue;
            }

            $is_quick_fix   = ! empty( $stored_rule['quick_fix'] )
                || 'validation_quick_fix' === ( $stored_rule['source'] ?? '' );
            $same_attribute = $is_quick_fix
                && $attribute === ( $stored_rule['rules_if'] ?? '' )
                && $attribute === ( $stored_rule['rules_then'] ?? '' );

            if ( $same_attribute ) {
                if ( ! $replaced ) {
                    $updated[] = $new_rule;
                    $replaced  = true;
                }
                continue;
            }

            $updated[] = $stored_rule;
        }

        if ( ! $replaced ) {
            $updated[] = $new_rule;
        }

        update_post_meta( $feed_id, '_rex_feed_feed_config_rules', array_values( $updated ) );
        update_post_meta( $feed_id, '_rex_feed_feed_rules_button', 'added' );
        delete_transient( 'rex_feed_validation_' . $feed_id );
        delete_transient( 'rex_feed_validation_health_' . $feed_id );
    }

    /**
     * Delete validation Feed Rule for one affected attribute.
     *
     * @param int    $feed_id   Feed ID.
     * @param string $attribute Affected feed attribute.
     * @return bool Whether matching rule was removed.
     */
    public static function delete_feed_rule( $feed_id, $attribute ) {
        $stored_rules = self::get_stored_rules( $feed_id );
        $updated      = array();
        $removed      = false;

        foreach ( $stored_rules as $stored_rule ) {
            if ( ! is_array( $stored_rule ) ) {
                continue;
            }

            $is_quick_fix   = ! empty( $stored_rule['quick_fix'] )
                || 'validation_quick_fix' === ( $stored_rule['source'] ?? '' );
            $same_attribute = $is_quick_fix
                && $attribute === (string) ( $stored_rule['rules_if'] ?? '' )
                && $attribute === (string) ( $stored_rule['rules_then'] ?? '' );

            if ( $same_attribute ) {
                $removed = true;
                continue;
            }

            $updated[] = $stored_rule;
        }

        if ( ! $removed ) {
            return false;
        }

        update_post_meta( $feed_id, '_rex_feed_feed_config_rules', array_values( $updated ) );
        update_post_meta( $feed_id, '_rex_feed_feed_rules_button', empty( $updated ) ? 'removed' : 'added' );
        delete_transient( 'rex_feed_validation_' . $feed_id );
        delete_transient( 'rex_feed_validation_health_' . $feed_id );

        return true;
    }

    /** Read current rules without reviving intentionally emptied legacy meta. */
    public static function get_stored_rules( $feed_id ) {
        if ( metadata_exists( 'post', $feed_id, '_rex_feed_feed_config_rules' ) ) {
            $stored_rules = get_post_meta( $feed_id, '_rex_feed_feed_config_rules', true );
        } else {
            $stored_rules = get_post_meta( $feed_id, 'rex_feed_feed_config_rules', true );
        }

        return is_array( $stored_rules ) ? $stored_rules : array();
    }

    /**
     * Check whether an identical Feed Rule already exists in stored rules.
     *
     * @param int   $feed_id  Feed ID.
     * @param array $new_rule Feed Rule to check.
     * @return bool True if identical rule exists, false otherwise.
     */
    public static function rule_exists( $feed_id, $new_rule ) {
        $stored_rules = self::get_stored_rules( $feed_id );

        if ( empty( $stored_rules ) || ! is_array( $stored_rules ) ) {
            return false;
        }

        $new_if        = trim( (string) ( $new_rule['rules_if'] ?? '' ) );
        $new_condition = trim( (string) ( $new_rule['rules_condition'] ?? '' ) );
        $new_find      = trim( (string) ( $new_rule['rules_find'] ?? '' ) );
        $new_then      = trim( (string) ( $new_rule['rules_then'] ?? '' ) );
        $new_is_static = ! empty( $new_rule['rules_static'] ) && 'off' !== $new_rule['rules_static'];
        $new_static    = trim( (string) ( $new_rule['rules_static_replace'] ?? '' ) );
        $new_replace   = trim( (string) ( $new_rule['rules_replace'] ?? '' ) );

        foreach ( $stored_rules as $stored_rule ) {
            if ( ! is_array( $stored_rule ) ) {
                continue;
            }

            $stored_if        = trim( (string) ( ! empty( $stored_rule['rules_if'] ) ? $stored_rule['rules_if'] : ( $stored_rule['cust_rules_if'] ?? '' ) ) );
            $stored_condition = trim( (string) ( $stored_rule['rules_condition'] ?? '' ) );
            $stored_find      = trim( (string) ( $stored_rule['rules_find'] ?? '' ) );
            $stored_then      = trim( (string) ( $stored_rule['rules_then'] ?? '' ) );
            $stored_is_static = ! empty( $stored_rule['rules_static'] ) && 'off' !== $stored_rule['rules_static'];
            $stored_static    = trim( (string) ( $stored_rule['rules_static_replace'] ?? '' ) );
            $stored_replace   = trim( (string) ( $stored_rule['rules_replace'] ?? '' ) );

            if ( strcasecmp( $new_if, $stored_if ) !== 0 ) {
                continue;
            }

            if ( $new_condition !== $stored_condition ) {
                continue;
            }

            if ( strcasecmp( $new_find, $stored_find ) !== 0 ) {
                continue;
            }

            if ( strcasecmp( $new_then, $stored_then ) !== 0 ) {
                continue;
            }

            if ( $new_is_static !== $stored_is_static ) {
                continue;
            }

            if ( $new_is_static ) {
                if ( strcasecmp( $new_static, $stored_static ) === 0 ) {
                    return true;
                }
            } else {
                if ( strcasecmp( $new_replace, $stored_replace ) === 0 ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Update main feed attribute mapping row in _rex_feed_feed_config.
     *
     * @param int    $feed_id   Feed ID.
     * @param string $attribute Merchant attribute key.
     * @param string $type      'meta' or 'static'.
     * @param string $value     Assigned meta_key or static value.
     * @return array|false
     */
    public static function update_feed_mapping( $feed_id, $attribute, $type, $value ) {
        $feed_config = get_post_meta( $feed_id, '_rex_feed_feed_config', true );
        if ( ! is_array( $feed_config ) ) {
            $feed_config = get_post_meta( $feed_id, 'rex_feed_feed_config', true );
        }
        if ( ! is_array( $feed_config ) ) {
            $feed_config = array();
        }

        $updated        = false;
        $original_type  = 'meta';
        $original_value = '';

        foreach ( $feed_config as &$config ) {
            if ( ! is_array( $config ) ) {
                continue;
            }

            $config_attr = (string) ( $config['attr'] ?? ( $config['cust_attr'] ?? '' ) );
            $matches     = ( $attribute === $config_attr )
                || ( preg_replace( '/^g:/i', '', $attribute ) === preg_replace( '/^g:/i', '', $config_attr ) );

            if ( $matches ) {
                $original_type  = (string) ( $config['type'] ?? 'meta' );
                $original_value = 'static' === $original_type
                    ? (string) ( $config['st_value'] ?? '' )
                    : (string) ( $config['meta_key'] ?? '' );

                if ( 'static' === $type ) {
                    $config['type']     = 'static';
                    $config['st_value'] = (string) $value;
                    $config['meta_key'] = '';
                } else {
                    $config['type']     = 'meta';
                    $config['meta_key'] = (string) $value;
                    $config['st_value'] = '';
                }
                $updated = true;
                break;
            }
        }
        unset( $config );

        if ( $updated ) {
            update_post_meta( $feed_id, '_rex_feed_feed_config', $feed_config );
            update_post_meta( $feed_id, 'rex_feed_feed_config', $feed_config );
            delete_transient( 'rex_feed_validation_' . $feed_id );
            delete_transient( 'rex_feed_validation_health_' . $feed_id );
            return array(
                'updated'        => true,
                'original_type'  => $original_type,
                'original_value' => $original_value,
            );
        }

        return false;
    }

    /**
     * Keep retry history only while its exact validation issue still exists.
     *
     * @param int   $feed_id Feed ID.
     * @param array $issues  Fresh validation issues.
     * @return void
     */
    public static function prune_resolved_attempts( $feed_id, $issues = array() ) {
        delete_post_meta( $feed_id, self::META_KEY_ATTEMPTS );
    }

    /**
     * Check if a feed attribute has a semantic mapping anomaly in feed_config.
     *
     * @param int    $feed_id   Feed ID.
     * @param string $attribute Merchant output attribute key.
     * @return bool True if mapped to an incompatible attribute.
     */
    public function is_feed_mapping_anomaly( $feed_id, $attribute ) {
        $feed_config = get_post_meta( $feed_id, '_rex_feed_feed_config', true );
        if ( ! is_array( $feed_config ) ) {
            $feed_config = get_post_meta( $feed_id, 'rex_feed_feed_config', true );
        }
        if ( ! is_array( $feed_config ) ) {
            return false;
        }

        foreach ( $feed_config as $config ) {
            if ( ! is_array( $config ) ) {
                continue;
            }
            if ( isset( $config['cust_attr'] ) || empty( $config['attr'] ) ) {
                continue;
            }

            $config_attr = (string) $config['attr'];
            $matches     = ( $attribute === $config_attr )
                || ( preg_replace( '/^g:/i', '', $attribute ) === preg_replace( '/^g:/i', '', $config_attr ) );

            if ( ! $matches ) {
                continue;
            }

            if ( 'meta' !== (string) ( $config['type'] ?? '' ) ) {
                continue;
            }

            $meta_key = (string) ( $config['meta_key'] ?? '' );
            if ( '' === $meta_key ) {
                continue;
            }

            $source_label     = $this->format_label( $attribute );
            $assigned_label   = $this->get_attribute_label( $meta_key );
            $source_cluster   = $this->get_synonym_cluster( $this->normalize( $attribute . ' ' . $source_label ) );
            $assigned_cluster = $this->get_synonym_cluster( $this->normalize( $meta_key . ' ' . $assigned_label ) );

            if ( '' !== $source_cluster && '' !== $assigned_cluster ) {
                $id_clusters = array( 'id', 'mpn', 'gtin' );
                if ( in_array( $source_cluster, $id_clusters, true ) && in_array( $assigned_cluster, $id_clusters, true ) ) {
                    return false;
                }

                return $source_cluster !== $assigned_cluster;
            }
        }

        return false;
    }

    /** Find matching stored issue. */
    protected function find_issue( $feed_id, $attribute, $rule, $severity ) {
        $issues = get_post_meta( $feed_id, Rex_Feed_Validation_Results::META_KEY_RESULTS, true );

        foreach ( (array) $issues as $issue ) {
            if (
                $attribute === (string) ( $issue['attribute'] ?? '' )
                && $rule === (string) ( $issue['rule'] ?? '' )
                && $severity === (string) ( $issue['severity'] ?? '' )
            ) {
                return $issue;
            }
        }

        return null;
    }

    /** Select condition using validator rule semantics. */
    protected function get_condition_for_issue( $issue ) {
        $rule_and_message = strtolower( (string) ( $issue['rule'] ?? '' ) . ' ' . (string) ( $issue['message'] ?? '' ) );
        $is_empty_issue   = preg_match( '/empty|missing|required/', $rule_and_message );
        $is_anomaly_issue = preg_match( '/wrongly_assigned|wrong_assigned|anomaly|invalid_mapping|wrong/', $rule_and_message );

        if ( $is_empty_issue ) {
            return array(
                'condition' => 'equal_to',
                'label'     => __( 'Is equal to', 'rex-product-feed' ),
                'find'      => '',
                'automatic' => true,
            );
        }

        if ( $is_anomaly_issue ) {
            return array(
                'condition' => 'any',
                'label'     => __( 'Is Any', 'rex-product-feed' ),
                'find'      => '',
                'automatic' => true,
            );
        }

        return array(
            'condition' => '',
            'label'     => '',
            'find'      => '',
            'automatic' => false,
        );
    }

    /** Find highest-scoring candidate attribute. */
    protected function find_best_candidate( $attribute, $feed_id, $issue, $target_attribute = '' ) {
        $catalog   = $this->get_attribute_catalog();
        $excluded  = array_fill_keys( array_filter( array( $attribute, $target_attribute ) ), true );
        $best      = null;
        $threshold = (float) apply_filters(
            'rex_feed_quick_fix_confidence_threshold',
            self::DEFAULT_CONFIDENCE_THRESHOLD,
            $feed_id,
            $issue
        );

        foreach ( $catalog as $candidate ) {
            if ( isset( $excluded[ $candidate['value'] ] ) ) {
                continue;
            }

            $score = $this->calculate_score(
                $attribute,
                $this->format_label( $attribute ),
                $candidate['value'],
                $candidate['label'],
                $candidate['group']
            );

            if ( $score < $threshold ) {
                continue;
            }

            if ( null === $best || $score > $best['score'] ) {
                $candidate['score'] = $score;
                $best               = $candidate;
            }
        }

        return $best;
    }

    /**
     * Find highest-scoring attribute for mapping fix (without excluding the attribute name itself).
     *
     * @param string $attribute Merchant attribute key.
     * @param int    $feed_id   Feed ID.
     * @param array  $issue     Validation issue.
     * @return array|null
     */
    public function find_best_candidate_for_mapping( $attribute, $feed_id, $issue = array() ) {
        $catalog   = $this->get_attribute_catalog();
        $best      = null;
        $threshold = (float) apply_filters(
            'rex_feed_quick_fix_confidence_threshold',
            self::DEFAULT_CONFIDENCE_THRESHOLD,
            $feed_id,
            $issue
        );

        foreach ( $catalog as $candidate ) {
            $score = $this->calculate_score(
                $attribute,
                $this->format_label( $attribute ),
                $candidate['value'],
                $candidate['label'],
                $candidate['group']
            );

            if ( $score < $threshold ) {
                continue;
            }

            if ( null === $best || $score > $best['score'] ) {
                $candidate['score'] = $score;
                $best               = $candidate;
            }
        }

        return $best;
    }

    /** Flatten available Feed Rule attributes while preserving group order. */
    protected function get_attribute_catalog() {
        if ( ! class_exists( 'Rex_Feed_Attributes' ) ) {
            require_once dirname( __DIR__ ) . '/class-rex-feed-attributes.php';
        }

        $groups  = Rex_Feed_Attributes::get_attributes();
        $catalog = array();

        foreach ( (array) $groups as $group => $options ) {
            if ( ! is_array( $options ) || 'Attributes Separator' === $group ) {
                continue;
            }

            foreach ( $options as $value => $label ) {
                if ( '' === (string) $value || ! is_scalar( $label ) ) {
                    continue;
                }

                $catalog[] = array(
                    'value' => (string) $value,
                    'label' => wp_strip_all_tags( (string) $label ),
                    'group' => (string) $group,
                );
            }
        }

        return $catalog;
    }

    /** Find catalog label for one source key. */
    protected function get_attribute_label( $attribute ) {
        foreach ( $this->get_attribute_catalog() as $candidate ) {
            if ( $attribute === $candidate['value'] ) {
                return $candidate['label'];
            }
        }

        return $this->format_label( $attribute );
    }

    /** Deterministic semantic confidence score from 0 to 0.99. */
    protected function calculate_score( $source_key, $source_label, $candidate_key, $candidate_label, $group ) {
        $sources    = array_unique( array_filter( array( $this->normalize( $source_key ), $this->normalize( $source_label ) ) ) );
        $candidates = array_unique( array_filter( array( $this->normalize( $candidate_key ), $this->normalize( $candidate_label ) ) ) );
        $score      = 0.0;

        foreach ( $sources as $source ) {
            foreach ( $candidates as $candidate ) {
                if ( $source === $candidate ) {
                    $score = max( $score, 0.95 );
                    continue;
                }

                $source_cluster    = $this->get_synonym_cluster( $source );
                $candidate_cluster = $this->get_synonym_cluster( $candidate );

                if ( $source_cluster && $source_cluster === $candidate_cluster ) {
                    $score = max( $score, 0.92 );
                }

                $score = max( $score, $this->token_overlap( $source, $candidate ) * 0.85 );
                $score = max( $score, $this->dice_similarity( $source, $candidate ) * 0.80 );
            }
        }

        if ( $score > 0 && in_array( $group, array( 'Primary Attributes', 'Price Attributes', 'Shipping Attributes', 'Image Attributes' ), true ) ) {
            $score += 0.04;
        }

        $normalized_source    = $this->normalize( $source_key );
        $normalized_candidate = $this->normalize( $candidate_key . ' ' . $candidate_label );

        if ( 'price' === $normalized_source && preg_match( '/\bsale\b|\btax\b/', $normalized_candidate ) ) {
            $score -= 0.25;
        }

        return max( 0.0, min( 0.99, $score ) );
    }

    /** Normalize technical keys and labels for matching. */
    protected function normalize( $value ) {
        $value = strtolower( html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES, 'UTF-8' ) );
        $value = preg_replace( '/\[[^\]]*\]|\([^\)]*\)/', ' ', $value );
        $value = preg_replace( '/^(g:|bwf_attr_pa_|custom_attributes__wpfm_product_|custom_attributes__|custom_attributes_|pa_|woo_product_|woo_|_alg_|_mantella_)/', '', $value );
        $value = preg_replace( '/[^a-z0-9]+/', ' ', $value );
        $noise = array( 'product', 'item', 'woocommerce', 'woo', 'wpfm', 'default', 'field', 'from', 'db', 'without', 'underscore' );
        $words = array_filter( preg_split( '/\s+/', trim( $value ) ) );
        $words = array_values( array_diff( $words, $noise ) );

        return implode( ' ', $words );
    }

    /** Return shared ecommerce synonym cluster. */
    protected function get_synonym_cluster( $value ) {
        $clusters = array(
            'id'          => array( 'id', 'product id', 'item id', 'sku', 'parent id', 'variation id' ),
            'title'       => array( 'title', 'product title', 'item title', 'post title', 'name', 'product name' ),
            'brand'       => array( 'brand', 'manufacturer', 'make', 'vendor', 'producer', 'oem', 'designer' ),
            'gtin'        => array( 'gtin', 'upc', 'ean', 'barcode', 'jan', 'isbn', 'itf' ),
            'mpn'         => array( 'mpn', 'sku', 'model number', 'part number', 'manufacturer part number' ),
            'weight'      => array( 'weight', 'shipping weight' ),
            'length'      => array( 'length', 'shipping length' ),
            'width'       => array( 'width', 'shipping width' ),
            'height'      => array( 'height', 'shipping height' ),
            'color'       => array( 'color', 'colour', 'shade' ),
            'size'        => array( 'size', 'dimensions', 'apparel size' ),
            'gender'      => array( 'gender', 'sex', 'target gender' ),
            'age_group'   => array( 'age group', 'age range', 'target age' ),
            'material'    => array( 'material', 'fabric', 'composition' ),
            'pattern'     => array( 'pattern', 'graphic', 'print' ),
            'image'       => array( 'image', 'image link', 'featured image', 'main image', 'thumbnail image' ),
            'price'       => array( 'price', 'regular price', 'current price' ),
            'sale_price'  => array( 'sale price', 'discount price', 'offer price', 'special price' ),
            'category'    => array( 'product type', 'category', 'categories', 'product cats', 'google product category' ),
            'stock'       => array( 'availability', 'stock status', 'in stock', 'quantity', 'inventory' ),
            'description' => array( 'description', 'short description', 'product description', 'body', 'details', 'excerpt', 'summary', 'brief', 'content' ),
            'url'         => array( 'link', 'product url', 'permalink', 'url' ),
            'condition'   => array( 'condition', 'state' ),
            'rating'      => array( 'rating', 'average rating', 'total rating', 'reviews' ),
            'tax'         => array( 'tax', 'tax class', 'tax rate' ),
            'shipping'    => array( 'shipping', 'shipping cost', 'shipping class' ),
            'tag'         => array( 'tag', 'tags', 'product tag', 'product tags' ),
            'author'      => array( 'author', 'author name', 'creator', 'artist' ),
            'date'        => array( 'date', 'date created', 'date modified', 'published' ),
        );

        foreach ( $clusters as $cluster => $terms ) {
            foreach ( $terms as $term ) {
                if ( preg_match( '/(?:^|\s)' . preg_quote( $term, '/' ) . '(?:\s|$)/', $value ) ) {
                    return $cluster;
                }
            }
        }

        return '';
    }

    /** Token overlap using smaller token set as denominator. */
    protected function token_overlap( $first, $second ) {
        $first_tokens  = array_values( array_unique( array_filter( explode( ' ', $first ) ) ) );
        $second_tokens = array_values( array_unique( array_filter( explode( ' ', $second ) ) ) );
        $denominator   = min( count( $first_tokens ), count( $second_tokens ) );

        if ( 0 === $denominator ) {
            return 0.0;
        }

        return count( array_intersect( $first_tokens, $second_tokens ) ) / $denominator;
    }

    /** Sorensen-Dice bigram similarity. */
    protected function dice_similarity( $first, $second ) {
        if ( $first === $second ) {
            return 1.0;
        }

        if ( strlen( $first ) < 2 || strlen( $second ) < 2 ) {
            return 0.0;
        }

        $first_bigrams  = $this->get_bigrams( $first );
        $second_bigrams = $this->get_bigrams( $second );
        $remaining      = $second_bigrams;
        $matches        = 0;

        foreach ( $first_bigrams as $bigram ) {
            $index = array_search( $bigram, $remaining, true );
            if ( false !== $index ) {
                $matches++;
                unset( $remaining[ $index ] );
            }
        }

        return ( 2.0 * $matches ) / ( count( $first_bigrams ) + count( $second_bigrams ) );
    }

    /** Build character bigrams. */
    protected function get_bigrams( $value ) {
        $bigrams = array();
        $length  = strlen( $value );

        for ( $index = 0; $index < $length - 1; $index++ ) {
            $bigrams[] = substr( $value, $index, 2 );
        }

        return $bigrams;
    }


    /** Human-readable attribute label. */
    protected function format_label( $attribute ) {
        $attribute = preg_replace( '/^g:/i', '', (string) $attribute );
        return ucwords( str_replace( array( '_', '-' ), ' ', $attribute ) );
    }
}
