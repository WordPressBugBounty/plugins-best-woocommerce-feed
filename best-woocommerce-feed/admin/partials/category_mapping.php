<?php
/**
 * This file is responsible for displaying category mapping content.
 *
 * Renders the list of saved category maps (or an empty state), one shared
 * add / edit modal and a delete confirmation modal. The modal rows are rendered
 * once; `category-mapper.js` fills them from the `#wpfm-catmap-data` JSON when a
 * map is opened.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

$category_map = get_option( 'rex-wpfm-category-mapping' );
$category_map = is_array( $category_map ) ? array_reverse( $category_map, true ) : array();

// Merchant choices, grouped like the feed editor. Maps saved before merchants
// were stored on the map were all made for Google Shopping.
$wpfm_catmap_default_merchant = 'google';
$wpfm_catmap_merchant_groups  = array();
$wpfm_catmap_merchant_names   = array();
$wpfm_all_merchants           = Rex_Feed_Merchants::get_merchants();
$wpfm_merchant_group_labels   = array(
	'popular'        => __( 'Popular Merchants', 'rex-product-feed' ),
	'pro_merchants'  => __( 'Pro Merchants', 'rex-product-feed' ),
	'free_merchants' => __( 'All Merchants', 'rex-product-feed' ),
);
foreach ( $wpfm_merchant_group_labels as $wpfm_group_key => $wpfm_group_label ) {
	if ( empty( $wpfm_all_merchants[ $wpfm_group_key ] ) || ! is_array( $wpfm_all_merchants[ $wpfm_group_key ] ) ) {
		continue;
	}
	foreach ( $wpfm_all_merchants[ $wpfm_group_key ] as $wpfm_merchant_key => $wpfm_merchant ) {
		if ( isset( $wpfm_catmap_merchant_names[ $wpfm_merchant_key ] ) || empty( $wpfm_merchant['name'] ) ) {
			continue;
		}
		$wpfm_catmap_merchant_names[ $wpfm_merchant_key ]                = $wpfm_merchant['name'];
		$wpfm_catmap_merchant_groups[ $wpfm_group_label ][ $wpfm_merchant_key ] = $wpfm_merchant['name'];
	}
}

/**
 * Merchant slug of a saved map (legacy maps have none and were Google maps).
 *
 * @param array $map Saved map.
 * @return string
 */
$wpfm_catmap_map_merchant = static function ( $map ) use ( $wpfm_catmap_default_merchant ) {
	return ! empty( $map['merchant'] ) ? (string) $map['merchant'] : $wpfm_catmap_default_merchant;
};

/**
 * Display name of a merchant slug.
 *
 * @param string $merchant Merchant slug.
 * @return string
 */
$wpfm_catmap_merchant_name = static function ( $merchant ) use ( $wpfm_catmap_merchant_names ) {
	return isset( $wpfm_catmap_merchant_names[ $merchant ] ) ? $wpfm_catmap_merchant_names[ $merchant ] : Rex_Feed_Merchants::get_merchant_name( $merchant );
};

// Store categories that can be mapped (the editor shows one row per term).
$wpfm_catmap_term_ids = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'fields'     => 'ids',
	)
);
$wpfm_catmap_term_ids = is_wp_error( $wpfm_catmap_term_ids ) ? array() : array_map( 'strval', $wpfm_catmap_term_ids );
$wpfm_catmap_total    = count( $wpfm_catmap_term_ids );
$wpfm_catmap_term_set = array_flip( $wpfm_catmap_term_ids );

/**
 * Number of existing store categories that have a value in a map.
 *
 * @param array $values Saved values keyed by term ID.
 * @return int
 */
$wpfm_catmap_count_mapped = static function ( $values ) use ( $wpfm_catmap_term_set ) {
	$mapped = 0;
	foreach ( $values as $term_id => $value ) {
		if ( isset( $wpfm_catmap_term_set[ (string) $term_id ] ) && '' !== trim( (string) $value ) ) {
			++$mapped;
		}
	}
	return $mapped;
};

