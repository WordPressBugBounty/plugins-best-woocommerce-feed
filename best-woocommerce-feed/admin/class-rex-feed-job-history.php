<?php
/**
 * Retrieves and renders past ActionScheduler job history for wpfm groups.
 *
 * @package Rex_Product_Feed
 * @since   7.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Rex_Feed_Job_History {

    const PER_PAGE = 20;

    /**
     * Fetch paginated job records.
     *
     * @param int $page     1-based page number.
     * @param int $per_page Records per page.
     * @return array { actions: ActionScheduler_Action[], total: int }
     */
    public function get_jobs( $page = 1, $per_page = self::PER_PAGE ) {
        if ( ! function_exists( 'as_get_scheduled_actions' ) ) {
            return array( 'actions' => array(), 'total' => 0 );
        }

        $groups  = $this->get_wpfm_groups();
        $all     = array();

        foreach ( $groups as $group ) {
            foreach ( array( 'complete', 'failed', 'canceled' ) as $status ) {
                $actions = as_get_scheduled_actions( array(
                    'group'    => $group,
                    'status'   => $status,
                    'per_page' => -1,
                    'orderby'  => 'date',
                    'order'    => 'DESC',
                ) );
                foreach ( $actions as $id => $action ) {
                    $all[ $id ] = $action;
                }
            }
        }

        uasort( $all, function ( $a, $b ) {
            $ta = $a->get_schedule()->get_date();
            $tb = $b->get_schedule()->get_date();
            if ( ! $ta || ! $tb ) {
                return 0;
            }
            return $tb->getTimestamp() - $ta->getTimestamp();
        } );

        $total   = count( $all );
        $offset  = ( max( 1, $page ) - 1 ) * $per_page;
        $actions = array_slice( $all, $offset, $per_page, true );

        return array( 'actions' => $actions, 'total' => $total );
    }

    /**
     * Resolve a human-readable feed name from action args.
     *
     * @param ActionScheduler_Action $action
     * @return string
     */
    private function resolve_feed_name( $action ) {
        $args    = $action->get_args();
        $feed_id = isset( $args['feed_id'] ) ? absint( $args['feed_id'] ) : 0;

        if ( ! $feed_id ) {
            return '—';
        }

        $title = get_the_title( $feed_id );
        if ( $title ) {
            return $title;
        }

        /* translators: %d: feed post ID */
        return sprintf( esc_html__( 'Deleted feed (#%d)', 'rex-product-feed' ), $feed_id );
    }

    /**
     * Render the job history table into output buffer.
     *
     * @param int $page Current page.
     */
    public function render_table( $page = 1 ) {
        $data    = $this->get_jobs( $page );
        $actions = $data['actions'];
        $total   = $data['total'];
        $pages   = $total > 0 ? (int) ceil( $total / self::PER_PAGE ) : 1;

        if ( empty( $actions ) ) {
            echo '<p class="wpfm-job-history-empty">' . esc_html__( 'No past scheduled jobs found.', 'rex-product-feed' ) . '</p>';
            return;
        }

        echo '<table class="wpfm-job-history-table widefat striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__( 'Hook', 'rex-product-feed' ) . '</th>';
        echo '<th>' . esc_html__( 'Feed', 'rex-product-feed' ) . '</th>';
        echo '<th>' . esc_html__( 'Status', 'rex-product-feed' ) . '</th>';
        echo '<th>' . esc_html__( 'Scheduled', 'rex-product-feed' ) . '</th>';
        echo '</tr></thead><tbody>';

        foreach ( $actions as $action_id => $action ) {
            $hook   = esc_html( $action->get_hook() );
            $feed   = esc_html( $this->resolve_feed_name( $action ) );
            $status = $this->get_status( $action_id );
            $date   = $action->get_schedule()->get_date();
            $date   = $date ? esc_html( $date->format( 'Y-m-d H:i:s' ) ) : '—';

            $badge_class = 'wpfm-status-badge wpfm-status-' . esc_attr( $status );
            echo '<tr>';
            echo '<td><code>' . $hook . '</code></td>';
            echo '<td>' . $feed . '</td>';
            echo '<td><span class="' . $badge_class . '">' . esc_html( $status ) . '</span></td>';
            echo '<td>' . $date . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';

        if ( $pages > 1 ) {
            $settings_url = admin_url( 'edit.php?post_type=product-feed&page=wpfm_dashboard' );
            echo '<div class="wpfm-job-history-pagination">';
            if ( $page > 1 ) {
                $prev_url = add_query_arg( 'wpfm_job_page', $page - 1, $settings_url );
                echo '<a href="' . esc_url( $prev_url ) . '" class="button">&laquo; ' . esc_html__( 'Previous', 'rex-product-feed' ) . '</a> ';
            }
            echo '<span>' . sprintf(
                /* translators: 1: current page, 2: total pages */
                esc_html__( 'Page %1$d of %2$d', 'rex-product-feed' ),
                $page,
                $pages
            ) . '</span>';
            if ( $page < $pages ) {
                $next_url = add_query_arg( 'wpfm_job_page', $page + 1, $settings_url );
                echo ' <a href="' . esc_url( $next_url ) . '" class="button">' . esc_html__( 'Next', 'rex-product-feed' ) . ' &raquo;</a>';
            }
            echo '</div>';
        }
    }

    /**
     * Return status string for an action by ID.
     *
     * @param int $action_id
     * @return string
     */
    private function get_status( $action_id ) {
        if ( ! class_exists( 'ActionScheduler_Store' ) ) {
            return 'unknown';
        }
        try {
            return ActionScheduler_Store::instance()->get_status( $action_id );
        } catch ( Exception $e ) {
            return 'unknown';
        }
    }

    /**
     * Return all wpfm ActionScheduler group slugs.
     *
     * @return string[]
     */
    private function get_wpfm_groups() {
        global $wpdb;

        if ( ! $wpdb ) {
            return array( 'wpfm' );
        }

        $table = $wpdb->prefix . 'actionscheduler_groups';
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            return array( 'wpfm' );
        }

        $groups = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT slug FROM {$table} WHERE slug = %s OR slug LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
                'wpfm',
                'wpfm-feed-%'
            )
        );

        return ! empty( $groups ) ? $groups : array( 'wpfm' );
    }
}
