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
		self::$client->setAccessType( 'offline' );
		self::$client->setPrompt( 'consent' );
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

		$client_obj = $this->init_client();

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
	 * Check if feed already exists in merchant.
	 *
	 * Routes to Merchant API v1 when `_rex_feed_google_data_source_id` is set,
	 * otherwise falls back to the legacy Content API `datafeeds->get()` call.
	 *
	 * @param string|int $feed_id Feed id.
	 *
	 * @return bool
	 */
	public function feed_exists( $feed_id ) {
		$data_source_id = get_post_meta( $feed_id, '_rex_feed_google_data_source_id', true );

		if ( $data_source_id ) {
			// Merchant API path.
			$merchant_client = Rex_Feed_Merchant_API_Client::from_stored_credentials();
			if ( ! $merchant_client ) {
				return false;
			}
			try {
				$request = new \RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\GetDataSourceRequest();
				$request->setName( $data_source_id );
				$merchant_client->get_datasources_client()->getDataSource( $request );
				return true;
			} catch ( \RexFeed\Vendor\Google\ApiCore\ApiException $e ) {
				return false;
			}
		}

		// Content API fallback path.
		$data_feed_id = get_post_meta( $feed_id, '_rex_feed_google_data_feed_id', true ) ?: get_post_meta( $feed_id, 'rex_feed_google_data_feed_id', true );
		if ( $data_feed_id ) {
			$client_obj = $this->init_client();
			$service    = new RexFeed\Google\Service\ShoppingContent( $client_obj );
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
