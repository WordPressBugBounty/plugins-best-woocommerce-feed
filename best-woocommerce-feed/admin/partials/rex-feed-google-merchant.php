<?php $icon = '../assets/icon/icon-svg/icon-question.php'; ?>

<?php
$feed_id      = get_the_ID();
$value        = get_post_meta( $feed_id, '_rex_feed_google_target_country', true );
$value        = $value ?: get_post_meta( $feed_id, 'rex_feed_google_target_country', true );
$value        = $value ?: 'US';
$schedule_val = get_post_meta( $feed_id, '_rex_feed_google_schedule', true );
$schedule_val = $schedule_val ?: get_post_meta( $feed_id, 'rex_feed_google_schedule', true );
$display_none = 'style="display: none"';
$is_google_content_api = 'yes' === get_post_meta( $feed_id, '_rex_feed_is_google_content_api', true ) || 'yes' === get_post_meta( $feed_id, 'rex_feed_is_google_content_api', true );

// Migration banner: show only for Google feeds on the legacy Content API path.
$_data_source_id = get_post_meta( $feed_id, '_rex_feed_google_data_source_id', true );
$_data_feed_id   = get_post_meta( $feed_id, '_rex_feed_google_data_feed_id', true ) ?: get_post_meta( $feed_id, 'rex_feed_google_data_feed_id', true );
$_feed_merchant  = get_post_meta( $feed_id, '_rex_feed_merchant', true ) ?: get_post_meta( $feed_id, 'rex_feed_merchant', true );
$_show_migration_banner = ( 'google' === $_feed_merchant ) && ( $is_google_content_api || ! empty( $_data_feed_id ) ) && empty( $_data_source_id );
?>
<style>
@media (max-width: 768px) {
	.rex-merchant-api-migration-banner {
		flex-direction: column;
		align-items: flex-start !important;
		gap: 8px !important;
	}
	.rex-merchant-api-migration-banner button {
		align-self: flex-start;
	}
}
</style>

<?php if ( $is_google_content_api ) : ?>
	<style>
		#rex_feed_google_merchant .rex_feed_google_schedule_all__content,
		#send-to-google {
			display: none;
		}
	</style>
<?php endif; ?>

