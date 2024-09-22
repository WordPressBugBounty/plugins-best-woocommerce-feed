<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is display the custom feed configuration part of the metabox on feed edit screen.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

// Exit if $feed_template obj isn't available.
if ( ! isset($feed_template) ) {
	return;
}
$wpfm_hide_char = get_option( 'rex_feed_hide_character_limit_field', 'on' );
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<thead>
    <tr>
        <th class="" id="rex_feed_attr_head" style="width:20%" title="<?php esc_html_e('Required Attributes', 'rex-product-feed') ?>"><?php esc_html_e('Required Attributes', 'rex-product-feed') ?><span>*</span></th>
        <th class="" id="rex_feed_type_head" style="width:12%" title="<?php esc_html_e('Attribute Type', 'rex-product-feed') ?>"><?php esc_html_e('Attribute Type', 'rex-product-feed') ?><span>*</span></th>
        <th class="" id="rex_feed_val_head" style="width:13%" title="<?php esc_html_e('Assigned Values', 'rex-product-feed') ?>"><?php esc_html_e('Assigned Values', 'rex-product-feed') ?><span>*</span></th>
        <th class="" id="rex_feed_prefix_head" style="width:15%"> &nbsp;</th>
        <th class="" id="rex_feed_sanitization_head" title="<?php esc_html_e('Output Filter', 'rex-product-feed') ?>"><?php esc_html_e('Output Filter', 'rex-product-feed') ?></th>
        <th class="" id="rex_feed_output_limit_head" title="<?php esc_html_e('char Limit', 'rex-product-feed') ?>"><?php esc_html_e('char Limit', 'rex-product-feed') ?></th>
        <th class="" id="rex_feed_output_action_head" style="width:12%">
            <div class="table-action">
                <?php echo esc_html__('Click Here To Edit Attributes', 'rex-product-feed') ?>  
                <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/click-here.php';?>
            </div>
            <?php echo esc_html__('Action', 'rex-product-feed') ?>
        </th>
    </tr>
</thead>

<tbody>

<?php
$keyy = rand(999, 3000); ?>
<tr data-row-id="<?php echo esc_attr($keyy); ?>" style="display: none;" class="edit-mode">
    <td data-title="Attributes : " style="width:15%">
        <div class="attributes-wrapper">
            <p class="attr-full-name" style="display: none"></p>
            <?php $feed_template->print_select_dropdown( $keyy, 'attr', '', 'attr-dropdown' );?>
        </div>
    </td>
    <td data-title="Type : " style="width:12%"><?php $feed_template->print_attr_type( $keyy, '' ); ?></td>
    <td data-title="Value : " style="width:13%">
        <div class="meta-dropdown">
			<?php
			echo '<select class="attr-val-dropdown" name="fc['.esc_attr($keyy).'][meta_key]" >';
			echo "<option value=''>".esc_html__('Please Select', 'rex-product-feed')."</option>";
			echo $feed_template->print_product_attributes(); // phpcs:ignore
			echo "</select>";
			?>
        </div>
        <div class="static-input">
			<?php $feed_template->print_input( $keyy, 'st_value', '', 'attribute-val-static-field' ); ?>
        </div>
        <?php do_action( 'rex_feed_after_static_input', $feed_template, $keyy, '' );?>
    </td>
    <td data-title="Prefix Suffix : " style="width:15%">
        <div class="rex-prefix-dropdown-area" style="visibility: visible">
            <div class="rex-dropdown">
                <button
                        data-toggle="dropdown2"
                        class="button dropdown-toggle"
                        type="button"
                >
                    <?php echo esc_attr__( 'Prefix/Suffix', 'rex-product-feed' ) ?>
                </button>

                <div class="dropdown-menu">
                    <p><?php echo esc_attr__( 'Prefix', 'rex-product-feed' ) ?></p>
                    <?php $feed_template->print_input( $keyy, 'prefix', '', 'rex-prefix-field', '' ); ?>
                    <p><?php echo esc_attr__( 'Suffix', 'rex-product-feed' ) ?></p>
                    <?php $feed_template->print_input( $keyy, 'suffix', '', 'rex-suffix-field', '' ); ?>
                </div>
            </div>
        </div>
    </td>
    
    <td data-title="Output Sanitization : ">
        <div class="output-count-area">
            <span class="rex-product-picker-count" title=""></span>
            <?php $feed_template->print_select_dropdown( $keyy, 'escape', 'default', 'default-sanitize-dropdown', 'multiple', '[]' ); ?>
        </div>
    </td>
    <td data-title="Output Limit : "><?php $feed_template->print_input( $keyy, 'limit', 0, 'output-limit-field' ); ?></td>
    <td data-title="Table Row Edit : " style="width:10%">
        <div class=" wpfm-config-table-row-edit-icon-area">
            <a class="wpfm-icon-btn wpfm-name-edit wpfm-config-table-row-edit wpfm-config-table-custom-row-edit" title="Edit">
                <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/feed-row-edit-icon.php';?>
            </a>
            <a type="submit" class="wpfm-name-cancel wpfm-config-table-row-edit-cancel wpfm-config-table-custom-row-edit-cancel">
                <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/feed-row-reset-icon.php';?>
            </a>
            <a type="submit" class="wpfm-name-submit wpfm-config-table-row-edit-submit wpfm-config-table-custom-row-edit-submit">
                <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/confirm-icon.php';?>
            </a>
            <a class="delete-row  wpfm-icon-btn wpfm-config-table-row-delete wpfm-config-table-custom-row-delete" title="Delete">
                <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/icon-delete.php';?>
            </a>
        </div>
    </td>
</tr>

