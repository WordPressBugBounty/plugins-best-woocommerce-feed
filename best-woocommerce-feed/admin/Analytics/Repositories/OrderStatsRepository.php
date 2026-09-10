<?php

namespace RexTheme\Analytics\Repositories;

use RexTheme\Analytics\Currency\CurrencyGuard;

/**
 * Reads WooCommerce's own `wc_order_stats` report table instead of hydrating
 * `WC_Order` objects — `net_total` is already refund-adjusted by WC core's
 * own Analytics sync (active on every WC install since the 4.0-era Analytics
 * rewrite, independent of whether the Analytics UI is used), and a single
 * grouped query avoids loading every order into PHP for a date range that
 * can span thousands of orders (design.md Decision 8).
 *
 * Falls back to null (callers fall back to `OrderRepository`-based
 * computation) if the table is unexpectedly missing rather than hard-erroring
 * — defensive against unusual installs, not expected in practice on WC 10.7.
 */
class OrderStatsRepository {

	private static ?bool $table_exists = null;

	public function table_available(): bool {
		if ( null !== self::$table_exists ) {
			return self::$table_exists;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'wc_order_stats';
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a constant derived from $wpdb->prefix.
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		self::$table_exists = ( $found === $table );
		return self::$table_exists;
	}

	private function paid_status_list(): array {
		return array_map(
			static fn( $status ) => 'wc-' . $status,
			wc_get_is_paid_statuses()
		);
	}

	/**
	 * @return array{revenue: float, orders: int}
	 */
	public function get_totals( string $start, string $end ): array {
		global $wpdb;
		$table    = $wpdb->prefix . 'wc_order_stats';
		$statuses = $this->paid_status_list();
		$in       = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
		$currency = CurrencyGuard::order_id_currency_fragment();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a constant derived from $wpdb->prefix; $in/$currency['sql'] are fixed fragments, not user input.
		$sql = $wpdb->prepare(
			"SELECT COUNT(*) AS orders, COALESCE(SUM(net_total), 0) AS revenue
			FROM {$table}
			WHERE date_created BETWEEN %s AND %s
			AND status IN ({$in})
			{$currency['sql']}",
			array_merge(
				array( $start . ' 00:00:00', $end . ' 23:59:59' ),
				$statuses,
				$currency['params']
			)
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql built via $wpdb->prepare() above.
		$row = $wpdb->get_row( $sql, ARRAY_A );

		return array(
			'revenue' => $row ? round( (float) $row['revenue'], 2 ) : 0.0,
			'orders'  => $row ? (int) $row['orders'] : 0,
		);
	}

	/**
	 * Batch-fetch refund-adjusted `net_total` for a specific set of order
	 * IDs — used by services that must iterate orders in PHP for custom
	 * attribution logic (Feed Performance, Traffic-by-Source) but still want
	 * refund-accurate revenue without repeating `get_total_refunded()` calls.
	 *
	 * @param int[] $order_ids
	 * @return array<int, float> order_id => net_total
	 */
	public function get_net_totals_by_order_id( array $order_ids ): array {
		if ( empty( $order_ids ) || ! $this->table_available() ) {
			return array();
		}

		global $wpdb;
		$table        = $wpdb->prefix . 'wc_order_stats';
		$placeholders = implode( ',', array_fill( 0, count( $order_ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a constant derived from $wpdb->prefix; $placeholders is a fixed fragment sized to $order_ids, not user input.
		$sql = $wpdb->prepare(
			"SELECT order_id, net_total FROM {$table} WHERE order_id IN ({$placeholders})",
			$order_ids
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql built via $wpdb->prepare() above.
		$rows = $wpdb->get_results( $sql, ARRAY_A );

		$map = array();
		foreach ( $rows ?: array() as $row ) {
			$map[ (int) $row['order_id'] ] = (float) $row['net_total'];
		}

		return $map;
	}

	/**
	 * Per-day {date, revenue, orders} rows for the range (only days with at
	 * least one order — caller fills the zero-order days).
	 *
	 * @return array<int, array{date: string, revenue: float, orders: int}>
	 */
	public function get_daily_series( string $start, string $end ): array {
		global $wpdb;
		$table    = $wpdb->prefix . 'wc_order_stats';
		$statuses = $this->paid_status_list();
		$in       = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
		$currency = CurrencyGuard::order_id_currency_fragment();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a constant derived from $wpdb->prefix; $in/$currency['sql'] are fixed fragments, not user input.
		$sql = $wpdb->prepare(
			"SELECT DATE(date_created) AS day, COUNT(*) AS orders, COALESCE(SUM(net_total), 0) AS revenue
			FROM {$table}
			WHERE date_created BETWEEN %s AND %s
			AND status IN ({$in})
			{$currency['sql']}
			GROUP BY DATE(date_created)",
			array_merge(
				array( $start . ' 00:00:00', $end . ' 23:59:59' ),
				$statuses,
				$currency['params']
			)
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql built via $wpdb->prepare() above.
		$rows = $wpdb->get_results( $sql, ARRAY_A );

		return array_map(
			static fn( $row ) => array(
				'date'    => $row['day'],
				'revenue' => round( (float) $row['revenue'], 2 ),
				'orders'  => (int) $row['orders'],
			),
			$rows ?: array()
		);
	}
}
