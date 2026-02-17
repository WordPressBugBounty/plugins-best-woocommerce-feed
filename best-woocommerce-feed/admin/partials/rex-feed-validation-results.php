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

// Get counts from summary
$total_errors   = $summary['total_errors'] ?? 0;
$total_warnings = $summary['total_warnings'] ?? 0;
$total_info     = $summary['total_info'] ?? 0;
$total_issues   = $total_errors + $total_warnings + $total_info;
?>

<div class="rex-feed-validation-wrapper" data-feed-id="<?php echo esc_attr( $feed_id ); ?>">
    
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
        
        <!-- Validation Header -->
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
            
            <div class="rex-feed-validation-header__actions">
                <button type="button" class="button button-primary rex-feed-validate-btn" <?php disabled( ! $is_supported ); ?>>
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e( 'Validate Feed', 'rex-product-feed' ); ?>
                </button>
                
                <?php if ( $has_results ) : ?>
                    <button type="button" class="button rex-feed-export-validation-btn">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e( 'Export', 'rex-product-feed' ); ?>
                    </button>
                    
                    <button type="button" class="button rex-feed-clear-validation-btn">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e( 'Clear', 'rex-product-feed' ); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Validation Progress (hidden by default) -->
        <div class="rex-feed-validation-progress" style="display: none;">
            <div class="rex-feed-validation-progress__bar">
                <div class="rex-feed-validation-progress__fill"></div>
            </div>
            <span class="rex-feed-validation-progress__text"><?php esc_html_e( 'Validating products...', 'rex-product-feed' ); ?></span>
        </div>
        
        <?php if ( $has_results ) : ?>
            
            <!-- Summary Cards -->
            <div class="rex-feed-validation-summary-cards">
                <div class="rex-feed-validation-card rex-feed-validation-card--error rex-feed-validation-card--clickable" data-filter-severity="error" title="<?php esc_attr_e( 'Click to filter errors only', 'rex-product-feed' ); ?>">
                    <div class="rex-feed-validation-card__icon">
                        <span class="dashicons dashicons-warning"></span>
                    </div>
                    <div class="rex-feed-validation-card__content">
                        <span class="card-number"><?php echo esc_html( $total_errors ); ?></span>
                        <span class="card-label"><?php esc_html_e( 'Errors', 'rex-product-feed' ); ?></span>
                    </div>
                </div>
                
                <div class="rex-feed-validation-card rex-feed-validation-card--warning rex-feed-validation-card--clickable" data-filter-severity="warning" title="<?php esc_attr_e( 'Click to filter warnings only', 'rex-product-feed' ); ?>">
                    <div class="rex-feed-validation-card__icon">
                        <span class="dashicons dashicons-info-outline"></span>
                    </div>
                    <div class="rex-feed-validation-card__content">
                        <span class="card-number"><?php echo esc_html( $total_warnings ); ?></span>
                        <span class="card-label"><?php esc_html_e( 'Warnings', 'rex-product-feed' ); ?></span>
                    </div>
                </div>
                
                <div class="rex-feed-validation-card rex-feed-validation-card--info rex-feed-validation-card--clickable" data-filter-severity="info" title="<?php esc_attr_e( 'Click to filter suggestions only', 'rex-product-feed' ); ?>">
                    <div class="rex-feed-validation-card__icon">
                        <span class="dashicons dashicons-lightbulb"></span>
                    </div>
                    <div class="rex-feed-validation-card__content">
                        <span class="card-number"><?php echo esc_html( $total_info ); ?></span>
                        <span class="card-label"><?php esc_html_e( 'Suggestions', 'rex-product-feed' ); ?></span>
                    </div>
                </div>
                
                <div class="rex-feed-validation-card rex-feed-validation-card--total rex-feed-validation-card--clickable" data-filter-severity="" title="<?php esc_attr_e( 'Click to show all issues', 'rex-product-feed' ); ?>">
                    <div class="rex-feed-validation-card__icon">
                        <span class="dashicons dashicons-list-view"></span>
                    </div>
                    <div class="rex-feed-validation-card__content">
                        <span class="card-number"><?php echo esc_html( $total_issues ); ?></span>
                        <span class="card-label"><?php esc_html_e( 'Total Issues', 'rex-product-feed' ); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ( $total_issues === 0 ) : ?>
                <!-- Success Message -->
                <div class="rex-feed-validation-no-results rex-feed-validation-success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <h4><?php esc_html_e( 'Validation Successful', 'rex-product-feed' ); ?></h4>
                    <p><?php esc_html_e( 'Your feed meets all required validation rules and is ready for submission. No errors or warnings were found.', 'rex-product-feed' ); ?></p>
                </div>
            <?php else : ?>
            <div class="rex-feed-validation-filters">
                <div class="rex-feed-validation-filter-group">
                    <label for="rex-validation-severity-filter"><?php esc_html_e( 'Severity:', 'rex-product-feed' ); ?></label>
                    <select id="rex-validation-severity-filter" class="rex-validation-filter">
                        <option value=""><?php esc_html_e( 'All', 'rex-product-feed' ); ?></option>
                        <option value="error"><?php esc_html_e( 'Errors', 'rex-product-feed' ); ?></option>
                        <option value="warning"><?php esc_html_e( 'Warnings', 'rex-product-feed' ); ?></option>
                        <option value="info"><?php esc_html_e( 'Suggestions', 'rex-product-feed' ); ?></option>
                    </select>
                </div>
                
                <div class="rex-feed-validation-filter-group">
                    <label for="rex-validation-attribute-filter"><?php esc_html_e( 'Attribute:', 'rex-product-feed' ); ?></label>
                    <select id="rex-validation-attribute-filter" class="rex-validation-filter">
                        <option value=""><?php esc_html_e( 'All', 'rex-product-feed' ); ?></option>
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
                    <label for="rex-validation-search"><?php esc_html_e( 'Search:', 'rex-product-feed' ); ?></label>
                    <input type="text" id="rex-validation-search" class="rex-validation-filter" placeholder="<?php esc_attr_e( 'Search issues...', 'rex-product-feed' ); ?>">
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="rex-feed-validation-results-area">
                <table class="rex-feed-validation-table wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th class="column-severity" style="width: 80px;"><?php esc_html_e( 'Severity', 'rex-product-feed' ); ?></th>
                            <th class="column-product" style="width: 200px;"><?php esc_html_e( 'Product', 'rex-product-feed' ); ?></th>
                            <th class="column-attribute" style="width: 120px;"><?php esc_html_e( 'Attribute', 'rex-product-feed' ); ?></th>
                            <?php /* <th class="column-rule" style="width: 150px;"><?php esc_html_e( 'Rule', 'rex-product-feed' ); ?></th> */ ?>
                            <th class="column-message"><?php esc_html_e( 'Issue', 'rex-product-feed' ); ?></th>
                            <th class="column-value" style="width: 150px;"><?php esc_html_e( 'Raw Value', 'rex-product-feed' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="rex-validation-results-body">
                        <!-- Results will be loaded via AJAX -->
                        <tr class="rex-validation-loading">
                            <td colspan="5">
                                <span class="spinner is-active" style="float: none; margin: 0 auto;"></span>
                                <?php esc_html_e( 'Loading validation results...', 'rex-product-feed' ); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div class="rex-feed-validation-pagination">
                    <div class="rex-feed-validation-pagination__info">
                        <span class="showing-info"></span>
                    </div>
                    <div class="rex-feed-validation-pagination__nav">
                        <button type="button" class="button rex-validation-prev-page" disabled>
                            &laquo; <?php esc_html_e( 'Previous', 'rex-product-feed' ); ?>
                        </button>
                        <span class="page-info"></span>
                        <button type="button" class="button rex-validation-next-page" disabled>
                            <?php esc_html_e( 'Next', 'rex-product-feed' ); ?> &raquo;
                        </button>
                    </div>
                    <div class="rex-feed-validation-pagination__per-page">
                        <label for="rex-validation-per-page"><?php esc_html_e( 'Per page:', 'rex-product-feed' ); ?></label>
                        <select id="rex-validation-per-page">
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
        <?php else : ?>
            
            <!-- No Results -->
            <div class="rex-feed-validation-no-results">
                <?php if ( $last_validated ) : ?>
                    <!-- Feed was validated and has no errors -->
                    <span class="dashicons dashicons-yes" style="color: #46b450;"></span>
                    <h4><?php esc_html_e( 'Your feed meets all required validation rules and is ready for submission.', 'rex-product-feed' ); ?></h4>
                    <p><?php esc_html_e( 'No errors or warnings were found.', 'rex-product-feed' ); ?></p>
                <?php else : ?>
                    <!-- Feed has never been validated -->
                    <span class="dashicons dashicons-yes-alt"></span>
                    <h4><?php esc_html_e( 'No validation results yet', 'rex-product-feed' ); ?></h4>
                    <p>
                        <?php
                        // Get merchant name and make it human-readable
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
        
    <?php endif; ?>
    
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