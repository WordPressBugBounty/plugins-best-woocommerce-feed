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
        var wpvrNonce = '<?php echo wp_create_nonce('rex-product-feed'); ?>';
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
                            <p class="welcome-label"><?php esc_html_e('WELCOME', 'rex-product-feed'); ?></p>
                            <h1><?php esc_html_e('Welcome to Product Feed Manager', 'rex-product-feed'); ?></h1>
                            <p class="welcome-description"><?php esc_html_e('Create powerful product feeds for major shopping platforms in just a few steps. This wizard will help you set up and configure your first product feed.', 'rex-product-feed'); ?></p>
                        </div>
                        <button class="primary-btn" id="getStartedBtn"><?php esc_html_e('Get Started', 'rex-product-feed'); ?></button>
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
            <div class="exit"><?php esc_html_e('Exit', 'rex-product-feed'); ?></div>
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
                    <h3 class="section-title"><?php esc_html_e('Popular Merchants', 'rex-product-feed'); ?></h3>
                    <div class="popular-grid" id="popularGrid"></div>
                    
                    <!-- 200+ Merchants Info Banner -->
                    <div class="merchants-info-banner">
                        <div class="info-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3f04fe" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                        </div>
                        <div class="info-content">
                            <h4 class="info-title"><?php esc_html_e('200+ Merchants Available', 'rex-product-feed'); ?></h4>
                            <p class="info-description"><?php esc_html_e('Search above to discover more merchants and find the perfect platform for your product feed', 'rex-product-feed'); ?></p>
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
            <div class="exit"><?php esc_html_e('Exit', 'rex-product-feed'); ?></div>
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
            <div class="exit"><?php esc_html_e('Exit', 'rex-product-feed'); ?></div>
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

        <!-- Complete Step -->
        <section class="step" id="step-complete">
            <div class="card complete-card">
                <div class="success-content">
                    <!-- Success Icon -->
                    <div class="success-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#239654" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>

                    <h1 class="success-title"><?php esc_html_e('Your Feed is Ready!', 'rex-product-feed'); ?></h1>
                    <p class="success-desc"><?php esc_html_e('Your product feed has been generated and is ready to use.', 'rex-product-feed'); ?></p>

                    <!-- Feed URL Section -->
                    <div class="feed-url-section">
                        <div class="feed-url-header">
                            <span class="feed-url-label"><?php esc_html_e('Feed URL', 'rex-product-feed'); ?></span>
                            <a href="#" class="edit-feed-link" id="editFeedLink">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                <?php esc_html_e('Edit Feed', 'rex-product-feed'); ?>
                            </a>
                        </div>
                        <div class="feed-url-row">
                            <input type="text" class="feed-url-input" id="feedUrl" value="" readonly />
                            <button class="icon-btn" id="copyBtn" title="<?php esc_attr_e('View feed', 'rex-product-feed'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                    <polyline points="15 3 21 3 21 9"/>
                                    <line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                            </button>
                            <button class="icon-btn" id="downloadBtn" title="<?php esc_attr_e('Download Feed', 'rex-product-feed'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="nav-buttons">
                    <button class="btn btn-secondary" id="createAnotherBtn"><?php esc_html_e('Create Another Feed', 'rex-product-feed'); ?></button>
                    <button class="btn btn-primary" id="dashboardBtn"><?php esc_html_e('Go to Dashboard', 'rex-product-feed'); ?></button>
                </div>
            </div>
        </section>
    </main>
</div>
<?php wp_print_scripts(); ?>
<?php do_action('admin_footer'); ?>
</body>
</html>