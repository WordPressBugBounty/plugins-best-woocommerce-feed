<?php

/**
 * Lightweight REST client for Google Merchant API v1.
 *
 * Replaces the heavy Google Cloud PHP SDK with standard WordPress HTTP API
 * requests (wp_remote_*), providing 100% compatibility with PHP 7.4 through 8.4+
 * without third-party vendor dependency bloat.
 *
 * @since 7.7.0
 * @package Rex_Product_Feed/admin/api
 */
class Rex_Feed_Merchant_API_Client {

	/**
	 * @var string Google OAuth client ID.
	 */
	private $client_id;

	/**
	 * @var string Google OAuth client secret.
	 */
	private $client_secret;

	/**
	 * @var string Google OAuth refresh token.
	 */
	private $refresh_token;

	/**
	 * @var string|null Cached access token for the current request.
	 */
	private $access_token = null;

	/**
	 * Base URL for Google Merchant API.
	 */
	const API_BASE_URL = 'https://merchantapi.googleapis.com/';

	/**
	 * Constructor.
	 *
	 * @param string $client_id
	 * @param string $client_secret
	 * @param string $refresh_token
	 */
	public function __construct( $client_id, $client_secret, $refresh_token ) {
		$this->client_id     = (string) $client_id;
		$this->client_secret = (string) $client_secret;
		$this->refresh_token = (string) $refresh_token;
	}

	/**
	 * Build a client instance from the plugin's stored OAuth2 token.
	 *
	 * Compatible with all PHP versions >= 7.4.
	 *
	 * @return static|null null when credentials are incomplete.
	 */
	public static function from_stored_credentials(): ?self {
		$token_data    = get_option( 'rex_google_access_token', '' );
		$token_data    = is_array( $token_data ) ? $token_data : json_decode( $token_data, true );
		$refresh_token = $token_data['refresh_token'] ?? '';
		$client_id     = get_option( 'rex_google_client_id', '' );
		$client_secret = get_option( 'rex_google_client_secret', '' );

		if ( ! $refresh_token || ! $client_id || ! $client_secret ) {
			return null;
		}

		return new self( $client_id, $client_secret, $refresh_token );
	}

	/**
	 * Fetch a valid OAuth access token using stored credentials.
	 * Reuses valid cached token if available; otherwise refreshes via OAuth2 endpoint.
	 *
	 * @param bool $force_refresh
	 * @return string|null Access token or null on failure.
	 */
	public function get_access_token( bool $force_refresh = false ): ?string {
		if ( ! $force_refresh && ! empty( $this->access_token ) ) {
			return $this->access_token;
		}

		$stored     = get_option( 'rex_google_access_token', '' );
		$stored_arr = is_array( $stored ) ? $stored : ( json_decode( $stored, true ) ?: array() );

		// Check if stored access token is still valid (with 60s buffer).
		if ( ! $force_refresh && ! empty( $stored_arr['access_token'] ) && ! empty( $stored_arr['created'] ) ) {
			$expires_in = (int) ( $stored_arr['expires_in'] ?? 3600 );
			if ( time() < ( $stored_arr['created'] + $expires_in - 60 ) ) {
				$this->access_token = $stored_arr['access_token'];
				return $this->access_token;
			}
		}

		// Refresh token via Google OAuth2 endpoint.
		$response = wp_remote_post(
			'https://oauth2.googleapis.com/token',
			array(
				'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
				'body'    => array(
					'client_id'     => $this->client_id,
					'client_secret' => $this->client_secret,
					'refresh_token' => $this->refresh_token,
					'grant_type'    => 'refresh_token',
				),
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! empty( $data['access_token'] ) ) {
			$merged = array_merge( $stored_arr, $data );
			$merged['created'] = time();
			if ( empty( $merged['refresh_token'] ) && ! empty( $this->refresh_token ) ) {
				$merged['refresh_token'] = $this->refresh_token;
			}
			update_option( 'rex_google_access_token', wp_json_encode( $merged ) );
			$this->access_token = $data['access_token'];
			return $this->access_token;
		}

		return null;
	}

	/**
	 * Send an authenticated request to the Google Merchant REST API.
	 *
	 * @param string     $method       HTTP method (GET, POST, PATCH, DELETE).
	 * @param string     $path         API path relative to base URL (e.g. 'datasources/v1/accounts/123/dataSources').
	 * @param array      $query_params Optional query parameters.
	 * @param array|null $body         Optional request body (array will be JSON-encoded).
	 * @return array
	 */
	public function request( string $method, string $path, array $query_params = array(), $body = null ): array {
		$access_token = $this->get_access_token();
		if ( ! $access_token ) {
			return array(
				'success'    => false,
				'message'    => __( 'Could not obtain a valid Google access token. Please re-authenticate your Google Merchant account.', 'rex-product-feed' ),
				'error_type' => 'auth_error',
			);
		}

		$url = self::API_BASE_URL . ltrim( $path, '/' );
		if ( ! empty( $query_params ) ) {
			$url = add_query_arg( $query_params, $url );
		}

		$args = array(
			'method'  => strtoupper( $method ),
			'headers' => array(
				'Authorization' => "Bearer {$access_token}",
				'Content-Type'  => 'application/json',
			),
			'timeout' => 30,
		);

		if ( null !== $body ) {
			$args['body'] = is_string( $body ) ? $body : wp_json_encode( $body );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'success'    => false,
				'message'    => $response->get_error_message(),
				'error_type' => 'network_error',
			);
		}

		$status_code   = (int) wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data          = json_decode( $response_body, true );

		if ( $status_code >= 200 && $status_code < 300 ) {
			return array(
				'success' => true,
				'data'    => $data ?: array(),
			);
		}

		return self::normalize_api_error( array(
			'code'          => $status_code,
			'response_body' => $data,
			'raw_body'      => $response_body,
		) );
	}

