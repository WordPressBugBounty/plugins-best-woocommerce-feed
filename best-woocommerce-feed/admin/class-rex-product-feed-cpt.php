<?php
/**
 * The admin-specific functionality of the plugin
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Metabox
 * @subpackage Rex_Product_Feed/admin
 */

/**
 * The admin-specific functionality of the plugin
 *
 * Defines all the Metaboxes for Products
 *
 * @package    Rex_Product_Metabox
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Product_CPT {

	/**
	 * Register all metaboxes
	 *
	 * @since    1.0.0
	 */
	public function register_cpt() {
		$this->create_post_type();
		add_filter( 'manage_product-feed_posts_columns', array( $this, 'product_feed_custom_columns' ) );
		add_action( 'manage_product-feed_posts_custom_column', array( $this, 'fill_product_feed_columns' ), 10, 2 );
	}

	/**
	 * Creates a custom post type for Product Feeds
	 *
	 * @since    7.3.19
	 */
	private function create_post_type() {
		$labels = [
			'name'               => _x( 'Product Feeds', 'Post Type General Name', 'rex-product-feed' ),
			'singular_name'      => _x( 'Product Feed', 'Post Type General Name', 'rex-product-feed' ),
			'all_items'          => __( 'All Product Feeds', 'rex-product-feed' ),
			'menu_name'          => _x( 'Product Feeds', 'Post Type General Name', 'rex-product-feed' ),
			'add_new'            => __( 'Add New Feed', 'rex-product-feed' ),
			'add_new_item'       => __( 'Add New Product Feed', 'rex-product-feed' ),
			'edit_item'          => __( 'Edit Product Feed', 'rex-product-feed' ),
			'new_item'           => __( 'New Product Feed', 'rex-product-feed' ),
			'view_item'          => __( 'View Product Feed', 'rex-product-feed' ),
			'search_items'       => __( 'Search Product Feeds', 'rex-product-feed' ),
			'not_found'          => __( 'No product feeds found', 'rex-product-feed' ),
			'not_found_in_trash' => __( 'No product feeds found in trash', 'rex-product-feed' ),
			'parent_item_colon'  => __( 'Parent Product Feed:', 'rex-product-feed' ),
		];

		$args = [
			'label'               => 'product-feed',
			'labels'              => $labels,
			'supports'            => [ 'title' ],
			'hierarchical'        => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => $this->get_menu_icon(),
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'rewrite'             => [ 'slug' => 'product-feed' ],
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'public'              => true,
			'capability_type'     => 'post'
		];

		register_post_type( 'product-feed', $args );
	}

	/**
	 * Register custom admin column for product feed
	 *
	 * @return array
	 * @since 6.1.2
	 */
	public function product_feed_custom_columns() {
		return [
			'cb'               => '<input type="checkbox" />',
			'title'            => esc_html__( 'Title', 'rex-product-feed' ),
			'merchant'         => esc_html__( 'Merchant', 'rex-product-feed' ),
			'xml_feed'         => esc_html__( 'Feed File', 'rex-product-feed' ),
			'refresh_interval' => esc_html__( 'Refresh Interval', 'rex-product-feed' ),
			'feed_status'      => esc_html__( 'Status', 'rex-product-feed' ),
			'update_feed'      => esc_html__( 'Update Feed', 'rex-product-feed' ),
			'view_feed'        => esc_html__( 'View/Download', 'rex-product-feed' ),
			'total_products'   => esc_html__( 'Total Products', 'rex-product-feed' ),
			'date'             => esc_html__( 'Date', 'rex-product-feed' ),
			'scheduled'        => esc_html__( 'Updated', 'rex-product-feed' )
		];
	}

	/**
	 * Fill contents for custom products
	 *
	 * @param string     $column Column name.
	 * @param string|int $post_id Feed/post ID.
	 * @since 6.1.2
	 */
	public function fill_product_feed_columns( $column, $post_id ) {
		$feed_update_status = get_post_meta( $post_id, '_rex_feed_status', true ) ?: get_post_meta( $post_id, 'rex_feed_status', true );
		$disabled = '';
		
		if( 'processing' === $feed_update_status || 'In queue' === $feed_update_status ) {
			$disabled = 'disabled="disabled" style="pointer-events: none;"';
		}

		switch ( $column ) {
			case 'merchant':
				$feed_merchant = get_post_meta( $post_id, '_rex_feed_merchant', true ) ?: get_post_meta( $post_id, 'rex_feed_merchant', true );
				echo esc_html( ucwords( str_replace( '_', ' ', $feed_merchant ) ) );
				break;
			case 'xml_feed':
				$feed_url = get_post_meta( $post_id, '_rex_feed_xml_file', true ) ?: get_post_meta( $post_id, 'rex_feed_xml_file', true );
				echo esc_url( $feed_url );
				break;
			case 'refresh_interval':
				$schedule    = get_post_meta( $post_id, '_rex_feed_schedule', true ) ?: get_post_meta( $post_id, 'rex_feed_schedule', true );
				$custom_time = 'custom' === $schedule ? get_post_meta( $post_id, 'rex_feed_custom_time', true ) . ':00' : '';

				if ( 'custom' === $schedule ) {
					$custom_time = get_post_meta( $post_id, '_rex_feed_custom_time', true ) ?: get_post_meta( $post_id, 'rex_feed_custom_time', true );
					$custom_time = $custom_time ? $custom_time . ':00' : '';
				}
				$format = get_option( 'time_format', 'g:i a' );

				echo esc_html( ucwords( $schedule ) );
				if ( 'custom' === $schedule && '' !== $custom_time ) {
					$time = gmdate( $format, strtotime( $custom_time ) );
					echo "<br>";
					echo 'Daily at ' . esc_html( $time );
				}
				break;
			case 'feed_status':
				if ( $feed_update_status ) {
					if ( 'processing' === $feed_update_status ) {
						?>
						<script>
							(function($) {
								$(document).ready( function ( e ) {
									const post_id = '<?php echo esc_attr( $post_id ); ?>';
									const id      = '#post-' + post_id;
									$( id + ' .view_feed a' ).attr( 'disabled', 'disabled' );
									$( id + ' .view_feed a' ).css( 'pointer-events', 'none' );
								} );
							})(jQuery);
						</script>
						<?php
						echo '<div class="blink">' . esc_html( ucfirst( $feed_update_status ) ) . '<span>.</span><span>.</span><span>.</span></div>';
					}
					else {
						echo esc_html( ucfirst( $feed_update_status ) );
					}
				}
				else {
					echo 'Completed';
				}
				break;
			case 'update_feed' :
				echo '<a class="button rex-feed-update-single-feed" data-feed-id="' . $post_id . '" ' . $disabled . '>' . __( 'Update', 'rex-product-feed' ) .  '</a> ';
				break;
			case 'view_feed':
				$feed_status = get_post_status( $post_id );
				$disabled    = 'draft' === $feed_status ? 'disabled="disabled" style="pointer-events: none;"' : '';
				$url         = get_post_meta( $post_id, '_rex_feed_xml_file', true ) ?: get_post_meta( $post_id, 'rex_feed_xml_file', true );
				$url         = esc_url( $url );
				echo '<a target="_blank" class="button" href="' . esc_url( $url ) . '" ' . $disabled . '>' . __( 'View', 'rex-product-feed' ) . '</a> ';
				echo '<a target="_blank" class="button" href="' . esc_url( $url ) . '" ' . $disabled . ' download>' . __( 'Download', 'rex-product-feed' ) . '</a>';
				break;
			case 'total_products':
				$total_products = get_post_meta( $post_id, '_rex_feed_total_products', true ) ?: get_post_meta( $post_id, 'rex_feed_total_products', true );
				$total_products = $total_products ?: array(
					'total'           => 0,
					'simple'          => 0,
					'variable'        => 0,
					'variable_parent' => 0,
					'group'           => 0,
				);

				if ( !array_key_exists( 'variable_parent', $total_products ) ) {
					$total_products[ 'variable_parent' ] = 0;
				}

				$product_count = get_post_meta( $post_id, '_rex_feed_total_products_for_all_feed', true ) ?: get_post_meta( $post_id, 'rex_feed_total_products_for_all_feed', true );
				$product_count = $product_count ?: $total_products[ 'total' ];
				$product_count = isset( $total_products[ 'total' ] ) && $product_count < $total_products[ 'total' ] ? $total_products[ 'total' ] : $product_count;

				echo '<ul style="margin: 0;">';
				echo '<li><b>' . esc_html__( 'Total products : ', 'rex-product-feed' ) . esc_html( $total_products[ 'total' ] ) . '/' . esc_html( $product_count ) . '</b></li>';
				if ( isset( $total_products[ 'total_reviews' ] ) ) {
					echo '<li><b>' . esc_html__( 'Total reviews : ', 'rex-product-feed' ) . esc_html( $total_products[ 'total_reviews' ] ) . '</b></li>';
				}
				echo '<li><b>' . esc_html__( 'Simple products : ', 'rex-product-feed' ) . esc_html( $total_products['simple'] ) . '</b></li>';
				echo '<li><b>' . esc_html__( 'Variable parent : ', 'rex-product-feed' ) . esc_html( $total_products['variable_parent'] ) . '</b></li>';
				echo '<li><b>' . esc_html__( 'Variations : ', 'rex-product-feed' ) . esc_html( $total_products['variable'] ) . '</b></li>';
				echo '<li><b>' . esc_html__( 'Group products : ', 'rex-product-feed' ) . esc_html( $total_products['group'] ) . '</b></li>';
				echo '</ul><b>';
				break;
			case 'scheduled':
				$format         = get_option( 'time_format', 'g:i a' ) . ', ' . get_option( 'date_format', 'F j, Y' );
				$last_updated   = get_post_meta( $post_id, 'updated', true );
				$formatted_time = '';

				if ( $last_updated ) {
					$formatted_time = gmdate( $format, strtotime( $last_updated ) );
				}

				$schedule = get_post_meta( $post_id, '_rex_feed_schedule', true ) ?: get_post_meta( $post_id, 'rex_feed_schedule', true );

				echo '<div><strong>' . esc_html__( 'Last Updated: ', 'rex-product-feed' ) . '</strong><span style="text-decoration: dotted underline;" title="' . esc_attr( $formatted_time ) . '">' . esc_html( $formatted_time ) . '</span></div></br>';

                $next_update = '';
                if ( 'hourly' === $schedule ) {
                    $next_update = gmdate( $format, strtotime( '+1 hours', strtotime( $last_updated ) ) );
                } elseif ( 'daily' === $schedule ) {
                    $next_update = gmdate( $format, strtotime( '+1 days', strtotime( $last_updated ) ) );
                } elseif ( 'weekly' === $schedule ) {
                    $next_update = gmdate( $format, strtotime( '+7 days', strtotime( $last_updated ) ) );
                } elseif ( 'custom' === $schedule ) {
                    $custom_time = get_post_meta( $post_id, '_rex_feed_custom_time', true ) ?: get_post_meta( $post_id, 'rex_feed_custom_time', true );
                    $custom_time = $custom_time ? $custom_time . ':00' : '00:00:00';
                    $next_date = gmdate( 'Y-m-d', strtotime( '+1 day', strtotime( $last_updated ) ) );
                    $next_schedule_time = $next_date . ' ' . $custom_time;
                    $format = get_option( 'time_format', 'g:i a' ) . ', F j, Y';
                    $next_update = gmdate( $format, strtotime( $next_schedule_time ) );
                }

				if ( 'no' !== $schedule ) {
					echo '<div><strong>' . esc_html__( 'Next Schedule: ', 'rex-product-feed' ) . '</strong><span style="text-decoration: dotted underline;" title="' . esc_attr( $next_update ) . '">' . esc_html( $next_update ) . '</span></div>';
				}
				break;
		}
	}

	/**
 	 * Gets the SVG icon for menu
 	 *
 	 * @desc Gets the SVG icon for menu
 	 * @return string
 	 * @since 7.4.36
 	 */
	  private function get_menu_icon() {
		return 'data:image/svg+xml;base64,' . base64_encode(        //phpcs:ignore
			'<?xml version="1.0" encoding="utf-8"?>
			<!-- Generator: Adobe Illustrator 27.1.1, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
			
			<svg width="20" height="17" viewBox="0 0 20 17" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M8.22898 16.0601C8.94144 16.0601 9.519 15.4825 9.519 14.7701C9.519 14.0576 8.94144 13.48 8.22898 13.48C7.51652 13.48 6.93896 14.0576 6.93896 14.7701C6.93896 15.4825 7.51652 16.0601 8.22898 16.0601Z" fill="#A8AAAD"/>
			<path d="M14.2058 16.0365C14.9182 16.0365 15.4958 15.4589 15.4958 14.7464C15.4958 14.034 14.9182 13.4564 14.2058 13.4564C13.4933 13.4564 12.9158 14.034 12.9158 14.7464C12.9158 15.4589 13.4933 16.0365 14.2058 16.0365Z" fill="#A8AAAD"/>
			<path d="M0.346855 3.70433L2.74936 4.09489C2.87955 4.11856 2.97423 4.23691 2.95056 4.37893L2.89138 4.78132C2.86771 4.91151 2.74936 5.00619 2.60734 4.98252L0.204835 4.58013C0.0746495 4.55646 -0.0200321 4.43811 0.00363799 4.29609L0.0628131 3.8937C0.0983181 3.77534 0.216669 3.68066 0.346855 3.70433Z" fill="#A8AAAD"/>
			<path d="M1.53045 5.62168L3.25836 5.90572C3.38855 5.92939 3.48323 6.04774 3.45956 6.18976L3.40038 6.59215C3.37671 6.72234 3.25836 6.81702 3.11634 6.79335L1.38843 6.50931C1.25824 6.48564 1.16356 6.36729 1.18723 6.22527L1.24641 5.82287C1.27008 5.69269 1.40026 5.59801 1.53045 5.62168Z" fill="#A8AAAD"/>
			<path d="M0.417654 7.10105L3.83798 7.6573C3.96816 7.68097 4.06284 7.79932 4.03917 7.94134L3.98 8.34373C3.95633 8.47391 3.83798 8.56859 3.69596 8.54492L0.275634 7.98868C0.145449 7.96501 0.0507688 7.84666 0.0744388 7.70464L0.133614 7.30225C0.169119 7.17206 0.287468 7.07738 0.417654 7.10105Z" fill="#A8AAAD"/>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M20 3.46766C20 5.3828 18.4475 6.93532 16.5324 6.93532C14.6172 6.93532 13.0647 5.3828 13.0647 3.46766C13.0647 1.55252 14.6172 0 16.5324 0C18.4475 0 20 1.55252 20 3.46766ZM19.6092 3.46765C19.6092 5.16709 18.2316 6.54475 16.5321 6.54475C14.8327 6.54475 13.455 5.16709 13.455 3.46765C13.455 1.76821 14.8327 0.390544 16.5321 0.390544C18.2316 0.390544 19.6092 1.76821 19.6092 3.46765Z" fill="#A8AAAD"/>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M13.0998 2.97173L5.40038 2.99425C5.2702 2.99425 5.15185 2.92324 5.12818 2.80489L4.5956 1.36102C4.47725 1.04147 4.19321 0.816604 3.86183 0.769264L1.95639 0.485224C1.90905 0.473389 1.86171 0.473389 1.81437 0.473389C1.30547 0.473389 0.903076 0.887614 0.903076 1.39652C0.903076 1.84625 1.23446 2.22497 1.68419 2.29598L2.53631 2.41433C2.89136 2.47351 3.18724 2.73388 3.28192 3.07709L5.81461 12.0835C5.92112 12.4859 6.28801 12.7581 6.70224 12.7581L15.7205 12.7226C16.1466 12.7226 16.5135 12.4268 16.6081 12.0125L17.8422 6.67816C17.4379 6.84331 16.9955 6.93433 16.5318 6.93433C14.617 6.93433 13.0647 5.38203 13.0647 3.46716C13.0647 3.29895 13.0767 3.13354 13.0998 2.97173ZM14.1465 6.34354C14.1465 6.52137 14.0435 6.6689 13.8981 6.73845C13.8613 7.29068 13.6795 8.69924 12.6198 9.49167C11.7085 10.1189 10.5131 10.1189 9.60184 9.49167C8.55388 8.7175 8.3645 7.32576 8.33076 6.75235C8.14545 6.69884 8.016 6.5274 8.016 6.3317C8.016 6.095 8.2172 5.8938 8.4539 5.8938C8.70243 5.8938 8.89179 6.095 8.89179 6.3317C8.89179 6.50252 8.79675 6.64538 8.66046 6.71803C8.68914 7.23662 8.85842 8.54294 9.8267 9.2668C10.1936 9.53901 10.6433 9.68103 11.1049 9.68103C11.5665 9.68103 12.0162 9.53901 12.3831 9.2668C13.3488 8.56777 13.5354 7.29687 13.5707 6.75964C13.3933 6.70148 13.2707 6.534 13.2707 6.34354C13.2707 6.095 13.4719 5.90564 13.7086 5.90564C13.9453 5.90564 14.1465 6.10684 14.1465 6.34354Z" fill="#A8AAAD"/>
			<path d="M18.2179 5.2429H17.851C17.78 5.2429 17.7326 5.19556 17.7326 5.12455C17.6735 3.66884 16.5018 2.49717 15.0461 2.438C14.9751 2.438 14.9277 2.39066 14.9277 2.31965V1.95276C14.9277 1.88175 14.9751 1.83441 15.0461 1.83441C15.0461 1.83441 15.0461 1.83441 15.0579 1.83441C16.845 1.90542 18.277 3.33746 18.3481 5.12455C18.3481 5.18372 18.2889 5.2429 18.2179 5.2429C18.2179 5.2429 18.2297 5.2429 18.2179 5.2429ZM17.1172 5.2429H16.7503C16.6793 5.2429 16.632 5.19556 16.632 5.12455C16.5728 4.27243 15.8982 3.58599 15.0461 3.52682C14.9751 3.52682 14.9277 3.47948 14.9277 3.40847V3.04158C14.9277 2.97057 14.9751 2.92323 15.0461 2.92323H15.0579C16.2414 2.99424 17.1764 3.92921 17.2474 5.11271C17.2474 5.18372 17.2001 5.2429 17.1172 5.2429C17.129 5.2429 17.129 5.2429 17.1172 5.2429ZM15.413 5.2429C15.1408 5.2429 14.9277 5.02987 14.9277 4.75766C14.9277 4.48546 15.1408 4.27243 15.413 4.27243C15.6852 4.27243 15.8982 4.48546 15.8982 4.75766C15.8982 5.01803 15.6852 5.2429 15.413 5.2429Z" fill="#A8AAAD"/>
			</svg>'
		);
	}
}
