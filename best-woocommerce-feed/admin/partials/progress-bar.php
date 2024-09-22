<?php
/**
 * This file is responsible for displaying progress bar in feed
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

?>

<div class="rex-feed-progressbar clearfix" style="display: none">
	<div class="progressbar-bar" style="height: 30px; width: 0; background: #1fb3fa; border-radius: 5px;"></div>
	<div class="progressbar-bar-percent">0</div>
</div>

<div class="progress-msg" style="display: none">
	<div class="feed-msg">
		<i class="fa fa-cog fa-spin" style="font-size:24px"></i> <span><?php echo esc_html__( 'Your feed is generating', 'rex-product-feed' ) . '....'; ?></span>
	</div>
	<div class="wpfm-time-container" id="wpfm-feed-clock"></div>
</div>

