<?php

namespace RexTheme\FeedEditor;

/**
 * Saves attribute-mapping (`fc`), product-filter (`ff`), and Pro feed-rules
 * (`fr`) data for the new standalone feed-editor page.
 *
 * This MUST be an admin-ajax action, not a REST route (unlike
 * FeedEditorEndpoint's read-only GET) — found by live-testing a REST version
 * of this endpoint against a real install with the Pro plugin active: the
 * hook fired, the response was 200 OK, but Pro's save_draft_data() listener
 * never ran and `_rex_feed_feed_config_rules` was never written. Root cause,
 * traced into the Pro plugin's own source
 * (best-woocommerce-feed-pro/includes/class-rex-product-feed-pro.php,
 * should_boot_admin_layer()): Pro only registers its admin hooks
 * (define_admin_hooks(), including the save_draft_data listener) when
 * is_admin() is true. is_admin() is FALSE during a `/wp-json/` REST request
 * (REST requests don't go through wp-admin's bootstrap), but TRUE during an
 * admin-ajax.php request — which is exactly why the plugin's existing
 * settings/filter drawer saves (drawerSync.js) already use admin-ajax and
 * work correctly, and why this endpoint must too.
 *
 * Registered via the same `wp_ajax_helper()` package Free's own AJAX
 * actions use (admin/class-rex-product-feed-ajax.php), for the same
 * per-action-nonce wire format the JS side (wpAjaxHelper.js) already
 * implements.
 */
class AjaxController {

	const POST_TYPE = 'product-feed';

	public function init(): void {
		if ( ! wpfm_is_feed_editor_v2_enabled() ) {
			return;
		}
		add_action( 'admin_init', array( $this, 'register_actions' ) );
	}

	/**
	 * Allowlisted extension-point hooks the "raw HTML bridge" (render_hook())
	 * can fire — see design.md's extension-point checklist. A fixed
	 * allowlist, not an arbitrary client-supplied hook name: firing
	 * do_action()/apply_filters() on a name the client controls would be a
	 * real vulnerability (any hook happening to have a security-sensitive
	 * listener becomes client-triggerable), so render_hook() only ever
	 * dispatches to one of these known keys.
	 */
	const BRIDGEABLE_HOOKS = array(
		'wpfm_product_filter_fields',
		'rex_feed_before_taxonomy_fields',
		'rex_feed_after_static_input',
		'rex_wpfm_attributes',
		'wpfm_pro_feed_attribute_type_render',
		'rexfeed_meta_attribute_types',
		'rex_feed_after_autogenerate_options_field',
		'rexfeed_auto_generation_option_markups',
		'wpfm_option_schedules',
		'wpfm_is_premium',
		'wpfm_is_premium_activate',
	);

	public function register_actions(): void {
		$validations = array(
			'logged_in' => true,
			'user_can'  => 'edit_posts',
		);

		wp_ajax_helper()->handle( 'wpfm-feed-editor-save-mapping' )
			->with_callback( array( $this, 'save_mapping' ) )
			->with_validation( $validations );

		wp_ajax_helper()->handle( 'wpfm-feed-editor-render-hook' )
			->with_callback( array( $this, 'render_hook' ) )
			->with_validation( $validations );
	}

	public function save_mapping( $payload ) {
		$id = isset( $payload['feed_id'] ) ? absint( $payload['feed_id'] ) : 0;

		if ( ! current_user_can( 'edit_post', $id ) ) {
			return array(
				'saved'   => false,
				'message' => __( 'You are not allowed to edit this feed.', 'rex-product-feed' ),
			);
		}

		$post = get_post( $id );
		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			return array( 'saved' => false, 'message' => __( 'Feed not found.', 'rex-product-feed' ) );
		}

		$fc = $this->decode_rows( $payload['fc'] ?? '' );
		$ff = $this->decode_rows( $payload['ff'] ?? '' );
		$fr = $this->decode_rows( $payload['fr'] ?? '' );

