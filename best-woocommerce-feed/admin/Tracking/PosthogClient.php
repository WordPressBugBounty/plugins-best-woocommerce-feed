<?php
/**
 * PostHog Client for sending events.
 *
 * @package RexTheme\RexProductFeedManager\Tracking
 * @since   7.4.47
 */

namespace RexTheme\RexProductFeedManager\Tracking;
use PostHog\PostHog;
use Exception;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PosthogClient
 *
 * Responsible for sending event data to PostHog via HTTP POST.
 *
 * @package RexTheme\RexProductFeedManager\Tracking
 * @since   7.4.47
 */
class PosthogClient {

	/**
	 * PostHog Project API Key.
	 *
	 * @since 7.4.47
	 * @var   string
	 */
	private const POSTHOG_API_KEY = 'phc_EJBvXgFEfcjGlL7NwXFvXEPoUsczs9RFe6jXtB3QFto';

	/**
	 * PostHog project ID.
	 *
	 * @since 7.4.47
	 * @var string
	 */
	private $project_id = '80683';

	/**
	 * PostHog API endpoint for capturing events.
	 *
	 * @since 7.4.47
	 * @var   string
	 */
	private const POSTHOG_HOST = 'https://eu.posthog.com';

	/**
	 * Captures an event and sends it to PostHog.
	 * Only captures events on WooCommerce Feed related pages.
	 *
	 * @since 7.4.47
	 *
	 * @param string $event_name The name of the event.
	 * @param array  $properties An array of properties for the event.
	 * @param string|null $distinct_id The distinct ID for the user. If null, uses admin email.
	 *
	 * @return bool True if the event was sent successfully, false otherwise.
	 */
	public function capture( $event_name, $properties = [], $distinct_id = 0 ) {
		if ( empty( self::POSTHOG_API_KEY ) ) {
			error_log('PostHog API key is empty');
			return false;
		}

		// Check if the PostHog class exists using the correct namespace
		if ( !class_exists( 'PostHog\\PostHog' ) ) {
			error_log('PostHog class not found. Please check if the PostHog PHP library is properly installed.');
			return false;
		}

		if ( ! $distinct_id ) {
			$distinct_id = $this->get_distinct_id();
		}

		// Add session context and WooCommerce Feed specific properties
		$properties = array_merge( $properties, [
            'page_url' => isset($_SERVER['REQUEST_URI']) ? esc_url($_SERVER['REQUEST_URI']) : '',
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
			'plugin' => 'best-woocommerce-feed',
			'plugin_version' => defined('WPFM_VERSION') ? WPFM_VERSION : 'unknown',
			'wp_version' => get_bloginfo( 'version' ),
			'site_url' => get_site_url(),
            'page_type' => $this->get_woocommerce_feed_page_type(),
		]);

		try {
			PostHog::init(
				self::POSTHOG_API_KEY,
				[
					'host' => self::POSTHOG_HOST,
				]
			);
			$response = \PostHog\PostHog::capture([
				'distinctId' => $distinct_id,
				'event' 	 => $event_name,
				'properties' => $properties,
			]);
			return $response;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Initialize PostHog for backend admin tracking.
	 * Only tracks WooCommerce Feed admin pages.
	 *
	 * @since 7.4.47
	 *
	 * @param array $config Additional configuration options.
	 * @return void
	 */
	public function init_backend_tracking( array $config = [] ): void {
		// Only initialize in admin area
		if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// Don't initialize if PostHog API key is missing
		if ( empty( self::POSTHOG_API_KEY ) ) {
			return;
		}

		// Only track WooCommerce Feed admin pages
		if ( ! $this->is_woocommerce_feed_admin_page() ) {
			return;
		}

		$default_config = [
			'capture_pageview' => true,
			'capture_pageleave' => true,
			'session_recording' => [
				'recordCrossOriginIframes' => false,
				'maskAllInputs' => true,
				'maskAllText' => false,
			],
			'autocapture' => true,
			'disable_session_recording' => false,
		];

		$config = array_merge( $default_config, $config );

		// Add PostHog script to admin with higher priority
		add_action( 'admin_head', function() use ( $config ) {
			$this->render_posthog_script( $config );
		}, 5 );

		// Identify user and set session context in admin
		add_action( 'admin_footer', function() {
			$this->identify_user();
			$this->set_admin_context();
		}, 10 );
	}

	/**
	 * Render PostHog JavaScript tracking script.
	 *
	 * @since 7.4.47
	 *
	 * @param array $config Configuration options.
	 * @return void
	 */
	public function render_posthog_script( array $config ): void {
		$config_json = wp_json_encode( $config );
		$api_key = esc_js( self::POSTHOG_API_KEY );
		$api_host = esc_js( self::POSTHOG_HOST );
		?>
		<script>
		!function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.crossOrigin="anonymous",p.async=!0,p.src=s.api_host.replace(".posthog.com","-assets.posthog.com")+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="init Re Cs Fs Pe Rs Ms capture Ve calculateEventProperties Ds register register_once register_for_session unregister unregister_for_session zs getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSurveysLoaded onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey canRenderSurveyAsync identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property getSessionProperty js As createPersonProfile Ns Is Us opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing clear_opt_in_out_capturing Os debug I Ls getPageViewId captureTraceFeedback captureTraceMetric".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
		posthog.init('<?php echo $api_key; ?>', {
			api_host: '<?php echo $api_host; ?>',
			<?php echo substr( $config_json, 1, -1 ); ?>, // Remove outer braces and spread config
			person_profiles: 'identified_only'
		});
		</script>
		<?php
	}

	/**
	 * Identify the current user to PostHog.
	 *
	 * @since 7.4.47
	 *
	 * @return void
	 */
	private function identify_user(): void {
		$distinct_id = $this->get_distinct_id();
		$user_properties = [];

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_properties = array_merge( $user_properties, [
				'email' => $current_user->user_email,
				'name' => $current_user->display_name,
				'role' => implode( ', ', $current_user->roles ),
			]);
		} else {
			$user_properties['anonymous'] = true;
		}

		?>
		<script>
		if (typeof posthog !== 'undefined') {
			posthog.identify('<?php echo esc_js( $distinct_id ); ?>', <?php echo wp_json_encode( $user_properties ); ?>);
		}
		</script>
		<?php
	}


	/**
	 * Set admin context for PostHog.
	 *
	 * @since 7.4.47
	 *
	 * @return void
	 */
	private function set_admin_context(): void {
		$admin_properties = [
            'admin_page' => isset($_SERVER['REQUEST_URI']) ? esc_url($_SERVER['REQUEST_URI']) : '',
			'is_admin' => true,
			'current_screen' => get_current_screen() ? get_current_screen()->id : '',
			'timestamp' => time(),
			'plugin' => 'best-woocommerce-feed',
			'plugin_version' => defined('WPFM_VERSION') ? WPFM_VERSION : 'unknown',
		];

		// Add WooCommerce context if available
		if ( class_exists( 'WooCommerce' ) ) {
			$admin_properties['woocommerce_version'] = WC()->version;
			$admin_properties['total_products'] = wp_count_posts( 'product' )->publish;
		}

		?>
		<script>
		if (typeof posthog !== 'undefined') {
			posthog.register(<?php echo wp_json_encode( $admin_properties ); ?>);
		}
		</script>
		<?php
	}

	/**
	 * Get the type of WooCommerce Feed page.
	 *
	 * @since 7.4.47
	 *
	 * @return string Page type.
	 */
	private function get_woocommerce_feed_page_type(): string {
		if ( $this->is_feed_list_page() ) {
			return 'feed_list';
		}

		if ( $this->is_feed_edit_page() ) {
			return 'feed_edit';
		}

		if ( $this->is_feed_settings_page() ) {
			return 'feed_settings';
		}

		if ( $this->is_feed_category_mapping_page() ) {
			return 'category_mapping';
		}

		if ( $this->is_feed_attribute_mapping_page() ) {
			return 'attribute_mapping';
		}

		return 'other';
	}

	/**
	 * Get distinct ID for PostHog tracking.
	 *
	 * @since 7.4.47
	 *
	 * @return string Distinct ID for the current user/session.
	 */
	private function get_distinct_id(): string {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			// Use email as distinct ID for logged-in users
			return $current_user->user_email;
		}

		// For anonymous users, use session ID as distinct ID
		return 'anonymous_' . uniqid('', true);
	}

