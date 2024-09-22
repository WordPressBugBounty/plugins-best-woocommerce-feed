<?php
/**
 * This file is responsible for displaying warning message after making any changes in filters drawer
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */
?>
<!-- `rex-google-shopping` block -->
<section class="rex-google-shopping" id="rex_filter_changes_save_warning_popup" style="display: none">
    <div class="rex-google-shopping__wrapper">
        <!-- `rex-google-shopping__body` element in the `rex-google-shopping` block  -->
        <div class="rex-google-shopping__body">
			<span class="rex-google-shopping__close-btn" id="rex_filters_changes_close">
				<svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
                    d="M10.9989 2.80005L9.17616 0.977356L5.98801 4.16551L2.79985 0.977356L0.977158 2.80005L4.16531 5.98821L0.977158 9.17636L2.79985 10.9991L5.98801 7.8109L9.17616 10.9991L10.9989 9.17636L7.81071 5.98821L10.9989 2.80005Z"
                    fill="#E56829"></path>
				</svg>
			</span>

            <!-- `rex-google-shopping__message` element in the `rex-google-shopping` block  -->
            <div class="rex-google-shopping__message">
                <span class="rex-google-shopping__svg-icon" id="">
                    <svg width="60" height="60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" color="#F4B42B">
                        <path d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"></path>
                    </svg>
                </span>
                <h4 class="rex-google-shopping__heading">
                    <?php esc_html_e( 'Uh-oh! You\'re trying to exit without saving your changes. Would you like to save them?', 'rex-product-feed' ); ?>
                </h4>

                <div class="rex-google-shopping__btn-area">
                    <a class="rex-google-shopping__btn rex-google-shopping__btn--green" id="rex_abort_filter_changes" target="_self" role="button">
                        <?php esc_html_e( 'No, Close', 'rex-product-feed' ); ?>
                    </a>

                    <a class="rex-google-shopping__btn" id="rex_save_filters" target="_self" role="button">
                        <span><?php esc_html_e( 'Yes, Save', 'rex-product-feed' ); ?>  </span>
                        <i class="fa fa-spinner fa-pulse fa-fw" style="display: none"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- `rex-google-shopping` block  end -->