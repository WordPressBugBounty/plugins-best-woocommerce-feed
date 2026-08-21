<?php

use RexFeed\Google\Client;
use RexFeed\Google\Service\ShoppingContent;

/**
 * Class Rex_Feed_Google_Shopping_Api
 *
 * This class handles the interaction with the Google Shopping API for the WooCommerce feed plugin.
 * It includes methods for initializing the Google API client, retrieving and saving credentials,
 * and managing access tokens.
 */
class Rex_Feed_Google_Shopping_Api {

	/**
	 * Get client object.
	 *
	 * This method initializes and configures a Google API client object.
	 * It sets the client ID, client secret, redirect URI, and scopes required for OAuth authentication.
	 *
	 * @return Client The configured Google API client object.
	 *
	 * @since 7.4.20
	 */
	public function get_client(): Client {
		$client = new Client();
		$client->setClientId( $this->get_client_id() );
		$client->setClientSecret( $this->get_client_secret() );
		$client->setRedirectUri( $this->get_redirect_url() );
		$client->setScopes( $this->get_scopes() );
		$client->setAccessType('offline');
		$client->setPrompt('consent');

		$access_token = $this->get_access_token();
		if ( ! empty( $access_token ) ) {
			$client->setAccessToken( $access_token );
		}

		return $client;
	}

	/**
	 * Update client ID.
	 *
	 * This method updates the Google app client ID in the WordPress options table.
	 *
	 * @param string $client_id The new client ID.
	 *
	 * @since 7.4.20
	 */
	public function update_client_id( string $client_id ) {
		update_option( 'rex_google_client_id', $client_id );
	}

	/**
	 * Update client secret.
	 *
	 * This method updates the Google app client secret in the WordPress options table.
	 *
	 * @param string $client_secret The new client secret.
	 *
	 * @since 7.4.20
	 */
	public function update_client_secrete( string $client_secret ) {
		update_option( 'rex_google_client_secret', $client_secret );
	}

	/**
	 * Update merchant ID.
	 *
	 * This method updates the Google Merchant ID in the WordPress options table.
	 *
	 * @param string $merchant_id The new merchant ID.
	 *
	 * @since 7.4.20
	 */
	public function update_merchant_id( $merchant_id ) {
		update_option( 'rex_google_merchant_id', $merchant_id );
	}

	/**
	 * Get google app client ID.
	 *
	 * This method retrieves the Google app client ID from the WordPress options table.
	 * If the option is not set, it returns an empty string.
	 *
	 * @return string The Google app client ID or an empty string if not set.
	 *
	 * @since 7.4.20
	 */
	public function get_client_id() {
		return get_option( 'rex_google_client_id', '' );
	}

	/**
	 * Get google app client secret.
	 *
	 * This method retrieves the Google app client secret from the WordPress options table.
	 * If the option is not set, it returns an empty string.
	 *
	 * @return string The Google app client secret or an empty string if not set.
	 *
	 * @since 7.4.20
	 */
	public function get_client_secret() {
		return get_option( 'rex_google_client_secret', '' );
	}

	/**
	 * Get google merchant id.
	 *
	 * This method retrieves the Google Merchant ID from the WordPress options table.
	 * If the option is not set, it returns an empty string.
	 *
	 * @return string The Google Merchant ID or an empty string if not set.
	 *
	 * @since 7.4.20
	 */
	public function get_merchant_id() {
		return get_option( 'rex_google_merchant_id', '' );
	}

	/**
	 * Get the redirect URL for the Google Merchant settings page.
	 *
	 * This method constructs and sanitizes the URL for the Google Merchant settings page
	 * in the WordPress admin area. The URL is used as the redirect URI for OAuth authentication.
	 *
	 * @return string|null The sanitized URL for the Google Merchant settings page, or null if invalid.
	 *
	 * @since 7.4.20
	 */
	public function get_redirect_url(): ?string {
		return sanitize_url( admin_url( 'admin.php?page=merchant_settings' ) );
	}

