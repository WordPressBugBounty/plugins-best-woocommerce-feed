<?php
/**
 * Handles saving changes for the Product Filter modal.
 *
 * @link       https://rextheme.com
 * @since    7.4.32
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */
?>


<div id="rex-feed-product-filter-save-changes" role="dialog" aria-labelledby="rex-feed-save-title">
    <button type="button" class="rex-content-filter__close-icon" id="rex_feed_filter_save_btn"
        aria-label="<?php esc_attr_e('Save Changes', 'rex-product-feed'); ?>">
        <span><?php echo __('Save Changes', 'rex-product-feed'); ?></span>
        <i class="fa fa-spinner fa-pulse fa-fw" style="display: none"></i>
    </button>
</div>