<?php foreach ( $feed_template->get_template_mappings() as $key => $item): ?>
	<?php
    $display_none = 'style="display: none"';
    $hide_meta    = !empty( $item[ 'type' ] ) && 'meta' === $item[ 'type' ] ? '' : $display_none;
    $hide_static  = !empty( $item[ 'type' ] ) && 'static' === $item[ 'type' ] ? '' : $display_none;

    if( isset( $item[ 'type' ] ) ) {
        /**
         * Applies filters to customize the available meta attribute types.
         *
         * This function triggers the filter hook "rexfeed_meta_attribute_types", allowing developers to modify
         * the array of available meta attribute types.
         *
         * @param array $meta_types An array containing the default meta attribute types.
         *
         * @return array Modified array of meta attribute types.
         * @since 7.3.11
         */
        $meta_types = apply_filters( 'rexfeed_meta_attribute_types', [ 'meta' ] );
        if( in_array( $item[ 'type' ], $meta_types, true ) ) {
            $hide_meta = '';
        }
        elseif( 'static' === $item[ 'type' ] ) {
            $hide_static = '';
        }
        elseif ( function_exists( 'rex_feed_is_wpfm_pro_active' ) && !rex_feed_is_wpfm_pro_active() ) {
            $hide_meta = '';
            $item[ 'type' ] = 'meta';
        }
    }
	?>
    <tr data-row-id="<?php echo esc_html($key); ?>">
        <td data-title="Attributes : " style="width:15%">
            <div class="attributes-wrapper">
                <p class="attr-full-name" style="display: none"></p>
            <?php
                if(array_key_exists('attr', $item)) {
                    $feed_template->print_select_dropdown( $key, 'attr', !empty( $item['attr'] ) ? $item['attr'] : '', 'attr-dropdown disable-custom-id-dropdown' );
                } else {
                    $feed_template->print_input( $key, 'cust_attr', !empty( $item['cust_attr'] ) ? $item['cust_attr'] : '', 'rex-custom-attribute disable-custom-id-dropdown' );
                }
            ?>
            </div>
        </td>

        <td data-title="Type : ">
            <?php $feed_template->print_attr_type( $key, !empty( $item['type'] ) ? $item['type'] : '', 'type-dropdowns' ); ?>
        </td>

        <td data-title="Value : " style="width:13%">
            <div class="meta-dropdown" <?php echo filter_var( $hide_meta ); ?>>
				<?php
				echo '<select class="attr-val-dropdown select2-attr-dropdown disable-custom-dropdown" name="fc['.esc_attr($key).'][' . esc_attr( 'meta_key' ) . ']" readonly="true">';
				echo "<option value=''>".esc_html__('Please Select', 'rex-product-feed')."</option>";
				echo $feed_template->print_product_attributes( !empty( $item['meta_key'] ) ? $item['meta_key'] : '' ); // phpcs:ignore
				echo "</select>";
				?>
            </div>
            <div class="static-input" <?php echo filter_var( $hide_static ); ?>>
				<?php $feed_template->print_input( $key, 'st_value', $item['st_value'] ?? '', 'attribute-val-static-field disable-custom-id-dropdown' ); ?>
            </div>
            <?php do_action( 'rex_feed_after_static_input', $feed_template, $key, $item );?>
        </td>

        <td data-title="Prefix Suffix : " style="width:15%">
            <div class="rex-prefix-dropdown-area" style="visibility: hidden">
                <div class="rex-dropdown">
                    <button
                        data-toggle="dropdown2"
                        class="button dropdown-toggle"
                        type="button"
                    >
                        <?php echo esc_attr__( 'Prefix/Suffix', 'rex-product-feed' ) ?>
                    </button>
            
                    <div class="dropdown-menu">
                        <p><?php echo esc_attr__( 'Prefix', 'rex-product-feed' ) ?></p>
                        <?php $feed_template->print_input( $key, 'prefix', !empty( $item['prefix'] ) ? $item['prefix'] : '', 'rex-prefix-field' ); ?>
                        <p><?php echo esc_attr__( 'Suffix', 'rex-product-feed' ) ?></p>
                        <?php $feed_template->print_input( $key, 'suffix', !empty( $item['suffix'] ) ? $item['suffix'] : '', 'rex-suffix-field' ); ?>
                    </div>
                </div>
            </div>

        </td>

        <td data-title="Output Sanitization : ">
            <div class="output-count-area">
                <span class="rex-product-picker-count" title=""></span>
                <?php $feed_template->print_select_dropdown( $key, 'escape', !empty( $item['escape'] ) ? $item['escape'] : '', 'sanitize-dropdown', 'multiple', '[]' ); ?>
            </div>
        </td>

        <td data-title="Output Limit : ">
            <?php $feed_template->print_input( $key, 'limit', !empty( $item['limit'] ) ? $item['limit'] : '', 'output-limit-field disable-custom-id-dropdown' ); ?>
        </td>

        <td data-title="Table Row Edit : " style="width:12%">
            <div class=" wpfm-config-table-row-edit-icon-area">
                <a class="wpfm-icon-btn wpfm-name-edit wpfm-config-table-row-edit" title="Edit">
                    <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/feed-row-edit-icon.php';?>
                </a>
                <a type="submit" class="wpfm-name-cancel wpfm-config-table-row-edit-cancel" title="Reset">
                    <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/feed-row-reset-icon.php';?>
                </a>
                <a type="submit" class="wpfm-name-submit wpfm-config-table-row-edit-submit" title="Confirm">
                    <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/confirm-icon.php';?>
                </a>
                <a class="delete-row  wpfm-icon-btn wpfm-config-table-row-delete" title="Delete">
                    <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/icon-delete.php';?>
                </a>
            </div>
        </td>
    </tr>
<?php endforeach ?>
</tbody>