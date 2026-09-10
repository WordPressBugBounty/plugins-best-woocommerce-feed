<?php

namespace RexTheme\Analytics\Services;

use Rex_Feed_Merchants;
use RexTheme\Analytics\Attribution\AttributionResolver;
use RexTheme\Analytics\Attribution\OrderAttribution;
use RexTheme\Analytics\Currency\CurrencyGuard;
use RexTheme\Analytics\EmptyStates\EmptyStateResolver;
use RexTheme\Analytics\Repositories\OrderRepository;
use RexTheme\Analytics\Repositories\OrderStatsRepository;

/**
 * Feed-level only, published feeds only (design.md Decision 9) — attributes
 * each order, at the order level, to whichever feed its captured `utm_term`
 * resolves to (never by iterating which feeds statically contain a product —
 * avoids double-counting when the same product is listed in multiple feeds).
 * No channel-level fallback row: that signal lives in `TrafficBySourceService`
 * instead. Rows for `not_applicable`-flagged merchants are omitted entirely;
 * `uncertain` rows carry an explicit caveat.
 */
class FeedPerformanceService {

	private OrderRepository $orders;
	private OrderStatsRepository $stats;

	public function __construct( OrderRepository $orders, OrderStatsRepository $stats ) {
		$this->orders = $orders;
		$this->stats  = $stats;
	}

	public function get_data( string $start, string $end ): array {
		$orders   = $this->orders->get_paid_orders_in_range( $start, $end );
		$filtered = CurrencyGuard::filter_orders( $orders );
		$orders   = $filtered['kept'];

		$feed_orders      = array(); // feed_id => WC_Order[]
		$feed_resolved    = 0;
		$channel_resolved = 0;

		foreach ( $orders as $order ) {
			$utm = OrderAttribution::raw_utm( $order );
			if ( '' === $utm['term'] ) {
				continue;
			}

			$date_created = $order->get_date_created();
			$order_date   = $date_created ? $date_created->date( 'Y-m-d H:i:s' ) : current_time( 'mysql', true );
			$resolution   = AttributionResolver::resolve( $utm['term'], $order_date );

			if ( 'resolved' === $resolution['status'] ) {
				$feed_resolved++;
				$feed_id = (int) $resolution['feed_id'];
				$feed_orders[ $feed_id ][] = $order;
				continue;
			}

			if ( '' !== $utm['source'] ) {
				$channel_resolved++; // Counted for empty-state purposes only — no row is rendered for it here.
			}
		}

		$net_totals = $this->stats->get_net_totals_by_order_id( $this->all_order_ids( $feed_orders ) );

		$rows = $this->finalize_feed_rows( $feed_orders, $net_totals );
		$rows = array_merge( $rows, $this->awaiting_crawl_rows( array_column( $rows, 'feed_id' ) ) );
		usort( $rows, static fn( $a, $b ) => $b['revenue'] <=> $a['revenue'] );

		return array(
			'rows'                     => $rows,
			'currency'                 => CurrencyGuard::base_currency(),
			'currency_excluded_orders' => $filtered['excluded'],
			'empty_state'              => EmptyStateResolver::feed_performance(
				$orders,
				$feed_resolved,
				$channel_resolved,
				$this->is_portfolio_entirely_non_attributable()
			),
		);
	}

	/**
	 * @param array<int, \WC_Order[]> $feed_orders
	 * @return int[]
	 */
	private function all_order_ids( array $feed_orders ): array {
		$ids = array();
		foreach ( $feed_orders as $orders ) {
			foreach ( $orders as $order ) {
				$ids[] = $order->get_id();
			}
		}
		return $ids;
	}

	private function order_revenue( \WC_Order $order, array $net_totals ): float {
		$order_id = $order->get_id();
		if ( isset( $net_totals[ $order_id ] ) ) {
			return $net_totals[ $order_id ];
		}
		// Fallback if wc_order_stats is unexpectedly missing that order.
		return (float) $order->get_total() - (float) $order->get_total_refunded();
	}