	/**
	 * Create a DataSource via Google Merchant API v1.
	 *
	 * @param string $merchant_id
	 * @param array  $data_source
	 * @return array
	 */
	public function create_data_source( string $merchant_id, array $data_source ): array {
		return $this->request(
			'POST',
			"datasources/v1/accounts/{$merchant_id}/dataSources",
			array(),
			$data_source
		);
	}

	/**
	 * Get a DataSource by full resource name (accounts/{account}/dataSources/{datasource}) or numeric ID.
	 *
	 * @param string $name Full resource name or DataSource ID.
	 * @return array
	 */
	public function get_data_source( string $name ): array {
		if ( false === strpos( $name, 'accounts/' ) ) {
			$merchant_id = get_option( 'rex_google_merchant_id', '' );
			$name        = "accounts/{$merchant_id}/dataSources/{$name}";
		}
		return $this->request(
			'GET',
			"datasources/v1/{$name}"
		);
	}

	/**
	 * Update an existing DataSource.
	 *
	 * @param string $name Full resource name or DataSource ID.
	 * @param array  $data_source
	 * @param string $update_mask Comma-separated fields to update.
	 * @return array
	 */
	public function update_data_source( string $name, array $data_source, string $update_mask = 'display_name,primary_product_data_source' ): array {
		if ( false === strpos( $name, 'accounts/' ) ) {
			$merchant_id = get_option( 'rex_google_merchant_id', '' );
			$name        = "accounts/{$merchant_id}/dataSources/{$name}";
		}
		return $this->request(
			'PATCH',
			"datasources/v1/{$name}",
			array( 'updateMask' => $update_mask ),
			$data_source
		);
	}

	/**
	 * Delete an existing DataSource.
	 *
	 * @param string $name Full resource name or DataSource ID.
	 * @return array
	 */
	public function delete_data_source( string $name ): array {
		if ( false === strpos( $name, 'accounts/' ) ) {
			$merchant_id = get_option( 'rex_google_merchant_id', '' );
			$name        = "accounts/{$merchant_id}/dataSources/{$name}";
		}
		return $this->request(
			'DELETE',
			"datasources/v1/{$name}"
		);
	}

	/**
	 * Trigger an immediate DataSource fetch.
	 *
	 * @param string $name Full resource name or DataSource ID.
	 * @return array
	 */
	public function fetch_data_source( string $name ): array {
		if ( false === strpos( $name, 'accounts/' ) ) {
			$merchant_id = get_option( 'rex_google_merchant_id', '' );
			$name        = "accounts/{$merchant_id}/dataSources/{$name}";
		}
		return $this->request(
			'POST',
			"datasources/v1/{$name}:fetch",
			array(),
			new \stdClass()
		);
	}

	/**
	 * Search Reports via Google Merchant API v1 Reports API.
	 *
	 * @param string      $parent     Resource name of the parent account (accounts/{account}).
	 * @param string      $query      Merchant query language (MQL) string.
	 * @param int         $page_size  Number of rows to return per page.
	 * @param string|null $page_token Page token.
	 * @return array
	 */
	public function search_reports( string $parent, string $query, int $page_size = 10, ?string $page_token = null ): array {
		$body = array(
			'query'    => $query,
			'pageSize' => $page_size,
		);
		if ( ! empty( $page_token ) ) {
			$body['pageToken'] = $page_token;
		}

		return $this->request(
			'POST',
			"reports/v1/{$parent}/reports:search",
			array(),
			$body
		);
	}

