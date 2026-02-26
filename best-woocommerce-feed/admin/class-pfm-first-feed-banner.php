<?php
/**
 * PFM First Feed Banner Class
 *
 * This class handles the display of an encouragement banner for users
 * who haven't published their first feed yet.
 *
 * @since 7.4.57
 */
class PFM_First_Feed_Banner {
    /**
     * Option name for storing dismissal timestamp
     *
     * @var string
     * @since 7.4.57
     */
    private $dismissal_option = 'pfm_feed_banner_dismissed';

    /**
     * Banner ID for tracking
     *
     * @var string
     * @since 7.4.57
     */
    private $banner_id = 'pfm-feed-encourage-banner';

    /**
     * Constructor
     * @since 7.4.57
     */
    public function __construct() {
        if ($this->should_show_banner()) {
            add_action('admin_notices', array($this, 'display_banner'));
            add_action('admin_head', array($this, 'add_styles'));
            add_action('wp_ajax_pfm_dismiss_feed_banner', array($this, 'handle_dismiss_banner'));
        }
    }

    /**
     * Check if banner should be shown
     *
     * @return bool
     * @since 7.4.57
     */
    private function should_show_banner() {
        // Check if banner was dismissed (7 days ago or less).
        $dismissed_time = get_option($this->dismissal_option, 0);
        if ($dismissed_time && (time() - $dismissed_time) < (7 * DAY_IN_SECONDS)) {
            return false;
        }

        // Check if user has published feeds.
        $published_feeds = $this->get_published_feeds_count();

        // Show banner if no feeds exist, or if feeds exist but Pro is not active.
        if ($published_feeds === 0) {
            return true;
        } elseif ($published_feeds > 0 && !defined('REX_PRODUCT_FEED_PRO_VERSION')) {
            return true;
        }
        return false;
    }

    /**
     * Get count of published feeds.
     *
     * @return int
     * @since 7.4.57
     */
    private function get_published_feeds_count() {
        $args = array(
            'post_type'      => 'product-feed',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        );
        $feeds = get_posts($args);
        return count($feeds);
    }

    /**
     * Display the banner
     * @since 7.4.57
     */
    public function display_banner() {
        if (!$this->is_pfm_admin_page()) {
            return;
        }
        $this->render_banner_html();
    }

