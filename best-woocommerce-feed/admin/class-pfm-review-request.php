<?php
/**
 * PFM Milestone Review Request Class
 *
 * Shows a bottom-right review-request card after specific user milestones
 * (feed validated, 3rd feed created, 30 days of usage, Pro upgrade), with a
 * progressive dismiss cooldown and a permanent stop once the user leaves a
 * review (or self-reports having already done so from Settings).
 *
 * @since 7.12.0
 */
class PFM_Review_Request {

    /**
     * Global status option name: '' or 'completed'. Once 'completed', no
     * trigger of any kind ever shows a card again. Public so the plugin's
     * existing settings-page AJAX handler (Rex_Product_Feed_Ajax) can set it
     * directly from the "I've already reviewed PFM" toggle without needing
     * an instance of this class.
     *
     * @var string
     */
    const OPTION_STATUS = 'pfm_review_status';

    /**
     * @var string
     */
    private $status_option = self::OPTION_STATUS;

    /**
     * Per-site dismissal counters, used to look up the escalating cooldown.
     *
     * @var string
     */
    private $dismiss_count_option = 'pfm_review_dismiss_count';

    /**
     * Timestamp of the most recent dismissal.
     *
     * @var string
     */
    private $last_dismissed_option = 'pfm_review_last_dismissed';

    /**
     * Two independent pending-trigger slots, one per screen a trigger can
     * render on — Validation (feed-edit) never competes with the other four
     * (feed-listing) for the same slot, since they never share a screen.
     * Each holds: {trigger, context, fired_at, shown_at}.
     */
    const PENDING_OPTION_EDIT    = 'pfm_review_pending_edit';
    const PENDING_OPTION_LISTING = 'pfm_review_pending_listing';

    /**
     * Timestamp this site's Pro Upgrade trigger fired (0 if never). Anchors
     * the Pro Renewal / Continued Usage trigger.
     *
     * @var string
     */
    private $pro_upgrade_at_option = 'pfm_review_pro_upgrade_at';

    /**
     * Last-seen "is premium" state, used to detect the false→true transition
     * that defines the Pro Upgrade trigger.
     *
     * @var string
     */
    private $was_premium_option = 'pfm_review_was_premium';

    /**
     * Per-trigger one-time "already fired" dedup map: {trigger_key => timestamp}.
     *
     * @var string
     */
    private $fired_option = 'pfm_review_fired_triggers';

    /**
     * Guards the one-time legacy-option migration.
     *
     * @var string
     */
    private $migrated_option = 'pfm_review_migrated';

    /**
     * Escalating cooldown schedule, in days, keyed by dismissal count.
     * The last value repeats for further dismissals.
     *
     * @var int[]
     */
    private $cooldown_schedule_days = array( 14, 30, 90 );

    /**
     * Trigger keys.
     */
    const TRIGGER_VALIDATION  = 'feed_validated';
    const TRIGGER_THREE_FEEDS = 'three_feeds_created';
    const TRIGGER_THIRTY_DAYS = 'thirty_days_usage';
    const TRIGGER_PRO_UPGRADE = 'pro_upgrade';
    const TRIGGER_PRO_RENEWAL = 'pro_renewal';

    /**
     * Screen ids each trigger is scoped to. Validation is the feed-edit
     * screen; every other trigger is the feed-listing screen.
     */
    const SCREEN_EDIT    = 'product-feed';
    const SCREEN_LISTING = 'edit-product-feed';

    /**
     * Collision priority for the feed-listing screen's four triggers —
     * higher wins the pending slot (design.md Decision 6). Validation is
     * absent: it never collides, since it has its own screen and slot.
     *
     * @var int[]
     */
    private $priority = array(
        self::TRIGGER_PRO_UPGRADE  => 4,
        self::TRIGGER_PRO_RENEWAL  => 3,
        self::TRIGGER_THREE_FEEDS  => 2,
        self::TRIGGER_THIRTY_DAYS  => 1,
    );

