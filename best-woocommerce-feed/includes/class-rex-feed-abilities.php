<?php
/**
 * Registers RexFeed operations with WordPress core's Abilities API (WP 6.9+)
 * so AI agents / MCP tooling can discover and call them.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Rex_Feed_Abilities.
 *
 * Thin adapters only — every execute_callback delegates to the existing
 * feed, scheduler, and validator classes; permission_callbacks reuse the
 * same capability check as the corresponding AJAX handlers.
 */
class Rex_Feed_Abilities {

	const CATEGORY = 'product-feed';

	/**
	 * Hook registration. No-op entirely on WP < 6.9 (no Abilities API).
	 *
	 * @since 7.14.0
	 */
	public static function init() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', array( __CLASS__, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( __CLASS__, 'register_abilities' ) );
	}

	/**
	 * Register the `product-feed` ability category.
	 *
	 * @since 7.14.0
	 */
	public static function register_category() {
		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'Product Feed', 'rex-product-feed' ),
				'description' => __( 'Abilities that list, inspect, validate, and regenerate WooCommerce product feeds.', 'rex-product-feed' ),
			)
		);
	}

	/**
	 * Register the feed abilities.
	 *
	 * @since 7.14.0
	 */
	public static function register_abilities() {
		wp_register_ability(
			'rexfeed/list-feeds',
			array(
				'label'               => __( 'List Product Feeds', 'rex-product-feed' ),
				'description'         => __( 'Returns the list of product feeds with their merchant, status, and last run time.', 'rex-product-feed' ),
				'category'            => self::CATEGORY,
				'output_schema'       => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'id'       => array( 'type' => 'integer' ),
							'name'     => array( 'type' => 'string' ),
							'merchant' => array( 'type' => 'string' ),
							'status'   => array( 'type' => 'string' ),
							'last_run' => array( 'type' => array( 'integer', 'null' ) ),
						),
					),
				),
				'execute_callback'    => array( __CLASS__, 'list_feeds' ),
				'permission_callback' => array( __CLASS__, 'can_manage_feeds' ),
				'meta'                => array(
					'annotations' => array( 'readonly' => true ),
				),
			)
		);

		wp_register_ability(
			'rexfeed/get-feed-status',
			array(
				'label'               => __( 'Get Feed Status', 'rex-product-feed' ),
				'description'         => __( 'Returns the generation status, batch progress, and file URL for a product feed.', 'rex-product-feed' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'feed_id' => array(
							'type'    => 'integer',
							'minimum' => 1,
						),
					),
					'required'   => array( 'feed_id' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'status'        => array( 'type' => 'string' ),
						'current_batch' => array( 'type' => 'integer' ),
						'total_batches' => array( 'type' => 'integer' ),
						'last_run'      => array( 'type' => array( 'integer', 'null' ) ),
						'product_count' => array( 'type' => array( 'integer', 'null' ) ),
						'file_url'      => array( 'type' => 'string' ),
					),
				),
				'execute_callback'    => array( __CLASS__, 'get_feed_status' ),
				'permission_callback' => array( __CLASS__, 'can_manage_feed' ),
				'meta'                => array(
					'annotations' => array( 'readonly' => true ),
				),
			)
		);

		wp_register_ability(
			'rexfeed/validate-feed',
			array(
				'label'               => __( 'Validate Product Feed', 'rex-product-feed' ),
				'description'         => __( 'Runs the merchant-specific validator for a product feed and returns its errors and warnings.', 'rex-product-feed' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'feed_id' => array(
							'type'    => 'integer',
							'minimum' => 1,
						),
					),
					'required'   => array( 'feed_id' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'merchant' => array( 'type' => 'string' ),
						'errors'   => array( 'type' => 'array' ),
					),
				),
				'execute_callback'    => array( __CLASS__, 'validate_feed' ),
				'permission_callback' => array( __CLASS__, 'can_manage_feed' ),
				'meta'                => array(
					'annotations' => array( 'readonly' => true ),
				),
			)
		);

		wp_register_ability(
			'rexfeed/regenerate-feed',
			array(
				'label'               => __( 'Regenerate Product Feed', 'rex-product-feed' ),
				'description'         => __( 'Triggers regeneration of a product feed, the same action as the "Generate Now" button.', 'rex-product-feed' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'feed_id' => array(
							'type'    => 'integer',
							'minimum' => 1,
						),
					),
					'required'   => array( 'feed_id' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'triggered'     => array( 'type' => 'boolean' ),
						'total_batches' => array( 'type' => 'integer' ),
					),
				),
				'execute_callback'    => array( __CLASS__, 'regenerate_feed' ),
				'permission_callback' => array( __CLASS__, 'can_manage_feed' ),
				'meta'                => array(
					'annotations' => array(
						'destructive' => true,
						'idempotent'  => false,
					),
				),
			)
		);

		wp_register_ability(
			'rexfeed/create-feed',
			array(
				'label'               => __( 'Create Product Feed', 'rex-product-feed' ),
				'description'         => __( 'Creates a new product feed for a merchant, using that merchant\'s default attribute template. Pro-only merchants require an active Pro license.', 'rex-product-feed' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'feed_name'        => array(
							'type'      => 'string',
							'minLength' => 1,
						),
						'merchant'         => array(
							'type'      => 'string',
							'minLength' => 1,
						),
						'feed_format'      => array(
							'type'    => 'string',
							'default' => 'xml',
						),
						'update_frequency' => array(
							'type'    => 'string',
							'default' => 'hourly',
						),
						'exclude_out_of_stock' => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'exclude_simple_products' => array(
							'type'    => 'boolean',
							'default' => false,
						),
					),
					'required'   => array( 'feed_name', 'merchant' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'feed_id'  => array( 'type' => 'integer' ),
						'edit_url' => array( 'type' => 'string' ),
					),
				),
				'execute_callback'    => array( __CLASS__, 'create_feed' ),
				'permission_callback' => array( __CLASS__, 'can_create_feed' ),
				'meta'                => array(
					'annotations' => array(
						'destructive' => false,
						'idempotent'  => false,
					),
				),
			)
		);
	}

	/**
	 * Permission check shared with the feed-level AJAX handlers.
	 *
	 * @since 7.14.0
	 * @return bool|WP_Error
	 */
	public static function can_manage_feeds() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'wpfm_forbidden', __( 'You are not allowed to manage product feeds.', 'rex-product-feed' ) );
		}
		return true;
	}

	/**
	 * Permission check for a single feed id. Validates existence first so a
	 * bad id surfaces as a clear error rather than a generic denial.
	 *
	 * @since 7.14.0
	 * @param array $input Ability input, expects `feed_id`.
	 * @return bool|WP_Error
	 */
	public static function can_manage_feed( $input ) {
		$feed_id = ! empty( $input['feed_id'] ) ? (int) $input['feed_id'] : 0;
		if ( ! $feed_id || 'product-feed' !== get_post_type( $feed_id ) ) {
			return new WP_Error( 'wpfm_invalid_feed', __( 'Invalid feed ID.', 'rex-product-feed' ) );
		}
		return self::can_manage_feeds();
	}

	/**
	 * Permission check for creating a feed: base capability, plus a Pro
	 * license if the requested merchant is Pro-only.
	 *
	 * @since 7.14.0
	 * @param array $input Ability input, expects `merchant`.
	 * @return bool|WP_Error
	 */
	public static function can_create_feed( $input ) {
		$allowed = self::can_manage_feeds();
		if ( is_wp_error( $allowed ) ) {
			return $allowed;
		}

		$merchant = ! empty( $input['merchant'] ) ? sanitize_text_field( $input['merchant'] ) : '';
		if ( self::is_pro_merchant( $merchant ) && ! apply_filters( 'wpfm_is_premium', false ) ) {
			do_action( 'rex_product_feed_advanced_feature_used', 0, array( 'feature' => 'Ability: create-feed pro-gate hit (' . $merchant . ')' ) );
			return new WP_Error(
				'wpfm_pro_required',
				/* translators: %s: merchant name/slug */
				sprintf( __( '"%s" is a Pro-only merchant. Upgrade to Pro to create this feed.', 'rex-product-feed' ), $merchant )
			);
		}

		return true;
	}

	/**
	 * Whether a merchant slug is in the Pro-only merchant group.
	 *
	 * @since 7.14.0
	 * @param string $merchant Merchant slug.
	 * @return bool
	 */
	private static function is_pro_merchant( $merchant ) {
		if ( ! $merchant || ! class_exists( 'Rex_Product_Feed_Merchants' ) ) {
			return false;
		}
		$merchants = Rex_Product_Feed_Merchants::get_merchants();
		return isset( $merchants['pro_merchants'][ $merchant ] );
	}

	/**
	 * `rexfeed/list-feeds` execute_callback.
	 *
	 * @since 7.14.0
	 * @return array
	 */
	public static function list_feeds() {
		$feeds = get_posts(
			array(
				'post_type'      => 'product-feed',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$result = array();
		foreach ( $feeds as $feed_id ) {
			$result[] = array(
				'id'       => $feed_id,
				'name'     => get_the_title( $feed_id ),
				'merchant' => (string) ( get_post_meta( $feed_id, '_rex_feed_merchant', true ) ?: get_post_meta( $feed_id, 'rex_feed_merchant', true ) ),
				'status'   => (string) ( get_post_meta( $feed_id, '_rex_feed_status', true ) ?: get_post_meta( $feed_id, 'rex_feed_status', true ) ),
				'last_run' => self::get_last_run( $feed_id ),
			);
		}

		return $result;
	}

	/**
	 * `rexfeed/get-feed-status` execute_callback.
	 *
	 * @since 7.14.0
	 * @param array $input Ability input, expects `feed_id`.
	 * @return array|WP_Error
	 */
	public static function get_feed_status( $input ) {
		$feed_id = (int) $input['feed_id'];

		if ( ! class_exists( 'Rex_Product_Feed_Ajax' ) ) {
			return new WP_Error( 'wpfm_unavailable', __( 'Feed status is unavailable.', 'rex-product-feed' ) );
		}

		$status = Rex_Product_Feed_Ajax::get_feed_generation_status( array( 'feed_id' => $feed_id ) );
		if ( isset( $status['status'] ) && 'error' === $status['status'] ) {
			return new WP_Error( 'wpfm_invalid_feed', $status['message'] ?? __( 'Invalid feed ID.', 'rex-product-feed' ) );
		}

		return array(
			'status'        => (string) ( $status['status'] ?? '' ),
			'current_batch' => (int) ( $status['current_batch'] ?? 0 ),
			'total_batches' => (int) ( $status['total_batches'] ?? 0 ),
			'last_run'      => self::get_last_run( $feed_id ),
			'product_count' => self::get_product_count( $feed_id ),
			'file_url'      => (string) ( $status['feed_url'] ?? '' ),
		);
	}

	/**
	 * `rexfeed/validate-feed` execute_callback.
	 *
	 * @since 7.14.0
	 * @param array $input Ability input, expects `feed_id`.
	 * @return array|WP_Error
	 */
	public static function validate_feed( $input ) {
		$feed_id = (int) $input['feed_id'];

		if ( ! class_exists( 'Rex_Feed_Validator_Loader' ) ) {
			return new WP_Error( 'wpfm_unavailable', __( 'Feed validation is unavailable.', 'rex-product-feed' ) );
		}

		$merchant = (string) ( get_post_meta( $feed_id, '_rex_feed_merchant', true ) ?: get_post_meta( $feed_id, 'rex_feed_merchant', true ) );
		$errors   = Rex_Feed_Validator_Loader::instance()->run_validation( $feed_id );

		if ( false === $errors ) {
			return new WP_Error( 'wpfm_validation_unavailable', __( 'Validation is disabled or unsupported for this feed.', 'rex-product-feed' ) );
		}

		do_action( 'rex_product_feed_advanced_feature_used', $feed_id, array( 'feature' => 'Ability: validate-feed' ) );

		return array(
			'merchant' => $merchant,
			'errors'   => is_array( $errors ) ? $errors : array(),
		);
	}

	/**
	 * `rexfeed/regenerate-feed` execute_callback.
	 *
	 * @since 7.14.0
	 * @param array $input Ability input, expects `feed_id`.
	 * @return array|WP_Error
	 */
	public static function regenerate_feed( $input ) {
		$feed_id = (int) $input['feed_id'];

		if ( ! class_exists( 'Rex_Product_Feed_Ajax' ) ) {
			return new WP_Error( 'wpfm_unavailable', __( 'Feed regeneration is unavailable.', 'rex-product-feed' ) );
		}

		$result = Rex_Product_Feed_Ajax::dispatch_feed_generation( array( 'feed_id' => $feed_id ) );

		do_action( 'rex_product_feed_advanced_feature_used', $feed_id, array( 'feature' => 'Ability: regenerate-feed' ) );

		return array(
			'triggered'     => (bool) ( $result['dispatched'] ?? false ),
			'total_batches' => (int) ( $result['total_batches'] ?? 0 ),
		);
	}

	/**
	 * `rexfeed/create-feed` execute_callback. Mirrors
	 * Rex_Product_Feed_Setup_Wizard_Ajax::create_feed() minus the $_POST/nonce
	 * handling (the Abilities API validates/authorizes input itself) and
	 * minus the wizard-only bookkeeping meta (`pfm_feed_created_by`,
	 * `_rex_feed_created_via_wizard`), since this feed isn't from the wizard.
	 *
	 * @since 7.14.0
	 * @param array $input Ability input: feed_name, merchant, feed_format, update_frequency.
	 * @return array|WP_Error
	 */
	public static function create_feed( $input ) {
		if ( ! class_exists( 'Rex_Feed_Template_Factory' ) ) {
			require_once plugin_dir_path( __FILE__ ) . '../admin/class-rex-feed-template-factory.php';
		}

		$feed_name        = sanitize_text_field( $input['feed_name'] );
		$merchant         = sanitize_text_field( $input['merchant'] );
		$feed_format      = ! empty( $input['feed_format'] ) ? sanitize_text_field( $input['feed_format'] ) : 'xml';
		$update_frequency = ! empty( $input['update_frequency'] ) ? sanitize_text_field( $input['update_frequency'] ) : 'hourly';

		$feed_id = wp_insert_post(
			array(
				'post_title'  => $feed_name,
				'post_type'   => 'product-feed',
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
			)
		);

		if ( is_wp_error( $feed_id ) ) {
			return $feed_id;
		}

		try {
			$template    = Rex_Feed_Template_Factory::build( $merchant, false );
			$feed_config = $template->get_template_mappings();
		} catch ( Exception $e ) {
			$feed_config = array();
		}

		update_post_meta( $feed_id, '_rex_feed_merchant', $merchant );
		update_post_meta( $feed_id, '_rex_feed_feed_format', $feed_format );
		update_post_meta( $feed_id, '_rex_feed_schedule', $update_frequency );
		update_post_meta( $feed_id, '_rex_feed_feed_config', $feed_config );
		update_post_meta( $feed_id, '_rex_feed_variations', 'yes' );
		update_post_meta( $feed_id, '_rex_feed_variable_product', 'yes' );
		update_post_meta( $feed_id, '_rex_feed_parent_product', 'yes' );
		$exclude_out_of_stock    = ! isset( $input['exclude_out_of_stock'] ) || (bool) $input['exclude_out_of_stock'];
		$exclude_simple_products = ! empty( $input['exclude_simple_products'] );

		// `_rex_feed_include_out_of_stock` is inverted: 'no' means out-of-stock products are excluded.
		update_post_meta( $feed_id, '_rex_feed_include_out_of_stock', $exclude_out_of_stock ? 'no' : 'yes' );
		update_post_meta( $feed_id, '_rex_feed_exclude_simple_products', $exclude_simple_products ? 'yes' : 'no' );
		update_post_meta( $feed_id, '_rex_feed_include_zero_price_products', 'no' );
		update_post_meta( $feed_id, '_rex_feed_hidden_products', 'no' );
		update_post_meta( $feed_id, '_rex_feed_skip_product', 'no' );
		update_post_meta( $feed_id, '_rex_feed_skip_row', 'no' );
		update_post_meta( $feed_id, '_rex_feed_products', 'all' );
		update_post_meta( $feed_id, '_feed_created_at', time() );
		update_post_meta( $feed_id, 'edit_count', 0 );
		update_post_meta( $feed_id, '_rex_feed_is_google_content_api', 'no' );
		update_post_meta( $feed_id, 'pfm_feed_created_by', 'abilities_api' );

		do_action( 'rex_product_feed_advanced_feature_used', $feed_id, array( 'feature' => 'Ability: create-feed (' . $merchant . ')' ) );

		return array(
			'feed_id'  => $feed_id,
			'edit_url' => admin_url( 'post.php?post=' . $feed_id . '&action=edit' ),
		);
	}

	/**
	 * Best-effort last-run timestamp, trying the meta keys the generation
	 * pipeline actually writes to, newest first.
	 *
	 * @since 7.14.0
	 * @param int $feed_id Feed post ID.
	 * @return int|null
	 */
	private static function get_last_run( $feed_id ) {
		foreach ( array( '_rex_feed_last_active_time', '_generation_start_time', '_rex_mas_last_sync' ) as $meta_key ) {
			$value = get_post_meta( $feed_id, $meta_key, true );
			if ( $value ) {
				return (int) $value;
			}
		}
		return null;
	}

	/**
	 * Best-effort generated product count for the feed's canonical output
	 * file. Returns null rather than duplicating the private file-counting
	 * logic in Rex_Feed_Product_Count_Guard when the path can't be resolved.
	 *
	 * @since 7.14.0
	 * @param int $feed_id Feed post ID.
	 * @return int|null
	 */
	private static function get_product_count( $feed_id ) {
		if ( ! class_exists( 'Rex_Feed_Product_Count_Guard' ) ) {
			return null;
		}

		$feed_url = get_post_meta( $feed_id, '_rex_feed_xml_file', true ) ?: get_post_meta( $feed_id, 'rex_feed_xml_file', true );
		if ( ! $feed_url ) {
			return null;
		}

		$upload_dir = wp_get_upload_dir();
		$path       = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $feed_url );
		if ( ! is_readable( $path ) ) {
			return null;
		}

		$format = (string) ( get_post_meta( $feed_id, '_rex_feed_feed_format', true ) ?: get_post_meta( $feed_id, 'rex_feed_feed_format', true ) );
		$count  = Rex_Feed_Product_Count_Guard::get_file_count( $path, $format );

		return is_int( $count ) ? $count : null;
	}
}
