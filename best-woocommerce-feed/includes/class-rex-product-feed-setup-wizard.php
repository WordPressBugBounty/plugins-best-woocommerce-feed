<?php
/**
 * Setup wizard for the plugin
 *
 * @package ''
 * @since 7.4.14
 */

class Rex_Product_Feed_Setup_Wizard
{

    /**
     * Initialize setup wizards
     *
     * @since 7.4.14
     */
    public function setup_wizard()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        $this->output_html();
    }


    public function enqueue_scripts($hook) {
        if (!isset($_GET['page']) || $_GET['page'] !== 'wpfm-setup-wizard') {
            return;
        }

        wp_enqueue_style(
            'pfm-setup-wizard',
            WPFM_PLUGIN_ASSETS_FOLDER . 'css/setup-wizard.css',
            [],
            WPFM_VERSION
        );
        wp_enqueue_script(
            'pfm-onboarding-js',
            WPFM_PLUGIN_ASSETS_FOLDER . 'js/library/onboarding.js',
            ['jquery'],
            WPFM_VERSION,
            true
        );
        wp_enqueue_script(
            'pfm-setup-wizard',
            WPFM_PLUGIN_ASSETS_FOLDER . 'js/setup-wizard.js',
            ['jquery'],
            WPFM_VERSION,
            true
        );
        
        // Load all merchants data into JavaScript
        $merchants_data = $this->get_merchants_for_js();
        wp_localize_script(
            'pfm-setup-wizard',
            'pfmMerchantsData',
            array_merge(
                $merchants_data,
                array(
                    'assetsUrl' => WPFM_PLUGIN_ASSETS_FOLDER . 'icon/setup-wizard-images/'
                )
            )
        );
    }

    /**
     * Output the rendered contents
     *
     * @since 7.4.14
     */
    private function output_html()
    {
        require_once plugin_dir_path(__FILE__) . '../admin/partials/rex-product-feed-setup-wizard-views.php';
        exit();
    }

    /**
     * Prepare merchant data for JavaScript
     *
     * @since 7.4.14
     * @return array
     */
    private function get_merchants_for_js() {
        // Load merchants class
        require_once plugin_dir_path(__FILE__) . '../admin/class-rex-product-feed-merchants.php';
        
        $all_merchants_data = Rex_Feed_Merchants::get_merchants();
        $merchants_list = array();
        
        // Popular merchants (already shown separately, so exclude them from search)
        $popular_ids = array('google', 'facebook', 'tiktok', 'instagram', 'yandex');
        
        // Get premium status
        $is_premium = apply_filters( 'wpfm_is_premium', false );
        
        // Process pro merchants
        if ( isset($all_merchants_data['pro_merchants']) && is_array($all_merchants_data['pro_merchants']) ) {
            foreach ( $all_merchants_data['pro_merchants'] as $id => $merchant ) {
                if ( !in_array($id, $popular_ids) ) {
                    $merchants_list[] = array(
                        'id' => $id,
                        'name' => $merchant['name'],
                        'isPro' => true,
                        'isAvailable' => $is_premium
                    );
                }
            }
        }
        
        // Process free merchants
        if ( isset($all_merchants_data['free_merchants']) && is_array($all_merchants_data['free_merchants']) ) {
            foreach ( $all_merchants_data['free_merchants'] as $id => $merchant ) {
                if ( !in_array($id, $popular_ids) ) {
                    $merchants_list[] = array(
                        'id' => $id,
                        'name' => $merchant['name'],
                        'isPro' => false,
                        'isAvailable' => true
                    );
                }
            }
        }
        
        // Sort alphabetically by name
        usort($merchants_list, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return array(
            'merchants' => $merchants_list,
            'isPremium' => $is_premium
        );
    }
}
