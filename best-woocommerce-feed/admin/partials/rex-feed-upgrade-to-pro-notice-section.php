<?php
/**
 * This file is responsible for displaying pro upgrade notice
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

$features = array(
	esc_html__( 'Generate Unlimited Products (Free version is limited to 200 products)', 'rex-product-feed' ),
	esc_html__( 'Custom Field Support - Brand, GTIN, MPN, etc.', 'rex-product-feed' ),
	esc_html__( 'Detailed Product Attributes Support  - Size, Gender, Material, etc.', 'rex-product-feed' ),
	esc_html__( 'Apply Feed Rules', 'rex-product-feed' ),
	esc_html__( 'Merge Multiple Attributes Values Together', 'rex-product-feed' ),
	esc_html__( 'Manipulate Product Pricing', 'rex-product-feed' ),
	esc_html__( 'Fix WooCommerce\'s (JSON-LD) Structure Data Bug', 'rex-product-feed' ),
	esc_html__( 'Exclude Tax From Structured Data Prices', 'rex-product-feed' ),
	esc_html__( 'Access to an Elite Support Team', 'rex-product-feed' ),
);
$features = apply_filters( 'rex_feed_pro_features_overview', $features );

$active_plugins = get_option( 'active_plugins' );
if ( is_array( $active_plugins ) && !in_array( 'best-woocommerce-feed-pro/rex-product-feed-pro.php', $active_plugins ) ) {
	echo '<div id="' . esc_attr( $this->prefix ) . 'features_text">';
	echo '<h2>' . esc_html__( 'Why Upgrade To The Premium Version?', 'rex-product-feed' ) . '</h2>';
	echo '<ul class="parent">';

	foreach ( $features as $feature ) {
		echo '<li class="item">';
		include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/right-check.php';
        echo $feature; //phpcs:ignore
		echo '</li>';
	}
	echo '</ul>';

	echo '<div class="features-btn-area">';
	echo '<a class="btn" target="_blank" href="' . esc_url( 'https://rextheme.com/best-woocommerce-product-feed/pricing/?utm_source=go_pro_button&utm_medium=plugin&utm_campaign=pfm_pro&utm_id=pfm_pro' ) . '">' . esc_html__( 'Upgrade to Pro', 'rex-product-feed' ) . '</a>';
	echo '</div>';
	echo '</div>';
}
else {
	do_action( 'rex_feed_pro_features_overview' );
}
