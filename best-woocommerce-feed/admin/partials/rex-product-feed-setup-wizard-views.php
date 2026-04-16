<?php

/**
 * Setup wizard view
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
    <title><?php esc_html_e('Product Feed Manager - Setup Wizard', 'rex-product-feed'); ?></title>
    <?php
    do_action('admin_enqueue_scripts');
    do_action('admin_print_styles');
    do_action('admin_head');
    ?>
    <script type="text/javascript">
        var ajaxurl = '<?php echo admin_url('admin-ajax.php', 'relative'); ?>';
        var pfmNonce = '<?php echo wp_create_nonce('rex-product-feed'); ?>';
    </script>
</head>
<body>
<div id="onboarding-app">
    <!-- Sidebar (shared across merchant, create-feed, and complete steps) -->
    <aside class="sidebar" id="main-sidebar">
        <div class="sidebar-logo">
            <img src="<?php echo WPFM_PLUGIN_ASSETS_FOLDER. 'icon/setup-wizard-images/pfm.webp'; ?>" alt="Product Feed Manager Logo">
        </div>
        <nav class="nav-steps">
            <div class="nav-item" data-step="step-welcome">
                <span class="nav-circle">1</span>
                <?php esc_html_e('Welcome', 'rex-product-feed'); ?>
            </div>
            <div class="nav-item" data-step="step-select-merchant">
                <span class="nav-circle">2</span>
                <?php esc_html_e('Merchant', 'rex-product-feed'); ?>
            </div>
            <div class="nav-item" data-step="step-feed-settings">
                <span class="nav-circle">3</span>
                <?php esc_html_e('Feed Settings', 'rex-product-feed'); ?>
            </div>
            <div class="nav-item" data-step="step-attribute-mapping">
                <span class="nav-circle">4</span>
                <?php esc_html_e('Attribute Mapping', 'rex-product-feed'); ?>
            </div>
            <div class="nav-item" data-step="step-complete">
                <span class="nav-circle">5</span>
                <?php esc_html_e('Complete', 'rex-product-feed'); ?>
            </div>
        </nav>
    </aside>

    <div class="exit" id="wizardExit"><?php esc_html_e('Exit setup wizard', 'rex-product-feed'); ?></div>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Welcome Step -->
        <section class="step active full-width" id="step-welcome">
            <div class="welcome-app">
                <div class="welcome-wrapper">
                    <div class="welcome-card">
                        <div class="welcome-logo">
                            <img src="<?php echo WPFM_PLUGIN_ASSETS_FOLDER. 'icon/setup-wizard-images/pfm.webp'; ?>" alt="Product Feed Manager Logo">
                        </div>
                        <div class="welcome-content">
                            <h1><?php esc_html_e('Welcome to Product Feed Manager', 'rex-product-feed'); ?></h1>
                            <p class="welcome-description"><?php esc_html_e('Create powerful product feeds for major shopping platforms in just a few steps. This wizard will help you set up and configure your first product feed.', 'rex-product-feed'); ?></p>
                        </div>
                        <button class="primary-btn" id="getStartedBtn" data-loading-text="<?php esc_attr_e('Please wait...', 'rex-product-feed'); ?>"><?php esc_html_e('Get Started', 'rex-product-feed'); ?></button>
                        <label class="consent">
                            <input type="checkbox" checked id="consentCheckbox" />
                            <span class="custom-checkbox"></span>
                            <span class="consent-text"><?php esc_html_e('I agree to receive product updates and marketing communications from Product Feed Manager.', 'rex-product-feed'); ?></span>
                        </label>
                    </div>
                </div>
            </div>
        </section>
        <!-- Select Merchant Step -->
        <section class="step" id="step-select-merchant">
            <div class="card">
                <!-- Search Box (Top) -->
                <div class="search-box">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#76708c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" id="merchantSearch" placeholder="<?php esc_attr_e('Type at least 3 letters to search from 200+ merchants...', 'rex-product-feed'); ?>" />
                </div>

                <!-- Search Results -->
                <div class="search-results-container" id="searchResultsContainer" style="display: none;">
                    <div class="search-results-grid" id="searchResults">
                        <!-- Search results will be dynamically populated here -->
                    </div>
                </div>

                <!-- Popular Merchants Section -->
                <div class="popular-section" id="popularSection">
                    <h3 class="section-title"><?php esc_html_e('Which sales channel do you want to launch first?', 'rex-product-feed'); ?></h3>
                    <div class="popular-grid" id="popularGrid"></div>
                    
                    <!-- 200+ Merchants Info Banner -->
                    <div class="merchants-info-banner">
                        <div class="info-icon">
                            <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.76 9.96997V14.46C20.76 18.95 18.97 20.75 14.47 20.75H9.08002C8.50002 20.75 7.96998 20.72 7.47998 20.65" stroke="#75718B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2.79004 14.27V9.96997" stroke="#75718B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M11.78 10.75C13.61 10.75 14.96 9.26005 14.78 7.43005L14.11 0.75H9.44001L8.77003 7.43005C8.59003 9.26005 9.95004 10.75 11.78 10.75Z" stroke="#75718B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M18.0801 10.75C20.1001 10.75 21.5801 9.10998 21.3801 7.09998L21.1 4.34998C20.74 1.74998 19.7401 0.75 17.1201 0.75H14.0701L14.7701 7.76001C14.9501 9.41001 16.4301 10.75 18.0801 10.75Z" stroke="#75718B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.42004 10.75C7.07004 10.75 8.56003 9.41001 8.72003 7.76001L8.94006 5.55005L9.42004 0.75H6.37005C3.75005 0.75 2.75007 1.74998 2.39007 4.34998L2.11004 7.09998C1.91004 9.10998 3.40004 10.75 5.42004 10.75Z" stroke="#75718B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8.75 17.75C8.75 18.5 8.53998 19.2101 8.16998 19.8101C7.97998 20.1301 7.74998 20.42 7.47998 20.65C7.44998 20.69 7.42 20.72 7.38 20.75C6.68 21.38 5.76 21.75 4.75 21.75C3.53 21.75 2.43997 21.2 1.71997 20.34C1.69997 20.31 1.67002 20.29 1.65002 20.26C1.53002 20.12 1.42002 19.9701 1.33002 19.8101C0.960017 19.2101 0.75 18.5 0.75 17.75C0.75 16.49 1.33 15.36 2.25 14.63C2.42 14.49 2.59998 14.37 2.78998 14.27C3.36998 13.94 4.04 13.75 4.75 13.75C5.75 13.75 6.64998 14.11 7.34998 14.72C7.46998 14.81 7.57999 14.92 7.67999 15.03C8.33999 15.75 8.75 16.7 8.75 17.75Z" stroke="#75718B" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6.23999 17.73H3.26001" stroke="#75718B" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4.75 16.27V19.26" stroke="#75718B" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="info-content">
                            <h4 class="info-title"><?php esc_html_e('200+ Merchants Available', 'rex-product-feed'); ?></h4>
                            <p class="info-description"><?php esc_html_e('Search above to discover your preferred merchant.', 'rex-product-feed'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="nav-buttons">
                    <button class="btn btn-back" id="merchantBackBtn"><?php esc_html_e('Back', 'rex-product-feed'); ?></button>
                    <button class="btn btn-continue" id="merchantContinueBtn" disabled><?php esc_html_e('Continue', 'rex-product-feed'); ?></button>
                </div>
            </div>
        </section>

        <!-- Feed Settings Step (Renamed from Create Feed) -->
        <section class="step" id="step-feed-settings">
            <div class="card">
                <form id="feedSettingsForm" novalidate>
                    <div class="form-group">
                        <label class="form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            <?php esc_html_e('Feed Name', 'rex-product-feed'); ?>
                        </label>
                        <input type="text" id="feedName" class="form-input" placeholder="<?php esc_attr_e('e.g., My Product Feed', 'rex-product-feed'); ?>" />
                        <span class="form-hint"><?php esc_html_e('Give your feed a descriptive name for easy identification', 'rex-product-feed'); ?></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 4 23 10 17 10"/>
                                <polyline points="1 20 1 14 7 14"/>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                            </svg>
                            <?php esc_html_e('Update Frequency', 'rex-product-feed'); ?>
                        </label>
                        <div class="select-wrapper">
                            <select id="updateFrequency" class="form-select">
                                <option value="no" selected><?php esc_html_e('No interval', 'rex-product-feed'); ?></option>
                                <option value="hourly"><?php esc_html_e('Hourly', 'rex-product-feed'); ?></option>
                                <option value="daily"><?php esc_html_e('Daily', 'rex-product-feed'); ?></option>
                                <option value="weekly"><?php esc_html_e('Weekly', 'rex-product-feed'); ?></option>
                            </select>
                            <svg class="select-arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#76708c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </div>
                        <span class="form-hint"><?php esc_html_e('How often should the feed be automatically updated?', 'rex-product-feed'); ?></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                            <?php esc_html_e('Feed Format', 'rex-product-feed'); ?>
                        </label>
                        <div class="select-wrapper">
                            <select id="feedFormat" class="form-select">
                                <option value="xml" selected><?php esc_html_e('XML', 'rex-product-feed'); ?></option>
                                <option value="csv"><?php esc_html_e('CSV', 'rex-product-feed'); ?></option>
                            </select>
                            <svg class="select-arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#76708c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </div>
                        <span class="form-hint"><?php esc_html_e('Choose the format for your product feed', 'rex-product-feed'); ?></span>
                    </div>
                </form>
                <div class="nav-buttons">
                    <button class="btn btn-back" id="feedBackBtn"><?php esc_html_e('Back', 'rex-product-feed'); ?></button>
                    <button class="btn btn-continue" id="feedContinueBtn" disabled><?php esc_html_e('Continue', 'rex-product-feed'); ?></button>
                </div>
            </div>
        </section>

        <!-- Attribute Mapping Step -->
        <section class="step" id="step-attribute-mapping">
            <div class="card">
                <div class="mapping-header">
                    <h3 class="mapping-title"><?php esc_html_e('Product Attribute Mapping', 'rex-product-feed'); ?></h3>
                    <p class="mapping-description"><?php esc_html_e('Review the default attribute mappings for your selected merchant. These mappings determine how your WooCommerce product data will be mapped to the feed.', 'rex-product-feed'); ?></p>
                </div>
                
                <div class="mapping-table-wrapper">
                    <table class="mapping-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Merchant Attribute', 'rex-product-feed'); ?></th>
                                <th><?php esc_html_e('Type', 'rex-product-feed'); ?></th>
                                <th><?php esc_html_e('Mapped To', 'rex-product-feed'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="mappingTableBody">
                            <tr class="loading-row">
                                <td colspan="3" style="text-align: center; padding: 40px;">
                                    <div style="display: inline-flex; align-items: center; gap: 12px; color: #666;">
                                        <svg class="spinner" width="20" height="20" viewBox="0 0 50 50" style="animation: rotate 1s linear infinite;">
                                            <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="5" stroke-dasharray="31.4 31.4" stroke-linecap="round" style="animation: dash 1.5s ease-in-out infinite;"></circle>
                                        </svg>
                                        <?php esc_html_e('Loading mappings...', 'rex-product-feed'); ?>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="nav-buttons">
                    <button class="btn btn-back" id="mappingBackBtn"><?php esc_html_e('Back', 'rex-product-feed'); ?></button>
                    <button class="btn btn-publish" id="publishBtn" disabled><?php esc_html_e('Create Feed', 'rex-product-feed'); ?></button>
                </div>
            </div>
        </section>

        <!-- Complete Step (Upsell) -->
        <section class="step" id="step-complete">
            <div class="complete-upsell-app">
                <div class="complete-upsell-wrapper">
                    <!-- Header -->
                    <div class="complete-upsell-header">
                        <h1 class="complete-upsell-title">
                            <?php esc_html_e('Your Feed Is Live.', 'rex-product-feed'); ?><br>
                            <span class="complete-upsell-title-blue"><?php esc_html_e('Now Make Every Click Profitable.', 'rex-product-feed'); ?></span>
                        </h1>
                        <p class="complete-upsell-desc" id="completeUpsellDesc"></p>
                    </div>

                    <!-- Plugin Cards -->
                    <div class="upsell-plugins-list">
                        <!-- WPFunnels Card -->
                        <div class="upsell-plugin-card upsell-plugin-featured">
                            <span class="upsell-badge"><?php esc_html_e('HIGHLY RECOMMENDED', 'rex-product-feed'); ?></span>
                            <div class="upsell-plugin-header-row">
                                <div class="upsell-plugin-icon-wrap">
                                    <img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/wpfunnels.png' ); ?>" alt="WPFunnels" class="upsell-plugin-icon-img">
                                </div>
                                <div class="upsell-plugin-titles">
                                    <h3 class="upsell-plugin-name"><?php esc_html_e('Turn Clicks Into Profit', 'rex-product-feed'); ?></h3>
                                    <p class="upsell-plugin-brand">WPFunnels &mdash; <?php esc_html_e('Funnel Builder for WooCommerce', 'rex-product-feed'); ?></p>
                                </div>
                            </div>
                            <p class="upsell-plugin-desc" id="wpfunnelsPluginDesc"></p>
                            <div class="upsell-plugin-footer-row">
                                <button class="btn btn-continue upsell-install-btn" id="installWpfunnelsBtn">
                                    <?php esc_html_e('Install WPFunnels (Free)', 'rex-product-feed'); ?> &rarr;
                                </button>
                                <span class="upsell-plugin-stats">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#f59e0b" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:4px;flex-shrink:0;">
                                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                                    </svg><?php esc_html_e('Used by 6,000+ WooCommerce stores', 'rex-product-feed'); ?> &bull; <?php esc_html_e('4.9/5 rating', 'rex-product-feed'); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Cart Lift Card -->
                        <div class="upsell-plugin-card">
                            <div class="upsell-plugin-body-row">
                                <div class="upsell-plugin-icon-wrap">
                                    <img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/cart-lift.png' ); ?>" alt="Cart Lift" class="upsell-plugin-icon-img">
                                </div>
                                <div class="upsell-plugin-titles">
                                    <h3 class="upsell-plugin-name"><?php esc_html_e('Recover Lost Revenue', 'rex-product-feed'); ?></h3>
                                    <p class="upsell-plugin-brand upsell-plugin-brand--muted">Cart Lift</p>
                                    <p class="upsell-plugin-desc"><?php esc_html_e('Automatically recover abandoned carts with email reminders.', 'rex-product-feed'); ?></p>
                                </div>
                                <div class="upsell-cartlift-action">
                                    <button class="upsell-secondary-btn" id="installCartLiftBtn"><?php esc_html_e('Install Cart Lift (Free)', 'rex-product-feed'); ?> &rarr;</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Feed Info Card -->
                    <div class="feed-info-card" id="feedInfoCard">
                        <div class="feed-info-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                            <?php esc_html_e('Feed URL', 'rex-product-feed'); ?>
                            <span class="feed-info-format-badge" id="feedFormatBadge"></span>
                        </div>
                        <div class="feed-info-url-wrap">
                            <input type="text" class="feed-info-url-input" id="feedUrlInput" readonly>
                            <div class="feed-info-actions">
                                <a class="feed-info-icon-btn" id="feedViewLink" href="#" target="_blank" title="<?php esc_attr_e('View Feed', 'rex-product-feed'); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </a>
                                <a class="feed-info-icon-btn" id="feedEditLink" href="#" target="_blank" title="<?php esc_attr_e('Edit Feed', 'rex-product-feed'); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </a>
                                <a class="feed-info-icon-btn" id="feedDownloadLink" href="#" target="_blank" download title="<?php esc_attr_e('Download Feed', 'rex-product-feed'); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Skip -->
                    <div class="complete-skip-row">
                        <button class="complete-skip-btn" id="skipForNowBtn"><?php esc_html_e('Skip for now', 'rex-product-feed'); ?> &rarr;</button>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
<?php wp_print_scripts(); ?>
<?php do_action('admin_footer'); ?>
</body>
</html>