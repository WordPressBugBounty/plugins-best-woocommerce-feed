<?php
/**
 * Class Rex_Product_Feed_Shipping
 *
 * @package    Rex_Product_Feed_Shipping
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 */

/**
 * This class is responsible for managing shipping zones for feed
 *
 * @package    Rex_Product_Feed_Shipping
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 * @since 7.3.0
 */
class Rex_Product_Feed_Shipping {

    /**
     * @var string $feed_country - Feed country variable.
     * @since 7.3.0
     */
    protected static $feed_country;

    /**
     * @var array $zone_countries - Feed zone countries variable.
     * @since 7.3.0
     */
    protected static $zone_countries;

    /**
     * @var array $shipping_methods - Feed shipping methods variable.
     * @since 7.3.0
     */
    protected static $shipping_methods;

    /**
     * @var \WC_Product $product - Feed product variable.
     * @since 7.4.15
     */
    protected static $product;

    /**
     * Constructor for the Rex_Product_Feed_Shipping class
     *
     * @param string $country_code - A string containing country data in the format "state:country:continent".
     * @since 7.3.0
     */
    public function __construct( $country_code, \WC_Product $product ) {
        self::$feed_country = $country_code;
        self::$product      = $product;
    }

    /**
     * Checks if the WooCommerce Table Rate Shipping plugin is active
     *
     * @return bool - True if the plugin is active, false otherwise.
     * @since 7.3.0
     */
    public static function is_wc_table_rate_shipping_active() {
        $active_plugings        = get_option( 'active_plugins', [] );
        $wc_table_rate_shipping = 'woocommerce-table-rate-shipping/woocommerce-table-rate-shipping.php';

        return in_array( $wc_table_rate_shipping, $active_plugings ) || is_plugin_active_for_network( $wc_table_rate_shipping );
    }

    /**
     * Retrieves the list of countries belonging to a specific continent in WooCommerce.
     *
     * @param string $continent_code - The continent code for which the countries are being retrieved.
     * 
     * @return array - An array of country codes associated with the specified continent.
     *                If no countries are found, an empty array is returned.
     * @since 7.3.0
     */
    public static function get_wc_countries_by_continent( $continent_code ) {
        // Retrieve the continent data from the WooCommerce plugin directory
        $wc_countries = new WC_Countries();
        $continents   = $wc_countries->get_continents();

        // Check if the country codes array for the specified continent exists and is not empty
        return !empty( $continents[ $continent_code ][ 'countries' ] ) ? $continents[ $continent_code ][ 'countries' ] : [];
    }

    /**
     * Get shipping zones and their shipping methods for a given product.
     *
     * @return array An array containing information about the shipping zones and their shipping methods.
     *              Each element in the array represents a shipping zone and includes the country, region, service, price, and instance settings.
     * @since 7.3.0
     */
    public function get_shipping_zones() {
        self::$shipping_methods = wpfm_get_cached_data( 'shipping_methods' );
        if ( empty( self::$shipping_methods ) ) {
            $wc_shipping_zones = WC_Shipping_Zones::get_zones();
            if ( !empty( $wc_shipping_zones ) ) {
                foreach ( $wc_shipping_zones as $zone ) {
                    if ( empty( $zone[ 'shipping_methods' ] ) ) continue;
                    if ( empty( $zone[ 'zone_locations' ] ) ) continue;

                    self::format_zone_locations( $zone[ 'zone_locations' ] );

                    if ( self::$feed_country && ( empty( self::$zone_countries ) || !in_array( self::$feed_country, self::$zone_countries ) ) ) continue;

                    $zone_name = !empty( $zone[ 'zone_name' ] ) ? $zone[ 'zone_name' ] : '';

                    self::get_formatted_shipping_methods( $zone[ 'shipping_methods' ], $zone_name );
                }
            }
            wpfm_set_cached_data( 'shipping_methods', self::$shipping_methods );
        }
        $this->set_shipping_price();
        return self::$shipping_methods;
    }

