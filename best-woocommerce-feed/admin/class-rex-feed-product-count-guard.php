<?php
/**
 * Protects the last valid scheduled feed and reports automatic generation errors.
 *
 * @package Rex_Product_Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinates product-count comparison, feed restoration, admin alerts, and email alerts.
 */
class Rex_Feed_Product_Count_Guard {

	const AUTOMATIC_RUN_GUARD_ENABLED = false;
	const RUN_META             = '_rex_feed_product_count_run';
	const ERROR_META           = '_rex_feed_error_state';
	const ERROR_IDS_OPTION     = 'wpfm_active_feed_error_ids';
	const ADMIN_NOTICE_OPTION  = 'wpfm_feed_error_admin_notice_enabled';
	const EMAIL_ENABLED_OPTION = 'wpfm_feed_error_email_enabled';
	const EMAIL_BATCH_OPTION   = 'wpfm_feed_error_email_batches';
	const EMAIL_LOCK_OPTION    = 'wpfm_feed_error_email_lock';
	const EMAIL_HOOK           = 'wpfm_send_feed_error_email_batch';
	const EMAIL_SUBJECT        = '[{{store_name}}] Product feed error detected ({{error_count}})';

	/**
	 * Records the run source and snapshots the current valid feed before generation begins.
	 *
	 * Automatic runs need the snapshot to restore the canonical file and count metadata
	 * when the generated count decreases. Manual runs need only the source marker so their
	 * intentionally lower output can be accepted.
	 *
	 * @param int    $feed_id    Product-feed post ID used to locate feed files and metadata.
	 * @param string $source     Run source used to apply protection only to automatic runs.
	 * @param int    $started_at Unix timestamp used as the run and email-batch anchor.
	 *
	 * @return void
	 */
	public static function begin_run( $feed_id, $source, $started_at ) {
		$feed_id = absint( $feed_id );
		$source  = 'automatic' === $source ? 'automatic' : 'manual';

		if ( 'automatic' === $source && ! self::AUTOMATIC_RUN_GUARD_ENABLED ) {
			return;
		}

		if ( ! $feed_id || 'product-feed' !== get_post_type( $feed_id ) ) {
			return;
		}

		$existing_run = get_post_meta( $feed_id, self::RUN_META, true );
		if ( is_array( $existing_run ) && ! empty( $existing_run['source'] ) ) {
			return;
		}

		$started_at = absint( $started_at );
		$run        = array(
			'source'     => $source,
			'started_at' => $started_at ? $started_at : time(),
		);

		if ( 'automatic' === $source ) {
			$canonical            = self::get_canonical_file( $feed_id );
			$valid_totals         = get_post_meta( $feed_id, '_rex_feed_total_products', true );
			$valid_all_feed_count = get_post_meta( $feed_id, '_rex_feed_total_products_for_all_feed', true );
			$valid_count          = self::get_file_count( $canonical['path'], $canonical['format'] );
			if ( null === $valid_count ) {
				$valid_count = self::get_feed_count( $feed_id, $canonical['path'], $canonical['format'] );
			}
			$run['canonical_url']               = $canonical['url'];
			$run['canonical_path']              = $canonical['path'];
			$run['backup_path']                 = '';
			$run['format']                      = $canonical['format'];
			$run['valid_count']                 = $valid_count;
			$run['valid_totals']                = $valid_totals;
			$run['valid_totals_exists']         = metadata_exists( 'post', $feed_id, '_rex_feed_total_products' );
			$run['valid_all_feed_count']        = $valid_all_feed_count;
			$run['valid_all_feed_count_exists'] = metadata_exists( 'post', $feed_id, '_rex_feed_total_products_for_all_feed' );

			if ( $canonical['path'] && is_readable( $canonical['path'] ) ) {
				$backup_path = trailingslashit( dirname( $canonical['path'] ) ) . sprintf(
					'.wpfm-valid-%1$d-%2$d.%3$s',
					$feed_id,
					$run['started_at'],
					$canonical['format']
				);

				if ( copy( $canonical['path'], $backup_path ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
					$run['backup_path'] = $backup_path;
				}
			}

			if ( empty( $run['backup_path'] ) ) {
				$run['valid_count'] = null;
			}
		}

		update_post_meta( $feed_id, self::RUN_META, $run );
	}

	/**
	 * Evaluates the completed run and accepts manual/non-decreasing output or rejects a decrease.
	 *
	 * @param int $feed_id Product-feed post ID whose final generated output is ready to inspect.
	 *
	 * @return void
	 */
	public static function complete_run( $feed_id ) {
		$feed_id = absint( $feed_id );
		$run     = get_post_meta( $feed_id, self::RUN_META, true );

		if ( ! is_array( $run ) || empty( $run['source'] ) ) {
			return;
		}

		if ( 'manual' === $run['source'] ) {
			self::accept_manual_run( $feed_id );
			return;
		}

		$temp_xml_url = get_post_meta( $feed_id, '_rex_feed_temp_xml_file', true );
		$temp_xml_url = $temp_xml_url ? $temp_xml_url : get_post_meta( $feed_id, 'rex_feed_temp_xml_file', true );
		if ( $temp_xml_url ) {
			self::fail_run( $feed_id, 'invalid_xml', __( 'The generated XML file did not pass validation.', 'rex-product-feed' ) );
			return;
		}

		$canonical       = self::get_canonical_file( $feed_id );
		$generated_count = self::get_file_count( $canonical['path'], $canonical['format'] );
		if ( null === $generated_count ) {
			$generated_count = self::get_feed_count( $feed_id, $canonical['path'], $canonical['format'] );
		}

		if ( ! $canonical['path'] || ! is_readable( $canonical['path'] ) || null === $generated_count ) {
			self::fail_run( $feed_id, 'generated_file_missing', __( 'The automatic run did not produce a readable feed file.', 'rex-product-feed' ) );
			return;
		}

		$valid_count = array_key_exists( 'valid_count', $run ) ? $run['valid_count'] : null;
		if ( null === $valid_count || $generated_count >= (int) $valid_count ) {
			self::accept_run( $feed_id, $run );
			return;
		}

		self::reject_run( $feed_id, $run, $generated_count );
	}

	/**
	 * Restores an automatic run snapshot after generation fails before comparison completes.
	 *
	 * @param int    $feed_id   Product-feed post ID whose automatic run failed.
	 * @param string $error_type Stable error identifier used by admin and email renderers.
	 * @param string $message   Human-readable failure detail retained for the feed edit screen.
	 *
	 * @return void
	 */
	public static function fail_run( $feed_id, $error_type, $message = '' ) {
		$feed_id = absint( $feed_id );
		$run     = get_post_meta( $feed_id, self::RUN_META, true );

		if ( ! is_array( $run ) || 'automatic' !== ( $run['source'] ?? '' ) ) {
			delete_post_meta( $feed_id, self::RUN_META );
			return;
		}

		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( '', array(), "wpfm-feed-{$feed_id}" );
		}

		$restore_result = array(
			'restored'   => false,
			'error_url'  => '',
			'error_path' => '',
		);

		if ( 'invalid_xml' === $error_type ) {
			$error_url                    = get_post_meta( $feed_id, '_rex_feed_temp_xml_file', true );
			$error_url                    = $error_url ? $error_url : get_post_meta( $feed_id, 'rex_feed_temp_xml_file', true );
			$restore_result['error_url']  = $error_url;
			$restore_result['error_path'] = self::resolve_feed_url_to_path( $error_url );
			$restore_result['restored']   = ! empty( $run['canonical_path'] ) && is_readable( $run['canonical_path'] );

			if ( $restore_result['restored'] ) {
				if ( ! empty( $run['backup_path'] ) ) {
					self::delete_owned_file( $run['backup_path'] );
				}
				self::restore_count_metadata( $feed_id, $run );
			} else {
				$restore_result = self::restore_snapshot( $feed_id, $run, false );
			}
		} else {
			$restore_result = self::restore_snapshot( $feed_id, $run, 'generation_exception' === $error_type );
		}

		if ( ! $restore_result['restored'] ) {
			self::log_restore_failure( $feed_id, $run );
			$error_type = 'restore_failed';
			$message    = __( 'The automatic feed failed and the last valid feed could not be restored automatically.', 'rex-product-feed' );
		}

		$error = array(
			'status'               => 'active',
			'type'                 => sanitize_key( $error_type ),
			'message'              => sanitize_text_field( $message ),
			'last_valid_count'     => $run['valid_count'] ?? null,
			'generated_count'      => null,
			'missing_count'        => null,
			'scheduled_started_at' => absint( $run['started_at'] ?? time() ),
			'detected_at'          => time(),
			'canonical_feed_url'   => $run['canonical_url'] ?? '',
			'error_feed_url'       => $restore_result['error_url'],
			'error_feed_path'      => $restore_result['error_path'],
			'valid_backup_path'    => $restore_result['restored'] ? '' : ( $run['backup_path'] ?? '' ),
			'restore_succeeded'    => $restore_result['restored'],
		);

		self::record_error( $feed_id, $error );
		delete_post_meta( $feed_id, self::RUN_META );
	}

