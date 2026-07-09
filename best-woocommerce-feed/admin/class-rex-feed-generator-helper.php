<?php
/**
 * Feed Generator Helper Class
 * Contains utility functions for feed generation across different feed classes
 * @since 7.4.37
 */

class Rex_Feed_Generator_Helper {
    /**
     * @return string 'database' or 'filesystem'
     */
    private static function wpfm_get_cache_driver() {
        return get_option( 'wpfm_feed_cache_storage', 'database' );
    }

    /**
     * Returns the cache directory path, creating it if needed.
     * Returns false if the directory is not writable.
     *
     * @return string|false
     */
    private static function wpfm_get_cache_dir() {
        $dir = wp_upload_dir()['basedir'] . '/rex-feed/cache';
        if ( ! file_exists( $dir ) ) {
            wp_mkdir_p( $dir );
        }
        if ( ! is_writable( $dir ) ) {
            return false;
        }
        return $dir;
    }

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
            return true;
        }

        if ( 'filesystem' === self::wpfm_get_cache_driver() ) {
            return self::wpfm_should_regenerate_feed_fs( $feed_id, $batch, $current_products, $feed );
        }

        $feed_transient_key = "feed_{$feed_id}_batch_{$batch}_feed";
        $product_ids_transient_key = "feed_{$feed_id}_batch_{$batch}_product_ids";

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

            $deleted_product_ids = array_diff($cached_product_ids, $current_products);
            $new_product_ids = array_diff($current_products, $cached_product_ids);
            if (empty($modified_products) && empty($deleted_product_ids) && empty($new_product_ids) && $cache_per_batch_product === $current_batch_length) {
                $feed = $cached_feed;
                return false;
            }
        }

        return true;
    }

    /**
     * Filesystem branch of should_regenerate check.
     */
    private static function wpfm_should_regenerate_feed_fs($feed_id, $batch, $current_products, &$feed) {
        $dir = self::wpfm_get_cache_dir();
        if ( false === $dir ) {
            return true;
        }

        $meta_file  = $dir . "/feed-{$feed_id}-batch-{$batch}.meta";
        $cache_file = $dir . "/feed-{$feed_id}-batch-{$batch}.cache";

        if ( ! file_exists( $meta_file ) || ! file_exists( $cache_file ) ) {
            return true;
        }

        $meta = json_decode( file_get_contents( $meta_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        if ( ! is_array( $meta ) || empty( $meta['ids'] ) ) {
            return true;
        }

        $cached_product_ids   = $meta['ids'];
        $current_batch_length = get_option( 'rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT );
        $cache_batch_size     = isset( $meta['size'] ) ? (string) $meta['size'] : '0';

        $feed_post         = get_post( $feed_id );
        $feed_last_updated = strtotime( $feed_post->post_modified_gmt );

        global $wpdb;
        $placeholders    = implode( ',', array_fill( 0, count( $cached_product_ids ), '%d' ) );
        $params          = array_merge( $cached_product_ids, [ gmdate( 'Y-m-d H:i:s', $feed_last_updated ) ] );
        $modified_products = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts}
                 WHERE ID IN ($placeholders)
                   AND post_type = 'product'
                   AND post_modified_gmt > %s",
                ...$params
            )
        );

        $deleted_product_ids = array_diff( $cached_product_ids, $current_products );
        $new_product_ids     = array_diff( $current_products, $cached_product_ids );

        if ( empty( $modified_products ) && empty( $deleted_product_ids ) && empty( $new_product_ids )
            && $cache_batch_size === (string) $current_batch_length ) {
            $feed = file_get_contents( $cache_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            return false;
        }

        return true;
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
        if ( 'filesystem' === self::wpfm_get_cache_driver() ) {
            self::wpfm_cache_feed_fs( $feed_id, $batch, $products, $feed );
            return;
        }

        $rex_feed_scheduler = new Rex_Feed_Scheduler();
        $schedule_types = [
            'daily' => 30 * HOUR_IN_SECONDS,
            'hourly' => 90 * MINUTE_IN_SECONDS,
            'weekly' => 8 * DAY_IN_SECONDS,
            'default' => 24 * HOUR_IN_SECONDS
        ];

        $expiration = self::wpfm_get_feed_expiration($feed_id, $rex_feed_scheduler, $schedule_types);
        $feed_transient_key = "feed_{$feed_id}_batch_{$batch}_feed";
        $product_ids_transient_key = "feed_{$feed_id}_batch_{$batch}_product_ids";
        $feed_update_timestamp = "feed_{$feed_id}_feed_update_timestamp";
        $feed_update_timestamp_gmt = "feed_{$feed_id}_feed_update_timestamp_gmt";

        set_transient($feed_transient_key, $feed, $expiration);
        set_transient($product_ids_transient_key, $products, $expiration);
        set_transient('wpfm_current_feed_product_number', get_option('rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT), $expiration);

        if ($batch == 1) {
            set_transient($feed_update_timestamp, current_time('mysql'), $expiration);
            set_transient($feed_update_timestamp_gmt, current_time('mysql', true), $expiration);
        }
    }

    /**
     * Filesystem branch of cache_feed.
     */
    private static function wpfm_cache_feed_fs($feed_id, $batch, $products, $feed) {
        $dir = self::wpfm_get_cache_dir();
        if ( false === $dir ) {
            return;
        }

        $batch_size = get_option( 'rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT );
        $meta       = wp_json_encode( [ 'ids' => array_values( $products ), 'size' => (int) $batch_size ] );

        file_put_contents( $dir . "/feed-{$feed_id}-batch-{$batch}.cache", $feed );  // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        file_put_contents( $dir . "/feed-{$feed_id}-batch-{$batch}.meta", $meta );   // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
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
     * Get the total size in bytes of all _transient_feed_* rows in wp_options.
     *
     * @return int Size in bytes.
     * @since 7.4.60
     */
    public static function wpfm_get_feed_transient_size() {
        global $wpdb;
        $size = $wpdb->get_var(
            "SELECT SUM(LENGTH(option_value))
             FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_feed_%'"
        );
        return (int) $size;
    }

    /**
     * Delete ALL _transient_feed_* rows from wp_options (both _transient_ and _transient_timeout_ pairs).
     * Safe to run anytime — feed files on disk are unaffected.
     * Skips feeds currently generating.
     *
     * @return int Number of rows deleted.
     * @since 7.4.60
     */
    public static function wpfm_cleanup_all_feed_transients() {
        global $wpdb;

        // Find IDs of feeds currently generating so we can skip them
        $generating_ids = $wpdb->get_col(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_rex_feed_status'
             AND meta_value IN ('processing', 'In queue')"
        );

        if ( empty( $generating_ids ) ) {
            $deleted = $wpdb->query(
                "DELETE FROM {$wpdb->options}
                 WHERE option_name LIKE '_transient_feed_%'
                    OR option_name LIKE '_transient_timeout_feed_%'"
            );
            return (int) $deleted;
        }

        // Build exclusion pattern for generating feeds
        $deleted = 0;
        $rows = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_feed_%'
                OR option_name LIKE '_transient_timeout_feed_%'"
        );

        $to_delete = [];
        foreach ( $rows as $option_name ) {
            if ( preg_match( '/^_transient(?:_timeout)?_feed_(\d+)_/', $option_name, $m ) ) {
                if ( ! in_array( (int) $m[1], array_map( 'intval', $generating_ids ), true ) ) {
                    $to_delete[] = $option_name;
                }
            }
        }

        foreach ( array_chunk( $to_delete, 500 ) as $chunk ) {
            $placeholders = implode( ',', array_fill( 0, count( $chunk ), '%s' ) );
            $deleted += (int) $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name IN ($placeholders)",
                    ...$chunk
                )
            );
        }

        return $deleted;
    }

    /**
     * Delete all transients for a feed ID from wp_options.
     * Removes both _transient_ and _transient_timeout_ row pairs.
     *
     * @param int $feed_id Feed ID
     * @since 7.4.60
     */
    public static function wpfm_delete_feed_transients($feed_id) {
        $feed_id = (int) $feed_id;

        if ( 'filesystem' === self::wpfm_get_cache_driver() ) {
            $dir = self::wpfm_get_cache_dir();
            if ( false !== $dir ) {
                foreach ( glob( $dir . "/feed-{$feed_id}-batch-*.cache" ) ?: [] as $f ) {
                    @unlink( $f ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                }
                foreach ( glob( $dir . "/feed-{$feed_id}-batch-*.meta" ) ?: [] as $f ) {
                    @unlink( $f ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                }
            }
            return;
        }

        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                "_transient_feed_{$feed_id}_%",
                "_transient_timeout_feed_{$feed_id}_%"
            )
        );
    }

    /**
     * Delete transients for batch numbers that no longer exist after a regeneration.
     *
     * @param int $feed_id   Feed ID
     * @param int $new_total New (current) total batch count
     * @param int $old_total Previous total batch count
     * @since 7.4.60
     */
    public static function wpfm_delete_stale_batch_transients($feed_id, $new_total, $old_total) {
        $feed_id   = (int) $feed_id;
        $new_total = (int) $new_total;
        $old_total = (int) $old_total;

        if ( 'filesystem' === self::wpfm_get_cache_driver() ) {
            $dir = self::wpfm_get_cache_dir();
            if ( false !== $dir ) {
                for ( $batch = $new_total + 1; $batch <= $old_total; $batch++ ) {
                    @unlink( $dir . "/feed-{$feed_id}-batch-{$batch}.cache" ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                    @unlink( $dir . "/feed-{$feed_id}-batch-{$batch}.meta" );  // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                }
            }
            return;
        }

        global $wpdb;
        for ($batch = $new_total + 1; $batch <= $old_total; $batch++) {
            $keys = [
                "_transient_feed_{$feed_id}_batch_{$batch}_feed",
                "_transient_timeout_feed_{$feed_id}_batch_{$batch}_feed",
                "_transient_feed_{$feed_id}_batch_{$batch}_product_ids",
                "_transient_timeout_feed_{$feed_id}_batch_{$batch}_product_ids",
            ];
            foreach ($keys as $key) {
                $wpdb->delete($wpdb->options, ['option_name' => $key]);
            }
        }
    }

    /**
     * Check whether a feed is currently being generated.
     *
     * @param int $feed_id Feed ID
     * @return bool
     * @since 7.4.60
     */
    public static function wpfm_is_feed_generating($feed_id) {
        $status = get_post_meta((int) $feed_id, '_rex_feed_status', true);
        return in_array($status, ['processing', 'In queue'], true);
    }

    /**
     * Delete up to $limit orphaned feed transient rows (feed ID absent from wp_posts).
     * Skips feeds currently generating. Deletes both _transient_ and _transient_timeout_ pairs.
     *
     * @param int $limit Max rows to delete per call.
     * @return int Number of rows deleted.
     * @since 7.4.60
     */
    public static function wpfm_purge_orphan_transients_chunk($limit = 500) {
        if ( 'filesystem' === self::wpfm_get_cache_driver() ) {
            return self::wpfm_purge_orphan_fs_chunk( $limit );
        }

        global $wpdb;

        $rows = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_feed_%'
             LIMIT 2000"
        );

        if (empty($rows)) {
            return 0;
        }

        $feed_ids = [];
        foreach ($rows as $option_name) {
            if (preg_match('/^_transient_feed_(\d+)_/', $option_name, $m)) {
                $feed_ids[$m[1]] = (int) $m[1];
            }
        }

        if (empty($feed_ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($feed_ids), '%d'));
        $existing_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE ID IN ($placeholders) AND post_type = 'product-feed'",
                ...array_values($feed_ids)
            )
        );
        $existing_ids = array_map('intval', $existing_ids);

        $orphan_ids = [];
        foreach ($feed_ids as $id) {
            if (!in_array($id, $existing_ids, true) && !self::wpfm_is_feed_generating($id)) {
                $orphan_ids[] = $id;
            }
        }

        if (empty($orphan_ids)) {
            return 0;
        }

        $deleted = 0;
        foreach ($orphan_ids as $orphan_id) {
            if ($deleted >= $limit) {
                break;
            }
            $result = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                    "_transient_feed_{$orphan_id}_%",
                    "_transient_timeout_feed_{$orphan_id}_%"
                )
            );
            $deleted += (int) $result;
        }

        return $deleted;
    }

    /**
     * Filesystem branch of orphan purge.
     */
    private static function wpfm_purge_orphan_fs_chunk($limit = 500) {
        global $wpdb;

        $dir = self::wpfm_get_cache_dir();
        if ( false === $dir ) {
            return 0;
        }

        $meta_files = glob( $dir . '/feed-*-batch-*.meta' ) ?: [];
        if ( empty( $meta_files ) ) {
            return 0;
        }

        $feed_ids = [];
        foreach ( $meta_files as $file ) {
            $basename = basename( $file );
            if ( preg_match( '/^feed-(\d+)-batch-\d+\.meta$/', $basename, $m ) ) {
                $feed_ids[ $m[1] ] = (int) $m[1];
            }
        }

        if ( empty( $feed_ids ) ) {
            return 0;
        }

        $placeholders = implode( ',', array_fill( 0, count( $feed_ids ), '%d' ) );
        $existing_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE ID IN ($placeholders) AND post_type = 'product-feed'",
                ...array_values( $feed_ids )
            )
        );
        $existing_ids = array_map( 'intval', $existing_ids );

        $orphan_ids = [];
        foreach ( $feed_ids as $id ) {
            if ( ! in_array( $id, $existing_ids, true ) && ! self::wpfm_is_feed_generating( $id ) ) {
                $orphan_ids[] = $id;
            }
        }

        if ( empty( $orphan_ids ) ) {
            return 0;
        }

        $deleted = 0;
        foreach ( $orphan_ids as $orphan_id ) {
            if ( $deleted >= $limit ) {
                break;
            }
            foreach ( glob( $dir . "/feed-{$orphan_id}-batch-*.cache" ) ?: [] as $f ) {
                if ( @unlink( $f ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                    $deleted++;
                }
            }
            foreach ( glob( $dir . "/feed-{$orphan_id}-batch-*.meta" ) ?: [] as $f ) {
                if ( @unlink( $f ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                    $deleted++;
                }
            }
        }

        return $deleted;
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