    /**
     * Constructor.
     *
     * @since 7.12.0
     */
    public function __construct() {
        $this->maybe_migrate_from_legacy_option();

        add_action( 'rex_product_feed_validation_completed', array( $this, 'maybe_fire_validation_trigger' ), 10, 3 );
        add_action( 'publish_product-feed', array( $this, 'maybe_fire_three_feeds_trigger' ), 10, 1 );
        add_action( 'admin_init', array( $this, 'maybe_fire_thirty_days_trigger' ) );
        add_action( 'admin_init', array( $this, 'maybe_fire_pro_upgrade_trigger' ) );
        add_action( 'admin_init', array( $this, 'maybe_fire_pro_renewal_trigger' ) );

        add_action( 'admin_footer', array( $this, 'render_card' ) );
        add_action( 'wp_ajax_pfm_review_request_action', array( $this, 'handle_ajax' ) );
    }

    // -------------------------------------------------------------------
    // Trigger listeners
    // -------------------------------------------------------------------

    /**
     * Successful Feed Validation trigger — fires only when a validation run
     * completes with zero critical errors.
     *
     * @param int    $feed_id  Feed post ID.
     * @param string $merchant Merchant slug.
     * @param array  $summary  Validation summary (total_errors, total_warnings, ...).
     * @return void
     */
    public function maybe_fire_validation_trigger( $feed_id, $merchant, $summary ) {
        if ( empty( $summary['total_errors'] ) === false && (int) $summary['total_errors'] > 0 ) {
            return;
        }

        $products = (int) get_post_meta( $feed_id, '_rex_feed_total_products', true );

        $this->fire_trigger(
            self::TRIGGER_VALIDATION,
            array(
                'products' => $products,
                'errors'   => 0,
            )
        );
    }

    /**
     * 3+ Feeds Created trigger — fires once the site's 3rd feed is published.
     *
     * @param int $post_id Feed post ID.
     * @return void
     */
    public function maybe_fire_three_feeds_trigger( $post_id ) {
        $count = count( get_posts( array(
            'post_type'      => 'product-feed',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ) ) );

        if ( $count < 3 ) {
            return;
        }

        $this->fire_trigger( self::TRIGGER_THREE_FEEDS );
    }

    /**
     * 30 Days of Usage trigger.
     *
     * @return void
     */
    public function maybe_fire_thirty_days_trigger() {
        if ( $this->has_fired( self::TRIGGER_THIRTY_DAYS ) ) {
            return;
        }

        $installed_time = class_exists( 'Rex_Product_Feed_Activator' )
            ? Rex_Product_Feed_Activator::get_installed_time()
            : (int) get_option( 'rex_wpfm_installed_time' );

        if ( ! $installed_time || ( time() - (int) $installed_time ) < ( 30 * DAY_IN_SECONDS ) ) {
            return;
        }

        $this->fire_trigger( self::TRIGGER_THIRTY_DAYS );
    }

    /**
     * Pro Upgrade trigger — fires when Pro transitions from not-licensed
     * (inactive, or active without a valid license) to active-and-licensed.
     * Uses the existing `wpfm_is_premium_activate` filter (the same signal
     * the plugin already uses elsewhere to gate the Pro submenu / the
     * license-inactive upsell notice) rather than the raw `activated_plugin`
     * hook, since activating the Pro plugin alone does not mean it's licensed.
     *
     * @return void
     */
    public function maybe_fire_pro_upgrade_trigger() {
        $is_premium  = (bool) apply_filters( 'wpfm_is_premium_activate', false );
        $was_premium = (bool) get_option( $this->was_premium_option, false );
        update_option( $this->was_premium_option, $is_premium );

        if ( ! $is_premium || $was_premium ) {
            return;
        }

        update_option( $this->pro_upgrade_at_option, time() );

        // Unlike the other triggers, Pro Upgrade tracks a togglable state,
        // not a permanent one-time milestone — a genuine re-upgrade after an
        // uninstall or a lapsed-then-renewed license deserves its own
        // thank-you, so each new false→true transition gets a fresh chance
        // (also resets Pro Renewal, since it belongs to this new Pro cycle).
        $this->unmark_fired( self::TRIGGER_PRO_UPGRADE );
        $this->unmark_fired( self::TRIGGER_PRO_RENEWAL );
        $this->fire_trigger( self::TRIGGER_PRO_UPGRADE );
    }

