<?php
/**
 * Class Rex_Feed_Discount_Rules_Asana_Plugins
 *
 * @link       https://rextheme.com
 * @since      7.3.19
 *
 * @package    Rex_Product_Feed
 */

/**
 * Helper Class to retrieve Discounted Price by
 * Discount Rules and Dynamic Pricing for WooCommerce [by Asana Plugins]
 *
 * @link       https://rextheme.com
 * @since      7.3.19
 *
 * @package    Rex_Product_Feed
 */
class Rex_Feed_Discount_Rules_Asana_Plugins {

	/**
	 * Get condition id that has apply_mode = individually
	 *
	 * @param string $type Condition type.
	 * @param string $apply_mode Apply mode.
	 * @param int    $enabled If enabled.
	 *
	 * @return array|false
	 * @since 7.2.19
	 */
	private static function get_condition_ids( string $type, string $apply_mode, int $enabled = 0 ) {
		if ( $type && $apply_mode ) {
			global $wpdb;
			$condition_table      = $wpdb->prefix . 'wccs_conditions';
			$condition_meta_table = $wpdb->prefix . 'wccs_condition_meta';
			$query                = 'SELECT meta.wccs_condition_id AS `id` FROM %1s AS meta';
			$query               .= ' JOIN %1s AS conditions ON meta.wccs_condition_id = conditions.id';
			$query               .= ' WHERE meta.meta_key = %s';
			$query               .= ' AND meta.meta_value = %s';
			$query               .= ' AND conditions.type = %s';
			$query               .= ' AND conditions.status = %d';
			$query               .= ' ORDER BY meta.wccs_condition_id ASC';
			$condition_id         = $wpdb->get_col( $wpdb->prepare( $query, $condition_meta_table, $condition_table, 'apply_mode', $apply_mode, $type, $enabled ) ); //phpcs:ignore
			return !is_wp_error( $condition_id ) && $condition_id ? $condition_id : false;
		}
		return false;
	}

	/**
	 * Get condition meta values by condition id
	 *
	 * @param int    $condition_id Condition id.
	 * @param string $meta_key Meta key.
	 * @param bool   $single Should return a single specific value.
	 *
	 * @return array|mixed|string|void
	 * @since 7.2.19
	 */
	private static function get_meta_by_condition_id( int $condition_id, string $meta_key = '', bool $single = false ) {
		if ( !$condition_id ) {
			return;
		}

		global $wpdb;
		$condition_meta_table = $wpdb->prefix . 'wccs_condition_meta';
		$formatted_meta       = array();
		$query                = "SELECT `meta_key`, `meta_value` FROM {$condition_meta_table} WHERE `wccs_condition_id` = %d";

		if ( $meta_key ) {
			$query .= " AND `meta_key` = %s";
			$query  = $wpdb->prepare( $query, $condition_id, $meta_key ); //phpcs:ignore
		}
		else {
			$query = $wpdb->prepare( $query, $condition_id ); //phpcs:ignore
		}
		$meta_values = $wpdb->get_results( $query, ARRAY_A ); //phpcs:ignore

		if ( !is_wp_error( $meta_values ) && is_array( $meta_values ) ) {
			foreach ( $meta_values as $meta ) {
				$key = isset( $meta[ 'meta_key' ] ) ? $meta[ 'meta_key' ] : '';

				if ( isset( $meta[ 'meta_value' ] ) && !empty( $meta[ 'meta_key' ] ) ) {
                    $val = false !== @unserialize( $meta[ 'meta_value' ] ) ? unserialize( $meta[ 'meta_value' ] ) : $meta[ 'meta_value' ]; //phpcs:ignore
				}

				if ( '' !== $key ) {
					$formatted_meta[ $key ] = $val;
				}
			}
		}
		if ( $single ) {
			return !empty( $formatted_meta[ $meta_key ] ) ? $formatted_meta[ $meta_key ] : '';
		}
		return $formatted_meta;
	}