	/**
	 * Get scopes for Google API.
	 *
	 * This method returns an array of scopes required for accessing the Google Content API.
	 *
	 * @return array The array of scopes.
	 *
	 * @since 7.4.20
	 */
	public function get_scopes(): array {
		return [
			'https://www.googleapis.com/auth/content',
		];
	}

	/**
	 * Get access token.
	 *
	 * This method retrieves the Google API access token from the WordPress options table.
	 * The token is stored as a JSON-encoded string and is decoded before being returned.
	 *
	 * @return array|null The access token array or null if not set.
	 *
	 * @since 7.4.20
	 */
	public function get_access_token() {
		$token_data = get_option( 'rex_google_access_token', '' );
		return is_array( $token_data ) ? $token_data : json_decode( $token_data, true );
	}

	/**
	 * Save access token.
	 *
	 * This method saves the Google API access token retrieved using the provided authorization code.
	 * It uses the specified fetch function to get the access token and updates the WordPress options table.
	 *
	 * @param string $code The authorization code.
	 * @param string $fetch_function The function to fetch the access token.
	 *
	 * @since 7.4.20
	 */
	public function save_access_token( $code, $fetch_function ) {
		try {
			$access_token = $this->get_client()->$fetch_function( $code );
		} catch ( Exception $e ) {
			$log = wc_get_logger();
			$log->info( sprintf( __( 'Error fetching access token: %s', 'rex-product-feed' ), $e->getMessage() ), [ 'source' => 'WPFMGmcAuthorization' ] );
		}

		if ( empty( $access_token[ 'error' ] ) && ! empty( $access_token[ 'access_token' ] ) ) {
			if ( empty( $access_token[ 'refresh_token' ] ) ) {
				$token_data = $this->get_access_token();
				if ( ! empty( $token_data[ 'refresh_token' ] ) ) {
					$access_token[ 'refresh_token' ] = $token_data[ 'refresh_token' ];
				}
			}
			update_option( 'rex_google_access_token', wp_json_encode( $access_token ) );
		}
	}

	/**
	 * Fetch access token.
	 *
	 * This method fetches the Google API access token using the provided authorization code.
	 * If the authorization code is not provided, it attempts to fetch the access token using the refresh token.
	 *
	 * @param string|null $auth_code The authorization code.
	 *
	 * @since 7.4.20
	 */
	public function fetch_access_token( $auth_code = null ) {
		if ( ! empty( $auth_code ) ) {
			// Always exchange a fresh auth code — even if the current token is still valid.
			// This ensures re-authentication always stores the new refresh_token.
			$this->save_access_token( sanitize_text_field( $auth_code ), 'fetchAccessTokenWithAuthCode' );
		} elseif ( !$this->is_authorized() ) {
			$token_data = $this->get_access_token();
			if ( ! empty( $token_data['refresh_token'] ) ) {
				$this->save_access_token( $token_data['refresh_token'], 'fetchAccessTokenWithRefreshToken' );
			}
		}
	}

	/**
	 * Check if the client is authorized.
	 *
	 * This method checks if the Google API client has a valid access token.
	 * It returns `true` if the access token is not expired, indicating that the client is authorized.
	 *
	 * @return bool `true` if the client is authorized, `false` otherwise.
	 *
	 * @since 7.4.20
	 */
	public function is_authorized() {
		// Check if we have basic credentials first
		$client_id = $this->get_client_id();
		$client_secret = $this->get_client_secret();
		$merchant_id = $this->get_merchant_id();
		
		if ( empty( $client_id ) || empty( $client_secret ) || empty( $merchant_id ) ) {
			return false;
		}
		
		// Check if we have an access token
		$access_token = $this->get_access_token();
		if ( empty( $access_token ) ) {
			return false;
		}
		
		try {
			$client = $this->get_client();
			if ( ! $client->isAccessTokenExpired() ) {
				return true;
			}

			// If access token is expired, attempt to refresh it using the refresh token.
			if ( ! empty( $access_token['refresh_token'] ) ) {
				$this->save_access_token( $access_token['refresh_token'], 'fetchAccessTokenWithRefreshToken' );
				$refreshed_token = $this->get_access_token();
				if ( ! empty( $refreshed_token ) && empty( $refreshed_token['error'] ) ) {
					$client = $this->get_client();
					return ! $client->isAccessTokenExpired();
				}
			}

			return false;
		} catch ( Exception $e ) {
			// If there's an error checking the token, consider it unauthorized
			$log = wc_get_logger();
			$log->info( sprintf( __( 'Authorization check failed: %s', 'rex-product-feed' ), $e->getMessage() ), [ 'source' => 'WPFMGmcAuthorization' ] );
			return false;
		}
	}

