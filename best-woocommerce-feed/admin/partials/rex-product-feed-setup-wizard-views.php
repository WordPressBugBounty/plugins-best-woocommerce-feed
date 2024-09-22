<?php

/**
 * Setup wizard view
 *
 * @package ''
 * @since 7.4.14
 */
?>

<!DOCTYPE html>
<html style="background-color: #EDF3FD;" lang="en" xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php esc_html_e( 'Product Feed - Setup Wizard', 'rex-product-feed' ); ?></title>
    <?php do_action( 'admin_enqueue_scripts' ); ?>
    <?php do_action( 'admin_print_styles' ); ?>
    <?php do_action( 'admin_head' ); ?>
    <script type="text/javascript">
        addLoadEvent = function (func) {
            if (typeof jQuery != "undefined") jQuery(document).ready(func);
            else if (typeof wpOnload != 'function') {
                wpOnload = func;
            } else {
                var oldonload = wpOnload;
                wpOnload = function () {
                    oldonload();
                    func();
                }
            }
        };
        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
        var pagenow = '';
    </script>
</head>

<body>
<div class="wpfm-setup-wizard__container">
    <div class="setup-wizard__inner-container">
        <div id="wizardContainer" style="height:100vh;">

        </div>
    </div>
</div>
<?php
wp_enqueue_media(); // add media
wp_print_scripts(); // window.wp
do_action( 'admin_footer' );
$data = array(
    'stepOne' => array(
        'step_text'           => __( "Welcome", "rex-product-feed" ),
        'heading'             => __( "Hello, welcome to", "rex-product-feed" ),
        'strong_heading'      => array(
            __( "Product Feed Manager for WooCommerce", "rex-product-feed" ),
        ),
        'strong_description'  => __( "Product Feed Manager for WooCommerce,", "rex-product-feed" ),
        'description'         => __( "Create accurate product feeds with your WooCommerce products in just a few clicks for any marketplace of your choice. Use our pre-defined merchant templates to generate flawless feeds for popular merchants" ),
        'img_alt'             => __( "Preview video image ", "rex-product-feed" ),
        'button_text'         => array(
            __( "Let’s create your first feed", "rex-product-feed" ),
            __( "Check the guide", "rex-product-feed" ),
        ),
        'pfm_feature_content' => array(
            __( "Product Feed Manager Features", "rex-product-feed" ),
            __( "Feed Management Made Easy With Product Feed Manager!", "rex-product-feed" ),
        ),

        'pfm_feature_heading' => array(
            __( 'Extensive Filtering Options', 'rex-product-feed' ),
            __( 'Feed Rules', 'rex-product-feed' ),
            __( 'Track Facebook Pixel', 'rex-product-feed' ),
            __( 'Combined Attributes', 'rex-product-feed' ),
            __( 'Dynamic Pricing', 'rex-product-feed' ),
            __( 'Multilingual Capabilities', 'rex-product-feed' ),
            __( 'Google Product Categories', 'rex-product-feed' ),
            __( 'Auto-sync With Google', 'rex-product-feed' ),
        ),

        'pfm_feature_description' => array(
            __( 'Extensive Filtering Options For Exporting A Precised Feed', 'rex-product-feed' ),
            __( 'Tailor Your Product Feed to Perfection with Feed Rules', 'rex-product-feed' ),
            __( 'Track Facebook Pixel To Measure Feed Performance', 'rex-product-feed' ),
            __( 'Customize Compelling Product Titles With Combined Attributes', 'rex-product-feed' ),
            __( 'Manipulate Your Product Price Using Dynamic Pricing', 'rex-product-feed' ),
            __( "Unleash Your Store's Global Potential with Multilingual Capabilities", 'rex-product-feed' ),
            __( 'Merge your WooCommerce categories with Google Product Categories', 'rex-product-feed' ),
            __( 'Sync Your WooCommerce Store With Google Merchant Center', 'rex-product-feed' )
        ),

        'pfm_feature_pro_heading' => array(
            __( "Product Feed Manager ", "rex-product-feed" ),
            __( "Pro Features", "rex-product-feed" ),
        ),

        'pfm_feature_pro_list_heading' => array(
            __( 'Feed Rules', 'rex-product-feed' ),
            __( 'WooCommerce JSON-LD Bug Fix', 'rex-product-feed' ),
            __( 'Feed for Unlimited Products', 'rex-product-feed' ),
            __( 'Specific Product Selection', 'rex-product-feed' ),
            __( 'Google Dynamic Remarketing Pixel', 'rex-product-feed' ),
            __( 'Google Review Feed', 'rex-product-feed' ),
            __( 'Email Notification for Feed Generation Error', 'rex-product-feed' ),
            __( 'Detailed Product Attributes (Size, Pattern, Material, Gender, etc)', 'rex-product-feed' ),

        ),

    ),

    'stepTwo' => array(
        'step_text'         => __( "Plugins & Merchants", "rex-product-feed" ),
        'heading'           => __( "Necessary", "rex-product-feed" ),
        'strong_heading'    => array(
            __( "Plugins", "rex-product-feed" ),
        ),
        'label'             => __( " License Key", "rex-product-feed" ),
        'button_text'       => array(
            __( "Activate License", "rex-product-feed" ),
            __( "Next", "rex-product-feed" ),
        ),
        'error_text'        => __( 'Please enter a valid one.', 'rex-product-feed' ),
        'strong_error_text' => __( 'Invalid license key', 'rex-product-feed' ),
        'success_text'      => __( 'Success', 'rex-product-feed' ),
    ),

    'stepThree' => array(
        'step_text'                => __( "Done", "rex-product-feed" ),
        'heading'                  => __( "Get", "rex-product-feed" ),
        'testimonials_description' => array(
            __( "From integration to post set up, the support over the first 2 months has been phenomenal, fast effective and reliable communication – if not the best, one of the best plugins i’ve used for a shopping feed on WordPress and being able to get bugs fixed effectively has been fantastic.", "rex-product-feed" ),
            __( "The only plugin with all functions for Google Shopping.", "rex-product-feed" ),
        ),
        'testimonials_author'      => array(
            __( "Samocpr" ),
            __( "Ale320", "rex-product-feed" ),
        ),
        'button_text'              => array(
            __( "Let’s create your first feed", "rex-product-feed" ),
            __( "Upgrade To Pro", "rex-product-feed" ),
        ),
    ),

    'stepFour' => array(
        'step_text'      => __( "Select Merchant", "rex-product-feed" ),
        'heading'        => __( "Select Your ", "rex-product-feed" ),
        'strong_heading' => array(
            __( "Favourite Merchant", "rex-product-feed" ),
        ),
        'button_text'    => array(
            __( "Google Shopping", "rex-product-feed" ),
            __( "Facebook", "rex-product-feed" ),
            __( "Etsy", "rex-product-feed" ),
            __( "Bing", "rex-product-feed" ),
            __( "eBay", "rex-product-feed" ),
        ),
    ),
);

