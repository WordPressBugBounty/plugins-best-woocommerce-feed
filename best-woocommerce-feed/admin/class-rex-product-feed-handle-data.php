<?php
/**
 * Class Rex_Product_Feed_Ajax
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Metabox
 * @subpackage Rex_Product_Feed/admin
 */

/**
 * The admin-specific functionality of the plugin
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Metabox
 * @subpackage Rex_Product_Feed/admin
 */
class Rex_Product_Feed_Data_Handle {

    /**
     * Retrieves and filters specific data from the given feed data.
     *
     * This function takes an array of feed data and filters out specific data items
     * based on their keys. The filtered data is stored in a new array and returned.
     * The function ensures that empty feed data is handled gracefully by returning
     * an empty array.
     *
     * @param array $feed_data The feed data array to filter.
     * @return array The filtered data array.
     * @since 7.3.1
     */
    public static function get_filter_drawer_data( $feed_data ) {
        if( empty( $feed_data ) ) {
            return [];
        }
        $filter_data = [];

        if( !empty( $feed_data[ 'rex_feed_products' ] ) ) {
            $filter_data[ 'rex_feed_products' ] = $feed_data[ 'rex_feed_products' ];
        }
        if( !empty( $feed_data[ 'rex_feed_feed_rules_button' ] ) ) {
            $filter_data[ 'rex_feed_feed_rules_button' ] = $feed_data[ 'rex_feed_feed_rules_button' ];
        }
        if( !empty( $feed_data[ 'rex_feed_custom_filter_option_btn' ] ) ) {
            $filter_data[ 'rex_feed_custom_filter_option_btn' ] = $feed_data[ 'rex_feed_custom_filter_option_btn' ];
        }
        if( !empty( $feed_data[ 'rex_feed_cats' ] ) ) {
            $filter_data[ 'rex_feed_cats' ] = $feed_data[ 'rex_feed_cats' ];
        }
        if( !empty( $feed_data[ 'rex_feed_tags' ] ) ) {
            $filter_data[ 'rex_feed_tags' ] = $feed_data[ 'rex_feed_tags' ];
        }
        if( !empty( $feed_data[ 'rex_feed_product_filter_ids' ] ) ) {
            $filter_data[ 'rex_feed_product_filter_ids' ] = $feed_data[ 'rex_feed_product_filter_ids' ];
        }
        if( !empty( $feed_data[ 'product_filter_condition' ] ) ) {
            $filter_data[ 'product_filter_condition' ] = $feed_data[ 'product_filter_condition' ];
        }
        if( !empty( $feed_data[ 'rex_feed_cats_check_all_btn' ] ) ) {
            $filter_data[ 'rex_feed_cats_check_all_btn' ] = $feed_data[ 'rex_feed_cats_check_all_btn' ];
        }
        if( !empty( $feed_data[ 'rex_feed_tags_check_all_btn' ] ) ) {
            $filter_data[ 'rex_feed_tags_check_all_btn' ] = $feed_data[ 'rex_feed_tags_check_all_btn' ];
        }
        if( !empty( $feed_data[ 'fr' ] ) ) {
            $filter_data[ 'fr' ] = $feed_data[ 'fr' ];
        }
        if( !empty( $feed_data[ 'ff' ] ) ) {
            $filter_data[ 'ff' ] = $feed_data[ 'ff' ];
        }

        /**
         * Filter the list of filters drawer data to save.
         *
         * @param array $filter_data A list of the filters drawer data.
         * @param array $feed_data A list of the all feed data.
         *
         * @since 7.3.1
         */
        return apply_filters( 'rexfeed_filters_drawer_data', $filter_data, $feed_data );
    }