	/**
	 * Check if credentials were ever successfully authorized.
	 *
	 * This method checks if the user has ever been successfully authorized by verifying
	 * if an access token has been stored. This helps distinguish between first-time
	 * invalid credentials and expired tokens from previously valid credentials.
	 *
	 * @return bool `true` if previously authorized at some point, `false` otherwise.
	 *
	 * @since 7.4.21
	 */
	public function was_previously_authorized() {
		$token_data = $this->get_access_token();
		return !empty( $token_data ) && ( !empty( $token_data['access_token'] ) || !empty( $token_data['refresh_token'] ) );
	}

	/**
	 * Get authorization URL.
	 *
	 * This method retrieves the authorization URL for the Google API.
	 * It uses the Google API client to create the URL with the required scopes.
	 * Returns empty string if credentials are invalid or missing.
	 *
	 * @return string The authorization URL or empty string on error.
	 *
	 * @since 7.4.20
	 */
	public function get_auth_url() {
		try {
			// Validate that required credentials are present
			if ( empty( $this->get_client_id() ) || empty( $this->get_client_secret() ) ) {
				return '';
			}
			return $this->get_client()->createAuthUrl();
		} catch ( Exception $e ) {
			$log = wc_get_logger();
			$log->error( 'Failed to create auth URL: ' . $e->getMessage(), [ 'source' => 'WPFMGmcAuthorization' ] );
			return '';
		}
	}

	/**
	 * Validate authorization.
	 *
	 * This method checks if the client is authorized and updates the access token if necessary.
	 * If the client is not authorized, it retrieves the access token using the refresh token.
	 *
	 * @since 7.4.20
	 */
	public function validate_auth() {
		$this->fetch_access_token();
		return $this->is_authorized();
	}

