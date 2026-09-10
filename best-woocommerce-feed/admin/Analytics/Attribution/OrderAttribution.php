<?php

namespace RexTheme\Analytics\Attribution;

/**
 * Normalizes a WC_Order's native Order Attribution meta (captured
 * automatically by WooCommerce >= 8.5, zero PFM code involved in capture)
 * into a single shape every Analytics service reads the same way.
 */
class OrderAttribution {

	const UNATTRIBUTED = 'Unattributed';

	/**
	 * Raw captured UTM values, independent of any fallback/display logic —
	 * used by the UTM filter, which must operate on captured data as-is
	 * (analytics-utm-filter spec: independent of attribution resolution).
	 *
	 * @return array{source: string, medium: string, campaign: string, term: string, content: string}
	 */
	public static function raw_utm( \WC_Order $order ): array {
		return array(
			'source'   => trim( (string) $order->get_meta( '_wc_order_attribution_utm_source' ) ),
			'medium'   => trim( (string) $order->get_meta( '_wc_order_attribution_utm_medium' ) ),
			'campaign' => trim( (string) $order->get_meta( '_wc_order_attribution_utm_campaign' ) ),
			'term'     => trim( (string) $order->get_meta( '_wc_order_attribution_utm_term' ) ),
			'content'  => trim( (string) $order->get_meta( '_wc_order_attribution_utm_content' ) ),
		);
	}

	/**
	 * @return array{source: string, medium: string, term: string, is_referral: bool, is_attributed: bool}
	 */
	public static function extract( \WC_Order $order ): array {
		$utm         = self::raw_utm( $order );
		$source_type = trim( (string) $order->get_meta( '_wc_order_attribution_source_type' ) );
		$referrer    = trim( (string) $order->get_meta( '_wc_order_attribution_referrer' ) );

		if ( '' !== $utm['source'] ) {
			return array(
				'source'        => $utm['source'],
				'medium'        => '' !== $utm['medium'] ? $utm['medium'] : $source_type,
				'term'          => $utm['term'],
				'is_referral'   => false,
				'is_attributed' => true,
			);
		}

		// Non-UTM fallback: WC's own source-type/referrer-domain detection
		// (organic, referral, typein, admin, mobile_app, ...), grouped into a
		// distinct "Referrer (non-UTM)" bucket, never merged into UTM rows.
		if ( '' !== $source_type && 'typein' !== $source_type ) {
			$source = '' !== $referrer ? wp_parse_url( $referrer, PHP_URL_HOST ) : $source_type;
			return array(
				'source'        => $source ?: $source_type,
				'medium'        => $source_type,
				'term'          => '',
				'is_referral'   => true,
				'is_attributed' => true,
			);
		}

		return array(
			'source'        => self::UNATTRIBUTED,
			'medium'        => self::UNATTRIBUTED,
			'term'          => '',
			'is_referral'   => false,
			'is_attributed' => false,
		);
	}
}