    /**
     * Saves the filter drawer data for a specific feed.
     *
     * This function takes a feed ID and a data array and saves the relevant filter drawer
     * data for that feed. The function updates post meta values and sets object terms
     * based on the provided data. If either the feed ID or the data is empty, the function
     * does nothing.
     *
     * @param int    $feed_id The ID of the feed to save the data for.
     * @param array  $data    The filter drawer data to save.
     * @return void
     * @since 7.3.1
     */
    public static function save_filter_drawer_data( $feed_id, $data ) {
        if( !$feed_id || empty( $data ) ) {
            return;
        }

        if( isset( $data[ 'rex_feed_products' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_products', $data[ 'rex_feed_products' ] );
        }
        if( isset( $data[ 'rex_feed_feed_rules_button' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_feed_rules_button', $data[ 'rex_feed_feed_rules_button' ] );
        }
        if( isset( $data[ 'rex_feed_custom_filter_option_btn' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_custom_filter_option', $data[ 'rex_feed_custom_filter_option_btn' ] );
        }
        if( isset( $data[ 'rex_feed_product_filter_ids' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_product_filter_ids', $data[ 'rex_feed_product_filter_ids' ] );
        }
        if( isset( $data[ 'product_filter_condition' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_product_condition', $data[ 'product_filter_condition' ] );
        }

        if( isset( $data[ 'fr' ] ) ) {
            reset( $data[ 'fr' ] );
            $key = key( $data[ 'fr' ] );
            unset( $data[ 'fr' ][ $key ] );
            update_post_meta( $feed_id, '_rex_feed_feed_config_rules', array_values( $data[ 'fr' ] ) );
        }
        if( isset( $data[ 'ff' ] ) ) {
            reset( $data[ 'ff' ] );
            $key = key( $data[ 'ff' ] );
            unset( $data[ 'ff' ][ $key ] );
            update_post_meta( $feed_id, '_rex_feed_feed_config_filter', array_values( $data[ 'ff' ] ) );
        }

        if( isset( $data[ 'rex_feed_cats_check_all_btn' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_cats_check_all_btn', $data[ 'rex_feed_cats_check_all_btn' ] );
        }
        else {
            delete_post_meta( $feed_id, '_rex_feed_cats_check_all_btn' );
        }
        if( isset( $data[ 'rex_feed_tags_check_all_btn' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_tags_check_all_btn', $data[ 'rex_feed_tags_check_all_btn' ] );
        }
        else {
            delete_post_meta( $feed_id, '_rex_feed_tags_check_all_btn' );
        }

        if( isset( $data[ 'rex_feed_cats' ] ) ) {
            $cats = array();
            foreach( $data[ 'rex_feed_cats' ] as $cat ) {
                $cats[] = get_term_by( 'slug', $cat, 'product_cat' )->term_id;
            }
            wp_set_object_terms( $feed_id, $cats, 'product_cat' );
        }
        else {
            wp_set_object_terms( $feed_id, array(), 'product_cat' );
        }
        if( isset( $data[ 'rex_feed_tags' ] ) ) {
            $tags = array();
            foreach( $data[ 'rex_feed_tags' ] as $tag ) {
                $tags[] = get_term_by( 'slug', $tag, 'product_tag' )->term_id;
            }
            wp_set_object_terms( $feed_id, $tags, 'product_tag' );
        }
        else {
            wp_set_object_terms( $feed_id, array(), 'product_tag' );
        }
    }

    /**
     * Retrieves the settings drawer data for a specific feed.
     *
     * This function takes a feed data array and extracts the relevant settings drawer
     * data from it. The function checks for specific keys in the feed data and populates
     * the settings data array accordingly. If the feed data is empty, an empty array is
     * returned. The function applies a filter 'rexfeed_settings_drawer_data' to the
     * settings data before returning it.
     *
     * @param array  $feed_data The feed data array.
     * @return array The settings drawer data extracted from the feed data.
     * @since 7.3.1
     */
    public static function get_settings_drawer_data( $feed_data ) {
        if( empty( $feed_data ) ) {
            return [];
        }
        $settings_data = [];

        if( !empty( $feed_data[ 'rex_feed_schedule' ] ) ) {
            $settings_data[ 'rex_feed_schedule' ] = $feed_data[ 'rex_feed_schedule' ];
        }
        if( !empty( $feed_data[ 'rex_feed_include_out_of_stock' ] ) ) {
            $settings_data[ 'rex_feed_include_out_of_stock' ] = $feed_data[ 'rex_feed_include_out_of_stock' ];
        }
        if( !empty( $feed_data[ 'rex_feed_variable_product' ] ) ) {
            $settings_data[ 'rex_feed_variable_product' ] = $feed_data[ 'rex_feed_variable_product' ];
        }
        if( !empty( $feed_data[ 'rex_feed_variations' ] ) ) {
            $settings_data[ 'rex_feed_variations' ] = $feed_data[ 'rex_feed_variations' ];
        }
        if( !empty( $feed_data[ 'rex_feed_parent_product' ] ) ) {
            $settings_data[ 'rex_feed_parent_product' ] = $feed_data[ 'rex_feed_parent_product' ];
        }
        if( !empty( $feed_data[ 'rex_feed_variation_product_name' ] ) ) {
            $settings_data[ 'rex_feed_variation_product_name' ] = $feed_data[ 'rex_feed_variation_product_name' ];
        }
        if( !empty( $feed_data[ 'rex_feed_hidden_products' ] ) ) {
            $settings_data[ 'rex_feed_hidden_products' ] = $feed_data[ 'rex_feed_hidden_products' ];
        }
        if( !empty( $feed_data[ 'rex_feed_skip_product' ] ) ) {
            $settings_data[ 'rex_feed_skip_product' ] = $feed_data[ 'rex_feed_skip_product' ];
        }
        if( !empty( $feed_data[ 'rex_feed_skip_row' ] ) ) {
            $settings_data[ 'rex_feed_skip_row' ] = $feed_data[ 'rex_feed_skip_row' ];
        }
        if( !empty( $feed_data[ 'rex_feed_include_zero_price_products' ] ) ) {
            $settings_data[ 'rex_feed_include_zero_price_products' ] = $feed_data[ 'rex_feed_include_zero_price_products' ];
        }
        if( !empty( $feed_data[ 'rex_feed_analytics_params_options' ] ) ) {
            $settings_data[ 'rex_feed_analytics_params_options' ] = $feed_data[ 'rex_feed_analytics_params_options' ];
        }
        if( !empty( $feed_data[ 'rex_feed_aelia_currency' ] ) ) {
            $settings_data[ 'rex_feed_aelia_currency' ] = $feed_data[ 'rex_feed_aelia_currency' ];
        }
        if( !empty( $feed_data[ 'rex_feed_wmc_currency' ] ) ) {
            $settings_data[ 'rex_feed_wmc_currency' ] = $feed_data[ 'rex_feed_wmc_currency' ];
        }
        if( !empty( $feed_data[ 'rex_feed_wcml_currency' ] ) ) {
            $settings_data[ 'rex_feed_wcml_currency' ] = $feed_data[ 'rex_feed_wcml_currency' ];
        }
        if( !empty( $feed_data[ 'rex_feed_analytics_params' ] ) ) {
            $settings_data[ 'rex_feed_analytics_params' ] = $feed_data[ 'rex_feed_analytics_params' ];
        }
        if( !empty( $feed_data[ 'rex_feed_feed_country' ] ) ) {
            $settings_data[ 'rex_feed_feed_country' ] = $feed_data[ 'rex_feed_feed_country' ];
        }
        if( !empty( $feed_data[ 'rex_feed_update_on_product_change' ] ) ) {
            $settings_data[ 'rex_feed_update_on_product_change' ] = $feed_data[ 'rex_feed_update_on_product_change' ];
        }
        if( !empty( $feed_data[ 'rex_feed_tax_id' ] ) ) {
            $settings_data[ 'rex_feed_tax_id' ] = $feed_data[ 'rex_feed_tax_id' ];
        }

        /**
         * Filter the list of settings drawer data to save.
         *
         * @param array $settings_data A list of the settings drawer data.
         * @param array $feed_data A list of the all feed data.
         *
         * @since 7.3.1
         */
        return apply_filters( 'rexfeed_settings_drawer_data', $settings_data, $feed_data );
    }

    /**
     * Saves the filter drawer data for a specific feed.
     *
     * This function takes a feed ID and a data array and saves the relevant filter drawer
     * data for that feed. The function updates post meta values and sets object terms
     * based on the provided data. If either the feed ID or the data is empty, the function
     * does nothing.
     *
     * @param int    $feed_id The ID of the feed to save the data for.
     * @param array  $data    The filter drawer data to save.
     * @return void
     * @since 7.3.1
     */
    public static function save_settings_drawer_data( $feed_id, $data ) {
        if( !$feed_id || empty( $data ) ) {
            return;
        }
        if( isset( $data[ 'rex_feed_schedule' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_schedule', $data[ 'rex_feed_schedule' ] );
            delete_post_meta( $feed_id, 'rex_feed_schedule' );
        }
        if( isset( $data[ 'rex_feed_include_out_of_stock' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_include_out_of_stock', $data[ 'rex_feed_include_out_of_stock' ] );
        }
        else {
            update_post_meta( $feed_id, '_rex_feed_include_out_of_stock', 'no' );
        }
        if( isset( $data[ 'rex_feed_variable_product' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_variable_product', $data[ 'rex_feed_variable_product' ] );
        }
        else {
            update_post_meta( $feed_id, '_rex_feed_variable_product', 'no' );
        }
        if( isset( $data[ 'rex_feed_variations' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_variations', $data[ 'rex_feed_variations' ] );
        }
        else {
            update_post_meta( $feed_id, '_rex_feed_variations', 'no' );
        }
        if( isset( $data[ 'rex_feed_parent_product' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_parent_product', $data[ 'rex_feed_parent_product' ] );
        }
        else {
            update_post_meta( $feed_id, '_rex_feed_parent_product', 'no' );
        }
        if( isset( $data[ 'rex_feed_variation_product_name' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_variation_product_name', $data[ 'rex_feed_variation_product_name' ] );
        }
        else {
            update_post_meta( $feed_id, '_rex_feed_variation_product_name', 'no' );
        }
        if( isset( $data[ 'rex_feed_hidden_products' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_hidden_products', $data[ 'rex_feed_hidden_products' ] );
        }
        else {
            update_post_meta( $feed_id, '_rex_feed_hidden_products', 'no' );
        }
        if( isset( $data[ 'rex_feed_skip_product' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_skip_product', $data[ 'rex_feed_skip_product' ] );
        }
        else {
            update_post_meta( $feed_id, '_rex_feed_skip_product', 'no' );
        }
        if( isset( $data[ 'rex_feed_skip_row' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_skip_row', $data[ 'rex_feed_skip_row' ] );
        }
        else {
            update_post_meta( $feed_id, '_rex_feed_skip_row', 'no' );
        }
        if( isset( $data[ 'rex_feed_include_zero_price_products' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_include_zero_price_products', $data[ 'rex_feed_include_zero_price_products' ] );
        }
        else {
            update_post_meta( $feed_id, '_rex_feed_include_zero_price_products', 'no' );
        }
        if( isset( $data[ 'rex_feed_analytics_params_options' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_analytics_params_options', $data[ 'rex_feed_analytics_params_options' ] );
        }
        else {
            update_post_meta( $feed_id, '_rex_feed_analytics_params_options', 'no' );
        }

        if( isset( $data[ 'rex_feed_aelia_currency' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_aelia_currency', $data[ 'rex_feed_aelia_currency' ] );
        }
        if( isset( $data[ 'rex_feed_wmc_currency' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_wmc_currency', $data[ 'rex_feed_wmc_currency' ] );
        }
        if( isset( $data[ 'rex_feed_wcml_currency' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_wcml_currency', $data[ 'rex_feed_wcml_currency' ] );
        }
        if( isset( $data[ 'rex_feed_analytics_params' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_analytics_params', $data[ 'rex_feed_analytics_params' ] );
        }
        if( isset( $data[ 'rex_feed_feed_country' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_feed_country', $data[ 'rex_feed_feed_country' ] );
        }
        if( isset( $data[ 'rex_feed_tax_id' ] ) ) {
            update_post_meta( $feed_id, '_rex_feed_tax_id', $data[ 'rex_feed_tax_id' ] );
        }
    }
}