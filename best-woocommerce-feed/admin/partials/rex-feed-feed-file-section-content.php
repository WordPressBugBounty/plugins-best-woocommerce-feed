<?php
/**
 * This file is responsible for displaying feed file view/download section
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

?>
<h2> <?php esc_html_e( 'Your Feed URL', 'rex-product-feed' ); ?> </h2>

<input type="text" name="<?php echo esc_attr( $this->prefix ); ?>xml_file" id="<?php echo esc_attr( $this->prefix ); ?>xml_file" value="<?php echo esc_url( $feed_url ); ?>" disabled>

<a href="<?php echo esc_url( $feed_url ); ?>" target="_blank" class="btn waves-effect waves-light btn-default">
	<i class="fa fa-external-link" aria-hidden="true"></i>
	<?php esc_html_e( 'View Feed', 'rex-product-feed' ); ?>
</a>

<a href="<?php echo esc_url( $feed_url ); ?>" target="_blank" class="btn waves-effect waves-light btn-default" download="">
	<i class="fa fa-download" aria-hidden="true"></i>
	<?php esc_html_e( 'Download Feed', 'rex-product-feed' ); ?>
</a>
