<?php

/**
 * SpecialOccasionBanner Class
 *
 * This class is responsible for displaying a special occasion banner in the WordPress admin.
 *
 * @package YourVendor\SpecialOccasionPlugin
 *
 * @since 7.3.18
 */
class Rex_Feed_Special_Occasion_Banner {

	/**
	 * The occasion identifier.
	 *
	 * @var string
	 *
	 * @since 7.3.18
	 */
	private $occasion;

	/**
	 * The start date and time for displaying the banner.
	 *
	 * @var int
	 *
	 * @since 7.3.18
	 */
	private $start_date;

	/**
	 * The end date and time for displaying the banner.
	 *
	 * @var int
	 *
	 * @since 7.3.18
	 */
	private $end_date;

	/**
	 * Constructor method for SpecialOccasionBanner class.
	 *
	 * @param string $occasion The occasion identifier.
	 * @param string $start_date The start date and time for displaying the banner.
	 * @param string $end_date The end date and time for displaying the banner.
	 *
	 * @since 7.3.18
	 */
	public function __construct( $occasion, $start_date, $end_date ) {
		$this->occasion   = "rex_feed_{$occasion}";
		$this->start_date = strtotime( $start_date );
		$this->end_date   = strtotime( $end_date );
	}

	/**
	 * Controls the initialization of certain admin-related functionalities based on conditions.
	 * It checks the current screen, defined allowed screens, product feed version availability,
	 * and date conditions to determine whether to display a banner and enqueue styles.
	 *
	 * @since 7.3.18
	 */
	public function init() {
		$current_date_time = current_time( 'timestamp' );

		 if (
		 	'hidden' !== get_option( $this->occasion, '' )
		 	&& !defined( 'REX_PRODUCT_FEED_PRO_VERSION' )
		 	&& ( $current_date_time >= $this->start_date && $current_date_time <= $this->end_date )
		 ) {
			// Add styles
			add_action( 'admin_head', [ $this, 'enqueue_css' ] );
			// Hook into the admin_notices action to display the banner
			add_action( 'admin_notices', [ $this, 'display_banner' ] );

            add_action( 'wp_ajax_rexfeed_hide_deal_notice', [ $this, 'hide_special_deal_notice' ] );
		 }
	}

