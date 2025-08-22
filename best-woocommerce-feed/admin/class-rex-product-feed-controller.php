<?php
/**
 * Class Rex_Product_Feed_Controller
 *
 * @link       https://rextheme.com
 * @since      2.0.0
 *
 * @package    Rex_Product_Feed_Controller
 * @subpackage Rex_Product_Feed/admin
 */

/**
 * The Rex_Product_Feed_Controller class file that
 * control the feed
 *
 * @link       https://rextheme.com
 * @since      2.0.0
 *
 * @package    Rex_Product_Feed_Controller
 * @subpackage Rex_Product_Feed/admin
 */
class Rex_Product_Feed_Controller {
	/**
	 * Update feed status
	 *
	 * @param string $feed_id Feed ID.
	 * @param string $status Feed status.
	 */
    public static function update_feed_status( $feed_id, $status ) {
        delete_post_meta( $feed_id, 'rex_feed_status' );
        update_post_meta( $feed_id, '_rex_feed_status', $status );
        if ( 'completed' === $status ) {
            $is_scheduled_run = (
                ( function_exists( 'as_get_scheduled_actions' ) && did_action( 'action_scheduler_run_queue' ) ) ||
                ( defined( 'DOING_CRON' ) && DOING_CRON ) ||
                get_post_meta( $feed_id, '_generation_start_time', true )
            );

            if ( $is_scheduled_run ) {
                do_action( 'rex_product_feed_scheduler_generate', $feed_id );
                delete_post_meta( $feed_id, '_generation_start_time' );
            }
        }
    }
}
