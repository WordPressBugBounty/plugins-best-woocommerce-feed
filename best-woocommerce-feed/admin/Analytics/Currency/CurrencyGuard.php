<?php

namespace RexTheme\Analytics\Currency;

/**
 * Multi-currency handling for Analytics (design.md Decision 7): no per-order
 * historical exchange rate exists for any of WPML/WCML, Aelia, Curcy, or
 * WOOCS, so revenue is never converted or blended across currencies. Every
 * revenue/AOV figure only aggregates orders placed in the store's configured
 * base currency; orders in any other currency are excluded and disclosed via
 * an explicit count, never silently dropped.
 *
 * Cost containment: the SQL-side helpers only add a currency join when a
 * multi-currency plugin is actually detected active — on a single-currency
 * store (the overwhelming majority) this costs nothing.
 */
class CurrencyGuard {

	public static function is_multi_currency_active(): bool {
		if ( function_exists( 'wpfm_is_wcml_active' ) && wpfm_is_wcml_active() ) {
			return true;
		}
		if ( function_exists( 'wpfm_is_curcy_active' ) && wpfm_is_curcy_active() ) {
			return true;
		}
		if ( defined( 'WOOCS_VERSION' ) ) {
			return true;
		}
		if ( class_exists( 'WC_Aelia_CurrencySwitcher' ) ) {
			return true;
		}
		if ( class_exists( 'WOOMULTI_CURRENCY_Data' ) ) {
			return true;
		}
		if ( class_exists( 'WCPay\MultiCurrency\MultiCurrency' ) ) {
			return true;
		}
		return false;
	}

	public static function base_currency(): string {
		return get_woocommerce_currency();
	}

	/**
	 * Split an in-memory list of WC_Order objects into base-currency orders
	 * and a count of excluded (non-base-currency) orders.
	 *
	 * @param \WC_Order[] $orders
	 * @return array{kept: \WC_Order[], excluded: int}
	 */
	public static function filter_orders( array $orders ): array {
		$base     = self::base_currency();
		$kept     = array();
		$excluded = 0;

		foreach ( $orders as $order ) {
			if ( $order->get_currency() === $base ) {
				$kept[] = $order;
			} else {
				$excluded++;
			}
		}

		return array(
			'kept'     => $kept,
			'excluded' => $excluded,
		);
	}

	/**
	 * SQL fragment (with its own bound value) restricting a query against
	 * `wc_order_stats`/`wc_order_product_lookup` (both keyed by `order_id`) to
	 * base-currency orders. Empty string when no multi-currency plugin is
	 * active — callers should treat an empty fragment as "no filtering
	 * needed", not concatenate it blindly.
	 *
	 * @return array{sql: string, params: array}
	 */
	public static function order_id_currency_fragment(): array {
		if ( ! self::is_multi_currency_active() ) {
			return array(
				'sql'    => '',
				'params' => array(),
			);
		}

		global $wpdb;
		$base = self::base_currency();

		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			return array(
				'sql'    => " AND order_id IN ( SELECT id FROM {$wpdb->prefix}wc_orders WHERE currency = %s ) ",
				'params' => array( $base ),
			);
		}

		return array(
			'sql'    => " AND order_id IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_order_currency' AND meta_value = %s ) ",
			'params' => array( $base ),
		);
	}

	/**
	 * Count of orders in `wc_order_stats` within the given range that are
	 * NOT in the base currency — for the "N orders excluded" disclosure.
	 * Always 0 (and never queries) when no multi-currency plugin is active.
	 */
	public static function count_excluded_in_stats_range( string $start, string $end ): int {
		if ( ! self::is_multi_currency_active() ) {
			return 0;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'wc_order_stats';
		$base  = self::base_currency();

		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$orders_table = $wpdb->prefix . 'wc_orders';
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table names are constants derived from $wpdb->prefix.
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} s
					INNER JOIN {$orders_table} o ON o.id = s.order_id
					WHERE s.date_created BETWEEN %s AND %s AND o.currency != %s",
					$start . ' 00:00:00',
					$end . ' 23:59:59',
					$base
				)
			);
			return (int) $count;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table names are constants derived from $wpdb->prefix.
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} s
				INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = s.order_id AND pm.meta_key = '_order_currency'
				WHERE s.date_created BETWEEN %s AND %s AND pm.meta_value != %s",
				$start . ' 00:00:00',
				$end . ' 23:59:59',
				$base
			)
		);
		return (int) $count;
	}
}
