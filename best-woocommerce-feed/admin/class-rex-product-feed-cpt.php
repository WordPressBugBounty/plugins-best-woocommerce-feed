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
			'menu_icon'           => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/icon-svg/dashboard-icon.svg',
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
				$url = get_post_meta( $post_id, '_rex_feed_xml_file', true ) ?: get_post_meta( $post_id, 'rex_feed_xml_file', true );
				$url = esc_url( $url );
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
				}elseif ( 'daily' === $schedule || 'custom' === $schedule ) {
					$next_update = gmdate( $format, strtotime( '+1 days', strtotime( $last_updated ) ) );
				}elseif ( 'weekly' === $schedule ) {
					$next_update = gmdate( $format, strtotime( '+ 7 days', strtotime( $last_updated ) ) );
				}
				if ( 'no' !== $schedule ) {
					echo '<div><strong>' . esc_html__( 'Next Schedule: ', 'rex-product-feed' ) . '</strong><span style="text-decoration: dotted underline;" title="' . esc_attr( $next_update ) . '">' . esc_html( $next_update ) . '</span></div>';
				}
				break;
		}
	}
}
