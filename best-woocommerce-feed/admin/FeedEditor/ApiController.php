<?php

namespace RexTheme\FeedEditor;

use RexTheme\FeedEditor\Endpoints\FeedEditorEndpoint;

/**
 * Registers the feed-editor's own REST routes under the shared `wpfm/v1`
 * namespace, mirroring RexTheme\Dashboard\ApiController's pattern. Gated
 * behind the feature flag, same as the page itself — no routes exist at all
 * when the new editor is disabled.
 *
 * Read-only data loading only. The mapping SAVE action deliberately does
 * NOT live here — see AjaxController's doc comment for why a REST request
 * cannot be used for anything that needs the Pro plugin's admin hooks to
 * fire (`is_admin()` is false during `/wp-json/` requests, so Pro's
 * admin-only hook registration never runs for them).
 */
class ApiController {

	public function init(): void {
		if ( ! wpfm_is_feed_editor_v2_enabled() ) {
			return;
		}
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		$endpoints = array(
			new FeedEditorEndpoint(),
		);

		foreach ( $endpoints as $endpoint ) {
			$endpoint->register_routes();
		}
	}
}