    /**
     * Pro Renewal / Continued Usage trigger — fires 30 days after Pro
     * Upgrade fired, if Pro is still active and licensed.
     *
     * @return void
     */
    public function maybe_fire_pro_renewal_trigger() {
        if ( $this->has_fired( self::TRIGGER_PRO_RENEWAL ) ) {
            return;
        }

        $upgraded_at = (int) get_option( $this->pro_upgrade_at_option, 0 );
        if ( ! $upgraded_at || ( time() - $upgraded_at ) < ( 30 * DAY_IN_SECONDS ) ) {
            return;
        }

        if ( ! apply_filters( 'wpfm_is_premium_activate', false ) ) {
            return;
        }

        $this->fire_trigger( self::TRIGGER_PRO_RENEWAL );
    }

    /**
     * Common trigger-firing path: per-trigger one-time dedup (always
     * recorded as "fired" regardless of outcome below), then priority-ranked
     * collision handling within the trigger's own screen bucket (design.md
     * Decision 6) — Validation's bucket only ever holds Validation, so it
     * never actually collides.
     *
     * @param string $trigger_key One of the TRIGGER_* constants.
     * @param array  $context     Optional copy-override context (e.g. stats).
     * @return void
     */
    private function fire_trigger( $trigger_key, $context = array() ) {
        if ( $this->has_fired( $trigger_key ) ) {
            return;
        }

        $this->mark_fired( $trigger_key );
        $this->record_event( 'growth/review_request_fired', $trigger_key );

        if ( 'completed' === get_option( $this->status_option ) ) {
            return;
        }

        $pending_option = $this->pending_option_for_trigger( $trigger_key );
        $pending        = get_option( $pending_option );

        if ( ! empty( $pending ) && ! empty( $pending['trigger'] ) ) {
            $pending_unshown  = empty( $pending['shown_at'] );
            $outranks_pending = $this->get_priority( $trigger_key ) > $this->get_priority( $pending['trigger'] );

            if ( ! $pending_unshown || ! $outranks_pending ) {
                // Existing pending trigger keeps the slot; the new one is
                // already recorded as fired above, dropped here.
                return;
            }
        }

        update_option(
            $pending_option,
            array(
                'trigger'  => $trigger_key,
                'context'  => $context,
                'fired_at' => time(),
                'shown_at' => null,
            ),
            false
        );
    }

    // -------------------------------------------------------------------
    // Dedup helpers
    // -------------------------------------------------------------------

    private function has_fired( $trigger_key ) {
        $fired = get_option( $this->fired_option, array() );
        return ! empty( $fired[ $trigger_key ] );
    }

    private function mark_fired( $trigger_key ) {
        $fired                 = get_option( $this->fired_option, array() );
        $fired[ $trigger_key ] = time();
        update_option( $this->fired_option, $fired, false );
    }

    private function unmark_fired( $trigger_key ) {
        $fired = get_option( $this->fired_option, array() );
        unset( $fired[ $trigger_key ] );
        update_option( $this->fired_option, $fired, false );
    }

    private function get_priority( $trigger_key ) {
        return isset( $this->priority[ $trigger_key ] ) ? $this->priority[ $trigger_key ] : 0;
    }

    // -------------------------------------------------------------------
    // Screen / pending-slot routing
    // -------------------------------------------------------------------

    /**
     * Which screen a given trigger is scoped to.
     *
     * @param string $trigger_key One of the TRIGGER_* constants.
     * @return string SCREEN_EDIT or SCREEN_LISTING.
     */
    private function get_trigger_screen( $trigger_key ) {
        return self::TRIGGER_VALIDATION === $trigger_key ? self::SCREEN_EDIT : self::SCREEN_LISTING;
    }

    /**
     * Which pending-slot option belongs to a given screen.
     *
     * @param string $screen SCREEN_EDIT or SCREEN_LISTING.
     * @return string
     */
    private function pending_option_for_screen( $screen ) {
        return self::SCREEN_EDIT === $screen ? self::PENDING_OPTION_EDIT : self::PENDING_OPTION_LISTING;
    }

    private function pending_option_for_trigger( $trigger_key ) {
        return $this->pending_option_for_screen( $this->get_trigger_screen( $trigger_key ) );
    }

