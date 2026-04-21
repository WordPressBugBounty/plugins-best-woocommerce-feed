<?php
/**
 * Setup wizard view — redesigned 3-step wizard
 *
 * @package ''
 * @since 7.4.14
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e( 'Product Feed Manager - Setup Wizard', 'rex-product-feed' ); ?></title>
    <?php
    do_action( 'admin_enqueue_scripts' );
    do_action( 'admin_print_styles' );
    do_action( 'admin_head' );
    ?>
    <script type="text/javascript">
        var ajaxurl  = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
        var pfmNonce = '<?php echo wp_create_nonce( 'rex-product-feed' ); ?>';
    </script>
</head>
<body>

<div id="pfm-wizard-app">

    <!-- ===================== STEP 1: Welcome + Merchant ===================== -->
    <section class="pfm-wizard-step active" id="pfm-step-1">
        <div class="pfm-s1-wrap">

            <div class="pfm-s1-logo">
                <img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/pfm.webp' ); ?>" alt="Product Feed Manager">
            </div>

            <div class="pfm-step-indicator">
                <div class="pfm-step-dots">
                    <span class="pfm-dot pfm-dot-active"></span>
                    <span class="pfm-dot"></span>
                    <span class="pfm-dot"></span>
                </div>
                <span class="pfm-step-label"><?php esc_html_e( 'Step 1 of 3', 'rex-product-feed' ); ?></span>
            </div>

            <h1 class="pfm-s1-title"><?php esc_html_e( 'Set up your first product feed', 'rex-product-feed' ); ?></h1>
            <p class="pfm-s1-subtitle"><?php esc_html_e( 'Connect your WooCommerce products to 200+ channels', 'rex-product-feed' ); ?></p>

            <p class="pfm-popular-label"><?php esc_html_e( 'Popular Channels', 'rex-product-feed' ); ?></p>
            <div class="pfm-popular-grid" id="pfmPopularGrid"></div>

            <div class="pfm-search-wrap">
                <div class="pfm-search-inner">
                    <svg class="pfm-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#76708c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" id="pfmMerchantSearch" placeholder="<?php esc_attr_e( 'Search 200+ channels...', 'rex-product-feed' ); ?>" />
                </div>
                <div id="pfmSearchResultsWrap" class="pfm-search-results-wrap" style="display:none;">
                    <div id="pfmSearchResults" class="pfm-search-results-grid"></div>
                </div>
            </div>

            <div class="pfm-s1-footer">
                <button class="pfm-btn-primary pfm-btn-lg" id="pfmStep1ContinueBtn" disabled>
                    <?php esc_html_e( 'Continue', 'rex-product-feed' ); ?> &rarr;
                </button>
                <a href="#" class="pfm-remind-later" id="pfmRemindLaterBtn">
                    <?php esc_html_e( 'remind me later', 'rex-product-feed' ); ?>
                </a>
            </div>

        </div>
    </section>

    <!-- ===================== STEP 2: Smart Configuration ===================== -->
    <section class="pfm-wizard-step" id="pfm-step-2">
        <div class="pfm-s2-wrap">

            <div class="pfm-step-indicator">
                <div class="pfm-step-dots">
                    <span class="pfm-dot pfm-dot-done"></span>
                    <span class="pfm-dot pfm-dot-active"></span>
                    <span class="pfm-dot"></span>
                </div>
                <span class="pfm-step-label"><?php esc_html_e( 'Step 2 of 3', 'rex-product-feed' ); ?></span>
            </div>

            <h2 class="pfm-s2-title" id="pfmConfigHeading"><?php esc_html_e( 'Setting up your feed', 'rex-product-feed' ); ?></h2>

            <div class="pfm-field-group">
                <label class="pfm-field-label" for="pfmFeedNameInput"><?php esc_html_e( 'Feed Name', 'rex-product-feed' ); ?></label>
                <input type="text" id="pfmFeedNameInput" class="pfm-field-input" />
            </div>

            <div class="pfm-autoconfig-section">
                <p class="pfm-autoconfig-intro"><?php esc_html_e( "We're configuring everything else automatically:", 'rex-product-feed' ); ?></p>
                <div class="pfm-autoconfig-item">
                    <span class="pfm-autoconfig-check">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="8" r="8" fill="#239654"/><path d="M4.5 8L6.8 10.5L11.5 5.5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span><?php esc_html_e( 'Update frequency — Daily', 'rex-product-feed' ); ?></span>
                </div>
                <div class="pfm-autoconfig-item">
                    <span class="pfm-autoconfig-check">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="8" r="8" fill="#239654"/><path d="M4.5 8L6.8 10.5L11.5 5.5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span id="pfmFormatConfirmText"><?php esc_html_e( 'Feed format — XML', 'rex-product-feed' ); ?></span>
                </div>
            </div>

            <div class="pfm-mapping-section" id="pfmMappingSection">
                <p class="pfm-mapping-status" id="pfmMappingStatus"><?php esc_html_e( 'Mapping your product attributes...', 'rex-product-feed' ); ?></p>
                <div class="pfm-mapping-bar-track">
                    <div class="pfm-mapping-bar-fill" id="pfmMappingBarFill"></div>
                </div>
                <p class="pfm-mapping-detail" id="pfmMappingDetail"><?php esc_html_e( 'Analyzing products...', 'rex-product-feed' ); ?></p>
            </div>

            <div class="pfm-nav-row">
                <button class="pfm-btn-back" id="pfmStep2BackBtn">&larr; <?php esc_html_e( 'Back', 'rex-product-feed' ); ?></button>
                <button class="pfm-btn-primary" id="pfmCreateFeedBtn"><?php esc_html_e( 'Create My Feed', 'rex-product-feed' ); ?> &rarr;</button>
            </div>

        </div>
    </section>

    <!-- ===================== STEP 3: Aha Moment + Consent ===================== -->
    <section class="pfm-wizard-step" id="pfm-step-3">
        <div class="pfm-s3-wrap">

            <div class="pfm-step-indicator">
                <div class="pfm-step-dots">
                    <span class="pfm-dot pfm-dot-done"></span>
                    <span class="pfm-dot pfm-dot-done"></span>
                    <span class="pfm-dot pfm-dot-active"></span>
                </div>
                <span class="pfm-step-label"><?php esc_html_e( 'Step 3 of 3', 'rex-product-feed' ); ?></span>
            </div>

            <!-- Hero -->
            <div class="pfm-aha-hero">
                <div class="pfm-aha-checkmark">
                    <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="32" r="32" fill="#DCFCE7"/>
                        <circle cx="32" cy="32" r="22" fill="#239654"/>
                        <path d="M21 32L28 39L43 24" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2 class="pfm-aha-title" id="pfmAhaTitle"><?php esc_html_e( 'Your Feed is Ready!', 'rex-product-feed' ); ?></h2>
                <p class="pfm-aha-feed-name" id="pfmAhaFeedName"></p>
            </div>

            <!-- Feed URL Card -->
            <div class="pfm-feed-url-card">
                <div class="pfm-feed-url-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                    <?php esc_html_e( 'Feed URL', 'rex-product-feed' ); ?>
                </div>
                <div class="pfm-feed-url-row">
                    <input type="text" id="pfmFeedUrlInput" class="pfm-feed-url-input" readonly />
                    <div class="pfm-feed-url-actions">
                        <button class="pfm-url-btn" id="pfmCopyUrlBtn"><?php esc_html_e( 'Copy', 'rex-product-feed' ); ?></button>
                        <a href="#" class="pfm-url-btn pfm-url-open-btn" id="pfmOpenUrlBtn" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open', 'rex-product-feed' ); ?> &#8599;</a>
                    </div>
                </div>
            </div>

            <!-- Propagation notice -->
            <div class="pfm-propagation-notice">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4B9EE8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span id="pfmPropagationText"><?php esc_html_e( 'Your feed URL is ready. Submit to your merchant account to start listing your products. The feed file refreshes automatically every day.', 'rex-product-feed' ); ?></span>
            </div>

            <hr class="pfm-divider" />

            <!-- Consent checkbox -->
            <label class="pfm-consent-row" for="pfmConsentCheckbox">
                <input type="checkbox" id="pfmConsentCheckbox" checked />
                <span class="pfm-consent-box"></span>
                <span class="pfm-consent-text"><?php esc_html_e( 'Send me tips on optimizing this feed for more sales.', 'rex-product-feed' ); ?></span>
            </label>

            <!-- CTAs -->
            <div class="pfm-aha-ctas">
                <button class="pfm-btn-primary" id="pfmGoToDashboardBtn"><?php esc_html_e( 'Go to my feed dashboard', 'rex-product-feed' ); ?></button>
                <!-- <button class="pfm-btn-text-link" id="pfmSkipForNowBtn"><?php esc_html_e( 'Skip for now', 'rex-product-feed' ); ?></button> -->
            </div>

            <hr class="pfm-divider" />

            <!-- Upsells (below fold) -->
            <div class="pfm-upsell-section">
                <div class="pfm-upsell-header-row">
                    &#9660; <?php esc_html_e( 'Grow your store further', 'rex-product-feed' ); ?>
                </div>
                <div class="pfm-upsell-cards">

                    <!-- WPFunnels -->
                    <div class="pfm-upsell-card pfm-upsell-featured" id="pfmWpfCard">
                        <span class="pfm-upsell-badge"><?php esc_html_e( 'HIGHLY RECOMMENDED', 'rex-product-feed' ); ?></span>
                        <div class="pfm-upsell-card-header">
                            <div class="pfm-upsell-icon-wrap">
                                <img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/wpfunnels.png' ); ?>" alt="WPFunnels" class="pfm-upsell-icon-img">
                            </div>
                            <div class="pfm-upsell-titles">
                                <h3 class="pfm-upsell-name"><?php esc_html_e( 'Turn Clicks Into Profit', 'rex-product-feed' ); ?></h3>
                                <p class="pfm-upsell-brand">WPFunnels &mdash; <?php esc_html_e( 'Funnel Builder for WooCommerce', 'rex-product-feed' ); ?></p>
                            </div>
                        </div>
                        <p class="pfm-upsell-desc" id="pfmWpfDesc"></p>
                        <div class="pfm-upsell-card-footer">
                            <button class="pfm-btn-primary pfm-upsell-install-btn" id="pfmInstallWpfBtn">
                                <?php esc_html_e( 'Install WPFunnels (Free)', 'rex-product-feed' ); ?> &rarr;
                            </button>
                            <span class="pfm-upsell-stats">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="#f59e0b" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:3px;"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
                                <?php esc_html_e( 'Used by 6,000+ WooCommerce stores', 'rex-product-feed' ); ?> &bull; <?php esc_html_e( '4.9/5 rating', 'rex-product-feed' ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Cart Lift -->
                    <div class="pfm-upsell-card" id="pfmClCard">
                        <div class="pfm-upsell-body-row">
                            <div class="pfm-upsell-icon-wrap">
                                <img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/cart-lift.png' ); ?>" alt="Cart Lift" class="pfm-upsell-icon-img">
                            </div>
                            <div class="pfm-upsell-titles">
                                <h3 class="pfm-upsell-name"><?php esc_html_e( 'Recover Lost Revenue', 'rex-product-feed' ); ?></h3>
                                <p class="pfm-upsell-brand pfm-upsell-brand-muted">Cart Lift</p>
                                <p class="pfm-upsell-desc"><?php esc_html_e( 'Automatically recover abandoned carts with email reminders.', 'rex-product-feed' ); ?></p>
                            </div>
                            <div class="pfm-upsell-cl-action">
                                <button class="pfm-btn-outline pfm-upsell-install-btn" id="pfmInstallClBtn">
                                    <?php esc_html_e( 'Install Cart Lift (Free)', 'rex-product-feed' ); ?> &rarr;
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

</div>

<?php wp_print_scripts(); ?>
<?php do_action( 'admin_footer' ); ?>
</body>
</html>