$necessary_plugins = array(
    'woocommerce' => array(
        'name'      => 'WooCommerce',
        'slug'      => 'woocommerce',
        'required'  => true,
        'is_active' => is_plugin_active( 'woocommerce/woocommerce.php' ),
        'url'       => 'https://wordpress.org/plugins/woocommerce/',
        'img'       => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/icon-svg/wpfm_logo.png',
    ),
);
$popular_merchants = [
    'google' => [
        'name' => 'Google',
        'feed_url' => 'post-new.php?post_type=product-feed&rex_feed_merchant=google',
        'logo_url' => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/google.webp',
    ],
    'facebook' => [
        'name' => 'Facebook',
        'feed_url' => 'post-new.php?post_type=product-feed&rex_feed_merchant=facebook',
        'logo_url' => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/facebook.webp',
    ],
    'tiktok' => [
        'name' => 'TikTok Ads',
        'feed_url' => 'post-new.php?post_type=product-feed&rex_feed_merchant=tiktok',
        'logo_url' => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/tiktok.webp',
    ],
    'twitter' => [
        'name' => 'X (Twitter)',
        'feed_url' => 'post-new.php?post_type=product-feed&rex_feed_merchant=twitter',
        'logo_url' => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/x.webp',
    ],
    'instagram' => [
        'name' => 'Instagram',
        'feed_url' => 'post-new.php?post_type=product-feed&rex_feed_merchant=instagram',
        'logo_url' => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/instagram.webp',
    ],
    'pinterest' => [
        'name' => 'Pinterest',
        'feed_url' => 'post-new.php?post_type=product-feed&rex_feed_merchant=pinterest',
        'logo_url' => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/pinterest.webp',
    ],
    'snapchat' => [
        'name' => 'Snapchat',
        'feed_url' => 'post-new.php?post_type=product-feed&rex_feed_merchant=snapchat',
        'logo_url' => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/snapchat.webp',
    ],
    'bing' => [
        'name' => 'Bing',
        'feed_url' => 'post-new.php?post_type=product-feed&rex_feed_merchant=bing',
        'logo_url' => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/bing.webp',
    ],
    'yandex' => [
        'name' => 'Yandex',
        'feed_url' => 'post-new.php?post_type=product-feed&rex_feed_merchant=yandex',
        'logo_url' => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/yendex.webp',
    ],
    'vivino' => [
        'name' => 'Vivino',
        'feed_url' => 'post-new.php?post_type=product-feed&rex_feed_merchant=vivino',
        'logo_url' => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/vivino.webp',
    ],
];
$all_merchants     = Rex_Feed_Merchants::get_merchants();
$popular_merchant  = $all_merchants[ 'popular' ] ?? [];
$pro_merchant      = $all_merchants[ 'pro_merchants' ] ?? [];
$free_merchant     = $all_merchants[ 'free_merchants' ] ?? [];
$merged_merchants  = array_merge( $popular_merchant, $pro_merchant, $free_merchant );
?>
<script type="text/javascript">
    const rex_wpfm_wizard_translate_string = <?php echo wp_json_encode( $data ); ?>;
    const logoUrl = <?php echo json_encode( esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/pfm.webp' ) ); ?>;
    const bannerUrl = <?php echo json_encode( esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/welcome-image.webp' ) ); ?>;
    const thumnailImage = <?php echo json_encode( esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/youtube-thumbnill.webp' ) ); ?>;
    const woocommerceUrl = <?php echo json_encode( esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/woocommerce-logo.webp' ) ); ?>;
    const yt_video = 'https://www.youtube.com/embed/shv3-tMqWWU?si=UJGuCek7eiszj19M&autoplay=1';
    const necessary_plugins = <?php echo json_encode( $necessary_plugins ); ?>;
    const popular_merchants = <?php echo json_encode( $popular_merchants ); ?>;
    const all_merchants = <?php echo json_encode( $merged_merchants ); ?>;
</script>
<script src="<?php echo WPFM_PLUGIN_ASSETS_FOLDER . 'js/setup-wizard/setup_wizard.js'; ?>'">
</script>
</body>

</html>