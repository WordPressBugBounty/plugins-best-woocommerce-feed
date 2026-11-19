<?php

use RexFeed\Google\Service\ShoppingContent\Installment;
use RexFeed\Google\Service\ShoppingContent\LoyaltyPoints;
use RexFeed\Google\Service\ShoppingContent\Price;
use RexFeed\Google\Service\ShoppingContent\Product;
use RexFeed\Google\Service\ShoppingContent\ProductShipping;
use RexFeed\Google\Service\ShoppingContent\ProductShippingDimension;
use RexFeed\Google\Service\ShoppingContent\ProductShippingWeight;
use RexFeed\Google\Service\ShoppingContent\ProductTax;
use RexFeed\Google\Service\ShoppingContent\ProductUnitPricingBaseMeasure;
use RexFeed\Google\Service\ShoppingContent\ProductUnitPricingMeasure;

/**
 * Class Rex_Feed_Handle_Google_Product
 *
 * This class provides methods for setting additional product data for Google products.
 *
 * @since 7.4.20
 */
class  Rex_Feed_Handle_Google_Product {
	/**
	 * Units Google accepts for unit_pricing_measure and unit_pricing_base_measure.
	 *
	 * @see https://support.google.com/merchants/answer/6324455
	 *
	 * @since 7.15.1
	 *
	 * @var string[]
	 */
	const UNIT_PRICING_UNITS = array(
		'oz', 'lb', 'mg', 'g', 'kg',
		'floz', 'pt', 'qt', 'gal',
		'ml', 'cl', 'l', 'cbm',
		'in', 'ft', 'yd', 'cm', 'm',
		'sqft', 'sqm',
		'ct',
	);

	/**
	 * Set an additional image link for the Google product.
	 *
	 * This method adds additional image URL to the Google product by retrieving the current list of additional
	 * image links, appending the new image URL, and updating the product with the new list.
	 *
	 * @param Product $google_product The Google product object to set the additional image link for.
	 * @param string $value The URL of the additional image to be added to the product.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_additional_image_links( Product &$google_product, string $value ) {
		$additional_image_links   = $google_product->getAdditionalImageLinks();
		$additional_image_links[] = $value;
		$google_product->setAdditionalImageLinks( $additional_image_links );
	}

	/**
	 * Set the price for the Google product.
	 *
	 * This method creates a new Price object, sets its value and currency, and assigns it to the product.
	 * The currency is retrieved based on the WooCommerce currency setting.
	 *
	 * @param Product $google_product The Google product object to set the price for.
	 * @param float $value The price value to be set.
	 * @param string $currency The currency code to use (optional, defaults to WooCommerce currency).
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	/**
	 * Convert a decimal price to Merchant API v1 amountMicros integer.
	 *
	 * @param float $price Decimal price (e.g. 9.99).
	 * @return int Price × 1,000,000 rounded to nearest integer.
	 */
	public static function price_to_micros( float $price ): int {
		return (int) round( $price * 1_000_000 );
	}

	public static function set_price( Product &$google_product, float $value, string $currency = '' ) {
		$price = new Price();
		$price->setValue( $value );
		$price->setCurrency( !empty( $currency ) ? $currency : get_option( 'woocommerce_currency' ) );
		$google_product->setPrice( $price );
	}