    /**
     * Render banner HTML
     * @since 7.4.57
     */
    private function render_banner_html() {
        $create_feed_url = admin_url('post-new.php?post_type=product-feed');
        $upgrade_url = 'https://rextheme.com/best-woocommerce-product-feed/pricing/';
        $published_feeds = $this->get_published_feeds_count();

        if ($published_feeds === 0) {
            $message = sprintf(
                wp_kses(
                    __('You haven\'t published your first feed yet. <a href="%s">Create and publish your first feed</a> to get started!', 'rex-product-feed'),
                    array(
                        'a' => array(
                            'href' => array(),
                            'class' => array()
                        )
                    )
                ),
                esc_url($create_feed_url)
            );
        } else if ($published_feeds > 0 && !defined('REX_PRODUCT_FEED_PRO_VERSION')) {
            $message = sprintf(
                wp_kses(
                    __('You\'ve published your first feed! <a href="%s" target="_blank" rel="noopener">Upgrade to Pro</a> for unlimited feeds, advanced filters, and premium support!', 'rex-product-feed'),
                    array(
                        'a' => array(
                            'href' => array(),
                            'target' => array(),
                            'rel' => array(),
                            'class' => array()
                        )
                    )
                ),
                esc_url($upgrade_url)
            );
        } else {
            // If Pro is active, do not show the banner.
            return;
        }
        ?>
    <div id="<?php echo esc_attr($this->banner_id); ?>" class="pfm-feed-encourage-banner">
            <div class="pfm-feed-encourage-banner__content">
                <div class="pfm-feed-encourage-banner__text">
                    <p><?php echo wp_kses_post($message); ?></p>
                </div>
            </div>
            <button type="button" class="pfm-feed-encourage-banner__close" aria-label="Dismiss this notice">
                <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23 11.5C23 17.8513 17.8513 23 11.5 23C5.14873 23 0 17.8513 0 11.5C0 5.14873 5.14873 0 11.5 0C17.8513 0 23 5.14873 23 11.5Z" fill="#FFECE2"/>
                    <path d="M16 8.63687L14.3631 7L11.5 9.86313L8.63687 7L7 8.63687L9.86313 11.5L7 14.3631L8.63687 16L11.5 13.1369L14.3631 16L16 14.3631L13.1369 11.5L16 8.63687Z" fill="#E56829"/>
                </svg>
            </button>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $(document).on('click', '#<?php echo esc_attr($this->banner_id); ?> .pfm-feed-encourage-banner__close', function() {
                pfm_dismiss_feed_banner();
            });
            function pfm_dismiss_feed_banner() {
                var $banner = $('#<?php echo esc_attr($this->banner_id); ?>');
                $banner.fadeOut(300, function() {
                    $(this).remove();
                });
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pfm_dismiss_feed_banner',
                        nonce: '<?php echo wp_create_nonce('pfm_dismiss_banner'); ?>'
                    },
                    success: function(response) {
                        console.log('Banner dismissed successfully');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error dismissing banner:', error);
                    }
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Add banner styles
     * @since 7.4.57
     */
    public function add_styles() {
        ?>
        <style id="pfm-feed-encourage-banner-styles" type="text/css">
            .pfm-feed-encourage-banner {
                position: relative;
                padding: 16px 46px 16px 20px;
                border: 1px solid #201cfe;
                border-radius: 4px;
                background: #fff;
                margin: 35px 20px 0 0;
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
            }
            .pfm-feed-encourage-banner__content {
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                position: relative;
            }
            .pfm-feed-encourage-banner__text {
                flex: 1;
                text-align: center;
            }
            .pfm-feed-encourage-banner__text p {
                margin: 0;
                font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-weight: 600;
                font-style: normal;
                font-size: 16px;
                line-height: 100%;
                letter-spacing: 0%;
                text-align: center;
                color: #3c434a;
            }
            .pfm-feed-encourage-banner__text a {
                color: #201cfe;
                text-decoration: none;
                font-weight: 500;
                font-style: normal;
                border-bottom: 1px solid #201cfe;
            }
            .pfm-feed-encourage-banner__close {
                position: absolute;
                top: 50%;
                right: 12px;
                transform: translateY(-50%);
                background: none;
                border: none;
                padding: 0;
                cursor: pointer;
                width: 23px;
                height: 23px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: opacity 0.2s ease;
            }
            .pfm-feed-encourage-banner__close:hover {
                opacity: 0.8;
            }
            .pfm-feed-encourage-banner__close:active {
                transform: translateY(-50%) scale(0.95);
            }
            .pfm-feed-encourage-banner__close svg {
                display: block;
                width: 23px;
                height: 23px;
            }
            .pfm-feed-encourage-banner.dismissed {
                display: none;
            }
            @media screen and (max-width: 782px) {
                .pfm-feed-encourage-banner {
                    padding: 16px 46px 16px 16px;
                    margin: 16px 16px 0 0;
                }
                .pfm-feed-encourage-banner__content {
                    flex-direction: column;
                    text-align: center;
                    justify-content: center;
                }
                .pfm-feed-encourage-banner__close {
                    right: 8px;
                }
            }
        </style>
        <?php
    }

    /**
     * Check if current screen is a PFM admin page
     *
     * @return bool
     * @since 7.4.57
     */
    private function is_pfm_admin_page() {
        if (!is_admin() || !function_exists('get_current_screen')) {
            return false;
        }
        $screen = get_current_screen();
        if (!$screen) {
            return false;
        }
        $pfm_pages = array(
            'product-feed',
            'edit-product-feed',
            'product-feed_page_wpfm-license',
            'product-feed_page_category_mapping',
            'product-feed_page_merchant_settings',
            'product-feed_page_wpfm_dashboard',
        ); 
        return in_array($screen->id, $pfm_pages, true);
    }

    /**
     * Handle AJAX request to dismiss banner
     * @since 7.4.57
     */
    public function handle_dismiss_banner() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pfm_dismiss_banner')) {
            wp_die('Invalid nonce');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        update_option($this->dismissal_option, time());
        wp_send_json_success(array('message' => __('Banner dismissed successfully', 'rex-product-feed')));
    }
}