	/**
	 * Check if any Google feed has been migrated to Merchant API v1.
	 *
	 * @return bool
	 */
	private function is_merchant_api_active(): bool {
		$migrated = get_posts( array(
			'post_type'      => 'product-feed',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_rex_feed_merchant',
					'value'   => 'google',
					'compare' => '=',
				),
				array(
					'key'     => '_rex_feed_google_data_source_id',
					'compare' => 'EXISTS',
				),
			),
		) );
		return ! empty( $migrated );
	}

	/**
	 * Get product status summary via Merchant API v1 Reports API.
	 *
	 * Returns the same array shape as get_product_stats_summery() so the display
	 * layer needs no changes.
	 *
	 * @return array
	 */
	public function get_product_stats_summery_merchant_api(): array {
		$data = array(
			'total'       => 0,
			'active'      => array( 'count' => 0, 'rate' => '0%' ),
			'expiring'    => array( 'count' => 0, 'rate' => '0%' ),
			'pending'     => array( 'count' => 0, 'rate' => '0%' ),
			'disapproved' => array( 'count' => 0, 'rate' => '0%' ),
		);

		$merchant_id     = $this->get_merchant_id();
		$merchant_client = Rex_Feed_Merchant_API_Client::from_stored_credentials();
		if ( ! $merchant_id || ! $merchant_client ) {
			return $data;
		}

		try {
			$request = new \RexFeed\Vendor\Google\Shopping\Merchant\Reports\V1\SearchRequest();
			$request->setParent( "accounts/{$merchant_id}" );
			$request->setQuery( 'SELECT aggregated_reporting_context_status FROM product_view' );

			$response = $merchant_client->get_reports_client()->search( $request );

			$counts = array(
				'ELIGIBLE'            => 0,
				'ELIGIBLE_LIMITED'    => 0,
				'DISAPPROVED'         => 0,
				'PENDING'             => 0,
			);

			foreach ( $response->iterateAllElements() as $row ) {
				$pv     = $row->getProductView();
				$status = $pv ? $pv->getAggregatedReportingContextStatus() : null;
				if ( null !== $status ) {
					$name = \RexFeed\Vendor\Google\Shopping\Merchant\Reports\V1\ProductView\AggregatedReportingContextStatus::name( $status );
					if ( isset( $counts[ $name ] ) ) {
						$counts[ $name ]++;
					}
				}
			}

			$active      = $counts['ELIGIBLE'] + $counts['ELIGIBLE_LIMITED'];
			$disapproved = $counts['DISAPPROVED'];
			$pending     = $counts['PENDING'];
			$total       = $active + $disapproved + $pending;

			if ( $total > 0 ) {
				$data['total']                  = $total;
				$data['active']['count']        = $active;
				$data['active']['rate']         = round( ( $active / $total ) * 100 ) . '%';
				$data['disapproved']['count']   = $disapproved;
				$data['disapproved']['rate']    = round( ( $disapproved / $total ) * 100 ) . '%';
				$data['pending']['count']       = $pending;
				$data['pending']['rate']        = round( ( $pending / $total ) * 100 ) . '%';
			}
		} catch ( \RexFeed\Vendor\Google\ApiCore\ApiException $e ) {
			if ( is_wpfm_logging_enabled() ) {
				$log = wc_get_logger();
				$log->error(
					sprintf( '[Merchant API] get_product_stats_summery_merchant_api: %s', $e->getMessage() ),
					array( 'source' => 'WPFM-google-merchant-api' )
				);
			}
		}

		return $data;
	}

	/**
	 * Get detailed product status via Merchant API v1 Reports API.
	 *
	 * Returns the same array shape as get_product_detailed_stats() so the display
	 * layer needs no changes.
	 *
	 * @param string|null $page_token  Pagination token (not used — Reports API uses iterateAllElements).
	 * @param int         $max_results Limit on rows returned.
	 *
	 * @return array
	 */
	public function get_product_detailed_stats_merchant_api( $page_token = null, int $max_results = 10 ): array {
		$merchant_id     = $this->get_merchant_id();
		$merchant_client = Rex_Feed_Merchant_API_Client::from_stored_credentials();

		if ( ! $merchant_id ) {
			return array(
				'error'   => true,
				'message' => __( 'Invalid Merchant ID. Please check your Google Merchant Center settings.', 'rex-product-feed' ),
				'products' => array(),
			);
		}
		if ( ! $merchant_client ) {
			return array(
				'error'   => true,
				'message' => __( 'Authorization required. Please re-authorize your Google Merchant Center account.', 'rex-product-feed' ),
				'products' => array(),
			);
		}

		try {
			$request = new \RexFeed\Vendor\Google\Shopping\Merchant\Reports\V1\SearchRequest();
			$request->setParent( "accounts/{$merchant_id}" );
			$request->setQuery(
				'SELECT offer_id, id, title, item_issues FROM product_view WHERE item_issues IS NOT NULL'
			);
			$request->setPageSize( $max_results );

			$response = $merchant_client->get_reports_client()->search( $request );
			$products = array();
			$count    = 0;

			foreach ( $response->iterateAllElements() as $row ) {
				if ( $count >= $max_results ) {
					break;
				}
				$pv = $row->getProductView();
				if ( ! $pv ) {
					continue;
				}

				$item_issues = array();
				foreach ( $pv->getItemIssues() as $issue ) {
					$item_issues[] = array(
						'attribute'     => $issue->getItemIssueType() ? $issue->getItemIssueType()->getAttribute() : '',
						'description'   => $issue->getItemIssueType() ? $issue->getItemIssueType()->getDescription() : '',
						'detail'        => '',
						'documentation' => '',
						'status'        => $issue->getItemIssueSeverity() ? 'disapproved' : '',
					);
				}

				$raw_offer_id = $pv->getOfferId();
				$parent_id    = function_exists( 'wpfm_get_wc_parent_product' ) ? wpfm_get_wc_parent_product( $raw_offer_id ) : $raw_offer_id;
				$product_id   = ! empty( $parent_id ) ? $parent_id : $raw_offer_id;

				$products[] = array(
					'title'     => $pv->getTitle() ?? '',
					'edit_link' => get_edit_post_link( $product_id ),
					'issues'    => $item_issues,
				);

				$count++;
			}

			return array(
				'error'           => false,
				'prev_page_token' => null,
				'next_page_token' => null,
				'products'        => $products,
			);

		} catch ( \RexFeed\Vendor\Google\ApiCore\ApiException $e ) {
			if ( is_wpfm_logging_enabled() ) {
				$log = wc_get_logger();
				$log->error(
					sprintf( '[Merchant API] get_product_detailed_stats_merchant_api: %s', $e->getMessage() ),
					array( 'source' => 'WPFM-google-merchant-api' )
				);
			}
			return array(
				'error'           => true,
				'message'         => __( 'Failed to fetch product data from Google Merchant Center via Merchant API v1.', 'rex-product-feed' ),
				'technical_error' => $e->getMessage(),
				'products'        => array(),
			);
		}
	}

	/**
	 * Get products stats.
	 *
	 * Routes to Merchant API v1 when any Google feed has been migrated;
	 * otherwise falls back to the legacy Content API accountstatuses call.
	 *
	 * @return array The array of product statistics.
	 *
	 * @since 1.0.0
	 */
	public function get_product_stats_summery() {
		// Route to Merchant API when account has migrated feeds.
		if ( $this->is_merchant_api_active() ) {
			return $this->get_product_stats_summery_merchant_api();
		}

        $data = [
	        'total'       => 0,
	        'active'      => [ 'count' => 0, 'rate' => '0%' ],
	        'expiring'    => [ 'count' => 0, 'rate' => '0%' ],
	        'pending'     => [ 'count' => 0, 'rate' => '0%' ],
	        'disapproved' => [ 'count' => 0, 'rate' => '0%' ],
        ];
		$merchant_id = $this->get_merchant_id();
		if ( ! empty( $merchant_id ) && $this->validate_auth() ) {
			$client           = $this->get_client();
			$service          = new ShoppingContent( $client );
			$account_statuses = $service->accountstatuses->get( $merchant_id, $merchant_id );

			$products = $account_statuses->getProducts();

			if ( ! empty( $products[ 0 ] ) ) {
				$stats       = $products[ 0 ]->getStatistics();
				$active      = (int) $stats->getActive();
				$expiring    = (int) $stats->getExpiring();
				$pending     = (int) $stats->getPending();
				$disapproved = (int) $stats->getDisapproved();
				$total       = ( $active + $expiring + $pending + $disapproved );

				if ( $total > 0 ) {
					$data['total'] = $total;
					foreach (['active', 'expiring', 'pending', 'disapproved'] as $status) {
						$data[$status]['count'] = $$status;
						$data[$status]['rate'] = ( $$status * 100 ) / $total . '%';
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Get detailed product stats.
	 *
	 * This method retrieves the detailed statistics for the products submitted to Google Merchant.
	 * It uses the Google Shopping API to get the product status and issues.
	 *
	 * @param string|null $page_token The page token for pagination.
	 * @param int $max_results The maximum number of results to retrieve.
	 *
	 * @return array The array of detailed product statistics.
	 *
	 * @since 1.0.0
	 */
	public function get_product_detailed_stats( $page_token = null, $max_results = 10 ) {
		// Route to Merchant API when account has migrated feeds.
		if ( $this->is_merchant_api_active() ) {
			return $this->get_product_detailed_stats_merchant_api( $page_token, (int) $max_results );
		}

		$merchant_id = $this->get_merchant_id();

		// Check if we have valid credentials and merchant ID
		if ( empty( $merchant_id ) || ! is_numeric( $merchant_id ) ) {
			return [
				'error' => true,
				'message' => __( 'Invalid Merchant ID. Please check your Google Merchant Center settings.', 'rex-product-feed' ),
				'products' => [],
			];
		}
		
		// Validate authorization before attempting to fetch data
		if ( ! $this->validate_auth() ) {
			return [
				'error' => true,
				'message' => __( 'Authorization required. Please re-authorize your Google Merchant Center account.', 'rex-product-feed' ),
				'products' => [],
			];
		}
		
		try {
			$client   = $this->get_client();
			$products = [];
			$service  = new ShoppingContent( $client );

			if ( empty( $page_token ) && ! is_null( $page_token ) ) {
				$page_token = null;
			}

			try {
				$products_statuses = $service->productstatuses->listProductstatuses( $merchant_id, [
					'pageToken'  => $page_token,
					'maxResults' => $max_results,
				] );
			} catch ( Exception $e ) {
				$log = wc_get_logger();
				$log->info( sprintf( __( 'Error fetching product statuses: %s', 'rex-product-feed' ), $e->getMessage() ), [ 'source' => 'WPFMGmcDiagnosticsReport' ] );
				
				// Return user-friendly error instead of fatal error
				return [
					'error' => true,
					'message' => __( 'Failed to fetch product data from Google Merchant Center. Please verify your authorization and try again.', 'rex-product-feed' ),
					'technical_error' => $e->getMessage(),
					'products' => [],
				];
			}

			if ( !empty( $products_statuses ) && is_object( $products_statuses ) && method_exists( $products_statuses, 'getResources' ) ) {
				$resources = $products_statuses->getResources();
				if ( !empty( $resources ) ) {
					foreach ( $resources as $product_status ) {
						$item_issues = [];
						if ( !empty( $product_status ) && is_object( $product_status ) && method_exists( $product_status, 'getItemLevelIssues' ) ) {
							$item_level_issues = $product_status->getItemLevelIssues();
							if ( !empty( $item_level_issues ) ) {
								foreach ( $item_level_issues as $issue ) {
									$attribute_name = $issue->getAttributeName();
									if ( ! empty( $attribute_name ) ) {
										$item_issues[] = [
											'attribute'     => str_replace( ' ', '_', $attribute_name ),
											'description'   => $issue->getDescription(),
											'detail'        => $issue->getDetail(),
											'documentation' => $issue->getDocumentation(),
											'status'        => $issue->getServability(),
										];
									}
								}
							}
						}

						$product_id = $product_status->getProductId();
						$product_id = explode( ':', $product_id );
						if ( ! empty( $product_id[ 3 ] ) ) {
							$parent_id  = function_exists( 'wpfm_get_wc_parent_product' ) ? wpfm_get_wc_parent_product( $product_id[ 3 ] ) : $product_id[ 3 ];
							$product_id = ! empty( $parent_id ) ? $parent_id : $product_id[ 3 ];
							$products[] = [
								'title'     => $product_status->getTitle(),
								'edit_link' => get_edit_post_link( $product_id ),
								'issues'    => $item_issues,
							];
						}
					}
				}
			}

			return [
				'error' => false,
				'prev_page_token' => $page_token,
				'next_page_token' => $products_statuses->nextPageToken ?? null,
				'products'        => $products,
			];
		} catch ( Exception $e ) {
			$log = wc_get_logger();
			$log->error( sprintf( __( 'Fatal error in get_product_detailed_stats: %s', 'rex-product-feed' ), $e->getMessage() ), [ 'source' => 'WPFMGmcDiagnosticsReport' ] );
			
			return [
				'error' => true,
				'message' => __( 'An unexpected error occurred while fetching the report. Please check your credentials and authorization.', 'rex-product-feed' ),
				'technical_error' => $e->getMessage(),
				'products' => [],
			];
		}
	}

	/**
	 * Build product status table data.
	 *
	 * This method generates the HTML markup for the product status table in the Google Merchant diagnostics report.
	 * It uses the product status data retrieved from the Google Shopping API to create the table rows.
	 *
	 * @param array $product_statuses The array of product status data.
	 * @param int $feed_id The ID of the feed.
	 *
	 * @return string The HTML markup for the product status table.
	 *
	 * @since 1.0.0
	 */
	public function build_product_status_table_data( $product_statuses, $feed_id ) {
		if ( ! empty( $product_statuses[ 'products' ] ) && is_array( $product_statuses[ 'products' ] ) && ! empty( $feed_id ) ) {
			$product_icon = '../assets/icon/icon-svg/product.php';
			$error_icon   = '../assets/icon/icon-svg/error.php';
			ob_start();
			foreach ( $product_statuses[ 'products' ] as $product ) {
				if ( ! empty( $product[ 'title' ] ) && !empty( $product[ 'edit_link' ] ) && ! empty( $product[ 'issues' ][ 0 ][ 'description' ] ) ) {
					include plugin_dir_path( __FILE__ ) . '../partials/rex-feed-gmc-report-table-row-data.php';
				}
			}
			?>
			<script type="application/javascript">
                (function( $ ) {
                    'use strict';
                    //google diagnostics report popup box accordion.
                    $(".rex-feed-gmc-diagnostics-report-popup__accordion-content-wrapper").hide();
                    // Show the content of the first accordion item.
                    $(".rex-feed-gmc-diagnostics-report-popup__accordion-list:first-child .rex-feed-gmc-diagnostics-report-popup__accordion-content-wrapper").show();
                    // When an accordion header is clicked.
                    $(".rex-feed-gmc-diagnostics-report-popup__accordion-header").click(function(){
                        // Toggle the associated content.
                        $(this).next(".rex-feed-gmc-diagnostics-report-popup__accordion-content-wrapper").slideToggle();
                        $(this).find(".rex-accordion__arrow").toggleClass("rotated");
                        // Collapse other content.
                        $(".rex-feed-gmc-diagnostics-report-popup__accordion-content-wrapper").not($(this).next()).slideUp();

                        // Reset the arrow rotation for other headers.
                        $(".rex-accordion__arrow").not($(this).find(".rex-accordion__arrow")).removeClass("rotated");
                    });
                })( jQuery );
			</script>
			<?php
			$markups = ob_get_contents();
			ob_end_clean();
		}

		return $markups ?? '';
	}

	/**
	 * Generates the HTML markup for the access token expiration message.
	 *
	 * This method creates and returns the HTML content to display a message
	 * indicating that the user's access token has expired and needs to be re-authenticated.
	 * It provides a link to authenticate the token for Google Merchant Shop.
	 *
	 * @return string The HTML markup for the access token expiration message.
	 *
	 * @since 7.4.20
	 */
	public function get_access_token_html() {
		$was_previously_authorized = $this->was_previously_authorized();
		ob_start();
		?>
        <div class="single-merchant-area authorized">
            <div class="single-merchant-block">
                <header>
					<?php if ( $was_previously_authorized ) : ?>
                        <h2 class="title"><?php esc_html_e( "You Are Not Authorized", "rex-product-feed" ); ?></h2>
					<?php else : ?>
                        <h2 class="title"><?php esc_html_e( "Invalid Credentials", "rex-product-feed" ); ?></h2>
					<?php endif; ?>
                    <img src="<?php echo WPFM_PLUGIN_ASSETS_FOLDER . "/icon/danger.png"; ?>" class="title-icon" alt="bwf-documentation">
                </header>
                <div class="body">
					<?php if ( $was_previously_authorized ) : ?>
                        <p><?php esc_html_e( 'Your access token has expired. Re-authenticate to restore the connection.', 'rex-product-feed' ); ?></p>
                        <p class="single-merchant-bold"><?php esc_html_e( 'Your Client ID, Client Secret, and Merchant ID are unchanged — re-authenticating connects to Google Merchant API v1 automatically. No credential changes required.', 'rex-product-feed' ); ?></p>
					<?php else : ?>
                        <p><?php esc_html_e( 'Invalid credentials. Please check your Client ID, Client Secret, or Merchant ID and try again. Make sure you have entered the correct values from your Google Cloud Console and Google Merchant Center.', 'rex-product-feed' ); ?></p>
                        <p class="single-merchant-bold"><?php esc_html_e( 'Tip: Verify that your Client ID and Client Secret match those in your Google Cloud Console OAuth 2.0 credentials, and that your Merchant ID is correct.', 'rex-product-feed' ); ?></p>
					<?php endif; ?>
                    <a class="btn-default" href="<?php echo esc_url( $this->get_auth_url() ); ?>" target="_blank"><?php esc_html_e( 'Authenticate', 'rex-product-feed' ); ?></a>
                </div>
            </div>
        </div>
		<?php
        $content = ob_get_contents();
		ob_get_clean();
		return $content;
	}

	/**
	 * Generates the HTML markup for new user authentication.
	 *
	 * This method creates and returns the HTML content to display a message
	 * prompting new users to authorize with Google Merchant Center (GMC) to send a new feed.
	 * It provides links to documentation for both direct upload and API methods.
	 *
	 * @return string The HTML markup for new user authentication.
	 *
	 * @since 7.4.20
	 */
	public function get_new_user_authenticate_markups() {
		ob_start();
		?>
        <div class="single-merchant-area authorized">
            <div class="single-merchant-block">
                <header>
                    <h2 class="title">
						<?php esc_html_e( 'Set up Google Merchant API v1', 'rex-product-feed' ); ?>
                    </h2>
                </header>
                <div class="body">
                    <p>
						<?php esc_html_e( 'Connect your WooCommerce store to Google Merchant Center using Merchant API v1 — Google\'s current standard for product catalog sync. You\'ll need a Client ID, Client Secret, and Merchant ID from Google Cloud Console.', 'rex-product-feed' ); ?>
                    </p>
                    <div class="single-merchant_pdf__link">
                        <a href="<?php echo esc_url( 'https://rextheme.com/docs/upload-woocomerce-product-feed-directly-to-google-merchant-center/?utm_source=plugin&utm_medium=google_form_direct_upload_link&utm_campaign=pfm_plugin' ); ?>"
                           target="_blank">
							<?php esc_html_e( 'Direct Upload Method (No need for authorization)', 'rex-product-feed' ); ?>
                        </a>
                        <a href="<?php echo esc_url( 'https://rextheme.com/docs/auto-sync-products-to-google-merchant-center-using-merchant-api/?utm_source=plugin&utm_medium=get_started_auto_sync_link&utm_campaign=pfm_plugin' ); ?>"
                           target="_blank">
							<?php esc_html_e( 'Merchant API v1 Method (Require authorization)', 'rex-product-feed' ); ?>
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
	 * Generates the HTML markup for the authorization success message.
	 *
	 * This method creates and returns the HTML content to display a success message
	 * indicating that the user is authorized to send feeds to Google Merchant Center.
	 *
	 * @return string The HTML markup for the authorization success message.
	 *
	 * @since 7.4.20
	 */
	public function authorization_success_html() {
		ob_start();
        ?>
        <div id="card-alert" class="single-merchant-area authorized">
            <div class="single-merchant-block">
                <span class="card-title rex-card-title">&#10003; <?php esc_html_e( 'Connected — Google Merchant API v1', 'rex-product-feed' ); ?></span>
                <p class="rex-p"><?php esc_html_e( 'Your store is connected to Google Merchant Center via Merchant API v1.', 'rex-product-feed' ); ?></p>
				<a class="btn-default" href="<?php echo esc_url( $this->get_auth_url() ); ?>" target="_blank"><?php esc_html_e( 'Re-authenticate', 'rex-product-feed' ); ?></a>
            </div>
        </div>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
	}
}