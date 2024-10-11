<div class="rex-flex-table-body" role="rowgroup">
	<div class="flex-table-row">
		<div class="flex-row" role="columnheader">

			<div class="rex-feed-gmc-diagnostics-report-list-area__product-link">
				<?php include plugin_dir_path(__FILE__) . $product_icon;?>
				<a href="<?php echo esc_url( $product[ 'edit_link' ] ?? '' )?>" target="_blank" class="rex-feed-gmc-diagnostics-report-list-area__link"><?php echo esc_html( $product[ 'title' ] ?? '' )  ?></a>
			</div>

		</div>

		<div class="flex-row" role="columnheader">

			<!-- "Classes for low-diagnostics, medium-diagnostics, and advance-diagnostics have been added, with corresponding color changes for the report variable." -->
			<div class="rex-feed-gmc-diagnostics-report-list-area__potential-link low-diagnostics">
				<span class="rex-feed-gmc-diagnostics-report-list-area__low-text">Low</span>
				<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect width="5" height="20" transform="matrix(-1 0 0 1 21 0)" fill="#B8DFCD"/>
					<rect width="5" height="15" transform="matrix(-1 0 0 1 13 5)" fill="#B8DFCD"/>
					<rect width="5" height="8" transform="matrix(-1 0 0 1 5 12)" fill="#B8DFCD"/>
				</svg>
			</div>
		</div>

		<div class="flex-row" role="columnheader">
			<div class="rex-feed-gmc-diagnostics-report-list-area__status-link">
				<span class="rex-feed-gmc-diagnostics-report-list-area__not-approved">Not Approved</span>
				<a href="<?php echo esc_url( admin_url( "post.php?post={$feed_id}&action=edit" ) )?>" target="_blank" class="rex-feed-gmc-diagnostics-report-list-area__fix">Fix</a>
			</div>

		</div>

		<div class="flex-row" role="columnheader">
			<div class="rex-feed-gmc-diagnostics-report-list-area__error-area">
				<?php include plugin_dir_path(__FILE__) . $error_icon;?>
				<div class="rex-feed-gmc-diagnostics-report-list-area__error-view">
					<span class="rex-feed-gmc-diagnostics-report-list-area__error-text"><?php esc_html_e( $product[ 'issues' ][0][ 'description' ] ?? '' )?></span>
					<a class="rex-feed-gmc-diagnostics-report-list-area__view-detail">View Detail</a>
				</div>
			</div>
		</div>

		<!-- `rex-feed-gmc-diagnostics-report-popup` block -->
		<div class="rex-feed-gmc-diagnostics-report-popup" style="display: none" >
			<div class="rex-feed-gmc-diagnostics-report-popup__wrapper">
				<!-- `rex-feed-gmc-diagnostics-report-popup__body` element in the `rex-feed-gmc-diagnostics-report-popup` block  -->
				<div class="rex-feed-gmc-diagnostics-report-popup__body">
					<h2><?php echo __('What needs attention','rex-product-feed')?></h2>
					<span class="rex-feed-gmc-diagnostics-report-popup__close-btn">
		                <svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
		                <path d="M28.89 14.445C28.89 22.4228 22.4228 28.89 14.445 28.89C6.46725 28.89 0 22.4228 0 14.445C0 6.46725 6.46725 0 14.445 0C22.4228 0 28.89 6.46725 28.89 14.445Z" fill="white"/>
		                <path d="M19.9988 11.8L18.1761 9.97734L14.9879 13.1655L11.7997 9.97734L9.97705 11.8L13.1652 14.9882L9.97705 18.1763L11.7997 19.999L14.9879 16.8109L18.1761 19.999L19.9988 18.1763L16.8106 14.9882L19.9988 11.8Z" fill="#E56829"/>
		                </svg>
					</span>

					<!-- `rex-feed-gmc-diagnostics-report-popup__message` element in the `rex-feed-gmc-diagnostics-report-popup` block  -->
					<div class="rex-feed-gmc-diagnostics-report-popup__message">
						<?php foreach( $product[ 'issues' ] as $key => $issue ) { ?>
							<div class="rex-feed-gmc-diagnostics-report-popup__accordion-list">
								<div class="rex-feed-gmc-diagnostics-report-popup__accordion-active">
									<div class="rex-feed-gmc-diagnostics-report-popup__accordion-header">
										<?php include plugin_dir_path(__FILE__) . $error_icon;?>
										<div class="rex-feed-gmc-diagnostics-report-popup__accordion-header-icon">
											<h3><?php esc_html( $issue[ 'description' ] ?? '' )?></h3>
											<span class="rex-accordion__arrow <?php if ( 0 === (int)$key ) echo 'rotated';?>"></span>
										</div>
									</div>

									<div class="rex-feed-gmc-diagnostics-report-popup__accordion-content-wrapper">
										<p>Attribute: [<?php esc_html_e( $issue[ 'attribute' ] ?? '', 'rex-product-feed' );?>]</p>
										<p><?php esc_html( $issue[ 'detail' ] ?? '' );?></p>
										<a href="<?php echo esc_url( $issue[ 'documentation' ] ?? '' );?>" target="_blank" role="button">Learn more</a>
									</div>

								</div>
							</div>
						<?php }?>
					</div>
				</div>
			</div>
		</div>
		<!-- `rex-feed-gmc-diagnostics-report-popup` block  end -->
	</div>
	<!-- .flex-table-row end -->
</div>