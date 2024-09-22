<?php
/**
 * This file is responsible for displaying missing attribute warning section for google
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

?>
<!-- `rex-google-shopping` block -->
<section class="rex-google-shopping" id="rex_feed_google_req_attr_warning_popup" style="display: none">
	<div class="rex-google-shopping__wrapper">
		<!-- `rex-google-shopping__body` element in the `rex-google-shopping` block  -->
		<div class="rex-google-shopping__body">
			<span class="rex-google-shopping__close-btn" id="rex_google_missing_attr_cross_btn">
				<svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M10.9989 2.80005L9.17616 0.977356L5.98801 4.16551L2.79985 0.977356L0.977158 2.80005L4.16531 5.98821L0.977158 9.17636L2.79985 10.9991L5.98801 7.8109L9.17616 10.9991L10.9989 9.17636L7.81071 5.98821L10.9989 2.80005Z" fill="#E56829"></path>
				</svg>
			</span>

			<!-- `rex-google-shopping__message` element in the `rex-google-shopping` block  -->
			<div class="rex-google-shopping__message">
				<h4 class="rex-google-shopping__heading">
					<?php esc_html_e( 'Some required attributes are not configured properly!', 'rex-product-feed' ); ?>
				</h4>
				<div class="rex-google-shopping__message-content">
			   
					<ul class="rex-google-shopping__lists-area"></ul>
				</div>

				<div class="rex-google-shopping__notice-area">
					<h6 class="rex-google-shopping__heading--notice"><?php esc_html_e( 'Do you still want to continue?', 'rex-product-feed' ); ?></h6>
				</div>

				<div class="rex-google-shopping__btn-area">
					<a class="rex-google-shopping__btn rex-google-shopping__btn--green" id="rex_google_missing_attr_cancel_btn" target="_self" role="button">
						<?php esc_html_e( 'Cancel', 'rex-product-feed' ); ?>
					</a>

					<a class="rex-google-shopping__btn" id="rex_google_missing_attr_okay_btn" target="_self" role="button">
						<span><?php esc_html_e( 'Okay', 'rex-product-feed' ); ?>  </span>
						<i class="fa fa-spinner fa-pulse fa-fw" style="display: none"></i>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- `rex-google-shopping` block  end -->
