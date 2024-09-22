<?php
/**
 * This file is responsible for displaying feed filter description section
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

?>
<!-- `rex-feed-published-product` block -->
<div id="rex-feed-published-product" style="display: none">
	<div class="rex-feed-published-product__content">
		<?php echo '<h2>' . esc_html__( 'All the published products will be included in the feed.', 'rex-product-feed' ) . '</h2>'; ?>
	</div>
</div>
<!-- `rex-feed-published-product` block end -->

<!-- `rex-feed-feature-product` block -->
<div id="rex-feed-featured-product" style="display: none">
	<div class="rex-feed-featured-product__content">
		<?php echo '<h2>' . esc_html__( 'Only the featured products will be included in the feed.', 'rex-product-feed' ) . '</h2>'; ?>
	</div>
</div>
<!-- `rex-feed-feature-product` block end -->
