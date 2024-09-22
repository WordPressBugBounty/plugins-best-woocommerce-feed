<?php
/**
 * This file is responsible for displaying eBay seller section
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

?>

<div class="rex_feed_config_div rex_feed_ebay_seller_fields" style="display: none">
	<label for="<?php echo esc_attr( $this->prefix . 'ebay_seller_site_id' ); ?>">
		<?php esc_html_e( 'eBaySeller Site ID', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require_once plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php esc_html_e( 'eBaySeller Site ID', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$saved_value = get_post_meta( get_the_ID(), '_rex_feed_ebay_seller_site_id', true ) ?: get_post_meta( get_the_ID(), 'rex_feed_ebay_seller_site_id', true );
	?>
	<input type="text" name="<?php echo esc_attr( $this->prefix . 'ebay_seller_site_id' ); ?>" id="<?php echo esc_attr( $this->prefix . 'ebay_seller_site_id' ); ?>" value="<?php echo esc_attr( $saved_value ); ?>">
</div>

<div class="rex_feed_config_div rex_feed_ebay_seller_fields" style="display: none">
	<label for="<?php echo esc_attr( $this->prefix . 'ebay_seller_country' ); ?>"><?php esc_html_e( 'Country', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require_once plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php esc_html_e( 'Country', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$saved_value = get_post_meta( get_the_ID(), '_rex_feed_ebay_seller_country', true ) ?: get_post_meta( get_the_ID(), 'rex_feed_ebay_seller_country', true );
	?>
	<select name="<?php echo esc_attr( $this->prefix . 'ebay_seller_country' ); ?>" id="<?php echo esc_attr( $this->prefix . 'ebay_seller_country' ); ?>">
		<?php
		foreach ( $countries as $key => $value ) {
			$selected = $saved_value === $key ? ' selected' : '';
			echo wp_kses( "<option value='{$key}' {$selected}>{$value}</option>", rex_feed_get_allowed_kseser() );
		}
		?>
	</select>
</div>

<div class="rex_feed_config_div rex_feed_ebay_seller_fields" style="display: none">
	<label for="<?php echo esc_attr( $this->prefix . 'ebay_seller_currency' ); ?>">
						   <?php
							esc_html_e( 'eBaySeller Currency', 'rex-product-feed' )
							?>
		<span class="rex_feed-tooltip">
			<?php require_once plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php esc_html_e( 'eBaySeller Currency', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$saved_value = get_post_meta( get_the_ID(), '_rex_feed_ebay_seller_currency', true ) ?: get_post_meta( get_the_ID(), 'rex_feed_ebay_seller_currency', true );
	?>
	<input type="text" name="<?php echo esc_attr( $this->prefix . 'ebay_seller_currency' ); ?>" id="<?php echo esc_attr( $this->prefix . 'ebay_seller_currency' ); ?>" value="<?php echo esc_attr( $saved_value ); ?>">
</div>
