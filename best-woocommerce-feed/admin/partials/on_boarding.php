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
	<div class="rex-onboarding__header">
		<h2 class="rex-onboarding__title"><?php echo esc_html__('Settings', 'rex-product-feed'); ?></h2>
	</div>

	<div class="rex-onboarding__tab-wrapper">
		<nav class="rex-settings__nav-items">
			<ul class="rex-settings__tabs" role="tablist">
				<li class="rex-settings__tab active" role="tab" data-tab="tab1">
					<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/controls.php'; ?>
					<span><?php echo esc_html__('Controls', 'rex-product-feed'); ?></span>
				</li>

				<li class="rex-settings__tab merchant" role="tab" data-tab="tab2">
					<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/merchants.php'; ?>
					<span><?php echo esc_html__('Merchants', 'rex-product-feed'); ?></span>
				</li> 

				<li class="rex-settings__tab" role="tab" data-tab="tab3">
					<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/status.php'; ?>
					<span><?php echo esc_html__('System Status', 'rex-product-feed'); ?></span>
				</li>

				<li class="rex-settings__tab" role="tab" data-tab="tab4">
					<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/logs.php'; ?>
					<span><?php echo esc_html__('Logs', 'rex-product-feed'); ?></span>
				</li>

				<?php if (!$is_premium_activated) : ?>
				<li class="rex-settings__tab rex-settings__tab--free-pro" role="tab" data-tab="tab5">
					<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/free-vs-pro.php'; ?>
					<span><?php echo esc_html__('Free vs Pro', 'rex-product-feed'); ?></span>
				</li>
				<?php endif; ?>
			</ul>
		</nav>


		<!-- Tab content section with appropriate semantics -->
		<div class="rex-settings__tab-contents">
			<div id="tab1" class="tab-content active">
                <h3 class="merchant-title"><?php echo esc_html__( 'Controls', 'rex-product-feed' ); ?> </h3>
                <div class="feed-settings">
                    <div class="single-merchant product-batch">
                        <div class="">
                            <span class="title"><?php echo sprintf( esc_html__( 'Products per batch', 'rex-product-feed' ), esc_html( WPFM_FREE_MAX_PRODUCT_LIMIT ) );?></span>
                            <p><?php echo esc_html__( 'Free users cannot generate more than %d products. For free users it will run only 1 batch', 'rex-product-feed' ); ?></p>
                        </div>

                        <div class="switch">
                            <form id="wpfm-per-batch" class="wpfm-per-batch">
                                <input id="wpfm_product_per_batch" type="number" name="wpfm_product_per_batch"
                                       value="<?php echo esc_attr( $per_batch ); ?>"
                                       min="1" max="<?php echo !$is_premium_activated ? esc_attr( WPFM_FREE_MAX_PRODUCT_LIMIT ) : esc_attr( 500 ); ?>">
                                <button type="submit" class="save-batch">
                                    <span><?php _e( 'Save', 'rex-product-feed' );?></span>
                                    <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="single-merchant wpfm-clear-btn">
                        <span class="title"><?php echo esc_html__( 'Clear Batch (Remove all)', 'rex-product-feed' ); ?></span>
                        <button class="wpfm-clear-batch" id="wpfm-clear-batch">
							<span>
								<?php echo esc_html__( 'Clear Batch', 'rex-product-feed' ); ?>
							</span>
                            <i class="fa fa-spinner fa-pulse fa-fw"></i>
                        </button>
                    </div>

                    <div class="single-merchant detailed-product  purge-cache">
                        <span class="title"><?php echo esc_html__( 'Purge Cache', 'rex-product-feed' ); ?></span>
                        <button id="wpfm-purge-cache" class="wpfm-purge-cache">
                            <span><?php echo esc_html__( 'Purge Cache', 'rex-product-feed' ); ?></span>
                            <i class="fa fa-spinner fa-pulse fa-fw"></i>
                        </button>
                    </div>

                    <div class="single-merchant update-list">
                        <span class="title"><?php echo esc_html__( 'Update WooCommerce variation child list that has no parent assigned (abandoned child)', 'rex-product-feed' ); ?></span>
                        <button id="rex_feed_abandoned_child_list_update_button" class="rex-feed-abandoned-child-list-update-button">
                            <span><?php echo esc_html__( 'Update List', 'rex-product-feed' ); ?></span>
                            <i class="fa fa-spinner fa-pulse fa-fw"></i>
                        </button>
                    </div>

                    <div class="single-merchant detailed-product detailed-merchants">
                        <span class="title"><?php echo esc_html__( 'WPFM cache TTL', 'rex-product-feed' ); ?></span>
                        <div class="wpfm-dropdown">
                            <form id="wpfm-transient-settings" class="wpfm-transient-settings">
                                <div class="wpfm-cache-ttl-area">
                                    <select id="wpfm_cache_ttl" name="wpfm_cache_ttl">
                                        <?php foreach ( $schedule_hours as $key => $label ) { ?>
                                            <option value="<?php echo esc_attr( (int) $key * $hour_in_seconds ); ?>" <?php selected( $wpfm_cache_ttl, (int) $key * $hour_in_seconds ); ?>><?php echo esc_attr( $label ); ?></option>
                                        <?php } ?>
                                    </select>

                                    <button type="submit" class="save-transient-button">
                                        <span><?php echo esc_html__( 'Save', 'rex-product-feed' ); ?></span>
                                        <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                    </button>
                                </div>
                                <span class="helper-text"><?php echo esc_html__( 'When the cache will be expired.', 'rex-product-feed' ); ?></span>
                            </form>
                        </div>
                    </div>

                    <div class="single-merchant remove-plugin-data">
						<span class="title">
							<?php echo esc_html__( 'Remove All Plugin Data on Plugin Uninstallation', 'rex-product-feed' ); ?>
						</span>
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

                    <div class="single-merchant enable-log">
						<span class="title">
							<?php echo esc_html__( 'Enable log', 'rex-product-feed' ); ?>
						</span>
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

                    <div class="single-merchant hide-character">
						<span class="title">
							<?php echo esc_html__( 'Hide Character Limit Column', 'rex-product-feed' ); ?>
						</span>
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

                    <?php do_action( 'rex_feed_after_log_enable_button_field' ); ?>

                    <div class="single-merchant detailed-product rex-feed-rollback">
                        <span class="title"><?php echo esc_html__( 'Rollback to Older Version', 'rex-product-feed' ); ?></span>
                        <div class="wpfm-dropdown">
                            <div class="wpfm-rollback-option-area">
                                <select id="wpfm_rollback_options" name="wpfm_rollback_options">
                                    <?php
                                    foreach ( $rollback_versions as $version ) {
                                        echo "<option value='" . esc_attr( $version ) . "'>" . esc_html( $version ) . "</option>";
                                    }
                                    ?>
                                </select>

                                <?php
                                echo sprintf(
                                    '<button type="button" data-placeholder-text="' . esc_html__( 'Reinstall', 'rex-product-feed' ) . ' v{VERSION}" data-placeholder-url="%s" class="rex-feed-button-spinner rex-feed-rollback-button">%s</button>',
                                    esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=rex_feed_rollback&version=VERSION' ), 'rex_feed_rollback' ) ),
                                    esc_html__( 'Reinstall', 'rex-product-feed' )
                                );
                                ?>
                            </div>
                            <span class="helper-text"><?php echo __( 'Warning: Please back up your database before making the rollback as you might lose you previous data.', 'rex-product-feed' );// phpcs:ignore ?></span>
                        </div>

                    </div>

                    <div class="single-merchant unique-product <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>">
                        <?php if ( !$is_premium_activated ) { ?>
                            <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                               class="wpfm-pro-cta">
                                <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                            </a>
                        <?php } ?>
                        <div class="single-merchant-pro">
							<span class="title">
								<?php
                                echo esc_html__(
                                        "Add Unique Product Identifiers ( Brand, GTIN, MPN, UPC, EAN, JAN, ISBN, ITF14,"
                                    ) . "<br>" .
                                    esc_html__(
                                        "Offer price, Offer effective date, Additional info ) to product",
                                        'rex-product-feed'
                                    );
                                ?>
							</span>

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

                    <?php do_action( 'rex_feed_after_upi_enable_field' ); ?>

                    <div class="single-merchant detailed-product <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>">
                        <?php if ( !$is_premium_activated ) { ?>
                            <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                               class="wpfm-pro-cta">
                                <span class="wpfm-pro-tag"><?php esc_html_e( 'pro', 'rex-product-feed' ); ?></span>
                            </a>
                        <?php } ?>

                        <div class="single-merchant-pro">
                            <span class="title"><?php esc_html_e( 'Add Detailed Product Attributes ( Size, Color, Pattern, Material, Age group, Gender ) to product', 'rex-product-feed' ); ?></span>
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

                    <div class="single-merchant exclude-tax <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>">
                        <?php if ( !$is_premium_activated ) { ?>
                            <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                               class="wpfm-pro-cta">
                                <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                            </a>
                        <?php } ?>

                        <div class="single-merchant-pro">

							<span class="title">
								<?php echo esc_html__( 'Exclude TAX from structured data prices', 'rex-product-feed' ); ?>
							</span>
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

                    <div class="single-merchant detailed-product">
                        <span class="title"><?php esc_html_e( 'Allow Private Products', 'rex-product-feed' ); ?></span>
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


                    <div class="single-merchant increase-product <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>">
                        <?php if ( !$is_premium_activated ) { ?>
                            <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                               class="wpfm-pro-cta">
                                <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                            </a>
                        <?php } ?>

                        <div class="single-merchant-pro">
							<span class="title">
							<?php
                            echo esc_html__(
                                    "Increase the number of products that will be approved in Google's Merchant Center: This option will fix"
                                ) . "<br>" .
                                esc_html__(
                                    "WooCommerce's (JSON-LD) structured data bug and add extra structured data elements to your pages",
                                    'rex-product-feed'
                                );
                            ?>
							</span>

                            <div class="switch">
                                <?php
                                if ( !$is_premium_activated ) {
                                    $disabled = 'disabled';
                                    $checked  = '';
                                }
                                else {
                                    $checked = 'yes' === $structured_data ? 'checked' : '';
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

                    <div class="single-merchant fb-pixel">
                        <div class="single-merchant-fb-pixel-pro">
							<span class="title">
								<?php echo esc_html__( 'Enable Facebook Pixel', 'rex-product-feed' ); ?>
							</span>
                            <div class="switch">
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

                        </div>

                        <div class="single-merchant wpfm-fb-pixel-field <?php echo esc_attr( $hidden_class ); ?>">
                            <span class="title"><?php echo esc_html__( 'Facebook Pixel ID', 'rex-product-feed' ); ?></span>
                            <div class="switch">
                                <form id="wpfm-fb-pixel" class="wpfm-fb-pixel" style="width: 300px;">
                                    <input id="wpfm_fb_pixel" type="text" name="wpfm_fb_pixel"
                                           value="<?php echo esc_attr( $wpfm_fb_pixel_data ); ?>" style="width: 200px;">
                                    <button type="submit" class="save-fb-pixel"><span><?php echo esc_html__( 'Save', 'rex-product-feed' ); ?></span>
                                        <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>

                    <div class="single-merchant tiktok-pixel <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>">
                        <?php if ( !$is_premium_activated ) { ?>
                            <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                               class="wpfm-pro-cta">
                                <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                            </a>
                        <?php } ?>
                        <div class="single-merchant-tiktok-pixel-pro">
							<span class="title">
								<?php echo esc_html__( 'Enable Tiktok Pixel', 'rex-product-feed' ); ?>
							</span>
                            <div class="switch">
                                <?php
                                $disabled = '';
                                $checked  = 'yes' === $wpfm_tiktok_pixel_enabled ? 'checked' : '';
                                if ( !$is_premium_activated ) {
                                    $disabled = 'disabled';
                                    $checked  = '';
                                }
                                ?>
                                <div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
                                    <input class="switch-input" type="checkbox" id="wpfm_tiktok_pixel" <?php echo esc_attr( $checked ); ?>>
                                    <label class="lever" for="wpfm_tiktok_pixel"></label>
                                </div>
                            </div>

                        </div>

                        <?php do_action( 'rexfeed_after_tiktok_pixel_field' );?>

                    </div>

                    <?php do_action( 'rexfeed_after_tiktok_pixel_fields' );?>


                    <div class="single-merchant google-drm-pixel <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>">
                        <?php
                        /**
                         * Apply filter to enable or disable Google DRM (Digital Rights Management) pixel.
                         *
                         * This method applies the 'rexfeed_enable_google_drm_pixel' filter hook to determine
                         * whether the Google DRM pixel should be enabled or disabled for a specific context.
                         *
                         * @param bool $default_value The default value for enabling Google DRM pixel (true/false).
                         * @return bool The filtered value to enable or disable Google DRM pixel.
                         *
                         * @since 7.4.5
                         */
                        $enable_google_drm = apply_filters( 'rexfeed_enable_google_drm_pixel', false );
                        ?>
                        <?php if ( !$is_premium_activated ) { ?>
                            <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                               class="wpfm-pro-cta">
                                <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                            </a>
                        <?php } ?>

                        <div class="single-merchant-pro">
							<span class="title">
								<?php echo esc_html__( 'Enable Google Dynamic Remarketing Pixel', 'rex-product-feed' ); ?>
							</span>
                            <div class="switch">
                                <?php
                                $disabled = '';
                                $checked  = 'yes' === get_option( 'wpfm_google_drm_pixel_enabled', 'no' ) ? 'checked' : '';
                                if ( !$enable_google_drm ) {
                                    $disabled = 'disabled';
                                    $checked  = '';
                                }
                                ?>
                                <div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
                                    <input class="switch-input" type="checkbox" id="wpfm_google_drm_pixel" <?php echo esc_attr( $checked ); echo esc_attr( $disabled ); ?>>
                                    <label class="lever" for="wpfm_google_drm_pixel"></label>
                                </div>
                            </div>
                        </div>

                        <?php do_action( 'rexfeed_after_google_drm_pixel_field' );?>

                    </div>

                    <?php do_action( 'rexfeed_after_google_drm_pixel_fields' );?>

                    <div class="single-merchant">
                        <?php if ( !$is_premium_activated ) { ?>
                            <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
                               class="wpfm-pro-cta">
                                <span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
                            </a>
                        <?php } ?>
                        <div class="single-merchant-pro">
                            <span class="title"><?php echo esc_html__( 'Get email notification if your feed is not generated properly', 'rex-product-feed' ); ?></span>
                            <div class="switch">
                                <form id="wpfm-user-email" class="wpfm-fb-pixel" style="width: 300px;" disabled>
                                    <input class="<?php echo !$is_premium_activated ? 'rexfeed-pro-disabled' : ''; ?>" placeholder="user@email.com" id="wpfm_user_email" type="text" name="wpfm_user_email" value="<?php echo esc_attr( $current_user_email ); ?>" style="width: 200px;<?php echo !$is_premium_activated ? ' cursor:pointer;' : ''; ?>">
                                    <button type="submit" class="save-user-email <?php echo !$is_premium_activated ? 'rexfeed-pro-disabled' : ''; ?>" <?php echo !$is_premium_activated ? ' style="background-color: #f2f2f8; color: #d9d9db;" ' : ''; ?>>
                                        <span><?php echo esc_html__( 'Save', 'rex-product-feed' ); ?></span>
                                        <i class="fa fa-spinner fa-pulse fa-fw"></i>
                                    </button>
                                </form>
                            </div>
                        </div>





                    </div>

                </div>
               
			</div>
			<!--/settings tab-->

			<div id="tab2" class="tab-content">
				<div class="rex-settings__merchant">

					<div class="rex-settings__merchant-header">
						<h3 class="merchant-title"><?php echo esc_html__( 'Supported Merchants', 'rex-product-feed' ); ?></h3>
						<div class="rex-settings__search-container">
							<input type="text" id="search" class="search-input" placeholder="Search Your Merchant">
							<span class="rex-settings__search-icons" id="search-button">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
								<path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
								<path d="M19 19L14.65 14.65" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
								</svg>
							</span>
						</div>
					</div>

					<div class="rex-settings__merchant-lists" id="rex-settings__merchant-lists">
						<?php
						// Free vs pro merchants.
						$all_merchants = Rex_Feed_Merchants::get_merchants();
						$_merchants    = !empty( $all_merchants[ 'popular' ] ) ? $all_merchants[ 'popular' ] : array();

						if ( !$is_premium_activated ) {
							$_merchants = !empty( $all_merchants[ 'pro_merchants' ] ) ? array_merge( $_merchants, $all_merchants[ 'pro_merchants' ] ) : $_merchants;
						}

						$_merchants = !empty( $all_merchants[ 'free_merchants' ] ) ? array_merge( $_merchants, $all_merchants[ 'free_merchants' ] ) : $_merchants;

						// Result of bad planning.
						$_merchants[ 'google' ][ 'name' ]    = 'Google Shopping';
						$_merchants[ 'google_Ad' ][ 'name' ] = 'Google AdWords';
						$_merchants[ 'drm' ][ 'name' ]       = 'Google Remarketing (DRM)';

							foreach ( $_merchants as $key => $merchant ) {
								if ( $key ) {
									$show_pro = false;
									$style    = '';
									if ( $is_premium_activated ) {
										$pro_cls  = '';
										$disabled = '';
									}
									else {
										if ( isset( $merchant[ 'free' ] ) && $merchant[ 'free' ] ) {
											$pro_cls  = '';
											$disabled = '';
										}
										else {
											$pro_cls  = 'wpfm-pro';
											$disabled = 'disabled';
											$show_pro = true;
											$style    = 'style="pointer-events: none"';
										}
									}
									?>
										<div class="single-merchant <?php echo esc_attr( $pro_cls ); ?>">
											<?php if ( $show_pro ) { ?>
												<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank"
													title="Click to Upgrade Pro" class="wpfm-pro-cta">
													<span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
												</a>
											<?php } ?>

											<div class="single-merchant__pro">
												<span class="title">
													<?php
													$merchant_name = !empty( $merchant['name'] ) ? $merchant['name'] : '';
													echo esc_html( $merchant_name );
													?>
												</span>

												<button class="single-merchant__button" type="button">
													<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product-feed&rex_feed_merchant=' . $key ) ); ?>" target="_self" <?php echo wp_kses( $style, wp_kses_allowed_html( 'post' ) ); ?>><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.0253 7.79616C11.5635 7.79616 11.9998 7.35985 11.9998 6.82164V0.97452C11.9998 0.436312 11.5635 0 11.0253 0H5.17466C4.63644 0 4.20014 0.436303 4.20014 0.97452C4.20014 1.51273 4.63644 1.94904 5.17466 1.94904H8.6728L0.285427 10.3363C-0.0951424 10.7169 -0.0951424 11.334 0.285427 11.7145C0.666046 12.0952 1.28301 12.0952 1.66356 11.7145L10.0508 3.32737V6.82164C10.0508 7.35985 10.4871 7.79616 11.0253 7.79616Z" fill="#216DF0" fill-opacity="0.4"/></svg></a>
												</button>
											</div>

										</div>
									<?php
								}
							}
						?>

					</div>

				</div>
			</div>
			<!--/merchant tab-->

			<!--System Status-->
			<?php require_once plugin_dir_path( __FILE__ ) . 'rex-feed-system-status-markups.php'; ?>

			<div id="tab4" class="tab-content  wpfm-log">
				<h3 class="wpfm-logo-title"><?php echo esc_html__( 'Logs', 'rex-product-feed' ); ?> </h3>
				<?php
				$logs      = WC_Admin_Status::scan_log_files();
				$wpfm_logs = array();

				$pattern = '/^wpfm|fatal/';
				foreach ( $logs as $key => $value ) {
					if ( preg_match( $pattern, $key ) ) {
						$wpfm_logs[ $key ] = $value;
					}
				}
				echo '<form id="wpfm-error-log-form" action="' . esc_url( admin_url( 'admin.php?page=wpfm_dashboard' ) ) . '" method="post">';
				echo '<select id="wpfm-error-log" name="wpfm-error-log">';
				echo '<option value="">'. __( 'Please Select', 'rex-product-feed' ) .'</option>';
				foreach ( $wpfm_logs as $key => $value ) {
					echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $value ) . '</option>';
				}
				echo '<select>';
				echo '<button type="submit">' . esc_html__( 'View log', 'rex-product-feed' ) . '</button>';
				echo '</form>';

				echo '<div id="log-viewer">';
				echo '<button id="wpfm-log-copy" class="btn-default" style="display: none"> <i class="fa fa-files-o"></i>' . esc_html__( 'Copy log', 'rex-product-feed' ) . '</button>';
				echo '<pre id="wpfm-log-content"></pre>';
				echo '</div>';
				?>
			</div>

			<div id="tab5" class="tab-content">

				<?php if ( !$is_premium_activated ) : ?>

					<h3 class="free-vs-pro-title"><?php echo esc_html__( 'Free Vs. Pro Features', 'rex-product-feed' ); ?></h3>
				
					<div class="wpfm-compare">
						<?php
							$pro_url = add_query_arg('wpfm-dashboard', '1', 'https://rextheme.com/best-woocommerce-product-feed/pricing/');
							
							$features = [
								__('Products per batch', 'rex-product-feed'),
								__('Allow Private Products', 'rex-product-feed'),
								__('Facebook Pixel', 'rex-product-feed'),
								__('Update WooCommerce variation child list that has no parent assigned', 'rex-product-feed'),
								__('Feeds for Unlimited Products', 'rex-product-feed'),
								__('Feed Rules', 'rex-product-feed'),
								__('Custom daily time for feed auto update', 'rex-product-feed'),
								__('Combined Attributes', 'rex-product-feed'),
								__('Unique Product Identifiers (Brand, GTIN, MPN, UPC, EAN, JAN, ISBN, ITF14, Offer price, Offer effective date, Additional info)', 'rex-product-feed'),
								__('Add Detailed Product Attributes', 'rex-product-feed'),
								__('Exclude TAX from structured data prices', 'rex-product-feed'),
								__('Fix WooCommerce (JSON-LD) structured data bug', 'rex-product-feed'),
								__('Google Dynamic Remarketing Pixel', 'rex-product-feed'),
								__('TikTok Pixel', 'rex-product-feed'),
								__('Get email notification if feed is not generated properly', 'rex-product-feed'),
								__('Google Product Review Feed Template', 'rex-product-feed'),
								__('eBay MIP Feed Template', 'rex-product-feed'),
								__('LeGuide.com Feed Template', 'rex-product-feed'),
								__('Google Remarketing (DRM) Feed Template', 'rex-product-feed')
							];

							$free_icons = array_fill(0, count($features), 'cross-list');
							$free_icons[0] = 'check';
							$free_icons[1] = 'check';
							$free_icons[2] = 'check';
							$free_icons[3] = 'check';

							echo '<div class="wpfm-compare__table">';
								echo '<div class="wpfm-compare__table-wrapper">';

									echo '<ul class="wpfm-compare__header">';
									echo '<li class="wpfm-compare__col wpfm-compare__col--feature">' . __('Features Name', 'wpfm') . '</li>';
									echo '<li class="wpfm-compare__col wpfm-compare__col--free">' . __('Free', 'wpfm') . '</li>';
									echo '<li class="wpfm-compare__col wpfm-compare__col--pro">' . __('Pro', 'wpfm') . '</li>';
									echo '</ul>';

									echo '<div class="wpfm-compare__body">';

										foreach ($features as $index => $feature) {
											echo '<ul class="wpfm-compare__feature">';
												echo '<li class="wpfm-compare__col wpfm-compare__col--feature"><p>' . $feature . '</p></li>';
												echo '<li class="wpfm-compare__col wpfm-compare__col--free"><span class="wpfm-compare__icon wpfm-compare__icon--' . $free_icons[$index] . '"><img loading="lazy" src="' . WPFM_PLUGIN_DIR_URL . 'admin/assets/icon/icon-svg/' . $free_icons[$index] . '.svg" alt="' . $free_icons[$index] . '"></span></li>';
												echo '<li class="wpfm-compare__col wpfm-compare__col--pro"><span class="wpfm-compare__icon wpfm-compare__icon--check"><img loading="lazy" src="' . WPFM_PLUGIN_DIR_URL . 'admin/assets/icon/icon-svg/check.svg" alt="check-mark"></span></li>';
											echo '</ul>';
										}

									echo '</div>';

								echo '</div>';

								echo '<div class="wpfm-compare__footer">';
									echo '<div class="wpfm-compare__footer-btn">';
										echo '<a class="wpfm-compare__btn wpfm-compare__btn--pro" href="' . esc_url($pro_url) . '" title="' . esc_attr__('Upgrade to Pro', 'wpfm') . '" target="_blank">';
											echo esc_html__('Upgrade to Pro', 'wpfm');
										echo '</a>';
									echo '</div>';
								echo '</div>';
							echo '</div>';
						?>
					</div>
					
				<?php endif; ?>

			</div>

		</div>

	</div>


</section>

