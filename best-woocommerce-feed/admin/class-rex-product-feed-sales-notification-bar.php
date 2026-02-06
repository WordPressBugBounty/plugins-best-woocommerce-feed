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
     * Occasion name
     *
     * @var string
     */
    private $occasion;

    /**
     * Start date timestamp
     *
     * @var int
     */
    private $start_date;

    /**
     * End date timestamp
     *
     * @var int
     */
    private $end_date;
    /**
     * Rex_Feed_Sales_Notification_Bar constructor.
     *
     * @since 7.4.15
     */
    public function __construct($occasion, $start_date, $end_date ) {

		$this->occasion   = "rex_feed_{$occasion}";
		$this->start_date = strtotime( $start_date );
		$this->end_date   = strtotime( $end_date );


        $current_date_time = current_time( 'timestamp' );

		 if (
		 	'hidden' !== get_option( $this->occasion, '' )
		 	&& !defined( 'REX_PRODUCT_FEED_PRO_VERSION' )
		 	&& ( $current_date_time >= $this->start_date && $current_date_time <= $this->end_date )
		 ) {
			// Hook into the admin_notices action to display the banner
            add_action( 'admin_notices', array( $this, 'display_banner' ) );
            // Add styles
            add_action( 'admin_head', [ $this, 'enqueue_css' ] );

            add_action( 'wp_ajax_rexfeed_sales_notification_notice', [ $this, 'sales_notification_notice' ] );
            add_action( 'wp_ajax_nopriv_rexfeed_sales_notification_notice', [ $this, 'sales_notification_notice' ] );
		 }

        
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


        // Get actual dimensions

        $img_url  = plugin_dir_url(__FILE__) . 'assets/icon/banner-images/happy-new-year.webp';
        $img_path = plugin_dir_path(__FILE__) . 'assets/icon/banner-images/happy-new-year.webp';
        $img_size = getimagesize($img_path);
        $img_width  = $img_size[0];
        $img_height = $img_size[1];
        ?>

        <section class="wpfm-promo-banner wpfm-promo-banner--regular" aria-labelledby="wpfm-promo-banner-title" id="wpfm-promo-banner">
            <div class="wpfm-promo-banner__container">


                <div class="wpfm-halloween-promotional-banner-content">
                    <div class="wpfm-banner-title">
                        <div class="wpfm-spooktacular">
                            <span><?php echo esc_html__('New Year Savings.', 'rex-product-feed'); ?></span>
                        </div>

                        <!-- Black Friday Logo -->
                        <figure class="wpfm-banner-img black-friday">
                            <img src="<?php echo esc_url($img_url); ?>" alt="New Year 2026 Sale"  width="<?php echo esc_attr($img_width); ?>"
                             height="<?php echo esc_attr($img_height); ?>" />
                            <figcaption class="visually-hidden">New Year 2026 Logo</figcaption>
                        </figure>
                        
                        <div class="wpfm-discount-text">
                            <?php echo esc_html__('Get', 'rex-product-feed'); ?>
                            <span class="wpfm-halloween-percentage"><?php echo esc_html__('25% OFF ', 'rex-product-feed'); ?></span>
                            <?php echo esc_html__('on ', 'rex-product-feed'); ?>
                            <span class="wpfm-text-highlight">
                                <?php echo esc_html__('Product Feed Manager!', 'rex-product-feed'); ?>
                            </span>
                        </div>

                        <!-- Countdown -->
                        <div id="wpfm_bf_countdown-banner">
                            <span id="wpfm_bf_countdown-text"></span>
                        </div>

                    </div>

                    <a href="<?php echo esc_url($btn_link); ?>"
                    target="_blank"
                    class="wpfm-halloween-banner-link"
                    aria-label="<?php echo esc_attr__('Get 25% OFF on Product Feed Manager for WooCommerce Pro', 'rex-product-feed'); ?>">
                        <?php echo esc_html__('Get 25% OFF', 'rex-product-feed'); ?>
                        <span class="wpfm-arrow-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11" fill="none">
                                <path d="M9.71875 0.25C9.99225 0.25 10.2548 0.358366 10.4482 0.551758C10.6416 0.745155 10.75 1.00775 10.75 1.28125V9.71875C10.75 9.99225 10.6416 10.2548 10.4482 10.4482C10.2548 10.6416 9.99225 10.75 9.71875 10.75C9.44525 10.75 9.18265 10.6416 8.98926 10.4482C8.79587 10.2548 8.6875 9.99225 8.6875 9.71875V3.77051L2.01074 10.4482C1.81734 10.6416 1.55476 10.75 1.28125 10.75C1.00775 10.75 0.745155 10.6416 0.551758 10.4482C0.358365 10.2548 0.25 9.99225 0.25 9.71875C0.250003 9.44525 0.358362 9.18265 0.551758 8.98926L7.22949 2.3125H1.28125C1.00775 2.3125 0.745151 2.20414 0.551758 2.01074C0.358366 1.81735 0.25 1.55475 0.25 1.28125C0.25 1.00775 0.358366 0.745154 0.551758 0.551758C0.745151 0.358365 1.00775 0.250004 1.28125 0.25H9.71875Z" fill="white" stroke="white" stroke-width="0.5"/>
                            </svg>
                        </span>
                    </a>
                </div>


                <a class="wpfm-promo-banner__cross-icon" type="button" aria-label="close banner"
                   id="wpfm-promo-banner__cross-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M11 1L1 11" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1 1L11 11" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

            </div>

        </section>
        <script>


            (function () {
                    const wpfm_bf_text = document.getElementById("wpfm_bf_countdown-text");

                    // === Configure start & end times ===
                    const wpfm_bf_start = new Date("2025-12-31T00:00:00"); // Deal start date
                    const wpfm_bf_end = new Date("2026-01-12T23:59:59");   // Deal end date

                    // === Update countdown text ===
                    function wpfm_bf_updateCountdown() {
                    const now = new Date();

                    // Before deal starts
                    if (now < wpfm_bf_start) {
                        wpfm_bf_text.textContent = "Deal coming soon!";
                        return;
                    }

                    // After deal ends
                    if (now > wpfm_bf_end) {
                        wpfm_bf_text.textContent = "Deal expired.";
                        clearInterval(wpfm_bf_timer);
                        return;
                    }

                    // Calculate remaining time
                    const diff = wpfm_bf_end - now;
                    const minutes = Math.floor(diff / (1000 * 60));
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

                        // Display message with <span> for styling numbers
                        if (days > 1) {
                            wpfm_bf_text.innerHTML = `<span>${days}</span> days left.`;
                        } else if (days === 1) {
                            wpfm_bf_text.innerHTML = `<span>1</span> day left.`;
                        } else if (hours >= 1) {
                            wpfm_bf_text.innerHTML = `<span>${hours}</span> hrs left.`;
                        } else if (minutes >= 1) {
                            wpfm_bf_text.innerHTML = `<span>${minutes}</span> mins left.`;
                        } else {
                            wpfm_bf_text.innerHTML = "Deal expired.";
                            clearInterval(wpfm_bf_timer);
                        }
                    }

                    // === Initialize countdown ===
                    wpfm_bf_updateCountdown(); // Run immediately
                    const wpfm_bf_timer = setInterval(wpfm_bf_updateCountdown, 30000); // Update every 30s
                })();


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
                --wpfm-primary-color: #24EC2C;
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
                padding: 17px 0;
                text-align: center;
                background: linear-gradient(90deg, #24EC2C 0%, #2022F8 16.24%, #1A1B9D 51.84%, #2022F8 99.14%);
                width: calc(100% - 20px);
            }

            .wpfm-promo-banner__container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 0 auto;
                padding: 0 20px;
                width: 100%;
            }

            .wpfm-halloween-promotional-banner-content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                max-width: 1090px;
                margin: 0 auto;
                width: 100%;
            }

            .wpfm-halloween-promotional-banner-content .wpfm-banner-title {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 20px;
                color: #FFF;
                font-size: 16px;
                font-weight: 500;
                line-height: 1;
                text-transform: capitalize;
            }

            .wpfm-spooktacular span {
                font-weight: 900;
            }

            .wpfm-halloween-promotional-banner-content span.wpfm-halloween-highlight {
                font-size: 16px;
                font-weight: 900;
                color: #24ec2c;
                text-transform: capitalize;
            }

            .wpfm-halloween-percentage {
                font-size: 16px;
                font-weight: 900;
                color: #24ec2c;
            }

            .wpfm-banner-img {
                margin: 0;
            }

            .wpfm-banner-img img {
                max-width: 150px;
                height: auto;
            }

            .wpfm-halloween-promotional-banner-content .visually-hidden {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                border: 0;
            }

            .wpfm-discount-text {
                font-weight: 600;
            }

            .wpfm-text-highlight {
                font-size: 16px;
                font-weight: 700;
                color: #fff;
            }

            .wpfm-halloween-banner-link {
                position: relative;
                font-family: 'Roboto';
                font-size: 15px;
                font-weight: 800;
                color: var(--wpfm-primary-color);
                transition: all .3s ease;
                text-decoration: none;
                letter-spacing: -0.084px;
            }

            .wpfm-halloween-banner-link:hover {
                color: var(--wpfm-primary-color);
            }

            #wpfm_bf_countdown-text {
                font-weight: 500;
                text-transform: capitalize;
            }

            #wpfm_bf_countdown-text  span {
                color: #24ec2c;
                font-weight: 900;
            }

            .wpfm-halloween-banner-link:focus {
                color: var(--wpfm-primary-color);
                box-shadow: none;
                outline: 0px solid transparent;
            }

            .wpfm-halloween-banner-link::before {
                content: "";
                position: absolute;
                left: 0;
                bottom: 1px;
                width: 100%;
                height: 2px;
                background-color: var(--wpfm-primary-color);
                transform: scaleX(1);
                transform-origin: bottom left;
                transition: transform .4s ease;
            }

            .wpfm-halloween-banner-link:hover::before {
                transform: scaleX(0);
                transform-origin: bottom right;
            }

            .wpfm-halloween-banner-link:hover svg {
                animation: arrowMove .5s .4s linear forwards;
            }

            .wpfm-arrow-icon{
                display: inline-block;
                margin-left: 8px;
                vertical-align: middle;
                width: 12px;
                height: 17px;
                overflow: hidden;
                line-height: 1;
                position: relative;
                top: 1px;
            }

            .wpfm-arrow-icon svg path {
                fill: var(--wpfm-primary-color);
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

            @media only screen and (max-width: 1399px) {
                .wpfm-promo-banner__cross-icon {
                    margin-left: 10px;
                }
            }


            @media only screen and (max-width: 1199px) {

                .wpfm-text-highlight,
                .wpfm-halloween-promotional-banner-content .wpfm-banner-title {
                    font-size:15px;
                }

                .wpfm-spooktacular {
                    max-width: 102px;
                    line-height: 1.2;
                }

                .wpfm-regular-promotional-banner .regular-promotional-banner-content img {
                    max-width: 115px;
                }

                .wpfm-discount-text {
                    max-width: 186px;
                    line-height: 1.2;
                }

                .wpfm-halloween-promotional-banner-content span.wpfm-halloween-highlight {
                    font-size: 16px;
                }

                .wpfm-halloween-percentage {
                    font-size: 16px;
                }

                .wpfm-halloween-promotional-banner-content {
                    max-width: 760px;
                }

                .wpfm-banner-img img {
                    max-width: 130px;
                }

                .wpfm-halloween-banner-link {
                    font-size: 14px;
                }

            }

            @media only screen and (max-width: 991px) {
                .wpfm-promo-banner__container {
                    padding: 0px 10px;
                }

                .wpfm-promo-banner {
                    margin-top: 66px;
                    padding: 15px 0;
                }

                .wpfm-arrow-icon {
                    margin-left: 5px;
                }

                .wpfm-banner-img img {
                    max-width: 115px;
                }
            }

            @media only screen and (max-width: 767px) {

                .wpfm-promo-banner__container {
                    align-items: flex-start;
                }

                .wpfm-halloween-promotional-banner-content .wpfm-banner-title {
                    flex-direction: column;
                    gap: 0;
                }

                .wpfm-halloween-promotional-banner-content {
                   flex-direction: column;
                }
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
        update_option('rexfeed_hide_happy_new_year_deal_notification_bar', 'yes');
        echo json_encode( ['success' => true,] );
        wp_die();
    }
}