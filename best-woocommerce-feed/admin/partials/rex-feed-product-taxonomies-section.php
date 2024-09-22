<?php
$prefix = 'rex_feed_';
$icon = '../assets/icon/icon-svg/icon-question.php';
$product_categories_url = 'https://rextheme.com/docs/wpfm-category-filter-generating-product-feed/?utm_source=plugin&utm_medium=category_filter_link&utm_campaign=pfm_plugin';
$product_tag_url = 'https://rextheme.com/docs/wpfm-tag-filter-generating-product-feed/?utm_source=plugin&utm_medium=tag_filter_link&utm_campaign=pfm_plugin';
?>

<div id="rex-feed-product-taxonomies-contents">
	<div id="rex-feed-product-cats" style="display: none">
        <div class="rex-feed-product-cats">
            <label for="<?php echo esc_attr($prefix) . 'cats';?>">
                <?php esc_html_e('Product Categories', 'rex-product-feed')?>
                <span class="rex_feed-tooltip">
                    <?php include plugin_dir_path(__FILE__) . $icon;?>
                    <p>
                        <?php esc_html_e('Filter feed by WooCommerce product categories', 'rex-product-feed')?>
                    </p>
                </span>
                <?php
                $saved_val = get_post_meta( $feed_id, '_rex_feed_cats_check_all_btn', true );
                $checked = $saved_val ? ' checked' : '';
                ?>
                <input id="rex_feed_cats_check_all_btn" name="rex_feed_cats_check_all_btn" type="checkbox" <?php echo $checked?>>
                <label for="rex_feed_cats_check_all_btn"><?php esc_html_e('Check All', 'rex-product-feed')?></label>
            </label>

            <a href="<?php echo esc_url($product_categories_url)?>" target="_blank">
                <?php esc_html_e('Learn How', 'rex-product-feed')?>
            </a>

        </div>

		<ul id="<?php echo esc_attr($prefix) . 'cats';?>">
			<?php
			$terms      = get_terms( array( 'taxonomy' => 'product_cat' ) );
			$terms      = is_array( $terms ) ? $terms : array();
			$post_terms = wp_get_post_terms( $feed_id, 'product_cat', array( 'fields' => 'slugs' ) );
			$post_terms = is_array( $post_terms ) ? $post_terms : array();
			$index      = 1;

            if ( empty( $terms ) ) {
                echo '<li>';
                echo '<label for="'. esc_attr($prefix) . 'tags' . esc_attr($index++) . '">'.esc_html__('No Categories', 'rex-product-feed').'</label>';
                echo '</li>';
            }
            else {
                foreach( $terms as $term ) {
                    $checked = in_array( $term->slug, $post_terms) ? ' checked' : '';
                    echo '<li>';
                    echo '<input type="checkbox" class="' . esc_attr($prefix) . 'cats' . '" id="'. esc_attr($prefix) . 'cats' . esc_attr($index) . '" name="'. esc_attr($prefix) . 'cats[]' . '" value="'. esc_attr($term->slug) .'" ' .esc_attr($checked). '>';
                    echo '<label for="'. esc_attr($prefix) . 'cats' . esc_attr($index++) . '">'.esc_html__($term->name, 'rex-product-feed').'</label>';
                    echo '</li>';
                }
            }
			?>
		</ul>
	</div>

	<div id="rex-feed-product-tags" style="display: none">
        <div class="rex-feed-product-tags">
            <label for="<?php echo esc_attr($prefix) . 'tags';?>">
                <?php esc_html_e('Product Tags', 'rex-product-feed')?>
                <span class="rex_feed-tooltip">
                    <?php include plugin_dir_path(__FILE__) . $icon;?>
                    <p>
                        <?php esc_html_e('Filter feed by WooCommerce tags ', 'rex-product-feed')?>
                    </p>
                </span>
                <?php
                $saved_val = get_post_meta( $feed_id, '_rex_feed_tags_check_all_btn', true );
                $checked = $saved_val ? ' checked' : '';
                ?>
                <input id="rex_feed_tags_check_all_btn" name="rex_feed_tags_check_all_btn" type="checkbox" <?php echo $checked?>>
                <label for="rex_feed_tags_check_all_btn"><?php esc_html_e('Check All', 'rex-product-feed')?></label>
            </label>

            <a href="<?php echo esc_url($product_tag_url)?>" target="_blank">
                <?php esc_html_e('Learn How', 'rex-product-feed')?>
            </a>

        </div>

		<ul id="<?php echo esc_attr($prefix) . 'tags';?>">
			<?php
			$terms      = get_terms( array( 'taxonomy' => 'product_tag' ) );
			$terms      = is_array( $terms ) ? $terms : array();
            $post_terms = wp_get_post_terms( $feed_id, 'product_tag', array( 'fields' => 'slugs' ) );
			$post_terms = is_array( $post_terms ) ? $post_terms : array();
			$index      = 1;

            if ( empty( $terms ) ) {
                echo '<li>';
                echo '<label for="'. esc_attr($prefix) . 'tags' . esc_attr($index++) . '">'.esc_html__('No Terms', 'rex-product-feed').'</label>';
                echo '</li>';
            }
            else {
                foreach( $terms as $term ) {
                    $checked = isset( $term->slug ) && in_array( $term->slug, $post_terms) ? ' checked' : '';
                    echo '<li>';
                    echo '<input type="checkbox" class="' . esc_attr($prefix) . 'tags' . '" id="'. esc_attr($prefix) . 'tags' . esc_attr($index) . '" name="'. esc_attr($prefix) . 'tags[]' . '" value="'. esc_attr($term->slug) .'" ' .esc_attr($checked). '>';
                    echo '<label for="'. esc_attr($prefix) . 'tags' . esc_attr($index++) . '">'.esc_html__($term->name, 'rex-product-feed').'</label>';
                    echo '</li>';
                }
            }
			?>
		</ul>
	</div>
</div>