/**
 * Progress bar + "x / y mapped" text for the list table.
 *
 * @param int $mapped Mapped categories.
 * @param int $total  All categories.
 */
$wpfm_catmap_progress = static function ( $mapped, $total ) {
	$percent = $total ? (int) round( $mapped / $total * 100 ) : 0;
	?>
	<div class="wpfm-catmap-progress<?php echo $total && $mapped >= $total ? ' is-complete' : ''; ?>">
		<div class="wpfm-catmap-progress__bar" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( $total ); ?>" aria-valuenow="<?php echo esc_attr( $mapped ); ?>" aria-label="<?php esc_attr_e( 'Mapped categories', 'rex-product-feed' ); ?>">
			<span style="width: <?php echo esc_attr( $percent ); ?>%"></span>
		</div>
		<span class="wpfm-catmap-progress__text">
			<?php
			/* translators: 1: mapped store categories, 2: all store categories. */
			echo esc_html( sprintf( __( '%1$s / %2$s mapped', 'rex-product-feed' ), number_format_i18n( $mapped ), number_format_i18n( $total ) ) );
			?>
		</span>
	</div>
	<?php
};

// Saved values per map, keyed by term ID, for the edit modal.
$wpfm_catmap_data = array();
foreach ( $category_map as $wpfm_map_key => $wpfm_map ) {
	$wpfm_values = array();
	if ( ! empty( $wpfm_map['map-config'] ) && is_array( $wpfm_map['map-config'] ) ) {
		foreach ( $wpfm_map['map-config'] as $wpfm_row ) {
			if ( isset( $wpfm_row['map-key'] ) && '' !== (string) $wpfm_row['map-key'] ) {
				$wpfm_values[ (string) absint( $wpfm_row['map-key'] ) ] = isset( $wpfm_row['map-value'] ) ? (string) $wpfm_row['map-value'] : '';
			}
		}
	}
	$wpfm_catmap_data[ (string) $wpfm_map_key ] = array(
		'name'     => isset( $wpfm_map['map-name'] ) ? (string) $wpfm_map['map-name'] : '',
		'merchant' => $wpfm_catmap_map_merchant( $wpfm_map ),
		'values'   => (object) $wpfm_values,
	);
}

$wpfm_icon_plus  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M12 5v14M5 12h14"/></svg>';
$wpfm_icon_trash = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/></svg>';
$wpfm_icon_edit  = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M17 3a2.83 2.83 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$wpfm_icon_close = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M18 6 6 18M6 6l12 12"/></svg>';
$wpfm_icon_spark = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9Z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8Z"/></svg>';
$wpfm_icon_back  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6"/></svg>';
?>

