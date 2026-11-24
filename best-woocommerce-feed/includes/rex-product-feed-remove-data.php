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
if ( ! function_exists( 'rex_feed_remove_smart_category_data' ) ) {
	/**
	 * Remove smart category mapping data on uninstall.
	 *
	 * Runs whether or not the user chose to remove plugin data: the stats and learned values are internal
	 * suggestion feedback, and the transients and downloaded taxonomy files are a rebuildable cache, not user settings.
	 * Plugin classes are not loaded during uninstall, so this is the only place that removes this data.
	 *
	 * @since 7.14.4
	 *
	 * @return void
	 */
	function rex_feed_remove_smart_category_data() {
		global $wpdb;

		delete_option( 'wpfm_smart_category_stats' );
		delete_option( 'wpfm_smart_category_learned' );
		if ( function_exists( 'wp_unschedule_hook' ) ) {
			wp_unschedule_hook( 'wpfm_smart_category_fetch_taxonomy' );
		}

		$like = $wpdb->esc_like( '_transient_wpfm_smart_cat_taxfail_' ) . '%';
		$keys = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( (array) $keys as $key ) {
			delete_transient( substr( $key, strlen( '_transient_' ) ) );
		}

		// Downloaded localized taxonomy files.
		$uploads = function_exists( 'wp_upload_dir' ) ? wp_upload_dir( null, false ) : array();
		if ( is_array( $uploads ) && empty( $uploads['error'] ) && ! empty( $uploads['basedir'] ) ) {
			$dir = trailingslashit( $uploads['basedir'] ) . 'wpfm-smart-category';
			if ( is_dir( $dir ) ) {
				foreach ( (array) glob( $dir . '/*' ) as $file ) {
					if ( is_string( $file ) && is_file( $file ) ) {
						@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
					}
				}
				@rmdir( $dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
			}
		}

		$like = $wpdb->esc_like( '_transient_wpfm_smart_cat_index_' ) . '%';
		$keys = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( (array) $keys as $key ) {
			delete_transient( substr( $key, strlen( '_transient_' ) ) );
		}
	}
}
if ( function_exists( 'rex_feed_remove_smart_category_data' ) ) {
	rex_feed_remove_smart_category_data();
}
if ( function_exists( 'rex_feed_remove_plugin_data' ) ) {
	rex_feed_remove_plugin_data();
}
