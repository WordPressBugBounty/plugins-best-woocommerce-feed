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
			$log->info( $e->getMessage(), [ 'source' => 'WPFMGmcAuthorization' ] );
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
		if ( !$this->is_authorized() ) {
			if ( ! empty( $auth_code ) ) {
				$this->save_access_token( sanitize_text_field( $auth_code ), 'fetchAccessTokenWithAuthCode' );
			}
			else {
				$token_data = $this->get_access_token();
				if ( ! empty( $token_data['refresh_token'] ) ) {
					$this->save_access_token( $token_data['refresh_token'], 'fetchAccessTokenWithRefreshToken' );
				}
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
		$client = $this->get_client();
		return !$client->isAccessTokenExpired();
	}

	/**
	 * Get authorization URL.
	 *
	 * This method retrieves the authorization URL for the Google API.
	 * It uses the Google API client to create the URL with the required scopes.
	 *
	 * @return string The authorization URL.
	 *
	 * @since 7.4.20
	 */
	public function get_auth_url() {
		return $this->get_client()->createAuthUrl();
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
	 * Get products stats.
	 *
	 * This method retrieves the statistics for the products submitted to Google Merchant.
	 * It uses the Google Shopping API to get the account status and product statistics.
	 *
	 * @return array The array of product statistics.
	 *
	 * @since 1.0.0
	 */
	public function get_product_stats_summery() {
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

				return [
					'total'       => $total,
					'active'      => [ 'count' => $active, 'rate' => ( $active * 100 ) / $total . '%' ],
					'expiring'    => [ 'count' => $expiring, 'rate' => ( $expiring * 100 ) / $total . '%' ],
					'pending'     => [ 'count' => $pending, 'rate' => ( $pending * 100 ) / $total . '%' ],
					'disapproved' => [ 'count' => $disapproved, 'rate' => ( $disapproved * 100 ) / $total . '%' ],
				];
			}
		}

		return [
			'total'       => 0,
			'active'      => 0,
			'expiring'    => 0,
			'pending'     => 0,
			'disapproved' => 0,
		];
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
		$merchant_id = $this->get_merchant_id();
		if ( ! empty( $merchant_id ) && is_numeric( $merchant_id ) && $this->validate_auth() ) {
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
				$log->info( $e->getMessage(), [ 'source' => 'WPFMGmcDiagnosticsReport' ] );
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
				'prev_page_token' => $page_token,
				'next_page_token' => $products_statuses->nextPageToken ?? null,
				'products'        => $products,
			];
		}

		return [];
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
		ob_start();
		?>
        <div class="single-merchant-area authorized">
            <div class="single-merchant-block">
                <header>
                    <h2 class="title"><?php esc_html_e( "You Are Not Authorized", "rex-product-feed" ); ?></h2>
                    <img src="<?php echo WPFM_PLUGIN_ASSETS_FOLDER . "/icon/danger.png"; ?>" class="title-icon" alt="bwf-documentation">
                </header>
                <div class="body">
                    <p><?php esc_html_e( 'Your access token has expired. This application uses OAuth 2.0 to Access Google APIs. Please insert the information below and authenticate token for Google Merchant Shop. Generated access token expires after 3600 sec.', 'rex-product-feed' ); ?></p>
                    <p class="single-merchant-bold"><?php _e( 'NB: This session expiration is set by Google. You only need to authorize while submitting a new feed. You can ignore this if you\'ve already submitted your feed to Google.', 'rex-product-feed' ); ?></p>
                    <a class="btn-default" href="<?php echo esc_url( $this->get_auth_url() ); ?>" target="_blank"><?php _e( 'Authenticate', 'rex-product-feed' ); ?></a>
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
                <span class="card-title rex-card-title"><?php esc_html_e( 'You Are Authorized.', 'rex-product-feed' );?></span>
                <p class="rex-p"><?php esc_html_e( 'You are now ready to send feed from Product Feed Manager for WooCommerce to your Google Merchant Center. ', 'rex-product-feed' );?>🚀 </p>
            </div>
        </div>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
	}
}