    /**
     * Get product shipping price from Table Rate Shipping by WooCommerce
     *
     * @param WC_Product $product WooCommerce Product instance.
     * @param WC_Shipping_Table_Rate $wc_table_rate WC Table Rate Shipping instance.
     *
     * @return array|string[]
     * @throws Exception
     * @since 7.3.7
     */
    protected function get_wc_table_rate_shipping_cost( WC_Product $product, WC_Shipping_Table_Rate $wc_table_rate ) {
        if( self::is_wc_table_rate_shipping_active() ) {
            $product_price          = $product->get_price();
            $product_weight         = $product->get_weight();
            $product_shipping_class = $product->get_shipping_class_id() ?: '';

            $rates = self::get_wc_table_rate_shipping_rates( $wc_table_rate, $product_price, $product_weight, $product_shipping_class );

            $cost = self::get_shipping_rate( $rates, $product_price );
            $cost = $cost ? wc_format_decimal( $cost, wc_get_price_decimals() ) : '';

            return [
                'shipping_cost' => $cost,
                'shipping_tax'  => ''
            ];
        }
        return [ 'shipping_cost' => '', 'shipping_tax' => '' ];
    }

    /**
     * Get the shipping rates from the WC_Shipping_Table_Rate instance based on the provided parameters.
     *
     * @param WC_Shipping_Table_Rate $wc_table_rate         The instance of WC_Shipping_Table_Rate.
     * @param float                  $product_price         The price of the product.
     * @param float|string           $product_weight        The weight of the product (optional).
     * @param string                 $product_shipping_class The shipping class of the product (optional).
     *
     * @return array The array of shipping rates returned by the query_rates method.
     * @since 7.3.7
     */
    protected function get_wc_table_rate_shipping_rates( WC_Shipping_Table_Rate $wc_table_rate, $product_price, $product_weight = '', $product_shipping_class = '' ) {
        return $wc_table_rate->query_rates( [
            'price'             => $product_price,
            'weight'            => $product_weight,
            'shipping_class_id' => $product_shipping_class
        ] );
    }

    /**
     * Calculates the total rate based on the given rate and product price.
     *
     * @param object $rate An object representing the rate information.
     * @param float $product_price The price of the product for which the rate is being calculated.
     *
     * @return float The calculated rate.
     * @since 7.3.7
     */
    protected function calculate_rate( $rate, $product_price ) {
        $rate_cost       = $rate->rate_cost ?? 0;
        $cost_per_item   = $rate->rate_cost_per_item ?? 0;
        $cost_per_weight = $rate->rate_cost_per_weight_unit ?? 0;
        $cost_percentage = $rate->rate_cost_percent ?? 0;

        return $rate_cost + $cost_per_item + $cost_per_weight + ( $product_price * $cost_percentage / 100 );
    }

    /**
     * Retrieves the shipping rate based on the given rates and product price.
     *
     * @param array $rates An array of rate information.
     * @param float $product_price The price of the product for which the shipping rate is being retrieved.
     *
     * @return float|string The shipping rate if found, otherwise an empty string.
     * @since 7.3.7
     */
    protected function get_shipping_rate( $rates, $product_price ) {
        if( is_array( $rates ) && !empty( $rates ) ) {
            $rate_abort = array_column( $rates, 'rate_abort' );
            if( in_array( 1, $rate_abort ) ) {
                // If rate_abort is found in any of the rates, return an empty string
                return '';
            }
            $rate_priority = array_column( $rates, 'rate_priority' );
            $index = array_search( 1, $rate_priority );

            if( !empty( $rates[ $index ] ) ) {
                // If rate_priority is found and the corresponding rate is not empty, calculate the rate.
                return self::calculate_rate( $rates[ $index ], $product_price ) ?: '';
            }
        }
        // Return an empty string if no suitable rate is found.
        return '';
    }

