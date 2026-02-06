<?php
$rand_key = rand(999, 3000);
$conditions = $feed_filter->get_filter_mappings();
$conditions = function_exists( 'wpfm_restructure_custom_filter_args' ) ? wpfm_restructure_custom_filter_args( $conditions ) : $conditions;

//compatibility with old structure
if(!has_cfo_key($conditions)) {
    $conditions = convert_old_to_new_structure($conditions);
}

?>

<!-- Template for cloning new groups/rows (hidden) -->
<div class="rex-or-markup-area" data-row-id="<?php echo esc_html($rand_key); ?>" style="display: none;">

    <div class="rex-custom-filter-dropdown" tabindex="0" >
        <div class="rex-custom-filter-dropdown__selected">
            <span class="rex-custom-filter-dropdown__text">AND</span>
            <span class="rex-custom-filter-dropdown__arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="6" viewBox="0 0 9 6" fill="none">
                    <path d="M1 1L4.5 4.5L8 1" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>

        <ul class="rex-custom-filter-dropdown__menu">
            <li class="rex-custom-filter-dropdown__option rex-custom-filter-dropdown__option--selected" data-value="AND" tabindex="0">
                AND
                <span class="rex-custom-filter-dropdown__checkmark">
                    <svg viewBox="0 0 20 20">
                        <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                    </svg>
                </span>
            </li>
            <li class="rex-custom-filter-dropdown__option" data-value="OR" tabindex="0">
                OR
                <span class="rex-custom-filter-dropdown__checkmark">
                    <svg viewBox="0 0 20 20">
                        <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                    </svg>
                </span>
            </li>
        </ul>
    </div>

    <div class="flex-table-and-box or-markup">

        <!-- Template for additional inner rows with AND/OR dropdown -->
        <div class="flex-table-group-and-or-box-content" data-row-id="<?php echo esc_html($rand_key); ?>" style="display: none;">

            <?php
            $options = ['AND', 'OR']; // dynamic data
            $selected_value = 'AND';  // default selected value
            ?>

            <div class="rex-custom-filter-dropdown" tabindex="0">
                <div class="rex-custom-filter-dropdown__selected">
                    <span class="rex-custom-filter-dropdown__text"><?php echo htmlspecialchars($selected_value); ?></span>
                    <span class="rex-custom-filter-dropdown__arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="6" viewBox="0 0 9 6" fill="none">
                            <path d="M1 1L4.5 4.5L8 1" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>

                <ul class="rex-custom-filter-dropdown__menu">
                    <?php foreach ($options as $option):
                        $is_selected = ($option === $selected_value) ? ' rex-custom-filter-dropdown__option--selected' : '';
                        ?>
                        <li class="rex-custom-filter-dropdown__option<?php echo $is_selected; ?>" data-value="<?php echo htmlspecialchars($option); ?>">
                            <?php echo htmlspecialchars($option); ?>
                            <span class="rex-custom-filter-dropdown__checkmark">
                                <svg viewBox="0 0 20 20">
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                </svg>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="flex-table-row">

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
                    <span class="remove-field delete-row delete-condition" title="Remove field">
                        <?php include plugin_dir_path(__FILE__) . '../assets/icon/icon-svg/remove.php';?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Template for first inner row (with hidden AND/OR dropdown) -->
        <div class="flex-table-group-and-or-box-content" data-row-id="<?php echo esc_html($rand_key); ?>">

            <?php
            $options = ['AND', 'OR']; // dynamic data
            $selected_value = 'AND';  // default selected value
            ?>

            <div class="rex-custom-filter-dropdown" tabindex="0" style="display: none;">
                <div class="rex-custom-filter-dropdown__selected">
                    <span class="rex-custom-filter-dropdown__text"><?php echo htmlspecialchars($selected_value); ?></span>
                    <span class="rex-custom-filter-dropdown__arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="6" viewBox="0 0 9 6" fill="none">
                            <path d="M1 1L4.5 4.5L8 1" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>

                <ul class="rex-custom-filter-dropdown__menu">
                    <?php foreach ($options as $option):
                        $is_selected = ($option === $selected_value) ? ' rex-custom-filter-dropdown__option--selected' : '';
                        ?>
                        <li class="rex-custom-filter-dropdown__option<?php echo $is_selected; ?>" data-value="<?php echo htmlspecialchars($option); ?>">
                            <?php echo htmlspecialchars($option); ?>
                            <span class="rex-custom-filter-dropdown__checkmark">
                                <svg viewBox="0 0 20 20">
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                </svg>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="flex-table-row">

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
                    <span class="remove-field delete-row delete-condition" title="Remove field">
                        <?php include plugin_dir_path(__FILE__) . '../assets/icon/icon-svg/remove.php';?>
                    </span>
                </div>
            </div>
        </div>

        <div class="flex-table-and-box-btn-area" role="group" aria-label="<?php esc_attr_e( 'Condition Controls', 'rex-product-feed' ); ?>">
            <div class="flex-table-and-box-content">
                <button
                        type="button"
                        class="group-add-and-condition"
                        aria-label="<?php esc_attr_e( 'Add an AND condition', 'rex-product-feed' ); ?>">
                    <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/icon-plus.php';?><?php esc_html_e( 'And', 'rex-product-feed' ); ?>
                </button>

                <button
                        type="button"
                        class="group-add-or-condition"
                        aria-label="<?php esc_attr_e( 'Add an OR condition', 'rex-product-feed' ); ?>">
                    <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/icon-plus.php';?><?php esc_html_e( 'Or', 'rex-product-feed' ); ?>
                </button>
            </div>
        </div>

    </div>

