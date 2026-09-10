<?php

namespace RexTheme\Analytics\Repositories;

/**
 * Thin wrapper over WC_Order_Query (via wc_get_orders()) so every Analytics
 * service fetches paid orders in a date range the same way, paginated
 * internally to avoid loading an entire store's order history into memory
 * at once. HPOS-safe: goes through WooCommerce's own CRUD, not raw SQL
 * against posts or the orders table directly.
 */
class OrderRepository {

	const PAGE_SIZE = 200;

	/**
	 * @param string $start Y-m-d, inclusive, site local time.
	 * @param string $end   Y-m-d, inclusive, site local time.
	 * @return \WC_Order[]
	 */
	public function get_paid_orders_in_range( string $start, string $end ): array {
		$statuses = wc_get_is_paid_statuses();
		$date_arg = $start . '...' . $end . ' 23:59:59';

		$orders = array();
		$page   = 1;

		do {
			$batch = wc_get_orders(
				array(
					'status'           => $statuses,
					'date_created'     => $date_arg,
					'limit'            => self::PAGE_SIZE,
					'page'             => $page,
					'orderby'          => 'date',
					'order'            => 'ASC',
					'return'           => 'objects',
					'type'             => 'shop_order',
					'suppress_filters' => true,
				)
			);

			if ( empty( $batch ) ) {
				break;
			}

			$orders = array_merge( $orders, $batch );
			$page++;
		} while ( count( $batch ) === self::PAGE_SIZE );

		return $orders;
	}
}
