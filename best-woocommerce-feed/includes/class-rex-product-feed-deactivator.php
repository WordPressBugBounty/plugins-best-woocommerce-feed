<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/includes
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Product_Feed_Deactivator {

    /**
     * @desc Complete all the required task in this function
     * before deactivating plugin
     * @return void
     * @since 1.0.0
     */
    public static function deactivate() {
        self::deregister_background_schedulers();
        self::update_feed_status();
    }

    /**
     * @desc Deregister all the background schedules
     * before deactivating plugin
     * @return void
     * @since 7.3.0
     */
    private static function deregister_background_schedulers() {
        if( function_exists( 'as_unschedule_all_actions' ) ) {
            as_unschedule_all_actions( SINGLE_SCHEDULE_HOOK );
        }
        if( function_exists( 'as_unschedule_action' ) ) {
            as_unschedule_action( HOURLY_SCHEDULE_HOOK, [], 'wpfm' );
            as_unschedule_action( DAILY_SCHEDULE_HOOK, [], 'wpfm' );
            as_unschedule_action( WEEKLY_SCHEDULE_HOOK, [], 'wpfm' );
        }
    }

    /**
     * Update feed status to cancel after deregistering all active schedules
     *
     * @return void
     */
    private static function update_feed_status() {
        global $wpdb;
        try {
            $wpdb->delete(
                $wpdb->postmeta,
                [ 'meta_key' => 'rex_feed_status' ],
            );

            $wpdb->update(
                $wpdb->postmeta,
                [ 'meta_value' => 'canceled' ],
                [ 'meta_key' => '_rex_feed_status' ],
            );
        }
        catch( Exception $e ) {
            if( is_wpfm_logging_enabled() ) {
                $log = wc_get_logger();
                $log->warning( print_r( $e->getMessage(), 1 ), array( 'source' => 'WPFM_DEACTIVATION_ERROR' ) );
            }
        }
    }
}