	/**
	 * Return the email address of the authenticated Google account.
	 *
	 * @return string Email address, or empty string on failure.
	 */
	public function get_google_email(): string {
		$access_token = $this->get_access_token();
		if ( ! $access_token ) {
			return '';
		}

		$response = wp_remote_get(
			'https://www.googleapis.com/oauth2/v3/userinfo',
			array(
				'headers' => array( 'Authorization' => "Bearer {$access_token}" ),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return $data['email'] ?? '';
	}

	/**
	 * Register this plugin's GCP project with the given Merchant Center account.
	 *
	 * @param string $merchant_id    Numeric merchant account ID.
	 * @param string $developer_email Email to associate with the registration.
	 * @return array{success: bool, message?: string, error_type?: string}
	 */
	public function register_gcp( string $merchant_id, string $developer_email ): array {
		$access_token = $this->get_access_token() ?: '';

		if ( ! $access_token ) {
			return array(
				'success'    => false,
				'message'    => __( 'Could not obtain access token for GCP registration.', 'rex-product-feed' ),
				'error_type' => 'registration_failed',
			);
		}

		$merchant_id = absint( $merchant_id );
		$url         = self::API_BASE_URL . "accounts/v1/accounts/{$merchant_id}/developerRegistration:registerGcp";
		$response    = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Authorization' => "Bearer {$access_token}",
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( array( 'developerEmail' => $developer_email ) ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success'    => false,
				'message'    => $response->get_error_message(),
				'error_type' => 'registration_failed',
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code >= 200 && $code < 300 ) {
			return array( 'success' => true );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		$msg  = $data['error']['message'] ?? sprintf( 'GCP registration failed (HTTP %d).', $code );
		return array(
			'success'    => false,
			'message'    => $msg,
			'error_type' => 'registration_failed',
		);
	}

	/**
	 * Normalise an API error array or exception into standard error format.
	 *
	 * @param mixed $error
	 * @return array{success: false, message: string, status: string, code: int, action_url: string, error_type: string}
	 */
	public static function normalize_api_error( $error ): array {
		if ( is_array( $error ) && isset( $error['success'] ) && false === $error['success'] && isset( $error['error_type'] ) ) {
			return $error;
		}

		$code        = is_array( $error ) ? ( $error['code'] ?? 0 ) : ( is_object( $error ) && method_exists( $error, 'getCode' ) ? $error->getCode() : 0 );
		$decoded     = is_array( $error ) ? ( $error['response_body'] ?? array() ) : array();
		$raw_message = $decoded['error']['message'] ?? ( is_string( $error ) ? $error : ( is_object( $error ) && method_exists( $error, 'getMessage' ) ? $error->getMessage() : '' ) );
		$status      = $decoded['error']['status'] ?? ( is_object( $error ) && method_exists( $error, 'getStatus' ) ? $error->getStatus() : '' );
		$message     = $raw_message ?: ( is_array( $error ) && ! empty( $error['raw_body'] ) ? $error['raw_body'] : 'Unknown Google API error' );
		$action_url  = '';

		if ( isset( $decoded['error']['errorInfoMetadata']['activationUrl'] ) ) {
			$action_url = $decoded['error']['errorInfoMetadata']['activationUrl'];
		} elseif ( isset( $decoded['error']['details'] ) && is_array( $decoded['error']['details'] ) ) {
			foreach ( $decoded['error']['details'] as $detail ) {
				if ( isset( $detail['metadata']['activationUrl'] ) ) {
					$action_url = $detail['metadata']['activationUrl'];
					break;
				}
			}
		}

		$error_type = 'api_error';
		$reason     = $decoded['error']['reason'] ?? '';
		if ( 'SERVICE_DISABLED' === $reason ) {
			$error_type = 'service_disabled';
		} elseif ( false !== strpos( $message, 'is not registered with the merchant account' ) || false !== strpos( $message, 'project not registered' ) ) {
			$error_type = 'project_not_registered';
			if ( ! $action_url && preg_match( '/https?:\/\/\S+/', $message, $url_match ) ) {
				$action_url = rtrim( $url_match[0], '.' );
			}
		} elseif ( false !== strpos( $status, 'NOT_FOUND' ) || 404 === $code ) {
			$error_type = 'not_found';
		}

		return array(
			'success'    => false,
			'message'    => $message,
			'status'     => (string) $status,
			'code'       => (int) $code,
			'action_url' => $action_url,
			'error_type' => $error_type,
		);
	}
}
