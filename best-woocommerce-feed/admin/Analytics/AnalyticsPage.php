<?php

namespace RexTheme\Analytics;

/**
 * Registers the "Analytics" submenu page. Unlike the removed Dashboard page,
 * this needs no menu-reorder hack — `register_page()` is called directly from
 * `Rex_Product_Feed_Admin::load_admin_pages()` (already running on
 * `admin_menu`), in call order right after the Settings registration, so it
 * lands in the submenu array in the right place without touching $submenu.
 */
class AnalyticsPage {

	const PAGE_SLUG  = 'wpfm-analytics';
	const CPT_PARENT = 'edit.php?post_type=product-feed';
	const CAPABILITY = 'manage_woocommerce';

	private string $page_hook = '';

	public function register_page(): void {
		$this->page_hook = add_submenu_page(
			self::CPT_PARENT,
			__( 'Analytics', 'rex-product-feed' ),
			__( 'Analytics', 'rex-product-feed' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue the React bundle only on this page's own screen hook.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( $hook !== $this->page_hook ) {
			return;
		}

		$asset_file = plugin_dir_path( dirname( __DIR__ ) ) . 'admin/assets/build/analytics/index.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			'wpfm-analytics',
			plugin_dir_url( dirname( __DIR__ ) ) . 'admin/assets/build/analytics/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'wpfm-analytics-style',
			plugin_dir_url( dirname( __DIR__ ) ) . 'admin/assets/build/analytics/analytics.css',
			array(),
			$asset['version']
		);

		wp_localize_script(
			'wpfm-analytics',
			'wpfmAnalytics',
			array(
				'nonce'          => wp_create_nonce( 'wp_rest' ),
				'adminUrl'       => admin_url(),
				'restUrl'        => rest_url( 'wpfm/v1' ),
				// Existing merchant-logo set (used by the setup wizard) reused
				// for UTM analytics' per-source channel icons — no new assets.
				'channelIconsUrl' => plugin_dir_url( dirname( __DIR__ ) ) . 'admin/assets/icon/setup-wizard-images/',
			)
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'rex-product-feed' ) );
		}

		echo '<div id="wpfm-analytics-root"></div>';
	}
}
