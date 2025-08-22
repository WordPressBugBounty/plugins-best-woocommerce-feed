<?php
/**
 * Class Rex_Product_Feed_Listing_Actions
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin
 */

/**
 * This class is responsible to modify listing page actions
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin
 */
class Rex_Product_Feed_Actions {

	/**
	 * Remove Bulk Edit for Feed
	 *
	 * @param array $actions Post listing page actions.
	 *
	 * @since    1.0.0
	 */
	public function remove_bulk_edit( $actions ) {
		unset( $actions[ 'edit' ] );
		return $actions;
	}

	/**
	 * Remove Quick Edit for Feed
	 *
	 * @param array $actions Post listing page actions.
	 *
	 * @since    1.0.0
	 */
	public function remove_quick_edit( $actions ) {
		// Abort if the post type is not "books"
		if ( !is_post_type_archive( 'product-feed' ) ) {
			return $actions;
		}

		// Remove the Quick Edit link
		if ( isset( $actions[ 'inline hide-if-no-js' ] ) ) {
			unset( $actions[ 'inline hide-if-no-js' ] );
		}

		// Return the set of links without Quick Edit
		return $actions;
	}


	/**
	 * Trigger review request on new feed publish
	 *
	 * @return void
	 */
	public function show_review_request_markups( ) {
		$show_review_request = get_option( 'rex_feed_review_request' );

		if ( empty( $show_review_request ) ) {
			$data = array(
				'show'      => true,
				'time'      => '',
				'frequency' => 'immediate',
			);
			update_option( 'rex_feed_review_request', $data );
		}
	}


	/**
	 * Save feed meta data on post saving as draft
	 *
	 * @param string|int $post_id Feed id.
	 * @param WP_Post    $post Post type object.
	 *
	 * @return int|string|void
	 */
	public function save_draft_feed_meta( $post_id, WP_Post $post ) {
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		$slug = 'product-feed';
		if ( $slug !== $post->post_type ) {
			return $post_id;
		}

		$feed_data = function_exists( 'rex_feed_get_sanitized_get_post' ) ? rex_feed_get_sanitized_get_post() : array();
		$feed_data = !empty( $feed_data[ 'post' ] ) ? $feed_data[ 'post' ] : '';

		$meta_keys       = [
			'rex_feed_products',
			'rex_feed_aelia_currency',
			'rex_feed_wcml_currency',
			'rex_feed_google_destination',
			'rex_feed_google_target_country',
			'rex_feed_google_target_language',
			'rex_feed_google_schedule',
			'rex_feed_google_schedule_month',
			'rex_feed_google_schedule_week_day',
			'rex_feed_google_schedule_time',
			'rex_feed_ebay_seller_site_id',
			'rex_feed_ebay_seller_country',
			'rex_feed_ebay_seller_currency',
			'rex_feed_analytics_params',
			'rex_feed_merchant',
			'rex_feed_feed_format',
			'rex_feed_separator',
			'rex_feed_feed_country',
			'rex_feed_custom_wrapper',
			'rex_feed_feed_country',
			'rex_feed_custom_wrapper',
			'rex_feed_custom_items_wrapper',
			'rex_feed_custom_wrapper_el',
			'rex_feed_custom_xml_header',
			'rex_feed_zip_codes',
			'rex_feed_update_on_product_change',
			'rex_feed_cats_check_all_btn',
			'rex_feed_tags_check_all_btn',
            'rex_feed_brands_check_all_btn',
			'rex_feed_yandex_company_name',
			'rex_feed_yandex_old_price',
			'rex_feed_hotline_firm_id',
			'rex_feed_hotline_firm_name',
			'rex_feed_hotline_exchange_rate',
            'rex_feed_curcy_currency'
		];
		$settings_toggle = [
			'rex_feed_include_out_of_stock',
			'rex_feed_include_zero_price_products',
			'rex_feed_variable_product',
			'rex_feed_hidden_products',
			'rex_feed_variation_product_name',
			'rex_feed_parent_product',
			'rex_feed_variations',
			'rex_feed_skip_product',
			'rex_feed_skip_row',
			'rex_feed_analytics_params_options'
		];

		foreach( $meta_keys as $meta_key ) {
			if( !empty( $feed_data[ $meta_key ] ) ) {
				update_post_meta( $post_id, "_{$meta_key}", $feed_data[ $meta_key ] );
			}
			else {
				delete_post_meta( $post_id, "_{$meta_key}" );
			}
		}

		foreach( $settings_toggle as $toggle_key ) {
			if( !empty( $feed_data[ $toggle_key ] ) ) {
				update_post_meta( $post_id, "_{$toggle_key}", $feed_data[ $toggle_key ] );
			}
			else {
				update_post_meta( $post_id, "_{$toggle_key}", 'no' );
			}
		}

		if ( isset( $feed_data[ 'rex_feed_schedule' ] ) ) {
			update_post_meta( $post_id, '_rex_feed_schedule', $feed_data[ 'rex_feed_schedule' ] );

			if ( isset( $feed_data[ 'rex_feed_custom_time' ] ) && 'custom' === $feed_data[ 'rex_feed_schedule' ] ) {
				update_post_meta( $post_id, '_rex_feed_custom_time', $feed_data[ 'rex_feed_custom_time' ] );
			}
			else {
				delete_post_meta( $post_id, '_rex_feed_custom_time' );
			}
		}

		if ( isset( $feed_data[ 'fc' ] ) ) {
			if ( 0 !== (int)array_key_first( $feed_data[ 'fc' ] ) ) {
				array_shift( $feed_data[ 'fc' ] );
			}
			update_post_meta( $post_id, '_rex_feed_feed_config', $feed_data[ 'fc' ] );
		}

		if ( isset( $feed_data[ 'ff' ] ) ) {
			if ( 0 !== (int)array_key_first( $feed_data[ 'ff' ] ) ) {
				array_shift( $feed_data[ 'ff' ] );
			}
			update_post_meta( $post_id, '_rex_feed_feed_config_filter', $feed_data[ 'ff' ] );
		}

		if ( isset( $feed_data[ 'rex_feed_custom_filter_option_btn' ] ) ) {
			update_post_meta( $post_id, '_rex_feed_custom_filter_option', $feed_data[ 'rex_feed_custom_filter_option_btn' ] );
		}

		if ( isset( $feed_data[ 'rex_feed_cats' ] ) ) {
			$cats = array();
			foreach ( $feed_data[ 'rex_feed_cats' ] as $cat ) {
				$cats[] = get_term_by('slug', $cat, 'product_cat' )->term_id;
			}
			wp_set_object_terms( $post_id, $cats, 'product_cat' );
		}
		else {
			wp_set_object_terms( $post_id, array(), 'product_cat' );
		}
		if ( isset( $feed_data[ 'rex_feed_tags' ] ) ) {
			$tags = array();
			foreach ( $feed_data[ 'rex_feed_tags' ] as $tag ) {
				$tags[] = get_term_by('slug', $tag, 'product_tag' )->term_id;
			}
			wp_set_object_terms( $post_id, $tags, 'product_tag' );
		}
		else {
			wp_set_object_terms( $post_id, array(), 'product_tag' );
		}

        if ( isset( $feed_data[ 'rex_feed_brands' ] ) ) {
            $brands = array();
            foreach ( $feed_data[ 'rex_feed_brands' ] as $brand ) {
                $brands[] = get_term_by('slug', $brand, 'product_brand' )->term_id;
            }
            wp_set_object_terms( $post_id, $brands, 'product_brand' );
        }
        else {
            wp_set_object_terms( $post_id, array(), 'product_brand' );
        }

		if ( !isset( $feed_data[ 'rex_feed_update_on_product_change' ] ) ) {
			delete_post_meta( $post_id, '_rex_feed_update_on_product_change' );
		}
		if ( !isset( $feed_data[ 'rex_feed_cats_check_all_btn' ] ) ) {
			delete_post_meta( $post_id, '_rex_feed_cats_check_all_btn' );
		}
		if ( !isset( $feed_data[ 'rex_feed_tags_check_all_btn' ] ) ) {
			delete_post_meta( $post_id, '_rex_feed_tags_check_all_btn' );
		}
        if ( !isset( $feed_data[ 'rex_feed_brands_check_all_btn' ] ) ) {
            delete_post_meta( $post_id, '_rex_feed_brands_check_all_btn' );
        }

		do_action( 'rex_feed_after_draft_feed_config_saved', $post_id, $feed_data );
	}