    /**
     * Formats the zone locations by extracting and storing zone countries and states.
     *
     * @param array $zone_locations The zone locations to format.
     *
     * @return void
     * @since 7.3.0
     */
    protected function format_zone_locations( $zone_locations ) {
        self::$zone_countries = [];

        foreach( $zone_locations as $location ) {
            if( !empty( $location->type ) && !empty( $location->code ) ) {
                if( 'state' === $location->type ) {
                    $continent_data = explode( ':', $location->code );
                    if( !empty( $continent_data[ 0 ] ) ) {
                        self::$zone_countries[] = $continent_data[ 0 ];
                    }
                }
                elseif( 'country' === $location->type ) {
                    self::$zone_countries[] = $location->code;
                }
                elseif( 'continent' === $location->type ) {
                    $countries            = self::get_wc_countries_by_continent( $location->code );
                    self::$zone_countries = !empty( $countries ) ? array_values( array_unique( array_merge( $countries, self::$zone_countries ) ) ) : self::$zone_countries;
                }
            }
        }
    }

    /**
     * Formats the shipping methods and stores them in the class variable.
     *
     * @param array $shipping_methods The array of shipping methods to format.
     * @param WC_Product $product The WooCommerce product.
     * @param string $zone_name The WooCommerce shipping zone title.
     *
     * @return void
     * @since 7.3.0
     */
    protected function get_formatted_shipping_methods( $shipping_methods, $zone_name = '' ) {
        self::$shipping_methods = [];
        foreach( $shipping_methods as $method ) {
            if( $method->is_enabled() ) {
                $service  = '';
                $instance = [];

                $service .= $zone_name;

                if( isset( $method->instance_settings[ 'title' ] ) ) {
                    $service .= ' ' . $method->instance_settings[ 'title' ];
                }
                $instance_id = $method->id ?? '';
                $instance_id .= !empty( $instance_id ) ? ':' : '';
                $instance_id .= !empty( $instance_id ) ? $method->instance_id : '';

                if ( 'WC_Shipping_Table_Rate' === get_class( $method ) && !empty( $instance_id ) && !empty( $method->table_rate_id ) ) {
                    $instance_id .= ':' . $method->table_rate_id;
                }

                self::$shipping_methods[] = [
                    'country'     => self::$feed_country,
                    'service'     => "{$service} " . self::$feed_country,
                    'instance_id' => $instance_id,
                    'instance'    => $instance,
                ];
            }
        }
    }

	/**
	 * Get the WooCommerce shipping zone ID for a specific country code.
	 *
	 * This function retrieves the shipping zone ID associated with a specific country code within WooCommerce.
	 *
	 * @return int|null Shipping zone ID if found, or null if not found.
	 * @since 7.3.15
	 */
	public function get_wc_shipping_zone_id() {
		global $wpdb;

		// Prepare a SQL query to fetch the shipping zone ID for a given country code.
		$query = $wpdb->prepare( 'SELECT `zone_id` FROM %i ', $wpdb->prefix . 'woocommerce_shipping_zone_locations' );
		$query .= "WHERE ((location_type = 'country' ";
		$query .= $wpdb->prepare( 'AND location_code = %s) ', self::$feed_country );
		$query .= $wpdb->prepare( "OR location_code LIKE %s) ", '%' . self::$feed_country . '%' );
		$query .= 'LIMIT 1';

		return $wpdb->get_var( $query ); // Return the shipping zone ID if found, or null if not found.
	}

