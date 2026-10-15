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
        $screen = get_current_screen();

        // Define a unique constant for each plugin to avoid conflicts
        $plugin_banner_constant = 'REX_SPECIAL_OCCASION_BANNER_SHOWN_WPFM';

        $allowed_screens = [ 'plugins', 'dashboard', 'product-feed' ];

        $should_show_banner = false;

        if ($screen->base === 'plugins' || $screen->base === 'dashboard') {
            if (!defined('REX_SPECIAL_OCCASION_BANNER_SHOWN_GLOBAL')) {
                $should_show_banner = true;
                define('REX_SPECIAL_OCCASION_BANNER_SHOWN_GLOBAL', true);
            }
        } elseif (in_array($screen->base, $allowed_screens) ||
            in_array($screen->parent_base, $allowed_screens) ||
            in_array($screen->post_type, $allowed_screens) ||
            in_array($screen->parent_file, $allowed_screens)) {
            if (!defined($plugin_banner_constant)) {
                $should_show_banner = true;
                define($plugin_banner_constant, true);
            }
        }

        if ($should_show_banner) {
            $time_remaining = $this->end_date - current_time('timestamp');
            $countdown = $this->rex_get_halloween_countdown();

            echo '<input type="hidden" id="rexfeed_special_occasion" name="rexfeed_special_occasion" value="' . esc_attr( $this->occasion ) . '">';
            ?>
            <!-- Name: Halloween 2026 Notification Banner -->
            <div class="rex-feed-tb__notification pfm-banner" id="rexfeed_deal_notification">
                <div class="rex-notification-counter">
                    <div class="rex-notification-counter__container">
                        <div class="rex-notification-counter__content">

                            <figure class="rex-notification-counter__figure-logo">
                                <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/icon/banner-images/halloween-img.webp' ); ?>" alt="<?php esc_attr_e( 'Halloween', 'rex-product-feed' ); ?>" class="rex-notification-counter__img">
                            </figure>

                            <div class="rex-notification-counter__text">
                                <p class="rex-notification-counter__title">
                                    <?php esc_html_e( 'Spooktacular Halloween Sale', 'rex-product-feed' ); ?>
                                </p>
                                <p class="rex-notification-counter__offer">
                                    <?php esc_html_e( 'GET 30% OFF!', 'rex-product-feed' ); ?>
                                    <span aria-hidden="true">&#127875;</span>
                                </p>
                            </div>

                            <div id="rex-halloween-countdown" class="rex-notification-counter__countdown" aria-live="polite">
                                <span class="screen-reader-text">
                                    <?php esc_html_e( 'Offer Countdown', 'rex-product-feed' ); ?>
                                </span>
                                <ul class="rex-notification-counter__list">
                                    <?php
                                    $countdown_labels = [
                                        'days'  => __( 'Day', 'rex-product-feed' ),
                                        'hours' => __( 'Hr', 'rex-product-feed' ),
                                        'mins'  => __( 'Min', 'rex-product-feed' ),
                                        'secs'  => __( 'Sec', 'rex-product-feed' ),
                                    ];
                                    foreach ( $countdown_labels as $unit => $label ) :
                                        ?>
                                        <li class="rex-notification-counter__item">
                                            <span id="rex-feed-halloween-<?php echo esc_attr( $unit ); ?>" class="rex-notification-counter__time">
                                                <?php echo esc_html( $countdown[ $unit ] ); ?>
                                            </span>
                                            <span class="rex-notification-counter__label">
                                                <?php echo esc_html( $label ); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="rex-notification-counter__btn-area">
                                <a href="<?php echo esc_url( 'https://rextheme.com/best-woocommerce-product-feed/pricing/?utm_source=website&utm_medium=plugin-ban-pfm&utm_campaign=pfm-halloween2026' ); ?>" class="rex-notification-counter__btn" target="_blank" rel="noopener noreferrer">
                                    <span class="screen-reader-text">
                                        <?php esc_html_e( 'Click to view Halloween sale offers', 'rex-product-feed' ); ?>
                                    </span>
                                    <?php esc_html_e( 'Claim Your Deal', 'rex-product-feed' ); ?>
                                    <svg width="10" height="10" fill="none" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M1 9L9 1m0 0H2.5M9 1v6.5" />
                                    </svg>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

                <button type="button" class="rex-feed-tb__cross-top" id="rexfeed_deal_close_btn" aria-label="<?php esc_attr_e( 'Dismiss Halloween offer', 'rex-product-feed' ); ?>">
                    <svg width="8" height="8" fill="none" viewBox="0 0 8 8" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke="currentColor" stroke-linecap="round" stroke-width="1.2" d="M7.5.5l-7 7m0-7l7 7" />
                    </svg>
                </button>
            </div>

            <script>
                rexfeed_deal_countdown_handler();

                function rexfeed_deal_countdown_handler() {
                    let timeRemaining = <?php echo (int) $time_remaining; ?>;

                    const countdownTimer = setInterval(function() {
                        const daysElement = document.getElementById('rex-feed-halloween-days');
                        const hoursElement = document.getElementById('rex-feed-halloween-hours');
                        const minutesElement = document.getElementById('rex-feed-halloween-mins');
                        const secondsElement = document.getElementById('rex-feed-halloween-secs');

                        timeRemaining--;

                        if (daysElement && hoursElement && minutesElement && secondsElement) {
                            let days = Math.floor(timeRemaining / (60 * 60 * 24)).toString().padStart(2, '0');
                            let hours = Math.floor((timeRemaining % (60 * 60 * 24)) / (60 * 60)).toString().padStart(2, '0');
                            let minutes = Math.floor((timeRemaining % (60 * 60)) / 60).toString().padStart(2, '0');
                            let seconds = (timeRemaining % 60).toString().padStart(2, '0');

                            daysElement.textContent = days;
                            hoursElement.textContent = hours;
                            minutesElement.textContent = minutes;
                            secondsElement.textContent = seconds;
                        }

                        if (timeRemaining <= 0) {
                            clearInterval(countdownTimer);
                            rexfeed_hide_deal_notice();
                        }
                    }, 1000);
                }

                document.getElementById('rexfeed_deal_close_btn').addEventListener('click', rexfeed_hide_deal_notice);

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
                src: url(<?php echo esc_url( "{$plugin_dir_url}assets/fonts/campaign-font/Inter-Bold.woff2" ); ?>) format('woff2');
                font-weight: 700;
                font-style: normal;
                font-display: swap;
            }

            @font-face {
                font-family: 'Inter';
                src: url(<?php echo esc_url( "{$plugin_dir_url}assets/fonts/campaign-font/Inter-SemiBold.woff2" ); ?>) format('woff2');
                font-weight: 600;
                font-style: normal;
                font-display: swap;
            }

            @font-face {
                font-family: 'Inter';
                src: url(<?php echo esc_url( "{$plugin_dir_url}assets/fonts/campaign-font/Inter-Regular.woff2" ); ?>) format('woff2');
                font-weight: 400;
                font-style: normal;
                font-display: swap;
            }

            .rex-feed-tb__notification,
            .rex-feed-tb__notification * {
                box-sizing: border-box;
            }

            /* Measured from the 1920px design: 70px bar, content block centered. */
            .rex-feed-tb__notification.pfm-banner {
                position: relative;
                display: block;
                width: calc(100% - 20px);
                height: 70px;
                margin: 50px 0 20px;
                padding: 0 60px;
                border: none;
                box-shadow: none;
                background: transparent;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                isolation: isolate;
            }

            /* Navy gradient, more transparent at the top and towards the right, as in the design. */
            .rex-feed-tb__notification.pfm-banner::before {
                content: '';
                position: absolute;
                inset: 0;
                z-index: -1;
                /* background: linear-gradient(90deg, #1c1aa6 0%, #1c19a6 20%, #18148a 47%, #131177 62%, #0f0d65 75%, #100e65 100%); */
                background: linear-gradient(280deg, #100E65 35.16%, #1D19A6 77.3%);
            }

            .pfm-banner .rex-notification-counter,
            .pfm-banner .rex-notification-counter__container,
            .pfm-banner .rex-notification-counter__content {
                height: 100%;
            }

            .pfm-banner .rex-notification-counter__content {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .pfm-banner .rex-notification-counter figure {
                margin: 0;
            }

            .pfm-banner .rex-notification-counter__figure-logo {
                flex-shrink: 0;
                width: 174px;
                transform: translateY(5px);
            }

            .pfm-banner .rex-notification-counter__img {
                display: block;
                width: 100%;
                max-width: 100%;
                height: auto;
            }

            .pfm-banner .rex-notification-counter__text {
                flex-shrink: 0;
                align-self: flex-start;
                margin-left: 25px;
                padding-top: 11px;
                white-space: nowrap;
            }

            .pfm-banner .rex-notification-counter__title,
            .pfm-banner .rex-notification-counter__offer {
                margin: 0;
                padding: 0;
            }

            .pfm-banner .rex-notification-counter__title {
                font-size: 16px;
                font-weight: 400;
                line-height: 20px;
                color: #fff;
            }

            .pfm-banner .rex-notification-counter__offer {
                display: flex;
                align-items: center;
                margin-top: 3px;
                font-size: 24px;
                font-weight: 700;
                line-height: 29px;
                color: #24ec2c;
            }

            .pfm-banner .rex-notification-counter__offer span {
                margin-left: 6px;
                font-size: 20px;
                line-height: 1;
            }

            .pfm-banner .rex-notification-counter__countdown {
                flex-shrink: 0;
                margin-left: 46px;
            }

            .pfm-banner .rex-notification-counter__list {
                display: flex;
                gap: 3px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .pfm-banner .rex-notification-counter__item {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: 53px;
                height: 50px;
                margin: 0;
                background: #100f7b;
                border: 1px solid #312faa;
                border-radius: 2px;
            }

            .pfm-banner .rex-notification-counter__time {
                font-size: 20px;
                font-weight: 700;
                line-height: 20px;
                color: #fff;
            }

            .pfm-banner .rex-notification-counter__label {
                margin-top: 7px;
                font-size: 12px;
                font-weight: 400;
                line-height: 12px;
                text-transform: uppercase;
                color: #cfcfe5;
            }

            .pfm-banner .rex-notification-counter__btn-area {
                flex-shrink: 0;
                margin-left: 81px;
            }

            .pfm-banner .rex-notification-counter__btn {
                display: inline-flex;
                align-items: center;
                gap: 11px;
                height: 38px;
                padding: 0 19px;
                font-size: 15px;
                font-weight: 600;
                line-height: 1;
                color: #000;
                text-decoration: none;
                white-space: nowrap;
                background: #24ec2c;
                border-radius: 3px;
                box-shadow: none;
                transition: background-color .2s ease;
            }

            .pfm-banner .rex-notification-counter__btn:hover,
            .pfm-banner .rex-notification-counter__btn:focus {
                color: #000;
                background: #1fd426;
            }

            .rex-feed-tb__notification.pfm-banner .rex-feed-tb__cross-top {
                position: absolute;
                top: 50%;
                right: 41px;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 16px;
                height: 16px;
                padding: 0;
                margin-top: -8px;
                color: #7e7e7e;
                background: transparent;
                border: none;
                cursor: pointer;
            }

            .rex-feed-tb__notification.pfm-banner .rex-feed-tb__cross-top:hover {
                color: #fff;
            }

            @media only screen and (max-width: 1299px) {
                .pfm-banner .rex-notification-counter__countdown {
                    margin-left: 32px;
                }

                .pfm-banner .rex-notification-counter__btn-area {
                    margin-left: 40px;
                }

                .rex-feed-tb__notification.pfm-banner .rex-feed-tb__cross-top {
                    right: 16px;
                }
            }

            @media only screen and (max-width: 1099px) {
                .rex-feed-tb__notification.pfm-banner {
                    height: auto;
                    margin: 80px 0 20px;
                    padding: 12px 40px;
                }

                .pfm-banner .rex-notification-counter__content {
                    flex-wrap: wrap;
                    gap: 12px 24px;
                }

                .pfm-banner .rex-notification-counter__text {
                    align-self: center;
                    padding-top: 0;
                }

                .pfm-banner .rex-notification-counter__text,
                .pfm-banner .rex-notification-counter__countdown,
                .pfm-banner .rex-notification-counter__btn-area {
                    margin-left: 0;
                }

                .rex-feed-tb__notification.pfm-banner .rex-feed-tb__cross-top {
                    top: 12px;
                    margin-top: 0;
                }
            }
        </style>

        <?php
    }
}