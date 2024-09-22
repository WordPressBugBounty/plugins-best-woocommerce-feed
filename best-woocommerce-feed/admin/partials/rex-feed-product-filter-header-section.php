<div class="rex-contnet-filter__header">
	<div class="rex-contnet-setting__header-text">
		<div class="rex-contnet-setting__icon">
			<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/icon-filter.php';?>
			<?php echo '<h2>' . esc_html__( "Product Filter", "rex-product-feed" ) . '</h2>';?>
		</div>
	</div>

    <div class="rex-feed-buttons">
        <?php
        do_action( 'rex_feed_before_filter_modal_close_button' );

        if( !apply_filters( 'wpfm_is_premium', false ) ) {
            $disabled = 'disabled';
            $checked  = '';
        }
        else {
            $disabled = '';
            $checked  = get_option( 'rex-wpfm-product-custom-field', 'no' ) === 'yes' ? 'checked' : '';
        }
        ?>

        <?php
        $status = get_post_meta( get_the_ID(), '_rex_feed_feed_rules_button', true ) ?: 'added';
        $style = 'added' === $status && apply_filters('wpfm_is_premium', false) ? 'style="display: none;"' : '';
        ?>
        <input type="hidden" name="rex_feed_feed_rules_button" value="<?php echo $status?>">
        <span class="rex-contnet-filter__close-icon <?php echo !apply_filters( 'wpfm_is_premium', false ) ? 'rexfeed-pro-disabled' : ''; ?>" id="rex_feed_rules_button" <?php echo $style?>>
            <?php if( !apply_filters( 'wpfm_is_premium', false ) ):?>
            <span class="wpfm-pro-tag"><?php echo esc_html__('pro', 'rex-product-feed'); ?></span>
            <?php endif;?>
            <?php esc_html_e( 'Add Feed Rule', 'rex-product-feed' ); ?>
        </span>

        <?php
        $status = get_post_meta( get_the_ID(), '_rex_feed_custom_filter_option', true );
        $style = 'added' === $status ? 'style="display: none;"' : '';
        ?>
        <input type="hidden" name="rex_feed_custom_filter_option_btn" value="<?php echo $status?>">
        <span class="rex-contnet-filter__close-icon" id="rex_feed_custom_filter_button" <?php echo $style?>>
            <?php esc_html_e( 'Add Custom Filter', 'rex-product-feed' ); ?>
        </span>

        <span class="rex-contnet-filter__cross-icon" id="rex_feed_filter_modal_close_btn">
            <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/corss.php';?>
        </span>
        
        <?php do_action( 'rex_feed_after_filter_modal_close_button' );?>
    </div>
</div>