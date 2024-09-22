<?php
/**
 * This file is responsible for displaying review request body section
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

?>
<!-- `rex-feed-review` block -->
<section class="rex-feed-review">
	<div class="rex-feed-review__wrapper">
		<div class="rex-feed-review__content">
			<div class="rex-feed-review__content-rating">
				<h2 class="rex-feed-review__header">
					<?php esc_html_e( 'Awesome, you\'ve generated the feed successfully!', 'rex-product-feed' ); ?>
				</h2>

				<a href="https://wordpress.org/support/plugin/best-woocommerce-feed/reviews/#new-post" target="_blank">
					<img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/star-rating.png' ); ?>"  alt="star-rating">
				</a>
			</div>
			<p>
                <?php
                echo sprintf(
                    esc_html__(
                        'Please do give us a %s if you like using our plugin. It will only take 2 minutes.',
                        'rex-product-feed'
                    ),
                    '<a href="https://wordpress.org/support/plugin/best-woocommerce-feed/reviews/#new-post" target="_blank">' .
                    esc_html__('rating', 'rex-product-feed') . '</a>'
                ); // phpcs:ignore
                ?>
            </p>
		</div>

		<div class="rex-feed-review__btn-area">
			<a id="rex_rated_already">
				<?php esc_html_e( 'Already Rated', 'rex-product-feed' ); ?>
			</a>

			<a id="rex_rate_now" href="https://wordpress.org/support/plugin/best-woocommerce-feed/reviews/#new-post" target="_blank">
				<?php esc_html_e( 'Rate Now', 'rex-product-feed' ); ?>
			</a>

			<a id="rex_rate_not_now">
				<?php esc_html_e( 'Not Now', 'rex-product-feed' ); ?>
			</a>
			
		</div>
	</div>
</section>

<!-- `rex-feed-review` block end -->