	/**
	 * Resolve each accumulated feed_id into a display row — feed-level only,
	 * published feeds only, applying the merchant attribution-capability flag
	 * and deleted-feed handling.
	 *
	 * @param array<int, \WC_Order[]> $feed_orders
	 */
	private function finalize_feed_rows( array $feed_orders, array $net_totals ): array {
		$rows = array();

		foreach ( $feed_orders as $feed_id => $orders ) {
			$feed_post = get_post( $feed_id );
			$revenue   = 0.0;
			foreach ( $orders as $order ) {
				$revenue += $this->order_revenue( $order, $net_totals );
			}
			$revenue = round( $revenue, 2 );

			if ( ! $feed_post || 'product-feed' !== $feed_post->post_type ) {
				$rows[] = array(
					'feed_id'         => $feed_id,
					/* translators: %d: deleted feed's post ID. */
					'feed_or_channel' => sprintf( __( 'Deleted feed #%d', 'rex-product-feed' ), $feed_id ),
					'channel'         => '',
					'channel_name'    => '—',
					'level'           => 'feed',
					'product_count'   => null,
					'orders'          => count( $orders ),
					'revenue'         => $revenue,
					'attribution'     => 'unknown',
					'caveat'          => null,
					'row_empty_state' => null,
				);
				continue;
			}

			if ( 'publish' !== $feed_post->post_status ) {
				continue; // Feed Performance is published-feeds-only (design.md Decision 9).
			}

			if ( $this->is_google_content_api_feed( $feed_id ) ) {
				continue; // Pushed directly to Google, not a listed/downloadable feed — excluded per merchant request.
			}

			$merchant   = get_post_meta( $feed_id, '_rex_feed_merchant', true ) ?: get_post_meta( $feed_id, 'rex_feed_merchant', true );
			$capability = Rex_Feed_Merchants::get_attribution_capability( (string) $merchant );

			if ( 'not_applicable' === $capability ) {
				continue; // Excluded entirely — not even a zero row.
			}

			$channel_name = Rex_Feed_Merchants::get_merchant_name( (string) $merchant );

			$rows[] = array(
				'feed_id'         => $feed_id,
				'feed_or_channel' => get_the_title( $feed_id ),
				'channel'         => (string) $merchant,
				'channel_name'    => $channel_name,
				'level'           => 'feed',
				'product_count'   => $this->get_product_count( $feed_id ),
				'orders'          => count( $orders ),
				'revenue'         => $revenue,
				'attribution'     => $capability,
				'caveat'          => 'uncertain' === $capability
					? __( 'Attribution may be unreliable for this platform’s checkout model.', 'rex-product-feed' )
					: null,
				'row_empty_state' => null,
			);
		}

		return $rows;
	}