</div>

<!-- Rendered conditions from database -->
<?php foreach ( $conditions as $key1 => $items): ?>
    <?php
    // Get the group-level CFO value
    $group_cfo = isset($items['cfo']) && is_string($items['cfo']) ? strtoupper($items['cfo']) : 'OR';
    $group_cfo_options = ['AND', 'OR'];
    $is_first_group = ($key1 == 0 || $key1 === array_key_first($conditions));
    $group_display_style = $is_first_group ? ' style="display: none;"' : '';
    // Compute first numeric key for filters
    $numeric_filter_keys = array_filter($items, function($v, $k) { return is_numeric($k); }, ARRAY_FILTER_USE_BOTH);
    $first_numeric_filter_key = $numeric_filter_keys ? array_key_first($numeric_filter_keys) : null;
    ?>

    <div class="rex-or-markup-area" data-row-id="<?php echo esc_html($key1); ?>">

        <!-- Group-level CFO dropdown (always rendered, hidden for first group) -->
        <div class="rex-custom-filter-dropdown" tabindex="0" data-group-cfo="<?php echo esc_attr($key1); ?>"<?php echo $group_display_style; ?>>
            <input type="hidden" name="ff[<?php echo esc_attr($key1); ?>][cfo]" value="<?php echo esc_attr($group_cfo); ?>">
            <div class="rex-custom-filter-dropdown__selected">
                <span class="rex-custom-filter-dropdown__text"><?php echo htmlspecialchars($group_cfo); ?></span>
                <span class="rex-custom-filter-dropdown__arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="6" viewBox="0 0 9 6" fill="none">
                    <path d="M1 1L4.5 4.5L8 1" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                </span>
            </div>

            <ul class="rex-custom-filter-dropdown__menu">
                <?php foreach ($group_cfo_options as $option):
                    $is_selected = ($option === $group_cfo) ? ' rex-custom-filter-dropdown__option--selected' : '';
                    ?>
                    <li class="rex-custom-filter-dropdown__option<?php echo $is_selected; ?>" data-value="<?php echo htmlspecialchars($option); ?>" tabindex="0">
                        <?php echo htmlspecialchars($option);
                        if($option === $group_cfo){
                        ?>
                        <span class="rex-custom-filter-dropdown__checkmark">
                            <svg viewBox="0 0 20 20">
                                <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                            </span>
                    </li>
                <?php
                        }
                endforeach; ?>
            </ul>
        </div>

        <div class="flex-table-and-box or-markup">

            <div class="flex-table-and-box-area flex-table-and-or-box-area">

                <?php foreach( $items as $key2 => $item ): ?>
                    <?php
                    // Skip the cfo key as it's not a filter
                    if ($key2 === 'cfo' || !is_numeric($key2)) {
                        continue;
                    }

                    // Get the filter-level CFO value
                    $filter_cfo = isset($item['cfo']) && is_string($item['cfo']) ? strtoupper($item['cfo']) : 'AND';
                    $filter_cfo_options = ['AND', 'OR'];
                    $is_first_filter_in_group = ($key2 == $first_numeric_filter_key);
                    $filter_display_style = $is_first_filter_in_group ? ' style="display: none;"' : '';
                    ?>

                    <div class="flex-table-group-and-or-box-content" data-row-id="<?php echo esc_html($key2); ?>">

                        <!-- Filter-level CFO dropdown (always rendered, hidden for first in group) -->
                        <div class="rex-custom-filter-dropdown" tabindex="0" data-filter-cfo="<?php echo esc_attr($key1); ?>-<?php echo esc_attr($key2); ?>"<?php echo $filter_display_style; ?>>
                            <input type="hidden" name="ff[<?php echo esc_attr($key1); ?>][<?php echo esc_attr($key2); ?>][cfo]" value="<?php echo esc_attr($filter_cfo); ?>">
                            <div class="rex-custom-filter-dropdown__selected">
                                <span class="rex-custom-filter-dropdown__text"><?php echo htmlspecialchars($filter_cfo); ?></span>
                                <span class="rex-custom-filter-dropdown__arrow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="6" viewBox="0 0 9 6" fill="none">
                                    <path d="M1 1L4.5 4.5L8 1" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                </span>
                            </div>

                            <ul class="rex-custom-filter-dropdown__menu">
                                <?php foreach ($filter_cfo_options as $option):
                                    $is_selected = ($option === $filter_cfo) ? ' rex-custom-filter-dropdown__option--selected' : '';
                                    ?>
                                    <li class="rex-custom-filter-dropdown__option<?php echo $is_selected; ?>" data-value="<?php echo htmlspecialchars($option); ?>" tabindex="0">
                                        <?php echo htmlspecialchars($option);
                                            if($option === $filter_cfo){
                                        ?>
                                        <span class="rex-custom-filter-dropdown__checkmark">
                                            <svg viewBox="0 0 20 20">
                                                <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                            </svg>
                                            </span>
                                    </li>
                                <?php
                                            }
                                endforeach; ?>
                            </ul>
                        </div>

                        <div class="flex-table-row good" >

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
                                    <span class="remove-field delete-row delete-condition" title="Remove field">
                                        <?php include plugin_dir_path(__FILE__) . '../assets/icon/icon-svg/remove.php'; ?>
                                    </span>
                            </div>


                        </div>

                    </div>

                <?php endforeach;?>

                <div class="flex-table-and-box-btn-area" role="group" aria-label="<?php esc_attr_e( 'Condition Controls', 'rex-product-feed' ); ?>">
                    <div class="flex-table-and-box-content">
                        <button
                                type="button"
                                class="add-and-condition"
                                aria-label="<?php esc_attr_e( 'Add an AND condition', 'rex-product-feed' ); ?>">
                            <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/icon-plus.php';?><?php esc_html_e( 'And', 'rex-product-feed' ); ?>
                        </button>

                        <button
                                type="button"
                                class="add-or-condition"
                                aria-label="<?php esc_attr_e( 'Add an OR condition', 'rex-product-feed' ); ?>">
                            <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/icon-plus.php';?><?php esc_html_e( 'Or', 'rex-product-feed' ); ?>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>
<?php endforeach; ?>

<div class="flex-table-or-button-area" role="group" aria-label="<?php esc_attr_e( 'Filter logic options', 'rex-product-feed' ); ?>">
    <button type="button" class="custom-table-row-and">
        <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/icon-plus.php';?><?php esc_html_e( 'AND', 'rex-product-feed' ); ?>
    </button>

    <button type="button" class="custom-table-row-add">
        <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/icon-plus.php';?><?php esc_html_e( 'OR', 'rex-product-feed' ); ?>
    </button>
</div>
<!-- .flex-table-or-button-area end  -->