	/**
	 * Check whether a matched with
	 * date_time condition of discount rules
	 *
	 * @param array  $dates Date array.
	 * @param string $condition Condition.
	 *
	 * @return bool
	 * @throws Exception Exception.
	 * @since 7.2.19
	 */
	private static function is_date_time_matched( array $dates, string $condition ) {
		$matched   = true;
		$timezone  = new DateTimeZone( wp_timezone_string() );
		$date_time = new DateTime( 'now', $timezone );
		$date      = $date_time->format( 'Y-m-d' );
		$date      = strtotime( $date );
		$time      = $date_time->format( 'H:i' );
		$time      = strtotime( $time );
		$day       = $date_time->format( 'l' );

		foreach ( $dates as $data ) {
			if ( isset( $data[ 'type' ] ) ) {
				switch ( $data[ 'type' ] ) {
					case 'date_time':
					case 'date':
						if ( isset( $date[ 'start' ][ 'time' ], $data[ 'end' ][ 'time' ] ) ) {
							$date_time_start = strtotime( $data[ 'start' ][ 'time' ] );
							$date_time_end   = strtotime( $data[ 'end' ][ 'time' ] );

							if ( $date_time >= $date_time_start && $date_time <= $date_time_end ) {
								if ( 'one' === $condition ) {
									return true;
								}
							}
							else {
								if ( 'one' !== $condition ) {
									$matched = false;
								}
							}
						}
						break;
					case 'specific_date':
						if ( isset( $data[ 'date' ][ 'time' ] ) ) {
							$specific_dates = $data[ 'date' ][ 'time' ];
							$specific_dates = str_replace( '[', '', $specific_dates );
							$specific_dates = str_replace( ']', '', $specific_dates );
							$specific_dates = explode( ',', $specific_dates );
							if ( in_array( $date, $specific_dates ) ) {
								if ( 'one' === $condition ) {
									return true;
								}
							}
							else {
								if ( 'one' !== $condition ) {
									$matched = false;
								}
							}
						}
						break;
					case 'time':
						if ( isset( $date[ 'start_time' ], $data[ 'end_time' ] ) ) {
							$time_start = strtotime( $data[ 'start_time' ] );
							$time_end   = strtotime( $data[ 'end_time' ] );

							if ( $time >= $time_start && $time <= $time_end ) {
								if ( 'one' === $condition ) {
									return true;
								}
							}
							else {
								if ( 'one' !== $condition ) {
									$matched = false;
								}
							}
						}
						break;
					case 'days':
						if ( isset( $date[ 'days' ][ 'time' ] ) ) {
							$days = $data[ 'days' ];

							if ( in_array( $day, $days ) ) {
								if ( 'one' === $condition ) {
									return true;
								}
							}
							else {
								if ( 'one' !== $condition ) {
									$matched = false;
								}
							}
						}
						break;
					default:
						break;
				}
			}
		}
		return $matched;
	}

	/**
	 * Check whether a its matched with
	 * any conditions of discount rule
	 *
	 * @param array      $conditions Conditions.
	 * @param string     $match_mode Match mode.
	 * @param WC_Product $product WC Product object.
	 * @param float      $price Product price.
	 *
	 * @return bool
	 * @since 7.2.19
	 */
	private static function is_conditions_matched( array $conditions, string $match_mode, WC_Product $product, float $price ) {
		foreach ( $conditions as $condition ) {
			if ( isset( $condition[ 'condition' ] ) ) {
				$is_applicable = false;
				switch ( $condition[ 'condition' ] ) {
					case 'subtotal_including_tax':
						if ( isset( $condition[ 'math_operation_type' ], $condition[ 'number_value_2' ] ) ) {
							$is_applicable = self::perform_math_operation( $condition[ 'math_operation_type' ], (float) $condition[ 'number_value_2' ], $price );
						}
						break;
					case 'cart_total_weight':
						if ( isset( $condition[ 'math_operation_type' ], $condition[ 'number_value_2' ] ) ) {
							$product_weight = (float) $product->get_weight();
							$is_applicable  = self::perform_math_operation( $condition[ 'math_operation_type' ], (float) $condition[ 'number_value_2' ], $product_weight );
						}
						break;
					default:
						break;
				}
				if ( $is_applicable ) {
					if ( 'one' === $match_mode ) {
						return true;
					}
				}
				elseif ( 'one' !== $match_mode ) {
					return false;
				}
			}
		}
		return false;
	}