	/**
	 * Deletes all available feed files after deleting a feed
	 *
	 * @param string|int $post_id Feed id.
	 *
	 * @return void
	 */
	public function delete_feed_files( $post_id ) {
		$path    = wp_upload_dir();
		$path    = $path[ 'basedir' ] . '/rex-feed';
		$formats = array( 'xml', 'yml', 'csv', 'tsv', 'txt', 'json' );

		foreach ( $formats as $format ) {
			$file = trailingslashit( $path ) . "feed-{$post_id}.{$format}";
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
	}


	/**
	 * Removes plugin log files from upload/wc-logs folder
	 * older than 30 days
	 *
	 * @return void
	 */
	public function remove_logs() {
		$today = gmdate( 'Y-m-d' );
		$today = (int) str_replace( '-', '', $today );
		$path  = wp_upload_dir();
		$path  = $path[ 'basedir' ] . '/wc-logs';

		$files = array(
			'WPFM-*.log',
			'wpfm-*.log',
			'WPFM.*.log',
		);

		foreach ( $files as $file ) {
			$logs = glob( trailingslashit( $path ) . $file );

			if ( !empty( $logs ) ) {
				foreach ( $logs as $log ) {
					$split_path = str_split( $log, strlen( trailingslashit( $path ) ) );
					$split_name = str_split( $split_path[ 1 ], 15 );
					$split_date = str_split( $split_name[ 0 ], 5 );
					$log_date   = (int) str_replace( '-', '', $split_date[ 1 ] . $split_date[ 2 ] );

					$diff = $today - $log_date;

					if ( $diff >= 30 ) {
						unlink( $log );
					}
				}
			}
		}
	}


	/**
	 * Renders admin notice if there is an error generating a xml feed
	 *
	 * @return void
	 * @since 7.2.9
	 */
	public function render_xml_error_message() {
		$feed_id = get_the_ID();

		if ( 'product-feed' === get_post_type( $feed_id ) ) {
			$temp_xml_url = get_post_meta( $feed_id, '_rex_feed_temp_xml_file', true ) ?: get_post_meta( $feed_id, 'rex_feed_temp_xml_file', true );
			$feed_format  = get_post_meta( $feed_id, '_rex_feed_feed_format', true ) ?: get_post_meta( $feed_id, 'rex_feed_feed_format', true );

			if ( '' !== $temp_xml_url && 'xml' === $feed_format ) {
				?>
				<script>
					(function ($) {
						'use strict';
						$(document).on('ready', function () {
							$('#message.updated.notice-success').remove();
						})
					})(jQuery);
				</script>
				<div id="message" class="notice notice-error rex-feed-notice">
					<p>
						<?php
						esc_html_e( 'There was an error when generating the feed. Please try the following to troubleshoot the issue.', 'rex-product-feed' );
						?>
					</p>
					<ol style="margin-left: 20px; font-size: 13px;">
						<li>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpfm_dashboard' ) ); ?>" target="_blank">
                                <?php esc_html_e( 'Clear Batch', 'rex-product-feed' ); ?>
                            </a>
                            <?php esc_html_e( 'and Regenerate.', 'rex-product-feed' ); ?>
						</li>
						<li>
                            <?php esc_html_e( 'Use Strip Tags For Description.', 'rex-product-feed' ); ?>
                        </li>
                        <li>
                            <?php esc_html_e( 'Checkout this troubleshoot doc ', 'rex-product-feed' ); ?> -
                            <a href="<?php echo esc_url( 'https://rextheme.com/docs/troubleshooting-common-issues-feed-error/' ); ?>" target="_blank">
                                <?php esc_html_e( 'View Doc', 'rex-product-feed' ); ?>
                            </a>
                        </li>
					</ol>
					<p>
                        <?php esc_html_e( 'If these don\'t work, please reach out to us at', 'rex-product-feed' ); ?>
                        <a href="mailto: support@rextheme.com" target="_blank">support@rextheme.com</a>
                        <?php esc_html_e( 'and we will assist you.', 'rex-product-feed' ); ?>
                    </p>
					<p>
						<?php
						esc_html_e( 'Make sure to attach your temporary feed link, and screenshots of your feed attributes, feed settings, and the feed filter section in the email.', 'rex-product-feed' );
						?>
					</p>
					<p>
						<?php
						esc_html_e( 'Temporary Feed URL: ', 'rex-product-feed' );
						?>
						 <a href="
						<?php
						echo esc_url( $temp_xml_url );
						?>
						" target="_blank">
						<?php
							echo esc_url( $temp_xml_url );
						?>
							</a>
					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Duplicate posts as draft
	 *
	 * @since 1.0.0
	 */
	public function duplicate_feed_as_draft() {
		global $wpdb;
		$data         = function_exists( 'rex_feed_get_sanitized_get_post' ) ? rex_feed_get_sanitized_get_post() : array();
		$get_data     = !empty( $data[ 'get' ] ) ? $data[ 'get' ] : array();
		$post_data    = !empty( $data[ 'post' ] ) ? $data[ 'post' ] : array();
		$request_data = !empty( $data[ 'request' ] ) ? $data[ 'request' ] : array();

		if ( !( isset( $get_data[ 'post' ] ) || isset( $post_data[ 'post' ] ) || ( isset( $request_data[ 'action' ] ) && 'wpfm_duplicate_post_as_draft' === $request_data[ 'action' ] ) ) ) {
			wp_die( 'No post to duplicate has been supplied!' );
		}

		if ( !isset( $get_data[ 'duplicate_nonce' ] ) || !wp_verify_nonce( sanitize_text_field( $get_data[ 'duplicate_nonce' ] ), basename( __FILE__ ) ) ) {
			return;
		}

		$post_id         = ( isset( $get_data[ 'post' ] ) ? absint( $get_data[ 'post' ] ) : absint( $post_data[ 'post' ] ) );
		$post            = get_post( $post_id );
		$current_user    = wp_get_current_user();
		$new_post_author = $current_user->ID;

		if ( $post ) {
			if ( '' === $post->post_title ) {
				$title = 'Untitled-duplicate';
			}
			else {
				$title = $post->post_title . ' - duplicate';
			}

			if ( '' === $post->post_name ) {
				$name = 'Untitled-duplicate';
			}
			else {
				$name = $post->post_name . ' - duplicate';
			}

			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'draft',
				'post_title'     => $title,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order,
			);

			$categories = get_the_terms( $post->ID, 'product_cat' );
			$tags       = get_the_terms( $post->ID, 'product_tag' );
            $brands     = get_the_terms( $post->ID, 'product_brand' );

			$new_post_id = wp_insert_post( $args );

			if ( $categories ) {
				foreach ( $categories as $cat ) {
					$p_cats[] = $cat->slug;
				}
				if ( !empty( $p_cats ) ) {
					wp_set_object_terms( $new_post_id, $p_cats, 'product_cat' );
				}
			}
			if ( $tags ) {
				foreach ( $tags as $tag ) {
					$p_tags[] = $tag->slug;
				}
				if ( !empty( $p_tags ) ) {
					wp_set_object_terms( $new_post_id, $p_tags, 'product_tag' );
				}
			}

            if ( $brands ) {
                foreach ( $brands as $brand ) {
                    $p_brands[] = $brand->slug;
                }
                if ( !empty( $p_brands ) ) {
                    wp_set_object_terms( $new_post_id, $p_brands, 'product_brand' );
                }
            }

			$taxonomies = get_object_taxonomies( $post->post_type ); // returns array of taxonomy names for post type, ex array("category", "post_tag");

			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
				wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
			}

			$query           = $wpdb->prepare( "SELECT `meta_key`, `meta_value` FROM %1s WHERE post_id = %d", $wpdb->postmeta, $post_id );
			$post_meta_infos = $wpdb->get_results( $query ); //phpcs:ignore

			if ( 0 !== count( $post_meta_infos ) ) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ( $post_meta_infos as $meta_info ) {
					$meta_key = $meta_info->meta_key;
					if ( '_wp_old_slug' === $meta_key ) {
						continue;
					}
					$meta_value      = ( $meta_info->meta_value );
					$query           = "SELECT $new_post_id, %s, %s";
					$query           = $wpdb->prepare( $query, $meta_key, $meta_value ); //phpcs:ignore
					$sql_query_sel[] = $query;
				}
				$sql_query .= implode( ' UNION ALL ', $sql_query_sel );
				$wpdb->query( $sql_query ); //phpcs:ignore
			}
            $url = admin_url( 'post.php?action=edit&post=' . $new_post_id . '&pr_post=' . 	$post_id);
			$url = filter_var( $url, FILTER_SANITIZE_URL );
			exit( esc_url( wp_redirect( $url ) ) ); //phpcs:ignore
		}
		else {
			wp_die( 'Post creation failed, could not find original post: ' . esc_attr( $post_id ) );
		}
	}


	/**
	 * Duplicate post link for feed-item
	 *
	 * @param array  $actions Post actions.
	 * @param object $post Post object.
	 * @return array
	 */
	public function duplicate_feed_link( $actions, $post ) {
		$user = wp_get_current_user();
        if ( 'product-feed' !== $post->post_type ) {
			return $actions;
		}
		if ( in_array( 'administrator', $user->roles ) && current_user_can( 'edit_posts' ) ) {
			$actions[ 'duplicate' ] = '<a href="' . wp_nonce_url( 'admin.php?action=wpfm_duplicate_post_as_draft&post=' . $post->ID, basename( __FILE__ ), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
		}
		return $actions;
	}


	/**
	 * WPFM action links
	 *
	 * @param array $links Array of links.
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$is_premium     = apply_filters( 'wpfm_is_premium_activate', false );
		$dashboard_link = sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'admin.php?page=wpfm_dashboard' ), __( 'Dashboard', 'rex-product-feed' ) );
		array_unshift( $links, $dashboard_link );
		if ( !$is_premium ) {
			$links[ 'wpfm_go_pro' ] = sprintf( '<a href="%1$s" target="_blank" class="wpfm-plugins-gopro" style="color: #2BBBAC; font-weight: bold; ">%2$s</a>', 'https://rextheme.com/best-woocommerce-product-feed/pricing/?utm_source=go_pro_button&utm_medium=plugin&utm_campaign=pfm_pro&utm_id=pfm_pro', __( 'Go Pro', 'rex-product-feed' ) );
		}
		return $links;
	}

	/**
	 * Render `Purge Cache` button with `Update` button
	 *
	 * @param object $post WP_Post object.
	 *
	 * @return void
	 */
	public function register_purge_button( $post ) {
		if ( 'product-feed' === $post->post_type ) {
			$html  = '<button id="btn_on_feed" ';
			$html .= 'class="wpfm-purge-cache btn_on_feed">';
			$html .= __( 'Purge Cache', 'rex-product-feed' );
			$html .= '<i class="fa fa-spinner fa-pulse fa-fw" style="display: none"></i></button>';

            print $html; // phpcs:ignore
		}
	}


	/**
	 * Add Pixel to WC pages
	 *
	 * @throws Exception Exception.
	 */
	public function enable_facebook_pixel() {
		global $product;
		$currency              = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';
		$wpfm_fb_pixel_enabled = get_option( 'wpfm_fb_pixel_enabled', 'no' );
		$view_content          = '';
		if ( 'yes' === $wpfm_fb_pixel_enabled ) {
			$wpfm_fb_pixel_data = get_option( 'wpfm_fb_pixel_value' );
			if ( isset( $wpfm_fb_pixel_data ) ) {
				if ( is_product() ) {
					$product_id    = $product->get_id();
					$price         = $product->get_price();
					$product_title = $product->get_name();
					$cats          = '';
					$terms         = wp_get_post_terms( $product_id, 'product_cat', array( 'orderby' => 'term_id' ) );

					if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
						foreach ( $terms as $term ) {
							$cats .= $term->name . ',';
						}
						$cats = rtrim( $cats, ',' );
						$cats = str_replace( '&amp;', '&', $cats );
					}

					if ( $product->is_type( 'variable' ) ) {
						$data         = function_exists( 'rex_feed_get_sanitized_get_post' ) ? rex_feed_get_sanitized_get_post() : array();
						$data         = isset( $data[ 'get' ] ) ? $data[ 'get' ] : array();
						$variation_id = function_exists( 'rex_feed_find_matching_product_variation' ) ? rex_feed_find_matching_product_variation( $product, $data ) : null;
						$total_get    = count( $data );
						if ( $total_get > 0 && $variation_id > 0 ) {
							$product_id       = $variation_id;
							$variable_product = wc_get_product( $variation_id );
							$content_type     = 'product';
							if ( is_object( $variable_product ) ) {
								$formatted_price = wc_format_decimal( $variable_product->get_price(), wc_get_price_decimals() );
							}
							else {
								$prices          = $product->get_variation_prices();
								$lowest          = reset( $prices[ 'price' ] );
								$formatted_price = wc_format_decimal( $lowest, wc_get_price_decimals() );
							}
						}
						else {
							$variation_ids   = $product->get_visible_children();
							$prices          = $product->get_variation_prices();
							$lowest          = reset( $prices[ 'price' ] );
							$formatted_price = wc_format_decimal( $lowest, wc_get_price_decimals() );
							$product_ids     = '';
							foreach ( $variation_ids as $variation ) {
								$product_ids .= "'" . $variation . "'" . ',';
							}
							$product_id   = rtrim( $product_ids, ',' );
							$content_type = 'product_group';
						}
					}
					else {
						$formatted_price = wc_format_decimal( $price, wc_get_price_decimals() );
						$content_type    = 'product';
					}
					$view_content = "fbq(\"track\",\"ViewContent\",{content_category:\"$cats\", content_name:\"$product_title\", content_type:\"$content_type\", content_ids:[$product_id],value:\"$formatted_price\",currency:\"$currency\"});";
					?>
					<?php
				}
				elseif ( is_product_category() ) {
					global $wp_query;
					$product_ids = wp_list_pluck( $wp_query->posts, 'ID' );
					$term        = get_queried_object();

					$product_id = '';

					foreach ( $product_ids as $id ) {
						$product = wc_get_product( $id );
						if ( !is_object( $product ) ) {
							continue;
						}

						if ( !$product->is_visible() ) {
							continue;
						}

						if ( $product->is_type( 'simple' ) ) {
							$product_id .= $id . ',';
						}
						elseif ( $product->is_type( 'variable' ) ) {
							$variations = $product->get_visible_children();
							foreach ( $variations as $variation ) {
								$product_id .= $variation . ',';
							}
						}
					}
					$product_id    = rtrim( $product_id, ',' );
					$category_name = $term->name;
					$category_path = function_exists( 'wpfm_get_the_term_path' ) ? wpfm_get_the_term_path( $term->term_id, 'product_cat', ' > ' ) : '';
					$view_content  = "fbq(\"trackCustom\",\"ViewCategory\",{content_category:\"$category_path\", content_name:\"$category_name\", content_type:\"product\", content_ids:[$product_id]});";
				}
				elseif ( is_search() ) {
					$search_term = sanitize_text_field( filter_input( INPUT_GET, 's' ) );
					global $wp_query;
					$product_ids = wp_list_pluck( $wp_query->posts, 'ID' );

					$product_id = '';

					foreach ( $product_ids as $id ) {
						$product = wc_get_product( $id );
						if ( !is_object( $product ) ) {
							continue;
						}

						if ( !$product->is_visible() ) {
							continue;
						}

						if ( $product->is_type( 'simple' ) ) {
							$product_id .= $id . ',';
						}
						elseif ( $product->is_type( 'variable' ) ) {
							$variations = $product->get_visible_children();
							foreach ( $variations as $variation ) {
								$product_id .= $variation . ',';
							}
						}
					}
					$product_id   = rtrim( $product_id, ',' );
					$view_content = "fbq(\"trackCustom\",\"Search\",{search_string:\"$search_term\", content_type:\"product\", content_ids:[{$product_id}]});";
				}
				elseif ( is_cart() || is_checkout() ) {
					if ( is_checkout() && !empty( is_wc_endpoint_url( 'order-received' ) ) ) {
						$order_key = sanitize_text_field( filter_input( INPUT_GET, 'key' ) );
						if ( !empty( $order_key ) ) {
							$order_id    = wc_get_order_id_by_order_key( $order_key );
							$order       = wc_get_order( $order_id );
							$order_items = $order->get_items();
							$order_real  = 0;
							$contents    = '';
							if ( !is_wp_error( $order_items ) ) {
								foreach ( $order_items as $order_item ) {
									$prod_id            = $order_item->get_product_id();
									$prod_quantity      = $order_item->get_quantity();
									$order_subtotal     = $order_item->get_subtotal();
									$order_subtotal_tax = $order_item->get_subtotal_tax();
									$order_real        += (int) number_format( ( (int) $order_subtotal + (int) $order_subtotal_tax ), 2 );
									$contents          .= "{'id': '$prod_id', 'quantity': $prod_quantity},";
								}
							}
							$contents     = rtrim( $contents, ',' );
							$view_content = "fbq(\"trackCustom\",\"Purchase\",{content_type:\"product\", value:\"$order_real\", currency:\"$currency\", contents:\"[$contents]\"});";
						}
					}
					else {
						$cart_real = 0;
						$contents  = '';
						foreach ( WC()->cart->get_cart() as $cart_item ) {
							$product_id = !empty( $cart_item[ 'product_id' ] ) ? $cart_item[ 'product_id' ] : null;
							if ( !empty( $cart_item[ 'variation_id' ] ) ) {
								$product_id = $cart_item[ 'variation_id' ];
							}
							$contents   .= !empty( $product_id ) ? "'{$product_id}'," : '';
							$line_total = (int)$cart_item[ 'line_total' ];
							$line_tax   = (int)$cart_item[ 'line_tax' ];
							$cart_real  += (int)number_format( ( $line_total + $line_tax ), 2 );
						}
						$contents = rtrim( $contents, ',' );
						if ( is_cart() ) {
							$view_content = "fbq(\"trackCustom\",\"AddToCart\",{ content_type:\"product\", value:\"$cart_real\", currency:\"$currency\", content_ids:[$contents]});";
						}
						elseif ( is_checkout() ) {
							$view_content = "fbq(\"trackCustom\",\"InitiateCheckout\",{content_type:\"product\", value:\"$cart_real\", currency:\"$currency\", content_ids:[$contents]});";
						}
					}
				}
			}

			?>
			<!-- Facebook pixel code - added by RexTheme.com -->
			<script type="text/javascript">
				!function (f, b, e, v, n, t, s) {
					if (f.fbq) return;
					n = f.fbq = function () {
						n.callMethod ?
							n.callMethod.apply(n, arguments) : n.queue.push(arguments)
					};
					if (!f._fbq) f._fbq = n;
					n.push = n;
					n.loaded = !0;
					n.version = '2.0';
					n.queue = [];
					t = b.createElement(e);
					t.async = !0;
					t.src = v;
					s = b.getElementsByTagName(e)[0];
					s.parentNode.insertBefore(t, s)
				}(window, document, 'script',
					'https://connect.facebook.net/en_US/fbevents.js');
				fbq('init', '<?php print esc_attr( "$wpfm_fb_pixel_data" ); ?>');
				fbq('track', 'PageView');
				<?php
				if ( strlen( $view_content ) > 2 ) {
					print $view_content; //phpcs:ignore
				}
				?>
			</script>
			<noscript>
				<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr( "{$wpfm_fb_pixel_data}" ); ?>&ev=PageView&noscript=1"/>
			</noscript>
			<!-- End Facebook Pixel Code -->
			<?php
		}
	}

    /**
     * Updates the product price compatibility with WPML.
     *
     * This method adjusts the product price if WPML is enabled and a custom price is set for the product in the specified currency.
     *
     * @param string $product_price The original product price.
     * @param WC_Product $product The WooCommerce product object.
     * @param string $type The type of price being updated (e.g., _regular_price, _sale_price).
     * @param object $feed_retriever_obj The feed retriever object.
     * @return string The updated product price considering WPML compatibility.
     *
     * @since 7.4.0
     */
    public function update_price_compatibility_with_wpml( $product_price, $product, $type, $feed_retriever_obj ) {
        global $woocommerce_wpml;

        // Early validation checks
        if ( empty( $product ) || ! is_object( $product ) || ! method_exists( $feed_retriever_obj, 'get_wcml_currency' ) ) {
            return $product_price;
        }

        $feed_wcml_currency = $feed_retriever_obj->get_wcml_currency();
        $base_currency = get_option( 'woocommerce_currency' );

        // If currencies are the same, no conversion needed
        if ( $base_currency === $feed_wcml_currency ) {
            return $product_price;
        }

        // Check for custom WPML prices first
        if ( ! empty( $type ) && $product->get_meta( '_wcml_custom_prices_status' ) ) {
            $custom_price = $product->get_meta( "{$type}_{$feed_wcml_currency}" );
            if ( ! empty( $custom_price ) && is_numeric( $custom_price ) ) {
                return (float)$custom_price;
            }
        }

        // Language code check (this condition seems unusual but keeping it from original)
        if ( defined( 'ICL_LANGUAGE_CODE' ) && $feed_retriever_obj->get_wcml_currency() === ICL_LANGUAGE_CODE ) {
            return $product_price;
        }

        // WPML currency conversion logic
        if ( method_exists( $feed_retriever_obj, 'is_wcml_active' ) && $feed_retriever_obj->is_wcml_active() ) {

            // Handle different product types
            $converted_price = $this->handle_product_type_conversion( $product, $type, $base_currency, $feed_wcml_currency, $product_price );

            if ( $converted_price !== false ) {
                return $converted_price;
            }
        }

        return $product_price;
    }

    /**
     * Handle currency conversion based on product type
     *
     * @param WC_Product $product The product object
     * @param string $type The price type
     * @param string $base_currency The base currency
     * @param string $target_currency The target currency
     * @param string $fallback_price The fallback price
     * @return float|false The converted price or false if conversion failed
     * @since 7.4.46
     */
    private function handle_product_type_conversion( $product, $type, $base_currency, $target_currency, $fallback_price ) {
        $product_type = $product->get_type();

        switch ( $product_type ) {
            case 'variable':
                return $this->convert_variable_product_price( $product, $type, $base_currency, $target_currency, $fallback_price );

            case 'grouped':
                return $this->convert_grouped_product_price( $product, $type, $base_currency, $target_currency, $fallback_price );

            case 'variation':
                return $this->convert_variation_product_price( $product, $type, $base_currency, $target_currency, $fallback_price );

            case 'simple':
            case 'external':
            default:
                return $this->convert_simple_product_price( $product, $type, $base_currency, $target_currency, $fallback_price );
        }
    }

    /**
     * Convert simple product price
     *
     * @param WC_Product $product The product object
     * @param string $type The price type
     * @param string $base_currency The base currency
     * @param string $target_currency The target currency
     * @param string $fallback_price The fallback price
     * @return float|false The converted price or false if conversion failed
     */
    private function convert_simple_product_price( $product, $type, $base_currency, $target_currency, $fallback_price ) {
        $original_base_price = $this->get_base_currency_price( $product, $type, $base_currency );

        if ( empty( $original_base_price ) || ! is_numeric( $original_base_price ) || $original_base_price <= 0 ) {
            return false;
        }

        return $this->apply_currency_conversion( $original_base_price, $target_currency );
    }

    /**
     * Convert variable product price
     *
     * @param WC_Product_Variable $product The variable product object
     * @param string $type The price type
     * @param string $base_currency The base currency
     * @param string $target_currency The target currency
     * @param string $fallback_price The fallback price
     * @return float|false The converted price or false if conversion failed
     */
    private function convert_variable_product_price( $product, $type, $base_currency, $target_currency, $fallback_price ) {
        // For variable products, we need to handle the parent product price
        // The variations will be handled separately when they're processed individually

        $original_base_price = $this->get_base_currency_price( $product, $type, $base_currency );

        if ( empty( $original_base_price ) || ! is_numeric( $original_base_price ) || $original_base_price <= 0 ) {
            // For variable products, if no parent price, try to get from variations
            $variations = $product->get_children();
            if ( ! empty( $variations ) ) {
                $variation_prices = array();
                foreach ( $variations as $variation_id ) {
                    $variation = wc_get_product( $variation_id );
                    if ( $variation ) {
                        $var_price = $this->get_base_currency_price( $variation, $type, $base_currency );
                        if ( is_numeric( $var_price ) && $var_price > 0 ) {
                            $variation_prices[] = $var_price;
                        }
                    }
                }

                if ( ! empty( $variation_prices ) ) {
                    // Use minimum price for variable products
                    $original_base_price = min( $variation_prices );
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        return $this->apply_currency_conversion( $original_base_price, $target_currency );
    }

    /**
     * Convert variation product price
     *
     * @param WC_Product_Variation $product The variation product object
     * @param string $type The price type
     * @param string $base_currency The base currency
     * @param string $target_currency The target currency
     * @param string $fallback_price The fallback price
     * @return float|false The converted price or false if conversion failed
     * @since 7.4.46
     */
    private function convert_variation_product_price( $product, $type, $base_currency, $target_currency, $fallback_price ) {
        // First check if variation has custom WPML prices
        if ( $product->get_meta( '_wcml_custom_prices_status' ) ) {
            $custom_price = $product->get_meta( "{$type}_{$target_currency}" );
            if ( ! empty( $custom_price ) && is_numeric( $custom_price ) ) {
                return (float)$custom_price;
            }
        }

        $original_base_price = $this->get_base_currency_price( $product, $type, $base_currency );

        if ( empty( $original_base_price ) || ! is_numeric( $original_base_price ) || $original_base_price <= 0 ) {
            return false;
        }

        return $this->apply_currency_conversion( $original_base_price, $target_currency );
    }

    /**
     * Convert grouped product price
     *
     * @param WC_Product_Grouped $product The grouped product object
     * @param string $type The price type
     * @param string $base_currency The base currency
     * @param string $target_currency The target currency
     * @param string $fallback_price The fallback price
     * @return float|false The converted price or false if conversion failed
     * @since 7.4.46
     */
    private function convert_grouped_product_price( $product, $type, $base_currency, $target_currency, $fallback_price ) {
        // Grouped products typically don't have their own price, but get price from children
        $children = $product->get_children();

        if ( empty( $children ) ) {
            return false;
        }

        $child_prices = array();
        foreach ( $children as $child_id ) {
            $child_product = wc_get_product( $child_id );
            if ( $child_product ) {
                $child_price = $this->get_base_currency_price( $child_product, $type, $base_currency );
                if ( is_numeric( $child_price ) && $child_price > 0 ) {
                    $child_prices[] = $child_price;
                }
            }
        }

        if ( empty( $child_prices ) ) {
            return false;
        }

        // Use minimum price from children
        $original_base_price = min( $child_prices );

        return $this->apply_currency_conversion( $original_base_price, $target_currency );
    }

    /**
     * Apply currency conversion with validation
     *
     * @param float $original_price The original price
     * @param string $target_currency The target currency
     * @return float|false The converted price or false if conversion failed
     * @since 7.4.46
     */
    private function apply_currency_conversion( $original_price, $target_currency ) {
        $original_price = (float)$original_price;

        // Apply WPML currency conversion
        $converted_price = apply_filters( 'wcml_raw_price_amount', $original_price, $target_currency );

        if ( ! empty( $converted_price ) && is_numeric( $converted_price ) ) {
            $converted_price = (float)$converted_price;

            // Validate conversion ratio to prevent extreme values
            $conversion_ratio = $converted_price / $original_price;
            if ( $conversion_ratio > 0.001 && $conversion_ratio < 1000 ) {
                return $converted_price;
            }
        }

        // Fallback to original base price if conversion fails
        return $original_price;
    }

    /**
     * Get the original price in base currency, bypassing WPML conversions
     *
     * @param WC_Product $product The WooCommerce product object
     * @param string $type The price type
     * @param string $base_currency The base currency code
     * @return float|null The original base price
     * @since 7.4.46
     */
    private function get_base_currency_price( $product, $type, $base_currency ) {
        global $woocommerce_wpml;

        $original_price = null;
        $current_currency = null;

        // Try to get price by temporarily switching to base currency
        if ( isset( $woocommerce_wpml->multi_currency ) ) {
            try {
                $current_currency = $woocommerce_wpml->multi_currency->get_client_currency();
                $woocommerce_wpml->multi_currency->set_client_currency( $base_currency );
                wp_cache_delete( $product->get_id(), 'posts' );
                $fresh_product = wc_get_product( $product->get_id() );
                if ( $fresh_product ) {
                    $original_price = $this->extract_price_by_type( $fresh_product, $type );
                }

            } catch ( Exception $e ) {
                // Silently handle exception
            } finally {
                if ($current_currency) {
                    $woocommerce_wpml->multi_currency->set_client_currency( $current_currency );
                }
            }
        }

        // Fallback to database query if WPML method failed
        if (! is_numeric( $original_price )) {
            $original_price = $this->get_price_from_database( $product->get_id(), $type );
        }

        // Final fallback to direct product method
        if ( empty( $original_price ) || ! is_numeric( $original_price ) ) {
            $original_price = $this->extract_price_by_type( $product, $type );
        }

        return $original_price;
    }

    /**
     * Extract price from product based on type
     *
     * @param WC_Product $product The product object
     * @param string $type The price type
     * @return float|null The price value
     * @since 7.4.46
     */
    private function extract_price_by_type( $product, $type ) {
        if (! is_object( $product )) {
            return null;
        }

        switch( $type ) {
            case '_price':
            case 'price':
                return $product->get_price();

            case '_regular_price':
            case 'regular_price':
                return $product->get_regular_price();

            case '_sale_price':
            case 'sale_price':
                return $product->get_sale_price();

            default:
                $meta_price = $product->get_meta( $type );
                return ! empty( $meta_price ) ? $meta_price : $product->get_price();
        }
    }

    /**
     * Get price directly from database, bypassing all filters
     *
     * @param int $product_id The product ID
     * @param string $type The price type
     * @return float|null The price from database
     * @since 7.4.46
     */
    private function get_price_from_database( $product_id, $type ) {
        global $wpdb;

        if ( empty( $product_id ) || ! is_numeric( $product_id ) ) {
            return null;
        }

        $meta_key = ltrim( $type, '_' );
        if ( substr( $meta_key, 0, 1 ) !== '_' ) {
            $meta_key = '_' . $meta_key;
        }

        $price = $wpdb->get_var( $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} 
         WHERE post_id = %d AND meta_key = %s 
         LIMIT 1",
            (int)$product_id,
            $meta_key
        ) );

        return is_numeric( $price ) ? (float)$price : null;
    }

    /**
     * Retrieves the converted product price using WooCommerce Multi-Currency (WMC).
     *
     * This method retrieves the converted product price if WMC (WooCommerce Multi-Currency) is active and the product has a fixed price set in the specified currency. If no fixed price is set, it calculates the converted price based on the currency exchange rate.
     *
     * @param string $product_price The original product price.
     * @param WC_Product $product The WooCommerce product object.
     * @param string $type The type of price being updated (e.g., regular price, sale price).
     * @param object $feed_retriever_obj The feed retriever object.
     * @return string The converted product price based on WMC settings.
     *
     * @since 7.4.1
     */
    public function get_converted_price_by_wmc( $product_price, $product, $type, $feed_retriever_obj ) {
        if ( wpfm_is_wmc_active() && !empty( $product_price ) && !empty( $product ) && !empty( $type ) && !empty( $feed_retriever_obj ) ) {
            $wmc_params = get_option( 'woo_multi_currency_params', array() );

            if ( !empty( $wmc_params ) && isset( $wmc_params[ 'enable_fixed_price' ] ) && $wmc_params[ 'enable_fixed_price' ] ) {
                $prices       = get_post_meta( $product->get_id(), "{$type}_wmcp", true );
                $prices       = json_decode( $prices );
                $wmc_currency = $feed_retriever_obj->get_wmc_currency();
                if ( !empty( $prices ) && isset( $prices->$wmc_currency ) ) {
                    return $prices->$wmc_currency;
                }
            }
            $wmc_settings      = class_exists( 'WOOMULTI_CURRENCY_Data' ) ? WOOMULTI_CURRENCY_Data::get_ins() : array();
            $wmc_currency_list = !empty( $wmc_settings ) ? $wmc_settings->currencies_list : array();

            if ( !empty( $wmc_currency_list ) ) {
                $to_currency = $feed_retriever_obj->get_wmc_currency();
                $rate        = $wmc_currency_list[ $to_currency ][ 'rate' ];
                return $product_price * $rate;
            }
        }
        return $product_price;
    }

    /**
     * Retrieves the converted product price using Aelia Currency Switcher.
     *
     * This method retrieves the converted product price if Aelia Currency Switcher is active. It converts the product price from the base currency to the target currency specified in the feed retriever object.
     *
     * @param string $product_price The original product price.
     * @param WC_Product $product The WooCommerce product object.
     * @param string $type The type of price being updated (e.g., regular price, sale price).
     * @param object $feed_retriever_obj The feed retriever object.
     * @return string The converted product price based on Aelia Currency Switcher settings.
     *
     * @since 7.4.0
     */
    public function get_converted_price_by_aelia( $product_price, $product, $type, $feed_retriever_obj ) {
        if ( wpfm_is_aelia_active() ) {
            $from_currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';
            $to_currency   = $feed_retriever_obj->aelia_currency;

            try {
                return apply_filters( 'wc_aelia_cs_convert', $product_price, $from_currency, $to_currency );
            }
            catch ( Exception $e ) {
                if ( $feed_retriever_obj->is_logging_enabled ) {
                    $log = wc_get_logger();
                    $log->warning( $e->getMessage(), [ 'source' => 'wpfm-error' ] );
                }
            }
        }
        return $product_price;
    }

    /**
     * Retrieves the converted product price using WooCommerce Multi-Currency (WMC).
     *
     * This method retrieves the converted product price if WMC (WooCommerce Multi-Currency) is active and the product has a fixed price set in the specified currency. If no fixed price is set, it calculates the converted price based on the currency exchange rate.
     *
     * @param string $product_price The original product price.
     * @param WC_Product $product The WooCommerce product object.
     * @param string $type The type of price being updated (e.g., regular price, sale price).
     * @param object $feed_retriever_obj The feed retriever object.
     * @return string The converted product price based on WMC settings.
     *
     * @since 7.4.24
     */
    public function get_converted_price_by_curcy( $product_price, $product, $type, $feed_retriever_obj ) {
        if(wpfm_is_curcy_active()){
            $curcy_instance = \WOOMULTI_CURRENCY_F_Data::get_ins();
            $exchange_currency = $curcy_instance->get_list_currencies();

            if ( isset( $exchange_currency[ $feed_retriever_obj->curcy_currency ] ) ) {

                $product_price = wmc_get_price( $product_price, $feed_retriever_obj->curcy_currency );
            }
        }
        return $product_price;
    }


    /**
     * Retrieves the converted product price using WooCommerce Currency Switcher (WOOCS).
     *
     * This method retrieves the converted product price if WOOCS is active. It converts the product price from the base currency to the target currency specified in the feed retriever object.
     *
     * @param string $product_price The original product price.
     * @param WC_Product $product The WooCommerce product object.
     * @param string $type The type of price being updated (e.g., regular price, sale price).
     * @param object $feed_retriever_obj The feed retriever object.
     * @return string The converted product price based on WOOCS settings.
     *
     * @since 7.4.15
     */
    public function get_converted_price_by_woocs( $product_price, $product, $type, $feed_retriever_obj ) {
        if ( defined( 'WOOCS_VERSION' ) ) {
            global $WOOCS;
            $to_currency = $feed_retriever_obj->woocs_currency ?? get_woocommerce_currency();

            try {
                // Store the current currency
                $current_currency = $WOOCS->current_currency ?? get_woocommerce_currency();

                // Set the currency to $to_currency
                $WOOCS->current_currency = $to_currency;

                // Convert the price to the active currency (USD in this case)
                $converted_price = $WOOCS->woocs_exchange_value( $product_price );

                // Restore the original currency
                $WOOCS->current_currency = $current_currency;

                return $converted_price;
            }
            catch ( Exception $e ) {
                if ( $feed_retriever_obj->is_logging_enabled ) {
                    $log = wc_get_logger();
                    $log->warning( $e->getMessage(), [ 'source' => 'wpfm-error' ] );
                }
            }
        }
        return $product_price;
    }

    /**
     * Retrieves the ACF Fields configurations.
     *
     * @param string $selector The field name or key.
     *
     * @return array
     *
     * @since 7.4.4
     */
    private static function get_acf_field_configs( $selector ) {
        global $wpdb;
        $query = "SELECT `ID` AS `field_id`, `post_content` AS `configs`, `post_name` AS `unique_key`, `post_parent` AS `parent_id` ";
        $query .= "FROM {$wpdb->posts} WHERE `post_type` = %s AND `post_excerpt` = %s";
        $query = $wpdb->prepare( $query, 'acf-field', $selector );
        $field_data = $wpdb->get_row( $query, ARRAY_A );
        if ( !empty( $field_data[ 'configs' ] ) ) {
            $field_data[ 'configs' ] = @unserialize( $field_data[ 'configs' ] );
        }
        return $field_data;
    }

    /**
     * Checks if a specific Advanced Custom Fields (ACF) field type is associated with a product.
     *
     * @param string $field_key The key of the ACF field.
     * @param string $field_type The type of the ACF field to check.
     *
     * @return bool True if the field type matches, false otherwise.
     *
     * @since 7.4.1
     */
    public static function is_acf_field_type( $field_key, $field_type ) {
        $field_data = self::get_acf_field_configs( $field_key );
        if ( !empty( $field_data[ 'configs' ][ 'type' ] ) && $field_data[ 'configs' ][ 'type' ] === $field_type ) {
            return true;
        }
        return false;
    }

    /**
     * Adds TranslatePress translation to the value based on the rule and instance.
     *
     * This method checks if TranslatePress is active and applies translations to the value based on the provided rule and instance.
     *
     * @param string $value The value to be translated.
     * @param array $rule The rule containing meta_key for translation.
     * @param object $instance The instance containing feed and language information.
     * @return string The translated value or original value if no translation is applied.
     *
     * @since 7.4.34
     */
    public function add_translate_press_value( $value, $rule, $instance ) {

        if ( wpfm_is_translatePress_active() ) {
            if (empty($value) || !is_string($value)) {
                return $value;
            }

            $meta_key = isset($rule['meta_key']) ? $rule['meta_key'] : '';

            $trp_default_lang = function_exists('rexfeed_get_trp_default_language') ? rexfeed_get_trp_default_language() : '';
            $language = isset($instance->feed->translatepress_language) ? $instance->feed->translatepress_language : $trp_default_lang;

            if (!function_exists('trp_translate') || !function_exists('wpfm_is_translatePress_active') || !wpfm_is_translatePress_active()) {
                return $value;
            }

            if ($language === $trp_default_lang) {
                return $value;
            }

            // Set the global TRP_LANGUAGE variable before translation
            global $TRP_LANGUAGE;
            $original_language = $TRP_LANGUAGE;
            $TRP_LANGUAGE = $language;

            if ($meta_key === 'link') {
                $slug = function_exists('rexfeed_get_trp_url_slug') ? rexfeed_get_trp_url_slug($language) : '';
                if (!empty($slug)) {
                    if (function_exists('is_plugin_active') && is_plugin_active('translatepress-personal/index.php')) {
                        $parsed_url = parse_url($value);
                        if (isset($parsed_url['path'])) {
                            $path_parts = explode('/', trim($parsed_url['path'], '/'));
                            $translated_path_parts = [];
                            foreach ($path_parts as $index => $part) {
                                if (method_exists($this, 'rexfeed_get_translated_slug')) {
                                    $translated_path_parts[] = $this->rexfeed_get_translated_slug($part, $language);
                                } else {
                                    $translated_path_parts[] = $part;
                                }
                            }
                            $translated_path = implode('/', $translated_path_parts);
                            $value = trailingslashit(home_url()) . untrailingslashit($slug) . '/' . untrailingslashit($translated_path) . '/';
                            $value = urldecode($value);
                        }
                    } else {
                        $value = str_replace(home_url('/'), home_url('/') . "$slug/", $value);
                    }
                }
                // Restore original language
                $TRP_LANGUAGE = $original_language;
                return $value;
            }

            $translatable_keys = ['description', 'short_description'];
            if (in_array($meta_key, $translatable_keys, true)) {

                // CRITICAL: Apply 'the_content' filter to match what TranslatePress stored
                // This processes the content through wptexturize() and other WP filters
                $processed_value = apply_filters('the_content', $value);

                // Now translate the processed content as a whole
                $translated_value = trp_translate($processed_value, $language, false);

                // Restore original language
                $TRP_LANGUAGE = $original_language;
                return $translated_value;
            }

            // For titles, apply the_title filter
            if (in_array($meta_key, ['title', 'product_title'], true)) {
                $processed_value = apply_filters('the_title', $value);
                $value = trp_translate($processed_value, $language, false);
            } else {
                // For other content, apply appropriate filters
                $processed_value = apply_filters('the_content', $value);
                $value = trp_translate($processed_value, $language, false);
            }

            // Restore original language
            $TRP_LANGUAGE = $original_language;
            return $value;
        } else {
            return $value;
        }
    }

    /**
     * Retrieves the translated slug for a given slug and language.
     *
     * @param string $slug The original slug.
     * @param string $language The target language.
     * @return string The translated slug.
     *
     * @since 7.4.34
     */
    public function rexfeed_get_translated_slug( $slug, $language ) {
        global $wpdb;

        // Get the original slug ID from wp_trp_slug_originals table
        $original_slug = $wpdb->get_var( $wpdb->prepare( "
        SELECT id FROM {$wpdb->prefix}trp_slug_originals
        WHERE original = %s
        LIMIT 1
    ", $slug ) );

        // If an original slug ID is found, get the translated slug from wp_trp_slug_translations table
        if ( $original_slug ) {
            $translated_slug = $wpdb->get_var( $wpdb->prepare( "
            SELECT translated FROM {$wpdb->prefix}trp_slug_translations
            WHERE original_id = %d AND language = %s
            LIMIT 1
        ", $original_slug, $language ) );

            // Return translated slug if found, otherwise return the original slug
            return $translated_slug ? $translated_slug : $slug;
        }

        // If no original slug ID is found, return the original slug
        return $slug;
    }

}
