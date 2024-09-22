<?php
/**
 * This file is responsible for displaying tour guide confirmation section
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

?>
<!-- `rex-take-alert` block -->
<section class="rex-take-alert">

	<div class="rex-take-alert__wrapper">
		<!-- `rex-take-alert__body` element in the `rex-take-alert` block  -->
		<div class="rex-take-alert__body">
			<span class="rex-take-alert__close-btn">
				<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M18.7778 18.7786C14.4819 23.074 7.51728 23.0738 3.22169 18.778C-1.0739 14.4823 -1.0739 7.51769 3.22169 3.22196C7.51728 -1.07378 14.4819 -1.07402 18.7778 3.22143C20.8409 5.28436 22 8.08242 22 11C22 13.9176 20.8409 16.7156 18.7778 18.7786ZM14.9278 8.21857L12.1071 11L14.9278 13.7814C15.0865 13.93 15.1765 14.1376 15.1765 14.355C15.1765 14.5724 15.0865 14.78 14.9278 14.9286C14.7795 15.0756 14.5788 15.1576 14.3699 15.1564C14.1638 15.1556 13.9663 15.0737 13.8199 14.9286L10.9992 12.1079L8.21778 14.9286C8.07143 15.0737 7.8739 15.1556 7.66778 15.1564C7.45893 15.1576 7.2582 15.0756 7.10992 14.9286C6.80528 14.6221 6.80528 14.1272 7.10992 13.8207L9.89135 11L7.10992 8.21857C6.84295 7.90683 6.8609 7.44213 7.15112 7.15191C7.44134 6.8617 7.90604 6.84375 8.21778 7.11071L10.9992 9.89214L13.7806 7.11071C13.9785 6.9058 14.2707 6.82202 14.5471 6.89095C14.8236 6.95988 15.0422 7.17104 15.1207 7.44488C15.1992 7.71872 15.1257 8.01365 14.9278 8.21857ZM4.34363 4.34471C8.02058 0.663508 13.9845 0.656605 17.6699 4.32929C19.4452 6.09842 20.4431 8.50157 20.4431 11.0079C20.4431 13.5141 19.4452 15.9173 17.6699 17.6864C13.9845 21.3591 8.02058 21.3522 4.34363 17.671C0.666691 13.9898 0.666691 8.02591 4.34363 4.34471Z" fill="#A8A7BE"/>
				</svg>
			</span>

			<div class="rex-take-alert__message">
				<h3 class="rex-take-alert__heading">
					<?php esc_html_e( 'Welcome to Product Feed Manager!', 'rex-product-feed' ); ?>
				</h3>
				<p class="rex-take-alert__description">
					<?php esc_html_e( 'How about we guide you on how to create your first product feed?', 'rex-product-feed' ); ?>
				</p>
			</div>

		</div>

		<!-- `rex-take-alert__footer` element in the `rex-take-alert` block  -->
		<footer class="rex-take-alert__footer">
			<ul class="rex-take-alert__btn-area">
				<li>
					<button type="button" id="rex-feed-tour-guide-popup-no-thanks-btn" class="rex-take-alert__button rex-take-alert__button--cancel">
						<?php esc_html_e( 'No, Thanks', 'rex-product-feed' ); ?>
					</button>
				</li>

				<li>
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product-feed&tour_guide=1' ) ); ?>">
						<button type="button" class="rex-take-alert__button rex-take-alert__button--yes">
							<?php esc_html_e( 'Click Here To Start', 'rex-product-feed' ); ?>
						</button>
					</a>
				</li>
			</ul>
		</footer>
	</div>
	
</section>
<!-- `rex-take-alert` block end -->

