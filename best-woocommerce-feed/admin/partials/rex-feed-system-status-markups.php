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

$grouped_status = Rex_Feed_System_Status::get_grouped_system_status();

// Calculate total healthy and warnings
$total_healthy = 0;
$total_warnings = 0;
$total_items = 0;
foreach ($grouped_status as $group) {
	foreach ($group['items'] as $item) {
		$total_items++;
		if (isset($item['status']) && 'error' === $item['status']) {
			$total_warnings++;
		} else {
			$total_healthy++;
		}
	}
}
?>

<div id="tab3" class="tab-content block-wrapper pfm-system-status-tab">
	<div class="pfm-system-status-header">
		<div class="pfm-system-status-counts">
			<span class="pfm-status-count pfm-status-healthy">
				<span class="pfm-status-dot pfm-dot-green"></span> <?php echo esc_html($total_healthy); ?> healthy
			</span>
			<span class="pfm-status-count pfm-status-warning">
				<span class="pfm-status-dot pfm-dot-orange"></span> <?php echo esc_html($total_warnings); ?> warnings
			</span>
		</div>
		<div class="pfm-system-status-actions">
			<button type="button" class="pfm-action-btn" id="pfm-expand-all-btn">Expand all</button>
			<button type="button" class="pfm-action-btn" id="pfm-collapse-all-btn">Collapse all</button>
			<button type="button" class="pfm-btn pfm-btn-primary" id="rex-feed-system-status-copy-btn">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M13.3333 6H7.33333C6.59695 6 6 6.59695 6 7.33333V13.3333C6 14.0697 6.59695 14.6667 7.33333 14.6667H13.3333C14.0697 14.6667 14.6667 14.0697 14.6667 13.3333V7.33333C14.6667 6.59695 14.0697 6 13.3333 6Z" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M3.33333 10H2.66667C2.29848 10 2.00001 9.70152 2.00001 9.33333V2.66667C2.00001 2.29848 2.29848 2 2.66667 2H9.33333C9.70152 2 10 2.29848 10 2.66667V3.33333" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				Copy report
			</button>
		</div>
	</div>

	<div class="pfm-system-status-accordions">
		<?php foreach ($grouped_status as $group_id => $group) : 
			$group_warnings = 0;
			$group_items_count = count($group['items']);
			foreach ($group['items'] as $item) {
				if (isset($item['status']) && 'error' === $item['status']) {
					$group_warnings++;
				}
			}
			$group_status_text = $group_warnings > 0 ? $group_warnings . ' warning' . ($group_warnings > 1 ? 's' : '') : $group_items_count . ' items';
			$group_status_class = $group_warnings > 0 ? 'pfm-text-warning' : 'pfm-text-muted';
		?>
		<div class="pfm-accordion-group expanded" data-group="<?php echo esc_attr($group_id); ?>">
			<div class="pfm-accordion-header">
				<div class="pfm-accordion-title">
					<div class="pfm-accordion-icon">
						<?php echo $group['icon']; // phpcs:ignore ?>
					</div>
					<h3><?php echo esc_html($group['label']); ?></h3>
				</div>
				<div class="pfm-accordion-meta">
					<span class="<?php echo esc_attr($group_status_class); ?>"><?php echo esc_html($group_status_text); ?></span>
					<svg class="pfm-chevron-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M6 4L10 8L6 12" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
			</div>
			<div class="pfm-accordion-content" style="display: block;">
				<div class="pfm-accordion-items-list">
					<?php foreach ($group['items'] as $item) : 
						$status_class = (isset($item['status']) && 'error' === $item['status']) ? 'pfm-status-error' : 'pfm-status-ok';
						$status_text = (isset($item['status']) && 'error' === $item['status']) ? 'Review' : 'OK';
					?>
					<div class="pfm-accordion-item">
						<div class="pfm-item-label"><?php echo esc_html($item['label']); ?></div>
						<div class="pfm-item-value">
							<div class="pfm-item-message"><?php echo wp_kses_post($item['message']); ?></div>
							<?php if (isset($item['sub_message']) && '' !== $item['sub_message']) : ?>
								<div class="pfm-item-sub-message <?php echo esc_attr($status_class); ?>"><?php echo esc_html($item['sub_message']); ?></div>
							<?php endif; ?>
						</div>
						<div class="pfm-item-status">
							<span class="<?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_text); ?></span>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<div class="pfm-accordion-footer">
					<button type="button" class="pfm-copy-section-btn" data-group="<?php echo esc_attr($group_id); ?>">
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M11.6667 5.25H6.41667C5.77233 5.25 5.25 5.77233 5.25 6.41667V11.6667C5.25 12.311 5.77233 12.8333 6.41667 12.8333H11.6667C12.311 12.8333 12.8333 12.311 12.8333 11.6667V6.41667C12.8333 5.77233 12.311 5.25 11.6667 5.25Z" stroke="#6B7280" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M2.91667 8.75H2.33333C2.01117 8.75 1.75 8.48883 1.75 8.16667V2.33333C1.75 2.01117 2.01117 1.75 2.33333 1.75H8.16667C8.48883 1.75 8.75 2.01117 8.75 2.33333V2.91667" stroke="#6B7280" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						Copy this section
					</button>
					<textarea class="pfm-section-copy-area" style="display:none;"><?php 
						$section_text = "=== " . $group['label'] . " ===\n";
						foreach ($group['items'] as $item) {
							if ( isset( $item['label'] ) && '' !== $item['label'] && isset( $item['message'] ) && '' !== $item['message'] ) {
								$msg = strip_tags($item['message']);
								if (isset($item['sub_message']) && '' !== $item['sub_message']) {
									$msg .= ' (' . $item['sub_message'] . ')';
								}
								$section_text .= $item['label'] . ': ' . $msg . "\n";
							}
						}
						echo esc_textarea($section_text);
					?></textarea>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	
	<textarea name="" id="rex-feed-system-status-area" style="display: none; margin-top: 10px" cols="100" rows="30"><?php echo Rex_Feed_System_Status::get_system_status_text(); //phpcs:ignore?></textarea>
</div>
