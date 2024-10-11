<?php
/**
 * This file is responsible for displaying google diagnostics report
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

$filter_icon = '../assets/icon/icon-svg/filter.php';
$export_icon = '../assets/icon/icon-svg/export-all.php';
$product_icon = '../assets/icon/icon-svg/product.php';
$error_icon = '../assets/icon/icon-svg/error.php';
$low_icon = '../assets/icon/icon-svg/low-var.php';

$feed_id = filter_input( INPUT_GET, 'feed_id', FILTER_SANITIZE_NUMBER_INT );
$google_api = new Rex_Feed_Google_Shopping_Api();
$stats = $google_api->get_product_stats_summery();

require_once plugin_dir_path( __FILE__ ) . 'loading-spinner.php';
?>

<div class="rex-feed-gmc-diagnostics-report-area">

	<input type='hidden' name="rex_feed_id" value="<?php echo esc_html( $feed_id );?>">

	<h2 class="rex-feed-gmc-heading"><?php echo __("Google Merchant Product Diagnostics Report", 'rex-product-feed')?></h2>

	<div class="rex-feed-gmc-rex-card-area">
		<div class="rex-card rex-card--total">
			<h2 class="rex-card__heading"><?php echo __('Total Items Submitted', 'rex-product-feed')?></h2>
			<div class="rex-card__number"><?php echo !empty( $stats[ 'total' ] ) ? $stats[ 'total' ] : 0;?></div>
			<div class="rex-card__percentage"><?php echo !empty( $stats[ 'total' ] ) ? '100%' : 0;?></div>
		</div>

		<div class="rex-card rex-card--active">
			<h2 class="rex-card__heading"><?php echo __('Active Items', 'rex-product-feed')?></h2>
			<div class="rex-card__number"><?php echo !empty( $stats[ 'active' ][ 'count' ] ) ? $stats[ 'active' ][ 'count' ] : 0;?></div>
			<div class="rex-card__percentage"><?php echo !empty( $stats[ 'active' ][ 'rate' ] ) ? $stats[ 'active' ][ 'rate' ] : 0;?></div>
		</div>

		<div class="rex-card rex-card--expiring">
			<h2 class="rex-card__heading"><?php echo __('Expiring Items', 'rex-product-feed')?></h2>
			<div class="rex-card__number"><?php echo !empty( $stats[ 'expiring' ][ 'count' ] ) ? $stats[ 'expiring' ][ 'count' ] : 0;?></div>
			<div class="rex-card__percentage"><?php echo !empty( $stats[ 'expiring' ][ 'rate' ] ) ? $stats[ 'expiring' ][ 'rate' ] : 0;?></div>
		</div>

		<div class="rex-card rex-card--pending">
			<h2 class="rex-card__heading"><?php echo __('Pending Items', 'rex-product-feed')?></h2>
			<div class="rex-card__number"><?php echo !empty( $stats[ 'pending' ][ 'count' ] ) ? $stats[ 'pending' ][ 'count' ] : 0;?></div>
			<div class="rex-card__percentage"><?php echo !empty( $stats[ 'pending' ][ 'rate' ] ) ? $stats[ 'pending' ][ 'rate' ] : 0;?></div>
		</div>

		<div class="rex-card rex-card--disapproved">
			<h2 class="rex-card__heading"><?php echo __('Disapproved Items', 'rex-product-feed')?></h2>
			<div class="rex-card__number"><?php echo !empty( $stats[ 'disapproved' ][ 'count' ] ) ? $stats[ 'disapproved' ][ 'count' ] : 0;?></div>
			<div class="rex-card__percentage"><?php echo !empty( $stats[ 'disapproved' ][ 'rate' ] ) ? $stats[ 'disapproved' ][ 'rate' ] : 0;?></div>
		</div>

	</div>
    <?php if ( !$google_api->is_authorized() ) { ?>
        <div class="rex-feed-gmc-diagnostics-report-no-product-found">
            <?php
            include_once WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/error.php';
            echo '&nbsp;' . sprintf(
            /* translators: %s: Anchor tag for authentication link */
	            __( 'Please %1$s&nbsp;authenticate&nbsp;%2$s your Google account to view the report', 'rex-product-feed' ),
	            '<a href="' . esc_url( admin_url( 'edit.php?post_type=product-feed&page=merchant_settings' ) ) . '">',
	            '</a>'
            );
            ?>
        </div>
	<!-- .rex-feed-gmc-rex-card-area end -->
	<?php } elseif ( empty( $stats[ 'total' ] ) ) { ?>
		<div class="rex-feed-gmc-diagnostics-report-no-product-found">
			<?php echo __('No data found','rex-product-feed');?>
		</div>
		<!-- .rex-feed-gmc-diagnostics-report-no-product-found end -->
	<?php } else {?>
		<div class="rex-feed-gmc-diagnostics-report-section">
			<div class="rex-feed-gmc-diagnostics-report-filter">
				<span class="rex-feed-gmc-diagnostics-report-filter__date"><?php echo date( 'M d, Y', current_time( 'timestamp', 1 ) )?></span>
			</div>
			<!-- .rex-feed-gmc-diagnostics-report-filter -->

			<div class="rex-feed-gmc-diagnostics-report-list-area">
				<div class="rex-flex-table-header" role="rowgroup">
					<div class="flex-row" role="columnheader">
						<?php echo __('Products', 'rex-product-feed') ?>
					</div>

					<div class="flex-row" role="columnheader">
						<?php echo __('Click Potential', 'rex-product-feed') ?>
					</div>

					<div class="flex-row" role="columnheader">
						<?php echo __('Status', 'rex-product-feed') ?>
					</div>

					<div class="flex-row" role="columnheader">
						<?php echo __('What needs attention', 'rex-product-feed') ?>
					</div>

				</div>
				<!-- .rex-flex-table-header end -->

				<!--Table Rows Should be Here-->
			</div>
			<!-- .rex-feed-gmc-diagnostics-report-list-area-area end -->

			<div class="rex-feed-gmc-diagnostics-report-footer">
				<span></span>

				<!-- Pagination -->
				<ul class="rex-feed-gmc-pagination" role="navigation" aria-label="Pagination" >
					<li class="page-item">
						<input type="hidden" name="rexfeed_gmc_report_prev_page_token">
						<div id="rexfeed_gmc_report_prev_btn_wrapper">
							<a class="page-link" id="rexfeed_gmc_report_prev_page">
								<svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M7 13L1 7L7 1" stroke="#BEBEBE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</a>
						</div>
					</li>

					<li class="page-item">
						<input type="hidden" name="rexfeed_gmc_report_next_page_token">
						<div id="rexfeed_gmc_report_next_btn_wrapper">
							<a class="page-link" id="rexfeed_gmc_report_next_page">
								<svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1 13L7 7L1 1" stroke="#BEBEBE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</a>
						</div>
					</li>
				</ul>
				<!-- .rex-feed-gmc-pagination end -->

				<div class="entries-amount">
					<span>Show rows</span>
					<select class="select" id="rexfeed_gmc_report_max_result">
						<option value="10">10</option>
						<option value="20">20</option>
						<option value="30">30</option>
					</select>
				</div>
			</div>
			<!-- .rex-feed-gmc-diagnostics-report-footer end -->
		</div>
	<?php }?>

	<!-- .rex-feed-gmc-diagnostics-report-section end -->
</div>
<!-- .rex-feed-gmc-diagnostics-report-area end -->