	/**
	 * Set the sale price for the Google product.
	 *
	 * This method creates a new Price object, sets its value and currency, and assigns it to the product as a sale price.
	 * The currency is retrieved based on the WooCommerce currency setting.
	 *
	 * @param Product $google_product The Google product object to set the sale price for.
	 * @param float $value The sale price value to be set.
	 * @param string $currency The currency code to use (optional, defaults to WooCommerce currency).
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_sale_price( Product &$google_product, float $value, string $currency = '' ) {
		if ( $value > 0 ) {
			$price = new Price();
			$price->setValue( $value );
			$price->setCurrency( !empty( $currency ) ? $currency : get_option( 'woocommerce_currency' ) );
			$google_product->setSalePrice( $price );
		}
	}

	/**
	 * Set the tax information for the Google product.
	 *
	 * This method iterates through an array of tax data, creates a ProductTax object for each entry, sets the respective
	 * fields (country, rate, region, and taxShip), and assigns the complete array of taxes to the product.
	 *
	 * @param Product $google_product The Google product object to set taxes for.
	 * @param array $value An array of tax information, each entry containing keys 'country', 'rate', 'region', and 'taxShip'.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_taxes( Product &$google_product, $value = array() ) {
		if ( empty( $value ) || ! is_array( $value ) ) {
			return;
		}
		$taxes = [];
		foreach ( $value as $tax_data ) {
			if ( ! is_array( $tax_data ) ) {
				continue;
			}
			$tax = new ProductTax();
			$tax->setCountry( $tax_data[ 'country' ] ?? '' );
			$tax->setRate( $tax_data[ 'rate' ] ?? '' );
			$tax->setRegion( $tax_data[ 'region' ] ?? '' );
			$tax->setTaxShip( $tax_data[ 'taxShip' ] ?? '' );
			$taxes[] = $tax;
		}
		if ( ! empty( $taxes ) ) {
			$google_product->setTaxes( $taxes );
		}
	}

	/**
	 * Set shipping information for the Google product.
	 *
	 * This method creates a new ProductShipping object, sets the country, service, and price (with currency based on the WooCommerce setting),
	 * and assigns the shipping information to the Google product.
	 *
	 * @param Product $google_product The Google product object to set shipping details for.
	 * @param mixed   $value An associative array or array of shipping details including 'country', 'service', and 'price'.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_shipping( Product &$google_product, $value = array() ) {
		if ( empty( $value ) || ! is_array( $value ) ) {
			return;
		}

		$shipping_entries = ( isset( $value['country'] ) || isset( $value['service'] ) || isset( $value['price'] ) || isset( $value['shipping_cost'] ) )
			? array( $value )
			: $value;

		$shippings = array();
		foreach ( $shipping_entries as $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}
			$price_val = $entry['price'] ?? $entry['shipping_cost'] ?? '';
			$shipping  = new ProductShipping();
			$shipping->setCountry( $entry['country'] ?? '' );
			if ( ! empty( $entry['region'] ) ) {
				$shipping->setRegion( $entry['region'] );
			}
			$shipping->setService( $entry['service'] ?? '' );
			$shipping->setPrice( new Price( array(
				'value'    => $price_val,
				'currency' => get_option( 'woocommerce_currency' ),
			) ) );
			$shippings[] = $shipping;
		}

		if ( ! empty( $shippings ) ) {
			$google_product->setShipping( $shippings );
		}
	}

	/**
	 * Set the shipping weight for the Google product.
	 *
	 * This method creates a new ProductShippingWeight object, sets its value, and assigns it to the Google product.
	 *
	 * @param Product $google_product The Google product object to set the shipping weight for.
	 * @param float $value The shipping weight value to be set.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_shipping_weight( Product &$google_product, float $value ) {
		$shippingWeight = new ProductShippingWeight();
		$shippingWeight->setValue( $value );
		$google_product->setShippingWeight( $shippingWeight );
	}

	/**
	 * Set the shipping length for the Google product.
	 *
	 * This method creates a new ProductShippingDimension object, sets the length value, and assigns it to the Google product.
	 *
	 * @param Product $google_product The Google product object to set the shipping length for.
	 * @param float $value The shipping length value to be set.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_shipping_length( Product &$google_product, float $value ) {
		$shippingLength = new ProductShippingDimension();
		$shippingLength->setValue( $value );
		$google_product->setShippingLength( $shippingLength );
	}

	/**
	 * Set the shipping width for the Google product.
	 *
	 * This method creates a new ProductShippingDimension object, sets the width value, and assigns it to the Google product.
	 *
	 * @param Product $google_product The Google product object to set the shipping width for.
	 * @param float $value The shipping width value to be set.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_shipping_width( Product &$google_product, float $value ) {
		$shippingWidth = new ProductShippingDimension();
		$shippingWidth->setValue( $value );
		$google_product->setShippingWidth( $shippingWidth );
	}

	/**
	 * Set the shipping height for the Google product.
	 *
	 * This method creates a new ProductShippingDimension object, sets the height value, and assigns it to the Google product.
	 *
	 * @param Product $google_product The Google product object to set the shipping height for.
	 * @param float $value The shipping height value to be set.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_shipping_height( Product &$google_product, float $value ) {
		$shippingHeight = new ProductShippingDimension();
		$shippingHeight->setValue( $value );
		$google_product->setShippingHeight( $shippingHeight );
	}

	/**
	 * Parse a mapped unit pricing value such as "160 ml", "100ml", "1,5 kg" or "16 fl oz".
	 *
	 * Mapped values are free text, so this is lenient on format (comma or dot decimals,
	 * thousands separators when both separators appear, NBSP, "fl oz" style units) but
	 * strict on meaning: anything without a positive number and a Google unit returns null
	 * so callers skip the attribute instead of throwing or sending bad data.
	 *
	 * Ambiguous single-separator values ("1,000 g" = 1000 or 1?) are skipped; see
	 * normalize_unit_pricing_number().
	 *
	 * @since 7.15.1
	 *
	 * @param mixed $value Raw mapped attribute value.
	 * @return array{value: float, unit: string}|null Parsed value and lowercase unit, or null when invalid.
	 */
	public static function parse_unit_pricing_value( $value ): ?array {
		if ( ! is_scalar( $value ) || is_bool( $value ) ) {
			return null;
		}

		// [\s\x{00A0}] = whitespace incl. NBSP. Unit parts may be split by a space and/or dot ("fl oz", "fl. oz.").
		if ( ! preg_match( '/^[\s\x{00A0}]*(\d+(?:[.,]\d+)*)[\s\x{00A0}]*([a-z]+(?:[\s\x{00A0}.]{0,2}[a-z]+)*)\.?[\s\x{00A0}]*$/iu', (string) $value, $matches ) ) {
			return null;
		}

		$number = self::normalize_unit_pricing_number( $matches[1] );
		$unit   = strtolower( preg_replace( '/[^a-z]/i', '', $matches[2] ) );

		if ( null === $number || $number <= 0 || ! in_array( $unit, self::UNIT_PRICING_UNITS, true ) ) {
			return null;
		}

		return array(
			'value' => $number,
			'unit'  => $unit,
		);
	}