	/**
	 * Updates an option on notice dismissal [for deal],
	 * so that deal notice doesn't appear again
	 *
	 * @return void
	 * @since 7.3.1
	 */
	public static function hide_special_deal_notice() {
		$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! wp_verify_nonce( $nonce, 'rex-wpfm-ajax' ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized request' ] );
		}

		$occasion = filter_input( INPUT_POST, 'occasion', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( $occasion ) {
			update_option( $occasion, 'hidden' );
			wp_send_json_success( [ 'message' => 'Success' ] );
		}
		wp_send_json_error( [ 'message' => 'An unknown error occurred!' ] );
	}

    /**
     * Calculate time remaining until Halloween
     *
     * @return array Time remaining in days, hours, and minutes
     */
    public function rex_get_halloween_countdown() {
	    $diff = $this->end_date - current_time( 'timestamp' );
	    return [
		    'days'  => sprintf("%02d", floor( $diff / ( 60 * 60 * 24 ) )),
		    'hours' => sprintf("%02d", floor( ( $diff % ( 60 * 60 * 24 ) ) / ( 60 * 60 ) ) ),
		    'mins'  => sprintf("%02d", floor( ( $diff % ( 60 * 60 ) ) / 60 ) ),
            'secs'  => sprintf("%02d", floor( $diff % 60 ) )
	    ];
    }
    

	/**
	 * Displays the special occasion banner if the current date and time are within the specified range.
	 *
	 * @since 7.3.18
	 */
	public function display_banner() {
        if ( ! defined( 'REX_SPECIAL_OCCASION_BANNER_SHOWN' ) ) {
            define( 'REX_SPECIAL_OCCASION_BANNER_SHOWN', true );
            $screen          = get_current_screen();
            $allowed_screens = [ 'dashboard', 'plugins', 'product-feed' ];
            $time_remaining  = $this->end_date - current_time( 'timestamp' );

            $countdown = $this->rex_get_halloween_countdown();

            if ( in_array( $screen->base, $allowed_screens ) || in_array( $screen->parent_base, $allowed_screens ) || in_array( $screen->post_type, $allowed_screens ) || in_array( $screen->parent_file, $allowed_screens ) ) {
                echo '<input type="hidden" id="rexfeed_special_occasion" name="rexfeed_special_occasion" value="'.$this->occasion.'">';
                ?>


                <!-- Name: Eid mubarak Notification Banner -->

                <div class="rex-feed-tb__notification pfm-banner" id="rexfeed_deal_notification">
                    <div class="banner-overflow">
                        <div class="rex-notification-counter">
                            <div class="rex-notification-counter__container">
                                <div class="rex-notification-counter__content">

                                    <figure class="rex-notification-counter__figure-logo">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ) .'./assets/icon/banner-images/wordpress-anniversary.webp'; ?>" alt="<?php esc_attr_e('WordPress anniversary offer logo', 'rex-product-feed'); ?>" class="rex-notification-counter__img">
                                    </figure>

                                    <figure class="rex-notification-counter__biggest-sale">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ) .'./assets/icon/banner-images/wordpress-annniversary-twenty-two-percent-discount.webp'; ?>" alt="<?php esc_attr_e('Biggest sale of the year!', 'rex-product-feed'); ?>" class="rex-notification-counter__img">
                                    </figure>

                                    <figure class="rex-notification-counter__figure-percentage">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ) .'./assets/icon/banner-images/pfm-logo.webp'; ?>" alt="<?php esc_attr_e('christmas special discount', 'rex-product-feed'); ?>" class="rex-notification-counter__img">
                                    </figure>

                                    <div id="rex-halloween-countdown" class="rex-notification-counter__countdown" aria-live="polite">
                                    <span class="screen-reader-text">
                                        <?php esc_html_e('Offer Countdown', 'rex-product-feed'); ?>
                                    </span>
                                        <ul class="rex-notification-counter__list">
                                            <?php foreach (['days', 'hours', 'mins', 'secs'] as $unit): ?>
                                                <li class="rex-notification-counter__item">
                                                <span id="rex-feed-halloween-<?php echo esc_attr($unit); ?>" class="rex-notification-counter__time">
                                                    <?php echo esc_html($countdown[$unit]); ?>
                                                </span>
                                                    <span class="rex-notification-counter__label">
                                                    <?php echo esc_html($unit); ?>
                                                </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <div class="rex-notification-counter__btn-area">
                                        <a
                                                href="<?php echo esc_url( 'https://rextheme.com/best-woocommerce-product-feed/pricing/?utm_source=website&utm_medium=plugin-ban-pfm&utm_campaign=wpanniv25'); ?>"
                                                class="rex-notification-counter__btn"
                                                target="_blank"
                                        >


                                        <span class="screen-reader-text">
                                            <?php esc_html_e('Click to view eid ul fitr sale products', 'rex-product-feed'); ?>
                                        </span>

                                            <?php esc_html_e('Get The Deal Now', 'rex-product-feed'); ?>
                                            <!-- <strong class="rex-notification-counter__stroke-font">30%</strong>  -->
                                            <?php //esc_html_e('OFF', 'rex-product-feed'); ?>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rex-feed-tb__cross-top" id="rexfeed_deal_close_btn">
                        <svg width="12" height="13" fill="none" viewBox="0 0 12 13" xmlns="http://www.w3.org/2000/svg">
                            <path stroke="#7A8B9A" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 1.97L1 11.96m0-9.99l10 9.99" />
                        </svg>
                    </div>
                </div>
                <!-- .rex-feed-tb-notification end -->

                <script>
                    rexfeed_deal_countdown_handler();
                    /**
                     * Handles count down on deal notice
                     *
                     * @since 7.3.18
                     */
                    function rexfeed_deal_countdown_handler() {
                        // Pass the calculated time remaining to JavaScript
                        let timeRemaining = <?php echo $time_remaining; ?>;

                        // Update the countdown every second
                        setInterval(function() {
                            const daysElement = document.getElementById('rex-feed-halloween-days');
                            const hoursElement = document.getElementById('rex-feed-halloween-hours');
                            const minutesElement = document.getElementById('rex-feed-halloween-mins');
                            const secondsElement = document.getElementById('rex-feed-halloween-secs');

                            timeRemaining--;

                            if (daysElement && hoursElement && minutesElement && secondsElement) {
                                // Decrease the remaining time

                                // Calculate new days, hours, minutes, and seconds
                                let days = Math.floor(timeRemaining / (60 * 60 * 24)).toString().padStart(2, '0');
                                let hours = Math.floor((timeRemaining % (60 * 60 * 24)) / (60 * 60)).toString().padStart(2, '0');
                                let minutes = Math.floor((timeRemaining % (60 * 60)) / 60).toString().padStart(2, '0');
                                let seconds = (timeRemaining % 60).toString().padStart(2, '0');

                                // Update the HTML
                                daysElement.textContent = days;
                                hoursElement.textContent = hours;
                                minutesElement.textContent = minutes;
                                secondsElement.textContent = seconds;

                            }

                            // Check if the countdown has ended
                            if (timeRemaining <= 0) {
                                rexfeed_hide_deal_notice();
                            }
                        }, 1000); // Update every second
                    }

                    document.getElementById('rexfeed_deal_close_btn').addEventListener('click', rexfeed_hide_deal_notice);

                    /**
                     * Hide deal notice and save parameter to keep it hidden for future
                     *
                     * @since 7.3.2
                     */
                    function rexfeed_hide_deal_notice() {
                        document.getElementById('rexfeed_deal_notification').style.display = 'none';

                        jQuery.ajax({
                            type: "POST",
                            url: rex_wpfm_ajax?.ajax_url,
                            data: {
                                action: "rexfeed_hide_deal_notice",
                                nonce : rex_wpfm_ajax.ajax_nonce,
                                occasion: document.getElementById('rexfeed_special_occasion')?.value
                            },
                        });
                    }
                </script>

                <?php
            }
        }

	}

    /**
     * Adds internal CSS styles for the special occasion banners.
     *
     * @since 7.3.18
     */
    public function enqueue_css() {
        $plugin_dir_url = plugin_dir_url(__FILE__ );
        ?>
        <style id="promotional-banner-style" type="text/css">
            @font-face {
                font-family: 'Inter';
                src: url(<?php echo "{$plugin_dir_url}assets/fonts/campaign-font/Inter-Bold.woff2"; ?>) format('woff2');
                font-weight: 700;
                font-style: normal;
                font-display: swap;
            }

            @font-face {
                font-family: 'Inter';
                src: url(<?php echo "{$plugin_dir_url}assets/fonts/campaign-font/Inter-SemiBold.woff2"; ?>) format('woff2');
                font-weight: 600;
                font-style: normal;
                font-display: swap;
            }

            @font-face {
                font-family: "Inter";
                src: url(<?php echo "{$plugin_dir_url}assets/fonts/campaign-font/Inter-Regular.woff2"; ?>) format('woff2');
                font-weight: 400;
                font-style: normal;
                font-display: swap;
            }

            .rex-feed-tb__notification,
            .rex-feed-tb__notification * {
                box-sizing: border-box;
            }

            .rex-feed-tb__notification.pfm-banner {
                background-color: #00B4FF;
                width: calc(100% - 20px);
                margin: 50px 0 20px;
                background-image: url(<?php echo "{$plugin_dir_url}assets/icon/banner-images/wordpress-anniversary-banner-bg.webp"; ?>);
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
                position: relative;
                border: none;
                box-shadow: none;
                display: block;
                max-height: 110px;
                object-fit: cover;
                z-index: 0;
            }

            .pfm-banner .rex-notification-counter {
                position: relative;
                z-index: 1111;
            }

            .pfm-banner .rex-notification-counter figure {
                margin: 0;
            }

            .pfm-banner .rex-notification-counter__container {
                position: relative;
                width: 100%;
                max-height: 110px;
                max-width: 1312px;
                margin: 0 auto;
                padding: 0px 15px;
            }
            .pfm-banner .rex-notification-counter__content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 20px;
            }
            .pfm-banner .rex-notification-counter__biggest-sale {
                max-width: 180px;
            }
            .pfm-banner .rex-notification-counter__figure-logo {
                max-width: 220px;
            }
            .pfm-banner .rex-notification-counter__figure-percentage {
                max-width: 108px;
            }
            .pfm-banner .rex-notification-counter__img {
                width: 100%;
                max-width: 100%;
                display: block;
            }
            .pfm-banner .rex-notification-counter__list {
                display: flex;
                justify-content: center;
                gap: 10px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            @media only screen and (max-width: 991px) {
                .pfm-banner .rex-notification-counter__list {
                    gap: 10px;
                }
            }
            @media only screen and (max-width: 767px) {
                .pfm-banner .rex-notification-counter__list {
                    align-items: center;
                    justify-content: center;
                    gap: 15px;
                }
            }
            .pfm-banner .rex-notification-counter__item {
                display: flex;
                flex-direction: column;
                width: 49px;
                font-family: "Inter";
                font-size: 14px;
                font-weight: 400;
                line-height: 1;
                letter-spacing: 0.75px;
                text-transform: uppercase;
                text-align: center;
                color: #111827;
                margin: 0;
            }
            @media only screen and (max-width: 1199px) {
                .pfm-banner .rex-notification-counter__item {
                    width: 44px;
                    font-size: 12px;
                }
            }
            @media only screen and (max-width: 991px) {
                .pfm-banner .rex-notification-counter__item {
                    font-size: 10px;
                }
            }
            @media only screen and (max-width: 767px) {
                .pfm-banner .rex-notification-counter__item {
                    font-size: 13px;
                    width: 47px;
                }
            }
            .pfm-banner .rex-notification-counter__label {
                font-weight: 400;
            }

            .pfm-banner .rex-notification-counter__time {
                    position: relative;
                    font-size: 28px;
                    font-family: "Inter";
                    font-weight: 600;
                    line-height: normal;
                    letter-spacing: -.56px;
                    color: #211cfd;
                    text-align: center;
                    height: 40px;
                    padding: 1px 1px 0;
                    display: -webkit-box;
                    display: -moz-box;
                    display: -ms-flexbox;
                    display: flex;
                    -webkit-box-align: center;
                    -moz-box-align: center;
                    -ms-flex-align: center;
                    align-items: center;
                    -webkit-box-pack: center;
                    -moz-box-pack: center;
                    -ms-flex-pack: center;
                    justify-content: center;
                    margin-bottom: 10px;
                    border-radius: 10px;
                    background: -moz-linear-gradient(117deg, #cccbff -2.66%, #fff 85.82%);
                    background: -o-linear-gradient(117deg, #cccbff -2.66%, #fff 85.82%);
                    background: linear-gradient(333deg, #cccbff -2.66%, #fff 85.82%);
                    -webkit-box-shadow: 0 3px 0 0 #211cfd;
                    box-shadow: 0 3px 0 0 #211cfd;
                    background-origin: border-box;
                    background-clip: content-box, border-box;
            }


            .pfm-banner .rex-notification-counter__time:before {
                content: "";
                position: absolute;
                inset: 0;
                border-radius: 9px;
                padding: 1px;
                background: linear-gradient(180deg, #00B4FF 0%, #211CFD 99%);
                -webkit-mask: linear-gradient(white 0 0) content-box, linear-gradient(white 0 0);
                -webkit-mask-composite: xor;
                mask-composite: exclude;
            }

            @media only screen and (max-width: 1199px) {
                .pfm-banner .rex-notification-counter__time {
                    font-size: 30px;
                }

            }

            @media only screen and (max-width: 991px) {
                .pfm-banner .rex-notification-counter__time {
                    font-size: 24px;
                }
            }
            .pfm-banner .rex-notification-counter__btn-area {
                display: flex;
                align-items: flex-end;
                justify-content: flex-end;
            }

            .pfm-banner .rex-notification-counter__btn {
                position: relative;
                background-color: #216DEF;
                font-family: "Inter";
                font-weight: 500;
                font-size: 18px;
                line-height: 1;
                color: #fff;
                text-align: center;
                filter: drop-shadow(0px 30px 60px rgba(21, 19, 119, 0.20));
                padding: 19px 33px;
                display: inline-block;
                border-radius: 50px;
                cursor: pointer;
                transition: all 0.3s ease;
                text-decoration: none;
                box-shadow: none;
            }
            .pfm-banner .rex-notification-counter__btn:hover {
                background-color: #fff;
                color: #216DEF;
            }
            .pfm-banner .rex-notification-counter__stroke-font {
                font-size: 26px;
                font-family: "Inter";
                font-weight: 600;
            }

            .rex-feed-tb__notification.pfm-banner .rex-feed-tb__cross-top {
                position: absolute;
                top: -10px;
                right: -9px;
                background: #fff;
                border: none;
                padding: 0;
                border-radius: 50%;
                cursor: pointer;
                z-index: 9999;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            

            @media only screen and (max-width: 1599px) {

                .pfm-banner .rex-notification-counter__container {
                    max-width: 1170px;
                }

                .pfm-banner .rex-notification-counter__btn {
                    font-size: 16px;
                }

                .pfm-banner .rex-notification-counter__stroke-font {
                    font-size: 22px;
                }

            }

            @media only screen and (max-width: 1399px) {
                .pfm-banner .rex-feed-tb__notification {
                    background-position: left center;
                }

                .pfm-banner .rex-notification-counter__container {
                    max-width: 1140px;
                }
                .pfm-banner .rex-notification-counter__biggest-sale {
                    max-width: 160px;
                }
                .pfm-banner .rex-notification-counter__figure-percentage {
                    max-width: 85px;
                }

                .pfm-banner .rex-notification-counter__list {
                    gap: 8px;
                }
                .pfm-banner .rex-notification-counter__item {
                    width: 44px;
                    font-size: 12px;
                }
                .pfm-banner .rex-notification-counter__time {
                    font-size: 24px;
                    height: 36px;
                }

                .pfm-banner .rex-notification-counter__btn {
                    padding: 15px 20px;
                }

            }

            @media only screen and (max-width: 1199px) {

                .rex-feed-tb__notification.pfm-banner .rex-feed-tb__cross-top {
                    top: -7px;
                    right: -7px;
                    width: 22px;
                    height: 22px;
                }
                .rex-feed-tb__notification.pfm-banner .rex-feed-tb__cross-top svg {
                    width: 10px;
                    height: 10px;
                }

                .pfm-banner .rex-notification-counter__container {
                    max-width: 820px;
                }
                .pfm-banner .rex-notification-counter__biggest-sale {
                    max-width: 110px;
                }
                .pfm-banner .rex-notification-counter__figure-logo {
                    max-width: 130px;
                }
                .pfm-banner .rex-notification-counter__figure-percentage {
                    max-width: 60px;
                }
                .pfm-banner .rex-notification-counter__time {
                    font-size: 16px;
                    height: 30px;
                    font-weight: 500;
                }
                .pfm-banner .rex-notification-counter__item {
                    font-size: 11px;
                    width: 35px;
                }
                .pfm-banner .rex-notification-counter__btn {
                    font-size: 13px;
                }
                .pfm-banner .rex-notification-counter__stroke-font {
                    font-size: 20px;
                }

                .rex-feed-tb__notification.pfm-banner {
                    background-position: right;
                }

                .pfm-banner .rex-notification-counter__btn {
                    padding: 10px 18px;
                }

            }

            @media only screen and (max-width: 991px) {

                .pfm-banner .rex-notification-counter__content {
                    padding: 5px 0;
                }

                .pfm-banner .rex-notification-counter__biggest-sale {
                    max-width: 100px;
                }
                .pfm-banner .rex-notification-counter__figure-logo {
                    max-width: 120px;
                }

                .rex-feed-tb__notification.pfm-banner {
                    margin: 80px 0 20px;
                }

                .pfm-banner .rex-notification-counter__item {
                    width: 36px;
                    font-size: 10px;
                }
                .pfm-banner .rex-notification-counter__time {
                    height: 28px;
                    margin-bottom: 8px;
                }

                .pfm-banner .rex-notification-counter__figure-percentage {
                    max-width: 55px;
                }

                .pfm-banner .rex-notification-counter__btn {
                    padding: 10px 14px;
                    border-radius: 6px;
                }

            }

        </style>

        <?php
    }
}