<div class="<?php echo esc_attr( $this->prefix ) . 'google_merchant_content__area'; ?>">

	<div class="<?php echo esc_attr( $this->prefix ) . 'google_desc__area'; ?>">
		<p>
            <?php
            esc_html_e( 'You can send the feed to Google Merchant Center through direct upload method or by using the Merchant API v1.', 'rex-product-feed' );
            ?>
        </p>
        <br>
		<div class="<?php echo esc_attr( $this->prefix ) . 'google_desc__link';?>">
            <a href="<?php echo esc_url( 'https://rextheme.com/docs/upload-woocomerce-product-feed-directly-to-google-merchant-center/?utm_source=plugin&utm_medium=google_form_direct_upload_link&utm_campaign=pfm_plugin' )?>" target="_blank"><?php esc_html_e('Direct Upload Method (No need for authorization)', 'rex-product-feed')?></a>
			<a href="<?php echo esc_url( 'https://rextheme.com/docs/auto-sync-products-to-google-merchant-center-using-merchant-api/?utm_source=plugin&utm_medium=get_started_auto_sync_link&utm_campaign=pfm_plugin' )?>" target="_blank"><?php esc_html_e('Merchant API Method (Require authorization)', 'rex-product-feed')?></a>
            <a href="<?php echo esc_url( 'https://rextheme.com/google-country-codes-list/?utm_source=plugin&utm_medium=google_form_abbreviation_link&utm_campaign=pfm_plugin' )?>" target="_blank"><?php esc_html_e('Check Abbreviation Lists','rex-product-feed')?></a>
		</div>

	</div>

	<div class="<?php echo esc_attr( $this->prefix ) . 'google_target__area'; ?>">
		<div class="<?php echo esc_attr( $this->prefix ) . 'google_target__content'; ?>">
			<div id="<?php echo esc_attr( $this->prefix ) . 'google_target_country__content'; ?>" class="<?php echo esc_attr( $this->prefix ) . 'google_target_country__content'; ?>">

				<label for="<?php echo esc_attr( $this->prefix ) . 'google_target_country';?>"><?php esc_html_e('Target Country', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
                        <?php include plugin_dir_path(__FILE__) . $icon;?>
                        <p><?php esc_html_e('Please note that Google has fixed abbreviations for Location. For example, the abbreviation for target location, United States is US.', 'rex-product-feed')?></p>
                    </span>
				</label>

				<input type="text" id="<?php echo esc_attr( $this->prefix ) . 'google_target_country';?>" value="<?php echo esc_attr($value)?>" name="<?php echo esc_attr( $this->prefix ) . 'google_target_country'?>" required>
			</div>

			<?php
			$value = get_post_meta( $feed_id, '_rex_feed_google_target_language', true );
			$value = $value ?: get_post_meta( $feed_id, 'rex_feed_google_target_language', true );
			$value = $value ?: 'en';
			
			?>
			<div id="<?php echo esc_attr( $this->prefix ) . 'google_target_language__content'; ?>" class="<?php echo esc_attr( $this->prefix ) . 'google_target_language__content'; ?>">
				<label for="<?php echo esc_attr( $this->prefix ) . 'google_target_language';?>"><?php esc_html_e('Target Language', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
                        <?php include plugin_dir_path(__FILE__) . $icon;?>
                        <p><?php esc_html_e('Please note that Google has fixed abbreviations for Language. For example, the abbreviation for language, English is en.', 'rex-product-feed')?></p>
                    </span>
				</label>
				<input type="text" id="<?php echo esc_attr( $this->prefix ) . 'google_target_language';?>" value="<?php echo esc_attr($value)?>" name="<?php echo esc_attr( $this->prefix ) . 'google_target_language'?>" required>
			</div>
		</div>

		<div class="<?php echo esc_attr( $this->prefix ) . 'google_schedule_all__content'; ?>">
            <div id="<?php echo esc_attr( $this->prefix ) . 'google_schedule__content'; ?>" class="<?php echo esc_attr( $this->prefix ) . 'google_schedule__content'; ?>">
                <label for="<?php echo esc_attr( $this->prefix ) . 'google_schedule';?>"><?php esc_html_e('Schedule', 'rex-product-feed')?>
                    <span class="rex_feed-tooltip">
								<?php include plugin_dir_path(__FILE__) . $icon;?>
								<p><?php esc_html_e('Schedule', 'rex-product-feed')?></p>
							</span>
                </label>
                <select name="<?php echo esc_attr( $this->prefix ) . 'google_schedule'; ?>" id="<?php echo esc_attr( $this->prefix ) . 'google_schedule'; ?>">
					<?php
					$schedule_val = $schedule_val ?: 'monthly';
					foreach ( $schedules as $key => $value ) {
						$selected = $key == $schedule_val ? ' selected' : '';
						echo '<option value="'.esc_attr($key).'" ' .esc_attr($selected). '>'.esc_attr($value).'</option>';
					}
					?>
                </select>
            </div>

			<div id="<?php echo esc_attr( $this->prefix ) . 'google_schedule_month__content'; ?>" class="<?php echo esc_attr( $this->prefix ) . 'google_schedule_month__content'; ?>" <?php if('monthly' !== $schedule_val) {echo $display_none;}?>>
				<label for="<?php echo esc_attr( $this->prefix ) . 'google_schedule_month';?>"><?php esc_html_e('Select Day of Month', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include plugin_dir_path(__FILE__) . $icon;?>
						<p><?php esc_html_e('Select Day of Month', 'rex-product-feed')?></p>
					</span>
				</label>

				<select name="<?php echo esc_attr( $this->prefix ) . 'google_schedule_month'; ?>"
						id="<?php echo esc_attr( $this->prefix ) . 'google_schedule_month'; ?>"
						data-conditional-value="monthly"
						data-conditional-id="<?php echo esc_attr( $this->prefix ) . 'google_schedule'; ?>">
					<?php
					$prev_value = get_post_meta( $feed_id, '_rex_feed_google_schedule_month', true );
					$prev_value = $prev_value === '' ? get_post_meta( $feed_id, 'rex_feed_google_schedule_month', true ) : $prev_value;
					$prev_value = $prev_value !== '' ? $prev_value : '1';

					foreach ( $month_array as $key => $value ) {
						$selected = $key == $prev_value ? ' selected' : '';
						echo '<option value="'.esc_attr($key).'" ' .esc_attr($selected). '>'.esc_attr($value).'</option>';
					}
					?>
				</select>
			</div>

			<div id="<?php echo esc_attr( $this->prefix ) . 'google_schedule_week_day__content'; ?>" class="<?php echo esc_attr( $this->prefix ) . 'google_schedule_week_day__content'; ?>"  <?php if('weekly' !== $schedule_val) {echo $display_none;}?>>
				<label for="<?php echo esc_attr( $this->prefix ) . 'google_schedule_month';?>"><?php esc_html_e('Select Day of Week', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include plugin_dir_path(__FILE__) . $icon;?>
						<p><?php esc_html_e('Select Day of Week', 'rex-product-feed')?></p>
					</span>
				</label>

				<select name="<?php echo esc_attr( $this->prefix ) . 'google_schedule_week_day'; ?>"
						id="<?php echo esc_attr( $this->prefix ) . 'google_schedule_week_day'; ?>"
						data-conditional-value="weekly"
						data-conditional-id="<?php echo esc_attr( $this->prefix ) . 'google_schedule'; ?>">
					<?php
					$prev_value = get_post_meta( $feed_id, '_rex_feed_google_schedule_week_day', true );
					$prev_value = $prev_value !== '' ? $prev_value : get_post_meta( $feed_id, 'rex_feed_google_schedule_week_day', true );
					$prev_value = $prev_value !== '' ? $prev_value : 'monday';
					
					foreach ( $weeks as $key => $value ) {
						$selected = $key == $prev_value ? ' selected' : '';
						echo '<option value="'.esc_attr($key).'" ' .esc_attr($selected). '>'.esc_attr($value).'</option>';
					}
					?>
				</select>
			</div>

			<div id="<?php echo esc_attr( $this->prefix ) . 'google_schedule_time__content'; ?>" class="<?php echo esc_attr( $this->prefix ) . 'google_schedule_time__content'; ?>"  <?php if('hourly' !== $schedule_val) {echo $display_none;}?>>
				<label for="<?php echo esc_attr( $this->prefix ) . 'google_schedule_time';?>"><?php esc_html_e('Select Hour', 'rex-product-feed')?>
					<span class="rex_feed-tooltip">
						<?php include plugin_dir_path(__FILE__) . $icon;?>
						<p><?php esc_html_e('Select Hour', 'rex-product-feed')?></p>
					</span>
				</label>

				<select name="<?php echo esc_attr( $this->prefix ) . 'google_schedule_time'; ?>"
						id="<?php echo esc_attr( $this->prefix ) . 'google_schedule_time'; ?>">
					<?php
					$prev_value = get_post_meta( $feed_id, '_rex_feed_google_schedule_time', true );
					$prev_value = $prev_value !== '' ? $prev_value : get_post_meta( $feed_id, 'rex_feed_google_schedule_time', true );
					$prev_value = $prev_value !== '' ? $prev_value : '1';

					foreach ( range( 0, 23 ) as $key => $value ) {
						$selected = $key == $prev_value ? ' selected' : '';
						echo '<option value="'.esc_attr($value).'" ' .esc_attr($selected). '>'.esc_attr($value).'</option>';
					}
					?>
				</select>
			</div>

		</div>

		<?php if ( $_show_migration_banner ) : ?>
		<div class="rex-merchant-api-migration-banner" id="rex-merchant-api-migration-banner" style="background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:12px 16px;margin-top:20px;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
			<span style="font-size:13px;">
				<strong><?php esc_html_e( 'Action required:', 'rex-product-feed' ); ?></strong>
				<?php esc_html_e( 'Google Content API retires August 18, 2026. Migrate this feed to Merchant API v1 now.', 'rex-product-feed' ); ?>
			</span>
			<button type="button" id="rex-migrate-to-merchant-api" class="button button-primary" data-feed-id="<?php echo esc_attr( $feed_id ); ?>"
				style="white-space:nowrap;flex-shrink:0;">
				<?php esc_html_e( 'Migrate Now', 'rex-product-feed' ); ?>
			</button>
		</div>
		<div id="rex-migration-status" style="display:none;margin-bottom:10px;padding:8px 12px;border-radius:4px;font-size:13px;"></div>
		<?php endif; ?>
		
		<?php 
			$feed_merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );
			$feed_merchant = $feed_merchant ?: get_post_meta( $feed_id, 'rex_feed_merchant', true );

			if ( $feed_merchant === 'google' ) {
				$feed_url = get_post_meta( $feed_id, '_rex_feed_xml_file', true ) || get_post_meta( $feed_id, 'rex_feed_xml_file', true );
				$rex_google_merchant = new Rex_Google_Merchant_Settings_Api();

                if ( $rex_google_merchant::$client_id && $rex_google_merchant::$client_secret && $rex_google_merchant::$merchant_id ) {
                    $message = esc_html__( 'Access token has expired. Please, authenticate again if you want to submit a completely new feed to Google Merchant Center.', 'rex-product-feed' );
                    $button  = esc_html__( 'Authenticate', 'rex-product-feed' );
                } else {
                    $message = esc_html__( 'Use Google Auto-sync to send data to your Google Merchant Center at fixed intervals. Configure and Authenticate Auto-sync with Google to be able to use this feature.', 'rex-product-feed' );
                    $button  = esc_html__( 'Configure', 'rex-product-feed' );
                }
				
				if ( $_data_source_id ) {
					// Feed is on Merchant API v1 — UserRefreshCredentials handles token refresh automatically.
					// No need to block on access token expiry.
					if ( !empty( $feed_url ) ) {
						echo '<a class="btn waves-effect waves-light" id="send-to-google" href="#">
								' . esc_attr__( 'Send to google merchant', 'rex-product-feed' ) . '
							</a> ';
					}
				} elseif ( !( $rex_google_merchant->is_authenticate() ) ) {
					// Content API path — requires a valid (non-expired) access token.
					echo '<div class="google-status-area">';
					echo sprintf(
						'<p class="google-status">%s</p>',
						esc_html( $message ) );

					echo sprintf(
						'<a href="%s" class="btn-default">' . esc_html( $button ) . '</a>',
						esc_url( admin_url( 'admin.php?page=merchant_settings' ) ) );

					echo '</div>';
				} elseif ( !empty( $feed_url ) ) {
					echo '<a class="btn waves-effect waves-light" id="send-to-google" href="#">
							' . esc_attr__( 'Send to google merchant', 'rex-product-feed' ) . '
						</a> ';
				}
				echo '<div class="rex-google-status"></div>';
			}
		?>
	</div>
</div>