<?php
/**
 * This file is responsible for displaying category mapping content
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

$category_map = get_option( 'rex-wpfm-category-mapping' );
$db_version   = get_option( 'rex_wpfm_db_version' );
require_once plugin_dir_path( __FILE__ ) . 'loading-spinner.php';
?>

<div class="row">
	<div class="col s12 m12">
		<div class="category-mapper-wrapper card ">
			<div class="cat-mapper-header">
				<h5><?php echo esc_html__( 'Add New Category Map', 'rex-product-feed' ); ?></h5>
			</div>

			<div class="mapper-name">
				<p><?php echo esc_html__( 'Mapper Name', 'rex-product-feed' ); ?></p>
				<input id="map_name" type="text" name="mapper_name">
			</div>


			<form action="#" method="post" class="add_cat_map">

				<div class="widefat fixed cat-map highlight" id="cat-map">
					<div class="categories">
						<?php wpfm_hierarchical_product_category_tree( 0 ); ?>
					</div>
				</div>
				<div class="cat-map-actions">
					<button type="submit" class="waves-effect waves-light btn-large green" id="save_mapping_cat"><i class="fa fa-floppy-o"></i> <?php echo esc_html__( 'Save', 'rex-product-feed' ); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="row">
	<div class="col s12 m12">
		<div class="rex-accordion existing-category-maps">
			<?php if ( $category_map ) { ?>
				<?php
				foreach ( array_reverse( $category_map ) as $key => $value ) {
					?>
					<div class="acordion-item">
						<h6><a href="#" class="mapper_name_update" data-id="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $value['map-name'] ); ?></a></h6>
						<div class="inner" style="display: none;">
							<form action="" method="post" class="update_cat_map">
								<div class="widefat fixed cat-map highlight" id="cat-map">
									<div class="categories">
										<?php
										$separator = '';
										$sub_cat   = array();
										wpfm_hierarchical_product_category_tree( 0, $value['map-config'] );
										?>
									</div>
								</div>
								<div class="cat-map-actions">
									<button type="submit" class="waves-effect waves-light btn-large green" id="update_mapping_cat"><i class="fa fa-pencil-square-o"></i> <?php echo esc_html__( 'Update', 'rex-product-feed' ); ?></button>
									<button type="submit" class="waves-effect waves-light btn-large red" id="delete_mapping_cat"><i class="fa fa-trash-o"></i> <?php echo esc_html__( 'Delete', 'rex-product-feed' ); ?></button>
								</div>
							</form>
						</div>
					</div>
					<?php
				}
			}
			else {
				if ( $db_version >= 3 ) {
					?>
				<div class="info-msg">
					<i class="fa fa-info-circle"></i>
					<?php echo esc_html__( 'Please update WPFM database', 'rex-product-feed' ); ?>
				</div>
					<?php
				}
			}
			?>
		</div>
	</div>
</div>
