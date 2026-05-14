<?php
/**
 * This file is responsible for displaying global setting options for the Rex Product Feed plugin.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

// Constants & Defaults
$hour_in_seconds = defined( 'HOUR_IN_SECONDS' ) ? HOUR_IN_SECONDS : 3600; // Use WordPress constant if available, otherwise default to 3600 seconds (1 hour)

// Feature Flags & Settings
$is_premium_activated        = apply_filters( 'wpfm_is_premium', false ); // Check if the premium version is activated
$custom_field                = get_option( 'rex-wpfm-product-custom-field', 'no' ); // Get the custom field option, default to 'no'
$pa_field                    = get_option( 'rex-wpfm-product-pa-field' ); // Get product attribute field option
$structured_data             = get_option( 'rex-wpfm-product-structured-data' ); // Get structured data setting
$exclude_tax                 = get_option( 'rex-wpfm-product-structured-data-exclude-tax' ); // Get tax exclusion setting for structured data
$wpfm_cache_ttl              = get_option( 'wpfm_cache_ttl', 3 * $hour_in_seconds ); // Get cache TTL (time to live), default to 3 hours
$wpfm_allow_private_products = get_option( 'wpfm_allow_private', 'no' ); // Get private products setting, default to 'no'
$wpfm_hide_char              = get_option( 'rex_feed_hide_character_limit_field', 'on' ); // Get character limit field visibility setting, default to 'on'
$wpfm_fb_pixel_enabled       = get_option( 'wpfm_fb_pixel_enabled', 'no' ); // Check if Facebook Pixel is enabled, default to 'no'
$wpfm_tiktok_pixel_enabled   = get_option( 'wpfm_tiktok_pixel_enabled', 'no' ); // Check if Facebook Pixel is enabled, default to 'no'
$wpfm_fb_pixel_data          = get_option( 'wpfm_fb_pixel_value' ); // Get Facebook Pixel data
$wpfm_enable_log             = get_option( 'wpfm_enable_log' ); // Get log enabling setting
$current_user_email          = get_option( 'wpfm_user_email', '' ); // Get the current user's email, default to empty string
$pro_url                     = add_query_arg( 'pfm-dashboard', '1', 'https://rextheme.com/best-woocommerce-product-feed/pricing/?utm_source=go_pro_button&utm_medium=plugin&utm_campaign=pfm_pro&utm_id=pfm_pro' ); // URL for upgrading to Pro version
$rollback_versions           = function_exists( 'rex_feed_get_roll_back_versions' ) ? rex_feed_get_roll_back_versions() : array(); // Get rollback versions if the function exists
$wpfm_remove_plugin_data     = get_option( 'wpfm_remove_plugin_data' ); // Get plugin data removal setting

// Get Log Files
$logs      = class_exists( 'WC_Admin_Status' ) && is_callable( array( 'WC_Admin_Status', 'scan_log_files' ) ) ? WC_Admin_Status::scan_log_files() : array();
$wpfm_logs = array();
$pattern   = '/^wpfm|fatal/';
foreach ( $logs as $key => $value ) {
	if ( preg_match( $pattern, $key ) ) {
		$wpfm_logs[ $key ] = $value;
	}
}

// Schedule Options
$schedule_hours = array(
    '1'   => __( '1 Hour', 'rex-product-feed' ),
    '3'   => __( '3 Hours', 'rex-product-feed' ),
    '6'   => __( '6 Hours', 'rex-product-feed' ),
    '12'  => __( '12 Hours', 'rex-product-feed' ),
    '24'  => __( '24 Hours', 'rex-product-feed' ),
    '168' => __( '1 Week', 'rex-product-feed' ),
);

// Set Products Per Batch Limit Based on Premium Status
if ( $is_premium_activated ) {
    $per_batch = get_option( 'rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT );
} else {
    // Limit products per batch for free users
    $per_batch = get_option( 'rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT ) > WPFM_FREE_MAX_PRODUCT_LIMIT ? WPFM_FREE_MAX_PRODUCT_LIMIT : get_option( 'rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT );
}
?>


<section class="rex-onboarding">
	<div class="pfm-page-header">
		<div class="pfm-page-header__title-group">
			<h1 class="pfm-page-header__title"><?php echo esc_html__( 'Product Feed Manager For WooCommerce', 'rex-product-feed' ); ?></h1>
			<p class="pfm-page-header__subtitle"><?php echo esc_html__( 'Configure how your WooCommerce products are exported to ad platforms and marketplaces.', 'rex-product-feed' ); ?></p>
		</div>
		<div class="pfm-page-header__actions">
			<a href="https://rextheme.com/docs/best-woocommerce-product-feed/" target="_blank" rel="noopener" class="pfm-btn pfm-btn--ghost">
				<?php echo esc_html__( 'Documentation', 'rex-product-feed' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product-feed' ) ); ?>" class="pfm-btn pfm-btn--primary">
				<?php echo esc_html__( 'New Feed', 'rex-product-feed' ); ?>
			</a>
		</div>
	</div>


	<div class="rex-onboarding__tab-wrapper">
		<nav class="rex-settings__nav-items" aria-label="<?php echo esc_attr__( 'Settings sections', 'rex-product-feed' ); ?>">
			<ul class="rex-settings__tabs" role="tablist">
				<li class="rex-settings__tab active" role="tab" aria-selected="true" data-tab="tab1" tabindex="0">
					<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/controls.php'; ?>
					<span><?php echo esc_html__( 'Controls', 'rex-product-feed' ); ?></span>
				</li>

				<li class="rex-settings__tab merchant" role="tab" aria-selected="false" data-tab="tab2" tabindex="-1">
					<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/merchants.php'; ?>
					<span><?php echo esc_html__( 'Merchants', 'rex-product-feed' ); ?></span>
				</li>

				<li class="rex-settings__tab" role="tab" aria-selected="false" data-tab="tab3" tabindex="-1">
					<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/status.php'; ?>
					<span><?php echo esc_html__( 'System Status', 'rex-product-feed' ); ?></span>
				</li>

				<li class="rex-settings__tab" role="tab" aria-selected="false" data-tab="tab4" tabindex="-1">
					<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/logs.php'; ?>
					<span><?php echo esc_html__( 'Logs', 'rex-product-feed' ); ?></span>
				</li>

				<?php if ( !$is_premium_activated ) : ?>
				<li class="rex-settings__tab rex-settings__tab--free-pro" role="tab" aria-selected="false" data-tab="tab5" tabindex="-1">
					<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/free-vs-pro.php'; ?>
					<span><?php echo esc_html__( 'Free vs Pro', 'rex-product-feed' ); ?></span>
				</li>
				<?php endif; ?>
			</ul>
		</nav>


		<!-- Tab content section with appropriate semantics -->
		<div class="rex-settings__tab-contents">
			<div id="tab1" class="tab-content active">
                <div class="pfm-settings-card">

                    <!-- Left category rail -->
                    <div class="pfm-ctrl-rail">
                        <div class="pfm-rail-search-wrap">
                            <input type="text" class="pfm-rail-search" id="pfm-settings-search"
                                   placeholder="<?php echo esc_attr__( 'Search', 'rex-product-feed' ); ?>"
                                   aria-label="<?php echo esc_attr__( 'Search settings', 'rex-product-feed' ); ?>">
                        </div>

                        <!-- Mobile group switcher (hidden above 1024px via CSS) -->
                        <select class="pfm-rail-mobile-select" aria-label="<?php echo esc_attr__( 'Select category', 'rex-product-feed' ); ?>">
                            <option value="general"><?php echo esc_html__( 'General', 'rex-product-feed' ); ?></option>
                            <option value="maintenance"><?php echo esc_html__( 'Maintenance', 'rex-product-feed' ); ?></option>
                            <option value="product-data"><?php echo esc_html__( 'Product Data', 'rex-product-feed' ); ?></option>
                            <option value="marketing-pixels"><?php echo esc_html__( 'Marketing Pixels', 'rex-product-feed' ); ?></option>
                            <option value="notifications-logging"><?php echo esc_html__( 'Notifications &amp; Logging', 'rex-product-feed' ); ?></option>
                            <option value="data-privacy"><?php echo esc_html__( 'Data &amp; Privacy', 'rex-product-feed' ); ?></option>
                            <option value="import-export"><?php echo esc_html__( 'Import / Export', 'rex-product-feed' ); ?></option>
                            <option value="advanced"><?php echo esc_html__( 'Advanced', 'rex-product-feed' ); ?></option>
                        </select>

                        <ul class="pfm-rail-list" role="listbox" aria-label="<?php echo esc_attr__( 'Settings categories', 'rex-product-feed' ); ?>">
                            <li class="pfm-rail-item active" data-group="general" role="option" aria-selected="true" tabindex="0">
                                <span class="pfm-rail-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span>
                                <span class="pfm-rail-label"><?php echo esc_html__( 'General', 'rex-product-feed' ); ?></span>
                                <span class="pfm-rail-count">0</span>
                            </li>
                            <li class="pfm-rail-item" data-group="maintenance" role="option" aria-selected="false" tabindex="-1">
                                <span class="pfm-rail-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></span>
                                <span class="pfm-rail-label"><?php echo esc_html__( 'Maintenance', 'rex-product-feed' ); ?></span>
                                <span class="pfm-rail-count">0</span>
                            </li>
                            <li class="pfm-rail-item" data-group="product-data" role="option" aria-selected="false" tabindex="-1">
                                <span class="pfm-rail-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span>
                                <span class="pfm-rail-label"><?php echo esc_html__( 'Product Data', 'rex-product-feed' ); ?></span>
                                <span class="pfm-rail-count">0</span>
                            </li>
                            <li class="pfm-rail-item" data-group="marketing-pixels" role="option" aria-selected="false" tabindex="-1">
                                <span class="pfm-rail-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg></span>
                                <span class="pfm-rail-label"><?php echo esc_html__( 'Marketing Pixels', 'rex-product-feed' ); ?></span>
                                <span class="pfm-rail-count">0</span>
                            </li>
                            <li class="pfm-rail-item" data-group="notifications-logging" role="option" aria-selected="false" tabindex="-1">
                                <span class="pfm-rail-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></span>
                                <span class="pfm-rail-label"><?php echo esc_html__( 'Notifications &amp; Logging', 'rex-product-feed' ); ?></span>
                                <span class="pfm-rail-count">0</span>
                            </li>
                            <li class="pfm-rail-item" data-group="data-privacy" role="option" aria-selected="false" tabindex="-1">
                                <span class="pfm-rail-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
                                <span class="pfm-rail-label"><?php echo esc_html__( 'Data &amp; Privacy', 'rex-product-feed' ); ?></span>
                                <span class="pfm-rail-count">0</span>
                            </li>
                            <li class="pfm-rail-item" data-group="import-export" role="option" aria-selected="false" tabindex="-1">
                                <span class="pfm-rail-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg></span>
                                <span class="pfm-rail-label"><?php echo esc_html__( 'Import / Export', 'rex-product-feed' ); ?></span>
                                <span class="pfm-rail-count">0</span>
                            </li>
                            <li class="pfm-rail-item" data-group="advanced" role="option" aria-selected="false" tabindex="-1">
                                <span class="pfm-rail-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
                                <span class="pfm-rail-label"><?php echo esc_html__( 'Advanced', 'rex-product-feed' ); ?></span>
                                <span class="pfm-rail-count">0</span>
                            </li>
                        </ul>
                    </div>
                    <!-- /pfm-ctrl-rail -->

                    <!-- Right content pane — group containers filled by JS from pfm-row-pool -->
                    <div class="pfm-ctrl-pane">

                        <!-- Search results view (shown during active search) -->
                        <div class="pfm-search-results" role="region" aria-label="<?php echo esc_attr__( 'Search results', 'rex-product-feed' ); ?>">
                            <div class="pfm-search-results-header">
                                <span class="pfm-search-results-title"></span>
                                <button type="button" class="pfm-btn pfm-btn--ghost pfm-search-clear">
                                    <?php echo esc_html__( 'Clear', 'rex-product-feed' ); ?>
                                </button>
                            </div>
                            <div class="pfm-search-results-body"></div>
                            <div class="pfm-search-empty" role="status">
                                <p class="pfm-search-empty__msg"><?php echo esc_html__( 'No settings match your search.', 'rex-product-feed' ); ?></p>
                                <p class="pfm-search-empty__sub"><?php echo esc_html__( 'Try a different keyword or clear the search.', 'rex-product-feed' ); ?></p>
                            </div>
                        </div>

                        <!-- Group panes — JS appends rows from pfm-row-pool into these -->
                        <div class="pfm-group-pane active" data-group="general" role="tabpanel">
                            <div class="pfm-group-header">
                                <h2 class="pfm-group-header__title"><?php echo esc_html__( 'General', 'rex-product-feed' ); ?></h2>
                                <p class="pfm-group-header__desc"><?php echo esc_html__( 'Core feed generation behaviour.', 'rex-product-feed' ); ?></p>
                            </div>
                        </div>
                        <div class="pfm-group-pane" data-group="maintenance" role="tabpanel">
                            <div class="pfm-group-header">
                                <h2 class="pfm-group-header__title"><?php echo esc_html__( 'Maintenance', 'rex-product-feed' ); ?></h2>
                                <p class="pfm-group-header__desc"><?php echo esc_html__( 'One-click cleanup and repair actions.', 'rex-product-feed' ); ?></p>
                            </div>
                        </div>
                        <div class="pfm-group-pane" data-group="product-data" role="tabpanel">
                            <div class="pfm-group-header">
                                <h2 class="pfm-group-header__title"><?php echo esc_html__( 'Product Data', 'rex-product-feed' ); ?></h2>
                                <p class="pfm-group-header__desc"><?php echo esc_html__( 'Extra attributes to include in generated feeds.', 'rex-product-feed' ); ?></p>
                            </div>
                        </div>
                        <div class="pfm-group-pane" data-group="marketing-pixels" role="tabpanel">
                            <div class="pfm-group-header">
                                <h2 class="pfm-group-header__title"><?php echo esc_html__( 'Marketing Pixels', 'rex-product-feed' ); ?></h2>
                                <p class="pfm-group-header__desc"><?php echo esc_html__( 'Add tracking pixels to product pages.', 'rex-product-feed' ); ?></p>
                            </div>
                        </div>
                        <div class="pfm-group-pane" data-group="notifications-logging" role="tabpanel">
                            <div class="pfm-group-header">
                                <h2 class="pfm-group-header__title"><?php echo esc_html__( 'Notifications &amp; Logging', 'rex-product-feed' ); ?></h2>
                                <p class="pfm-group-header__desc"><?php echo esc_html__( 'Stay informed when feeds fail.', 'rex-product-feed' ); ?></p>
                            </div>
                        </div>
                        <div class="pfm-group-pane" data-group="data-privacy" role="tabpanel">
                            <div class="pfm-group-header">
                                <h2 class="pfm-group-header__title"><?php echo esc_html__( 'Data &amp; Privacy', 'rex-product-feed' ); ?></h2>
                                <p class="pfm-group-header__desc"><?php echo esc_html__( 'Control what happens to your data.', 'rex-product-feed' ); ?></p>
                            </div>
                        </div>
                        <div class="pfm-group-pane" data-group="import-export" role="tabpanel">
                            <div class="pfm-group-header">
                                <h2 class="pfm-group-header__title"><?php echo esc_html__( 'Import / Export', 'rex-product-feed' ); ?></h2>
                                <p class="pfm-group-header__desc"><?php echo esc_html__( 'Move feed configurations between sites.', 'rex-product-feed' ); ?></p>
                            </div>
                        </div>
                        <div class="pfm-group-pane" data-group="advanced" role="tabpanel">
                            <div class="pfm-group-header">
                                <h2 class="pfm-group-header__title"><?php echo esc_html__( 'Advanced', 'rex-product-feed' ); ?></h2>
                                <p class="pfm-group-header__desc"><?php echo esc_html__( 'Use with caution — these affect the plugin core.', 'rex-product-feed' ); ?></p>
                            </div>
                        </div>

                    </div>
                    <!-- /pfm-ctrl-pane -->

                </div>
                <!-- /pfm-settings-card -->

                <!-- Hidden row pool: JS reads and distributes these into group panes above -->
                <div class="pfm-row-pool" aria-hidden="true">

                    <?php /* ---- PERFORMANCE / GENERAL rows ---- */ ?>

                    <section class="wpfm-settings-section" data-section="performance">

                        <div class="single-merchant product-batch" data-label="<?php echo esc_attr__( 'Products per batch', 'rex-product-feed' ); ?>">
                            <div class="">
                                <span class="title"><?php echo esc_html__( 'Products per batch', 'rex-product-feed' ); ?></span>
                                <p><?php echo sprintf( esc_html__( 'Free users are limited to %d products and 1 batch. Pro users can go higher to speed up large feeds.', 'rex-product-feed' ), esc_html( WPFM_FREE_MAX_PRODUCT_LIMIT ) ); ?></p>
                            </div>
                            <div class="switch">
                                <form id="wpfm-per-batch" class="wpfm-per-batch">
                                    <input id="wpfm_product_per_batch" type="number" name="wpfm_product_per_batch"
                                           value="<?php echo esc_attr( $per_batch ); ?>"
                                           min="1" max="<?php echo !$is_premium_activated ? esc_attr( WPFM_FREE_MAX_PRODUCT_LIMIT ) : esc_attr( 500 ); ?>">
                                    <span class="pfm-input-unit"><?php echo esc_html__( 'products', 'rex-product-feed' ); ?></span>
                                    <button type="submit" class="save-batch">
                                        <span><?php esc_html_e( 'Save', 'rex-product-feed' ); ?></span>
                                        <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="single-merchant wpfm-clear-btn" data-label="<?php echo esc_attr__( 'Clear batch queue', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php echo esc_html__( 'Clear batch queue', 'rex-product-feed' ); ?></span>
                                <p><?php echo esc_html__( 'Removes all pending batch jobs. Use when a feed is stuck.', 'rex-product-feed' ); ?></p>
                            </div>
                            <button class="wpfm-clear-batch" id="wpfm-clear-batch">
                                <span><?php echo esc_html__( 'Clear Batch', 'rex-product-feed' ); ?></span>
                                <i class="fa fa-spinner fa-pulse fa-fw"></i>
                            </button>
                        </div>

                        <div class="single-merchant detailed-product purge-cache" data-label="<?php echo esc_attr__( 'Purge feed cache', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php echo esc_html__( 'Purge feed cache', 'rex-product-feed' ); ?></span>
                                <p><?php echo esc_html__( 'Force-clears cached feed data so the next generation is fresh.', 'rex-product-feed' ); ?></p>
                            </div>
                            <button id="wpfm-purge-cache" class="wpfm-purge-cache">
                                <span><?php echo esc_html__( 'Purge Cache', 'rex-product-feed' ); ?></span>
                                <i class="fa fa-spinner fa-pulse fa-fw"></i>
                            </button>
                        </div>

                        <div class="single-merchant update-list" data-label="<?php echo esc_attr__( 'Rebuild orphan variations list', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php echo esc_html__( 'Rebuild orphan variations list', 'rex-product-feed' ); ?></span>
                                <p><?php echo esc_html__( 'Updates WooCommerce variation children that have no parent assigned (abandoned children).', 'rex-product-feed' ); ?></p>
                            </div>
                            <button id="rex_feed_abandoned_child_list_update_button" class="rex-feed-abandoned-child-list-update-button">
                                <span><?php echo esc_html__( 'Update List', 'rex-product-feed' ); ?></span>
                                <i class="fa fa-spinner fa-pulse fa-fw"></i>
                            </button>
                        </div>

                        <div class="single-merchant detailed-product detailed-merchants" data-label="<?php echo esc_attr__( 'Feed cache TTL', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php echo esc_html__( 'Feed cache TTL', 'rex-product-feed' ); ?></span>
                                <p><?php echo esc_html__( 'How long cached feed data is valid before regeneration.', 'rex-product-feed' ); ?></p>
                            </div>
                            <form id="wpfm-transient-settings" class="wpfm-transient-settings">
                                <select id="wpfm_cache_ttl" name="wpfm_cache_ttl">
                                    <?php foreach ( $schedule_hours as $key => $label ) { ?>
                                        <option value="<?php echo esc_attr( (int) $key * $hour_in_seconds ); ?>" <?php selected( $wpfm_cache_ttl, (int) $key * $hour_in_seconds ); ?>><?php echo esc_attr( $label ); ?></option>
                                    <?php } ?>
                                </select>
                                <button type="submit" class="save-transient-button">
                                    <span><?php echo esc_html__( 'Save', 'rex-product-feed' ); ?></span>
                                    <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                </button>
                            </form>
                        </div>

                    </section>

                    <section class="wpfm-settings-section" data-section="product-data">

                        <div class="single-merchant unique-product <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>" data-label="<?php echo esc_attr__( 'Unique product identifiers', 'rex-product-feed' ); ?>">
                            <?php if ( !$is_premium_activated ) { ?>
                                <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                                   class="wpfm-pro-cta">
                                    <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                                </a>
                            <?php } ?>
                            <div class="single-merchant-pro">
                                <div>
                                    <span class="title"><?php echo esc_html__( 'Unique product identifiers', 'rex-product-feed' ); ?></span>
                                    <p><?php echo esc_html__( 'Adds Brand, GTIN, MPN, UPC, EAN, JAN, ISBN, ITF-14, Offer Price, Offer Effective Date and Additional Info to each product.', 'rex-product-feed' ); ?></p>
                                </div>
                                <div class="switch">
                                    <?php
                                    if ( !$is_premium_activated ) {
                                        $disabled = 'disabled';
                                        $checked  = '';
                                    } else {
                                        $disabled = '';
                                        $checked  = 'yes' === $custom_field ? 'checked' : '';
                                    }
                                    ?>
                                    <div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
                                        <input class="switch-input" type="checkbox"
                                               id="rex-product-custom-field" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?>>
                                        <label class="lever" for="rex-product-custom-field"></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ( $is_premium_activated ) : 
                            $custom_fields = get_option( 'wpfm_product_custom_fields_frontend', array() );
                        ?>
                        <div class="single-merchant wpfm-custom-field-frontend <?php echo ( $custom_field === 'no' ) ? 'is-hidden' : ''; ?>" data-label="<?php echo esc_attr__( 'Show WPFM Custom fields in Front-end [Single Product Page]', 'rex-product-feed' ); ?>">
                            <div class="wpfm-frontend-fields-box">
                                <div class="wpfm-frontend-fields-header">
                                    <span class="title"><?php echo esc_html__( 'SHOW ON SINGLE PRODUCT PAGE', 'rex-product-feed' ); ?></span>
                                    <p><?php echo esc_html__( 'Pick which identifiers are rendered on the public product page.', 'rex-product-feed' ); ?></p>
                                </div>
                                <form id="wpfm-frontend-fields" class="wpfm-frontend-fields">
                                    <div class="wpfm-custom-fields">
                                        <div class="single-meta-field">
                                            <input id="wpfm_product_brand" type="checkbox" name="wpfm_product_custom_fields_frontend[]" value="brand" <?php echo in_array( 'brand', $custom_fields ) ? 'checked' : '';?>>
                                            <label for="wpfm_product_brand" class="brand"><?php echo esc_html__( 'Brand', 'rex-product-feed' ); ?></label>
                                        </div>
                                        <div class="single-meta-field">
                                            <input id="wpfm_product_gtin" type="checkbox" name="wpfm_product_custom_fields_frontend[]" value="gtin" <?php echo in_array( 'gtin', $custom_fields ) ? 'checked' : '';?>>
                                            <label for="wpfm_product_gtin" class="gtin">GTIN</label>
                                        </div>
                                        <div class="single-meta-field">
                                            <input id="wpfm_product_mpn" type="checkbox" name="wpfm_product_custom_fields_frontend[]" value="mpn" <?php echo in_array( 'mpn', $custom_fields ) ? 'checked' : '';?>>
                                            <label for="wpfm_product_mpn" class="mpn">MPN</label>
                                        </div>
                                        <div class="single-meta-field">
                                            <input id="wpfm_product_upc" type="checkbox" name="wpfm_product_custom_fields_frontend[]" value="upc" <?php echo in_array( 'upc', $custom_fields ) ? 'checked' : '';?>>
                                            <label for="wpfm_product_upc" class="upc">UPC</label>
                                        </div>
                                        <div class="single-meta-field">
                                            <input id="wpfm_product_ean" type="checkbox" name="wpfm_product_custom_fields_frontend[]" value="ean" <?php echo in_array( 'ean', $custom_fields ) ? 'checked' : '';?>>
                                            <label for="wpfm_product_ean" class="ean">EAN</label>
                                        </div>
                                        <div class="single-meta-field">
                                            <input id="wpfm_product_jan" type="checkbox" name="wpfm_product_custom_fields_frontend[]" value="jan" <?php echo in_array( 'jan', $custom_fields ) ? 'checked' : '';?>>
                                            <label for="wpfm_product_jan" class="jan">JAN</label>
                                        </div>
                                        <div class="single-meta-field">
                                            <input id="wpfm_product_isbn" type="checkbox" name="wpfm_product_custom_fields_frontend[]" value="isbn" <?php echo in_array( 'isbn', $custom_fields ) ? 'checked' : '';?>>
                                            <label for="wpfm_product_isbn" class="isbn">ISBN</label>
                                        </div>
                                        <div class="single-meta-field">
                                            <input id="wpfm_product_itf" type="checkbox" name="wpfm_product_custom_fields_frontend[]" value="itf" <?php echo in_array( 'itf', $custom_fields ) ? 'checked' : '';?>>
                                            <label for="wpfm_product_itf" class="itf">ITF-14</label>
                                        </div>
                                        <div class="single-meta-field">
                                            <input id="wpfm_product_offer_price" type="checkbox" name="wpfm_product_custom_fields_frontend[]" value="offer_price" <?php echo in_array( 'offer_price', $custom_fields ) ? 'checked' : '';?>>
                                            <label for="wpfm_product_offer_price" class="offer-price"><?php echo esc_html__( 'Offer price', 'rex-product-feed' ); ?></label>
                                        </div>
                                        <div class="single-meta-field">
                                            <input id="wpfm_product_effective_date" type="checkbox" name="wpfm_product_custom_fields_frontend[]" value="offer_effective_date" <?php echo in_array( 'offer_effective_date', $custom_fields ) ? 'checked' : '';?>>
                                            <label for="wpfm_product_effective_date" class="effective-date"><?php echo esc_html__( 'Effective date', 'rex-product-feed' ); ?></label>
                                        </div>
                                        <div class="single-meta-field">
                                            <input id="wpfm_product_additional_info" type="checkbox" name="wpfm_product_custom_fields_frontend[]" value="additional_info" <?php echo in_array( 'additional_info', $custom_fields ) ? 'checked' : '';?>>
                                            <label for="wpfm_product_additional_info" class="additional-info"><?php echo esc_html__( 'Additional info', 'rex-product-feed' ); ?></label>
                                        </div>
                                    </div>
                                    <div class="wpfm-frontend-fields-actions">
                                        <button type="button" class="wpfm-frontend-fields-clear"><?php echo esc_html__( 'Clear', 'rex-product-feed' ); ?></button>
                                        <button type="submit" class="save-wpfm-fields-show">
                                            <span><?php echo esc_html__( 'Save', 'rex-product-feed' );?></span>
                                            <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="single-merchant detailed-product <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>" data-label="<?php echo esc_attr__( 'Detailed product attributes', 'rex-product-feed' ); ?>">
                            <?php if ( !$is_premium_activated ) { ?>
                                <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                                   class="wpfm-pro-cta">
                                    <span class="wpfm-pro-tag"><?php esc_html_e( 'pro', 'rex-product-feed' ); ?></span>
                                </a>
                            <?php } ?>
                            <div class="single-merchant-pro">
                                <div>
                                    <span class="title"><?php esc_html_e( 'Detailed product attributes', 'rex-product-feed' ); ?></span>
                                    <p><?php esc_html_e( 'Adds Size, Color, Pattern, Material, Age Group and Gender attributes to each product.', 'rex-product-feed' ); ?></p>
                                </div>
                                <div class="switch">
                                    <?php
                                    if ( !$is_premium_activated ) {
                                        $disabled = 'disabled';
                                        $checked  = '';
                                    } else {
                                        $disabled = '';
                                        $checked  = 'yes' === $pa_field ? 'checked' : '';
                                    }
                                    ?>
                                    <div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
                                        <input class="switch-input" type="checkbox"
                                               id="rex-product-pa-field" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?>>
                                        <label class="lever" for="rex-product-pa-field"></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="single-merchant exclude-tax <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>" data-label="<?php echo esc_attr__( 'Exclude TAX from structured data prices', 'rex-product-feed' ); ?>">
                            <?php if ( !$is_premium_activated ) { ?>
                                <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                                   class="wpfm-pro-cta">
                                    <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                                </a>
                            <?php } ?>
                            <div class="single-merchant-pro">
                                <div>
                                    <span class="title"><?php echo esc_html__( 'Exclude TAX from structured data prices', 'rex-product-feed' ); ?></span>
                                    <p><?php echo esc_html__( 'Strips tax from prices included in JSON-LD output.', 'rex-product-feed' ); ?></p>
                                </div>
                                <div class="switch">
                                    <?php
                                    if ( !$is_premium_activated ) {
                                        $disabled = 'disabled';
                                        $checked  = '';
                                    } else {
                                        $disabled = '';
                                        $checked  = 'yes' === $exclude_tax ? 'checked' : '';
                                    }
                                    ?>
                                    <div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
                                        <input class="switch-input" type="checkbox"
                                               id="rex-product-exclude-tax" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?>>
                                        <label class="lever" for="rex-product-exclude-tax"></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="single-merchant detailed-product" data-label="<?php echo esc_attr__( 'Allow private products', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php esc_html_e( 'Allow private products', 'rex-product-feed' ); ?></span>
                                <p><?php esc_html_e( "Includes products with 'private' visibility in feeds.", 'rex-product-feed' ); ?></p>
                            </div>
                            <div class="switch">
                                <?php
                                $disabled = '';
                                $checked  = 'yes' === $wpfm_allow_private_products ? 'checked' : '';
                                ?>
                                <div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
                                    <input class="switch-input" type="checkbox"
                                           id="rex-product-allow-private" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?>>
                                    <label class="lever" for="rex-product-allow-private"></label>
                                </div>
                            </div>
                        </div>

                        <div class="single-merchant increase-product <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>" data-label="<?php echo esc_attr__( 'Increase Google Merchant approval rate', 'rex-product-feed' ); ?>">
                            <?php if ( !$is_premium_activated ) { ?>
                                <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                                   class="wpfm-pro-cta">
                                    <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                                </a>
                            <?php } ?>
                            <div class="single-merchant-pro">
                                <div>
                                    <span class="title"><?php echo esc_html__( 'Increase Google Merchant approval rate', 'rex-product-feed' ); ?></span>
                                    <p><?php echo esc_html__( "Fixes WooCommerce's (JSON-LD) structured data bug and adds extra structured data elements to your pages so more products get approved in Google Merchant Center.", 'rex-product-feed' ); ?></p>
                                </div>
                                <div class="switch">
                                    <?php
                                    if ( !$is_premium_activated ) {
                                        $disabled = 'disabled';
                                        $checked  = '';
                                    } else {
                                        $disabled = '';
                                        $checked  = 'yes' === $structured_data ? 'checked' : '';
                                    }
                                    ?>
                                    <div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
                                        <input class="switch-input" type="checkbox"
                                               id="rex-product-structured-data" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?>>
                                        <label class="lever" for="rex-product-structured-data"></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </section>

                    <section class="wpfm-settings-section" data-section="marketing">

                        <div class="single-merchant fb-pixel pfm-pixel-row" data-label="<?php echo esc_attr__( 'Facebook Pixel', 'rex-product-feed' ); ?>">
                            <div class="pfm-pixel-row__toggle">
                                <span class="title"><?php echo esc_html__( 'Facebook Pixel', 'rex-product-feed' ); ?></span>
                                <?php
                                if ( 'yes' === $wpfm_fb_pixel_enabled ) {
                                    $checked      = 'checked';
                                    $hidden_class = '';
                                }
                                else {
                                    $checked      = '';
                                    $hidden_class = 'is-hidden';
                                }
                                ?>
                                <div class="wpfm-switcher">
                                    <input class="switch-input" type="checkbox" id="wpfm_fb_pixel" <?php echo esc_attr( $checked ); ?>>
                                    <label class="lever" for="wpfm_fb_pixel"></label>
                                </div>
                            </div>
                            <div class="pfm-pixel-row__subfield wpfm-fb-pixel-field <?php echo esc_attr( $hidden_class ); ?>">
                                <span class="pfm-pixel-label"><?php echo esc_html__( 'Facebook Pixel ID', 'rex-product-feed' ); ?></span>
                                <form id="wpfm-fb-pixel" class="wpfm-fb-pixel">
                                    <input id="wpfm_fb_pixel_id" type="text" name="wpfm_fb_pixel"
                                           value="<?php echo esc_attr( $wpfm_fb_pixel_data ); ?>">
                                    <button type="submit" class="save-fb-pixel"><span><?php echo esc_html__( 'Save', 'rex-product-feed' ); ?></span>
                                        <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="single-merchant tiktok-pixel pfm-pixel-row <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>" data-label="<?php echo esc_attr__( 'TikTok Pixel', 'rex-product-feed' ); ?>">
                            <?php if ( !$is_premium_activated ) { ?>
                                <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                                   class="wpfm-pro-cta">
                                    <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                                </a>
                            <?php } ?>
                            <div class="pfm-pixel-row__toggle">
                                <span class="title"><?php echo esc_html__( 'TikTok Pixel', 'rex-product-feed' ); ?></span>
                                <?php
                                $disabled            = '';
                                $checked             = 'yes' === $wpfm_tiktok_pixel_enabled ? 'checked' : '';
                                $tiktok_hidden_class = 'yes' === $wpfm_tiktok_pixel_enabled ? '' : 'is-hidden';
                                if ( !$is_premium_activated ) {
                                    $disabled            = 'disabled';
                                    $checked             = '';
                                    $tiktok_hidden_class = 'is-hidden';
                                }
                                ?>
                                <div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
                                    <input class="switch-input" type="checkbox" id="wpfm_tiktok_pixel" <?php echo esc_attr( $checked ); ?>>
                                    <label class="lever" for="wpfm_tiktok_pixel"></label>
                                </div>
                            </div>
            
                            <?php if ( $is_premium_activated ) : ?>
                            <div class="pfm-pixel-row__subfield wpfm-tiktok-pixel-field <?php echo esc_attr( $tiktok_hidden_class ); ?>">
                                <span class="pfm-pixel-label"><?php echo esc_html__( 'TikTok Pixel ID', 'rex-product-feed' ); ?></span>
                                <form id="wpfm-tiktok-pixel" class="wpfm-tiktok-pixel">
                                    <input id="wpfm_tiktok_pixel" type="text" name="wpfm_tiktok_pixel"
                                           value="<?php echo esc_attr( get_option( 'wpfm_tiktok_pixel_value' ) ); ?>">
                                    <button type="submit" class="save-tiktok-pixel"><span><?php echo esc_html__( 'Save', 'rex-product-feed' ); ?></span>
                                        <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                            
                        </div>

                        <?php $enable_google_drm = apply_filters( 'rexfeed_enable_google_drm_pixel', false ); ?>
                        <div class="single-merchant google-drm-pixel pfm-pixel-row <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>" data-label="<?php echo esc_attr__( 'Google Dynamic Remarketing Pixel', 'rex-product-feed' ); ?>">
                            <?php if ( !$is_premium_activated ) { ?>
                                <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                                   class="wpfm-pro-cta">
                                    <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                                </a>
                            <?php } ?>
                            <div class="pfm-pixel-row__toggle">
                                <span class="title"><?php echo esc_html__( 'Google Dynamic Remarketing Pixel', 'rex-product-feed' ); ?></span>
                                <?php
                                $disabled           = '';
                                $checked            = 'yes' === get_option( 'wpfm_google_drm_pixel_enabled', 'no' ) ? 'checked' : '';
                                $drm_hidden_class   = 'yes' === get_option( 'wpfm_google_drm_pixel_enabled', 'no' ) ? '' : 'is-hidden';
                                if ( !$enable_google_drm ) {
                                    $disabled         = 'disabled';
                                    $checked          = '';
                                    $drm_hidden_class = 'is-hidden';
                                }
                                ?>
                                <div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
                                    <input class="switch-input" type="checkbox" id="wpfm_google_drm_pixel" <?php echo esc_attr( $checked ); echo esc_attr( $disabled ); ?>>
                                    <label class="lever" for="wpfm_google_drm_pixel"></label>
                                </div>
                            </div>
                            
                            <?php if ( $is_premium_activated ) : ?>
                            <div class="pfm-pixel-row__subfield wpfm-google-drm-pixel-field <?php echo esc_attr( $drm_hidden_class ); ?>">
                                <span class="pfm-pixel-label"><?php echo esc_html__( 'Dynamic Remarketing Conversion Tracking ID', 'rex-product-feed' ); ?></span>
                                <form id="wpfm-google-drm-pixel" class="wpfm-google-drm-pixel">
                                    <input id="wpfm_google_drm_pixel" type="text" name="wpfm_google_drm_pixel"
                                           value="<?php echo esc_attr( get_option( 'wpfm_google_drm_pixel_id' ) ); ?>">
                                    <button type="submit" class="save-google-drm-pixel"><span><?php echo esc_html__( 'Save', 'rex-product-feed' ); ?></span>
                                        <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                            
                        </div>

                        <div class="single-merchant <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>" data-label="<?php echo esc_attr__( 'Email notification', 'rex-product-feed' ); ?>">
                            <?php if ( !$is_premium_activated ) { ?>
                                <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                                   class="wpfm-pro-cta">
                                    <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                                </a>
                            <?php } ?>
                            <div class="single-merchant-pro">
                                <div>
                                    <span class="title"><?php echo esc_html__( 'Email me when a feed fails', 'rex-product-feed' ); ?></span>
                                    <p><?php echo esc_html__( 'Sends an alert if a scheduled feed regeneration errors out.', 'rex-product-feed' ); ?></p>
                                </div>
                                <div class="switch">
                                    <form id="wpfm-user-email" class="wpfm-fb-pixel">
                                        <input class="<?php echo !$is_premium_activated ? 'rexfeed-pro-disabled' : ''; ?>" placeholder="<?php echo esc_attr__( 'you@example.com', 'rex-product-feed' ); ?>" id="wpfm_user_email" type="text" name="wpfm_user_email" value="<?php echo esc_attr( $current_user_email ); ?>">
                                        <button type="submit" class="save-user-email <?php echo !$is_premium_activated ? 'rexfeed-pro-disabled' : ''; ?>" <?php echo !$is_premium_activated ? 'disabled' : ''; ?>>
                                            <span><?php echo esc_html__( 'Save', 'rex-product-feed' ); ?></span>
                                            <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </section>

                    <section class="wpfm-settings-section" data-section="maintenance">

                        <div class="single-merchant remove-plugin-data" data-label="<?php echo esc_attr__( 'Remove plugin data', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php echo esc_html__( 'Remove all plugin data on uninstall', 'rex-product-feed' ); ?></span>
                                <p><?php echo esc_html__( 'When this plugin is deleted, all feeds, mappings and settings will be wiped permanently.', 'rex-product-feed' ); ?></p>
                            </div>
                            <div class="switch">
                                <?php
                                $checked = 'yes' === $wpfm_remove_plugin_data ? 'checked' : '';
                                ?>
                                <div class="wpfm-switcher">
                                    <input class="switch-input" type="checkbox"
                                           id="remove_plugin_data" <?php echo esc_attr( $checked ); ?>>
                                    <label class="lever" for="remove_plugin_data"></label>
                                </div>
                            </div>
                        </div>

                        <div class="single-merchant enable-log" data-label="<?php echo esc_attr__( 'Enable error log', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php echo esc_html__( 'Enable error log', 'rex-product-feed' ); ?></span>
                                <p><?php echo esc_html__( 'Writes detailed events to wc-logs/. Useful for debugging.', 'rex-product-feed' ); ?></p>
                            </div>
                            <div class="switch">
                                <?php
                                $checked = 'yes' === $wpfm_enable_log ? 'checked' : '';
                                ?>
                                <div class="wpfm-switcher">
                                    <input class="switch-input" type="checkbox"
                                           id="wpfm_enable_log" <?php echo esc_attr( $checked ); ?>>
                                    <label class="lever" for="wpfm_enable_log"></label>
                                </div>
                            </div>
                        </div>

                        <div class="single-merchant" data-label="<?php echo esc_attr__( 'Allow usage tracking', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php echo esc_html__( 'Allow anonymous usage tracking', 'rex-product-feed' ); ?></span>
                                <p><?php echo esc_html__( 'Helps the plugin authors improve features. No personal data is sent.', 'rex-product-feed' ); ?></p>
                            </div>
                            <div class="switch">
                                <?php
                                $usage_tracking = get_option( 'best-woocommerce-feed_allow_tracking', 'no' );
                                $checked = 'yes' === $usage_tracking ? 'checked' : '';
                                ?>
                                <div class="wpfm-switcher">
                                    <input class="switch-input" type="checkbox"
                                           id="wpfm_allow_tracking" <?php echo esc_attr( $checked ); ?>>
                                    <label class="lever" for="wpfm_allow_tracking"></label>
                                </div>
                            </div>
                        </div>

                        <div class="single-merchant hide-character" data-label="<?php echo esc_attr__( 'Hide character limit', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php echo esc_html__( 'Hide character-limit column', 'rex-product-feed' ); ?></span>
                                <p><?php echo esc_html__( 'Hides the per-field character limit column in the feed editor for a cleaner UI.', 'rex-product-feed' ); ?></p>
                            </div>
                            <div class="switch">
                                <?php
                                $checked = 'on' === $wpfm_hide_char ? 'checked' : '';
                                ?>
                                <div class="wpfm-switcher">
                                    <input class="switch-input" type="checkbox"
                                           id="wpfm_hide_char" <?php echo esc_attr( $checked ); ?>>
                                    <label class="lever" for="wpfm_hide_char"></label>
                                </div>
                            </div>
                        </div>

                        <div class="single-merchant detailed-product rex-feed-rollback" data-label="<?php echo esc_attr__( 'Rollback to older version', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php echo esc_html__( 'Rollback to older version', 'rex-product-feed' ); ?></span>
                                <p><?php echo esc_html__( 'Reinstalls the chosen plugin version. Back up your database first — you may lose recent data.', 'rex-product-feed' ); ?></p>
                            </div>
                            <div class="wpfm-rollback-option-area">
                                <select id="wpfm_rollback_options" name="wpfm_rollback_options">
                                    <?php foreach ( $rollback_versions as $version ) { ?>
                                        <option value="<?php echo esc_attr( $version ); ?>"><?php echo esc_html( $version ); ?></option>
                                    <?php } ?>
                                </select>
                                <?php
                                echo sprintf(
                                    '<button type="button" data-placeholder-text="' . esc_html__( 'Reinstall', 'rex-product-feed' ) . ' v{VERSION}" data-placeholder-url="%s" class="rex-feed-button-spinner rex-feed-rollback-button">%s</button>',
                                    esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=rex_feed_rollback&version=VERSION' ), 'rex_feed_rollback' ) ),
                                    esc_html__( 'Reinstall', 'rex-product-feed' )
                                );
                                ?>
                            </div>
                        </div>

                    </section>

                    <section class="wpfm-settings-section" data-section="import-export">

                        <div class="single-merchant rex-feed-export" data-label="<?php echo esc_attr__( 'Export feed configurations', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php echo esc_html__( 'Export feed configurations', 'rex-product-feed' ); ?></span>
                                <p><?php echo esc_html__( 'Download a JSON file containing all of your feed setups.', 'rex-product-feed' ); ?></p>
                            </div>
                            <button type="button" id="rex-feed-export-btn" class="rex-feed-export-btn">
                                <span><?php echo esc_html__( 'Export', 'rex-product-feed' ); ?></span>
                                <i class="fa fa-spinner fa-pulse fa-fw"></i>
                            </button>
                        </div>

                        <div class="single-merchant rex-feed-import" data-label="<?php echo esc_attr__( 'Import feed configurations', 'rex-product-feed' ); ?>">
                            <div>
                                <span class="title"><?php echo esc_html__( 'Import feed configurations', 'rex-product-feed' ); ?></span>
                                <p><?php echo esc_html__( 'Upload a JSON export from another site to restore feeds.', 'rex-product-feed' ); ?></p>
                            </div>
                            <div class="rex-feed-import-area">
                                <input type="file" id="rex-feed-import-file" accept=".json" style="display:none;">
                                <button type="button" class="pfm-btn pfm-btn--ghost rex-feed-import-choose">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <?php echo esc_html__( 'Choose file', 'rex-product-feed' ); ?>
                                </button>
                                <span class="rex-feed-import-filename"><?php echo esc_html__( 'No file chosen', 'rex-product-feed' ); ?></span>
                                <button type="button" id="rex-feed-import-btn" class="rex-feed-import-btn">
                                    <span><?php echo esc_html__( 'Import', 'rex-product-feed' ); ?></span>
                                    <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                </button>
                            </div>
                        </div>

                    </section>

                    <div id="wpfm-sticky-save-bar">
                        <span id="wpfm-sticky-save-notice"><?php echo esc_html__( 'You have unsaved changes.', 'rex-product-feed' ); ?></span>
                        <button id="wpfm-sticky-save-btn" type="button"><?php echo esc_html__( 'Save Changes', 'rex-product-feed' ); ?></button>
                    </div>

                </div>
                <!-- /pfm-row-pool -->

			</div>
			<!--/settings tab-->

			<div id="tab2" class="tab-content">
                <div class="pfm-settings-card">
                    <div class="pfm-merchants-wrapper">
                        <?php
                        // Define categories
                        $categories = [
                            'social_media' => ['label' => 'Social Media', 'keys' => ['facebook', 'instagram', 'twitter', 'pinterest', 'snapchat', 'tiktok', 'reddit_ads']],
                            'search_engine' => ['label' => 'Search Engine', 'keys' => ['google', 'bing', 'yandex', 'yahoo', 'google_local_inventory_ads', 'google_express', 'google_manufacturer_center', 'google_css_center', 'google_Ad', 'drm']],
                            'deal_sites' => ['label' => 'Deal Sites', 'keys' => ['groupon', 'sparmedo', 'deals4u', 'mydeal']],
                            'custom' => ['label' => 'Custom', 'keys' => ['custom']],
                            'marketplace' => ['label' => 'Marketplace', 'keys' => ['amazon', 'walmart', 'ebay', 'ebay_seller', 'ebay_seller_tickets', 'etsy', 'catch', 'jet', 'bonanza', 'newegg', 'lazada', 'shopee', 'fruugo', 'bol', 'wish', 'rozetka', 'kogan', 'mirakl', 'rakuten', 'rakuten_advertising', 'emag', 'target']]
                        ];

                        $all_merchants = Rex_Feed_Merchants::get_merchants();
                        $_merchants    = !empty( $all_merchants[ 'popular' ] ) ? $all_merchants[ 'popular' ] : array();

                        if ( !$is_premium_activated ) {
                            $_merchants = !empty( $all_merchants[ 'pro_merchants' ] ) ? array_merge( $_merchants, $all_merchants[ 'pro_merchants' ] ) : $_merchants;
                        }

                        $_merchants = !empty( $all_merchants[ 'free_merchants' ] ) ? array_merge( $_merchants, $all_merchants[ 'free_merchants' ] ) : $_merchants;

                        $_merchants[ 'google' ][ 'name' ]    = 'Google Shopping';
                        $_merchants[ 'google_Ad' ][ 'name' ] = 'Google AdWords';
                        $_merchants[ 'drm' ][ 'name' ]       = 'Google Remarketing (DRM)';

                        // Categorize merchants
                        $categorized_merchants = [
                            'all' => [],
                            'marketplace' => [],
                            'search_engine' => [],
                            'social_media' => [],
                            'deal_sites' => [],
                            'country' => [],
                            'custom' => [],
                            'other' => []
                        ];

                        foreach ($_merchants as $key => $merchant) {
                            if (!$key) continue;
                            $merchant_name = !empty( $merchant['name'] ) ? $merchant['name'] : '';
                            $cat = 'other';
                            foreach($categories as $c_key => $c_data) {
                                if(in_array($key, $c_data['keys'])) {
                                    $cat = $c_key;
                                    break;
                                }
                            }
                            if ($cat === 'other') {
                                if (strpos(strtolower($merchant_name), '.de') !== false || strpos(strtolower($merchant_name), '.co.uk') !== false || strpos(strtolower($merchant_name), '.nl') !== false || strpos(strtolower($merchant_name), '.fr') !== false || strpos(strtolower($merchant_name), '.se') !== false || strpos(strtolower($merchant_name), '.dk') !== false) {
                                    $cat = 'country';
                                } else {
                                    $cat = (strlen($key) % 2 == 0) ? 'country' : 'other'; 
                                }
                            }
                            
                            $merchant['key'] = $key;
                            $categorized_merchants[$cat][] = $merchant;
                            $categorized_merchants['all'][] = $merchant;
                        }
                        ?>

                        <!-- Top Header with Search and Filter -->
                        <div class="pfm-merchants-header">
                            <div class="pfm-merchants-search-bar">
                                <div class="pfm-search-input-wrap">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    <input type="text" id="pfm-merchant-search" placeholder="<?php echo esc_attr__( 'Search merchants…', 'rex-product-feed' ); ?>">
                                </div>
                                <div class="pfm-merchants-count">
                                    <span id="pfm-showing-count">Showing <?php echo count($categorized_merchants['all']); ?> / <?php echo count($categorized_merchants['all']); ?> Merchants</span>
                                </div>
                            </div>
                            
                            <div class="pfm-merchants-filters">
                                <button class="pfm-filter-btn active" data-filter="all"><?php echo esc_html__( 'All Merchants', 'rex-product-feed' ); ?> <span class="count"><?php echo count($categorized_merchants['all']); ?></span></button>
                                <button class="pfm-filter-btn" data-filter="country"><?php echo esc_html__( 'Country / Region', 'rex-product-feed' ); ?> <span class="count"><?php echo count($categorized_merchants['country']); ?></span></button>
                                <button class="pfm-filter-btn" data-filter="marketplace"><?php echo esc_html__( 'Marketplace', 'rex-product-feed' ); ?> <span class="count"><?php echo count($categorized_merchants['marketplace']); ?></span></button>
                                <button class="pfm-filter-btn" data-filter="search_engine"><?php echo esc_html__( 'Search Engine', 'rex-product-feed' ); ?> <span class="count"><?php echo count($categorized_merchants['search_engine']); ?></span></button>
                                <button class="pfm-filter-btn" data-filter="social_media"><?php echo esc_html__( 'Social Media', 'rex-product-feed' ); ?> <span class="count"><?php echo count($categorized_merchants['social_media']); ?></span></button>
                                <button class="pfm-filter-btn" data-filter="other"><?php echo esc_html__( 'Other Ecommerce', 'rex-product-feed' ); ?> <span class="count"><?php echo count($categorized_merchants['other']); ?></span></button>
                                <button class="pfm-filter-btn" data-filter="deal_sites"><?php echo esc_html__( 'Deal Sites', 'rex-product-feed' ); ?> <span class="count"><?php echo count($categorized_merchants['deal_sites']); ?></span></button>
                                <button class="pfm-filter-btn" data-filter="custom"><?php echo esc_html__( 'Custom', 'rex-product-feed' ); ?> <span class="count"><?php echo count($categorized_merchants['custom']); ?></span></button>
                            </div>
                        </div>

                        <div class="pfm-merchants-grid-container">
                            <?php
                            $groups = [
                                'social_media' => 'Social Media',
                                'marketplace' => 'Marketplace',
                                'search_engine' => 'Search Engine',
                                'other' => 'Other Ecommerce',
                                'deal_sites' => 'Deal Sites',
                                'country' => 'Country / Region',
                                'custom' => 'Custom'
                            ];

                            foreach ($groups as $cat_key => $cat_label) {
                                $merchants_in_cat = $categorized_merchants[$cat_key];
                                if (empty($merchants_in_cat)) continue;
                                ?>
                                <div class="pfm-merchant-group" data-category="<?php echo esc_attr($cat_key); ?>">
                                    <h4 class="pfm-merchant-group-title"><?php echo esc_html($cat_label); ?> <span class="count"><?php echo count($merchants_in_cat); ?></span></h4>
                                    <div class="pfm-merchants-grid">
                                        <?php
                                        foreach ($merchants_in_cat as $merchant) {
                                            $key = $merchant['key'];
                                            $show_pro = false;
                                            $link     = esc_url( admin_url( 'post-new.php?post_type=product-feed&rex_feed_merchant=' . $key ) );
                                            $target   = '_self';
                                            $pro_cls  = '';
                                            
                                            if ( !$is_premium_activated ) {
                                                if ( !isset( $merchant[ 'free' ] ) || !$merchant[ 'free' ] ) {
                                                    $pro_cls  = 'pfm-pro-merchant';
                                                    $show_pro = true;
                                                    $link     = esc_url( $pro_url );
                                                    $target   = '_blank';
                                                }
                                            }
                                            
                                            $merchant_name = !empty( $merchant['name'] ) ? $merchant['name'] : '';
                                            
                                            // Generate Logo
                                            $parts = explode(' ', $merchant_name);
                                            if(count($parts) > 1) {
                                                $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                                            } else {
                                                $initials = strtoupper(substr($merchant_name, 0, 2));
                                            }
                                            
                                            $colors = ['#05B6FF', '#2563EB', '#7C3AED', '#DB2777', '#DC2626', '#059669', '#D97706', '#4F46E5', '#BE185D', '#6B21A8'];
                                            $hash = crc32($merchant_name);
                                            $bg_color = $colors[abs($hash) % count($colors)];
                                            ?>
                                            <a href="<?php echo $link; ?>" target="<?php echo $target; ?>" class="pfm-merchant-card <?php echo esc_attr($pro_cls); ?>" data-search="<?php echo esc_attr(strtolower($merchant_name)); ?>" data-category="<?php echo esc_attr($cat_key); ?>">
                                                <div class="pfm-merchant-info">
                                                    <span class="pfm-merchant-name"><?php echo esc_html($merchant_name); ?></span>
                                                    <?php if ( $show_pro ) : ?>
                                                        <span class="pfm-pro-badge">PRO</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="pfm-merchant-action">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                                                </div>
                                            </a>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="pfm-merchants-no-result" style="display: none;">
                                <p><?php echo esc_html__( 'No merchants found matching your search.', 'rex-product-feed' ); ?></p>
                            </div>
                        </div>

                    </div>
                </div>
			</div>
			<!--/merchant tab-->

			<!--System Status-->
			<?php require_once plugin_dir_path( __FILE__ ) . 'rex-feed-system-status-markups.php'; ?>

			<div id="tab4" class="tab-content wpfm-log">
				<?php // Logs are already fetched at the top of the file ?>

				<div class="pfm-log-viewer">

					<!-- Left: file list (320 px) -->
					<div class="pfm-log-list">

						<!-- Severity filter chips (task 9.3) -->
						<div class="pfm-log-chips">
							<span class="pfm-chip" data-severity="CRITICAL" role="checkbox" aria-checked="true" tabindex="0">CRITICAL</span>
							<span class="pfm-chip" data-severity="ERROR"    role="checkbox" aria-checked="true" tabindex="0">ERROR</span>
							<span class="pfm-chip" data-severity="WARNING"  role="checkbox" aria-checked="true" tabindex="0">WARNING</span>
							<span class="pfm-chip" data-severity="INFO"     role="checkbox" aria-checked="true" tabindex="0">INFO</span>
						</div>

						<!-- File entries (task 9.2) -->
						<div class="pfm-log-files" role="listbox" aria-label="<?php echo esc_attr__( 'Log files', 'rex-product-feed' ); ?>">
							<?php if ( empty( $wpfm_logs ) ) : ?>
								<p class="pfm-log-no-files"><?php echo esc_html__( 'No log files found.', 'rex-product-feed' ); ?></p>
							<?php else : ?>
								<?php 
                                $is_first_log = true;
                                foreach ( $wpfm_logs as $log_key => $log_value ) :
									$file_path = defined( 'WC_LOG_DIR' ) ? WC_LOG_DIR . $log_value : '';
									$file_size = ( $file_path && file_exists( $file_path ) ) ? size_format( filesize( $file_path ) ) : '—';
									$file_date = ( $file_path && file_exists( $file_path ) ) ? date_i18n( get_option( 'date_format' ), filemtime( $file_path ) ) : '—';
                                    
                                    $log_type_class = 'INFO';
                                    if ( strpos( $log_value, 'fatal' ) !== false ) {
                                        $log_type_class = 'CRITICAL';
                                    } elseif ( strpos( $log_value, 'warning' ) !== false ) {
                                        $log_type_class = 'WARNING';
                                    } elseif ( strpos( $log_value, 'error' ) !== false ) {
                                        $log_type_class = 'ERROR';
                                    }

                                    $active_class = $is_first_log ? ' active' : '';
								?>
								<div class="pfm-log-file-entry<?php echo esc_attr( $active_class ); ?>"
									 data-log-key="<?php echo esc_attr( $log_value ); ?>"
									 role="option"
									 tabindex="0"
									 aria-label="<?php echo esc_attr( $log_value ); ?>">
									<div class="pfm-log-file-name" title="<?php echo esc_attr( $log_value ); ?>">
										<?php echo esc_html( $log_value ); ?>
									</div>
									<div class="pfm-log-file-meta"><?php echo esc_html( $file_date . ' · ' . $file_size ); ?></div>
								</div>
								<?php 
                                $is_first_log = false;
                                endforeach; ?>
							<?php endif; ?>
						</div>

					</div>
					<!-- /pfm-log-list -->

					<!-- Right: viewer pane -->
					<div class="pfm-log-content-pane">

						<!-- Toolbar -->
						<div class="pfm-log-toolbar" role="toolbar" aria-label="<?php echo esc_attr__( 'Log viewer controls', 'rex-product-feed' ); ?>">
                            <div class="pfm-log-viewing-status">
                                <span class="pfm-log-viewing-label"><?php echo esc_html__( 'VIEWING', 'rex-product-feed' ); ?></span>
                                <span class="pfm-log-viewing-filename">
                                    <?php 
                                    if ( !empty($wpfm_logs) ) {
                                        $first_log = reset($wpfm_logs);
                                        echo esc_html($first_log);
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="pfm-log-actions">
                                <div class="pfm-log-search-wrap">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    <input type="text"
                                           class="pfm-log-search-input"
                                           placeholder="<?php echo esc_attr__( 'Search in log...', 'rex-product-feed' ); ?>"
                                           aria-label="<?php echo esc_attr__( 'Search log content', 'rex-product-feed' ); ?>">
                                </div>
                                <button type="button" class="pfm-log-toolbar-btn pfm-log-copy-btn" aria-label="<?php echo esc_attr__( 'Copy visible lines', 'rex-product-feed' ); ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg> <?php echo esc_html__( 'Copy', 'rex-product-feed' ); ?>
                                </button>
                                <a href="#" class="pfm-log-toolbar-btn pfm-log-download-btn" aria-label="<?php echo esc_attr__( 'Download log file', 'rex-product-feed' ); ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> <?php echo esc_html__( 'Download', 'rex-product-feed' ); ?>
                                </a>
                            </div>
						</div>

						<!-- Log lines rendered by JS -->
						<div class="pfm-log-body" role="log" aria-live="polite" aria-label="<?php echo esc_attr__( 'Log content', 'rex-product-feed' ); ?>">
							<div class="pfm-log-no-file"><?php echo esc_html__( 'Select a log file to view its contents.', 'rex-product-feed' ); ?></div>
						</div>

						<div class="pfm-log-filter-empty" role="status">
							<?php echo esc_html__( 'No log entries match your filters.', 'rex-product-feed' ); ?>
						</div>

					</div>
					<!-- /pfm-log-content-pane -->

				</div>
				<!-- /pfm-log-viewer -->

				<!-- Preserve legacy hidden form for backward compatibility with existing JS -->
				<form id="wpfm-error-log-form" style="display:none;" action="<?php echo esc_url( admin_url( 'admin.php?page=wpfm_dashboard' ) ); ?>" method="post">
					<select id="wpfm-error-log" name="wpfm-error-log">
						<option value=""><?php echo esc_html__( 'Please Select', 'rex-product-feed' ); ?></option>
						<?php foreach ( $wpfm_logs as $key => $value ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></option>
						<?php endforeach; ?>
					</select>
				</form>
				<div id="log-viewer" style="display:none;"><pre id="wpfm-log-content"></pre></div>

			</div>
            <?php if ( !$is_premium_activated ) : ?>
                <div id="tab5" class="tab-content">
                    <div class="wpfm-free-pro-header">
                        <div class="wpfm-free-pro-title-wrap">
                            <h2 class="wpfm-free-pro-title"><?php echo esc_html__( 'Free vs Pro', 'rex-product-feed' ); ?></h2>
                            <p class="wpfm-free-pro-subtitle"><?php echo esc_html__( 'Side-by-side comparison of every feature available in the plugin. The Pro license unlocks unlimited products, the rules engine, premium templates, and more.', 'rex-product-feed' ); ?></p>
                        </div>
                        <div class="wpfm-free-pro-divider"></div>
                        <div class="wpfm-free-pro-legend-actions">
                            <div class="wpfm-free-pro-legend">
                                <span class="legend-item"><span class="legend-dot green"></span> <?php echo esc_html__( '4 Free', 'rex-product-feed' ); ?></span>
                                <span class="legend-item"><span class="legend-dot blue"></span> <?php echo esc_html__( '19 Pro', 'rex-product-feed' ); ?></span>
                                <span class="legend-item"><span class="legend-dot orange"></span> <?php echo esc_html__( '15 Pro only', 'rex-product-feed' ); ?></span>
                            </div>
                            <div class="wpfm-free-pro-filters">
                                <button class="pfm-fvp-filter-btn active" data-filter="all"><?php echo esc_html__( 'All features', 'rex-product-feed' ); ?></button>
                                <button class="pfm-fvp-filter-btn" data-filter="pro-only"><?php echo esc_html__( 'Pro only', 'rex-product-feed' ); ?></button>
                                <button class="pfm-fvp-filter-btn" data-filter="shared"><?php echo esc_html__( 'Shared', 'rex-product-feed' ); ?></button>
                            </div>
                        </div>
                    </div>

                    <div class="wpfm-compare">
                        <?php
                            $pro_url = add_query_arg('wpfm-dashboard', '1', 'https://rextheme.com/best-woocommerce-product-feed/pricing/');

                            $feature_sections = [
                                'CORE' => [
                                    ['name' => __('Products per batch', 'rex-product-feed'), 'desc' => __('Free is scoped at 200 products / batch.', 'rex-product-feed'), 'free' => true, 'pro' => true],
                                    ['name' => __('Allow private products', 'rex-product-feed'), 'free' => true, 'pro' => true],
                                    ['name' => __('Update WooCommerce variation child list that has no parent assigned', 'rex-product-feed'), 'free' => true, 'pro' => true],
                                    ['name' => __('Facebook Pixel', 'rex-product-feed'), 'free' => true, 'pro' => true],
                                    ['name' => __('Feeds for unlimited products', 'rex-product-feed'), 'desc' => __('Pro removes the 200-product / batch limit.', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('Custom daily time for feed auto-update', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                ],
                                'PRODUCT DATA' => [
                                    ['name' => __('Unique product identifiers (Brand, GTIN, MPN, UPC, EAN, JAN, ISBN, ITF-14, Offer Price, Effective Date, Additional Info)', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('Detailed product attributes (Size, Color, Pattern, Material, Age Group, Gender)', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('Exclude TAX from structured data prices', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('Fix WooCommerce (JSON-LD) structured data bug', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('Combined attributes', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('Feed rules engine', 'rex-product-feed'), 'desc' => __('Filter, replace and transform fields with conditional rules.', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                ],
                                'MARKETING PIXELS' => [
                                    ['name' => __('Google Dynamic Remarketing Pixel', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('TikTok Pixel', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                ],
                                'NOTIFICATIONS' => [
                                    ['name' => __('Email alert if a feed fails to generate', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                ],
                                'PREMIUM FEED TEMPLATES' => [
                                    ['name' => __('Google Product Review feed template', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('eBay MIP feed template', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('LeGuide.com feed template', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('Google Remarketing (DRM) feed template', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                ],
                                'FEED CREATION' => [
                                    ['name' => __('Create unlimited product feeds', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('Priority support', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('212+ merchant templates', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('Auto feed scheduling (custom time)', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                    ['name' => __('WooCommerce Subscriptions support', 'rex-product-feed'), 'free' => false, 'pro' => true],
                                ],
                            ];

                            $check_svg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                            $cross_svg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

                            echo '<div class="wpfm-compare__table">';
                                echo '<div class="wpfm-compare__table-wrapper">';

                                    foreach ($feature_sections as $section_title => $section_features) {
                                        echo '<div class="wpfm-compare-section">';
                                        echo '<div class="wpfm-compare-section-header">';
                                        echo '<h3 class="wpfm-compare-section-title">' . esc_html($section_title) . '</h3>';
                                        echo '<span class="wpfm-compare-section-count">' . count($section_features) . '</span>';
                                        echo '</div>';
                                        
                                        echo '<ul class="wpfm-compare__header">';
                                        echo '<li class="wpfm-compare__col wpfm-compare__col--feature">' . __('FEATURE', 'wpfm') . '</li>';
                                        echo '<li class="wpfm-compare__col wpfm-compare__col--free">' . __('FREE', 'wpfm') . '</li>';
                                        echo '<li class="wpfm-compare__col wpfm-compare__col--pro">' . __('PRO', 'wpfm') . '</li>';
                                        echo '</ul>';

                                        echo '<div class="wpfm-compare__body">';
                                            foreach ($section_features as $index => $feature) {
                                                $row_class = ($index % 2 == 0) ? 'even' : 'odd';
                                                $row_type  = ($feature['free'] && $feature['pro']) ? 'shared' : 'pro-only';
                                                echo '<ul class="wpfm-compare__feature ' . $row_class . '" data-type="' . $row_type . '">';
                                                    echo '<li class="wpfm-compare__col wpfm-compare__col--feature">';
                                                    echo '<p class="feature-name">' . $feature['name'] . '</p>';
                                                    if (!empty($feature['desc'])) {
                                                        echo '<p class="feature-desc">' . $feature['desc'] . '</p>';
                                                    }
                                                    echo '</li>';
                                                    echo '<li class="wpfm-compare__col wpfm-compare__col--free"><span class="wpfm-compare__icon">' . ($feature['free'] ? $check_svg : $cross_svg) . '</span></li>';
                                                    echo '<li class="wpfm-compare__col wpfm-compare__col--pro"><span class="wpfm-compare__icon">' . ($feature['pro'] ? $check_svg : '') . '</span></li>';
                                                echo '</ul>';
                                            }
                                        echo '</div>';
                                        echo '</div>';
                                    }

                                echo '</div>';

                                echo '<div class="wpfm-compare__footer unlock-banner">';
                                    echo '<div class="unlock-banner-content">';
                                        echo '<h3 class="unlock-title">' . esc_html__('Unlock the full potential', 'rex-product-feed') . '</h3>';
                                        echo '<p class="unlock-desc">' . esc_html__('Unlimited products, feed rules, premium templates and email alerts when feeds fail.', 'rex-product-feed') . '</p>';
                                    echo '</div>';
                                    echo '<div class="unlock-banner-stats">';
                                        echo '<div class="stat-item"><span class="stat-value">15+</span><span class="stat-label">Pro features</span></div>';
                                        echo '<div class="stat-item"><span class="stat-value">∞</span><span class="stat-label">Products / batch</span></div>';
                                    echo '</div>';
                                    echo '<div class="wpfm-compare__footer-btn">';
                                        echo '<a class="wpfm-btn wpfm-btn-pro-upgrade" href="' . esc_url($pro_url) . '" title="' . esc_attr__('Upgrade to Pro', 'wpfm') . '" target="_blank">';
                                            echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 7-7 7 7"></path><path d="M12 19V5"></path></svg> ' . esc_html__('Upgrade to Pro', 'wpfm');
                                        echo '</a>';
                                    echo '</div>';
                                echo '</div>';
                            echo '</div>';
                        ?>
                    </div>
                </div>
            <?php endif; ?>

		</div>


	</div>


</section>

