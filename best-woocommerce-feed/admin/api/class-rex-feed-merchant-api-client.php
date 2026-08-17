<?php

use RexFeed\Vendor\Google\ApiCore\ApiException;
use RexFeed\Vendor\Google\ApiCore\CredentialsWrapper;
use RexFeed\Vendor\Google\Auth\Credentials\UserRefreshCredentials;
use RexFeed\Vendor\Google\Shopping\Merchant\DataSources\V1\Client\DataSourcesServiceClient;
use RexFeed\Vendor\Google\Shopping\Merchant\Reports\V1\Client\ReportServiceClient;

/**
 * Facade for Merchant API v1 service clients.
 *
 * Lazily initialises DataSourcesServiceClient and ReportServiceClient using
 * the plugin's stored OAuth2 credentials. All callers share one instance per
 * request via the static factory below.
 *
 * @since 7.7.0
 */
class Rex_Feed_Merchant_API_Client {

	/** @var DataSourcesServiceClient|null */
	private ?DataSourcesServiceClient $datasources_client = null;

	/** @var ReportServiceClient|null */
	private ?ReportServiceClient $reports_client = null;

	/** @var CredentialsWrapper */
	private CredentialsWrapper $credentials;

	/** @var UserRefreshCredentials */
	private UserRefreshCredentials $user_creds;

	/**
	 * @param CredentialsWrapper     $credentials Pre-built credentials wrapper.
	 * @param UserRefreshCredentials $user_creds  Raw refresh credentials (used for token fetching in REST calls).
	 */
	public function __construct( CredentialsWrapper $credentials, UserRefreshCredentials $user_creds ) {
		$this->credentials = $credentials;
		$this->user_creds  = $user_creds;
	}

	/**
	 * Build a client instance from the plugin's stored OAuth2 token.
	 *
	 * @return static|null  null when credentials are incomplete.
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

		$user_creds  = new UserRefreshCredentials(
			[ 'https://www.googleapis.com/auth/content' ],
			[
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'refresh_token' => $refresh_token,
			]
		);
		// CredentialsWrapper::build() only accepts keyFile, not a FetchAuthTokenInterface.
		// Construct directly with the UserRefreshCredentials fetcher.
		$credentials = new CredentialsWrapper( $user_creds );

		return new self( $credentials, $user_creds );
	}

	/**
	 * Return (and lazily create) the DataSources service client.
	 *
	 * @return DataSourcesServiceClient
	 */
	public function get_datasources_client(): DataSourcesServiceClient {
		if ( null === $this->datasources_client ) {
			$this->datasources_client = new DataSourcesServiceClient(
				[ 'credentials' => $this->credentials ]
			);
		}
		return $this->datasources_client;
	}

	/**
	 * Return (and lazily create) the Reports service client.
	 *
	 * @return ReportServiceClient
	 */
	public function get_reports_client(): ReportServiceClient {
		if ( null === $this->reports_client ) {
			$this->reports_client = new ReportServiceClient(
				[ 'credentials' => $this->credentials ]
			);
		}
		return $this->reports_client;
	}

	/**
	 * Fetch a fresh OAuth access token using stored refresh credentials.
	 *
	 * @return string|null
	 */
	public function get_access_token(): ?string {
		$token_data = $this->user_creds->fetchAuthToken();
		if ( ! empty( $token_data['access_token'] ) ) {
			$stored     = get_option( 'rex_google_access_token', '' );
			$stored_arr = is_array( $stored ) ? $stored : ( json_decode( $stored, true ) ?: array() );
			$merged     = array_merge( $stored_arr, $token_data );
			if ( empty( $merged['refresh_token'] ) && ! empty( $stored_arr['refresh_token'] ) ) {
				$merged['refresh_token'] = $stored_arr['refresh_token'];
			}
			update_option( 'rex_google_access_token', wp_json_encode( $merged ) );
			return $token_data['access_token'];
		}
		return $token_data['access_token'] ?? null;
	}

	/**
	 * Return the email address of the authenticated Google account.
	 *
	 * Calls the Google UserInfo endpoint with the current access token.
	 * Used for GCP developer registration which requires a Google account email.
	 *
	 * @return string  Email address, or empty string on failure.
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
	 * Called automatically when a createDataSource / updateDataSource call returns
	 * PERMISSION_DENIED with "project not registered". On success the caller should
	 * instruct the user to wait ~5 minutes before retrying.
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
		$url         = "https://merchantapi.googleapis.com/accounts/v1/accounts/{$merchant_id}/developerRegistration:registerGcp";
		$response = wp_remote_post(
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
	 * Normalise an ApiException into the plugin's standard error array.
	 *
	 * @param ApiException $e
	 * @return array{success: false, message: string, status: string, code: int}
	 */
	public static function normalize_api_error( ApiException $e ): array {
		$raw     = $e->getMessage();
		$status  = $e->getStatus();
		$message = $raw;
		$action_url = '';

		// Extract human-readable message and activation URL from JSON error body.
		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) ) {
			if ( isset( $decoded['message'] ) ) {
				$message = $decoded['message'];
			}
			// Pull activation URL — check top-level errorInfoMetadata first, then details[].metadata.
			if ( isset( $decoded['errorInfoMetadata']['activationUrl'] ) ) {
				$action_url = $decoded['errorInfoMetadata']['activationUrl'];
			} elseif ( isset( $decoded['details'] ) ) {
				foreach ( $decoded['details'] as $detail ) {
					if ( isset( $detail['metadata']['activationUrl'] ) ) {
						$action_url = $detail['metadata']['activationUrl'];
						break;
					}
				}
			}
		}

		// Classify into a known error type for targeted UI messaging.
		$error_type = 'api_error';
		if ( ! empty( $decoded['reason'] ) && 'SERVICE_DISABLED' === $decoded['reason'] ) {
			$error_type = 'service_disabled';
		} elseif ( false !== strpos( $message, 'is not registered with the merchant account' ) ) {
			$error_type = 'project_not_registered';
			// Docs link is embedded in the message — extract it as the action URL.
			if ( ! $action_url && preg_match( '/https?:\/\/\S+/', $message, $url_match ) ) {
				$action_url = rtrim( $url_match[0], '.' );
			}
		}

		return [
			'success'    => false,
			'message'    => $message,
			'status'     => $status,
			'code'       => $e->getCode(),
			'action_url' => $action_url,
			'error_type' => $error_type,
		];
	}
}