	/**
	 * Task 7.4: feeds recently regenerated with tracking that have zero
	 * orders yet don't otherwise get a row — surface them explicitly with the
	 * "awaiting platform crawl" state instead of just being absent. Already
	 * `post_status => 'publish'`-scoped via the `get_posts()` query below.
	 *
	 * @param array $already_shown_feed_ids Feed IDs that already have a row.
	 */
	private function awaiting_crawl_rows( array $already_shown_feed_ids ): array {
		$feed_ids = get_posts(
			array(
				'post_type'      => 'product-feed',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_wpfm_analytics_tagging_last_applied',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		$rows = array();

		foreach ( $feed_ids as $feed_id ) {
			if ( in_array( $feed_id, $already_shown_feed_ids, true ) ) {
				continue;
			}

			if ( $this->is_google_content_api_feed( $feed_id ) ) {
				continue;
			}

			$merchant   = get_post_meta( $feed_id, '_rex_feed_merchant', true ) ?: get_post_meta( $feed_id, 'rex_feed_merchant', true );
			$capability = Rex_Feed_Merchants::get_attribution_capability( (string) $merchant );
			if ( 'not_applicable' === $capability ) {
				continue;
			}

			$last_applied = (string) get_post_meta( $feed_id, '_wpfm_analytics_tagging_last_applied', true );
			$empty_state  = EmptyStateResolver::feed_awaiting_crawl( $last_applied, false );
			if ( null === $empty_state ) {
				continue; // Tagged too long ago to be "recent" — silently absent, matching pre-existing behavior.
			}

			$channel_name = Rex_Feed_Merchants::get_merchant_name( (string) $merchant );

			$rows[] = array(
				'feed_id'         => $feed_id,
				'feed_or_channel' => get_the_title( $feed_id ),
				'channel'         => (string) $merchant,
				'channel_name'    => $channel_name,
				'level'           => 'feed',
				'product_count'   => $this->get_product_count( $feed_id ),
				'orders'          => 0,
				'revenue'         => 0.0,
				'attribution'     => $capability,
				'caveat'          => 'uncertain' === $capability
					? __( 'Attribution may be unreliable for this platform’s checkout model.', 'rex-product-feed' )
					: null,
				'row_empty_state' => $empty_state,
			);
		}

		return $rows;
	}

	/**
	 * Current product count for a feed, matching exactly what the feed list
	 * table itself shows (`Rex_Product_Feed_Cpt::fill_product_feed_columns()`
	 * / `total_products` column) — not `_wpfm_product_count`, a meta key the
	 * removed Dashboard module's `ApiController::on_feed_completed()` used to
	 * write on every `wpfm_feed_completed` hook. That handler no longer
	 * exists (Dashboard was deleted in section 1), so nothing populates
	 * `_wpfm_product_count` any more — every feed showed "—" here regardless
	 * of its real product count. Reads the same live-updated meta the
	 * generator itself writes instead.
	 */
	private function get_product_count( int $feed_id ): ?int {
		$totals = get_post_meta( $feed_id, '_rex_feed_total_products', true ) ?: get_post_meta( $feed_id, 'rex_feed_total_products', true );
		$totals = is_array( $totals ) ? $totals : array();

		$count = get_post_meta( $feed_id, '_rex_feed_total_products_for_all_feed', true ) ?: get_post_meta( $feed_id, 'rex_feed_total_products_for_all_feed', true );
		$count = $count ?: ( $totals['total'] ?? null );

		if ( isset( $totals['total'] ) && ( null === $count || $count < $totals['total'] ) ) {
			$count = $totals['total'];
		}

		return null !== $count && '' !== $count ? (int) $count : null;
	}

	/**
	 * Feeds with "Send to Google" (Content API) enabled push products
	 * straight into Google Merchant Center instead of generating a listed
	 * feed file — excluded from Feed Performance entirely, per merchant
	 * request. Only meaningful for the `google` merchant (matches the
	 * gating already used at generation time — see
	 * `abstract-rex-product-feed-generator.php`'s own `'google' === $this->merchant`
	 * check), but reading the flag alone is sufficient and cheaper here.
	 */
	private function is_google_content_api_feed( int $feed_id ): bool {
		$value = get_post_meta( $feed_id, '_rex_feed_is_google_content_api', true );
		return 'yes' === $value;
	}

	/**
	 * Task 6.3: every active feed's merchant flagged uncertain/not_applicable.
	 */
	private function is_portfolio_entirely_non_attributable(): bool {
		$feed_ids = get_posts(
			array(
				'post_type'      => 'product-feed',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => -1,
			)
		);

		if ( empty( $feed_ids ) ) {
			return false;
		}

		foreach ( $feed_ids as $feed_id ) {
			if ( $this->is_google_content_api_feed( $feed_id ) ) {
				continue;
			}
			$merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true ) ?: get_post_meta( $feed_id, 'rex_feed_merchant', true );
			if ( 'supported' === Rex_Feed_Merchants::get_attribution_capability( (string) $merchant ) ) {
				return false;
			}
		}

		return true;
	}
}