	/**
	 * Check if current page is a WooCommerce Feed frontend page.
	 *
	 * @since 7.4.47
	 *
	 * @return bool True if on WooCommerce Feed frontend page.
	 */
	private function is_woocommerce_feed_frontend_page(): bool {
		// Check for WooCommerce Feed shortcodes in content
		global $post;

		if ( ! $post ) {
			return false;
		}

		// Check for specific shortcodes related to product feeds
		$woo_feed_shortcodes = [
			'product_feed',
		];

		foreach ( $woo_feed_shortcodes as $shortcode ) {
			if ( has_shortcode( $post->post_content, $shortcode ) ) {
				return true;
			}
		}

		// Check if page contains feed-related content
		if (strpos( $post->post_content, 'product-feed' ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current page is a WooCommerce Feed admin page.
	 *
	 * @since 7.4.47
	 *
	 * @return bool True if on WooCommerce Feed admin page.
	 */
	private function is_woocommerce_feed_admin_page(): bool {
		// Check if we're on a WooCommerce Feed admin page
		if ( ! is_admin() ) {
			return false;
		}

		// Check for WooCommerce Feed admin pages
		if ( isset( $_GET['page'] ) ) {
			$page = sanitize_text_field( $_GET['page'] );

			// Main WooCommerce Feed admin pages
			if ( strpos( $page, 'product-feed' ) === 0 ||
				 strpos( $page, 'wpfm' ) === 0 ) {
				return true;
			}
		}

		// Check for WooCommerce Feed post types in edit screens
		global $pagenow, $post_type;

		if ( in_array( $pagenow, [ 'post.php', 'post-new.php', 'edit.php' ] ) ) {
			$woo_feed_post_types = [
				'product-feed',
			];

			if ( in_array( $post_type, $woo_feed_post_types ) ) {
				return true;
			}

			// Check current post type if not set in query
			if ( ! $post_type && isset( $_GET['post'] ) ) {
				$post_id = intval( $_GET['post'] );
				$current_post_type = get_post_type( $post_id );
				if ( in_array( $current_post_type, $woo_feed_post_types ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if current page is feed list page.
	 *
	 * @since 7.4.47
	 *
	 * @return bool True if on feed list page.
	 */
	private function is_feed_list_page(): bool {
		return isset( $_GET['page'] ) && $_GET['page'] === 'product-feed';
	}

	/**
	 * Check if current page is feed edit page.
	 *
	 * @since 7.4.47
	 *
	 * @return bool True if on feed edit page.
	 */
	private function is_feed_edit_page(): bool {
		global $pagenow, $post_type;

		return ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) &&
			   $post_type === 'product-feed';
	}

	/**
	 * Check if current page is feed settings page.
	 *
	 * @since 7.4.47
	 *
	 * @return bool True if on feed settings page.
	 */
	private function is_feed_settings_page(): bool {
		return isset( $_GET['page'] ) && $_GET['page'] === 'woo-feed-config';
	}

	/**
	 * Check if current page is category mapping page.
	 *
	 * @since 7.4.47
	 *
	 * @return bool True if on category mapping page.
	 */
	private function is_feed_category_mapping_page(): bool {
		return isset( $_GET['page'] ) && $_GET['page'] === 'woo-feed-category-mapping';
	}

	/**
	 * Check if current page is attribute mapping page.
	 *
	 * @since 7.4.47
	 *
	 * @return bool True if on attribute mapping page.
	 */
	private function is_feed_attribute_mapping_page(): bool {
		return isset( $_GET['page'] ) && $_GET['page'] === 'woo-feed-attribute-mapping';
	}

}