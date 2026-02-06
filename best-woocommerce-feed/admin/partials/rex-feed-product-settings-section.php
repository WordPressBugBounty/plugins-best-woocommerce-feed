<?php
$icon_question = 'icon/icon-svg/icon-question.php';
$post_id = isset($_GET['pr_post']) ? absint($_GET['pr_post']) : get_the_ID();
?>

<div class="rex-contnet-setting-area">

	<div class="rex-contnet-setting__header">
		<div class="rex-contnet-setting__header-text">
			<div class="rex-contnet-setting__icon rex-contnet__header-text">
				<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/icon-setting.php';?>
				<?php echo '<h2>' . esc_html__( 'Settings', 'rex-product-feed' ) . '</h2>';?>
			</div>
		</div>

        <span class="rex-contnet-filter__cross-icon close-btn" id="rex_feed_settings_modal_close_btn">
			<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/cross.php';?>
        </span>
	</div>

	<div class="rex-contnet-setting-content-area">

		<div class="<?php echo esc_attr( $this->prefix ) . 'schedule';?>">
			<label for="<?php echo esc_attr( $this->prefix ) . 'schedule_label';?>"><?php esc_html_e('Auto-Generate Your Feed', 'rex-product-feed')?>
				<span class="rex_feed-tooltip">
                    <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
                    <p><?php esc_html_e( 'Schedule feed updates at your preferred interval: No interval, Hourly, Daily, Weekly, or a custom time each day. ', 'rex-product-feed' ); ?><a href="<?php echo esc_url( ' https://rextheme.com/docs/wpfm-schedule-auto-update-of-feed-on-intervals/?utm_source=plugin&utm_medium=auto_update_link&utm_campaign=pfm_plugin' )?>" target="_blank"><?php esc_html_e('View Doc', 'rex-product-feed')?></a></p>
                </span>
			</label>

			<div class="rex-feed-custom-time-field-area"></div>
			<ul id="<?php echo esc_html( $this->prefix ) . 'schedule';?>">
				<?php
				$index = 1;
				$prev_value = get_post_meta( $post_id, '_rex_feed_schedule', true );
				$prev_value = $prev_value ?: get_post_meta( $post_id, 'rex_feed_schedule', true );
				$prev_value = $prev_value ?: 'no';
				foreach( $schedules as $key => $value ) {
					$checked = $key === $prev_value ? ' checked="checked"' : '';
					echo '<li>';
					echo '<input type="radio" id="'. esc_attr( $this->prefix ) . 'schedule' . esc_attr( $index ) . '" name="'. esc_attr( $this->prefix ) . 'schedule' . '" value="'. esc_attr( $key ) .'" ' . esc_html( $checked ) . '>';
					echo '<label for="'. esc_attr( $this->prefix ) . 'schedule' . esc_attr( $index++ ) . '">'.esc_html__( $value, 'rex-product-feed' ).'</label>';
					echo '</li>';
				}

				/**
				 * Fires when generating custom markup for an auto feed generation option in the Rex Product Feed plugin.
				 *
				 * This action provides a way to insert custom HTML markup or elements into the settings
				 * related to the automatic generation of product feeds. Developers can use this hook to
				 * add their own interface elements, such as custom schedule time dropdowns or other controls,
				 * to tailor the feed generation options to their specific needs.
				 *
				 * @since 7.3.13
				 */
				do_action('rexfeed_auto_generation_option_markups');
				?>
			</ul>

            <?php
            /**
             * Fires after rendering the auto-generation options field in the Rex Product Feed plugin settings.
             *
             * This action provides developers with the opportunity to insert custom content or elements
             * immediately after the auto-generation options field in the plugin settings. You can use this
             * hook to add supplementary instructions, additional controls, or any other content that should
             * appear after the auto-generation options for product feeds.
             *
             * @since 7.3.13
             */
            do_action('rex_feed_after_autogenerate_options_field');
            ?>
		</div>

		<div class="<?php echo esc_attr( $this->prefix ) . 'country_list_area'; ?>"

        <div class="<?php echo esc_attr( $this->prefix ) . 'curcy_list_area'; ?>">
			
            <?php
            $merchant = get_post_meta( $post_id, '_rex_feed_merchant', true );
            $merchant = $merchant ?: get_post_meta( $post_id, 'rex_feed_merchant', true );
            $display  = 'google' === $merchant ? '' : 'style="display:none;"';
            ?>
            <div class="<?php echo esc_attr( $this->prefix ) . 'is_google_content_api'; ?> pl-10" <?php echo $display;?>>
                <label for="<?php echo esc_attr( $this->prefix ) . 'is_google_content_api'; ?>">
                    <?php esc_html_e( 'Send Products via Google Content API', 'rex-product-feed' ); ?>
                    <span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'Sync Products using Google Content API.', 'rex-product-feed' ); ?></p>
					</span>
                </label>

                <div class="switch">
                    <div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta( $post_id, '_rex_feed_is_google_content_api', true );
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'is_google_content_api'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'is_google_content_api'?>" <?php echo esc_attr( $checked )?>>
                        <label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'is_google_content_api'?>"></label>
                    </div>
                </div>
            </div>

            <div class="<?php echo esc_attr( $this->prefix ) . 'country_list_content'; ?> pl-10">
                <label for="<?php echo esc_attr( $this->prefix ) . 'feed_country_label'; ?>"><?php esc_html_e( 'Country', 'rex-product-feed' ); ?>
                    <span class="rex_feed-tooltip">
						<?php require WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question; ?>
						<p>
							<?php esc_html_e( 'Define the country for shipping attribute values (Google/Facebook feeds). ', 'rex-product-feed' ); ?><a href="<?php echo esc_url( 'https://rextheme.com/docs/how-to-include-shipping-values-into-woocommerce-product-feed/' ); ?>" target="_blank"><?php esc_html_e( 'View Doc', 'rex-product-feed' ); ?></a>
						</p>
					</span>
                </label>

                <select name="<?php echo esc_attr( $this->prefix ) . 'feed_country'; ?>" id="<?php echo esc_attr( $this->prefix ) . 'feed_country'; ?>" class="">
                    <?php
                    $saved_country = get_post_meta( $post_id, '_' . esc_attr( $this->prefix ) . 'feed_country', true );
                    $saved_country = $saved_country ?: get_post_meta( $post_id, esc_attr( $this->prefix ) . 'feed_country', true );
                    $wc_countries  = new WC_Countries();

                    if( $saved_country ) {
                        $saved_country = explode( ':', $saved_country );
                        $saved_country = !empty( $saved_country[ 1 ] ) ? $saved_country[ 1 ] : $saved_country[ 0 ];
                    }
                    else {
                        $saved_country = $wc_countries->get_base_country();
                    }

                    $wc_countries = $wc_countries->get_countries();

                    if ( is_array( $wc_countries ) && !empty( $wc_countries ) ) {
                        foreach ( $wc_countries as $value => $label ) {
                            $selected = $saved_country === $value ? ' selected' : '';
                            echo '<option value="' . esc_attr( $value ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $label ) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

		<div class="<?php echo esc_attr( $this->prefix ) . 'include_out_of_stock'; ?> ">
			<div class="<?php echo esc_attr( $this->prefix ) . 'include_out_of_stock_content'; ?> pl-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'include_out_of_stock_label'; ?>">
					<?php esc_html_e( 'Include Out of Stock Products', 'rex-product-feed' ); ?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'Add or exclude out-of-stock items.', 'rex-product-feed' ); ?></p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_include_out_of_stock', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_include_out_of_stock', true);
                        $saved_value = $saved_value ?: 'yes';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
						<input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'include_out_of_stock'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'include_out_of_stock'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'include_out_of_stock'?>"></label>
					</div>
				</div>
			</div>

			<div class="<?php echo esc_attr( $this->prefix ) . 'include_zero_price_products_content';?> pr-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'include_zero_price_products_label';?>">
					<?php esc_html_e('Include Products with No Price', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'Add or exclude products without a set price.', 'rex-product-feed' ); ?></p>
					</span>
				</label>

			    <div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_include_zero_price_products', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_include_zero_price_products', true);
                        $saved_value = $saved_value ?: 'yes';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'include_zero_price_products'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'include_zero_price_products'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'include_zero_price_products'?>"></label>
					</div>
				</div>
			</div>
		</div>
		
		<div class="<?php echo esc_attr( $this->prefix ) . 'variable_product_area';?> ">

			<div class="<?php echo esc_attr( $this->prefix ) . 'variable_product';?> pl-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'variable_product_label';?>">
					<?php esc_html_e('Include Variable Parent Product (No Variations)', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'Add or exclude parent products without variations.', 'rex-product-feed' ); ?></p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_variable_product', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_variable_product', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'variable_product'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'variable_product'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'variable_product'?>"></label>
					</div>
				</div>
			</div>

			<div class="<?php echo esc_attr( $this->prefix ) . 'hidden_products';?> pr-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'hidden_products_label';?>"><?php esc_html_e('Exclude Invisible/Hidden Products', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'Skip products marked hidden in WooCommerce.', 'rex-product-feed' ); ?><a href="<?php echo esc_url( 'https://rextheme.com/docs/wpfm-exclude-invisible-products-hidden-products/?utm_source=plugin&utm_medium=exclude_invisible_products_link&utm_campaign=pfm_plugin' )?>" target="_blank"><?php esc_html_e('View Doc', 'rex-product-feed')?></a>
					</p>
					</span>
				</label>
				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_hidden_products', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_hidden_products', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'hidden_products'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'hidden_products'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'hidden_products'?>"></label>
					</div>
				</div>
			</div>

			<div class="<?php echo esc_attr( $this->prefix ) . 'variation_product_name';?> pl-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'variation_product_name_label';?>"><?php esc_html_e('Include Variation Name In The Product Title', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p>
							<?php
							esc_html_e( 'Add or exclude the variation name in the product title (e.g. “T-Shirt – Red”). ', 'rex-product-feed' );?>
							<a href="<?php echo esc_url( 'https://rextheme.com/docs/how-to-include-product-variation-term-to-the-product-name/' )?>" target="_blank"><?php esc_html_e('View Doc', 'rex-product-feed')?></a>
						</p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_variation_product_name', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_variation_product_name', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'variation_product_name'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'variation_product_name'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'variation_product_name'?>"></label>
					</div>
				</div>
			</div>

			<div class="<?php echo esc_attr( $this->prefix ) . 'parent_product';?> pr-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'parent_product_label';?>"><?php esc_html_e('Include Grouped Products', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'Add or exclude grouped products.', 'rex-product-feed' ); ?></p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_parent_product', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_parent_product', true);
                        $saved_value = $saved_value ?: 'yes';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'parent_product'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'parent_product'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'parent_product'?>"></label>
					</div>
				</div>
			</div>

			<div class="<?php echo esc_attr( $this->prefix ) . 'variations';?> pl-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'variations_label';?>"><?php esc_html_e('Include All Variable Products Variations', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p>
							<?php
							esc_html_e( 'Add or exclude all variations as separate products.', 'rex-product-feed' );
						
							?>
						</p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_variations', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_variations', true);
                        $saved_value = $saved_value ?: 'yes';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'variations'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'variations'?>" <?php echo esc_attr( $checked )?>>
												<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'variations'?>"></label>
					</div>
				</div>
			</div>

			<div class="<?php echo esc_attr( $this->prefix ) . 'default_variation';?> pr-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'default_variation_label';?>"><?php esc_html_e('Include Default Variations', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p>
							<?php
							esc_html_e( 'Enable this option to include only the default variation of variable products in the feed. Other variations will be excluded.', 'rex-product-feed' );
						
							?>
						</p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_default_variation', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_default_variation', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'default_variation'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'default_variation'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'default_variation'?>"></label>
					</div>
				</div>
			</div>

			<div class="<?php echo esc_attr( $this->prefix ) . 'cheapest_variation';?> pl-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'cheapest_variation_label';?>"><?php esc_html_e('Include Cheapest Priced Variations', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p>
							<?php
							esc_html_e( 'Enable this option to include only the cheapest variation of variable products in the feed. Other variations will be excluded.', 'rex-product-feed' );
						
							?>
						</p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_cheapest_variation', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_cheapest_variation', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'cheapest_variation'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'cheapest_variation'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'cheapest_variation'?>"></label>
					</div>
				</div>
			</div>

			<div class="<?php echo esc_attr( $this->prefix ) . 'highest_variation';?> pr-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'highest_variation_label';?>"><?php esc_html_e('Include Highest Priced Variations', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p>
							<?php
							esc_html_e( 'Enable this option to include only the highest priced variation of variable products in the feed. Other variations will be excluded.', 'rex-product-feed' );
						
							?>
						</p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_highest_variation', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_highest_variation', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'highest_variation'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'highest_variation'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'highest_variation'?>"></label>
					</div>
				</div>
			</div>

			<div class="<?php echo esc_attr( $this->prefix ) . 'first_variation';?> pl-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'first_variation_label';?>"><?php esc_html_e('Include First Variation', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p>
							<?php
							esc_html_e( 'Enable this option to include only the first variation of variable products in the feed. Other variations will be excluded.', 'rex-product-feed' );
						
							?>
						</p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_first_variation', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_first_variation', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'first_variation'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'first_variation'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'first_variation'?>"></label>
					</div>
				</div>
			</div>

			<div class="<?php echo esc_attr( $this->prefix ) . 'last_variation';?> pr-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'last_variation_label';?>"><?php esc_html_e('Include Last Variation', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p>
							<?php
							esc_html_e( 'Enable this option to include only the last variation of variable products in the feed. Other variations will be excluded.', 'rex-product-feed' );
						
							?>
						</p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_last_variation', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_last_variation', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'last_variation'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'last_variation'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'last_variation'?>"></label>
					</div>
				</div>
			</div>
			
			<div class="<?php echo esc_attr( $this->prefix ) . 'exclude_simple_products';?> pl-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'exclude_simple_products_label';?>"><?php esc_html_e('Exclude All Simple Products', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'Enable this option to exclude all Simple Products from the feed. Only Variable and Grouped products will be included.', 'rex-product-feed' ); ?></p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_exclude_simple_products', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_exclude_simple_products', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'exclude_simple_products'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'exclude_simple_products'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'exclude_simple_products'?>"></label>
					</div>
				</div>
			</div>


			<?php
			/**
             * dynamically.
             *
             * @since 7.4.5
             *
             * @hook rexfeed_feed_settings_after_product_types_fields
             */
            // do_action( 'rexfeed_feed_settings_after_product_types_fields' );
            ?>

		</div>

		<div class="<?php echo esc_attr( $this->prefix ) . 'skip_product_area';?> ">
			<div class="<?php echo esc_attr( $this->prefix ) . 'skip_product';?> pl-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'skip_product_label';?>"><?php esc_html_e('Skip products with empty value', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'Exclude products missing any required attribute.', 'rex-product-feed' ); ?></p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_skip_product', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_skip_product', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'skip_product'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'skip_product'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'skip_product'?>"></label>
					</div>
				</div>
				
			</div>

			<div class="<?php echo esc_attr( $this->prefix ) . 'skip_row';?> pr-10">
				<label for="<?php echo esc_attr( $this->prefix ) . 'skip_row_label';?>"><?php esc_html_e('Skip attributes with empty value', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'Exclude empty attributes (XML feeds only).', 'rex-product-feed' ); ?></p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_skip_row', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_skip_row', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'skip_row'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'skip_row'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'skip_row'?>"></label>
					</div>
				</div>
				
			</div>

		</div>
        <!-- .rex_feed_skip_product_area end -->

        <!-- Multi-currency plugin support starts from here -->
		<div class="<?php echo esc_attr( $this->prefix ) . 'currency_switcher_area';?>">

            <!-- Multi-currency by WPML -->
			<?php
			if( function_exists( 'wpfm_is_wcml_active' ) && wpfm_is_wcml_active() ) {
				global $sitepress, $woocommerce_wpml;
				$wcml_settings   = get_option( '_wcml_settings' );
				$wcml_currencies = isset( $wcml_settings[ 'currency_options' ] ) ? $wcml_settings[ 'currency_options' ] : array();
				$currencies      = array();

				foreach ($wcml_currencies as $key => $value) {
					$currencies[$key] = $key;
				}

				if( is_array($currencies )) {
					reset($currencies);
				}
			?>

			<div class="<?php echo esc_attr( $this->prefix ) . 'wcml_currency';?>">
				<label for="<?php echo esc_attr( $this->prefix ) . 'wcml_currency';?>"><?php esc_html_e('WCML Currency', 'rex-product-feed')?>
                    <span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'This option will convert all your product prices using WooCommerce Multilingual & Multicurrency', 'rex-product-feed' ); ?></p>
					</span>
				</label>
				<select name="<?php echo esc_html( $this->prefix ) . 'wcml_currency';?>" id="<?php echo esc_html( $this->prefix ) . 'wcml_currency';?>" class="">
					<?php
					$selected_price = get_post_meta( $post_id, '_rex_feed_wcml_currency', true );
					$selected_price = $selected_price ?: get_post_meta( $post_id, 'rex_feed_wcml_currency', true );
					foreach( $currencies as $key => $value ) {
						$selected = $selected_price === $key ? ' selected' : '';
						echo '<option value="'. esc_attr( $key ) .'" '. esc_html( $selected ) .'>'. esc_attr( $value ) .'</option>';
					}
					?>
				</select>
			</div>
			<?php } ?>

            <!-- Multi-currency by Aelia -->
			<?php
			if ( wpfm_is_aelia_active() ) {
				$aelia_settings = get_option( 'wc_aelia_currency_switcher' );
				$enabled_currency = is_array( $aelia_settings ) && isset( $aelia_settings[ 'enabled_currencies' ] )
					? $aelia_settings[ 'enabled_currencies' ] : '';
				$aelia_world_currency = get_woocommerce_currencies();
				$aelia_world_currency = is_array( $aelia_world_currency ) ? $aelia_world_currency : array();
				$currency_options = array();

				if ( is_array( $enabled_currency ) && !empty( $enabled_currency ) ) {
					foreach ( $enabled_currency as $currency ) {
						if( array_key_exists( $currency, $aelia_world_currency) ){
							$currency_options[ $currency ] = $aelia_world_currency[ $currency ];
						}
					}
				}
				else{
					$currency_options = array( 'Please configure Aelia Currency Switcher!' );
				}
				?>
				<div class="<?php echo esc_attr( $this->prefix ) . 'aelia_currency';?>">
					<label for="<?php echo esc_attr( $this->prefix ) . 'aelia_currency';?>"><?php esc_html_e('Aelia Currency', 'rex-product-feed')?>
						<i class="fa fa-question-circle" aria-hidden="true"></i>
					</label>
					<select name="<?php echo esc_html( $this->prefix ) . 'aelia_currency';?>" id="<?php echo esc_html( $this->prefix ) . 'aelia_currency';?>" class="">
						<?php
						$selected_price = get_post_meta( $post_id, '_rex_feed_aelia_currency', true );
						$selected_price = $selected_price ?: get_post_meta( $post_id, 'rex_feed_aelia_currency', true );
						foreach( $currency_options as $key => $value ) {
							$selected = $selected_price === $key ? ' selected' : '';
							echo '<option value="'. esc_attr( $key ) .'" '. esc_html( $selected ) .'>'. esc_attr( $value ) .'</option>';
						}
						?>
					</select>
				</div>

			<?php } ?>

            <!-- Multi-currency by WMC -->
			<?php
			if ( wpfm_is_wmc_active() ) {
				$wmc_settings = class_exists( 'WOOMULTI_CURRENCY_Data' ) ? WOOMULTI_CURRENCY_Data::get_ins() : array();
				$wmc_default_currency = !empty( $wmc_settings ) ? $wmc_settings->get_default_currency() : 'USD';
				$wmc_currency_list = !empty( $wmc_settings ) ? $wmc_settings->currencies_list : array();
				$wmc_world_currency = get_woocommerce_currencies();
				$wmc_world_currency = is_array( $wmc_world_currency ) ? $wmc_world_currency : array();
				$currency_options = array();

				if ( is_array( $wmc_currency_list ) && !empty( $wmc_currency_list ) ) {
					foreach ( $wmc_currency_list as $key => $value ) {
						if( array_key_exists( $key, $wmc_world_currency) ){
							$currency_options[ $key ] = $wmc_world_currency[ $key ];
						}
					}
				}
				else{
					$currency_options = array( 'Please configure WooCommerce Multi-Currency Switcher!' );
				}
				?>
				
				<div class="<?php echo esc_attr( $this->prefix ) . 'wmc_currency';?>">
					<label for="<?php echo esc_attr( $this->prefix ) . 'wmc_currency';?>"><?php esc_html_e('WooCommerce Multi-Currency', 'rex-product-feed')?>
						<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'This option will convert all your product prices using WooCommerce Multi-Currency Switcher', 'rex-product-feed' ); ?></p>
					</span>
					</label>
					<select name="<?php echo esc_html( $this->prefix ) . 'wmc_currency';?>" id="<?php echo esc_html( $this->prefix ) . 'wmc_currency';?>" class="">
						<?php
						$selected_price = get_post_meta( $post_id, '_rex_feed_wmc_currency', true );
						$selected_price = $selected_price ?: get_post_meta( $post_id, 'rex_feed_wmc_currency', true );
						$selected_price = $selected_price ?: $wmc_default_currency;
						foreach( $currency_options as $key => $value ) {
							$selected = $selected_price === $key ? ' selected' : '';
							echo '<option value="'. esc_attr( $key ) .'" '. esc_html( $selected ) .'>'. esc_attr( $value ) .'</option>';
						}
						?>
					</select>
				</div>

			<?php } ?>

	         <!-- TranslatePress start here -->
            <?php
            if ( wpfm_is_translatePress_active() ) {
                $translatePress_languages = trp_get_languages();
                ?>
                <div class="<?php echo esc_attr( $this->prefix ) . 'translate_press_language';?>">
                    <label for="<?php echo esc_attr( $this->prefix ) . 'translate_press_language';?>"><?php esc_html_e('Language by TranslatePress', 'rex-product-feed')?>
                        <span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'This option will translate your site string to the selected language.', 'rex-product-feed' ); ?></p>
					</span>
                    </label>
                    <select name="<?php echo esc_html( $this->prefix ) . 'translate_press_language';?>" id="<?php echo esc_html( $this->prefix ) . 'translate_press_language';?>" class="">
                        <?php
                        $selected_language = get_post_meta( $post_id, '_rex_feed_translate_press_language', true );
                        if ( is_array( $translatePress_languages ) && !empty( $translatePress_languages ) ) {
                            foreach ( $translatePress_languages as $key => $value ) {
                                $selected = $selected_language === $key ? ' selected' : '';
                                echo '<option value="' . esc_attr( $key ) . '" ' . esc_html( $selected ) . '>' . esc_html( $value ) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

            <?php } ?>

			<!-- TranslatePress end here-->

            <!-- CURCY start here -->
            <?php
            if ( wpfm_is_curcy_active() ) {
                $currencies = array();
                if ( class_exists( 'WOOMULTI_CURRENCY_F_Data' ) ) {
                  $curcy_instance =   \WOOMULTI_CURRENCY_F_Data::get_ins();
                    $currencies = $curcy_instance->get_currencies();
                    $get_default_currency = $curcy_instance->get_default_currency();
                }
                $curcy_world_currency = get_woocommerce_currencies();

                $currency_options = array();

                if ( is_array( $currencies ) && !empty( $currencies ) ) {
                    foreach ( $currencies as $key => $value ) {
                            $currency_options[ $value ] = $value;
                        }
                    }
                else {
                    $currency_options = array( __('Please configure Curcy Currency Switcher!', 'rex-product-feed') );
                }
                ?>
                <div class="<?php echo esc_attr( $this->prefix ) . 'curcy_currency';?>">
                    <label for="<?php echo esc_attr( $this->prefix ) . 'curcy_currency';?>"><?php esc_html_e('CURCY Currency', 'rex-product-feed')?>
                        <span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'This option will convert your product price to the selected currency.', 'rex-product-feed' ); ?></p>
					</span>
                    </label>
                    <select name="<?php echo esc_html( $this->prefix ) . 'curcy_currency';?>" id="<?php echo esc_html( $this->prefix ) . 'curcy_currency';?>" class="">
                        <?php
                        $selected_price = get_post_meta( $post_id, '_rex_feed_curcy_currency', true );
                        $selected_price = $selected_price ?: get_post_meta( $post_id, 'rex_feed_curcy_currency', true );
                        foreach( $currency_options as $key => $value ) {
                            $selected = $selected_price === $key ? ' selected' : '';
                            echo '<option value="'. esc_attr( $key ) .'" '. esc_html( $selected ) .'>'. esc_attr( $value ) .'</option>';
                        }
                        ?>
                    </select>
                </div>

            <?php } ?>

            <!-- CURCY end here-->

            <!-- Multi-currency by WOOCS -->
            <?php
            if( defined( 'WOOCS_VERSION' ) ) {
                global $WOOCS;
                $woocs_currencies = $WOOCS->get_currencies();
                ?>

                <div class="<?php echo esc_attr( $this->prefix ) . 'woocs_currency';?>">
                    <label for="<?php echo esc_attr( $this->prefix ) . 'woocs_currency';?>"><?php esc_html_e('Currency by WOOCS', 'rex-product-feed')?>
                        <span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'This option will convert all your product prices using FOX – Currency Switcher Professional for WooCommerce (formerly WOOCS)', 'rex-product-feed' ); ?></p>
					</span>
                    </label>
                    <select name="<?php echo esc_html( $this->prefix ) . 'woocs_currency';?>" id="<?php echo esc_html( $this->prefix ) . 'woocs_currency';?>" class="">
                        <?php
                        $selected_woocs = get_post_meta( $post_id, '_rex_feed_woocs_currency', true );
                        if ( is_array( $woocs_currencies ) && !empty( $woocs_currencies ) ) {
                            foreach ( $woocs_currencies as $key => $value ) {
                                $selected = $selected_woocs === $key ? ' selected' : '';
                                echo '<option value="' . esc_attr( $key ) . '" ' . esc_html( $selected ) . '>' . esc_html( $key ) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            <?php } ?>

		</div>
        <!-- Multi-currency plugin support ends here -->

		<div class="<?php echo esc_attr( $this->prefix ) . 'analytics_params_options';?>">
			<div class="<?php echo esc_attr( $this->prefix ) . 'analytics_params_content';?>">
				<label for="<?php echo esc_attr( $this->prefix ) . 'analytics_params_options_content';?>"><?php esc_html_e('Track Your Campaign', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e( 'Add UTM tracking parameters to feed links.', 'rex-product-feed' ); ?><a href="<?php echo esc_url( 'https://rextheme.com/docs/how-to-add-utm-parameters-to-product-urls/?utm_source=plugin&utm_medium=analytics_parameters_link&utm_campaign=pfm_plugin' )?>" target="_blank"><?php esc_html_e('View Doc', 'rex-product-feed')?></a>
					</p>
					</span>
				</label>

				<div class="switch">
					<div class="wpfm-switcher">
                        <?php
                        $saved_value = get_post_meta($post_id, '_rex_feed_analytics_params_options', true);
                        $saved_value = $saved_value ?: get_post_meta($post_id, 'rex_feed_analytics_params_options', true);
                        $saved_value = $saved_value ?: 'no';
                        $checked = 'yes' === $saved_value ? ' checked' : '';
                        ?>
                        <input class="switch-input" type="checkbox" name="<?php echo esc_attr( $this->prefix ) . 'analytics_params_options'?>" value="yes" id="<?php echo esc_attr( $this->prefix ) . 'analytics_params_options'?>" <?php echo esc_attr( $checked )?>>
						<label class="lever" for="<?php echo esc_attr( $this->prefix ) . 'analytics_params_options'?>"></label>
					</div>
				</div>
			</div>

			<span class="<?php echo esc_attr( $this->prefix ) . 'toggle_utm';?>"><?php esc_html_e( 'On Toggle to activate UTM Params', 'rex-product-feed' ); ?></span>

		</div>

		<div class="<?php echo esc_attr( $this->prefix ) . 'analytics_params';?>" style="display: none">
			<label for="<?php echo esc_attr( $this->prefix ) . 'analytics_params';?>"><?php esc_html_e('UTM Parameters', 'rex-product-feed')?></label>
			<ul id="<?php echo esc_html( $this->prefix ) . 'analytics_params';?>">
				<?php
				$analytics_params = get_post_meta( $post_id, '_rex_feed_analytics_params', true );
				$analytics_params = $analytics_params ?: get_post_meta( $post_id, 'rex_feed_analytics_params', true );
				$utm_source       = $analytics_params[ 'utm_source' ] ?? '';
				$utm_medium       = $analytics_params[ 'utm_medium' ] ?? '';
				$utm_campaign     = $analytics_params[ 'utm_campaign' ] ?? '';
				$utm_term         = $analytics_params[ 'utm_term' ] ?? '';
				$utm_content      = $analytics_params[ 'utm_content' ] ?? '';

				echo '<li>';
				?>
				<label for="<?php echo esc_attr( $this->prefix ) . 'analytics_params_utm_source';?>"><?php esc_html_e('Referrer', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e('Source of traffic (e.g. google, newsletter).', 'rex-product-feed') ?></p>
					</span>
				</label>

				<?php
				echo '<input type="text" name="' . esc_html( $this->prefix ) . 'analytics_params[utm_source]' . '" value="' .esc_attr($utm_source). '" id="'. esc_attr( $this->prefix ) . 'analytics_params_utm_source' .'">';
				echo '</li>';

				echo '<li>';
				?>
				<label for="<?php echo esc_attr( $this->prefix ) . 'analytics_params_utm_medium';?>"><?php esc_html_e('Medium', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e('Marketing channel (e.g. cpc, email).', 'rex-product-feed') ?></p>
					</span>
				</label>

				<?php
				echo '<input type="text" name="' . esc_html( $this->prefix ) . 'analytics_params[utm_medium]' . '" value="' .esc_attr($utm_medium). '" id="'. esc_attr( $this->prefix ) . 'analytics_params_utm_medium' .'">';
				echo '</li>';

				echo '<li>';
				?>
				<label for="<?php echo esc_attr( $this->prefix ) . 'analytics_params_utm_campaign';?>"><?php esc_html_e('Campaign', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e('Campaign name or promo code (e.g. spring_sale).', 'rex-product-feed') ?></p>
					</span>
				</label>

				<?php
				echo '<input type="text" name="' . esc_html( $this->prefix ) . 'analytics_params[utm_campaign]' . '" value="' .esc_attr($utm_campaign). '" id="'. esc_attr( $this->prefix ) . 'analytics_params_utm_campaign' .'">';
				echo '</li>';


				echo '<li>';
				?>
				<label for="<?php echo esc_attr( $this->prefix ) . 'analytics_params_utm_term';?>"><?php esc_html_e('Campaign Term', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e('Keyword for paid ads.', 'rex-product-feed') ?></p>
					</span>
				</label>

				<?php
				echo '<input type="text" name="' . esc_html( $this->prefix ) . 'analytics_params[utm_term]' . '" value="' .esc_attr($utm_term). '" id="'. esc_attr( $this->prefix ) . 'analytics_params_utm_term' .'">';
				echo '</li>';

				echo '<li>';
				?>
				<label for="<?php echo esc_attr( $this->prefix ) . 'analytics_params_utm_content';?>"><?php esc_html_e('Campaign Content', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon_question;?>
						<p><?php esc_html_e('Differentiate between multiple ads.', 'rex-product-feed') ?></p>
					</span>
				</label>

				<?php
				echo '<input type="text" name="' . esc_html( $this->prefix ) . 'analytics_params[utm_content]' . '" value="' .esc_attr($utm_content). '" id="'. esc_attr( $this->prefix ) . 'analytics_params_utm_content' .'">';
				echo '</li>';
				?>
			</ul>
		</div>

		<div id="rex-feed-settings-save-changes" role="dialog" aria-labelledby="rex-feed-save-title">
			<button id="rex_feed_settings_modal_close_btn" 
				aria-label="<?php esc_attr_e('Save Changes', 'rex-product-feed'); ?>">
				<?php echo __('Save Changes', 'rex-product-feed'); ?>
			</button>
		</div>
	</div>
</div>