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
        $this->output_html();
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
}
