<?php
/**
 * This file is responsible for displaying merchant dropdown section
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

use RexTheme\Hotline\ExchangeRate;

$icon = 'icon/icon-svg/icon-question.php';
?>

<div class="rex_feed_config_div rex-feed-merchant">
	<label for="<?php echo esc_attr( $this->prefix ) . 'merchant'; ?>"><?php esc_html_e( 'Feed Merchant', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon; ?>
			<p><?php echo __( 'Select your preferred merchant from the list', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$class    = 'rex-merchant-list-select2';
	$name     = $this->prefix . 'merchant';
	$selected = '' !== $saved_merchant ? $saved_merchant : '-1';
	Rex_Feed_Merchants::render_merchant_dropdown( $class, $name, $name, $selected );
	?>
</div>

<div class="rex_feed_config_div rex-feed-feed-format">
	<label for="<?php echo esc_attr( $this->prefix ) . 'feed_format'; ?>"><?php esc_html_e( 'Feed Type', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon; ?>
			<p><?php echo __( 'Select your feed file type', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<select name="<?php echo esc_attr( $this->prefix ) . 'feed_format'; ?>" id="<?php echo esc_attr( $this->prefix ) . 'feed_format'; ?>" class="<?php echo esc_attr( $this->prefix ) . 'feed-format'; ?>">
		<option value="xml" <?php echo 'xml' === $file_format ? 'selected' : ''; ?> ><?php echo esc_html__( 'XML', 'rex-product-feed' ); ?></option>
		<option value="text" <?php echo 'text' === $file_format ? 'selected' : ''; ?> ><?php echo esc_html__( 'TEXT', 'rex-product-feed' ); ?></option>
		<option value="csv" <?php echo 'csv' === $file_format ? 'selected' : ''; ?> ><?php echo esc_html__( 'CSV', 'rex-product-feed' ); ?></option>
		<option value="tsv" <?php echo 'tsv' === $file_format ? 'selected' : ''; ?> ><?php echo esc_html__( 'TSV', 'rex-product-feed' ); ?></option>
		<option value="rss" <?php echo 'rss' === $file_format ? 'selected' : ''; ?> ><?php echo esc_html__( 'RSS', 'rex-product-feed' ); ?></option>
	</select>
</div>

<div class="rex_feed_config_div rex-feed-feed-separator" style="display: none">
	<label for="
	<?php
	echo esc_attr( $this->prefix ) . 'separator';
	?>
	"><?php esc_html_e( 'Separator', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require WPFM_PLUGIN_ASSETS_FOLDER_PATH . $icon; ?>
			<p><?php echo __( 'Select separator', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$saved_value        = get_post_meta( get_the_ID(), '_rex_feed_separator', true );
	$saved_value        = $saved_value ?: get_post_meta( get_the_ID(), 'rex_feed_separator', true );
	$saved_value        = $saved_value ?: 'comma';
	$checked_comma      = 'comma' === $saved_value ? ' selected' : '';
	$checked_semi_colon = 'semi_colon' === $saved_value ? ' selected' : '';
	$checked_pipe       = 'pipe' === $saved_value ? ' selected' : '';
	?>
	<select name="<?php echo esc_attr( $this->prefix ) . 'separator'; ?>" id="<?php echo esc_attr( $this->prefix ) . 'separator'; ?>" class="">
		<option value="comma" <?php echo esc_attr( $checked_comma ); ?>><?php echo esc_html__( 'Comma (,)', 'rex-product-feed' ); ?></option>
		<option value="semi_colon" <?php echo esc_attr( $checked_semi_colon ); ?>><?php echo esc_html__( 'Semi-colon (;)', 'rex-product-feed' ); ?></option>
		<option value="pipe" <?php echo esc_attr( $checked_pipe ); ?>><?php echo esc_html__( 'Pipe (|)', 'rex-product-feed' ); ?></option>
	</select>
</div>
<?php
$style = '';
if ( 'custom' !== $saved_merchant || ( 'custom' === $saved_merchant && 'xml' !== $file_format ) ) {
	$style = ' style="display: none"';
}
?>

<!-- New include and exclude header -->
<div class="rex_feed_config_div rex_feed_custom_wrapper" <?php echo wp_kses( $style, wp_kses_allowed_html( 'post' ) ); ?>>
	<label for="<?php echo esc_attr( $this->prefix ) . 'custom_xml_header'; ?>">
		<?php echo __( 'XML Header', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php echo __( 'Include or exclude XML file header attributes (title, link, description, datetime)', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$saved_value = get_post_meta( get_the_ID(), '_rex_feed_custom_xml_header', true );
	?>
	<select name="<?php echo esc_attr( $this->prefix ) . 'custom_xml_header'; ?>" id="<?php echo esc_attr( $this->prefix ) . 'custom_xml_header'; ?>" class="<?php echo esc_attr( $this->prefix ) . 'custom-xml-header'; ?>">
		<option value="include" <?php echo 'include' === $saved_value ? 'selected' : ''; ?> ><?php echo esc_html__( 'Include', 'rex-product-feed' ); ?></option>
		<option value="exclude" <?php echo 'exclude' === $saved_value ? 'selected' : ''; ?> ><?php echo esc_html__( 'Exclude', 'rex-product-feed' ); ?></option>
	</select>
</div>

<div class="rex_feed_config_div rex_feed_custom_items_wrapper" <?php echo wp_kses( $style, wp_kses_allowed_html( 'post' ) ); ?>>
	<label for="<?php echo esc_attr( $this->prefix ) . 'custom_items_wrapper'; ?>">
		<?php echo __( 'Items Wrapper', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php echo __( 'Put custom xml attribute items wrapper name. Keep blank incase of using default structure.', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$saved_value = get_post_meta( get_the_ID(), '_rex_feed_custom_items_wrapper', true );
	?>
	<input type="text" name="rex_feed_custom_items_wrapper" id="<?php echo esc_attr( $this->prefix ) . 'custom_items_wrapper'; ?>" value="<?php echo esc_attr( $saved_value ); ?>">
</div>

<div class="rex_feed_config_div rex_feed_custom_wrapper" <?php echo wp_kses( $style, wp_kses_allowed_html( 'post' ) ); ?>>
	<label for="<?php echo esc_attr( $this->prefix ) . 'custom_wrapper_el'; ?>">
		<?php echo __( 'Wrapper Element', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php echo __( 'Put custom xml attribute item wrapper_el name. Keep blank incase of using default structure.', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$saved_value = get_post_meta( get_the_ID(), '_rex_feed_custom_wrapper_el', true );
	?>
	<input type="text" name="rex_feed_custom_wrapper_el" id="<?php echo esc_attr( $this->prefix ) . 'custom_wrapper_el'; ?>" value="<?php echo esc_attr( $saved_value ); ?>">
</div>

<div class="rex_feed_config_div rex_feed_custom_wrapper" <?php echo wp_kses( $style, wp_kses_allowed_html( 'post' ) ); ?>>
	<label for="<?php echo esc_attr( $this->prefix ) . 'custom_wrapper'; ?>">
		<?php echo __( 'Item Wrapper', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php echo __( 'Put custom xml attribute item wrapper name. Keep blank incase of using default structure.', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$saved_value = get_post_meta( get_the_ID(), '_rex_feed_custom_wrapper', true );
	?>
	<input type="text" name="rex_feed_custom_wrapper" id="<?php echo esc_attr( $this->prefix ) . 'custom_wrapper'; ?>" value="<?php echo esc_attr( $saved_value ); ?>">
</div>

<?php
$style = '';
if ( 'yandex' !== $saved_merchant || 'xml' !== $file_format ) {
	$style = ' style="display: none"';
}
?>

<div class="rex_feed_config_div rex_feed_yandex_old_price" <?php echo wp_kses( $style, wp_kses_allowed_html( 'post' ) ); ?>>
	<label for="<?php echo esc_attr( $this->prefix ) . 'yandex_old_price'; ?>">
		<?php echo __( 'Old Price', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php echo __( 'Choose option if you want to include/exclude the old price attribute from the feed if it is less/equal than/to the current price.', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$saved_value = get_post_meta( get_the_ID(), '_rex_feed_yandex_old_price', true );
	?>
	<select name="<?php echo esc_attr( $this->prefix ) . 'yandex_old_price'; ?>" id="<?php echo esc_attr( $this->prefix ) . 'yandex_old_price'; ?>" class="<?php echo esc_attr( $this->prefix ) . 'yandex_old_price'; ?>">
		<option value="include" <?php echo 'include' === $saved_value ? 'selected' : ''; ?> ><?php echo __( 'Include', 'rex-product-feed' ); ?></option>
		<option value="exclude" <?php echo 'exclude' === $saved_value ? 'selected' : ''; ?> ><?php echo __( 'Exclude', 'rex-product-feed' ); ?></option>
	</select>
</div>

<div class="rex_feed_config_div rex_feed_yandex_company_name" <?php echo wp_kses( $style, wp_kses_allowed_html( 'post' ) ); ?>>
	<label for="<?php echo esc_attr( $this->prefix ) . 'yandex_company_name'; ?>">
		<?php echo __( 'Company Name', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php echo __( 'Put your company name to include in the xml header section with company tag.', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php $saved_value = get_post_meta( get_the_ID(), '_rex_feed_yandex_company_name', true ); ?>
	<input type="text" name="rex_feed_yandex_company_name" id="<?php echo esc_attr( $this->prefix ) . 'yandex_company_name'; ?>" value="<?php echo esc_attr( $saved_value ); ?>">
</div>

<?php
$style = '';
if ( 'hotline' !== $saved_merchant || 'xml' !== $file_format ) {
	$style = ' style="display: none"';
}
?>
<div class="rex_feed_config_div rex_feed_hotline_content" <?php echo wp_kses( $style, wp_kses_allowed_html( 'post' ) ); ?>>
	<label for="<?php echo esc_attr( $this->prefix ) . 'hotline_firm_id'; ?>">
		<?php echo __( 'Firm ID', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php echo __( 'Unique code of the firm.', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$saved_value = get_post_meta( get_the_ID(), '_rex_feed_hotline_firm_id', true );
	?>
	<input type="text" name="rex_feed_hotline_firm_id" id="<?php echo esc_attr( $this->prefix ) . 'hotline_firm_id'; ?>" value="<?php echo esc_attr( $saved_value ); ?>">
</div>

<div class="rex_feed_config_div rex_feed_hotline_content" <?php echo wp_kses( $style, wp_kses_allowed_html( 'post' ) ); ?>>
	<label for="<?php echo esc_attr( $this->prefix ) . 'hotline_firm_name'; ?>">
		<?php echo __( 'Firm Name', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php echo __( 'Your hotline store name.', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$saved_value = get_post_meta( get_the_ID(), '_rex_feed_hotline_firm_name', true );
	?>
	<input type="text" name="rex_feed_hotline_firm_name" id="<?php echo esc_attr( $this->prefix ) . 'hotline_firm_name'; ?>" value="<?php echo esc_attr( $saved_value ); ?>">
</div>

<div class="rex_feed_config_div rex_feed_hotline_content" <?php echo wp_kses( $style, wp_kses_allowed_html( 'post' ) ); ?>>
	<label for="<?php echo esc_attr( $this->prefix ) . 'hotline_exchange_rate'; ?>">
		<?php echo __( 'Exchange Rate', 'rex-product-feed' ); ?>
		<span class="rex_feed-tooltip">
			<?php require plugin_dir_path( __FILE__ ) . '../assets/icon/icon-svg/icon-question.php'; ?>
			<p><?php echo __( 'Dollar rate, specify the real value if the prices in the product feed are given in dollars. If the prices are given in hryvnias, you can leave it empty.', 'rex-product-feed' ); ?></p>
		</span>
	</label>
	<?php
	$wc_currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';
	$saved_value = get_post_meta( get_the_ID(), '_rex_feed_hotline_exchange_rate', true ) ?: ExchangeRate::get_exchange_rate( $wc_currency );
	?>
	<input type="text" name="rex_feed_hotline_exchange_rate" id="<?php echo esc_attr( $this->prefix ) . 'hotline_exchange_rate'; ?>" value="<?php echo esc_attr( $saved_value ); ?>">
</div>