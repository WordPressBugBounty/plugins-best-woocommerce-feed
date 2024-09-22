<?php
/**
 * Class Rex_Product_Feed_Tax
 *
 * @package    Rex_Product_Feed_Shipping
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 */

/**
 * This class is responsible for managing tax related calculations for feed
 *
 * @package    Rex_Product_Feed_Tax
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 * @since 7.2.36
 */
class Rex_Product_Feed_Tax {

	/**
	 * Get the WooCommerce tax rate ID for a product based on its tax class and the feed country code.
	 *
	 * This function is used to find the appropriate tax rate ID for a product based on its tax class
	 * and the country code provided in the product feed. It checks the available tax rates for the
	 * product's tax class and matches the country code to find the corresponding tax rate.
	 *
	 * @param object $product           The product for which the tax rate is being determined.
	 * @param string $feed_country_code The country code provided in the product feed.
	 *
	 * @return int|null Tax rate ID if found, or null if no matching tax rate is found.
	 * @since 7.3.15
	 */
	public static function get_wc_tax_rate_id( $product, $feed_country_code ) {
		$tax_rate_id = null;
		// Retrieve tax rates for the product's tax class.
		$wc_tax_rates = WC_Tax::get_rates_for_tax_class( $product->get_tax_class() );

		// Iterate through the tax rates to find a matching one.
		foreach( $wc_tax_rates as $rate ) {
			if ( !empty( $rate->tax_rate_id ) ) {
				if ( empty( $rate->tax_rate_country ) ) {
					$tax_rate_id = empty( $tax_rate_id ) ? $rate->tax_rate_id : $tax_rate_id;
					continue;
				}
				if ( $feed_country_code === $rate->tax_rate_country ) {
					return $rate->tax_rate_id;
				}
			}
		}

		// Return null/matched tax rate id with no specific country.
		return $tax_rate_id;
	}

    /**
     * Retrieves all WooCommerce tax rates.
     *
     * @return array An array of WooCommerce tax rates.
     * @since 7.3.1
     */
    public static function get_wc_tax_rates() {
        $wc_tax_classes = [ '' ];
        $wc_tax_classes = array_merge( $wc_tax_classes, WC_Tax::get_tax_classes() );
        $wc_tax_rates   = [];

        foreach( $wc_tax_classes as $tax_class ) {
            $wc_tax_rates = array_merge( $wc_tax_rates, WC_Tax::get_rates_for_tax_class( $tax_class ) );
        }
        return $wc_tax_rates;
    }

    /**
     * Calculates the price including tax based on the given price and tax rate.
     *
     * @param float $price_excl_tax The original price.
     * @param int|string $tax_rate_id The ID of the tax rate to apply.
     * @return float The price including tax.
     * @since 7.3.1
     */
    public static function get_price_with_tax( $price_excl_tax, $tax_rate_id ) {
        if( empty( $price_excl_tax ) || empty( $tax_rate_id ) ) {
            return $price_excl_tax;
        }

        $tax_rate = self::get_tax_rate( $tax_rate_id );

        if( !empty( $tax_rate ) && is_array( $tax_rate ) ) {
            $tax_rate[ 'compound' ] = 'yes';
            $tax_value              = WC_Tax::calc_tax( $price_excl_tax, [ $tax_rate ] );
            return !empty( $tax_value[ 0 ] ) ? $price_excl_tax + $tax_value[ 0 ] : $price_excl_tax;
        }
        return $price_excl_tax;
    }

    /**
     * Calculates the price excluding tax based on the given price and tax rate.
     *
     * @param float $price_incl_tax The original price.
     * @param int|string $tax_rate_id The ID of the tax rate to apply.
     * @return float The price excluding tax.
     * @since 7.3.1
     */
    public static function get_price_without_tax( $price_incl_tax, $tax_rate_id ) {
        if( !$price_incl_tax || '-1' == $tax_rate_id ) {
            return $price_incl_tax;
        }

        $tax_rate = self::get_tax_rate( $tax_rate_id );

        if( !empty( $tax_rate ) && is_array( $tax_rate ) ) {
            $tax_rate[ 'compound' ] = 'yes';
            $tax_value              = WC_Tax::calc_tax( $price_incl_tax, [ $tax_rate ], true );
            return !empty( $tax_value[ 0 ] ) ? $price_incl_tax - $tax_value[ 0 ] : $price_incl_tax;
        }
        return $price_incl_tax;
    }

    /**
     * Retrieves the tax rate based on the given tax rate ID.
     *
     * @param int $tax_rate_id The ID of the tax rate.
     * @param string $output_type Optional. The output type of the tax rate. Defaults to ARRAY_A.
     * @return array|object|null The tax rate as an array or object, or null if not found.
     * @since 7.3.1
     */
    public static function get_tax_rate( $tax_rate_id, $output_type = ARRAY_A ) {
        global $wpdb;

        if( !$tax_rate_id || '-1' == $tax_rate_id ) {
            return [];
        }

	    return $wpdb->get_row(
		    $wpdb->prepare(
			    "SELECT `tax_rate` AS `rate`, `tax_rate_name` AS `label`, `tax_rate_shipping` AS `shipping`, `tax_rate_compound` AS `compound` FROM %i WHERE tax_rate_id = %d",
			    [ "{$wpdb->prefix}woocommerce_tax_rates", $tax_rate_id ]
		    ),
		    $output_type
	    );
    }
}