<div class="wpfm-catmap" id="wpfm-catmap">
	<div class="wpfm-catmap__header">
		<h1 class="wpfm-catmap__title"><?php esc_html_e( 'Category Mapping Lists', 'rex-product-feed' ); ?></h1>
		<button type="button" class="wpfm-catmap-btn wpfm-catmap-btn--primary wpfm-catmap-open-add">
			<?php echo $wpfm_icon_plus; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			<span><?php esc_html_e( 'Add Category Mapping', 'rex-product-feed' ); ?></span>
		</button>
	</div>

	<div class="wpfm-catmap-notice" role="status" aria-live="polite" hidden></div>

	<div class="wpfm-catmap-card wpfm-catmap-empty"<?php echo $category_map ? ' hidden' : ''; ?>>
		<svg class="wpfm-catmap-empty__art" width="132" height="108" viewBox="0 0 132 108" fill="none" aria-hidden="true" focusable="false">
			<g stroke="#1d2327" stroke-width="2.4">
				<rect x="6" y="6" width="46" height="64" rx="1.5" fill="#fff"/>
				<path d="M6 7.5a1.5 1.5 0 0 1 1.5-1.5h43a1.5 1.5 0 0 1 1.5 1.5V18H6Z" fill="#8c8f94"/>
				<rect x="80" y="42" width="46" height="62" rx="1.5" fill="#fff"/>
				<path d="M80 43.5a1.5 1.5 0 0 1 1.5-1.5h43a1.5 1.5 0 0 1 1.5 1.5V54H80Z" fill="#8c8f94"/>
				<path d="M52 24h12v26h12v8h4M52 32h8v26h12v6h8M52 40h4v26h12v6h12" stroke-linejoin="round"/>
			</g>
			<g stroke="#1d2327" stroke-width="2" stroke-linecap="round">
				<path d="M11 12h8M85 48h8"/>
				<path d="M12 26h22M12 31h28M12 36h18M12 41h28M12 46h24M12 51h28M12 56h20M12 61h16"/>
				<path d="M88 62h22M88 67h30M88 72h18M88 77h30M88 82h26M88 87h30M88 92h22M88 97h30"/>
			</g>
			<g fill="#a7aaad">
				<circle cx="68" cy="5" r="1.6"/><circle cx="58" cy="12" r="1.2"/><circle cx="106" cy="31" r="1.4"/>
				<circle cx="10" cy="76" r="1.2"/><circle cx="48" cy="92" r="1.4"/><circle cx="36" cy="80" r="2"/>
			</g>
		</svg>
		<h2 class="wpfm-catmap-empty__title"><?php esc_html_e( 'Create your first category mapping', 'rex-product-feed' ); ?></h2>
		<p class="wpfm-catmap-empty__text"><?php esc_html_e( 'Match your store categories with the right marketplace categories without changing the original categories in your store.', 'rex-product-feed' ); ?></p>
		<button type="button" class="wpfm-catmap-btn wpfm-catmap-btn--outline wpfm-catmap-open-add">
			<?php echo $wpfm_icon_plus; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			<span><?php esc_html_e( 'Add Category Mapping', 'rex-product-feed' ); ?></span>
		</button>
	</div>

	<div class="wpfm-catmap-card wpfm-catmap-list"<?php echo $category_map ? '' : ' hidden'; ?>>
		<table class="wpfm-catmap-table">
			<thead>
				<tr>
					<th scope="col" class="wpfm-catmap-table__name"><?php esc_html_e( 'Category name', 'rex-product-feed' ); ?></th>
					<th scope="col" class="wpfm-catmap-table__template"><?php esc_html_e( 'Template', 'rex-product-feed' ); ?></th>
					<th scope="col" class="wpfm-catmap-table__progress"><?php esc_html_e( 'Progress', 'rex-product-feed' ); ?></th>
					<th scope="col" class="wpfm-catmap-table__action"><?php esc_html_e( 'Action', 'rex-product-feed' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $category_map as $wpfm_map_key => $wpfm_map ) : ?>
					<?php $wpfm_map_name = isset( $wpfm_map['map-name'] ) ? $wpfm_map['map-name'] : ''; ?>
					<tr data-map-key="<?php echo esc_attr( $wpfm_map_key ); ?>">
						<td class="wpfm-catmap-table__name"><span class="wpfm-catmap-table__map-name"><?php echo esc_html( $wpfm_map_name ); ?></span></td>
						<td class="wpfm-catmap-table__template"><?php echo esc_html( $wpfm_catmap_merchant_name( $wpfm_catmap_map_merchant( $wpfm_map ) ) ); ?></td>
						<td class="wpfm-catmap-table__progress"><?php $wpfm_catmap_progress( $wpfm_catmap_count_mapped( (array) $wpfm_catmap_data[ (string) $wpfm_map_key ]['values'] ), $wpfm_catmap_total ); ?></td>
						<td class="wpfm-catmap-table__action">
							<div class="wpfm-catmap-actions">
								<button type="button" class="wpfm-catmap-icon-btn wpfm-catmap-icon-btn--danger wpfm-catmap-delete"
									aria-label="<?php /* translators: %s: category map name. */ echo esc_attr( sprintf( __( 'Delete %s', 'rex-product-feed' ), $wpfm_map_name ) ); ?>"
									title="<?php esc_attr_e( 'Delete', 'rex-product-feed' ); ?>">
									<?php echo $wpfm_icon_trash; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
								</button>
								<button type="button" class="wpfm-catmap-icon-btn wpfm-catmap-edit"
									aria-label="<?php /* translators: %s: category map name. */ echo esc_attr( sprintf( __( 'Edit %s', 'rex-product-feed' ), $wpfm_map_name ) ); ?>"
									title="<?php esc_attr_e( 'Edit', 'rex-product-feed' ); ?>">
									<?php echo $wpfm_icon_edit; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
								</button>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php // Add / edit modal. ?>
	<div class="wpfm-catmap-modal wpfm-catmap-modal--editor" id="wpfm-catmap-editor" role="dialog" aria-modal="true" aria-labelledby="wpfm-catmap-editor-title" hidden>
		<div class="wpfm-catmap-modal__dialog">
			<div class="wpfm-catmap-editor__top">
				<div class="wpfm-catmap-editor__top-actions">
					<button type="button" class="wpfm-catmap-icon-btn wpfm-catmap-icon-btn--round wpfm-catmap-close" aria-label="<?php esc_attr_e( 'Close', 'rex-product-feed' ); ?>">
						<?php echo $wpfm_icon_close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
					</button>
				</div>
			</div>

			<form action="#" method="post" class="wpfm-catmap-form" novalidate>
				<div class="wpfm-catmap-fields">
					<div class="wpfm-catmap-field">
						<label for="map_name"><?php esc_html_e( 'Mapping name', 'rex-product-feed' ); ?> <span class="wpfm-catmap-required" aria-hidden="true">*</span></label>
						<input id="map_name" type="text" class="browser-default" placeholder="<?php esc_attr_e( 'Mapping name', 'rex-product-feed' ); ?>" required aria-describedby="wpfm-catmap-name-error" autocomplete="off">
						<p class="wpfm-catmap-field__error" id="wpfm-catmap-name-error" hidden><?php esc_html_e( 'Please enter a mapping name.', 'rex-product-feed' ); ?></p>
					</div>
					<div class="wpfm-catmap-field wpfm-catmap-field-merchant">
						<label for="wpfm_catmap_merchant"><?php esc_html_e( 'Merchant name', 'rex-product-feed' ); ?> <span class="wpfm-catmap-required" aria-hidden="true">*</span></label>
						<select id="wpfm_catmap_merchant" class="browser-default" required data-default="<?php echo esc_attr( $wpfm_catmap_default_merchant ); ?>">
							<?php foreach ( $wpfm_catmap_merchant_groups as $wpfm_group_label => $wpfm_group_merchants ) : ?>
								<optgroup label="<?php echo esc_attr( $wpfm_group_label ); ?>">
									<?php foreach ( $wpfm_group_merchants as $wpfm_merchant_key => $wpfm_merchant_label ) : ?>
										<option value="<?php echo esc_attr( $wpfm_merchant_key ); ?>" <?php selected( $wpfm_merchant_key, $wpfm_catmap_default_merchant ); ?>><?php echo esc_html( $wpfm_merchant_label ); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="wpfm-catmap-filterbar">
					<div class="wpfm-catmap-filters" role="group" aria-label="<?php esc_attr_e( 'Filter store categories', 'rex-product-feed' ); ?>">
						<button type="button" class="wpfm-catmap-filter" data-filter="all" aria-pressed="true"><span class="wpfm-catmap-filter__label"><?php echo esc_html__( 'All', 'rex-product-feed' ); ?></span> <span class="wpfm-catmap-filter__count">0</span></button>
						<button type="button" class="wpfm-catmap-filter" data-filter="unmapped" aria-pressed="false"><span class="wpfm-catmap-filter__label"><?php echo esc_html__( 'Unmapped', 'rex-product-feed' ); ?></span> <span class="wpfm-catmap-filter__count">0</span></button>
						<button type="button" class="wpfm-catmap-filter" data-filter="mapped" aria-pressed="false"><span class="wpfm-catmap-filter__label"><?php echo esc_html__( 'Mapped', 'rex-product-feed' ); ?></span> <span class="wpfm-catmap-filter__count">0</span></button>
					</div>
					<div class="wpfm-catmap-filterbar__actions">
						<div class="wpfm-catmap-filter-search">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
							<input type="search" class="browser-default" id="wpfm-catmap-row-search" placeholder="<?php esc_attr_e( 'Search store categories', 'rex-product-feed' ); ?>" aria-label="<?php esc_attr_e( 'Search store categories', 'rex-product-feed' ); ?>" autocomplete="off">
						</div>
						<button type="button" class="wpfm-catmap-btn wpfm-catmap-btn--suggest" id="wpfm-catmap-suggest" aria-describedby="wpfm-catmap-suggest-status">
							<?php echo $wpfm_icon_spark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
							<span class="wpfm-catmap-btn--suggest__label"><?php esc_html_e( 'Suggest categories', 'rex-product-feed' ); ?></span>
						</button>
					</div>
				</div>

				<?php // Suggestion results: status, confidence filters and the bulk fill button. Shown only after "Suggest categories". ?>
				<div class="wpfm-catmap-suggest-panel" hidden>
					<div class="wpfm-catmap-suggest-head">
						<p class="wpfm-catmap-suggest-status" id="wpfm-catmap-suggest-status" role="status" aria-live="polite" dir="auto"></p>
						<div class="wpfm-catmap-info">
							<button type="button" class="wpfm-catmap-info__btn" aria-label="<?php esc_attr_e( 'About these suggestions', 'rex-product-feed' ); ?>" aria-describedby="wpfm-catmap-suggest-info">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
							</button>
							<div class="wpfm-catmap-info__tip" id="wpfm-catmap-suggest-info" role="tooltip">
								<p class="wpfm-catmap-info__title"><?php esc_html_e( 'About these suggestions', 'rex-product-feed' ); ?></p>
								<p><?php esc_html_e( 'Smart suggestions are based on your category names, products and past mappings. Review before saving, especially Medium and Low ones.', 'rex-product-feed' ); ?></p>
							</div>
						</div>
					</div>
					<div class="wpfm-catmap-suggest-panel__body" hidden>
						<div class="wpfm-catmap-filters wpfm-catmap-filters--smart" role="group" aria-label="<?php esc_attr_e( 'Filter by suggestion confidence', 'rex-product-feed' ); ?>">
							<button type="button" class="wpfm-catmap-filter wpfm-catmap-filter--smart wpfm-catmap-filter--high" data-filter="high" aria-pressed="false" hidden><span class="wpfm-catmap-filter__label"><?php echo esc_html__( 'High Confidence', 'rex-product-feed' ); ?></span> <span class="wpfm-catmap-filter__count">0</span></button>
							<button type="button" class="wpfm-catmap-filter wpfm-catmap-filter--smart wpfm-catmap-filter--medium" data-filter="medium" aria-pressed="false" hidden><span class="wpfm-catmap-filter__label"><?php echo esc_html__( 'Medium Confidence', 'rex-product-feed' ); ?></span> <span class="wpfm-catmap-filter__count">0</span></button>
							<button type="button" class="wpfm-catmap-filter wpfm-catmap-filter--smart wpfm-catmap-filter--low" data-filter="low" aria-pressed="false" hidden><span class="wpfm-catmap-filter__label"><?php echo esc_html__( 'Low Confidence', 'rex-product-feed' ); ?></span> <span class="wpfm-catmap-filter__count">0</span></button>
						</div>
					</div>
				</div>

				<div class="wpfm-catmap-grid-card">
					<div class="wpfm-catmap-grid__head" aria-hidden="true">
						<?php for ( $wpfm_i = 0; $wpfm_i < 2; $wpfm_i++ ) : ?>
							<div class="wpfm-catmap-grid__head-half">
								<span><?php esc_html_e( 'Store Category', 'rex-product-feed' ); ?></span>
								<span class="wpfm-catmap-merchant-col">
								<?php
								/* translators: %s: merchant name, e.g. Google Shopping. */
								echo esc_html( sprintf( __( '%s Category', 'rex-product-feed' ), $wpfm_catmap_merchant_name( $wpfm_catmap_default_merchant ) ) );
								?>
								</span>
							</div>
						<?php endfor; ?>
					</div>
					<div class="categories wpfm-catmap-grid">
						<?php wpfm_hierarchical_product_category_tree( 0 ); ?>
					</div>
					<p class="wpfm-catmap-grid__empty" hidden><?php esc_html_e( 'No store categories match this filter.', 'rex-product-feed' ); ?></p>
					<div class="wpfm-catmap-pager">
						<div class="wpfm-catmap-pager__info">
							<p class="wpfm-catmap-pager__status" aria-live="polite"></p>
							<label class="wpfm-catmap-pager__size">
								<span><?php esc_html_e( 'Rows per page', 'rex-product-feed' ); ?></span>
								<select class="browser-default" id="wpfm-catmap-per-page">
									<?php foreach ( array( 12, 24, 48, 96 ) as $wpfm_size ) : ?>
										<option value="<?php echo esc_attr( $wpfm_size ); ?>"><?php echo esc_html( number_format_i18n( $wpfm_size ) ); ?></option>
									<?php endforeach; ?>
									<option value="all"><?php esc_html_e( 'All', 'rex-product-feed' ); ?></option>
								</select>
							</label>
						</div>
						<div class="wpfm-catmap-pager__buttons">
							<button type="button" class="wpfm-catmap-btn wpfm-catmap-btn--ghost wpfm-catmap-pager__prev"><?php esc_html_e( 'Previous', 'rex-product-feed' ); ?></button>
							<button type="button" class="wpfm-catmap-btn wpfm-catmap-btn--ghost wpfm-catmap-pager__next"><?php esc_html_e( 'Next', 'rex-product-feed' ); ?></button>
						</div>
					</div>
				</div>

				<div class="wpfm-catmap-editor__footer">
					<button type="button" class="wpfm-catmap-btn wpfm-catmap-btn--outline" id="update_close_mapping_cat" hidden><?php esc_html_e( 'Save & Close Tab', 'rex-product-feed' ); ?></button>
					<button type="submit" class="wpfm-catmap-btn wpfm-catmap-btn--primary" id="save_mapping_cat"><?php esc_html_e( 'Save Category Mapping', 'rex-product-feed' ); ?></button>
				</div>
			</form>
		</div>
	</div>

	<?php // Delete confirmation modal. ?>
	<div class="wpfm-catmap-modal wpfm-catmap-modal--confirm" id="wpfm-catmap-delete" role="alertdialog" aria-modal="true" aria-labelledby="wpfm-catmap-delete-title" aria-describedby="wpfm-catmap-delete-text" hidden>
		<div class="wpfm-catmap-modal__dialog">
			<div class="wpfm-catmap-confirm__head">
				<h2 id="wpfm-catmap-delete-title"><?php esc_html_e( 'Delete Category Mapping', 'rex-product-feed' ); ?></h2>
				<button type="button" class="wpfm-catmap-icon-btn wpfm-catmap-close" aria-label="<?php esc_attr_e( 'Close', 'rex-product-feed' ); ?>">
					<?php echo $wpfm_icon_close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
				</button>
			</div>
			<p class="wpfm-catmap-confirm__body" id="wpfm-catmap-delete-text"></p>
			<div class="wpfm-catmap-confirm__foot">
				<button type="button" class="wpfm-catmap-btn wpfm-catmap-btn--ghost wpfm-catmap-close"><?php esc_html_e( 'Cancel', 'rex-product-feed' ); ?></button>
				<button type="button" class="wpfm-catmap-btn wpfm-catmap-btn--danger wpfm-catmap-delete-confirm"><?php esc_html_e( 'Delete', 'rex-product-feed' ); ?></button>
			</div>
		</div>
	</div>

	<script type="application/json" id="wpfm-catmap-data"><?php echo wp_json_encode( (object) $wpfm_catmap_data, JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
</div>
