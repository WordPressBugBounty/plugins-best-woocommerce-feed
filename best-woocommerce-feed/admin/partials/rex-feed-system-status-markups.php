<?php
/**
 * This file is responsible for displaying system status
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

$system_status = Rex_Feed_System_Status::get_all_system_status();
?>

<div id="tab3" class="tab-content block-wrapper">

	<!-- `rex-system-status`  block -->
	<div class="system-status rex-system-status">

		<!-- `system-status__platform` element in the `rex-system-status` block  -->
		<div class="rex-system-status__platform">
			<h3 class="rex-system-status__heading">
				<?php echo esc_html__( 'System Status', 'rex-product-feed' ); ?>
			</h3>
			<button type="button" class="rex-system-status__button" id="rex-feed-system-status-copy-btn">
				<svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none">
					<path d="M14.1 5.90002H7.80002C7.02683 5.90002 6.40002 6.52683 6.40002 7.30002V13.6C6.40002 14.3732 7.02683 15 7.80002 15H14.1C14.8732 15 15.5 14.3732 15.5 13.6V7.30002C15.5 6.52683 14.8732 5.90002 14.1 5.90002Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M3.6 10.1H2.9C2.5287 10.1 2.1726 9.9525 1.91005 9.68995C1.6475 9.4274 1.5 9.0713 1.5 8.7V2.4C1.5 2.0287 1.6475 1.6726 1.91005 1.41005C2.1726 1.1475 2.5287 1 2.9 1H9.2C9.5713 1 9.9274 1.1475 10.1899 1.41005C10.4525 1.6726 10.6 2.0287 10.6 2.4V3.1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<?php esc_html_e( 'Copy System Status', 'rex-product-feed' ); ?>
			</button>
		</div>

		<!-- `rex-system-status__content` element in the `rex-system-status` block  -->
		<div class="rex-system-status__content">

			<?php
			foreach ( $system_status as $sys_status ) {
				if ( isset( $sys_status[ 'label' ] ) && '' !== $sys_status[ 'label' ] && isset( $sys_status[ 'message' ] ) && '' !== $sys_status[ 'message' ] ) {
					$skip_label = array( 'Version', 'WP Cron' );
					if ( in_array( $sys_status[ 'label' ], $skip_label ) && !isset( $sys_status[ 'status' ] ) ) {
						continue;
					}
					?>
				<!-- `rex-system-status__info` element in the `rex-system-status` block  -->
				<div class="rex-system-status__info">

					<!-- `rex-system-status__label` element in the `rex-system-status` block  -->
					<div class="rex-system-status__ground">
						<h6 class="rex-system-status__label">
						<?php echo esc_html( $sys_status[ 'label' ] ); ?>
						</h6>
					</div>

					<div class="rex-system-status__lists">

						<span class="rex-system-status__list">
						<?php
							$message = $sys_status[ 'message' ];  //phpcs:ignore
							$classes = 'dashicons dashicons-yes';
						if ( !empty( $sys_status[ 'label' ] ) && ( 'Product Types' === $sys_status[ 'label' ] || 'Total Products by Types' === $sys_status[ 'label' ] ) ) {
							$classes = '';
						}
						if ( isset( $sys_status[ 'status' ] ) && 'error' === $sys_status[ 'status' ] || isset( $sys_status[ 'is_writable' ] ) && 'False' === $sys_status[ 'is_writable' ] ) {
							echo wp_kses( "<mark class='error'><span class='dashicons dashicons-warning'></span>{$message}</mark>", rex_feed_get_allowed_kseser() );
						}
						else {
							echo wp_kses( "<mark class='yes'><span class='{$classes}'></span>{$message}</mark>", rex_feed_get_allowed_kseser() );
						}
						?>
						</span>
					</div>

				 </div>

					<?php
				}
			}
			?>
		
		</div>

	</div>

    <textarea name="" id="rex-feed-system-status-area" style="visibility: hidden; margin-top: 10px" cols="100" rows="30"><?php echo Rex_Feed_System_Status::get_system_status_text(); //phpcs:ignore?></textarea>
</div>
