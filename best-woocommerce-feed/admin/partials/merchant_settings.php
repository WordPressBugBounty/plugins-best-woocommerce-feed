<?php
/**
 * This file is responsible for displaying google merchant api page section
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

$rex_google_merchant = new Rex_Feed_Google_Shopping_Api();
$data                = function_exists( 'rex_feed_get_sanitized_get_post' ) ? rex_feed_get_sanitized_get_post() : array();
$data                = !empty( $data[ 'get' ] ) ? $data[ 'get' ] : array();
$current_page        = isset( $data[ 'page' ] ) ? sanitize_text_field( $data[ 'page' ] ) : '';
$html                = '';
$client_id           = $rex_google_merchant->get_client_id();
$client_secret       = $rex_google_merchant->get_client_secret();
$merchant_id         = $rex_google_merchant->get_merchant_id();
$redirect_uri        = $rex_google_merchant->get_redirect_url();

if ( 'merchant_settings' === $current_page ) {
	$code = !empty( $data[ 'code' ] ) ? sanitize_text_field( $data[ 'code' ] ) : null;
	if ( $code ) {
		$rex_google_merchant->fetch_access_token( $code );
	}
}

// Check authorization status
$is_authorized = $rex_google_merchant->is_authorized();

if ( ! $is_authorized ) {
	if ( $client_id && $client_secret && $merchant_id ) {
		$html = $rex_google_merchant->get_access_token_html();
	}
	else {
		$html = $rex_google_merchant->get_new_user_authenticate_markups();
	}
}
else {
	$html = $rex_google_merchant->authorization_success_html();
}

require_once plugin_dir_path( __FILE__ ) . 'loading-spinner.php';
?>
<div class="merchant-settings">
	<div class="left-merchant">
		<div class="single-merchant-area configure">
			<div class="single-merchant-block">
				<div class="merchant-authorized-area">
				</div>
				<h2 class="title"><?php echo esc_html__( 'Google Merchant Center Authorization', 'rex-product-feed' ); ?></h2>
				<form class="rex-google-merchant" id="rex-google-merchant">
					<div class="row">
						<div class="input-field">
							<input id="client_id" type="text" name="client_id" class="validate" required value="<?php echo esc_html( $client_id ); ?>">
							<label for="client_id"><?php echo esc_html__( 'Client ID#: ', 'rex-product-feed' ); ?></label>
						</div>
						<div class="input-field">
							<input id="client_secret" type="text" name="client_secret" class="validate" required value="<?php echo esc_html( $client_secret ); ?>">
							<label for="client_secret"><?php echo esc_html__( 'Client Secret: ', 'rex-product-feed' ); ?></label>
						</div>
						<div class="input-field">
							<input id="merchant_id" type="text" name="merchant_id" class="validate" required value="<?php echo esc_html( $merchant_id ); ?>">
							<label for="merchant_id"><?php echo esc_html__( 'Merchant ID# : ', 'rex-product-feed' ); ?></label>
						</div>

						<div class="input-field">
							<input disabled value="<?php echo esc_url( $redirect_uri ); ?>" id="disabled" type="text" class="validate">
							<label for="disabled"><?php echo esc_html__( 'Redirect URL', 'rex-product-feed' ); ?></label>
						</div>

						<div class="button-area">
							<button class="btn waves-effect waves-light btn-default rex-reset-btn" type="button" style="margin-right: 10px;"><?php echo esc_html__( 'Reset', 'rex-product-feed' ); ?>

							</button>

							<button class="btn waves-effect waves-light btn-default" type="submit" name="action"><?php echo esc_html__( 'Submit', 'rex-product-feed' ); ?>

							</button>
						</div>

					</div>
				</form>
			</div>
		</div>
		<!-- single-merchant-area .end -->
	</div>
	<!-- left-merchant -->

	<div class="right-merchant">
		<?php if ( $is_authorized ) :
			$legacy_count = count( get_posts( array(
				'post_type'      => 'product-feed',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'relation' => 'OR',
						array( 'key' => '_rex_feed_merchant', 'value' => 'google', 'compare' => '=' ),
						array( 'key' => 'rex_feed_merchant', 'value' => 'google', 'compare' => '=' ),
					),
					array(
						'key'     => '_rex_feed_google_data_source_id',
						'compare' => 'NOT EXISTS',
					),
					array(
						'relation' => 'OR',
						array( 'key' => '_rex_feed_google_data_feed_id', 'value' => '', 'compare' => '!=' ),
						array( 'key' => 'rex_feed_google_data_feed_id', 'value' => '', 'compare' => '!=' ),
						array( 'key' => '_rex_feed_is_google_content_api', 'value' => 'yes', 'compare' => '=' ),
						array( 'key' => 'rex_feed_is_google_content_api', 'value' => 'yes', 'compare' => '=' ),
					),
				),
			) ) );
			$migrated_count = count( get_posts( array(
				'post_type'      => 'product-feed',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'relation' => 'OR',
						array( 'key' => '_rex_feed_merchant', 'value' => 'google', 'compare' => '=' ),
						array( 'key' => 'rex_feed_merchant', 'value' => 'google', 'compare' => '=' ),
					),
					array(
						'key'     => '_rex_feed_google_data_source_id',
						'compare' => 'EXISTS',
					),
				),
			) ) );

			if ( $legacy_count > 0 || $migrated_count > 0 ) :
		?>
		<div class="single-merchant-area api-version-notice-area">
			<div class="single-merchant-block">
				<h2 class="title"><?php esc_html_e( 'Google API Version', 'rex-product-feed' ); ?></h2>
				<?php if ( $legacy_count > 0 ) : ?>
				<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:10px 14px;margin-bottom:8px;">
					<p style="color:#856404;font-weight:600;margin:0 0 4px;">
						&#9888;
						<?php
						echo esc_html( sprintf(
							/* translators: %d: number of feeds */
							_n(
								'%d feed still on Content API (retires August 18, 2026).',
								'%d feeds still on Content API (retires August 18, 2026).',
								$legacy_count,
								'rex-product-feed'
							),
							$legacy_count
						) );
						?>
					</p>
					<p style="color:#50575e;font-size:13px;margin:0 0 8px;">
						<?php esc_html_e( 'Open each feed\'s edit page and click "Migrate Now" to move it to Merchant API v1. Your existing OAuth credentials work — no re-authentication required.', 'rex-product-feed' ); ?>
					</p>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product-feed' ) ); ?>" class="button button-secondary" style="font-size:12px;">
						<?php esc_html_e( 'View product feeds', 'rex-product-feed' ); ?>
					</a>
				</div>
				<?php else : ?>
				<p style="color:#2271b1;font-weight:600;margin-bottom:8px;">&#10003; <?php esc_html_e( 'All Google feeds on Merchant API v1', 'rex-product-feed' ); ?></p>
				<p style="color:#50575e;font-size:13px;margin-bottom:0;"><?php esc_html_e( 'New feeds are automatically created on Merchant API v1.', 'rex-product-feed' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; endif; ?>

		<!-- Connection Status banner / Setup guide -->
		<?php echo $html; // phpcs:ignore ?>

		<!-- Setup Resource / Documentation Quick Links card -->
		<?php if ( $is_authorized ) : ?>
		<div class="single-merchant-area documentation-links-area" style="margin-top:20px;">
			<div class="single-merchant-block">
				<h2 class="title"><?php esc_html_e( 'Setup & Resources', 'rex-product-feed' ); ?></h2>
				<p style="color:#50575e;font-size:13px;margin-bottom:15px;line-height:1.4;">
					<?php esc_html_e( 'Need help setting up your Google Merchant Center integration? Use these quick links to view step-by-step guides.', 'rex-product-feed' ); ?>
				</p>
				<div class="single-merchant_pdf__link">
					<a href="<?php echo esc_url( 'https://rextheme.com/docs/how-to-auto-sync-product-feed-to-google-merchant-shop/?utm_source=plugin&utm_medium=get_started_auto_sync_link&utm_campaign=pfm_plugin' ); ?>" target="_blank" style="margin-bottom:8px; display:inline-block;">
						<?php esc_html_e( 'Merchant API v1 Setup Guide (Requires OAuth)', 'rex-product-feed' ); ?>
					</a>
					<a href="<?php echo esc_url( 'https://rextheme.com/docs/upload-woocomerce-product-feed-directly-to-google-merchant-center/?utm_source=plugin&utm_medium=google_form_direct_upload_link&utm_campaign=pfm_plugin' ); ?>" target="_blank" style="margin-bottom:8px; display:inline-block;">
						<?php esc_html_e( 'Direct Upload Method (No authorization)', 'rex-product-feed' ); ?>
					</a>
					<a href="<?php echo esc_url( 'https://rextheme.com/google-country-codes-list/?utm_source=plugin&utm_medium=google_form_abbreviation_link&utm_campaign=pfm_plugin' ); ?>" target="_blank" style="display:inline-block;">
						<?php esc_html_e( 'Check Google Country & Language Codes', 'rex-product-feed' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<!-- right-merchant -->
</div>
<!-- merchant-settings .end -->

