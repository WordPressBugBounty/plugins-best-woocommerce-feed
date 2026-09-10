<?php
/**
 * Feed Validation Results Display
 *
 * This file displays the feed validation results in the admin area.
 *
 * @link       https://rextheme.com
 * @since      7.4.58
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Ensure validator classes are loaded
if ( ! class_exists( 'Rex_Feed_Validator_Factory' ) ) {
    $validator_path = plugin_dir_path( dirname( __FILE__ ) ) . 'feed-validator/';
    require_once $validator_path . 'abstract-rex-feed-validator.php';
    require_once $validator_path . 'class-rex-feed-validation-results.php';
    require_once $validator_path . 'class-rex-feed-validator-factory.php';
    require_once $validator_path . 'class-rex-feed-validator-google.php';
}

global $post;

$feed_id = isset( $post->ID ) ? $post->ID : 0;
$merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );
$is_supported = Rex_Feed_Validator_Factory::is_supported( $merchant );

$results_handler = new Rex_Feed_Validation_Results( $feed_id );
$has_results     = $results_handler->has_results();
$summary         = $results_handler->get_summary();
$last_validated  = $results_handler->get_last_validated();
$has_validation  = ! empty( $last_validated );
$validation_disabled = $results_handler->is_validation_disabled();
$exclude_error_products = $results_handler->is_error_product_exclusion_enabled();
$is_premium = (bool) apply_filters( 'wpfm_is_premium', false );

$pro_plugin       = 'best-woocommerce-feed-pro/rex-product-feed-pro.php';
$is_pro_installed = file_exists( WP_PLUGIN_DIR . '/' . $pro_plugin );
$is_pro_active    = false;

if ( $is_pro_installed ) {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $is_pro_active = is_plugin_active( $pro_plugin );
}

if ( $is_pro_installed && ! $is_pro_active ) {
    // 1. Pro installed but not Activated: Redirect to wordpress plugin page to tell user to activate the pro plugin (Button label: Activate)
    $pro_action_label  = __( 'Activate', 'rex-product-feed' );
    $pro_action_url    = admin_url( 'plugins.php' );
    $pro_action_target = '_self';
} elseif ( $is_pro_installed && $is_pro_active && ! $is_premium ) {
    // 2. Pro installed, Activated but no license activation: Redirect to plugin Activation page. (Button Label: Activate)
    $pro_action_label  = __( 'Activate', 'rex-product-feed' );
    $pro_action_url    = admin_url( 'edit.php?post_type=product-feed&page=wpfm-license' );
    $pro_action_target = '_self';
} else {
    // 3. Pro is not installed: Redirect to this link: https://rextheme.com/best-woocommerce-product-feed/pricing/ (Button Label: Upgrade to Pro)
    $pro_action_label  = __( 'Upgrade to Pro', 'rex-product-feed' );
    $pro_action_url    = 'https://rextheme.com/best-woocommerce-product-feed/pricing/';
    $pro_action_target = '_blank';
}

// Get counts from summary
if ( $has_results ) {
    $cumulative_summary = $results_handler->get_cumulative_summary();
    $total_errors   = $cumulative_summary['total_errors'] ?? 0;
    $total_warnings = $cumulative_summary['total_warnings'] ?? 0;
    $total_info     = $cumulative_summary['total_info'] ?? 0;
} else {
    $total_errors   = $summary['total_errors'] ?? 0;
    $total_warnings = $summary['total_warnings'] ?? 0;
    $total_info     = $summary['total_info'] ?? 0;
}
$total_issues   = $total_errors + $total_warnings + $total_info;

$total_products    = $results_handler->get_total_products_validated();
$error_product_ids = $results_handler->get_error_product_ids();
$health_score      = isset( $summary['health_score'] )
    ? max( 0, min( 100, absint( $summary['health_score'] ) ) )
    : Rex_Feed_Validation_Results::calculate_feed_health( $results_handler->get_results(), $total_products, $error_product_ids );

if ( $health_score >= 90 ) {
    $health_status       = __( 'Excellent', 'rex-product-feed' );
    $health_status_class = 'excellent';
} elseif ( $health_score >= 75 ) {
    $health_status       = __( 'Good', 'rex-product-feed' );
    $health_status_class = 'good';
} elseif ( $health_score >= 50 ) {
    $health_status       = __( 'Needs Attention', 'rex-product-feed' );
    $health_status_class = 'needs-attention';
} elseif ( $health_score >= 25 ) {
    $health_status       = __( 'Poor', 'rex-product-feed' );
    $health_status_class = 'poor';
} else {
    $health_status       = __( 'Critical', 'rex-product-feed' );
    $health_status_class = 'critical';
}

$validation_rule_conditions = array(
    '' => array(
        'find_and_replace'   => __( 'Find & Replace', 'rex-product-feed' ),
        'contain'            => __( 'Contains', 'rex-product-feed' ),
        'dn_contain'         => __( 'Does not contain', 'rex-product-feed' ),
        'equal_to'           => __( 'Is equal to', 'rex-product-feed' ),
        'nequal_to'          => __( 'Is not equal to', 'rex-product-feed' ),
        'greater_than'       => __( 'Greater than', 'rex-product-feed' ),
        'greater_than_equal' => __( 'Greater than or equal to', 'rex-product-feed' ),
        'less_than'          => __( 'Less than', 'rex-product-feed' ),
        'less_than_equal'    => __( 'Less than or equal to', 'rex-product-feed' ),
        'any'                => __( 'Is Any', 'rex-product-feed' ),
    ),
);
$validation_rule_attributes = class_exists( 'Rex_Feed_Attributes' ) ? Rex_Feed_Attributes::get_attributes() : array();

// Reuse Pro Feed Rule sources when available, keeping free-only installs functional.
if ( class_exists( 'Rex_Product_Feed_Rules' ) ) {
    $validation_feed_rules = new Rex_Product_Feed_Rules();

    if ( method_exists( $validation_feed_rules, 'getConditionOptions' ) ) {
        $validation_rule_conditions = $validation_feed_rules->getConditionOptions();
    }
    if ( method_exists( $validation_feed_rules, 'getRuleAttributes' ) ) {
        $validation_rule_attributes = $validation_feed_rules->getRuleAttributes();
    }
}

unset( $validation_rule_attributes['Attributes Separator'] );
?>

<div
    class="rex-feed-validation-wrapper"
    data-feed-id="<?php echo esc_attr( $feed_id ); ?>"
    data-validation-disabled="<?php echo $validation_disabled ? '1' : '0'; ?>"
    data-is-premium="<?php echo $is_premium ? '1' : '0'; ?>"
>
    <?php if ( ! $is_supported ) : ?>
        <div class="rex-feed-validation-notice rex-feed-validation-notice--info">
            <span class="dashicons dashicons-info"></span>
            <?php
            printf(
                esc_html__( 'Validation is not available for %s feeds yet. More merchant validators will be added in future updates.', 'rex-product-feed' ),
                '<strong>' . esc_html(ucwords(str_replace('_', ' ', $merchant)) ) . '</strong>'
            );
            ?>
        </div>
    <?php else : ?>
        <div class="rex-feed-validation-header">
            <div class="rex-feed-validation-header__title">
                <h3><?php esc_html_e( 'Feed Validation', 'rex-product-feed' ); ?></h3>
                <?php if ( $last_validated ) : ?>
                    <span class="rex-feed-validation-last-run">
                        <?php
                        printf(
                            esc_html__( 'Last validated: %s', 'rex-product-feed' ),
                            esc_html( human_time_diff( strtotime( $last_validated ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'rex-product-feed' ) )
                        );
                        ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="rex-feed-validation-header__toggle">
                <span class="rex-feed-validation-header__toggle-label"><?php esc_html_e( 'Enable/Disable', 'rex-product-feed' ); ?></span>
                <label class="rex-feed-validation-toggle" for="rex-feed-validation-toggle-input">
                    <input
                        id="rex-feed-validation-toggle-input"
                        class="rex-feed-validation-setting rex-feed-validation-toggle__input"
                        type="checkbox"
                        data-setting="validation-toggle"
                        <?php checked( ! $validation_disabled ); ?>
                    >
                    <span class="rex-feed-validation-toggle__switch" aria-hidden="true"></span>
                </label>
            </div>

            <div class="rex-feed-validation-header__actions">
                <label class="rex-feed-validation-action rex-feed-validation-exclude-btn<?php echo $validation_disabled ? ' is-disabled' : ''; ?>">
                    <input
                        class="rex-feed-validation-setting rex-feed-validation-exclude-btn__input"
                        type="checkbox"
                        data-setting="exclude-error-products"
                        <?php checked( $exclude_error_products ); ?>
                        <?php disabled( $validation_disabled ); ?>
                    >
                    <span class="rex-feed-validation-checkbox" aria-hidden="true">
                        <img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/icon-svg/validation-checkbox-checked.svg' ); ?>" alt="" draggable="false">
                    </span>
                    <span class="rex-feed-validation-exclude-btn__label"><?php esc_html_e( 'Exclude Error Products', 'rex-product-feed' ); ?></span>
                </label>

                <button
                    type="button"
                    class="rex-feed-validation-action rex-feed-validation-action--primary rex-feed-validate-btn"
                    <?php disabled( $is_premium ? true : $validation_disabled ); ?>
                >
                    <?php esc_html_e( 'Validate Feed', 'rex-product-feed' ); ?>
                </button>

                <?php if ( $has_results ) : ?>
                    <div class="rex-feed-validation-menu-wrap">
                        <button
                            type="button"
                            class="rex-feed-validation-menu-toggle"
                            aria-label="<?php esc_attr_e( 'Open validation options', 'rex-product-feed' ); ?>"
                            aria-controls="rex-feed-validation-menu"
                            aria-expanded="false"
                        >
                            <span class="rex-feed-validation-menu-toggle__dots" aria-hidden="true">
                                <img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/icon-svg/validation-menu-dot.svg' ); ?>" alt="" draggable="false">
                                <img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/icon-svg/validation-menu-dot.svg' ); ?>" alt="" draggable="false">
                                <img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/icon-svg/validation-menu-dot.svg' ); ?>" alt="" draggable="false">
                            </span>
                        </button>

                        <div id="rex-feed-validation-menu" class="rex-feed-validation-menu" hidden>
                            <button type="button" class="rex-feed-validation-menu__action rex-feed-export-validation-btn">
                                <svg class="rex-feed-validation-export-icon" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M4.73981 2.67L6.65981 0.75L8.57981 2.67" stroke="#666666" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6.65942 8.42998V0.80249" stroke="#666666" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M0.75 6.79504C0.75 10.11 3 12.795 6.75 12.795C10.5 12.795 12.75 10.11 12.75 6.79504" stroke="#666666" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span><?php esc_html_e( 'Export', 'rex-product-feed' ); ?></span>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="rex-feed-validation-progress" style="display: none;">
            <div class="rex-feed-validation-progress__bar">
                <div class="rex-feed-validation-progress__fill"></div>
            </div>
            <span class="rex-feed-validation-progress__text"><?php esc_html_e( 'Validating products...', 'rex-product-feed' ); ?></span>
        </div>

        <div class="rex-feed-validation-body" <?php echo $validation_disabled ? 'style="display: none;"' : ''; ?>>
            <?php if ( $has_results ) : ?>
                <div class="rex-feed-validation-summary-cards">
                    <div class="rex-feed-health-card">
                        <div class="rex-feed-health-card__copy">
                            <span class="rex-feed-health-card__title"><?php esc_html_e( 'Feed Health', 'rex-product-feed' ); ?></span>
                            <span class="rex-feed-health-card__status rex-feed-health-card__status--<?php echo esc_attr( $health_status_class ); ?>">
                                <?php echo esc_html( $health_status ); ?>
                            </span>
                        </div>
                        <div class="rex-feed-health-score" style="--rex-health-score: <?php echo esc_attr( $health_score ); ?>;">
                            <svg class="rex-feed-health-score__ring" viewBox="0 0 70 70" aria-hidden="true" focusable="false">
                                <circle class="rex-feed-health-score__track" cx="35" cy="35" r="32" pathLength="100"></circle>
                                <circle class="rex-feed-health-score__progress" cx="35" cy="35" r="32" pathLength="100"></circle>
                            </svg>
                            <span><?php echo esc_html( $health_score ); ?>%</span>
                        </div>
                    </div>

                    <button type="button" class="rex-feed-validation-card rex-feed-validation-card--error rex-feed-validation-card--clickable" data-filter-severity="error" title="<?php esc_attr_e( 'Click to filter errors only', 'rex-product-feed' ); ?>">
                        <span class="rex-feed-validation-card__icon" aria-hidden="true">
                            <svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.6 26.5H17.4C23.9 26.5 26.5 23.9 26.5 17.4V9.6C26.5 3.1 23.9 0.5 17.4 0.5H9.6C3.1 0.5 0.5 3.1 0.5 9.6V17.4C0.5 23.9 3.1 26.5 9.6 26.5Z" stroke="#FF2121" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M18.5348 8.5L8.5 18.5348" stroke="#FF2121" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8.59521 8.59534L18.6776 18.6301" stroke="#FF2121" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="rex-feed-validation-card__content">
                            <span class="card-number"><?php echo esc_html( $total_errors ); ?></span>
                            <span class="card-label"><?php esc_html_e( 'Errors', 'rex-product-feed' ); ?></span>
                        </span>
                    </button>

                    <button type="button" class="rex-feed-validation-card rex-feed-validation-card--warning rex-feed-validation-card--clickable" data-filter-severity="warning" title="<?php esc_attr_e( 'Click to filter warnings only', 'rex-product-feed' ); ?>">
                        <span class="rex-feed-validation-card__icon" aria-hidden="true">
                            <svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.6 26.5H17.4C23.9 26.5 26.5 23.9 26.5 17.4V9.6C26.5 3.1 23.9 0.5 17.4 0.5H9.6C3.1 0.5 0.5 3.1 0.5 9.6V17.4C0.5 23.9 3.1 26.5 9.6 26.5Z" stroke="#FFA600" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M13.5 7.5V14.5921" stroke="#FFA600" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M13.5 18.9148V19.0499" stroke="#FFA600" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="rex-feed-validation-card__content">
                            <span class="card-number"><?php echo esc_html( $total_warnings ); ?></span>
                            <span class="card-label"><?php esc_html_e( 'Warnings', 'rex-product-feed' ); ?></span>
                        </span>
                    </button>

                    <button type="button" class="rex-feed-validation-card rex-feed-validation-card--info rex-feed-validation-card--clickable" data-filter-severity="info" title="<?php esc_attr_e( 'Click to filter suggestions only', 'rex-product-feed' ); ?>">
                        <span class="rex-feed-validation-card__icon" aria-hidden="true"></span>
                        <span class="rex-feed-validation-card__content">
                            <span class="card-number"><?php echo esc_html( $total_info ); ?></span>
                            <span class="card-label"><?php esc_html_e( 'Suggestions', 'rex-product-feed' ); ?></span>
                        </span>
                    </button>
                </div>

                <?php if ( $total_issues === 0 ) : ?>
                    <div class="rex-feed-validation-no-results rex-feed-validation-success">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <h4><?php esc_html_e( 'Validation Successful', 'rex-product-feed' ); ?></h4>
                        <p><?php esc_html_e( 'Your feed meets all required validation rules and is ready for submission. No errors or warnings were found.', 'rex-product-feed' ); ?></p>
                    </div>
                <?php else : ?>
                    <div class="rex-feed-validation-results-panel">
                        <div class="rex-feed-validation-filters">
                            <div class="rex-feed-validation-severity-tabs" role="group" aria-label="<?php esc_attr_e( 'Filter by severity', 'rex-product-feed' ); ?>">
                                <button type="button" class="rex-validation-severity-tab is-active" data-severity="error" data-label="<?php esc_attr_e( 'Errors', 'rex-product-feed' ); ?>"><?php esc_html_e( 'Errors', 'rex-product-feed' ); ?></button>
                                <button type="button" class="rex-validation-severity-tab" data-severity="warning" data-label="<?php esc_attr_e( 'Warnings', 'rex-product-feed' ); ?>"><?php esc_html_e( 'Warnings', 'rex-product-feed' ); ?></button>
                                <button type="button" class="rex-validation-severity-tab" data-severity="info" data-label="<?php esc_attr_e( 'Info', 'rex-product-feed' ); ?>"><?php esc_html_e( 'Info', 'rex-product-feed' ); ?></button>
                            </div>

                            <select id="rex-validation-severity-filter" class="rex-validation-filter rex-validation-filter--hidden" tabindex="-1" aria-hidden="true">
                                <option value="error"><?php esc_html_e( 'Errors', 'rex-product-feed' ); ?></option>
                                <option value="warning"><?php esc_html_e( 'Warnings', 'rex-product-feed' ); ?></option>
                                <option value="info"><?php esc_html_e( 'Suggestions', 'rex-product-feed' ); ?></option>
                            </select>

                            <div class="rex-feed-validation-filter-group rex-feed-validation-filter-group--attribute">
                                <label class="screen-reader-text" for="rex-validation-attribute-filter"><?php esc_html_e( 'Attribute', 'rex-product-feed' ); ?></label>
                                <select id="rex-validation-attribute-filter" class="rex-validation-filter">
                                    <option value=""><?php esc_html_e( 'All attribute', 'rex-product-feed' ); ?></option>
                                    <?php
                                    $attribute_summary = $results_handler->get_attribute_summary();
                                    foreach ( $attribute_summary as $attr ) :
                                    ?>
                                        <option value="<?php echo esc_attr( $attr['attribute'] ); ?>">
                                            <?php echo esc_html( $attr['attribute'] . ' (' . $attr['total'] . ')' ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="rex-feed-validation-filter-group rex-feed-validation-filter-group--search">
                                <label class="screen-reader-text" for="rex-validation-search"><?php esc_html_e( 'Search', 'rex-product-feed' ); ?></label>
                                <input type="text" id="rex-validation-search" class="rex-validation-filter" placeholder="<?php esc_attr_e( 'Search', 'rex-product-feed' ); ?>">
                                <span class="dashicons dashicons-search" aria-hidden="true"></span>
                            </div>
                        </div>

                        <div class="rex-feed-validation-results-area">
                            <div class="rex-feed-validation-table-wrap">
                                <table class="rex-feed-validation-table wp-list-table widefat fixed">
                                    <thead>
                                        <tr>
                                            <th class="column-severity" data-all-label="<?php esc_attr_e( 'All', 'rex-product-feed' ); ?>">
                                                <span class="rex-validation-severity-heading"><?php esc_html_e( 'All Errors', 'rex-product-feed' ); ?></span>
                                            </th>
                                            <th class="column-attribute"><?php esc_html_e( 'Attribute', 'rex-product-feed' ); ?></th>
                                            <th class="column-message"><?php esc_html_e( 'Issue', 'rex-product-feed' ); ?></th>
                                            <th class="column-product"><?php esc_html_e( 'Products', 'rex-product-feed' ); ?></th>
                                            <th class="column-action"><?php esc_html_e( 'Fix issues', 'rex-product-feed' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="rex-validation-results-body">
                                        <tr class="rex-validation-loading">
                                            <td colspan="5">
                                                <span class="spinner is-active"></span>
                                                <?php esc_html_e( 'Loading validation results...', 'rex-product-feed' ); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="rex-feed-validation-pagination">
                                <div class="rex-feed-validation-pagination__info">
                                    <span class="showing-info"></span>
                                </div>
                                <div class="rex-feed-validation-pagination__nav">
                                    <button type="button" class="rex-validation-page-button rex-validation-first-page" aria-label="<?php esc_attr_e( 'First page', 'rex-product-feed' ); ?>" disabled><span class="rex-validation-page-icon" aria-hidden="true"></span></button>
                                    <button type="button" class="rex-validation-page-button rex-validation-prev-page" aria-label="<?php esc_attr_e( 'Previous page', 'rex-product-feed' ); ?>" disabled><span class="rex-validation-page-icon" aria-hidden="true"></span></button>
                                    <span class="page-info screen-reader-text"></span>
                                    <button type="button" class="rex-validation-page-button rex-validation-next-page" aria-label="<?php esc_attr_e( 'Next page', 'rex-product-feed' ); ?>" disabled><span class="rex-validation-page-icon" aria-hidden="true"></span></button>
                                    <button type="button" class="rex-validation-page-button rex-validation-last-page" aria-label="<?php esc_attr_e( 'Last page', 'rex-product-feed' ); ?>" disabled><span class="rex-validation-page-icon" aria-hidden="true"></span></button>
                                </div>
                                <div class="rex-feed-validation-pagination__per-page">
                                    <label for="rex-validation-per-page"><?php esc_html_e( 'Show rows:', 'rex-product-feed' ); ?></label>
                                    <select id="rex-validation-per-page">
                                        <option value="5" selected>5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="rex-feed-validation-no-results<?php echo $last_validated ? ' rex-feed-validation-success' : ''; ?>">
                    <span class="dashicons <?php echo $last_validated ? 'dashicons-yes' : 'dashicons-yes-alt'; ?>"></span>
                    <?php if ( $last_validated ) : ?>
                        <h4><?php esc_html_e( 'Your feed meets all required validation rules and is ready for submission.', 'rex-product-feed' ); ?></h4>
                        <p><?php esc_html_e( 'No errors or warnings were found.', 'rex-product-feed' ); ?></p>
                    <?php else : ?>
                        <h4><?php esc_html_e( 'No validation results yet', 'rex-product-feed' ); ?></h4>
                        <p>
                            <?php
                            $merchant_name = ucwords( str_replace( array( '_', '-' ), ' ', $merchant ) );
                            printf(
                                esc_html__( 'Click the "Validate Feed" button to check your feed for issues against %s guidelines.', 'rex-product-feed' ),
                                '<strong>' . esc_html( $merchant_name ) . '</strong>'
                            );
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Validation Fix Rule Modal -->
<div id="rex-validation-fix-modal" class="rex-validation-fix-modal" role="dialog" aria-modal="true" aria-labelledby="rex-validation-fix-modal-title" aria-hidden="true" hidden>
    <div class="rex-validation-fix-modal__dialog" role="document">
        <header class="rex-validation-fix-modal__header">
            <div class="rex-validation-fix-modal__heading">
                <h2 id="rex-validation-fix-modal-title"><?php esc_html_e( 'Product Attribute', 'rex-product-feed' ); ?></h2>
                <p class="rex-validation-fix-modal__subtitle"><?php esc_html_e( 'Update affected products', 'rex-product-feed' ); ?></p>
            </div>
            <button type="button" class="rex-validation-fix-modal__close" aria-label="<?php esc_attr_e( 'Close', 'rex-product-feed' ); ?>">
                <img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/icon-svg/validation-fix-modal-close.svg' ); ?>" alt="" draggable="false">
            </button>
        </header>

        <div class="rex-validation-fix-modal__body">
            <div class="rex-validation-fix-modal__alert" role="alert" style="display: none;">
                <span class="rex-validation-fix-modal__alert-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" fill="currentColor"/>
                    </svg>
                </span>
                <p class="rex-validation-fix-modal__alert-text">
                    <strong><?php esc_html_e( 'Notice:', 'rex-product-feed' ); ?></strong>
                    <span class="rex-validation-fix-modal__alert-message"><?php esc_html_e( 'This rule has already been added. Please configure a different rule.', 'rex-product-feed' ); ?></span>
                </p>
            </div>

            <div class="rex-validation-fix-modal__note" role="status"<?php echo ! $is_premium ? ' style="display: none;"' : ''; ?>>
                <span class="rex-validation-fix-modal__note-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-9-3a1 1 0 112 0 1 1 0 01-2 0zm1 3a1 1 0 00-1 1v3a1 1 0 102 0v-3a1 1 0 00-1-1z" fill="currentColor"/>
                    </svg>
                </span>
                <p class="rex-validation-fix-modal__note-text">
                    <strong><?php esc_html_e( 'Note:', 'rex-product-feed' ); ?></strong>
                    <span class="rex-validation-fix-modal__note-content"><?php esc_html_e( 'A potential fix has been auto-suggested and prefilled below. You can review or adjust it before submitting.', 'rex-product-feed' ); ?></span>
                </p>
            </div>

            <div class="rex-validation-fix-modal__upgrade-banner" role="region" aria-label="<?php esc_attr_e( 'Upgrade notice', 'rex-product-feed' ); ?>"<?php echo $is_premium ? ' style="display: none;"' : ''; ?>>
                <span class="rex-validation-fix-modal__upgrade-icon" aria-hidden="true">
                    <svg width="22" height="20" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16.7134 17.745H6.56163C6.10804 17.745 5.60045 17.415 5.44925 17.015L0.978148 5.435C0.340962 3.775 1.08615 3.265 2.61971 4.285L6.83162 7.075C7.53361 7.525 8.33279 7.295 8.63518 6.565L10.5359 1.875C11.1407 0.375 12.1451 0.375 12.7499 1.875L14.6507 6.565C14.953 7.295 15.7522 7.525 16.4434 7.075L20.3961 4.465C22.0809 3.345 22.8909 3.915 22.1997 5.725L17.8366 17.035C17.6746 17.415 17.167 17.745 16.7134 17.745Z" stroke="#216DF0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5.69754 20.7651H17.5773" stroke="#216DF0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.93747 12.7651H14.3374" stroke="#216DF0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <p class="rex-validation-fix-modal__upgrade-text"><?php esc_html_e( 'To use Feed Rules upgrade to pro.', 'rex-product-feed' ); ?></p>
                <a href="<?php echo esc_url( $pro_action_url ); ?>" class="rex-validation-fix-modal__upgrade-btn" target="<?php echo esc_attr( $pro_action_target ); ?>">
                    <?php echo esc_html( $pro_action_label ); ?>
                </a>
            </div>

            <div class="rex-validation-fix-modal__intro">
                <div class="rex-validation-fix-modal__message">
                    <span class="rex-validation-fix-modal__message-text"></span>
                    <span class="rex-validation-fix-modal__message-static"><?php esc_html_e( 'Map or use static to replace value.', 'rex-product-feed' ); ?></span>
                    <span class="rex-validation-fix-modal__help rex_feed-tooltip" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="12" fill="#1EB2FB"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="17" r="1.2" fill="white"/>
                        </svg>
                        <p><?php esc_html_e( 'Map a product field or use a static value to fill in the missing descriptions.', 'rex-product-feed' ); ?></p>
                    </span>
                </div>
            </div>

            <div class="rex-validation-fix-mapping" role="group" aria-label="<?php esc_attr_e( 'Feed attribute mapping', 'rex-product-feed' ); ?>" style="display: none;">
                <div class="rex-validation-fix-mapping__labels" aria-hidden="true">
                    <span><?php esc_html_e( 'Feed Attribute', 'rex-product-feed' ); ?><b>*</b></span>
                    <span><?php esc_html_e( 'Type', 'rex-product-feed' ); ?><b>*</b></span>
                    <span><?php esc_html_e( 'Value', 'rex-product-feed' ); ?><b>*</b></span>
                </div>

                <div class="rex-validation-fix-mapping__fields">
                    <label class="screen-reader-text" for="rex-validation-mapping-attribute"><?php esc_html_e( 'Feed Attribute', 'rex-product-feed' ); ?></label>
                    <input id="rex-validation-mapping-attribute" class="rex-validation-fix-rule__control" type="text" readonly disabled>

                    <label class="screen-reader-text" for="rex-validation-mapping-type"><?php esc_html_e( 'Mapping Type', 'rex-product-feed' ); ?></label>
                    <div class="rex-validation-fix-rule__select">
                        <select id="rex-validation-mapping-type" class="rex-validation-fix-rule__control">
                            <option value="meta"><?php esc_html_e( 'Attribute', 'rex-product-feed' ); ?></option>
                            <option value="static"><?php esc_html_e( 'Static', 'rex-product-feed' ); ?></option>
                        </select>
                    </div>

                    <label class="screen-reader-text" for="rex-validation-mapping-value"><?php esc_html_e( 'Assigned Value', 'rex-product-feed' ); ?></label>
                    <div class="rex-validation-fix-mapping__value-wrap">
                        <select id="rex-validation-mapping-value" class="rex-validation-fix-rule__control rex-validation-fix-rule__select2">
                            <option value=""><?php esc_html_e( 'Please Select', 'rex-product-feed' ); ?></option>
                            <?php foreach ( $validation_rule_attributes as $group_label => $attribute_options ) : ?>
                                <?php if ( ! is_array( $attribute_options ) || empty( $attribute_options ) ) : ?>
                                    <?php continue; ?>
                                <?php endif; ?>
                                <?php if ( '' !== $group_label ) : ?>
                                    <optgroup label="<?php echo esc_attr( $group_label ); ?>">
                                <?php endif; ?>
                                <?php foreach ( $attribute_options as $attribute_value => $attribute_option_label ) : ?>
                                    <option value="<?php echo esc_attr( $attribute_value ); ?>"><?php echo esc_html( $attribute_option_label ); ?></option>
                                <?php endforeach; ?>
                                <?php if ( '' !== $group_label ) : ?>
                                    </optgroup>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <input id="rex-validation-mapping-static-value" class="rex-validation-fix-rule__control" type="text" placeholder="<?php esc_attr_e( 'Enter static value', 'rex-product-feed' ); ?>" style="display: none;">
                    </div>
                </div>
            </div>

            <div class="rex-validation-fix-rule" role="group" aria-label="<?php esc_attr_e( 'Product feed rule', 'rex-product-feed' ); ?>">
                <div class="rex-validation-fix-rule__labels" aria-hidden="true">
                    <span><?php esc_html_e( 'If', 'rex-product-feed' ); ?><b>*</b></span>
                    <span><?php esc_html_e( 'Condition', 'rex-product-feed' ); ?><b>*</b></span>
                    <span><?php esc_html_e( 'Find', 'rex-product-feed' ); ?></span>
                    <span><?php esc_html_e( 'Then', 'rex-product-feed' ); ?></span>
                    <span><?php esc_html_e( 'Static', 'rex-product-feed' ); ?></span>
                    <span><?php esc_html_e( 'Replace', 'rex-product-feed' ); ?></span>
                </div>

                <div class="rex-validation-fix-rule__fields<?php echo ! $is_premium ? ' rex-validation-fix-rule__fields--disabled' : ''; ?>">
                    <label class="screen-reader-text" for="rex-validation-fix-if"><?php esc_html_e( 'If attribute', 'rex-product-feed' ); ?></label>
                    <select id="rex-validation-fix-if" class="rex-validation-fix-rule__control" disabled>
                        <option value=""><?php esc_html_e( 'Product attribute', 'rex-product-feed' ); ?></option>
                    </select>

                    <label class="screen-reader-text" for="rex-validation-fix-condition"><?php esc_html_e( 'Condition', 'rex-product-feed' ); ?></label>
                    <div class="rex-validation-fix-rule__select">
                        <select id="rex-validation-fix-condition" class="rex-validation-fix-rule__control rex-validation-fix-rule__select2" name="rex_validation_fix_rule[rules_condition]"<?php echo ! $is_premium ? ' disabled' : ''; ?>>
                            <option value=""><?php esc_html_e( 'Please Select', 'rex-product-feed' ); ?></option>
                            <?php foreach ( $validation_rule_conditions as $group_label => $condition_options ) : ?>
                                <?php if ( '' !== $group_label ) : ?>
                                    <optgroup label="<?php echo esc_attr( $group_label ); ?>">
                                <?php endif; ?>
                                <?php foreach ( $condition_options as $condition_value => $condition_label ) : ?>
                                    <option value="<?php echo esc_attr( $condition_value ); ?>"><?php echo esc_html( $condition_label ); ?></option>
                                <?php endforeach; ?>
                                <?php if ( '' !== $group_label ) : ?>
                                    </optgroup>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label class="screen-reader-text" for="rex-validation-fix-find"><?php esc_html_e( 'Find value', 'rex-product-feed' ); ?></label>
                    <input id="rex-validation-fix-find" class="rex-validation-fix-rule__control" type="text"<?php echo ! $is_premium ? ' disabled' : ''; ?>>

                    <label class="screen-reader-text" for="rex-validation-fix-then"><?php esc_html_e( 'Then attribute', 'rex-product-feed' ); ?></label>
                    <select id="rex-validation-fix-then" class="rex-validation-fix-rule__control" disabled>
                        <option value=""><?php esc_html_e( 'Product attribute', 'rex-product-feed' ); ?></option>
                    </select>

                    <span class="rex-validation-fix-rule__static wpfm-checkbox">
                        <input id="rex-validation-fix-static" type="checkbox"<?php echo ! $is_premium ? ' disabled' : ''; ?>>
                        <label for="rex-validation-fix-static">
                            <span class="screen-reader-text"><?php esc_html_e( 'Use a static value', 'rex-product-feed' ); ?></span>
                        </label>
                    </span>

                    <label class="screen-reader-text" for="rex-validation-fix-replace"><?php esc_html_e( 'Replacement value', 'rex-product-feed' ); ?></label>
                    <div class="rex-validation-fix-rule__replace">
                        <select id="rex-validation-fix-replace" class="rex-validation-fix-rule__control rex-validation-fix-rule__select2" name="rex_validation_fix_rule[rules_replace]"<?php echo ! $is_premium ? ' disabled' : ''; ?>>
                            <option value=""><?php esc_html_e( 'Please Select', 'rex-product-feed' ); ?></option>
                            <?php foreach ( $validation_rule_attributes as $group_label => $attribute_options ) : ?>
                                <?php if ( ! is_array( $attribute_options ) || empty( $attribute_options ) ) : ?>
                                    <?php continue; ?>
                                <?php endif; ?>
                                <?php if ( '' !== $group_label ) : ?>
                                    <optgroup label="<?php echo esc_attr( $group_label ); ?>">
                                <?php endif; ?>
                                <?php foreach ( $attribute_options as $attribute_value => $attribute_option_label ) : ?>
                                    <option value="<?php echo esc_attr( $attribute_value ); ?>"><?php echo esc_html( $attribute_option_label ); ?></option>
                                <?php endforeach; ?>
                                <?php if ( '' !== $group_label ) : ?>
                                    </optgroup>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <input id="rex-validation-fix-static-value" class="rex-validation-fix-rule__control rex-validation-fix-rule__static-value" type="text" name="rex_validation_fix_rule[rules_static_replace]"<?php echo ! $is_premium ? ' disabled' : ''; ?> hidden>
                    </div>
                </div>
            </div>
        </div>

        <footer class="rex-validation-fix-modal__footer">
            <button type="button" class="rex-validation-fix-modal__button rex-validation-fix-modal__cancel"><?php esc_html_e( 'Cancel', 'rex-product-feed' ); ?></button>
            <button
                type="button"
                id="rex_validation_add_rule_button"
                class="rex-validation-fix-modal__button rex-validation-fix-modal__submit"
                <?php echo ! $is_premium ? 'disabled' : ''; ?>
            >
                <span class="rex-validation-fix-modal__submit-label"><?php esc_html_e( 'Add Rule', 'rex-product-feed' ); ?></span>
            </button>
        </footer>
    </div>
</div>

<!-- Validation Products Modal -->
<div id="rex-validation-products-modal" class="rex-validation-products-modal" role="dialog" aria-modal="true" aria-labelledby="rex-validation-products-modal-title" aria-hidden="true" hidden>
    <div class="rex-validation-products-modal__dialog" role="document">
        <header class="rex-validation-products-modal__header">
            <div class="rex-validation-products-modal__heading">
                <h2 id="rex-validation-products-modal-title" class="rex-validation-products-modal__title"><?php esc_html_e( 'Affected Products', 'rex-product-feed' ); ?></h2>
                <p class="rex-validation-products-modal__subtitle">
                    <span class="rex-validation-products-modal__subtitle-label"><?php esc_html_e( 'Missing attribute:', 'rex-product-feed' ); ?></span>
                    <span class="rex-validation-products-modal__subtitle-value"></span>
                </p>
            </div>
            <button type="button" class="rex-validation-products-modal__close" aria-label="<?php esc_attr_e( 'Close', 'rex-product-feed' ); ?>">
                <img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/icon-svg/validation-fix-modal-close.svg' ); ?>" alt="" draggable="false">
            </button>
        </header>

        <div class="rex-validation-products-modal__body">
            <div class="rex-validation-products-table-card">
                <table class="rex-validation-products-table">
                    <thead>
                        <tr>
                            <th class="rex-validation-products-table__th rex-validation-products-table__th--products"><?php esc_html_e( 'Products', 'rex-product-feed' ); ?></th>
                            <th class="rex-validation-products-table__th rex-validation-products-table__th--link"><?php esc_html_e( 'Product link', 'rex-product-feed' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="rex-validation-products-modal-tbody">
                        <!-- Products will be populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

        <footer class="rex-validation-products-modal__footer">
            <div id="rex-validation-products-modal-pagination" class="rex-validation-products-modal__pagination" aria-label="<?php esc_attr_e( 'Products pagination', 'rex-product-feed' ); ?>"></div>
            <button type="button" class="rex-validation-products-modal__button rex-validation-products-modal__cancel"><?php esc_html_e( 'Cancel', 'rex-product-feed' ); ?></button>
        </footer>
    </div>
</div>

<!-- Export Modal -->
<div id="rex-validation-export-modal" class="rex-modal" style="display: none;">
    <div class="rex-modal__content">
        <div class="rex-modal__header">
            <h3><?php esc_html_e( 'Export Validation Results', 'rex-product-feed' ); ?></h3>
            <button type="button" class="rex-modal__close">&times;</button>
        </div>
        <div class="rex-modal__body">
            <p><?php esc_html_e( 'Choose the export format:', 'rex-product-feed' ); ?></p>
            <div class="rex-modal__options">
                <label>
                    <input type="radio" name="export_format" value="csv" checked>
                    <?php esc_html_e( 'CSV (Excel compatible)', 'rex-product-feed' ); ?>
                </label>
                <label>
                    <input type="radio" name="export_format" value="json">
                    <?php esc_html_e( 'JSON', 'rex-product-feed' ); ?>
                </label>
            </div>
        </div>
        <div class="rex-modal__footer">
            <button type="button" class="button rex-modal__cancel"><?php esc_html_e( 'Cancel', 'rex-product-feed' ); ?></button>
            <button type="button" class="button button-primary rex-modal__export"><?php esc_html_e( 'Export', 'rex-product-feed' ); ?></button>
        </div>
    </div>
</div>
