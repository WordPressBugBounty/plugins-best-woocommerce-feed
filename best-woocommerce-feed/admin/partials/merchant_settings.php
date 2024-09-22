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

$rex_google_merchant = new Rex_Google_Merchant_Settings_Api();
$data                = function_exists( 'rex_feed_get_sanitized_get_post' ) ? rex_feed_get_sanitized_get_post() : array();
$data                = !empty( $data[ 'get' ] ) ? $data[ 'get' ] : array();
$current_page        = isset( $data[ 'page' ] ) ? sanitize_text_field( $data[ 'page' ] ) : '';
$html                = '';
$disable             = '';
$client_id           = $rex_google_merchant::$client_id;
$client_secret       = $rex_google_merchant::$client_secret;
$merchant_id         = $rex_google_merchant::$merchant_id;
$redirect_uri        = admin_url( 'admin.php?page=merchant_settings' );

if ( isset( $data[ 'code' ] ) && 'merchant_settings' === $current_page ) {
	$code = sanitize_text_field( $data[ 'code' ] );
	$rex_google_merchant->save_access_token( sanitize_text_field( $data[ 'code' ] ) );
}

if ( !( $rex_google_merchant->is_authenticate() ) ) {
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

if ( $client_id && $client_secret && $merchant_id ) {
	$disable = 'disabled';
}

require_once plugin_dir_path( __FILE__ ) . 'loading-spinner.php';
?>
<div class="merchant-settings">
	<div class="left-merchant">
		<!-- single-merchant-area .end  -->
        <?php echo $html; // phpcs:ignore ?>
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

							<button class="btn waves-effect waves-light btn-default" type="submit" name="action" <?php echo esc_html( $disable ); ?>><?php echo esc_html__( 'Submit', 'rex-product-feed' ); ?>

							</button>
						</div>

					</div>
				</form>
			</div>
		</div>
		<!-- single-merchant-area .end -->

	</div>
	<!-- left-merchant -->
</div>
<!-- merchant-settings .end -->