		// Only fc/ff — matches exactly what save_draft_feed_meta() writes
		// directly. `_rex_feed_feed_config_rules` (fr) is NOT written here:
		// that key belongs exclusively to the Pro plugin's own
		// save_draft_data() listener (Free's save_draft_feed_meta() never
		// writes it either) — it's produced below by firing the hook, not
		// by this endpoint duplicating Pro's write.
		update_post_meta( $id, '_rex_feed_feed_config', $fc );
		update_post_meta( $id, '_rex_feed_feed_config_filter', $ff );

		/**
		 * Same hook `save_draft_feed_meta()` fires after a classic-form save
		 * (admin/class-rex-product-feed-actions.php:250) — fired directly
		 * here so the Pro plugin's save_draft_data() listener keeps running
		 * unmodified, without this endpoint depending on WordPress's
		 * draft_product-feed post-status-transition hook. If Pro isn't
		 * active, nothing listens and `fr` is simply not persisted anywhere
		 * — identical to today's behavior without Pro.
		 *
		 * @param int   $id        Feed post id.
		 * @param array $feed_data Shaped like the classic form's parsed POST data.
		 */
		do_action( 'rex_feed_after_draft_feed_config_saved', $id, array(
			'fc' => $fc,
			'ff' => $ff,
			'fr' => $fr,
		) );

