<?php
/**
 * Handles pruning of stale ActionScheduler job records for the wpfm group.
 *
 * @package Rex_Product_Feed
 * @since   7.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Rex_Feed_Job_Cleanup {

    const CRON_HOOK                   = 'wpfm_daily_job_cleanup';
    const BATCH_LIMIT                 = 500;
    const LOG_SOURCE_PRUNING          = 'WPFM_JOB_CLEANUP';
    const LOG_SOURCE_LOCK_REMEDIATION = 'WPFM_LOCK_REMEDIATION';

    /**
     * Register cron event and hook.
     */
    public function init() {
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time(), 'daily', self::CRON_HOOK );
        }
        add_action( self::CRON_HOOK, array( $this, 'run_auto_cleanup' ) );
    }

    /**
     * Auto-cleanup callback — uses saved retention setting and remediates stranded locks.
     */
    public function run_auto_cleanup() {
        $days = absint( get_option( 'wpfm_job_history_retention_days', 30 ) );
        if ( $days < 1 ) {
            $days = 30;
        }

        // 1. Prune historical ActionScheduler log records older than retention threshold.
        $this->cleanup( $days );
        $this->remediate_stranded_feed_locks( 'failed' );
    }

    /**
     * Plugin hook names to target for cleanup.
     * These are scheduled under wpfm / wpfm-feed-* groups.
     */
    private static function get_wpfm_hooks() {
        return array_filter( array(
            defined( 'SINGLE_SCHEDULE_HOOK' )   ? SINGLE_SCHEDULE_HOOK   : 'rex_feed_regenerate_feed_batch',
            defined( 'HOURLY_SCHEDULE_HOOK' )   ? HOURLY_SCHEDULE_HOOK   : 'rex_feed_hourly_update',
            defined( 'DAILY_SCHEDULE_HOOK' )    ? DAILY_SCHEDULE_HOOK    : 'rex_feed_daily_update',
            defined( 'WEEKLY_SCHEDULE_HOOK' )   ? WEEKLY_SCHEDULE_HOOK   : 'rex_feed_weekly_update',
            defined( 'CUSTOM_SCHEDULE_HOOK' )   ? CUSTOM_SCHEDULE_HOOK   : 'rex_feed_custom_update',
            defined( 'WATCHDOG_SCHEDULE_HOOK' ) ? WATCHDOG_SCHEDULE_HOOK : 'rex_feed_watchdog_stuck_feeds',
            defined( 'WC_SINGLE_SCHEDULER' )    ? WC_SINGLE_SCHEDULER    : 'rex_feed_update_abandoned_child_list',
        ) );
    }

    /**
     * Delete complete/failed/cancelled wpfm actions older than $retention_days.
     *
     * Queries by hook name rather than group slug so all wpfm-feed-* groups
     * are covered without a separate DB lookup.
     *
     * @param int $retention_days Minimum age in days.
     * @return int Number of records deleted.
     */
    public function cleanup( $retention_days ) {
        if ( ! function_exists( 'as_get_scheduled_actions' ) || ! class_exists( 'ActionScheduler_Store' ) ) {
            return 0;
        }

        $cutoff  = time() - ( absint( $retention_days ) * DAY_IN_SECONDS );
        $deleted = 0;
        $store   = ActionScheduler_Store::instance();
        $hooks   = self::get_wpfm_hooks();

        foreach ( $hooks as $hook ) {
            foreach ( array( 'complete', 'failed', 'canceled' ) as $status ) {
                $actions = as_get_scheduled_actions( array(
                    'hook'     => $hook,
                    'status'   => $status,
                    'per_page' => self::BATCH_LIMIT,
                    'orderby'  => 'date',
                    'order'    => 'ASC',
                ) );

                foreach ( $actions as $action_id => $action ) {
                    $scheduled_date = $action->get_schedule()->get_date();
                    if ( $scheduled_date && $scheduled_date->getTimestamp() < $cutoff ) {
                        $store->delete_action( $action_id );
                        $deleted++;
                        if ( $deleted >= self::BATCH_LIMIT ) {
                            $this->log(
                                sprintf( 'ActionScheduler log pruning reached batch limit. Deleted %d record(s) older than %d day(s).', $deleted, $retention_days ),
                                self::LOG_SOURCE_PRUNING,
                                'info'
                            );
                            return $deleted;
                        }
                    }
                }
            }
        }

        if ( $deleted > 0 ) {
            $this->log(
                sprintf( 'ActionScheduler log pruning complete. Deleted %d record(s) older than %d day(s).', $deleted, $retention_days ),
                self::LOG_SOURCE_PRUNING,
                'info'
            );
        }

        return $deleted;
    }

    /**
     * Check if a feed has active (in-progress) or pending scheduled actions in ActionScheduler.
     *
     * @param int $feed_id Feed post ID.
     * @return bool True if active/pending tasks exist, false otherwise.
     */
    public static function has_active_feed_actions( $feed_id ) {
        $feed_id = absint( $feed_id );
        if ( ! $feed_id || ! function_exists( 'as_get_scheduled_actions' ) ) {
            return false;
        }

        $group = "wpfm-feed-{$feed_id}";

        $pending_actions = as_get_scheduled_actions( array(
            'group'    => $group,
            'status'   => 'pending',
            'per_page' => 1,
        ) );
        if ( ! empty( $pending_actions ) ) {
            return true;
        }

        $running_actions = as_get_scheduled_actions( array(
            'group'    => $group,
            'status'   => 'in-progress',
            'per_page' => 1,
        ) );
        if ( ! empty( $running_actions ) ) {
            return true;
        }

        return false;
    }

    /**
     * Find feeds stuck in 'processing' or 'In queue' status that have no active/pending AS tasks.
     *
     * @return int[] Array of stranded feed IDs.
     */
    public static function get_stranded_feed_ids() {
        global $wpdb;

        if ( ! $wpdb ) {
            return array();
        }

        $feed_ids = $wpdb->get_col(
            "SELECT DISTINCT p.ID
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'product-feed'
               AND pm.meta_key IN ('_rex_feed_status', 'rex_feed_status')
               AND pm.meta_value IN ('processing', 'In queue')"
        );

        if ( empty( $feed_ids ) ) {
            return array();
        }

        $feed_ids = array_unique( array_map( 'absint', $feed_ids ) );
        $stranded = array();

        foreach ( $feed_ids as $feed_id ) {
            if ( ! $feed_id ) {
                continue;
            }

            if ( ! self::has_active_feed_actions( $feed_id ) ) {
                $stranded[] = $feed_id;
            }
        }

        return $stranded;
    }

    /**
     * Audit feeds with 'processing' or 'In queue' status and release abandoned postmeta locks
     * when no active or pending background tasks exist.
     *
     * @param string $fallback_status Feed status to assign ('failed' or 'cancelled'/'canceled'). Default 'failed'.
     * @return int[] Array of remediated feed post IDs.
     */
    public function remediate_stranded_feed_locks( $fallback_status = 'failed' ) {
        $allowed_statuses = array( 'failed', 'cancelled', 'canceled' );
        if ( ! in_array( $fallback_status, $allowed_statuses, true ) ) {
            $fallback_status = 'failed';
        }

        $stranded_ids = self::get_stranded_feed_ids();
        $remediated   = array();

        if ( empty( $stranded_ids ) ) {
            return $remediated;
        }

        foreach ( $stranded_ids as $feed_id ) {
            $prev_status = get_post_meta( $feed_id, '_rex_feed_status', true ) ?: get_post_meta( $feed_id, 'rex_feed_status', true );

            // 1. Release feed status lock and update to fallback status.
            if ( class_exists( 'Rex_Product_Feed_Controller' ) ) {
                Rex_Product_Feed_Controller::update_feed_status( $feed_id, $fallback_status, false );
            } else {
                update_post_meta( $feed_id, '_rex_feed_status', $fallback_status );
                delete_post_meta( $feed_id, 'rex_feed_status' );
            }

            // 2. Release in-flight generation locks.
            delete_post_meta( $feed_id, '_generation_start_time' );
            delete_post_meta( $feed_id, '_rex_feed_last_active_time' );

            // 3. Fail and release product count guard run if present.
            if ( class_exists( 'Rex_Feed_Product_Count_Guard' ) ) {
                Rex_Feed_Product_Count_Guard::fail_run(
                    $feed_id,
                    'abandoned_lock',
                    __( 'Abandoned feed generation lock released during maintenance sweep.', 'rex-product-feed' )
                );
            }
            delete_post_meta( $feed_id, '_rex_feed_product_count_run' );

            // 4. Cancel any remaining actions in ActionScheduler.
            if ( function_exists( 'as_unschedule_all_actions' ) ) {
                as_unschedule_all_actions( '', array(), "wpfm-feed-{$feed_id}" );
            }

            $remediated[] = $feed_id;

            $this->log(
                sprintf(
                    'Released abandoned postmeta lock for Feed #%d (previous status: "%s", new status: "%s").',
                    $feed_id,
                    $prev_status ? $prev_status : 'unknown',
                    $fallback_status
                ),
                self::LOG_SOURCE_LOCK_REMEDIATION,
                'warning'
            );
        }

        $this->log(
            sprintf(
                'Stranded postmeta lock remediation sweep completed. Released locks for %d feed(s): [%s].',
                count( $remediated ),
                implode( ', ', $remediated )
            ),
            self::LOG_SOURCE_LOCK_REMEDIATION,
            'info'
        );

        return $remediated;
    }

    /**
     * Immediately delete all complete-status rex_feed_regenerate_feed_batch actions
     * for a specific feed group. Called right after feed generation finishes so stale
     * batch records don't accumulate until the daily cron runs.
     *
     * Failed/pending actions are intentionally skipped.
     *
     * @param int $feed_id
     * @return int Number of records deleted.
     */
    public static function cleanup_feed_batch_jobs( $feed_id ) {
        if ( ! function_exists( 'as_get_scheduled_actions' ) || ! class_exists( 'ActionScheduler_Store' ) ) {
            return 0;
        }

        $feed_id = absint( $feed_id );
        if ( ! $feed_id ) {
            return 0;
        }

        $hook    = defined( 'SINGLE_SCHEDULE_HOOK' ) ? SINGLE_SCHEDULE_HOOK : 'rex_feed_regenerate_feed_batch';
        $store   = ActionScheduler_Store::instance();
        $deleted = 0;

        $actions = as_get_scheduled_actions( array(
            'hook'     => $hook,
            'group'    => "wpfm-feed-{$feed_id}",
            'status'   => 'complete',
            'per_page' => self::BATCH_LIMIT,
        ) );

        foreach ( $actions as $action_id => $action ) {
            $store->delete_action( $action_id );
            $deleted++;
        }

        return $deleted;
    }

    /**
     * Unschedule the daily cron event.
     */
    public static function deregister() {
        wp_clear_scheduled_hook( self::CRON_HOOK );
    }

    /**
     * Helper to log messages when logging is enabled.
     *
     * @param string $message Log message.
     * @param string $source  Log source identifier.
     * @param string $level   Log level ('info', 'warning', 'error', etc.).
     * @return void
     */
    protected function log( $message, $source, $level = 'info' ) {
        if ( ! function_exists( 'is_wpfm_logging_enabled' ) || ! is_wpfm_logging_enabled() || ! function_exists( 'wc_get_logger' ) ) {
            return;
        }

        $logger = wc_get_logger();
        if ( ! method_exists( $logger, $level ) ) {
            $level = 'info';
        }

        $logger->$level( $message, array( 'source' => $source ) );
    }
}
