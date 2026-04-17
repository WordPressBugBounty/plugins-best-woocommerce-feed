<?php
/**
 * Dashboard banner promoting WPFunnels to PFM users.
 *
 * Shown on the product-feed list screen when the user has at least one feed
 * that has been live for 48+ hours.
 *
 * Dismiss behaviour
 *   "Dismiss"          → 30-day transient  (pfm_wpfunnels_banner_temp_{user_id})
 *   "Don't show again" → permanent user_meta (pfm_wpfunnels_banner_dismissed)
 *
 * PostHog events (fired via the Linno telemetry action)
 *   pfm_wpfunnels_banner_impression  props: user_feed_count, pfm_version
 *   pfm_wpfunnels_banner_click       props: user_feed_count, pfm_version
 *   pfm_wpfunnels_banner_dismiss     props: user_feed_count, pfm_version
 *
 * @since 7.4.78
 */
class Rex_Product_Feed_Dashboard_Banner {

    /**
     * Register hooks.
     */
    public function __construct() {
        add_filter( 'views_edit-product-feed', array( $this, 'render_before_views' ) );
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    /**
     * Output the banner then pass the views array through unchanged.
     * Hooked on `views_edit-product-feed` so it renders between the h1
     * and the All | Published filter tabs.
     *
     * @param array $views Existing view links.
     * @return array
     */
    public function render_before_views( array $views ): array {
        if ( ! $this->should_show() ) {
            return $views;
        }

        $cta_url    = 'https://rextheme.com/amazons-secrect-to-profitability/?utm_source=pfm_dashboard&utm_medium=banner&utm_campaign=pfm_crosspromo';
        $feed_count = $this->get_feed_count();
        $ajax_url   = admin_url( 'admin-ajax.php' );
        $nonce      = wp_create_nonce( 'rex-wpfm-ajax' );
        ?>

        <div class="pfm-wpfunnels-banner"
             data-feed-count="<?php echo esc_attr( $feed_count ); ?>"
             data-pfm-version="<?php echo esc_attr( WPFM_VERSION ); ?>"
             data-ajax-url="<?php echo esc_attr( $ajax_url ); ?>"
             data-nonce="<?php echo esc_attr( $nonce ); ?>">

            <p class="pfm-wpfunnels-banner__message">
                <strong><?php esc_html_e( 'High traffic, low AOV?', 'rex-product-feed' ); ?></strong>
                <?php esc_html_e( "Your feed is generating clicks. Are you maximizing the value of every visitor? See how to add a 'Cashier' to your checkout.", 'rex-product-feed' ); ?>
                <a href="<?php echo esc_url( $cta_url ); ?>"
                   class="pfm-wpfunnels-banner__cta"
                   target="_blank"
                   rel="noopener noreferrer">
                    <?php esc_html_e( 'Learn the Strategy', 'rex-product-feed' ); ?>
                </a>
            </p>

            <button type="button" class="pfm-wpfunnels-banner__close" title="<?php esc_attr_e( 'Dismiss', 'rex-product-feed' ); ?>">&#x00D7;</button>
        </div>

        <?php $this->render_styles(); ?>
        <?php $this->render_script(); ?>
        <?php

        return $views;
    }

    /**
     * Output inline styles (once, scoped to the banner element).
     */
    private function render_styles(): void {
        ?>
        <style id="pfm-wpfunnels-banner-css">
        .pfm-wpfunnels-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 10px 14px;
            margin: 8px 0 14px;
        }
        .pfm-wpfunnels-banner__message {
            margin: 0;
            font-size: 13px;
            color: #3c434a;
            line-height: 1.5;
        }
        .pfm-wpfunnels-banner__message strong {
            color: #1d2327;
        }
        .pfm-wpfunnels-banner__cta {
            font-size: 13px;
            font-weight: 600;
            text-decoration: underline;
            white-space: nowrap;
            margin-left: 4px;
        }
        .pfm-wpfunnels-banner__close {
            background: none !important;
            border: none !important;
            padding: 0 !important;
            box-shadow: none !important;
            font-size: 18px;
            line-height: 1;
            color: #999;
            cursor: pointer;
            flex-shrink: 0;
        }
        .pfm-wpfunnels-banner__close:hover {
            color: #333;
            background: none !important;
            box-shadow: none !important;
        }
        </style>
        <?php
    }

    /**
     * Output inline JS that handles impression tracking, CTA click, and dismiss.
     * Self-contained — does not depend on rex_wpfm_ajax being defined.
     */
    private function render_script(): void {
        ?>
        <script id="pfm-wpfunnels-banner-js">
        (function() {
            var banner = document.querySelector('.pfm-wpfunnels-banner');
            if (!banner) return;

            var ajaxUrl   = banner.dataset.ajaxUrl;
            var nonce     = banner.dataset.nonce;
            var feedCount = banner.dataset.feedCount;
            var pfmVer    = banner.dataset.pfmVersion;

            function post(action, extra) {
                var body = new FormData();
                body.append('action',      action);
                body.append('security',    nonce);
                body.append('feed_count',  feedCount);
                body.append('pfm_version', pfmVer);
                if (extra) {
                    Object.keys(extra).forEach(function(k) { body.append(k, extra[k]); });
                }
                fetch(ajaxUrl, { method: 'POST', body: body });
            }

            // Impression on page load.
            post('pfm_dashboard_banner_track', { event: 'impression' });

            // CTA click.
            banner.querySelector('.pfm-wpfunnels-banner__cta').addEventListener('click', function() {
                post('pfm_dashboard_banner_track', { event: 'click' });
            });

            // × close — 14-day dismiss.
            banner.querySelector('.pfm-wpfunnels-banner__close').addEventListener('click', function() {
                post('pfm_dashboard_banner_track', { event: 'dismiss' });
                post('pfm_dashboard_banner_dismiss', { type: 'temp' });
                banner.remove();
            });
        })();
        </script>
        <?php
    }

    // -------------------------------------------------------------------------
    // Conditions
    // -------------------------------------------------------------------------

    /**
     * All conditions that must be true before the banner is rendered.
     */
    private function should_show(): bool {
        // Screen is guaranteed by the views_edit-product-feed filter hook.
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        // Permanent dismiss.
        if ( get_user_meta( get_current_user_id(), 'pfm_wpfunnels_banner_dismissed', true ) ) {
            return false;
        }

        // 14-day dismiss.
        if ( get_transient( 'pfm_wpfunnels_banner_temp_' . get_current_user_id() ) ) {
            return false;
        }

        // Must have at least one published feed older than 14 days.
        $feeds = get_posts( array(
            'post_type'   => 'product-feed',
            'post_status' => 'publish',
            'numberposts' => 1,
            'fields'      => 'ids',
            'date_query'  => array(
                array(
                    'column' => 'post_date_gmt',
                    'before' => gmdate( 'Y-m-d H:i:s', strtotime( '-14 days' ) ),
                ),
            ),
        ) );

        return ! empty( $feeds );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Number of published product-feed posts.
     */
    private function get_feed_count(): int {
        $counts = wp_count_posts( 'product-feed' );
        return isset( $counts->publish ) ? (int) $counts->publish : 0;
    }

}
