<?php

namespace RexTheme\Analytics\Services;

use RexTheme\Analytics\Attribution\OrderAttribution;
use RexTheme\Analytics\Currency\CurrencyGuard;
use RexTheme\Analytics\EmptyStates\EmptyStateResolver;
use RexTheme\Analytics\Repositories\OrderRepository;

/**
 * Only counts orders carrying UTM data (design.md Decision 10 — reverses the
 * original channel-agnostic design). Computed directly from `WC_Order`
 * objects/line items in range — see OverviewService's doc comment for why
 * this doesn't use `wc_order_product_lookup` (design.md Decision 15: that
 * table's sync lag showed an empty Top Products on a store's very first,
 * already-completed order). Product names are still batch-fetched (one
 * `wc_get_products()` call for every ranked product, not N+1).
 *
 * The `wc_order_product_lookup` GROUP-BY approach this replaced remains a
 * valid option for stores at genuinely large order volume where per-order
 * PHP iteration would be too slow — but it must not be the unconditional
 * default given the sync-lag correctness problem it demonstrated on a
 * brand-new store. Revisit as an opt-in ("large store mode") if real usage
 * shows PHP iteration is too slow in practice, not before.
 */
class TopProductsService {

	const LIMIT = 10;

	private OrderRepository $orders;

	public function __construct( OrderRepository $orders ) {
		$this->orders = $orders;
	}

	public function get_data( string $start, string $end ): array {
		$orders   = $this->orders->get_paid_orders_in_range( $start, $end );
		$filtered = CurrencyGuard::filter_orders( $orders );
		$has_any_orders = ! empty( $filtered['kept'] );

		$utm_orders = array_filter(
			$filtered['kept'],
			static function ( \WC_Order $order ) {
				$utm = OrderAttribution::raw_utm( $order );
				return '' !== $utm['source'] || '' !== $utm['medium'] || '' !== $utm['campaign'] || '' !== $utm['term'];
			}
		);
		$has_utm_orders = ! empty( $utm_orders );

		$products = $this->rank_products( $utm_orders );

		return array(
			'products'    => $this->attach_product_names( $products ),
			'currency'    => CurrencyGuard::base_currency(),
			'empty_state' => EmptyStateResolver::top_products( $has_any_orders, $has_utm_orders ),
		);
	}

	/**
	 * @param \WC_Order[] $orders
	 * @return array<int, array{product_id: int, orders: int, revenue: float}>
	 */
	private function rank_products( array $orders ): array {
		$products = array();

		foreach ( $orders as $order ) {
			$seen = array();
			foreach ( $order->get_items( 'line_item' ) as $item ) {
				/** @var \WC_Order_Item_Product $item */
				$product_id = $item->get_product_id();
				if ( ! $product_id ) {
					continue;
				}
				if ( ! isset( $products[ $product_id ] ) ) {
					$products[ $product_id ] = array(
						'product_id' => $product_id,
						'orders'     => 0,
						'revenue'    => 0.0,
					);
				}
				$products[ $product_id ]['revenue'] += (float) $item->get_total();
				if ( ! isset( $seen[ $product_id ] ) ) {
					$products[ $product_id ]['orders']++;
					$seen[ $product_id ] = true;
				}
			}
		}

		foreach ( $products as &$product ) {
			$product['revenue'] = round( $product['revenue'], 2 );
		}
		unset( $product );

		usort( $products, static fn( $a, $b ) => $b['revenue'] <=> $a['revenue'] );

		return array_slice( array_values( $products ), 0, self::LIMIT );
	}

	/**
	 * @param array<int, array{product_id: int, orders: int, revenue: float}> $rows
	 * @return array<int, array{product_id: int, name: string, orders: int, revenue: float}>
	 */
	private function attach_product_names( array $rows ): array {
		if ( empty( $rows ) ) {
			return array();
		}

		$product_ids = array_column( $rows, 'product_id' );
		$products    = wc_get_products(
			array(
				'include'          => $product_ids,
				'limit'            => -1,
				'return'           => 'objects',
				'suppress_filters' => true,
			)
		);

		$names = array();
		foreach ( $products as $product ) {
			$names[ $product->get_id() ] = $product->get_name();
		}

		return array_map(
			static function ( $row ) use ( $names ) {
				$name = $names[ $row['product_id'] ] ?? get_the_title( $row['product_id'] );
				$row['name'] = ! empty( $name ) ? $name : sprintf(
					/* translators: %d: deleted product's ID. */
					__( 'Deleted product #%d', 'rex-product-feed' ),
					$row['product_id']
				);
				return $row;
			},
			$rows
		);
	}
}
