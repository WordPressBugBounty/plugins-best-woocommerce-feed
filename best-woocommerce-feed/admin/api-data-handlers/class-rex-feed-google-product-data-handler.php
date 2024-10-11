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
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_price( Product &$google_product, float $value ) {
		$price = new Price();
		$price->setValue( $value );
		$price->setCurrency( get_option( 'woocommerce_currency' ) );
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
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_sale_price( Product &$google_product, float $value ) {
		$price = new Price();
		$price->setValue( $value );
		$price->setCurrency( get_option( 'woocommerce_currency' ) );
		$google_product->setSalePrice( $price );
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
	public static function set_taxes( Product &$google_product, array $value ) {
		$taxes = [];
		foreach ( $value as $tax_data ) {
			$tax = new ProductTax();
			$tax->setCountry( $tax_data[ 'country' ] ?? '' );
			$tax->setRate( $tax_data[ 'rate' ] ?? '' );
			$tax->setRegion( $tax_data[ 'region' ] ?? '' );
			$tax->setTaxShip( $tax_data[ 'taxShip' ] ?? '' );
			$taxes[] = $tax;
		}
		$google_product->setTaxes( $taxes );
	}

	/**
	 * Set shipping information for the Google product.
	 *
	 * This method creates a new ProductShipping object, sets the country, service, and price (with currency based on the WooCommerce setting),
	 * and assigns the shipping information to the Google product.
	 *
	 * @param Product $google_product The Google product object to set shipping details for.
	 * @param array $value An associative array with shipping details including 'country', 'service', and 'price'.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_shipping( Product &$google_product, array $value ) {
		$shipping = new ProductShipping();
		$shipping->setCountry( $value[ 'country' ] ?? '' );
		$shipping->setService( $value[ 'service' ] ?? '' );
		$shipping->setPrice( new Price( [
			'value'    => $value[ 'price' ] ?? '',
			'currency' => get_option( 'woocommerce_currency' ),
		] ) );
		$google_product->setShipping( [ $shipping ] );
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
	 * Set the unit pricing measure for the Google product.
	 *
	 * This method creates a new ProductUnitPricingMeasure object, sets its value, and assigns it to the Google product.
	 *
	 * @param Product $google_product The Google product object to set the unit pricing measure for.
	 * @param float $value The unit pricing measure value to be set.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_unit_pricing_measure( Product &$google_product, float $value ) {
		$unitPricingMeasure = new ProductUnitPricingMeasure();
		$unitPricingMeasure->setValue( $value );
		$google_product->setUnitPricingMeasure( $unitPricingMeasure );
	}

	/**
	 * Set the unit pricing base measure for the Google product.
	 *
	 * This method creates a new ProductUnitPricingBaseMeasure object, sets its value, and assigns it to the Google product.
	 *
	 * @param Product $google_product The Google product object to set the unit pricing base measure for.
	 * @param float $value The unit pricing base measure value to be set.
	 *
	 * @return void
	 *
	 * @since 7.4.20
	 */
	public static function set_unit_pricing_base_measure( Product &$google_product, float $value ) {
		$unitPricingBaseMeasure = new ProductUnitPricingBaseMeasure();
		$unitPricingBaseMeasure->setValue( $value );
		$google_product->setUnitPricingBaseMeasure( $unitPricingBaseMeasure );
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