<?php

namespace RexTheme\FeedEditor;

/**
 * Registers the new (React) feed-editor as a standalone admin page, hidden
 * from the nav menu, reachable at `admin.php?page=wpfm-feed-editor&feed={id}`
 * — behind the `wpfm_is_feed_editor_v2_enabled()` feature flag.
 *
 * Unlike the CPT's own post.php edit screen, this page owns its own
 * enqueue/data-loading and does not touch Rex_Product_Feed_Metabox at all —
 * the legacy editor remains fully intact and reachable at its existing URL.
 * Registered with a `null` parent slug — WordPress still fully wires up the
 * page hook, routing, and capability check, it just adds nothing to any
 * $submenu array, so there's no nav entry to hide. (An earlier version of
 * this class registered under a real parent and then unset the entry from
 * the live $submenu array — that broke admin.php's own capability lookup,
 * which depends on that array entry, and produced a "not allowed" error for
 * every user including administrators. Do not reintroduce that pattern.)
 */
class FeedEditorPage {

	const POST_TYPE  = 'product-feed';
	const PAGE_SLUG   = 'wpfm-feed-editor';

	private string $page_hook = '';

	public function init(): void {
		if ( ! wpfm_is_feed_editor_v2_enabled() ) {
			return;
		}
		add_action( 'admin_menu', array( $this, 'register_page' ) );
	}

	public function register_page(): void {
		$this->page_hook = add_submenu_page(
			null,
			__( 'Feed Editor', 'rex-product-feed' ),
			__( 'Feed Editor', 'rex-product-feed' ),
			'edit_posts',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Let the Pro plugin's own admin JS (rex-product-feed-pro-admin.js)
		// enqueue itself on this page too — Pro already exposes exactly this
		// extension point (best-woocommerce-feed-pro/admin/class-rex-product-feed-pro-admin.php::enqueue_scripts(),
		// gated on an allowlist of page hooks filterable via `wpfm_page_hooks`)
		// rather than gating on a fixed list of screens. Reusing it here means
		// Pro's script/nonce/localized data stay exactly as Pro defines them,
		// instead of this plugin re-enqueueing Pro's own script itself.
		add_filter( 'wpfm_page_hooks', array( $this, 'add_page_hook_for_pro' ) );
	}

	public function add_page_hook_for_pro( array $hooks ): array {
		if ( $this->page_hook ) {
			$hooks[] = $this->page_hook;
		}
		return $hooks;
	}

	/**
	 * Enqueue the React bundle only on this page.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( $hook !== $this->page_hook ) {
			return;
		}

		$asset_file = plugin_dir_path( dirname( __DIR__ ) ) . 'admin/assets/build/feed-editor/index.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			'wpfm-feed-editor',
			plugin_dir_url( dirname( __DIR__ ) ) . 'admin/assets/build/feed-editor/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'wpfm-feed-editor-style',
			plugin_dir_url( dirname( __DIR__ ) ) . 'admin/assets/build/feed-editor/feed-editor.css',
			array(),
			$asset['version']
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$feed_id = isset( $_GET['feed'] ) ? absint( $_GET['feed'] ) : 0;

		wp_localize_script(
			'wpfm-feed-editor',
			'wpfmFeedEditor',
			array(
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'adminUrl'  => admin_url(),
				'restUrl'   => rest_url( 'wpfm/v1' ),
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'ajaxNonce' => wp_create_nonce( 'rex-wpfm-ajax' ),
				'feedId'    => $feed_id,
			)
		);
	}

	public function render_page(): void {
		echo '<div class="wrap"><div id="wpfm-feed-editor-root"></div></div>';
	}
}
