<?php
/**
 * PFM 2-Hour Feed Nudge
 *
 * Shows a dismissible notice when a user hasn't generated any product feed
 * within 2 hours of installing the plugin. Demo and wizard-created feeds are
 * excluded from the "has feed" check. Permanently dismissible per user.
 *
 * @since 7.4.82
 */
class PFM_Feed_Nudge {

	private $dismiss_meta_key = 'pfm_2hr_nudge_dismissed';

	public function __construct() {
		add_action( 'wp_ajax_pfm_dismiss_2hr_nudge', array( $this, 'handle_dismiss' ) );
		add_action( 'admin_notices', array( $this, 'display' ) );
	}

	private function should_show() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Already permanently dismissed by this user.
		if ( get_user_meta( get_current_user_id(), $this->dismiss_meta_key, true ) ) {
			return false;
		}

		// 2-hour window hasn't elapsed since install.
		$installed_time = (int) get_option( 'rex_wpfm_installed_time', 0 );
		if ( ! $installed_time || ( time() - $installed_time ) < 2 * HOUR_IN_SECONDS ) {
			return false;
		}

		// User already has at least one manually-created published feed.
		if ( $this->has_manual_feed() ) {
			return false;
		}

		// Don't stack with telemetry consent notice.
		if ( $this->is_telemetry_consent_pending() ) {
			return false;
		}

		return true;
	}

	private function has_manual_feed() {
		$args = array(
			'post_type'      => 'product-feed',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'pfm_feed_created_by',
					'compare' => 'NOT EXISTS',
				),
			),
		);
		return ! empty( get_posts( $args ) );
	}

	private function is_telemetry_consent_pending() {
		// Notice already dismissed — won't show.
		if ( 'yes' === get_option( 'linno_telemetry_notice_dismissed' ) ) {
			return false;
		}
		// Any tracking decision already persisted — notice won't show.
		$tracking_keys = array(
			'linno_telemetry_allow_tracking',
			'product-feed-manager_allow_tracking',
			'best-woocommerce-feed_allow_tracking',
		);
		foreach ( $tracking_keys as $key ) {
			if ( null !== get_option( $key, null ) ) {
				return false;
			}
		}
		return true;
	}

	private function is_pfm_admin_page() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}
		$pfm_pages = array(
			'product-feed',
			'edit-product-feed',
			'product-feed_page_wpfm-license',
			'product-feed_page_category_mapping',
			'product-feed_page_merchant_settings',
			'product-feed_page_wpfm_dashboard',
		);
		return in_array( $screen->id, $pfm_pages, true );
	}

	public function display() {
		if ( ! $this->should_show() || ! $this->is_pfm_admin_page() ) {
			return;
		}
		$create_url = admin_url( 'post-new.php?post_type=product-feed' );
		$nonce      = wp_create_nonce( 'pfm_dismiss_2hr_nudge' );
		?>
		<div class="notice notice-warning is-dismissible" id="pfm-2hr-nudge">
			<p>
				<strong><?php esc_html_e( 'Your product feed is waiting!', 'rex-product-feed' ); ?></strong>
				&nbsp;<?php
				printf(
					wp_kses(
						/* translators: %s: URL to create a new feed. */
						__( 'You haven\'t generated a product feed yet. <a href="%s">Create your first feed</a> and start listing products on Google Shopping, Facebook, and 200+ channels.', 'rex-product-feed' ),
						array( 'a' => array( 'href' => array() ) )
					),
					esc_url( $create_url )
				);
				?>
			</p>
		</div>
		<script>
		jQuery( document ).ready( function( $ ) {
			$( document ).on( 'click', '#pfm-2hr-nudge .notice-dismiss', function() {
				$.post( ajaxurl, {
					action: 'pfm_dismiss_2hr_nudge',
					nonce: <?php echo wp_json_encode( $nonce ); ?>
				} );
			} );
		} );
		</script>
		<?php
	}

	public function handle_dismiss() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'pfm_dismiss_2hr_nudge' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ), 403 );
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
			return;
		}
		update_user_meta( get_current_user_id(), $this->dismiss_meta_key, '1' );
		wp_send_json_success();
	}
}
