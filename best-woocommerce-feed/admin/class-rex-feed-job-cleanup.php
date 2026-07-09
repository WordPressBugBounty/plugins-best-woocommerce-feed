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

    const CRON_HOOK   = 'wpfm_daily_job_cleanup';
    const BATCH_LIMIT = 500;

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
     * Auto-cleanup callback — uses saved retention setting.
     */
    public function run_auto_cleanup() {
        $days = absint( get_option( 'wpfm_job_history_retention_days', 30 ) );
        if ( $days < 1 ) {
            $days = 30;
        }
        $this->cleanup( $days );
    }

    /**
     * Plugin hook names to target for cleanup.
     * These are scheduled under wpfm / wpfm-feed-* groups.
     */
    private static function get_wpfm_hooks() {
        return array_filter( array(
            defined( 'SINGLE_SCHEDULE_HOOK' )  ? SINGLE_SCHEDULE_HOOK  : 'rex_feed_regenerate_feed_batch',
            defined( 'HOURLY_SCHEDULE_HOOK' )  ? HOURLY_SCHEDULE_HOOK  : 'rex_feed_hourly_update',
            defined( 'DAILY_SCHEDULE_HOOK' )   ? DAILY_SCHEDULE_HOOK   : 'rex_feed_daily_update',
            defined( 'WEEKLY_SCHEDULE_HOOK' )  ? WEEKLY_SCHEDULE_HOOK  : 'rex_feed_weekly_update',
            defined( 'CUSTOM_SCHEDULE_HOOK' )  ? CUSTOM_SCHEDULE_HOOK  : 'rex_feed_custom_update',
            defined( 'WC_SINGLE_SCHEDULER' )   ? WC_SINGLE_SCHEDULER   : 'rex_feed_update_abandoned_child_list',
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
                            return $deleted;
                        }
                    }
                }
            }
        }

        return $deleted;
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
}
