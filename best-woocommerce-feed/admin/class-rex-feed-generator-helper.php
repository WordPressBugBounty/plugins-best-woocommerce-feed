<?php
/**
 * Feed Generator Helper Class
 * Contains utility functions for feed generation across different feed classes
 * @since 7.4.37
 */

class Rex_Feed_Generator_Helper {
    /**
     * Check if cached feed is valid or needs regeneration
     *
     * @param int $feed_id Feed ID
     * @param int $batch Current batch number
     * @param bool $bypass Whether to use cache
     * @param array $current_products Current product IDs
     * @param string &$feed Reference to feed content variable to update if cache is valid
     * @return bool Whether feed should be regenerated
     * @since 7.4.37
     */
    public static function wpfm_should_regenerate_feed($feed_id, $batch, $bypass, $current_products, &$feed) {
        if (!$bypass) {
            return true; // Always regenerate if bypass is false
        }

        $feed_transient_key = "feed_{$feed_id}_batch_{$batch}_feed";
        $product_ids_transient_key = "feed_{$feed_id}_batch_{$batch}_product_ids";

        // Get cached values
        $cached_feed = get_transient($feed_transient_key);
        $cached_product_ids = get_transient($product_ids_transient_key);

        $feed_post = get_post($feed_id);
        $feed_last_updated = strtotime($feed_post->post_modified_gmt);

        $current_batch_length = get_option('rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT);
        $transient_batch_length = get_transient('wpfm_current_feed_product_number');
        $cache_per_batch_product = is_string($transient_batch_length) ? $transient_batch_length : '0';

        if (( (is_string($cached_feed) && trim($cached_feed) !== '') || (is_array($cached_feed) && !empty($cached_feed))) &&
            (is_array($cached_product_ids) && !empty($cached_product_ids))) {

            global $wpdb;
            $placeholders = implode(',', array_fill(0, count($cached_product_ids), '%d'));
            $params = array_merge($cached_product_ids, [gmdate('Y-m-d H:i:s', $feed_last_updated)]);
            $query = $wpdb->prepare(
                "
                SELECT ID FROM {$wpdb->posts}
                WHERE ID IN ($placeholders)
                  AND post_type = 'product'
                  AND post_modified_gmt > %s
                ",
                ...$params
            );

            $modified_products = $wpdb->get_col($query);

            // Detect deleted products
            $deleted_product_ids = array_diff($cached_product_ids, $current_products);

            // Newly added products
            $new_product_ids = array_diff($current_products, $cached_product_ids);
            if (empty($modified_products) && empty($deleted_product_ids) && empty($new_product_ids) && $cache_per_batch_product === $current_batch_length) {
                $feed = $cached_feed;
                return false; // No need to regenerate
            }

        }

        return true; // Need to regenerate
    }

    /**
     * Cache the generated feed
     *
     * @param int $feed_id Feed ID
     * @param int $batch Current batch number
     * @param bool $bypass Whether to use cache
     * @param array $products Product IDs
     * @param string $feed Feed content
     * @since 7.4.37
     */
    public static function wpfm_cache_feed($feed_id, $batch, $bypass, $products, $feed) {
        $rex_feed_scheduler = new Rex_Feed_Scheduler();
        $schedule_types = [
            'daily' => 30 * HOUR_IN_SECONDS,
            'hourly' => 90 * MINUTE_IN_SECONDS,
            'weekly' => 8 * DAY_IN_SECONDS,
            'default' => 24 * HOUR_IN_SECONDS
        ];

        // Determine expiration time
        $expiration = self::wpfm_get_feed_expiration($feed_id, $rex_feed_scheduler, $schedule_types);
        // Transient keys
        $feed_transient_key = "feed_{$feed_id}_batch_{$batch}_feed";
        $product_ids_transient_key = "feed_{$feed_id}_batch_{$batch}_product_ids";
        $feed_update_timestamp = "feed_{$feed_id}_feed_update_timestamp";
        $feed_update_timestamp_gmt = "feed_{$feed_id}_feed_update_timestamp_gmt";

        // Cache feed and product IDs
        set_transient($feed_transient_key, $feed, $expiration);
        set_transient($product_ids_transient_key, $products, $expiration);
        set_transient('wpfm_current_feed_product_number', get_option('rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT), $expiration);

        // Cache timestamps for the first batch
        if ($batch == 1) {
            set_transient($feed_update_timestamp, current_time('mysql'), $expiration);
            set_transient($feed_update_timestamp_gmt, current_time('mysql', true), $expiration);
        }
    }

    /**
     * Update the feed modification timestamp
     *
     * @param int $feed_id Feed ID
     * @since 7.4.37
     */
    public static function wpfm_update_feed_timestamp($feed_id) {
        $post_modified = get_transient("feed_{$feed_id}_feed_update_timestamp");
        $post_modified_gmt = get_transient("feed_{$feed_id}_feed_update_timestamp_gmt");

        // Only update if both timestamp values exist
        if ($post_modified && $post_modified_gmt) {
            wp_update_post([
                'ID' => $feed_id,
                'post_modified' => $post_modified,
                'post_modified_gmt' => $post_modified_gmt,
            ]);
        }
    }

    /**
     * Get feed expiration time based on schedule type.
     *
     * @param int $feed_id Feed ID
     * @param Rex_Feed_Scheduler $rex_feed_scheduler Scheduler instance
     * @param array $schedule_types Schedule types with expiration times
     * @return int Expiration time in seconds
     * @since 7.4.37
     */
    private static function wpfm_get_feed_expiration($feed_id, $rex_feed_scheduler, $schedule_types) {
        if (in_array((int)$feed_id, $rex_feed_scheduler->get_feeds('daily') ?: [])) {
            return $schedule_types['daily'];
        } elseif (in_array((int)$feed_id, $rex_feed_scheduler->get_feeds('hourly') ?: [])) {
            return $schedule_types['hourly'];
        } elseif (in_array((int)$feed_id, $rex_feed_scheduler->get_feeds('weekly') ?: [])) {
            return $schedule_types['weekly'];
        }
        return $schedule_types['default'];
    }
}