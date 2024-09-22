<?php

/**
 * Rex_Feed_Sales_Notification_Bar Class
 *
 * This class is responsible for displaying the sales notification banner in the WordPress admin.
 *
 * @since 7.4.15
 */
class Rex_Feed_Sales_Notification_Bar
{
    /**
     * Rex_Feed_Sales_Notification_Bar constructor.
     *
     * @since 7.4.15
     */
    public function __construct()
    {
        // Hook into the admin_notices action to display the banner
        add_action( 'admin_notices', array( $this, 'display_banner' ) );
        // Add styles
        add_action( 'admin_head', [ $this, 'enqueue_css' ] );

        add_action( 'wp_ajax_rexfeed_sales_notification_notice', [ $this, 'sales_notification_notice' ] );
        add_action( 'wp_ajax_nopriv_rexfeed_sales_notification_notice', [ $this, 'sales_notification_notice' ] );
    }


    /**
     * Displays the special occasion banner if the current date and time are within the specified range.
     *
     * @since 7.4.15
     */
    public function display_banner() {
        $screen          = get_current_screen();
        $allowed_screens = [ 'dashboard', 'plugins', 'product-feed' ];

        if ( !in_array( $screen->base, $allowed_screens ) && !in_array( $screen->parent_base, $allowed_screens ) && !in_array( $screen->post_type, $allowed_screens ) && !in_array( $screen->parent_file, $allowed_screens ) ) {
            return;
        }

        $btn_link = esc_url( 'https://rextheme.com/best-woocommerce-product-feed/pricing/' );
        ?>

        <section class="wpfm-promo-banner wpfm-promo-banner--regular" aria-labelledby="wpfm-promo-banner-title" id="wpfm-promo-banner">
            <div class="wpfm-promo-banner__container">
                <h2 class="wpfm-promo-banner__title" id="wpfm-promo-banner-title">
                    <a href="<?php echo esc_url($btn_link); ?>" target ="_blank" class="wpfm-promo-banner__link" aria-label="Get Special Discount">
                        <?php echo esc_html__('Get ', 'rex-product-feed'); ?><strong class="wpfm-promo-banner__discount"><?php echo esc_html__(' 20% ', 'rex-product-feed'); ?></strong><?php echo esc_html__(' Discount On ', 'rex-product-feed'); ?><strong class="wpfm-promo-banner__discount"><?php echo esc_html('Product Feed Manager for WooCommerce Pro' ); ?></strong>
                        <span class="wpfm-promo-banner__icon" aria-hidden="true">
                            <svg class="wpfm-promo-banner__svg" xmlns="http://www.w3.org/2000/svg" width="12"
                                 height="12" viewBox="0 0 12 12" fill="none">
                                <title id="arrow-icon-title">Arrow Icon</title>
                                <path d="M8.77887 2.05511L9.03494 1.79904H8.6728H5.17466C4.71928 1.79904 4.35014 1.42989 4.35014 0.97452C4.35014 0.519144 4.71928 0.15 5.17466 0.15H11.0253C11.4807 0.15 11.8498 0.519153 11.8498 0.97452V6.82164C11.8498 7.27701 11.4807 7.64616 11.0253 7.64616C10.5699 7.64616 10.2008 7.27701 10.2008 6.82164V3.32737V2.96524L9.94471 3.22131L1.5575 11.6084L1.55748 11.6085C1.23552 11.9305 0.713541 11.9305 0.391497 11.6085L0.391491 11.6084C0.0695031 11.2865 0.0695031 10.7644 0.391491 10.4424L0.285427 10.3363L0.391493 10.4424L8.77887 2.05511Z"
                                      fill="#216DF0" stroke="white" stroke-width="0.3"/>
                            </svg>
                        </span>
                    </a>
                </h2>

                <a class="wpfm-promo-banner__cross-icon" type="button" aria-label="close banner"
                   id="wpfm-promo-banner__cross-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M11 1L1 11" stroke="#C8C0E2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1 1L11 11" stroke="#C8C0E2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

            </div>

        </section>
        <script>
            (function ($) {
                /**
                 * Dismiss sale notification notice
                 *
                 * @param e
                 */
                
                function rexfeed_sales_notification_notice(e) {
                    e.preventDefault();
                    $('#wpfm-promo-banner').hide(); // Ensure the correct element is selected
                    jQuery.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: {
                            action: 'rexfeed_sales_notification_notice',
                            nonce: rex_wpfm_ajax?.ajax_nonce
                        },
                        success: function (response) {
                            $('#wpfm-promo-banner').hide(); // Ensure the correct element is selected
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX request failed:', status, error);
                        }
                    });
                }

                jQuery(document).ready(function($) {
                    $(document).on('click', '#wpfm-promo-banner__cross-icon', rexfeed_sales_notification_notice);
                });
                
            })(jQuery);
        </script>
        <!-- .rex-feed-tb-notification end -->
        <?php
    }


    /**
     * Adds internal CSS styles for the special occasion banners.
     *
     * @since 7.4.15
     */
    public function enqueue_css() {
        $plugin_dir_url = plugin_dir_url(__FILE__ );
        ?>
         <style type="text/css">
            :root {
                --wpfm-primary-color: #216DF0;
            }

            @font-face {
                font-family: 'Roboto';
                src: url(<?php echo "{$plugin_dir_url}assets/fonts/Roboto-Regular.woff2"; ?>) format('woff2');
                font-weight: 400;
                font-style: normal;
                font-display: swap;
            }

            @font-face {
                font-family: 'Roboto';
                src: url(<?php echo "{$plugin_dir_url}assets/fonts/Roboto-Bold.woff2"; ?>) format('woff2');
                font-weight: 700;
                font-style: normal;
                font-display: swap;
            }

            .wpfm-promo-banner * {
                box-sizing: border-box;
            }

            @keyframes arrowMove {
                0% {
                    transform: translate(0, 0);
                }
                50% {
                    transform: translate(18px, -18px);
                }
                55% {
                    opacity: 0;
                    visibility: hidden;
                    transform: translate(-18px, 18px);
                }
                100% {
                    opacity: 1;
                    visibility: visible;
                    transform: translate(0, 0);
                }
            }

            .wpfm-promo-banner {
                margin-top: 40px;
                padding: 14px 0;
                text-align: center;
                border-radius: 5px;
                border-left: 2px solid var(--wpfm-primary-color);
                background: #FFF;
                box-shadow: 0px 1px 1px 0px rgba(63, 4, 254, 0.10);
                width: calc(100% - 20px);
            }

            .wpfm-promo-banner__container {
                display: flex;
                margin: 0 auto;
                padding: 0 20px;
            }

            .wpfm-promo-banner__title {
                display: block;
                margin: 0 auto;
                text-align: center;
                font-size: 18px;
                line-height: normal;
            }

            .wpfm-promo-banner__link {
                position: relative;
                font-family: 'Roboto';
                font-size: 18px;
                font-style: normal;
                font-weight: 400;
                color: var(--wpfm-primary-color);
                transition: all .3s ease;
                text-decoration: none;
            }

            .wpfm-promo-banner__link:hover {
                color: var(--wpfm-primary-color);
            }

            .wpfm-promo-banner__link:focus {
                color: var(--wpfm-primary-color);
                box-shadow: none;
                outline: 0px solid transparent;
            }

            .wpfm-promo-banner__link::before {
                content: "";
                position: absolute;
                left: 0;
                bottom: 1px;
                width: calc(100% - 33px);
                height: 1px;
                background-color: var(--wpfm-primary-color);
                transform: scaleX(1);
                transform-origin: bottom left;
                transition: transform .4s ease;
            }

            .wpfm-promo-banner__link:hover::before {
                transform: scaleX(0);
                transform-origin: bottom right;
            }

            .wpfm-promo-banner__link:hover svg {
                animation: arrowMove .5s .4s linear forwards;
            }

            .wpfm-promo-banner__discount {
                font-weight: 700;
            }

            .wpfm-promo-banner__icon {
                display: inline-block;
                margin-left: 21px;
                vertical-align: middle;
                width: 12px;
                height: 17px;
                overflow: hidden;
                line-height: 1;
                position: relative;
                top: 1px;
            }

            .wpfm-promo-banner__svg {
                fill: none;
            }

            .wpfm-promo-banner__cross-icon {
                cursor: pointer;
                transition: all .3s ease;
            }

            .wpfm-promo-banner__cross-icon svg:hover path {
                stroke: var(--wpfm-primary-color);
            }

        </style>

        <?php
    }

    /**
     * Hide the sales notification bar
     *
     * @since 7.4.15
     */
    public function sales_notification_notice() {
        if ( !wp_verify_nonce( filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ), 'rex-wpfm-ajax')) {
            wp_die(__('Permission check failed', 'rex-product-feed'));
        }
        update_option('rexfeed_hide_sales_notification_bar', 'yes');
        echo json_encode( ['success' => true,] );
        wp_die();
    }
}