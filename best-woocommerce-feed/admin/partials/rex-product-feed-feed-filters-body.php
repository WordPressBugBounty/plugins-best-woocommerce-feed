<?php
$rand_key = rand(999, 3000);
$conditions = $feed_filter->get_filter_mappings();
$conditions = function_exists( 'wpfm_restructure_custom_filter_args' ) ? wpfm_restructure_custom_filter_args( $conditions ) : $conditions;
?>

<div class="flex-table-and-box" data-row-id="<?php echo esc_html($rand_key); ?>" style="display: none;">
    <div class="flex-table-row" data-row-id="<?php echo esc_html($rand_key); ?>" style="display: none;">

        <div class="flex-row" data-title="If : " role="cell">
            <?php $feed_filter->print_select_dropdown( $rand_key, $rand_key, 'if', 'ff', '', 'rex-custom-filter-if' ); ?>
        </div>

        <div class="flex-row" data-title="condition : " role="cell">
            <?php $feed_filter->print_select_dropdown( $rand_key, $rand_key, 'condition', 'ff', '' ); ?>
        </div>

        <div class="flex-row" data-title="value : " role="cell">
            <?php $feed_filter->print_input( $rand_key, $rand_key, 'value', 'ff', '' ); ?>
        </div>

        <div class="flex-row" data-title="then : " role="cell">
            <?php $feed_filter->print_select_dropdown( $rand_key, $rand_key, 'then', 'ff', '' ); ?>
        </div>

        <div class="flex-row condition-icon condition-repeater" role="cell">
            <span class="dropdown-add-row  add-condition" title="add field" >
                <a class="and-condition"><?php echo __('AND','rex-product-feed')?></a>
            </span>

            <span class="remove-field delete-row delete-condition" title="Remove field">
                <?php include plugin_dir_path(__FILE__) . '../assets/icon/icon-svg/remove.php';?>
            </span>
        </div>
    </div>

    <div class="flex-table-row" data-row-id="<?php echo esc_html($rand_key); ?>">

        <div class="flex-row" data-title="If : " role="cell">
            <?php $feed_filter->print_select_dropdown( $rand_key, $rand_key, 'if', 'ff', '', 'rex-custom-filter-if' ); ?>
        </div>

        <div class="flex-row" data-title="condition : " role="cell">
            <?php $feed_filter->print_select_dropdown( $rand_key, $rand_key, 'condition', 'ff', '' ); ?>
        </div>

        <div class="flex-row" data-title="value : " role="cell">
            <?php $feed_filter->print_input( $rand_key, $rand_key, 'value', 'ff', '' ); ?>
        </div>

        <div class="flex-row" data-title="then : " role="cell">
            <?php $feed_filter->print_select_dropdown( $rand_key, $rand_key, 'then', 'ff', '' ); ?>
        </div>

        <div class="flex-row condition-icon condition-repeater" role="cell">
            <span class="dropdown-add-row  add-condition" title="add field" >
                <a class="and-condition"><?php echo __('AND','rex-product-feed')?></a>
            </span>

            <span class="remove-field delete-row delete-condition" title="Remove field">
                <?php include plugin_dir_path(__FILE__) . '../assets/icon/icon-svg/remove.php';?>
            </span>
        </div>
    </div>
    <!-- .flex-table-row end -->
</div>

<?php foreach ( $conditions as $key1 => $items): ?>
<div class="flex-table-and-box" data-row-id="<?php echo esc_html($key1); ?>">
    <div class="flex-table-row" data-row-id="<?php echo esc_html($rand_key); ?>" style="display: none;">

        <div class="flex-row" data-title="If : " role="cell">
            <?php $feed_filter->print_select_dropdown( $rand_key, $rand_key, 'if', 'ff', '', 'rex-custom-filter-if' ); ?>
        </div>

        <div class="flex-row" data-title="condition : " role="cell">
            <?php $feed_filter->print_select_dropdown( $rand_key, $rand_key, 'condition', 'ff', '' ); ?>
        </div>

        <div class="flex-row" data-title="value : " role="cell">
            <?php $feed_filter->print_input( $rand_key, $rand_key, 'value', 'ff', '' ); ?>
        </div>

        <div class="flex-row" data-title="then : " role="cell">
            <?php $feed_filter->print_select_dropdown( $rand_key, $rand_key, 'then', 'ff', '' ); ?>
        </div>

        <div class="flex-row condition-icon condition-repeater" role="cell">
            <span class="dropdown-add-row  add-condition" title="add field" >
                <a class="and-condition"><?php echo __('AND','rex-product-feed')?></a>
            </span>

            <span class="remove-field delete-row delete-condition" title="Remove field">
                <?php include plugin_dir_path(__FILE__) . '../assets/icon/icon-svg/remove.php';?>
            </span>
        </div>
    </div>
    <?php foreach( $items as $key2 => $item ): ?>
    <div class="flex-table-row" data-row-id="<?php echo esc_html($key2); ?>">

        <div class="flex-row" data-title="If : " role="cell">
            <?php $feed_filter->print_select_dropdown( $key1, $key2, 'if', 'ff', $item['if'], 'filter-select2 rex-custom-filter-if' ); ?>
        </div>

        <div class="flex-row" data-title="condition : " role="cell">
            <?php $feed_filter->print_select_dropdown( $key1, $key2, 'condition', 'ff', $item['condition'], 'filter-select2' ); ?>
        </div>

        <div class="flex-row" data-title="value : " role="cell">
            <?php $type = Rex_Product_Filter::is_date_column( $item['if'] ) ? 'date' : 'text'; ?>
            <?php $feed_filter->print_input( $key1, $key2, 'value', 'ff', $item['value'], '', '', $type ); ?>
        </div>

        <div class="flex-row" data-title="then : " role="cell">
            <?php $feed_filter->print_select_dropdown( $key1, $key2, 'then', 'ff', $item['then'], 'filter-select2' ); ?>
        </div>

        <div class="flex-row condition-icon condition-repeater" role="cell">
            <span class="dropdown-add-row  add-condition" title="add field" >
                <a class="and-condition"><?php echo __('AND','rex-product-feed')?></a>
            </span>

            <?php if( 0 === $key1 && 0 === $key2 ) {
                echo '';
            } else {?>
            <span class="remove-field delete-row delete-condition" title="Remove field">
                <?php include plugin_dir_path(__FILE__) . '../assets/icon/icon-svg/remove.php';?>
            </span>
            <?php }?>
        </div>
    </div>
    <!-- .flex-table-row end -->
    <?php endforeach;?>
</div>
<?php endforeach; ?>
<!-- .flex-table-and-box end -->

<div class="flex-table-or-button-area">
    <span class="custom-table-row-add"><?php esc_html_e('OR', 'rex-product-feed')?></span>
</div>
<!-- .flex-table-or-button-area end  -->