	/**
	 * Convert a matched number string ("1,5", "1.000,5", "1,000.5", "1,000") to float.
	 *
	 * When both separators appear, the last one is the decimal mark and the other is a
	 * thousands separator. A separator repeated more than once is a thousands separator.
	 *
	 * Otherwise a single separator is the decimal mark ("1,5", "0,500"), except when it
	 * follows a non-zero group of 1-3 digits and is followed by exactly three digits
	 * ("1,000", "12.500"). That text means 1000 in some locales and 1 in others, so it
	 * returns null and the value is skipped rather than risk a 1000x wrong unit price.
	 *
	 * @since 7.15.1
	 *
	 * @param string $number Digits with optional "." / "," separators.
	 * @return float|null Parsed number, or null when the separator is ambiguous.
	 */
	private static function normalize_unit_pricing_number( string $number ): ?float {
		$last_dot   = strrpos( $number, '.' );
		$last_comma = strrpos( $number, ',' );

		if ( false !== $last_dot && false !== $last_comma ) {
			$thousands = $last_dot > $last_comma ? ',' : '.';
			$number    = str_replace( $thousands, '', $number );
		} elseif ( substr_count( $number, '.' ) > 1 ) {
			$number = str_replace( '.', '', $number );
		} elseif ( substr_count( $number, ',' ) > 1 ) {
			$number = str_replace( ',', '', $number );
		} elseif ( preg_match( '/^[1-9]\d{0,2}[.,]\d{3}$/', $number ) ) {
			return null;
		}

		return (float) str_replace( ',', '.', $number );
	}

	/**
	 * Set the unit pricing measure for the Google product.
	 *
	 * Parses the mapped value (e.g. "160 ml") into value + unit. Values that do not
	 * parse are skipped. Unit and range rules are left to Google item validation.
	 *
	 * @since 7.4.20
	 * @since 7.15.1 Accepts the raw mapped value and sends the unit.
	 *
	 * @param Product $google_product The Google product object to set the unit pricing measure for.
	 * @param mixed   $value          Raw mapped unit pricing measure value.
	 * @return void
	 */
	public static function set_unit_pricing_measure( Product &$google_product, $value ): void {
		$parsed = self::parse_unit_pricing_value( $value );
		if ( null === $parsed ) {
			return;
		}

		$unitPricingMeasure = new ProductUnitPricingMeasure();
		$unitPricingMeasure->setValue( $parsed['value'] );
		$unitPricingMeasure->setUnit( $parsed['unit'] );
		$google_product->setUnitPricingMeasure( $unitPricingMeasure );
	}

