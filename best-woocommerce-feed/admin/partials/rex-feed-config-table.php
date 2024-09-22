<?php
/**
 * Includes feed preview-popup markup
 * if preview button is triggered
 */
$get = rex_feed_get_sanitized_get_post();
if ( isset( $get[ 'get' ][ 'post' ] ) ) {
    $feed_id = $get[ 'get' ][ 'post' ];
    $publish_btn_id = get_post_meta( $feed_id, '_rex_feed_publish_btn', true )
        ?: get_post_meta( $feed_id, 'rex_feed_publish_btn', true );
    if ( 'rex-bottom-preview-btn' === $publish_btn_id ) {
        $path    = wp_upload_dir();
        $path    = $path[ 'basedir' ] . '/rex-feed';
        $format = get_post_meta( $feed_id, '_rex_feed_feed_format', true );
        $format = $format ?: get_post_meta( $feed_id, 'rex_feed_feed_format', true );
        $feed_url = get_post_meta( $feed_id, '_rex_feed_preview_file', true );
        $feed_url = $feed_url ?: get_post_meta( $feed_id, 'rex_feed_preview_file', true );

        $request  = wp_remote_get( esc_url( $feed_url ), array( 'sslverify' => FALSE ) );
        if( is_wp_error( $request ) ) {
            return 'false';
        }
        $feed_string = wp_remote_retrieve_body( $request );
        if ( 'xml' === $format ) {
            $feed = new DOMDocument;
            $feed->preserveWhiteSpace = FALSE;
            $feed->loadXML( $feed_string );
            $feed->formatOutput = TRUE;
            $feed_string = $feed->saveXML();
        }
        $format = 'text' === $format ? 'txt' : $format;
        unlink( trailingslashit( $path ) . "preview-feed-{$feed_id}.{$format}" );
        include_once plugin_dir_path(__FILE__) . 'rex-product-feed-xml-preview-popup.php';
    }
}


include_once plugin_dir_path( __FILE__ ) . 'rex-product-feed-google-missing-attribute-warning-popup.php';
?>

<table id="config-table" class="responsive-table wpfm-field-mappings">
</table>

<div id="rex-feed-footer-btn" class="rex-feed-footer-btn" style="display: none;">
    <div class="rex-feed-attr-btn-area">
       
        <div class="rex-dropdown">
            <button
                class="button dropdown-toggle"
                type="button">
                <?php echo esc_attr__( 'Add New Attribute', 'rex-product-feed' ) ?>
                <div class="icon-overlay">
                    <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/arrow-down.php';?>
                </div>
            </button>
            
            <div class="dropdown-menu">
                <a id="rex-new-attr">
                    <?php echo esc_attr__( 'New Attribute', 'rex-product-feed' ) ?>
                </a>
                <a id="rex-new-custom-attr">
                    <?php echo esc_attr__( 'New Custom Attribute', 'rex-product-feed' ) ?>
                </a>
            </div>
        </div>

    </div>


    <div class="rex-feed-publish-btn">
        <span class="spinner"></span>
        <a id="rex-bottom-preview-btn" class="bottom-preview-btn">
            <?php echo esc_attr__( 'Preview Feed', 'rex-product-feed' ) ?>
        </a>
        <a id="rex-bottom-publish-btn" class="rex-new-custom-btn bottom-publish-btn">
            <?php echo esc_attr__( 'Publish', 'rex-product-feed' ) ?>
        </a>
    </div>
</div>