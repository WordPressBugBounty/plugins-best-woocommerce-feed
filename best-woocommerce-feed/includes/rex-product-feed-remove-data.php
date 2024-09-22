<?php
if ( !function_exists( 'rex_feed_remove_plugin_data' ) ) {
	/**
	 * @desc Remove all plugin data from database on uninstalling/deleting plugin
	 * @return void
	 */
	function rex_feed_remove_plugin_data() {
		global $wpdb;
		$wpfm_remove_plugin_data = get_option( 'wpfm_remove_plugin_data', 'no' );
		delete_option( 'wpfm_remove_plugin_data' );

		if ( $wpfm_remove_plugin_data === 'yes' ) {
			$feed_ids = get_posts(
				array(
					'numberposts' => -1,
					'fields'      => 'ids',
					'post_type'   => array( 'product-feed' ),
					'post_status' => array( 'publish', 'auto-draft', 'trash', 'pending', 'draft' ),
				)
			);
			foreach ( $feed_ids as $feed_id ) {
				wp_delete_post( $feed_id, true );
			}

			$query   = "SELECT `option_name` FROM {$wpdb->prefix}options WHERE `option_name` LIKE '%{$wpdb->esc_like('rex_feed')}%' ";
			$query  .= "OR `option_name` LIKE '%{$wpdb->esc_like('bwfm')}%' ";
			$query  .= "OR `option_name` LIKE '%{$wpdb->esc_like('rex-wpfm')}%' ";
			$query  .= "OR `option_name` LIKE '%{$wpdb->esc_like('rex-feed')}%' ";
			$query  .= "OR `option_name` LIKE '%{$wpdb->esc_like('best-woocommerce-feed')}%' ";
			$query  .= "OR `option_name` LIKE '%{$wpdb->esc_like('wpfm')}%'";
			$options = $wpdb->get_results( $query );
			$options = array_column( $options, 'option_name' );
			foreach ( $options as $option ) {
				delete_option( $option );
			}

			$query     = "SELECT `meta_key` FROM {$wpdb->prefix}postmeta WHERE `meta_key` LIKE '{$wpdb->esc_like('_wpfm_product_')}%'";
			$meta_keys = $wpdb->get_results( $query );
			$meta_keys = array_column( $meta_keys, 'meta_key' );
			foreach ( $meta_keys as $meta_key ) {
				delete_post_meta_by_key( $meta_key );
			}
		}
	}
}
if ( function_exists( 'rex_feed_remove_plugin_data' ) ) {
	rex_feed_remove_plugin_data();
}
