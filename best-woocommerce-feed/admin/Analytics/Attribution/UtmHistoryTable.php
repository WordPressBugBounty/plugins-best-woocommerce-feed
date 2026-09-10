<?php

namespace RexTheme\Analytics\Attribution;

/**
 * Schema management for the `utm_term`-to-feed attribution-history table.
 * One row per `{value, feed_id}` window: `effective_to IS NULL` means the
 * window is still open (the value is currently in effect for that feed).
 *
 * Created lazily (option-version-gated dbDelta check) rather than only on
 * plugin (re)activation, so existing installs get the table on their next
 * page load after upgrading, without requiring a deactivate/reactivate.
 */
class UtmHistoryTable {

	const SCHEMA_VERSION = '1.0';
	const OPTION_KEY     = 'wpfm_utm_history_table_version';

	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'wpfm_utm_history';
	}

	public static function maybe_create(): void {
		if ( get_option( self::OPTION_KEY ) === self::SCHEMA_VERSION ) {
			return;
		}
		self::create();
		update_option( self::OPTION_KEY, self::SCHEMA_VERSION );
	}

	private static function create(): void {
		global $wpdb;

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$table_name      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			value VARCHAR(191) NOT NULL,
			feed_id BIGINT UNSIGNED NOT NULL,
			effective_from DATETIME NOT NULL,
			effective_to DATETIME NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY value (value),
			KEY feed_id (feed_id)
		) {$charset_collate};";

		dbDelta( $sql );
	}
}