    /**
     * The pending-slot option relevant to the *current* admin screen, or
     * false if the current screen isn't one either bucket targets.
     *
     * @return string|false
     */
    private function current_pending_option() {
        if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
            return false;
        }
        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }
        if ( self::SCREEN_EDIT === $screen->id ) {
            return self::PENDING_OPTION_EDIT;
        }
        if ( self::SCREEN_LISTING === $screen->id ) {
            return self::PENDING_OPTION_LISTING;
        }
        return false;
    }

    // -------------------------------------------------------------------
    // Visibility
    // -------------------------------------------------------------------

    /**
     * @return string|false The pending-slot option to render from, or false.
     */
    private function should_show() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return false;
        }

        $pending_option = $this->current_pending_option();
        if ( ! $pending_option ) {
            return false;
        }

        if ( 'completed' === get_option( $this->status_option ) ) {
            return false;
        }

        $pending = get_option( $pending_option );
        if ( empty( $pending ) || empty( $pending['trigger'] ) ) {
            return false;
        }

        // Pro-specific cards go stale if Pro is later uninstalled or its
        // license lapses — don't show a "Welcome to Pro" / "Thanks for
        // continuing" card once Pro no longer applies. Silently clear the
        // slot rather than consuming a cooldown/dismissal the user never
        // actually saw.
        if ( in_array( $pending['trigger'], array( self::TRIGGER_PRO_UPGRADE, self::TRIGGER_PRO_RENEWAL ), true )
            && ! apply_filters( 'wpfm_is_premium_activate', false ) ) {
            delete_option( $pending_option );
            return false;
        }

        $last_dismissed = (int) get_option( $this->last_dismissed_option, 0 );
        if ( $last_dismissed ) {
            $dismiss_count   = (int) get_option( $this->dismiss_count_option, 0 );
            $cooldown_days   = $this->cooldown_schedule_days[ min( $dismiss_count, count( $this->cooldown_schedule_days ) ) - 1 ] ?? end( $this->cooldown_schedule_days );
            $cooldown_until  = $last_dismissed + ( $cooldown_days * DAY_IN_SECONDS );

            if ( time() < $cooldown_until ) {
                return false;
            }
        }

        return $pending_option;
    }

    // -------------------------------------------------------------------
    // Copy
    // -------------------------------------------------------------------

    private function get_copy( $trigger_key ) {
        $copy = array(
            self::TRIGGER_VALIDATION  => array(
                'headline' => __( 'Your feed is perfect! Get ready for your first sale!', 'rex-product-feed' ),
                'question' => __( 'Was RexFeed helpful? Would you share your experience on WordPress?', 'rex-product-feed' ),
            ),
            self::TRIGGER_THREE_FEEDS => array(
                'headline' => __( "You're on a roll! 3 feeds created.", 'rex-product-feed' ),
                'subtext'  => __( "You're getting more of your products ready for different channels.", 'rex-product-feed' ),
                'question' => __( 'Has RexFeed been helpful? Share your experience on WordPress.', 'rex-product-feed' ),
            ),
            self::TRIGGER_THIRTY_DAYS => array(
                'headline' => __( "You've been using RexFeed for 30 days!", 'rex-product-feed' ),
                'subtext'  => __( 'Thanks for trusting RexFeed to manage your product feeds.', 'rex-product-feed' ),
                'question' => __( 'Has RexFeed made feed management easier for you?', 'rex-product-feed' ),
            ),
            self::TRIGGER_PRO_UPGRADE => array(
                'headline' => __( 'Welcome to RexFeed Pro!', 'rex-product-feed' ),
                'subtext'  => __( 'Thanks for upgrading and getting more out of your product feeds.', 'rex-product-feed' ),
                'question' => __( 'Enjoying RexFeed? We\'d love to hear your experience on WordPress.', 'rex-product-feed' ),
            ),
            self::TRIGGER_PRO_RENEWAL => array(
                'headline' => __( 'Thanks for continuing with RexFeed Pro!', 'rex-product-feed' ),
                'subtext'  => __( 'We really appreciate having you with us.', 'rex-product-feed' ),
                'question' => __( 'Would you share your RexFeed experience on WordPress?', 'rex-product-feed' ),
            ),
        );

        return isset( $copy[ $trigger_key ] ) ? $copy[ $trigger_key ] : false;
    }

    // -------------------------------------------------------------------
    // Confetti (decorative only — respects prefers-reduced-motion via CSS)
    // -------------------------------------------------------------------

    /**
     * Outputs a full-viewport radial confetti burst from the center of the
     * screen (paper strips and squares, 360° spread) — a fast outward launch
     * against gravity, decelerating into a tumbling fall on all axes, fading
     * out near the end. Independent of the card's own position.
     *
     * @return void
     */
    private function render_confetti_burst_pieces() {
        $palette = array( '#ff8736', '#22c55e', '#ec4899', '#c28fef', '#f7b600', '#22d3ee' );
        $count   = 40;

        for ( $i = 0; $i < $count; $i++ ) {
            $angle       = ( 360 / $count ) * $i + wp_rand( -10, 10 );
            $radians     = deg2rad( $angle );
            $launch_dist = wp_rand( 70, 140 );
            $fall_dist   = wp_rand( 260, 460 );

            // Launch phase: outward at the angle, with an upward bias against
            // gravity regardless of angle (mimics initial velocity dominating).
            $launch_x = round( cos( $radians ) * $launch_dist );
            $launch_y = round( sin( $radians ) * $launch_dist * 0.6 ) - wp_rand( 65, 130 );

            // Fall phase: continues outward a bit further, then gravity wins.
            $fall_x = round( cos( $radians ) * ( $launch_dist + wp_rand( 20, 60 ) ) );
            $fall_y = $fall_dist;

            $is_square = 0 === $i % 3;
            $w         = $is_square ? wp_rand( 7, 9 ) : wp_rand( 4, 6 );
            $h         = $is_square ? $w : wp_rand( 11, 16 );

            $color = $palette[ $i % count( $palette ) ];

            printf(
                '<span class="pfm-confetti-burst-piece" style="--pfm-c:%1$s;--pfm-w:%2$dpx;--pfm-h:%3$dpx;--pfm-lx:%4$dpx;--pfm-ly:%5$dpx;--pfm-fx:%6$dpx;--pfm-fy:%7$dpx;--pfm-rx:%8$ddeg;--pfm-ry:%9$ddeg;--pfm-rz:%10$ddeg;--pfm-dur:%11$ss;--pfm-delay:%12$ss;"></span>',
                esc_attr( $color ),
                (int) $w,
                (int) $h,
                (int) $launch_x,
                (int) $launch_y,
                (int) $fall_x,
                (int) $fall_y,
                (int) wp_rand( 180, 720 ),
                (int) wp_rand( 180, 720 ),
                (int) wp_rand( -540, 540 ),
                esc_attr( round( wp_rand( 28, 38 ) / 10, 1 ) ),
                esc_attr( round( wp_rand( 0, 3 ) / 10, 1 ) )
            );
        }
    }

    // -------------------------------------------------------------------
    // QA preview bypass — ?pfm_preview_review=<trigger_key>
    // -------------------------------------------------------------------

    /**
     * Lets an admin force-preview any of the 5 cards on demand, bypassing
     * every should_show() gate (fired/pending/cooldown/status/screen) — for
     * manual QA of all trigger copy/layouts without waiting for real
     * conditions or mutating any real state. Mirrors the `?{slug}_test_review=1`
     * bypass already used by `vendor/linno/telemetry`'s ReviewPrompt.
     *
     * @return string|false One of the TRIGGER_* constants, or false.
     */
    private function maybe_get_preview_trigger() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return false;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( empty( $_GET['pfm_preview_review'] ) ) {
            return false;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $requested = sanitize_key( wp_unslash( $_GET['pfm_preview_review'] ) );
        $valid     = array(
            self::TRIGGER_VALIDATION,
            self::TRIGGER_THREE_FEEDS,
            self::TRIGGER_THIRTY_DAYS,
            self::TRIGGER_PRO_UPGRADE,
            self::TRIGGER_PRO_RENEWAL,
        );
        return in_array( $requested, $valid, true ) ? $requested : false;
    }

    /**
     * Sample context for preview mode, matching the demo copy's numbers.
     *
     * @param string $trigger_key One of the TRIGGER_* constants.
     * @return array
     */
    private function preview_context( $trigger_key ) {
        if ( self::TRIGGER_VALIDATION === $trigger_key ) {
            return array( 'products' => 1245, 'errors' => 0 );
        }
        return array();
    }

    // -------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------

    public function render_card() {
        $is_preview = false;
        $trigger_key = $this->maybe_get_preview_trigger();

        if ( $trigger_key ) {
            $is_preview = true;
            $context    = $this->preview_context( $trigger_key );
        } else {
            $pending_option = $this->should_show();
            if ( ! $pending_option ) {
                return;
            }

            $pending     = get_option( $pending_option );
            $trigger_key = $pending['trigger'];
            $context     = isset( $pending['context'] ) ? (array) $pending['context'] : array();

            if ( empty( $pending['shown_at'] ) ) {
                $pending['shown_at'] = time();
                update_option( $pending_option, $pending, false );
                $this->record_event( 'growth/review_request_shown', $trigger_key );
            }
        }

        $copy = $this->get_copy( $trigger_key );
        if ( ! $copy ) {
            return;
        }

        $review_url = 'https://wordpress.org/support/plugin/best-woocommerce-feed/reviews/#new-post';
        $nonce      = wp_create_nonce( 'pfm_review_request_nonce' );
        ?>
        <style id="pfm-review-request-styles">
            .pfm-review-request { display: none; position: fixed; bottom: 24px; right: 24px; width: 380px; max-width: calc(100vw - 32px); background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,.14), 0 1.5px 6px rgba(0,0,0,.08); font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; z-index: 99998; padding: 28px 24px 24px; box-sizing: border-box; text-align: center; }
            .pfm-review-request.is-visible { display: block; animation: pfm-review-slide-in .25s ease; }
            @keyframes pfm-review-slide-in { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
            .pfm-review-request__close { position: absolute; top: 14px; right: 14px; background: none; border: none; cursor: pointer; padding: 2px; color: #9ca3af; line-height: 1; font-size: 18px; }
            .pfm-review-request__close:hover { color: #374151; }
            .pfm-review-request__icon-wrap { position: relative; width: 64px; margin: 0 auto 16px; }
            .pfm-review-request__icon { width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; }
            .pfm-review-request__icon svg { width: 100%; height: 100%; }
            .pfm-review-request__headline { font-size: 18px; font-weight: 700; color: #1D2327; margin: 0 0 8px; line-height: 1.35; }
            .pfm-review-request__subtext { font-size: 14px; color: #6b7280; margin: 0 0 20px; line-height: 1.5; }
            .pfm-review-request__stats { display: flex; gap: 12px; margin-bottom: 20px; }
            .pfm-review-request__stat { flex: 1; background: #F0F4FF; border-radius: 10px; padding: 14px 8px; }
            .pfm-review-request__stat--errors { background: #EEFCF3; }
            .pfm-review-request__stat-value { display: block; font-size: 26px; font-weight: 700; color: #2563EB; line-height: 1.2; }
            .pfm-review-request__stat-value--errors { color: #16a34a; }
            .pfm-review-request__stat-label { display: block; font-size: 12px; color: #6b7280; margin-top: 4px; }
            .pfm-review-request__divider { width: 70%; border: none; border-top: 1px solid #e2e5e9; margin: 0 auto 20px; }
            .pfm-review-request__question { font-size: 14px; font-weight: 600; color: #1D2327; margin: 0 0 14px; line-height: 1.5; }
            .pfm-review-request__stars { color: #f59e0b; font-size: 26px; letter-spacing: 4px; margin-bottom: 20px; }
            .pfm-review-request__cta { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; background: #2563EB; color: #fff; border: none; border-radius: 10px; padding: 14px; font-size: 15px; font-weight: 700; cursor: pointer; text-decoration: none; box-sizing: border-box; }
            .pfm-review-request__cta:hover { background: #1D4ED8; color: #fff; }
            .pfm-review-request__later { display: block; margin-top: 14px; font-size: 13px; color: #6b7280; cursor: pointer; background: none; border: none; width: 100%; }
            .pfm-review-request__later:hover { color: #374151; text-decoration: underline; }
            @media screen and (max-width: 480px) { .pfm-review-request { right: 16px; bottom: 16px; left: 16px; width: auto; } }
            .pfm-confetti-burst { display: none; position: absolute; inset: 0; pointer-events: none; z-index: 1; perspective: 700px; }
            .pfm-confetti-burst.is-visible { display: block; }
            .pfm-confetti-burst-piece { position: absolute; top: 30%; left: 50%; width: var(--pfm-w); height: var(--pfm-h); margin: calc(var(--pfm-h) / -2) 0 0 calc(var(--pfm-w) / -2); background: var(--pfm-c); opacity: 0; transform-style: preserve-3d; }
            @media screen and (prefers-reduced-motion: no-preference) {
                .pfm-confetti-burst.is-visible .pfm-confetti-burst-piece { animation-name: pfm-confetti-radial; animation-duration: var(--pfm-dur, 3.6s); animation-delay: var(--pfm-delay, 0s); animation-fill-mode: forwards; }
            }
            @keyframes pfm-confetti-radial {
                0%   { opacity: 0; transform: translate3d(0,0,0) rotateX(0) rotateY(0) rotateZ(0deg); animation-timing-function: ease-out; }
                8%   { opacity: 1; }
                35%  { transform: translate3d(var(--pfm-lx), var(--pfm-ly), 0) rotateX(calc(var(--pfm-rx) * .3)) rotateY(calc(var(--pfm-ry) * .3)) rotateZ(calc(var(--pfm-rz) * .3)); animation-timing-function: ease-in-out; }
                78%  { opacity: 1; }
                100% { opacity: 0; transform: translate3d(var(--pfm-fx), var(--pfm-fy), 0) rotateX(var(--pfm-rx)) rotateY(var(--pfm-ry)) rotateZ(var(--pfm-rz)); }
            }
        </style>
        <div class="pfm-review-request" id="pfm-review-request" data-trigger="<?php echo esc_attr( $trigger_key ); ?>" data-preview="<?php echo $is_preview ? '1' : '0'; ?>">
            <div class="pfm-confetti-burst" id="pfm-confetti-burst" aria-hidden="true"><?php $this->render_confetti_burst_pieces(); ?></div>
            <button type="button" class="pfm-review-request__close" id="pfm-review-request-close" aria-label="<?php esc_attr_e( 'Close', 'rex-product-feed' ); ?>">&times;</button>
            <?php if ( $is_preview ) : ?>
                <span style="position:absolute;top:12px;left:16px;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#f59e0b;">PREVIEW</span>
            <?php endif; ?>
            <div class="pfm-review-request__icon-wrap">
                <div class="pfm-review-request__icon">
                    <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="22" cy="22" r="22" fill="#D4F6CF"/><circle cx="22" cy="22" r="15" fill="#17AE00"/><path d="M28 18L19.75 26L16 22.3636" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
            <p class="pfm-review-request__headline"><?php echo esc_html( $copy['headline'] ); ?></p>
            <?php if ( self::TRIGGER_VALIDATION === $trigger_key ) : ?>
                <div class="pfm-review-request__stats">
                    <div class="pfm-review-request__stat">
                        <span class="pfm-review-request__stat-value"><?php echo esc_html( number_format_i18n( (int) ( $context['products'] ?? 0 ) ) ); ?></span>
                        <span class="pfm-review-request__stat-label"><?php esc_html_e( 'products included', 'rex-product-feed' ); ?></span>
                    </div>
                    <div class="pfm-review-request__stat pfm-review-request__stat--errors">
                        <span class="pfm-review-request__stat-value pfm-review-request__stat-value--errors"><?php echo esc_html( (int) ( $context['errors'] ?? 0 ) ); ?></span>
                        <span class="pfm-review-request__stat-label"><?php esc_html_e( 'critical errors', 'rex-product-feed' ); ?></span>
                    </div>
                </div>
            <?php elseif ( ! empty( $copy['subtext'] ) ) : ?>
                <p class="pfm-review-request__subtext"><?php echo esc_html( $copy['subtext'] ); ?></p>
            <?php endif; ?>
            <hr class="pfm-review-request__divider">
            <p class="pfm-review-request__question"><?php echo esc_html( $copy['question'] ); ?></p>
            <div class="pfm-review-request__stars">★★★★★</div>
            <a href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener noreferrer" class="pfm-review-request__cta" id="pfm-review-request-cta">
                <?php esc_html_e( 'Leave a Review', 'rex-product-feed' ); ?> &rarr;
            </a>
            <button type="button" class="pfm-review-request__later" id="pfm-review-request-later">
                <?php esc_html_e( 'Maybe later', 'rex-product-feed' ); ?>
            </button>
        </div>
        <script type="text/javascript">
        (function ($) {
            'use strict';
            var nonce     = <?php echo wp_json_encode( $nonce ); ?>;
            var trigger   = <?php echo wp_json_encode( $trigger_key ); ?>;
            var isPreview = <?php echo $is_preview ? 'true' : 'false'; ?>;

            function send(type) {
                // Preview mode never touches real state — no trigger is
                // actually pending, so this would just silently no-op
                // server-side anyway, but skip the request entirely.
                if (isPreview) {
                    return;
                }
                $.post(ajaxurl, { action: 'pfm_review_request_action', type: type, trigger: trigger, nonce: nonce });
            }

            $(function () {
                $('#pfm-review-request-cta').on('click', function () {
                    send('cta');
                    $('#pfm-review-request').fadeOut(200, function () { $(this).remove(); });
                });
                $('#pfm-review-request-later, #pfm-review-request-close').on('click', function () {
                    send('dismiss');
                    $('#pfm-review-request').fadeOut(200, function () { $(this).remove(); });
                });
            });

            // Wait for the full page load (not just DOM-ready) plus a short
            // settle buffer before revealing the card and starting its
            // slide-in/confetti animations — avoids popping in mid-load.
            function revealCard() {
                setTimeout(function () {
                    $('#pfm-review-request, #pfm-confetti-burst').addClass('is-visible');
                }, 600);
            }
            if ('complete' === document.readyState) {
                // Assets already cached — the 'load' event may have already
                // fired before this script ran, so it would never come.
                revealCard();
            } else {
                $(window).on('load', revealCard);
            }
        }(jQuery));
        </script>
        <?php
    }

    // -------------------------------------------------------------------
    // AJAX
    // -------------------------------------------------------------------

    public function handle_ajax() {
        check_ajax_referer( 'pfm_review_request_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rex-product-feed' ) ) );
        }

        $type        = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
        $trigger_key = isset( $_POST['trigger'] ) ? sanitize_text_field( wp_unslash( $_POST['trigger'] ) ) : '';

        $pending_option = $this->pending_option_for_trigger( $trigger_key );
        $pending        = get_option( $pending_option );

        // Only act if the posted trigger actually matches what's pending in
        // that screen's slot — guards against a stale card posting after
        // its slot was already cleared or preempted.
        if ( empty( $pending['trigger'] ) || $pending['trigger'] !== $trigger_key ) {
            wp_send_json_success();
            return;
        }

        if ( 'cta' === $type ) {
            update_option( $this->status_option, 'completed' );
            $this->record_event( 'growth/review_request_outcome', $trigger_key, array( 'outcome' => 'clicked' ) );
            delete_option( $pending_option );
        } elseif ( 'dismiss' === $type ) {
            update_option( $this->dismiss_count_option, (int) get_option( $this->dismiss_count_option, 0 ) + 1 );
            update_option( $this->last_dismissed_option, time() );
            $this->record_event( 'growth/review_request_outcome', $trigger_key, array( 'outcome' => 'dismissed' ) );
            delete_option( $pending_option );
        }

        wp_send_json_success();
    }

    // -------------------------------------------------------------------
    // Migration from the legacy `rex_feed_review_request` option
    // -------------------------------------------------------------------

    private function maybe_migrate_from_legacy_option() {
        if ( get_option( $this->migrated_option ) ) {
            return;
        }
        update_option( $this->migrated_option, 1 );

        $legacy = get_option( 'rex_feed_review_request' );
        if ( ! empty( $legacy['frequency'] ) && 'never' === $legacy['frequency'] ) {
            update_option( $this->status_option, 'completed' );
        }
    }

    // -------------------------------------------------------------------
    // Telemetry
    // -------------------------------------------------------------------

    /**
     * Record a review-request event via the existing consent-gated telemetry
     * client, mirroring the pattern in Rex_Product_Feed_Linno_Telemetry.
     * Never blocks or errors the feature itself if telemetry is unavailable.
     *
     * @param string $event       Fully-qualified event name (e.g. 'growth/review_request_fired').
     * @param string $trigger_key Which trigger this event is about.
     * @param array  $extra       Additional event properties.
     * @return void
     */
    private function record_event( $event, $trigger_key, $extra = array() ) {
        global $telemetry_client;
        if ( ! is_object( $telemetry_client ) || ! method_exists( $telemetry_client, 'track' ) ) {
            return;
        }

        $telemetry_client->track(
            $event,
            array_merge(
                array(
                    'site_url' => get_site_url(),
                    'trigger'  => $trigger_key,
                ),
                $extra
            )
        );
    }
}
