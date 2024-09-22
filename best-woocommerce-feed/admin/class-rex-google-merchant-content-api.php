<?php
/**
 * Rex_Google_Merchant_Settings_Api
 *
 * @package    Rex_Google_Merchant_Settings_Api
 * @subpackage admin/
 */

/**
 * This class is responsible to manage Google Merchant API functionalities
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Google_Merchant_Settings_Api
 * @subpackage admin/
 */
class Rex_Google_Merchant_Settings_Api {

	/**
	 * Client ID
	 *
	 * @var false|mixed|void
	 */
	public static $client_id;

	/**
	 * Client secrete key
	 *
	 * @var false|mixed|void
	 */
	public static $client_secret;

	/**
	 * Client merchant id
	 *
	 * @var false|mixed|void
	 */
	public static $merchant_id;

	/**
	 * Client object
	 *
	 * @var RexFeed\Google\Client
	 */
	protected static $client;

	/**
	 * Self class instance
	 *
	 * @var object
	 */
	protected static $instance;

	/**
	 * Initialize class functionalities
	 */
	public function __construct() {
		self::$client_id     = get_option( 'rex_google_client_id', '' );
		self::$client_secret = get_option( 'rex_google_client_secret', '' );
		self::$merchant_id   = get_option( 'rex_google_merchant_id', '' );
	}

	/**
	 * Get self instance
	 *
	 * @return Rex_Google_Merchant_Settings_Api|null
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Setup client initial settings
	 *
	 * @return \RexFeed\Google\Client
	 */
	public function init_client() {
		$redirect_uri = admin_url( 'admin.php?page=merchant_settings' );
		self::$client = self::get_client();
		self::$client->setClientId( self::$client_id );
		self::$client->setClientSecret( self::$client_secret );
		self::$client->setRedirectUri( $redirect_uri );
		self::$client->setScopes( 'https://www.googleapis.com/auth/content' );
		return self::$client;
	}

	/**
	 * Get client object
	 *
	 * @return \RexFeed\Google\Client
	 */
	public static function get_client() {
		return new RexFeed\Google\Client();
	}

	/**
	 * Get client's access token
	 *
	 * @return false|mixed|void
	 */
	public function get_access_token() {
		return get_option( 'rex_google_access_token', '' );
	}

	/**
	 * Check if client is already authenticated
	 *
	 * @return bool
	 */
	public function is_authenticate() {
		$access_token = $this->get_access_token();

		if ( !$access_token ) {
			return false;
		}

		$client_obj = self::get_client();

		if ( is_array( $access_token ) ) {
			$client_obj->setAccessToken( $access_token );
		} else {
			$client_obj->setAccessToken( json_decode( $access_token, true ) );
		}

		if ( $client_obj->isAccessTokenExpired() ) {
			return false;
		}
		return true;
	}