	/**
	 * Set the unit pricing base measure for the Google product.
	 *
	 * Parses the mapped value (e.g. "100 ml") into value + unit. Google requires an
	 * integer base value, so fractional values are skipped. Whether the integer is one
	 * Google allows (1, 10, 100, 2, 4, 8, ...) is left to Google item validation.
	 *
	 * @since 7.4.20
	 * @since 7.15.1 Accepts the raw mapped value and sends the unit.
	 *
	 * @param Product $google_product The Google product object to set the unit pricing base measure for.
	 * @param mixed   $value          Raw mapped unit pricing base measure value.
	 * @return void
	 */
	public static function set_unit_pricing_base_measure( Product &$google_product, $value ): void {
		$parsed = self::parse_unit_pricing_base_measure_value( $value );
		if ( null === $parsed ) {
			return;
		}

		$unitPricingBaseMeasure = new ProductUnitPricingBaseMeasure();
		$unitPricingBaseMeasure->setValue( $parsed['value'] );
		$unitPricingBaseMeasure->setUnit( $parsed['unit'] );
		$google_product->setUnitPricingBaseMeasure( $unitPricingBaseMeasure );
	}

	/**
	 * Parse a mapped unit pricing base measure value.
	 *
	 * Same as parse_unit_pricing_value(), but the value must be a whole number and is
	 * returned as a string because the API field is int64 (JSON-encoded as a string).
	 *
	 * @since 7.15.1
	 *
	 * @param mixed $value Raw mapped attribute value.
	 * @return array{value: string, unit: string}|null Parsed value and unit, or null when invalid.
	 */
	public static function parse_unit_pricing_base_measure_value( $value ): ?array {
		$parsed = self::parse_unit_pricing_value( $value );
		if ( null === $parsed || floor( $parsed['value'] ) !== $parsed['value'] ) {
			return null;
		}

		return array(
			'value' => (string) (int) $parsed['value'],
			'unit'  => $parsed['unit'],
		);
	}

	/**
	 * Set loyalty points for the Google product.
	 *
	 * This method creates a new LoyaltyPoints object, sets its points value, name, and ratio, and assigns it to the Google product.
	 *
	 * @param Product $google_product The Google product object to set loyalty points for.
	 * @param array $value An associative array with keys 'points', 'name', and 'ratio' representing loyalty points details.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_loyalty_points( Product &$google_product, array $value ) {
		$loyaltyPoints = new LoyaltyPoints();
		$loyaltyPoints->setPointsValue( $value[ 'points' ] );
		$loyaltyPoints->setName( $value[ 'name' ] );
		$loyaltyPoints->setRatio( $value[ 'ratio' ] );
		$google_product->setLoyaltyPoints( $loyaltyPoints );
	}

	/**
	 * Set the installment plan for the Google product.
	 *
	 * This method creates a new Installment object, sets the amount and the number of months for the installment plan,
	 * and assigns the installment information to the Google product.
	 *
	 * @param Product $google_product The Google product object to set installment details for.
	 * @param array $value An associative array with keys 'amount' and 'months' representing the installment details.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_installment( Product &$google_product, array $value ) {
		$installment = new Installment();
		$price       = new Price();
		$price->setValue( $value[ 'amount' ] ?? '' );
		$installment->setAmount( $price );
		$installment->setMonths( $value[ 'months' ] ?? '' );
		$google_product->setInstallment( $installment );
	}

	/**
	 * Set product highlights for the Google product.
	 *
	 * This method adds a product highlight by retrieving the current list of product highlights, appending the new highlight,
	 * and updating the Google product with the new list.
	 *
	 * @param Product $google_product The Google product object to set the product highlights for.
	 * @param string $value The product highlight text to be added.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_product_highlights( Product &$google_product, string $value ) {
		$product_highlights   = $google_product->getProductHighlights();
		$product_highlights[] = $value;
		$google_product->setProductHighlights( $product_highlights );
	}
}