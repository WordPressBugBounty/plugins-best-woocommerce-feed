<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed_Pro
 * @subpackage Rex_Product_Feed_Pro/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Rex_Product_Feed_Pro
 * @subpackage Rex_Product_Feed_Pro/public
 * @author     RexTheme <#>
 */
class Rex_Product_Feed_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rex_Product_Feed_Pro_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rex_Product_Feed_Pro_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$wpfm_fb_pixel_enabled = get_option( 'wpfm_fb_pixel_enabled', 'no' );
		if ( $wpfm_fb_pixel_enabled == 'yes' ) {
			$wpfm_fb_pixel_data = get_option( 'wpfm_fb_pixel_value' );
			if ( !empty( $wpfm_fb_pixel_data ) ) {
				wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rex-product-feed-addtocart.js', array( 'jquery' ), $this->version, false );
				wp_localize_script(
					$this->plugin_name,
					'wpfm_frontent_ajax',
					array(
						'ajax_url'   => admin_url( 'admin-ajax.php' ),
						'ajax_nonce' => wp_create_nonce( 'wpfm_add_to_cart_nonce' ),
					)
				);
			}
		}
	}


	/**
	 * Return product data for the Facebook Pixel AddToCart event.
	 *
	 * Open to guests, so the nonce only guards against CSRF. Access control is
	 * the product status check: only products any visitor can already see on
	 * the storefront are returned.
	 *
	 * @since 1.0.0
	 * @since x.x.x Rejects non-public products and invalid IDs.
	 *
	 * @return void
	 */
	public function wpfm_add_to_cart() {
		check_ajax_referer( 'wpfm_add_to_cart_nonce', 'security' );
		$currency   = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';
		$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		if ( ! $product_id ) {
			wp_send_json_error( null, 400 );
		}
		$product = wc_get_product( $product_id );
		if ( ! $product instanceof WC_Product || ! $this->is_public_product( $product ) ) {
			wp_send_json_error( null, 403 );
		}
		$price           = $product->get_price();
		$formatted_price = wc_format_decimal( $price, wc_get_price_decimals() );
		$product_title   = $product->get_name();
		$product_type    = $product->get_type();
		$cats            = $this->get_the_term_list( $product_id, 'product_cat' );
		$response        = array(
			'product_id'    => $product_id,
			'product_name'  => $product_title,
			'product_type'  => $product_type,
			'product_price' => $formatted_price,
			'currency'      => $currency,
			'cats'          => $cats,
		);
		wp_send_json( $response );
	}

	/**
	 * Check whether a product is visible to any storefront visitor.
	 *
	 * Hidden catalog visibility is allowed on purpose: the product page is
	 * public by direct link and shows the same data.
	 *
	 * @since x.x.x
	 *
	 * @param WC_Product $product Product or variation.
	 * @return bool True when published and not password-protected, including the parent of a variation.
	 */
	private function is_public_product( WC_Product $product ): bool {
		if ( 'publish' !== $product->get_status() || post_password_required( $product->get_id() ) ) {
			return false;
		}
		if ( $product->is_type( 'variation' ) ) {
			$parent = wc_get_product( $product->get_parent_id() );
			return $parent instanceof WC_Product && $this->is_public_product( $parent );
		}
		return true;
	}


	/**
	 * @param $id
	 * @param $taxonomy
	 * @param string   $before
	 * @param string   $sep
	 * @param string   $after
	 * @return string
	 */
	protected function get_the_term_list( $id, $taxonomy, $before = '', $sep = ' > ', $after = '' ) {
		$terms = wp_get_post_terms( $id, $taxonomy, array( 'orderby' => 'term_id' ) );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}
		$term_names = array();
		foreach ( $terms as $term ) {
			$term_names[] = $term->name;
		}
		ksort( $term_names );
		return $before . join( $sep, $term_names ) . $after;
	}

	/**
	 * Clears the WooCommerce cart if a specific query parameter is present in the URL.
	 *
	 * This function checks if a query parameter named 'rexfeed-clear-cart' is present in the URL
	 * and has a value of true. If the parameter is present and set to true, it empties the WooCommerce cart.
	 *
	 * @return void
	 * @since 7.3.14
	 */
	public function clear_woocommerce_cart_data() {
		// Check if the 'rexfeed-clear-cart' query parameter is set to true.
		$clear_cart = filter_input( INPUT_GET, 'rexfeed-clear-cart', FILTER_VALIDATE_BOOLEAN );

		if ( $clear_cart ) {
			// If the parameter is true, empty the WooCommerce cart.
			global $woocommerce;
			$woocommerce->cart->empty_cart();
		}
	}

}
