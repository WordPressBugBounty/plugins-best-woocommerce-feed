<?php
/**
 * Class Rex_Product_Appsero_Data
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin
 */

/**
 * This class is responsible to modify appsero tracking data
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin
 */
class Rex_Product_Appsero_Data {

	/**
	 * Update appsero tracking data and
	 * send feed merchant list.
	 *
	 * @param array $tracking_data Appsero tracking data.
	 *
	 * @return array
	 * @since 7.2.15
	 */
	public function send_merchant_info( $tracking_data ) {
		$merchants  = array();
		$feed_title = array();
		$args       = array(
			'post_type'        => 'product-feed',
			'post_status'      => 'publish',
			'suppress_filters' => true,
			'posts_per_page'   => -1,
			'fields'           => 'ids',
		);
		$feed_ids   = get_posts( $args );

		if ( !empty( $feed_ids ) ) {
			foreach ( $feed_ids as $id ) {
				$merchants[]  = get_post_meta( $id, '_rex_feed_merchant', true ) ?: get_post_meta( $id, 'rex_feed_merchant', true );
				$feed_title[] = get_the_title( $id );
			}
		}
		$tracking_data[ 'extra' ][ 'rex_feed_merchant' ] = implode( ', ', array_unique( $merchants ) );
		$tracking_data[ 'extra' ][ 'rex_feed_title' ]    = implode( ', ', array_unique( $feed_title ) );
		return $tracking_data;
	}
}
