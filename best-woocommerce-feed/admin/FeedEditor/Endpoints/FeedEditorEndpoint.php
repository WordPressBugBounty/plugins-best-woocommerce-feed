<?php

namespace RexTheme\FeedEditor\Endpoints;

/**
 * Minimal read endpoint proving the new standalone page can load a feed's
 * current data without WordPress's native post.php data-loading. The
 * product-feed CPT has no `show_in_rest` support (checked
 * admin/class-rex-product-feed-cpt.php — not present), and this endpoint
 * intentionally doesn't turn that on: a dedicated route keeps the surface
 * scoped to what the feed editor needs, rather than exposing the whole CPT
 * collection via core's generic REST controller.
 *
 * This only returns the minimal post-level fields (id/title/status) needed
 * to prove the mechanism — the actual feed-config/settings data contract is
 * for the Configuration-tab change to define, not this foundation change.
 */
class FeedEditorEndpoint {

	const NAMESPACE = 'wpfm/v1';
	const POST_TYPE  = 'product-feed';

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/feed-editor/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'handle' ),
				'permission_callback' => array( $this, 'permission_callback' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $value ) {
							return is_numeric( $value );
						},
					),
				),
			)
		);
	}

	public function permission_callback( \WP_REST_Request $request ) {
		$id = absint( $request->get_param( 'id' ) );

		if ( 0 === $id ) {
			// "New feed" — no post exists yet, same capability post-new.php requires.
			return current_user_can( 'edit_posts' );
		}

		return current_user_can( 'edit_post', $id );
	}

	public function handle( \WP_REST_Request $request ): \WP_REST_Response {
		$id = absint( $request->get_param( 'id' ) );

		if ( 0 === $id ) {
			return new \WP_REST_Response(
				array(
					'id'     => 0,
					'title'  => '',
					'status' => 'auto-draft',
					'exists' => false,
				),
				200
			);
		}

		$post = get_post( $id );
		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			return new \WP_REST_Response( array( 'message' => __( 'Feed not found.', 'rex-product-feed' ) ), 404 );
		}

		return new \WP_REST_Response(
			array(
				'id'     => $post->ID,
				'title'  => $post->post_title,
				'status' => $post->post_status,
				'exists' => true,
			),
			200
		);
	}
}