	/**
	 * Get the cost associated with shipping for a WooCommerce product.
	 *
	 * This function calculates the cost of shipping for a given product based on various criteria, including
	 * the product's type, shipping methods, and specific shipping rate settings.
	 *
	 * @param object $product The WooCommerce product for which shipping cost is being determined.
	 * @param bool $min_cost A boolean value if the minimum shipping cost is expecting.
	 *
	 * @return float|string Shipping cost if applicable, or an empty string if not applicable.
	 * @since 7.3.15
	 */
	public function get_wc_shipping_cost( $product, $min_cost = false ) {
		if ( empty( $product ) || is_wp_error( $product ) || $product->is_virtual() || $product->is_downloadable() ) {
			return '';
		}
		$class_id         = $product->get_shipping_class_id();
		$shipping_methods = wpfm_get_cached_data( 'wc_shipping_methods_' . self::$feed_country );

		if ( function_exists( 'wc_get_shipping_zone' ) && !$shipping_methods ) {
			$shipping_zone_id = $this->get_wc_shipping_zone_id();
			$shipping_zone    = new WC_Shipping_Zone( $shipping_zone_id ?: 0 );
			$shipping_methods = $shipping_zone->get_shipping_methods( true );
			wpfm_set_cached_data( 'wc_shipping_methods_' . self::$feed_country, $shipping_methods );
		}

		if ( is_array( $shipping_methods ) && !empty( $shipping_methods ) ) {
			$shipping_costs = [];
			foreach( $shipping_methods as $method ) {
				$shipping_rates = $method->instance_settings ?? [];
				if ( 'WC_Shipping_Free_Shipping' === get_class( $method ) && isset( $method->min_amount ) && $product->get_price() >= $method->min_amount && ( 'min_amount' === $method->requires || 'either' === $method->requires ) ) {
					$shipping_costs[] = 0;
				}
				elseif ( isset( $shipping_rates[ 'cost' ] ) ) {
					$rate = $shipping_rates[ 'cost' ];
					if ( !empty( $class_id ) ) {
						$class_rate = !empty( $shipping_rates[ "class_cost_{$class_id}" ] ) ? $shipping_rates[ "class_cost_{$class_id}" ] : 0;
					}
					else {
						$class_rate = !empty( $shipping_rates[ 'no_class_cost' ] ) ? $shipping_rates[ 'no_class_cost' ] : 0;
					}
					$shipping_costs[] = !empty( $rate ) && is_numeric( $rate ) && is_numeric( $class_rate ) ? $rate + $class_rate : $class_rate;
				}
			}
		}

		return !empty( $shipping_costs ) ? $min_cost ? min( $shipping_costs ) : max( $shipping_costs ) : '';
	}

    /**
     * Set the shipping price for the product.
     *
     * @return void
     * @since 7.4.15
     */
    protected function set_shipping_price() {
        if ( !is_object( self::$product ) || empty( self::$shipping_methods ) ) {
            return "";
        }

        foreach ( self::$shipping_methods as $key => $shipping ) {
            if ( !empty( $shipping['instance_id'] ) ) {
                defined( 'WC_ABSPATH' ) || exit;

                // Load cart functions which are loaded only on the front-end.
                include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
                include_once WC_ABSPATH . 'includes/class-wc-cart.php';

                wc_load_cart();
                global $woocommerce;

                // Make sure to empty the cart again
                $woocommerce->cart->empty_cart();

                // Set Shipping Country and State.
                $woocommerce->customer->set_shipping_country( $shipping[ 'country' ] ?? '' );

                // Set shipping method in the cart
                $chosen_ship_method_id = $shipping[ 'instance_id' ];
                WC()->session->set( 'chosen_shipping_methods', [ $chosen_ship_method_id ] );

                // Get product id
                $id = self::$product->get_id();
                if ( "variation" === self::$product->get_type() ) {
                    $id = self::$product->get_parent_id();
                }
                elseif ( "grouped" === self::$product->get_type() ) {
                    $children = self::$product->get_children();
                    $id       = reset( $children );
                }

                $woocommerce->cart->add_to_cart( $id, 1 );

                // Read cart and get shipping costs
                $shipping_cost = $woocommerce->cart->get_shipping_total();
                $tax           = $woocommerce->cart->get_shipping_tax();

                // Reset chosen shipping methods in the cart
                WC()->session->set( 'chosen_shipping_methods', [ '' ] );

                // Make sure to empty the cart again
                $woocommerce->cart->empty_cart();

                self::$shipping_methods[ $key ][ 'shipping_cost' ] = (float)$shipping_cost + (float)$tax;
            }
        }
    }
}