	/**
	 * Check whether a condition applies for a product price or not
	 *
	 * @param int        $condition_id Condition id.
	 * @param WC_Product $product WC Product object.
	 * @param float      $price Product price.
	 *
	 * @return bool
	 * @throws Exception Exception.
	 * @since 7.2.19
	 */
	private static function is_condition_apply( int $condition_id, WC_Product $product, float $price ) {
		$condition_meta = self::get_meta_by_condition_id( $condition_id );

		if ( isset( $condition_meta[ 'date_times_match_mode' ] ) && isset( $condition_meta[ 'date_time' ] ) && is_array( $condition_meta[ 'date_time' ] ) && !empty( $condition_meta[ 'date_time' ] ) ) {
			$date_matched = self::is_date_time_matched( $condition_meta[ 'date_time' ], $condition_meta[ 'date_times_match_mode' ] );
			if ( !$date_matched ) {
				return false;
			}
		}

		if ( isset( $condition_meta[ 'conditions_match_mode' ] ) && isset( $condition_meta[ 'conditions' ] ) && is_array( $condition_meta[ 'conditions' ] ) && !empty( $condition_meta[ 'conditions' ] ) ) {
			$conditions_matched = self::is_conditions_matched( $condition_meta[ 'conditions' ], $condition_meta[ 'conditions_match_mode' ], $product, $price );
			if ( !$conditions_matched ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Get the condition id that applies with the given product
	 *
	 * @param array      $condition_ids Conditions array.
	 * @param WC_Product $product WC Product object.
	 * @param float      $price Product price.
	 *
	 * @return false|mixed
	 * @throws Exception Exception.
	 * @since 7.2.19
	 */
	private static function get_applied_condition_id( array $condition_ids, WC_Product $product, float $price ) {
		if ( !is_wp_error( $condition_ids ) && is_array( $condition_ids ) && !empty( $condition_ids ) ) {
			foreach ( $condition_ids as $id ) {
				if ( self::is_condition_apply( $id, $product, $price ) ) {
					return $id;
				}
			}
		}
		return false;
	}

	/**
	 * Perform the math operation for a rule
	 *
	 * @param string $math_operation Math operation.
	 * @param float  $condition_value Condition value.
	 * @param float  $product_value Product value.
	 *
	 * @return bool
	 * @since 7.2.19
	 */
	private static function perform_math_operation( string $math_operation, float $condition_value, float $product_value ) {
		if ( 'less_than' === $math_operation ) {
			return $product_value < $condition_value;
		}
		elseif ( 'less_equal_to' === $math_operation ) {
			return $product_value <= $condition_value;
		}
		elseif ( 'greater_than' === $math_operation ) {
			return $product_value > $condition_value;
		}
		elseif ( 'greater_equal_to' === $math_operation ) {
			return $product_value >= $condition_value;
		}
		elseif ( 'equal_to' === $math_operation ) {
			return $product_value === $condition_value;
		}
		elseif ( 'not_equal_to' === $math_operation ) {
			return $product_value !== $condition_value;
		}
		return false;
	}

	/**
	 * Apply discount for a given price
	 *
	 * @param int   $condition_id Condition id.
	 * @param float $price Price.
	 *
	 * @return float
	 * @since 7.2.19
	 */
	private static function apply_discount( int $condition_id, float $price ) {
		$discount_price = self::get_meta_by_condition_id( $condition_id, 'discount_amount', true );
		$discount_type  = self::get_meta_by_condition_id( $condition_id, 'discount_type', true );

		if ( 'price' === $discount_type || 'price_discount_per_item' === $discount_type ) {
			return $price - (float) $discount_price;
		}
		else {
			return $price - ( ( (float) $discount_price * $price ) / 100.00 );
		}
	}

	/**
	 * Get Discounted price
	 *
	 * @param int   $product_id Product id.
	 * @param float $price Product price.
	 *
	 * @return float|void
	 * @throws Exception Exception.
	 * @since 7.2.19
	 */
	public static function get_discounted_price( int $product_id, float $price ) {
		if ( function_exists( 'wpfm_is_discount_rules_asana_plugins_active' ) && !wpfm_is_discount_rules_asana_plugins_active() ) {
			return $price;
		}

		if ( !$product_id || is_wp_error( $product_id ) || !$price || is_wp_error( $price ) ) {
			return;
		}

		$wc_product = wc_get_product( $product_id );

		if ( !is_wp_error( $wc_product ) ) {
			$condition_id  = false;
			$discount_type = 'cart-discount';

			$condition_ids = wpfm_get_cached_data( 'asana_discount_rules_individually' );
			if ( 'empty' !== $condition_ids && is_wp_error( $condition_ids ) || !is_array( $condition_ids ) && empty( $condition_ids ) ) {
				$condition_ids = self::get_condition_ids( $discount_type, 'individually', 1 );
				wpfm_set_cached_data( 'asana_discount_rules_individually', empty( $condition_ids ) ? 'empty' : $condition_ids );
			}
			if ( !is_wp_error( $condition_ids ) && is_array( $condition_ids ) && !empty( $condition_ids ) ) {
				$condition_id = self::get_applied_condition_id( $condition_ids, $wc_product, $price );
			}
			if ( !$condition_id ) {
				$condition_ids = wpfm_get_cached_data( 'asana_discount_rules_all' );
				if ( 'empty' !== $condition_ids && is_wp_error( $condition_ids ) || !is_array( $condition_ids ) && empty( $condition_ids ) ) {
					$condition_ids = self::get_condition_ids( $discount_type, 'all', 1 );
					wpfm_set_cached_data( 'asana_discount_rules_all', empty( $condition_ids ) ? 'empty' : $condition_ids );
				}
				if ( !is_wp_error( $condition_ids ) && is_array( $condition_ids ) && !empty( $condition_ids ) ) {
					$condition_id = self::get_applied_condition_id( $condition_ids, $wc_product, $price );
				}
			}
			if ( !$condition_id ) {
				$condition_ids = wpfm_get_cached_data( 'asana_discount_rules_applicable_not_exists' );
				if ( 'empty' !== $condition_ids && is_wp_error( $condition_ids ) || !is_array( $condition_ids ) && empty( $condition_ids ) ) {
					$condition_ids = self::get_condition_ids( $discount_type, 'applicable_not_exists', 1 );
					wpfm_set_cached_data( 'asana_discount_rules_applicable_not_exists', empty( $condition_ids ) ? 'empty' : $condition_ids );
				}
				if ( !is_wp_error( $condition_ids ) && is_array( $condition_ids ) && !empty( $condition_ids ) ) {
					$condition_id = self::get_applied_condition_id( $condition_ids, $wc_product, $price );
				}
			}

			if ( $condition_id ) {
				return self::apply_discount( $condition_id, $price );
			}
		}
		return $price;
	}
}