	/**
	 * Accepts a successful manual generation as the user's intended current feed.
	 *
	 * @param int $feed_id Product-feed post ID whose manually generated output is accepted.
	 *
	 * @return void
	 */
	public static function accept_manual_run( $feed_id ) {
		$feed_id  = absint( $feed_id );
		$run      = get_post_meta( $feed_id, self::RUN_META, true );
		$temp_url = get_post_meta( $feed_id, '_rex_feed_temp_xml_file', true );
		$temp_url = $temp_url ? $temp_url : get_post_meta( $feed_id, 'rex_feed_temp_xml_file', true );

		if ( is_array( $run ) && ! empty( $run['backup_path'] ) ) {
			self::delete_owned_file( $run['backup_path'] );
		}

		delete_post_meta( $feed_id, self::RUN_META );
		if ( $temp_url ) {
			return;
		}
		self::clear_error( $feed_id );
	}

	/**
	 * Shows a generalized feed-error notice on WordPress admin pages.
	 *
	 * @return void
	 */
	public function render_global_notice() {
		if ( 'no' === get_option( self::ADMIN_NOTICE_OPTION, 'yes' ) || ! current_user_can( 'manage_woocommerce' ) || empty( self::get_active_error_ids() ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			return;
		}
		?>
		<div class="notice notice-error is-dismissible rex-feed-notice">
			<p>
				<?php esc_html_e( 'One or more product feeds have errors.', 'rex-product-feed' ); ?>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product-feed' ) ); ?>">
					<?php esc_html_e( 'Review product feeds', 'rex-product-feed' ); ?>
				</a>
				<?php esc_html_e( 'to investigate.', 'rex-product-feed' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Adds an error class to affected feed rows without changing existing list columns.
	 *
	 * @param string[] $classes Existing post classes that WordPress will print on the row.
	 * @param string[] $requested_classes Additional requested classes retained for filter compatibility.
	 * @param int      $post_id Post ID used to look up the feed's active error state.
	 *
	 * @return string[] Filtered post classes including the feed-error class when required.
	 */
	public function add_feed_row_class( $classes, $requested_classes, $post_id ) {
		if ( is_admin() && 'product-feed' === get_post_type( $post_id ) ) {
			$error = get_post_meta( $post_id, self::ERROR_META, true );
			if ( is_array( $error ) && 'active' === ( $error['status'] ?? '' ) ) {
				$classes[] = 'rex-feed-row-has-error';
			}
		}

		return $classes;
	}

	/**
	 * Shows count and file details for the active error on an individual feed edit screen.
	 *
	 * @return void
	 */
	public function render_feed_detail_notice() {
		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base || 'product-feed' !== $screen->post_type ) {
			return;
		}

		$feed_id = get_the_ID();
		if ( ! $feed_id || 'product-feed' !== get_post_type( $feed_id ) || ! current_user_can( 'edit_post', $feed_id ) ) {
			return;
		}

		$error = get_post_meta( $feed_id, self::ERROR_META, true );
		if ( ! is_array( $error ) || 'active' !== ( $error['status'] ?? '' ) ) {
			return;
		}

		$merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );
		$merchant = $merchant ? $merchant : get_post_meta( $feed_id, 'rex_feed_merchant', true );
		$merchant = ucwords( str_replace( array( '_', '-' ), ' ', $merchant ) );
		$run_time = wp_date(
			get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
			absint( $error['scheduled_started_at'] ?? $error['detected_at'] ?? time() ),
			wp_timezone()
		);
		?>
		<div class="notice notice-error rex-feed-notice">
			<p><strong><?php esc_html_e( 'Automatic feed generation error', 'rex-product-feed' ); ?></strong></p>
			<p>
				<?php
				if ( 'product_count_decreased' === ( $error['type'] ?? '' ) ) {
					echo esc_html(
						sprintf(
							/* translators: 1: generated item count, 2: last valid item count. */
							__( 'This scheduled run generated %1$d items; the last valid feed contained %2$d. The lower feed was not kept as the public feed.', 'rex-product-feed' ),
							(int) $error['generated_count'],
							(int) $error['last_valid_count']
						)
					);
				} else {
					echo esc_html( $error['message'] ?? __( 'The automatic feed run did not complete successfully.', 'rex-product-feed' ) );
				}
				?>
			</p>
			<?php if ( ! empty( $error['restore_succeeded'] ) && ! empty( $error['canonical_feed_url'] ) ) : ?>
				<p><?php esc_html_e( 'The previous valid feed remains available from Your Feed URL.', 'rex-product-feed' ); ?></p>
			<?php endif; ?>
			<ul>
				<li><strong><?php esc_html_e( 'Feed:', 'rex-product-feed' ); ?></strong> <?php echo esc_html( get_the_title( $feed_id ) ); ?></li>
				<li><strong><?php esc_html_e( 'Merchant:', 'rex-product-feed' ); ?></strong> <?php echo esc_html( $merchant ); ?></li>
				<li><strong><?php esc_html_e( 'Scheduled run:', 'rex-product-feed' ); ?></strong> <?php echo esc_html( $run_time ); ?></li>
				<?php if ( null !== ( $error['last_valid_count'] ?? null ) ) : ?>
					<li><strong><?php esc_html_e( 'Last valid feed items:', 'rex-product-feed' ); ?></strong> <?php echo esc_html( number_format_i18n( (int) $error['last_valid_count'] ) ); ?></li>
				<?php endif; ?>
				<?php if ( null !== ( $error['generated_count'] ?? null ) ) : ?>
					<li><strong><?php esc_html_e( 'Generated feed items:', 'rex-product-feed' ); ?></strong> <?php echo esc_html( number_format_i18n( (int) $error['generated_count'] ) ); ?></li>
				<?php endif; ?>
				<?php if ( null !== ( $error['missing_count'] ?? null ) ) : ?>
					<li><strong><?php esc_html_e( 'Missing feed items:', 'rex-product-feed' ); ?></strong> <?php echo esc_html( number_format_i18n( (int) $error['missing_count'] ) ); ?></li>
				<?php endif; ?>
			</ul>
			<p>
				<?php if ( ! empty( $error['canonical_feed_url'] ) ) : ?>
					<a href="<?php echo esc_url( $error['canonical_feed_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open valid feed', 'rex-product-feed' ); ?></a>
				<?php endif; ?>
				<?php if ( ! empty( $error['error_feed_url'] ) ) : ?>
					&nbsp;|&nbsp;
					<a href="<?php echo esc_url( $error['error_feed_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open comparison feed', 'rex-product-feed' ); ?></a>
				<?php endif; ?>
			</p>
			<?php if ( 'product_count_decreased' === ( $error['type'] ?? '' ) && ! empty( $error['restore_succeeded'] ) && ! empty( $error['error_feed_path'] ) ) : ?>
				<form class="rex-feed-error-actions" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="wpfm_resolve_feed_error">
					<input type="hidden" name="feed_id" value="<?php echo esc_attr( $feed_id ); ?>">
					<?php wp_nonce_field( 'wpfm_resolve_feed_error_' . $feed_id ); ?>
					<button type="submit" name="decision" value="accept_new" class="button button-primary"><?php esc_html_e( 'Accept newly generated feed', 'rex-product-feed' ); ?></button>
					<button type="submit" name="decision" value="keep_previous" class="button"><?php esc_html_e( 'Keep previous feed', 'rex-product-feed' ); ?></button>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Resolves a count-drop error by accepting its comparison feed or retaining the valid feed.
	 *
	 * The submitted feed ID identifies the protected feed, while the decision selects whether
	 * the comparison file replaces the public feed or is deleted as rejected output.
	 *
	 * @return void Redirects to the feed edit page after the selected files and metadata are updated.
	 */
	public function resolve_feed_error() {
		$data     = function_exists( 'rex_feed_get_sanitized_get_post' ) ? rex_feed_get_sanitized_get_post() : array();
		$data     = isset( $data['post'] ) && is_array( $data['post'] ) ? $data['post'] : array();
		$feed_id  = absint( $data['feed_id'] ?? 0 );
		$decision = sanitize_key( $data['decision'] ?? '' );

		if ( ! $feed_id || 'product-feed' !== get_post_type( $feed_id ) || ! current_user_can( 'edit_post', $feed_id ) ) {
			wp_die( esc_html__( 'You cannot resolve this feed error.', 'rex-product-feed' ) );
		}

		check_admin_referer( 'wpfm_resolve_feed_error_' . $feed_id );

		$error = get_post_meta( $feed_id, self::ERROR_META, true );
		if ( ! is_array( $error ) || 'active' !== ( $error['status'] ?? '' ) || 'product_count_decreased' !== ( $error['type'] ?? '' ) ) {
			wp_die( esc_html__( 'This feed error is no longer available.', 'rex-product-feed' ) );
		}

		if ( 'keep_previous' === $decision ) {
			self::clear_error( $feed_id );
			wp_safe_redirect( get_edit_post_link( $feed_id, '' ) );
			exit;
		}

		if ( 'accept_new' !== $decision ) {
			wp_die( esc_html__( 'Invalid feed error action.', 'rex-product-feed' ) );
		}

		$canonical      = self::get_canonical_file( $feed_id );
		$canonical_path = wp_normalize_path( $canonical['path'] ?? '' );
		$error_path     = wp_normalize_path( $error['error_feed_path'] ?? '' );
		$error_prefix   = 'error-feed-' . $feed_id . '-';

		if ( ! $canonical_path || ! is_readable( $error_path ) || dirname( $canonical_path ) !== dirname( $error_path ) || 0 !== strpos( wp_basename( $error_path ), $error_prefix ) ) {
			wp_die( esc_html__( 'The newly generated comparison feed is unavailable.', 'rex-product-feed' ) );
		}

		$backup_path = '';
		if ( file_exists( $canonical_path ) ) {
			$backup_path = trailingslashit( dirname( $canonical_path ) ) . sprintf(
				'.wpfm-valid-%1$d-%2$d.%3$s',
				$feed_id,
				time(),
				$canonical['format']
			);

			if ( ! rename( $canonical_path, $backup_path ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
				wp_die( esc_html__( 'The previous valid feed could not be prepared for replacement.', 'rex-product-feed' ) );
			}
		}

		if ( ! rename( $error_path, $canonical_path ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( $backup_path ) {
				rename( $backup_path, $canonical_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			}
			wp_die( esc_html__( 'The newly generated feed could not replace the previous feed.', 'rex-product-feed' ) );
		}

		if ( $backup_path ) {
			self::delete_owned_file( $backup_path );
		}

		$generated_count           = absint( $error['generated_count'] ?? 0 );
		$generated_totals          = $error['generated_totals'] ?? get_post_meta( $feed_id, '_rex_feed_total_products', true );
		$generated_totals          = is_array( $generated_totals ) ? $generated_totals : array();
		$generated_totals['total'] = $generated_count;
		update_post_meta( $feed_id, '_rex_feed_total_products', $generated_totals );
		update_post_meta( $feed_id, '_rex_feed_total_products_for_all_feed', $generated_count );
		self::clear_error( $feed_id );

		wp_safe_redirect( get_edit_post_link( $feed_id, '' ) );
		exit;
	}

	/**
	 * Saves whether the generalized feed-error notice appears in WordPress admin.
	 *
	 * @param array $payload Submitted enabled state used to update the admin-notice option immediately.
	 *
	 * @return void Sends the AJAX response after the option is persisted.
	 */
	public static function save_admin_notice_setting( $payload ) {
		$payload = is_array( $payload ) ? $payload : array();
		$enabled = isset( $payload['enabled'] ) && 'yes' === sanitize_key( $payload['enabled'] ) ? 'yes' : 'no';

		update_option( self::ADMIN_NOTICE_OPTION, $enabled );
		wp_send_json_success();
	}

	/**
	 * Validates and saves the email notification toggle and recipient address.
	 *
	 * @param array $payload Submitted toggle and recipient address used for feed-error emails.
	 *
	 * @return void Sends the AJAX response after validation and persistence completes.
	 */
	public static function save_email_settings( $payload ) {
		$payload       = is_array( $payload ) ? $payload : array();
		$is_premium    = apply_filters( 'wpfm_is_premium', false );
		$enabled       = $is_premium && isset( $payload['enabled'] ) && 'yes' === sanitize_key( $payload['enabled'] ) ? 'yes' : 'no';
		$raw_recipient = isset( $payload['recipient'] ) ? trim( wp_unslash( $payload['recipient'] ) ) : '';
		$recipient     = sanitize_email( $raw_recipient );

		if ( 'yes' === $enabled && ( ! $recipient || ! is_email( $raw_recipient ) ) ) {
			wp_send_json_error(
				array(
					'field'   => 'recipient',
					'message' => __( 'Enter a valid recipient email address.', 'rex-product-feed' ),
				)
			);
		}

		if ( $is_premium ) {
			update_option( self::EMAIL_ENABLED_OPTION, $enabled );
			update_option( 'wpfm_user_email', $recipient );
		}

		if ( 'no' === $enabled ) {
			self::clear_email_batches();
		}

		wp_send_json_success( array( 'message' => __( 'Feed error email settings saved.', 'rex-product-feed' ) ) );
	}

	/**
	 * Adds a detected feed error to fixed ten-minute email groups.
	 *
	 * @param int   $feed_id Product-feed post ID used to identify and render the queued error.
	 * @param array $error   Error state containing the scheduled-run timestamp used for batching.
	 *
	 * @return void
	 */
	private static function queue_email_error( $feed_id, $error ) {
		if ( 'yes' !== get_option( self::EMAIL_ENABLED_OPTION, 'no' ) || ! is_email( get_option( 'wpfm_user_email', '' ) ) || ! apply_filters( 'wpfm_is_premium', false ) ) {
			return;
		}

		if ( ! self::acquire_email_lock() ) {
			return;
		}

		$batches = get_option( self::EMAIL_BATCH_OPTION, array() );
		$queued  = array();

		foreach ( is_array( $batches ) ? $batches : array() as $batch ) {
			foreach ( $batch['errors'] ?? array() as $queued_feed_id => $started_at ) {
				$queued[ $queued_feed_id ] = array(
					'feed_id'    => absint( $queued_feed_id ),
					'started_at' => absint( $started_at ),
				);
			}
		}

		$queued[ absint( $feed_id ) ] = array(
			'feed_id'    => absint( $feed_id ),
			'started_at' => absint( $error['scheduled_started_at'] ?? time() ),
		);

		self::replace_email_batches( array_values( $queued ), $batches );
		self::release_email_lock();
	}

	/**
	 * Renders and sends one queued feed-error email batch through WooCommerce email transport.
	 *
	 * @param string $batch_id Fixed-window anchor identifying the queued batch to send.
	 *
	 * @return void
	 */
	public function send_email_batch( $batch_id ) {
		if ( ! self::acquire_email_lock() ) {
			return;
		}

		$batches  = get_option( self::EMAIL_BATCH_OPTION, array() );
		$batch_id = (string) $batch_id;

		if ( 'yes' !== get_option( self::EMAIL_ENABLED_OPTION, 'no' ) || empty( $batches[ $batch_id ] ) ) {
			unset( $batches[ $batch_id ] );
			update_option( self::EMAIL_BATCH_OPTION, $batches, false );
			self::release_email_lock();
			return;
		}

		$batch  = $batches[ $batch_id ];
		$errors = array();
		foreach ( array_keys( $batch['errors'] ?? array() ) as $feed_id ) {
			$error = get_post_meta( $feed_id, self::ERROR_META, true );
			if ( is_array( $error ) && 'active' === ( $error['status'] ?? '' ) ) {
				$errors[ $feed_id ] = $error;
			}
		}

		if ( ! $errors ) {
			unset( $batches[ $batch_id ] );
			update_option( self::EMAIL_BATCH_OPTION, $batches, false );
			self::release_email_lock();
			return;
		}

		$recipient       = sanitize_email( get_option( 'wpfm_user_email', '' ) );
		$context         = array(
			'{{store_name}}'       => esc_html( get_bloginfo( 'name' ) ),
			'{{store_url}}'        => esc_url( home_url( '/' ) ),
			'{{error_count}}'      => (string) count( $errors ),
			'{{batch_started_at}}' => esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), absint( $batch['anchor_started_at'] ), wp_timezone() ) ),
			'{{feed_error_table}}' => self::build_feed_error_table( $errors ),
			'{{feeds_url}}'        => esc_url( admin_url( 'edit.php?post_type=product-feed' ) ),
		);
		$subject_context = array_intersect_key(
			$context,
			array(
				'{{store_name}}'  => true,
				'{{error_count}}' => true,
			)
		);
		$subject         = sanitize_text_field( strtr( self::EMAIL_SUBJECT, $subject_context ) );
		$body            = strtr( self::get_default_email_template(), $context );

		if ( $recipient ) {
			if ( ! class_exists( 'WC_Email', false ) && defined( 'WC_ABSPATH' ) ) {
				include_once WC_ABSPATH . 'includes/emails/class-wc-email.php';
			}

			if ( class_exists( 'WC_Email' ) ) {
				try {
					$email = new WC_Email();
					$email->send( $recipient, $subject, $body, $email->get_headers(), $email->get_attachments() );
				} catch ( Exception $exception ) {
					if ( function_exists( 'is_wpfm_logging_enabled' ) && is_wpfm_logging_enabled() && function_exists( 'wc_get_logger' ) ) {
						wc_get_logger()->warning( $exception->getMessage(), array( 'source' => 'WPFM_FEED_ERROR_EMAIL' ) );
					}
				}
			}
		}

		unset( $batches[ $batch_id ] );
		update_option( self::EMAIL_BATCH_OPTION, $batches, false );
		self::release_email_lock();
	}

	/**
	 * Removes feature-owned backup/error files and index state when a feed is deleted.
	 *
	 * @param int $post_id Post ID used to clean only state belonging to the deleted feed.
	 *
	 * @return void
	 */
	public function cleanup_feed_error_files( $post_id ) {
		if ( 'product-feed' !== get_post_type( $post_id ) ) {
			return;
		}

		$run   = get_post_meta( $post_id, self::RUN_META, true );
		$error = get_post_meta( $post_id, self::ERROR_META, true );
		if ( is_array( $run ) && ! empty( $run['backup_path'] ) ) {
			self::delete_owned_file( $run['backup_path'] );
		}
		if ( is_array( $error ) && ! empty( $error['error_feed_path'] ) ) {
			self::delete_owned_file( $error['error_feed_path'] );
		}
		if ( is_array( $error ) && ! empty( $error['valid_backup_path'] ) ) {
			self::delete_owned_file( $error['valid_backup_path'] );
		}

		self::update_active_error_index( $post_id, false );
	}

	/**
	 * Returns the system-defined HTML template for feed-error notification emails.
	 *
	 * @return string Trusted inline-styled HTML containing escaped placeholder values.
	 */
	private static function get_default_email_template() {
		return <<<'HTML'
<div style="margin:0;padding:24px;background:#f5f5f4;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#1e1e1e;">
  <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e7e5e4;border-radius:8px;overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:4px solid #dc2626;">
      <p style="margin:0;color:#57534e;font-size:13px;">Product Feed Manager</p>
      <h1 style="margin:6px 0 0;font-size:22px;line-height:1.3;color:#1e1e1e;">A scheduled feed needs attention</h1>
    </div>
    <div style="padding:24px;">
      <p style="margin:0 0 12px;font-size:15px;line-height:1.6;">Product Feed Manager detected {{error_count}} feed error(s) for <strong>{{store_name}}</strong>.</p>
      <p style="margin:0 0 20px;color:#57534e;font-size:14px;line-height:1.6;">A lower generated feed was kept separately for comparison. The last valid public feed was restored where file rollback is supported.</p>
      {{feed_error_table}}
      <p style="margin:24px 0 0;"><a href="{{feeds_url}}" style="display:inline-block;padding:11px 18px;background:#02b5ff;color:#ffffff;text-decoration:none;border-radius:4px;font-size:14px;font-weight:600;">Review product feeds</a></p>
    </div>
    <div style="padding:16px 24px;background:#fafaf9;border-top:1px solid #e7e5e4;color:#78716c;font-size:12px;line-height:1.5;">First run in this notification: {{batch_started_at}}<br>Store: <a href="{{store_url}}" style="color:#0099d9;">{{store_url}}</a></div>
  </div>
</div>
HTML;
	}

	/**
	 * Accepts a completed automatic run and removes its temporary protection state.
	 *
	 * @param int   $feed_id Product-feed post ID whose non-decreasing output is accepted.
	 * @param array $run     Run snapshot containing the backup path that is no longer needed.
	 *
	 * @return void
	 */
	private static function accept_run( $feed_id, $run ) {
		if ( ! empty( $run['backup_path'] ) ) {
			self::delete_owned_file( $run['backup_path'] );
		}

		delete_post_meta( $feed_id, self::RUN_META );
		self::clear_error( $feed_id );
	}

	/**
	 * Preserves lower output, restores the valid snapshot, and records a count-drop error.
	 *
	 * @param int   $feed_id        Product-feed post ID whose lower output must be rejected.
	 * @param array $run            Snapshot containing the previous valid file and metadata.
	 * @param int   $generated_count Completed feed-item count used in the error comparison.
	 *
	 * @return void
	 */
	private static function reject_run( $feed_id, $run, $generated_count ) {
		$detected_at      = time();
		$generated_totals = get_post_meta( $feed_id, '_rex_feed_total_products', true );
		$restore_result   = self::restore_snapshot( $feed_id, $run, true );
		$error_type       = 'product_count_decreased';
		$message          = '';

		if ( ! $restore_result['restored'] ) {
			self::log_restore_failure( $feed_id, $run );
			$error_type = 'restore_failed';
			$message    = __( 'A lower-count feed was generated, but the last valid feed could not be restored automatically.', 'rex-product-feed' );
		}

		$error = array(
			'status'               => 'active',
			'type'                 => $error_type,
			'message'              => $message,
			'last_valid_count'     => (int) $run['valid_count'],
			'generated_count'      => (int) $generated_count,
			'generated_totals'     => is_array( $generated_totals ) ? $generated_totals : array(),
			'missing_count'        => max( 0, (int) $run['valid_count'] - (int) $generated_count ),
			'scheduled_started_at' => absint( $run['started_at'] ?? $detected_at ),
			'detected_at'          => $detected_at,
			'canonical_feed_url'   => $run['canonical_url'] ?? '',
			'error_feed_url'       => $restore_result['error_url'],
			'error_feed_path'      => $restore_result['error_path'],
			'valid_backup_path'    => $restore_result['restored'] ? '' : ( $run['backup_path'] ?? '' ),
			'restore_succeeded'    => $restore_result['restored'],
		);

		self::record_error( $feed_id, $error );
		delete_post_meta( $feed_id, self::RUN_META );
	}

	/**
	 * Restores the automatic run's valid file and optionally retains the failed output.
	 *
	 * @param int   $feed_id         Product-feed post ID used to name a retained comparison file.
	 * @param array $run             Snapshot containing the backup path, URL, and count metadata.
	 * @param bool  $preserve_output Whether the current canonical output must be moved aside for comparison.
	 *
	 * @return array Restore status plus the retained comparison file URL and path, when available.
	 */
	private static function restore_snapshot( $feed_id, $run, $preserve_output ) {
		$canonical_path = $run['canonical_path'] ?? '';
		$backup_path    = $run['backup_path'] ?? '';
		$result         = array(
			'restored'   => false,
			'error_url'  => '',
			'error_path' => '',
		);

		if ( ! $backup_path || ! file_exists( $backup_path ) || ! $canonical_path ) {
			$result['restored'] = null === ( $run['valid_count'] ?? null );
			return $result;
		}

		if ( $preserve_output && file_exists( $canonical_path ) ) {
			$error_path = trailingslashit( dirname( $canonical_path ) ) . sprintf(
				'error-feed-%1$d-%2$d.%3$s',
				absint( $feed_id ),
				time(),
				sanitize_key( $run['format'] ?? 'xml' )
			);
			if ( ! rename( $canonical_path, $error_path ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
				return $result;
			}
			$result['error_path'] = $error_path;
			$result['error_url']  = trailingslashit( wp_upload_dir()['baseurl'] ) . 'rex-feed/' . wp_basename( $error_path );
		}

		$result['restored'] = rename( $backup_path, $canonical_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! $result['restored'] && ! file_exists( $canonical_path ) ) {
			$result['restored'] = copy( $backup_path, $canonical_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( $result['restored'] ) {
				self::delete_owned_file( $backup_path );
			}
		}

		if ( ! $result['restored'] && $result['error_path'] && ! file_exists( $canonical_path ) ) {
			if ( rename( $result['error_path'], $canonical_path ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
				$result['error_path'] = '';
				$result['error_url']  = '';
			}
			return $result;
		}

		if ( $result['restored'] ) {
			self::restore_count_metadata( $feed_id, $run );
			if ( ! empty( $run['canonical_url'] ) ) {
				update_post_meta( $feed_id, '_rex_feed_xml_file', esc_url_raw( $run['canonical_url'] ) );
			}
		}

		return $result;
	}

	/**
	 * Logs a failed canonical-feed restoration through the plugin's existing logger setting.
	 *
	 * @param int   $feed_id Product-feed post ID included so support can identify the affected feed.
	 * @param array $run     Run snapshot supplying the canonical and backup paths needed for diagnosis.
	 *
	 * @return void
	 */
	private static function log_restore_failure( $feed_id, $run ) {
		if ( ! function_exists( 'is_wpfm_logging_enabled' ) || ! is_wpfm_logging_enabled() || ! function_exists( 'wc_get_logger' ) ) {
			return;
		}

		wc_get_logger()->warning(
			'Restoring the last valid product feed failed.',
			array(
				'source'         => 'WPFM_PRODUCT_COUNT_GUARD',
				'feed_id'        => absint( $feed_id ),
				'canonical_path' => $run['canonical_path'] ?? '',
				'backup_path'    => $run['backup_path'] ?? '',
			)
		);
	}

	/**
	 * Restores the product-count metadata captured before an automatic run.
	 *
	 * @param int   $feed_id Product-feed post ID whose displayed counts must match the valid file.
	 * @param array $run     Snapshot carrying the prior metadata values and existence flags.
	 *
	 * @return void
	 */
	private static function restore_count_metadata( $feed_id, $run ) {
		if ( ! empty( $run['valid_totals_exists'] ) ) {
			update_post_meta( $feed_id, '_rex_feed_total_products', $run['valid_totals'] );
		} else {
			delete_post_meta( $feed_id, '_rex_feed_total_products' );
		}

		if ( ! empty( $run['valid_all_feed_count_exists'] ) ) {
			update_post_meta( $feed_id, '_rex_feed_total_products_for_all_feed', $run['valid_all_feed_count'] );
		} else {
			delete_post_meta( $feed_id, '_rex_feed_total_products_for_all_feed' );
		}
	}

	/**
	 * Reads the generated feed count from existing metadata with a file fallback.
	 *
	 * @param int    $feed_id Product-feed post ID used to read the generator's existing count metadata.
	 * @param string $path    Generated file path used only when count metadata is unavailable.
	 * @param string $format  Feed format needed to count fallback file records correctly.
	 *
	 * @return int|null Feed-item count including zero, or null when no count can be determined.
	 */
	private static function get_feed_count( $feed_id, $path, $format ) {
		$totals = get_post_meta( $feed_id, '_rex_feed_total_products', true );
		if ( is_array( $totals ) && array_key_exists( 'total', $totals ) ) {
			return (int) $totals['total'];
		}

		if ( metadata_exists( 'post', $feed_id, '_rex_feed_total_products_for_all_feed' ) ) {
			return (int) get_post_meta( $feed_id, '_rex_feed_total_products_for_all_feed', true );
		}

		return self::get_file_count( $path, $format );
	}

	/**
	 * Counts the completed feed file so an empty output cannot reuse stale count metadata.
	 *
	 * @param string $path   Local canonical feed path read after generation completes.
	 * @param string $format Feed format used to select the existing row or item counting convention.
	 *
	 * @return int|null Feed-item count including zero, or null when the file cannot be read or decoded.
	 */
	public static function get_file_count( $path, $format ) {
		if ( ! $path || ! is_readable( $path ) ) {
			return null;
		}

		if ( in_array( $format, array( 'csv', 'tsv', 'txt', 'text' ), true ) ) {
			return self::get_delimited_file_count( $path );
		}

		if ( 'json' === $format ) {
			$contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( false === $contents ) {
				return null;
			}

			$data = json_decode( $contents, true );
			if ( ! is_array( $data ) ) {
				return null;
			}
			if ( isset( $data['products'] ) && is_array( $data['products'] ) ) {
				return count( $data['products'] );
			}
			if ( isset( $data['images'] ) && is_array( $data['images'] ) ) {
				return count( $data['images'] );
			}
			if ( array() === $data || array_keys( $data ) === range( 0, count( $data ) - 1 ) ) {
				return count( $data );
			}
			return null;
		}

		return self::get_xml_file_count( $path );
	}

	/**
	 * Counts non-empty delimited-file rows without retaining the complete file in memory.
	 *
	 * Physical lines remain the counting convention so existing CSV, TSV, and text feed
	 * totals do not change. The first non-empty line is treated as the header row.
	 *
	 * @param string $path Readable local feed path.
	 *
	 * @return int|null Feed row count including zero, or null when the stream cannot be read.
	 */
	private static function get_delimited_file_count( $path ) {
		$handle = fopen( $path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( false === $handle ) {
			return null;
		}

		$line_count = 0;
		while ( false !== ( $line = fgets( $handle ) ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( '' !== rtrim( $line, "\r\n" ) ) {
				++$line_count;
			}
		}

		$read_failed = ! feof( $handle );
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		return $read_failed ? null : max( 0, $line_count - 1 );
	}

	/**
	 * Counts XML product tags in fixed-size chunks so memory use does not grow with feed size.
	 *
	 * Chunk overlap catches tags split across reads. Separate counters and the original
	 * expression preserve existing matching and tag-priority behavior.
	 *
	 * @param string $path Readable local XML feed path.
	 *
	 * @return int|null Feed-item count including zero, or null when the stream cannot be read.
	 */
	private static function get_xml_file_count( $path ) {
		$handle = fopen( $path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( false === $handle ) {
			return null;
		}

		$chunk_size = 65536;
		$overlap    = '';
		$counts = array(
			'item'    => 0,
			'entry'   => 0,
			'offer'   => 0,
			'product' => 0,
		);
		$pattern = '/<(item|entry|offer|product)(?:\s|>)/i';

		while ( ! feof( $handle ) ) {
			$chunk = fread( $handle, $chunk_size ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( false === $chunk ) {
				fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions
				return null;
			}

			$overlap_length = strlen( $overlap );
			$buffer         = $overlap . $chunk;
			$match_result   = preg_match_all( $pattern, $buffer, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
			if ( false === $match_result ) {
				fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions
				return null;
			}

			foreach ( $matches as $match ) {
				$match_end = $match[0][1] + strlen( $match[0][0] );
				if ( $match_end <= $overlap_length ) {
					continue;
				}

				++$counts[ strtolower( $match[1][0] ) ];
			}

			$overlap = substr( $buffer, -8 );
		}

		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		foreach ( $counts as $count ) {
			if ( $count ) {
				return $count;
			}
		}

		return 0;
	}

	/**
	 * Resolves the canonical feed URL, local path, and format from existing metadata.
	 *
	 * @param int $feed_id Product-feed post ID used to read the released URL and format metadata.
	 *
	 * @return array Canonical URL, validated local path, and normalized format.
	 */
	private static function get_canonical_file( $feed_id ) {
		$url    = get_post_meta( $feed_id, '_rex_feed_xml_file', true );
		$url    = $url ? $url : get_post_meta( $feed_id, 'rex_feed_xml_file', true );
		$format = get_post_meta( $feed_id, '_rex_feed_feed_format', true );
		$format = $format ? $format : get_post_meta( $feed_id, 'rex_feed_feed_format', true );
		$format = sanitize_key( $format ? $format : 'xml' );

		return array(
			'url'    => esc_url_raw( $url ),
			'path'   => self::resolve_feed_url_to_path( $url ),
			'format' => $format,
		);
	}

	/**
	 * Converts a plugin feed URL to a safe path inside the uploads rex-feed directory.
	 *
	 * @param string $url Feed URL whose filename is needed for backup, restore, or cleanup.
	 *
	 * @return string Validated local path, or an empty string for non-plugin/external URLs.
	 */
	private static function resolve_feed_url_to_path( $url ) {
		$uploads  = wp_upload_dir();
		$feed_url = trailingslashit( $uploads['baseurl'] ) . 'rex-feed/';
		if ( ! is_string( $url ) || 0 !== strpos( $url, $feed_url ) ) {
			return '';
		}

		$file_name = wp_basename( wp_parse_url( $url, PHP_URL_PATH ) );
		if ( ! $file_name || preg_match( '/[^A-Za-z0-9._-]/', $file_name ) ) {
			return '';
		}

		return trailingslashit( $uploads['basedir'] ) . 'rex-feed/' . $file_name;
	}

	/**
	 * Persists a feed error, maintains the admin index, and queues email when enabled.
	 *
	 * @param int   $feed_id Product-feed post ID used as the error owner and email row identifier.
	 * @param array $error   Sanitized error facts required by admin and email renderers.
	 *
	 * @return void
	 */
	private static function record_error( $feed_id, $error ) {
		$old_error = get_post_meta( $feed_id, self::ERROR_META, true );

		update_post_meta( $feed_id, self::ERROR_META, $error );
		self::update_active_error_index( $feed_id, true );

		if ( is_array( $old_error ) && ! empty( $old_error['error_feed_path'] ) && ( $error['error_feed_path'] ?? '' ) !== $old_error['error_feed_path'] ) {
			self::delete_owned_file( $old_error['error_feed_path'] );
		}

		self::queue_email_error( $feed_id, $error );
	}

	/**
	 * Clears a resolved feed error and removes its comparison file and index entry.
	 *
	 * @param int $feed_id Product-feed post ID whose error was resolved by accepted output.
	 *
	 * @return void
	 */
	private static function clear_error( $feed_id ) {
		$error = get_post_meta( $feed_id, self::ERROR_META, true );
		if ( is_array( $error ) && ! empty( $error['error_feed_path'] ) ) {
			self::delete_owned_file( $error['error_feed_path'] );
		}
		if ( is_array( $error ) && ! empty( $error['valid_backup_path'] ) ) {
			self::delete_owned_file( $error['valid_backup_path'] );
		}
		delete_post_meta( $feed_id, self::ERROR_META );
		self::update_active_error_index( $feed_id, false );
	}

	/**
	 * Adds or removes one feed ID from the option used by global admin notices.
	 *
	 * @param int  $feed_id Feed ID whose active-error membership must change.
	 * @param bool $active  Whether the feed currently has an active error.
	 *
	 * @return void
	 */
	private static function update_active_error_index( $feed_id, $active ) {
		$ids = array_map( 'absint', (array) get_option( self::ERROR_IDS_OPTION, array() ) );
		if ( $active ) {
			$ids[] = absint( $feed_id );
		} else {
			$ids = array_diff( $ids, array( absint( $feed_id ) ) );
		}
		update_option( self::ERROR_IDS_OPTION, array_values( array_unique( array_filter( $ids ) ) ), false );
	}

	/**
	 * Returns active feed IDs and removes stale entries from the lightweight notice index.
	 *
	 * @return int[] Product-feed IDs that still own active error metadata.
	 */
	private static function get_active_error_ids() {
		$stored = array_map( 'absint', (array) get_option( self::ERROR_IDS_OPTION, array() ) );
		$active = array();
		foreach ( $stored as $feed_id ) {
			$error = get_post_meta( $feed_id, self::ERROR_META, true );
			if ( 'product-feed' === get_post_type( $feed_id ) && is_array( $error ) && 'active' === ( $error['status'] ?? '' ) ) {
				$active[] = $feed_id;
			}
		}

		if ( $stored !== $active ) {
			update_option( self::ERROR_IDS_OPTION, $active, false );
		}

		return $active;
	}

	/**
	 * Deletes only feature-owned backup or rejected files inside the feed directory.
	 *
	 * @param string $path Candidate path checked before deletion to protect unrelated files.
	 *
	 * @return void
	 */
	private static function delete_owned_file( $path ) {
		$uploads   = wp_upload_dir();
		$feed_dir  = wp_normalize_path( trailingslashit( $uploads['basedir'] ) . 'rex-feed/' );
		$path      = wp_normalize_path( (string) $path );
		$file_name = wp_basename( $path );

		if ( 0 === strpos( $path, $feed_dir ) && ( 0 === strpos( $file_name, 'error-feed-' ) || 0 === strpos( $file_name, '.wpfm-valid-' ) ) && file_exists( $path ) ) {
			wp_delete_file( $path );
		}
	}

	/**
	 * Creates escaped table rows for every feed error included in an email batch.
	 *
	 * @param array $errors Error states keyed by feed ID so titles, merchants, and links can be resolved live.
	 *
	 * @return string Trusted HTML table inserted into the system email wrapper.
	 */
	private static function build_feed_error_table( $errors ) {
		$rows = '';
		foreach ( $errors as $feed_id => $error ) {
			$merchant   = get_post_meta( $feed_id, '_rex_feed_merchant', true );
			$merchant   = $merchant ? $merchant : get_post_meta( $feed_id, 'rex_feed_merchant', true );
			$merchant   = ucwords( str_replace( array( '_', '-' ), ' ', $merchant ) );
			$last_valid = null === ( $error['last_valid_count'] ?? null ) ? '&mdash;' : number_format_i18n( (int) $error['last_valid_count'] );
			$generated  = null === ( $error['generated_count'] ?? null ) ? '&mdash;' : number_format_i18n( (int) $error['generated_count'] );
			$links      = '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $feed_id ) . '&action=edit' ) ) . '" style="color:#0099d9;">' . esc_html__( 'Investigate', 'rex-product-feed' ) . '</a>';

			if ( ! empty( $error['canonical_feed_url'] ) ) {
				$links .= ' &middot; <a href="' . esc_url( $error['canonical_feed_url'] ) . '" style="color:#0099d9;">' . esc_html__( 'Valid feed', 'rex-product-feed' ) . '</a>';
			}
			if ( ! empty( $error['error_feed_url'] ) ) {
				$links .= ' &middot; <a href="' . esc_url( $error['error_feed_url'] ) . '" style="color:#0099d9;">' . esc_html__( 'Compare output', 'rex-product-feed' ) . '</a>';
			}

			$rows .= '<tr>';
			$rows .= '<td style="padding:10px;border-bottom:1px solid #e7e5e4;"><strong>' . esc_html( get_the_title( $feed_id ) ) . '</strong><br><span style="color:#57534e;">' . esc_html( $merchant ) . '</span></td>';
			$rows .= '<td align="right" style="padding:10px;border-bottom:1px solid #e7e5e4;">' . $last_valid . '</td>';
			$rows .= '<td align="right" style="padding:10px;border-bottom:1px solid #e7e5e4;">' . $generated . '</td>';
			$rows .= '<td style="padding:10px;border-bottom:1px solid #e7e5e4;">' . $links . '</td>';
			$rows .= '</tr>';
		}

		return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #e7e5e4;font-size:13px;"><thead><tr style="background:#fff1f2;"><th align="left" style="padding:10px;border-bottom:1px solid #fecdd3;">' . esc_html__( 'Feed / merchant', 'rex-product-feed' ) . '</th><th align="right" style="padding:10px;border-bottom:1px solid #fecdd3;">' . esc_html__( 'Last valid', 'rex-product-feed' ) . '</th><th align="right" style="padding:10px;border-bottom:1px solid #fecdd3;">' . esc_html__( 'Generated', 'rex-product-feed' ) . '</th><th align="left" style="padding:10px;border-bottom:1px solid #fecdd3;">' . esc_html__( 'Links', 'rex-product-feed' ) . '</th></tr></thead><tbody>' . $rows . '</tbody></table>';
	}

	/**
	 * Rebuilds fixed-window batches in scheduled-start order and schedules one send per group.
	 *
	 * Rebuilding keeps the first scheduled run as the anchor even when a later-started feed
	 * finishes and reports its error before an earlier-started feed.
	 *
	 * @param array $queued       Unsent feed and start-time items that need grouping.
	 * @param array $old_batches Existing batches whose outdated scheduled actions are cancelled.
	 *
	 * @return void
	 */
	private static function replace_email_batches( $queued, $old_batches ) {
		foreach ( is_array( $old_batches ) ? $old_batches : array() as $old_batch_id => $old_batch ) {
			if ( function_exists( 'as_unschedule_all_actions' ) ) {
				as_unschedule_all_actions( self::EMAIL_HOOK, array( (string) $old_batch_id ), 'wpfm' );
			}
		}

		$start_times = array_column( $queued, 'started_at' );
		array_multisort( $start_times, SORT_ASC, SORT_NUMERIC, $queued );

		$batches = array();
		foreach ( $queued as $item ) {
			$started_at = absint( $item['started_at'] );
			$batch_id   = '';
			foreach ( $batches as $candidate_id => $candidate ) {
				if ( $started_at <= (int) $candidate['anchor_started_at'] + 600 ) {
					$batch_id = $candidate_id;
					break;
				}
			}

			if ( '' === $batch_id ) {
				$batch_id             = (string) $started_at;
				$batches[ $batch_id ] = array(
					'anchor_started_at' => $started_at,
					'errors'            => array(),
				);
			}

			$batches[ $batch_id ]['errors'][ $item['feed_id'] ] = $started_at;
		}

		foreach ( $batches as $batch_id => $batch ) {
			if ( function_exists( 'as_schedule_single_action' ) ) {
				as_schedule_single_action( max( time() + 1, $batch['anchor_started_at'] + 600 ), self::EMAIL_HOOK, array( (string) $batch_id ), 'wpfm' );
			}
		}

		update_option( self::EMAIL_BATCH_OPTION, $batches, false );
	}

	/**
	 * Cancels all currently queued feed-error emails and removes their option state.
	 *
	 * @return void
	 */
	private static function clear_email_batches() {
		$batches = get_option( self::EMAIL_BATCH_OPTION, array() );
		foreach ( is_array( $batches ) ? $batches : array() as $batch_id => $batch ) {
			if ( function_exists( 'as_unschedule_all_actions' ) ) {
				as_unschedule_all_actions( self::EMAIL_HOOK, array( (string) $batch_id ), 'wpfm' );
			}
		}
		delete_option( self::EMAIL_BATCH_OPTION );
	}

	/**
	 * Acquires the short option lock that protects concurrent email batch updates.
	 *
	 * @return bool Whether this request obtained the lock and may update batch state.
	 */
	private static function acquire_email_lock() {
		if ( add_option( self::EMAIL_LOCK_OPTION, time(), '', false ) ) {
			return true;
		}

		$locked_at = absint( get_option( self::EMAIL_LOCK_OPTION, 0 ) );
		if ( ! $locked_at || $locked_at >= time() - 30 ) {
			return false;
		}

		delete_option( self::EMAIL_LOCK_OPTION );
		return add_option( self::EMAIL_LOCK_OPTION, time(), '', false );
	}

	/**
	 * Releases the email batch option lock after the protected update finishes.
	 *
	 * @return void
	 */
	private static function release_email_lock() {
		delete_option( self::EMAIL_LOCK_OPTION );
	}
}