	/**
	 * Get markup for authentication message
	 *
	 * @return string
	 */
	public function get_access_token_html() {
		$client_obj   = self::get_client();
		$redirect_uri = admin_url( 'admin.php?page=merchant_settings' );
		$client_obj->setClientId( self::$client_id );
		$client_obj->setClientSecret( self::$client_secret );
		$client_obj->setRedirectUri( $redirect_uri );
		$client_obj->setScopes( 'https://www.googleapis.com/auth/content' );
		$login_url = $client_obj->createAuthurl();
		$btn_html  = '<a class="btn-default" href="' . $login_url . '" target="_blank">' . __( 'Authenticate', 'rex-product-feed' ) . '</a>';
		return '<div class="single-merchant-area authorized">
                <div class="single-merchant-block">
                    <header>
                        <h2 class="title">' . __( "You Are Not Authorized", "rex-product-feed" ) . '</h2>
                        <img src="' . WPFM_PLUGIN_ASSETS_FOLDER . "/icon/danger.png" . '" class="title-icon" alt="bwf-documentation">
                    </header>
                    <div class="body">
                        <p>' . __( 'Your access token has expired. This application uses OAuth 2.0 to Access Google APIs. Please insert the information below and authenticate token for Google Merchant Shop. Generated access token expires after 3600 sec.', 'rex-product-feed' ) . '</p>
                        <p class="single-merchant-bold">' . __( 'NB: This session expiration is set by Google. You only need to authorize while submitting a new feed. You can ignore this if you\'ve already submitted your feed to Google.', 'rex-product-feed' ) . '</p>
                        ' . $btn_html . '
                    </div>
                </div>
            </div>';
	}

	/**
	 * Get markups for new user authentication
	 *
	 * @return false|string
	 */
	public function get_new_user_authenticate_markups() {
		ob_start();
		?>
		<div class="single-merchant-area authorized">
			<div class="single-merchant-block">
				<header>
					<h2 class="title">
					<?php
						esc_html_e( "Authorize with GMC to send a new feed for the first time with API Method", "rex-product-feed" );
					?>
						</h2>
				</header>
				<div class="body">
					<p>
						<?php
						esc_html_e( 'To send a feed to the Google Merchant Center, you need to authorize with Google Merchant Center. You can send the feed to Google Merchant Center through direct upload method or by using the Content API.', 'rex-product-feed' );
						?>
					</p>
					<div class="single-merchant_pdf__link">
						<a href="
						<?php
						echo esc_url( 'https://rextheme.com/docs/upload-woocomerce-product-feed-directly-to-google-merchant-center/?utm_source=plugin&utm_medium=google_form_direct_upload_link&utm_campaign=pfm_plugin' )
						?>
						"
						   target="_blank">
						   <?php
							esc_html_e( 'Direct Upload Method (No need for authorization)', 'rex-product-feed' )
							?>
							</a>
						<a href="
						<?php
						echo esc_url( 'https://rextheme.com/docs/how-to-auto-sync-product-feed-to-google-merchant-shop/?utm_source=plugin&utm_medium=get_started_auto_sync_link&utm_campaign=pfm_plugin' )
						?>
						"
						   target="_blank">
						   <?php
							esc_html_e( 'API Method (Require authorization)', 'rex-product-feed' )
							?>
							</a>
					</div>
				</div>
			</div>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}

	/**
	 * Get markups for authorization success
	 *
	 * @return string
	 */
	public function authorization_success_html() {
		return '<div id="card-alert" class="single-merchant-area authorized">
                  <div class="single-merchant-block">
                    <span class="card-title rex-card-title">' . __( 'You Are Authorized.', 'rex-product-feed' ) . '</span>
                    <p class="rex-p">' . __( 'You are now ready to send feed from Product Feed Manager for WooCommerce to your Google Merchant Center. ', 'rex-product-feed' ) . '🚀 </p>
                  </div>              
                </div>';
	}

	/**
	 * Save google merchant API settings
	 *
	 * @param array $payload Payload data.
	 *
	 * @return string[]
	 */
	public static function save_settings( $payload ) {
		if ( isset( $payload[ 'merchant_settings' ] ) && $payload[ 'merchant_settings' ] ) {
			if ( isset( $payload[ 'client_id' ] ) ) {
				update_option( 'rex_google_client_id', $payload[ 'client_id' ] );
			}
			if ( isset( $payload[ 'client_secret' ] ) ) {
				update_option( 'rex_google_client_secret', $payload[ 'client_secret' ] );
			}
			if ( isset( $payload[ 'merchant_id' ] ) ) {
				update_option( 'rex_google_merchant_id', $payload[ 'merchant_id' ] );
			}
		}

		self::instance();
		$client_obj   = self::get_client();
		$redirect_uri = admin_url( 'admin.php?page=merchant_settings' );
		$client_obj->setClientId( self::$client_id );
		$client_obj->setClientSecret( self::$client_secret );
		$client_obj->setRedirectUri( $redirect_uri );
		$client_obj->setScopes( 'https://www.googleapis.com/auth/content' );
		$login_url = $client_obj->createAuthurl();
		$btn_html  = '<a class="btn waves-effect waves-light" href="' . $login_url . '">Authenticate</a>';
		return array(
			'html' => '<div class="col s12 merchant-action">
                    <div id="card-alert" class="card rex-card">
                        <div class="card-content">
                            <span class="card-title rex-card-title">' . __( 'You Are Not Authorized.', 'rex-product-feed' ) . ' <i class="fa fa-exclamation-triangle"></i></span>
                            <p>' . __( 'Your access token has expired. This application uses OAuth 2.0 to Access Google APIs. Please insert the information below and authenticate token for Google Merchant Shop. Generated access token expires after 3600 sec.', 'rex-product-feed' ) . '</p>
                            <p class="single-merchant-bold">' . __( 'NB: This session expiration is set by Google. You only need to authorize while submitting a new feed. You can ignore this if you\'ve already submitted your feed to Google.', 'rex-product-feed' ) . '</p>
                        </div>
                        <div class="card-action">' . $btn_html . '</div>
                    </div>
                </div>',
		);
	}

	/**
	 * Save client access token
	 *
	 * @param string $code Code.
	 */
	public function save_access_token( $code ) {
		$redirect_uri = admin_url( 'admin.php?page=merchant_settings' );
		$client_obj   = self::get_client();
		$client_obj->setClientId( self::$client_id );
		$client_obj->setClientSecret( self::$client_secret );
		$client_obj->setRedirectUri( $redirect_uri );
		$client_obj->setScopes( 'https://www.googleapis.com/auth/content' );

		if ( !$this->is_authenticate() ) {
			$client_obj->authenticate( $code );
			$access_token = $client_obj->getAccessToken();

			if ( !empty( $access_token ) && is_array( $access_token ) ) {
				update_option( 'rex_google_access_token', wp_json_encode( $access_token ) );
			}
		}
	}

	/**
	 * Check if feed already exists in merchant
	 *
	 * @param string|int $feed_id Feed id.
	 *
	 * @return bool
	 */
	public function feed_exists( $feed_id ) {
		$client_obj   = $this->init_client();
		$service      = new RexFeed\Google\Service\ShoppingContent( $client_obj );
		$data_feed_id = get_post_meta( $feed_id, '_rex_feed_google_data_feed_id', true ) ?: get_post_meta( $feed_id, 'rex_feed_google_data_feed_id', true );
		if ( $data_feed_id ) {
			try {
				$service->datafeeds->get( self::$merchant_id, $data_feed_id );
				return true;
			} catch ( Exception $e ) {
				return false;
			}
		}
		return false;
	}
}
