<?php

$status = get_post_meta( get_the_ID(), '_rex_feed_custom_filter_option', true );
$style  = 'added' !== $status ? 'style="display: none;"' : '';

if ( wpfm_pro_compatibility() ) {
    do_action( 'wpfm_pro_filter_rules' );
}
?>
<div id="rex-feed-config-filter" class="rex-feed-config-filter" <?php echo $style; ?>>
    <div class="rex-feed-custom-filter">
        <div class="rex-feed-custom-filter__content">

            <div class="rex-feed-custom-filter__fine-replace rex-feed-custom-filter__fine-brp">
                <div class="rex-feed-custom-filter__delete">
                    <?php include plugin_dir_path(__FILE__) . '../assets/icon/icon-svg/section-delete.php';?>
                </div>
                <!-- .rex-feed-custom-filter__delete end -->

                <div class="accordion__list">
                    <div class="accordion">
                    <span class="accordion__title">
                        <span class="accordion__arrow"></span>
                        <label for="<?php echo 'rex_feed_custom-filter';?>">
                            <?php _e('Custom Filter', 'rex-product-feed' )?>
                            <span class="rex_feed-tooltip">
                                <?php include plugin_dir_path(__FILE__) . '../assets/icon/icon-svg/icon-question.php';?>
                                <p><?php esc_html_e( 'Filter your feed products with your preferred condition', 'rex-product-feed' ); ?></p>
                            </span>
                        </label>
                    </span>
                        <!-- .accordion__title end -->

                        <div class="accordion__content-wrap">
                            <div class="accordion__content">
                                <div class="accordion__table-container" role="table" aria-label="condition table">

                                    <div class="flex-table-header" role="rowgroup">
                                        <div class="flex-row" role="columnheader">
                                            <?php echo __('If', 'rex-product-feed') ?><span>*</span>
                                        </div>

                                        <div class="flex-row" role="columnheader">
                                            <?php echo __('Condition', 'rex-product-feed') ?><span>*</span>
                                        </div>

                                        <div class="flex-row" role="columnheader">
                                            <?php echo __('Value', 'rex-product-feed') ?>
                                        </div>

                                        <div class="flex-row" role="columnheader">
                                            <?php echo __('Then', 'rex-product-feed') ?><span>*</span>
                                        </div>

                                        <div class="flex-row" role="columnheader">
                                            <?php echo __('Action', 'rex-product-feed') ?>
                                        </div>

                                    </div>
                                    <!-- .flex-table-header end -->

                                    <div class="flex-table-body" role="rowgroup"></div>
                                    <!-- .flex-table-body end -->
                                </div>

                            </div>
                            <!-- .accordion__content end -->
                        </div>
                        <!-- .accordion__content-wrap end -->

                    </div>
                </div>
                <!-- .accordion__list end -->
            </div>
            <!-- .rex-feed-custom-filter__fine-replace end -->

        </div>
        <!-- .rex-feed-custom-filter__content end -->
    </div>
    <!-- .rex-feed-custom-filter end -->
</div>