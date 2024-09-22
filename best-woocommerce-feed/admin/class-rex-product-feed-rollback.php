<?php
/**
 * Class Rex_Feed_Rollback
 *
 * @package Product Feed Manager for WooCommerce
 */

/**
 * This class is responsible for the methods to rollback versions.
 *
 * @package Product Feed Manager for WooCommerce
 */
class Rex_Feed_Rollback {

	/**
	 * Package URL
	 *
	 * @var String Package URL.
	 */
	protected $package_url;


	/**
	 * WPFM versions
	 *
	 * @var String WPFM versions.
	 */
	protected $version;


	/**
	 * Plugin slug
	 *
	 * @var String Plugin slug.
	 */
	protected $plugin_slug;


	/**
	 * Plugin name
	 *
	 * @var String Plugin name.
	 */
	protected $plugin_name;


	/**
	 * Rollback constructor.
	 *
	 * @param array $args Constructor arguments.
	 */
	public function __construct( $args = array() ) {
		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}
	}


	/**
	 * Perform rollback version(s)
	 *
	 * @return void
	 * @since 7.2.5
	 */
	public function feeds_rollback() {
		check_admin_referer( 'rex_feed_rollback' );
		$data = function_exists( 'rex_feed_get_sanitized_get_post' ) ? rex_feed_get_sanitized_get_post() : array();
		$data = isset( $data[ 'get' ] ) ? $data[ 'get' ] : array();

		$rollback_versions = function_exists( 'rex_feed_get_roll_back_versions' ) ? rex_feed_get_roll_back_versions() : array();
		if ( empty( $data[ 'version' ] ) || !in_array( $data[ 'version' ], $rollback_versions ) ) {
			wp_die( esc_html__( 'Error occurred, The version selected is invalid. Try selecting different version.', 'rex-product-feed' ) );
		}

		$plugin_slug    = defined( 'WPFM_SLUG' ) ? WPFM_SLUG : 'best-woocommerce-feed';
		$plugin_version = sanitize_text_field( $data[ 'version' ] );

		$rollback = new Rex_Feed_Rollback(
			array(
				'version'     => $plugin_version,
				'plugin_name' => WPFM_BASE,
				'plugin_slug' => $plugin_slug,
				'package_url' => sprintf( 'https://downloads.wordpress.org/plugin/%s.%s.zip', $plugin_slug, $plugin_version ),
			)
		);

		$rollback->run();
		self::update_feed_scope_data();

		wp_die(
			'',
			esc_html__( 'Rollback to Previous Version', 'rex-product-feed' ),
			filter_var(
				array(
					'response' => 200,
				)
			)
		);
	}


	/**
	 * Updates feed product scoping option
	 * before rolling back to previous plugin version
	 *
	 * @return void
	 * @since 7.2.5
	 */
	private static function update_feed_scope_data() {
		$args     = array(
			'fields'      => 'ids',
			'post_type'   => 'product-feed',
			'post_status' => 'publish',
		);
		$feed_ids = get_posts( $args );

		foreach ( $feed_ids as $id ) {
			$product_scope = get_post_meta( $id, '_rex_feed_products', true ) ?: get_post_meta( $id, 'rex_feed_products', true );

			if ( 'all' === $product_scope ) {
				$custom_filter_option = get_post_meta( $id, '_rex_feed_custom_filter_option', true ) ?: get_post_meta( $id, 'rex_feed_custom_filter_option', true );
				if ( 'added' === $custom_filter_option ) {
					update_post_meta( $id, '_rex_feed_products', 'filter' );
				}
			}
		}
	}


	/**
	 * Print inline styles
	 *
	 * @since 7.2.5
	 */
	private function print_inline_style() {
		?>
		<style>
			.wrap {
				overflow: hidden;
				max-width: 850px;
				margin: auto;
				font-family: Courier, monospace;
			}

			h1 {
				background: #6E42D2;
				text-align: center;
				color: #fff !important;
				padding: 70px !important;
				text-transform: uppercase;
				letter-spacing: 1px;
			}

			h1 img {
				max-width: 300px;
				display: block;
				margin: auto auto 50px;
			}
		</style>
		<?php
	}


	/**
	 * Apply package.
	 *
	 * Change the plugin data when WordPress checks for updates. This method
	 * modifies package data to update the plugin from a specific URL containing
	 * the version package.
	 *
	 * @since 7.2.5
	 */
	protected function apply_package() {
		$update_plugins = get_site_transient( 'update_plugins' );
		if ( ! is_object( $update_plugins ) ) {
			$update_plugins = new \stdClass();
		}

		$plugin_info                                    = new \stdClass();
		$plugin_info->new_version                       = $this->version;
		$plugin_info->slug                              = $this->plugin_slug;
		$plugin_info->package                           = $this->package_url;
		$plugin_info->url                               = 'http://rextheme.com/';
		$update_plugins->response[ $this->plugin_name ] = $plugin_info;
		set_site_transient( 'update_plugins', $update_plugins );
	}

	/**
	 * Upgrade.
	 *
	 * Run WordPress upgrade to rollback WPFM to previous version.
	 *
	 * @since 7.2.5
	 */
	protected function upgrade() {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$upgrader_args = array(
			'url'    => 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $this->plugin_name ),
			'plugin' => $this->plugin_name,
			'nonce'  => 'upgrade-plugin_' . $this->plugin_name,
			'title'  => esc_html__( 'Rollback to Product Feed Manager for WooCommerce previous version', 'rex-product-feed' ),
		);

		$this->print_inline_style();

		$upgrader = new \Plugin_Upgrader( new \Plugin_Upgrader_Skin( $upgrader_args ) );
		$upgrader->upgrade( $this->plugin_name );
	}

	/**
	 * Run.
	 *
	 * Rollback Product Feed Manager for WooCommerce to previous versions.
	 *
	 * @since 7.2.5
	 */
	public function run() {
		$this->apply_package();
		$this->upgrade();
	}
}