		return array( 'saved' => true );
	}

	/**
	 * "Raw HTML bridge" backend — fires one allowlisted legacy extension-point
	 * hook and returns whatever HTML/data its listeners produced, so a React
	 * component (src/feed-editor/components/HookBridge.jsx) can render it.
	 * This foundation change only needs to prove each hook still fires and
	 * its output still renders (design.md's extension-point checklist); the
	 * real Configuration-tab context (actual attribute-mapping rows, actual
	 * saved filter state) is for whichever tab change owns that UI.
	 *
	 * Call signatures below are copied exactly from where each hook fires
	 * today (admin/class-rex-product-feed-metabox.php,
	 * admin/partials/feed-config-metabox-display.php,
	 * admin/class-rex-feed-attributes.php,
	 * admin/feed-templates/abstract-rex-feed-template.php) — not guessed.
	 *
	 * Must be admin-ajax, not REST — same is_admin() reasoning as
	 * save_mapping() above; Pro's listeners for these hooks are registered
	 * by the same admin-only bootstrap.
	 */
	public function render_hook( $payload ) {
		$id   = isset( $payload['feed_id'] ) ? absint( $payload['feed_id'] ) : 0;
		$hook = isset( $payload['hook'] ) ? sanitize_key( $payload['hook'] ) : '';

		if ( ! current_user_can( 'edit_post', $id ) ) {
			return array( 'error' => __( 'You are not allowed to edit this feed.', 'rex-product-feed' ) );
		}

		if ( ! in_array( $hook, self::BRIDGEABLE_HOOKS, true ) ) {
			return array( 'error' => __( 'Unknown extension point.', 'rex-product-feed' ) );
		}

		$data = null;
		ob_start();

		try {
			$this->fire_bridged_hook( $hook, $id, $data );
		} catch ( \Throwable $e ) {
			ob_end_clean();
			// A legacy/Pro listener throwing shouldn't take down this whole
			// response as an uncaught fatal (WP's own "critical error" HTML
			// page instead of JSON, which breaks the client's JSON.parse) —
			// degrade to a reported error instead.
			return array( 'error' => $e->getMessage() );
		}

		$html = ob_get_clean();

		return array( 'html' => $html, 'data' => $data );
	}

	private function fire_bridged_hook( string $hook, int $id, &$data ): void {
		switch ( $hook ) {
			case 'wpfm_product_filter_fields':
				// admin/class-rex-product-feed-metabox.php:245 — do_action( 'wpfm_product_filter_fields', $this->prefix ).
				do_action( 'wpfm_product_filter_fields', 'rex_feed_' );
				break;

			case 'rex_feed_before_taxonomy_fields':
				// admin/class-rex-product-feed-metabox.php:237 — do_action( 'rex_feed_before_taxonomy_fields', $this->prefix ).
				do_action( 'rex_feed_before_taxonomy_fields', 'rex_feed_' );
				break;

			case 'rex_feed_after_static_input':
				// admin/partials/feed-config-metabox-display.php:66 (blank template row) —
				// do_action( 'rex_feed_after_static_input', $feed_template, $key, '' ).
				$merchant = get_post_meta( $id, '_rex_feed_merchant', true ) ?: 'google';
				$mappings = get_post_meta( $id, '_rex_feed_feed_config', true ) ?: array();
				if ( class_exists( 'Rex_Feed_Template_Factory' ) ) {
					// Leading `\` required: Rex_Feed_Template_Factory is a
					// global-namespace class (legacy convention), but this
					// file is namespaced — an unqualified reference here
					// resolves to RexTheme\FeedEditor\Rex_Feed_Template_Factory
					// at compile time and fatals with "Class ... not found".
					// class_exists() above took a literal string, so it
					// wasn't namespace-resolved and passed regardless —
					// don't rely on that check alone as proof this works.
					$feed_template = \Rex_Feed_Template_Factory::build( $merchant, $mappings );
					do_action( 'rex_feed_after_static_input', $feed_template, 0, '' );
				}
				break;

			case 'rex_feed_after_autogenerate_options_field':
				// admin/partials/rex-feed-product-settings-section.php:71 — do_action(), no args.
				do_action( 'rex_feed_after_autogenerate_options_field' );
				break;

			case 'rexfeed_auto_generation_option_markups':
				// admin/partials/rex-feed-product-settings-section.php:56 — do_action(), no args.
				do_action( 'rexfeed_auto_generation_option_markups' );
				break;

			case 'rex_wpfm_attributes':
				// admin/class-rex-feed-attributes.php:115 — apply_filters over the attribute-group array.
				$data = apply_filters( 'rex_wpfm_attributes', array( 'Sample Group' => array( 'sample' => 'Sample' ) ) );
				break;

			case 'wpfm_pro_feed_attribute_type_render':
				// admin/feed-templates/abstract-rex-feed-template.php:201.
				$data = apply_filters( 'wpfm_pro_feed_attribute_type_render', array(
					'meta'   => __( 'Attribute', 'rex-product-feed' ),
					'static' => __( 'Static', 'rex-product-feed' ),
				) );
				break;

			case 'rexfeed_meta_attribute_types':
				// admin/partials/feed-config-metabox-display.php:132.
				$data = apply_filters( 'rexfeed_meta_attribute_types', array( 'meta' ) );
				break;

			case 'wpfm_option_schedules':
				// admin/class-rex-product-feed-metabox.php:186-192.
				$data = apply_filters( 'wpfm_option_schedules', array(
					'no'     => __( 'No Interval', 'rex-product-feed' ),
					'hourly' => __( 'Hourly', 'rex-product-feed' ),
					'daily'  => __( 'Daily', 'rex-product-feed' ),
					'weekly' => __( 'Weekly', 'rex-product-feed' ),
				) );
				break;

			case 'wpfm_is_premium':
				$data = array( 'is_premium' => (bool) apply_filters( 'wpfm_is_premium', false ) );
				break;

			case 'wpfm_is_premium_activate':
				$data = array( 'is_premium_activate' => (bool) apply_filters( 'wpfm_is_premium_activate', false ) );
				break;
		}
	}

	/**
	 * Rows arrive JSON-encoded (one string per fc/ff/fr) rather than as
	 * bracket-notation form fields — this is a new endpoint with no legacy
	 * client to match, so JSON is simpler than replicating jQuery's nested
	 * form-serialization for arrays-of-objects. Sanitizes string values
	 * recursively, mirroring the intent of the legacy path's
	 * filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS).
	 */
	private function decode_rows( $json ): array {
		if ( ! is_string( $json ) || '' === $json ) {
			return array();
		}
		$decoded = json_decode( wp_unslash( $json ), true );
		if ( ! is_array( $decoded ) ) {
			return array();
		}
		return map_deep( $decoded, 'sanitize_text_field' );
	}
}
