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
	public function show_review_request_markups() {
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
			'rex_feed_yandex_company_name',
			'rex_feed_yandex_old_price',
			'rex_feed_hotline_firm_id',
			'rex_feed_hotline_firm_name',
			'rex_feed_hotline_exchange_rate',
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

		if ( !isset( $feed_data[ 'rex_feed_update_on_product_change' ] ) ) {
			delete_post_meta( $post_id, '_rex_feed_update_on_product_change' );
		}
		if ( !isset( $feed_data[ 'rex_feed_cats_check_all_btn' ] ) ) {
			delete_post_meta( $post_id, '_rex_feed_cats_check_all_btn' );
		}
		if ( !isset( $feed_data[ 'rex_feed_tags_check_all_btn' ] ) ) {
			delete_post_meta( $post_id, '_rex_feed_tags_check_all_btn' );
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
                            <?php esc_html_e( 'and Regenerate', 'rex-product-feed' ); ?> -
                            <a href="<?php echo esc_url( 'https://rextheme.com/docs/wpfm-troubleshooting-for-common-issues/?utm_source=plugin&utm_medium=troubleshoot_button&utm_campaign=pfm_plugin' ); ?>" target="_blank">
                                <?php esc_html_e( 'View Doc', 'rex-product-feed' ); ?>
                            </a>
						</li>
						<li>
                            <?php esc_html_e( 'Use Strip Tags For Description', 'rex-product-feed' ); ?> -
                            <a href="<?php echo esc_url( 'https://rextheme.com/docs/wpfm-troubleshooting-for-common-issues/?utm_source=plugin&utm_medium=troubleshoot_button&utm_campaign=pfm_plugin' ); ?>" target="_blank">
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
						esc_html_e( 'Attach your temporary feed link, and screenshots of your feed attributes, feed settings, and the feed filter section in the email.', 'rex-product-feed' );
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
			$url = admin_url( 'post.php?action=edit&post=' . $new_post_id );
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
		if ( 'product-feed' === !$post->post_type ) {
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
        $feed_wcml_currency = $feed_retriever_obj->get_wcml_currency();
        if ( get_option( 'woocommerce_currency' ) === $feed_wcml_currency ) {
            return $product_price;
        }
		if ( ! empty( $product ) && ! empty( $type ) && $product->get_meta( '_wcml_custom_prices_status' ) ) {
			return $product->get_meta( "{$type}_{$feed_wcml_currency}" );
		}
		if ( defined( 'ICL_LANGUAGE_CODE' ) && $feed_retriever_obj->get_wcml_currency() === ICL_LANGUAGE_CODE ) {
			return $product_price;
		}
		if ( $feed_retriever_obj->is_wcml_active() ) {
			$updated_price = apply_filters( 'wcml_raw_price_amount',
				$product_price,
				$feed_retriever_obj->get_wcml_currency() );
			if ( ! empty( $updated_price ) ) {
				return $updated_price;
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
	 * Adds a translated value using TranslatePress.
	 *
	 * This function takes a value, a rule, and an instance, and returns the translated value
	 * based on the TranslatePress language setting. If the language is not set, it defaults to 'en_US'.
	 *
	 * @param mixed  $value    The value to be translated.
	 * @param mixed  $rule     The rule associated with the value.
	 * @param object $instance The instance containing the feed data.
	 * @return mixed The translated value.
	 *
	 * @since 7.4.20
	 */
    public function add_translate_press_value( $value, $rule, $instance ) {
        if ( wpfm_is_translatePress_active() ) {
            $language = $instance->feed->translatepress_language ?? 'en_US';
            $value = trp_translate( $value, $language, false );
        }
        return $value;
    }
}
