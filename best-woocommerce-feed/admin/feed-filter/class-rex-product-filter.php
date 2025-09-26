<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://rextheme.com
 * @since      1.1.10
 *
 * @package    Rex_Product_Filter
 * @subpackage Rex_Product_Feed/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines all the Filter for Products
 *
 * @package    Rex_Product_Filter
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Product_Filter {


    /**
     * The Feed Attributes.
     *
     * @since    1.1.10
     * @access   protected
     * @var      Rex_Product_Filter    $product_meta_keys    Feed Attributes.
     */
    protected $product_meta_keys;

    /**
     * The Feed Attributes.
     *
     * @access   protected
     * @var      Rex_Product_Filter    $product_rule_meta_keys    Feed Attributes.
     */
    protected $product_rule_meta_keys;


    /**
     * The Feed Condition.
     *
     * @since    1.1.10
     * @access   protected
     * @var      Rex_Product_Filter    $condition    Feed Condition.
     */
    protected $condition;


    /**
     * The Feed Condition Then.
     *
     * @since    1.1.10
     * @access   protected
     * @var      Rex_Product_Filter    $then    Feed Condition Then.
     */
    protected $then;


    /**
     * The Feed Rules
     *
     * @since    3.5
     * @access   protected
     * @var      Rex_Product_Filter    $rules    Feed Condition Then.
     */
    protected $rules;


    /**
     * The Feed Filter Mappings Attributes and associated value and other constraints.
     *
     * @since    1.1.10
     * @access   protected
     * @var      Rex_Product_Filter    $filter_mappings    Feed Filter mapping for template generation.
     */
    protected $filter_mappings;

    /**
     * The Product Object
     *
     * @since    1.1.10
     * @access   protected
     * @var      Rex_Product_Filter    $product    Product Object.
     */
    protected $product;

    /**
     * Term table count
     *
     * @since    7.3.1
     * @access   protected
     * @var      int    $term_table_count    Term table count.
     */
    protected static $term_table_count;

    /**
     * Meta table count
     *
     * @since    7.3.1
     * @access   protected
     * @var      int    $meta_table_count    Meta table count.
     */
    protected static $meta_table_count;

    protected static $is_shipping_cost_filter = false;


    /**
     * Set the filter and condition.
     *
     * @since    1.1.10
     * @param bool $feed_filter
     */
    public function __construct( $feed_filter = false ){
        $this->init_feed_filter_mappings( $feed_filter );
        $this->init_product_meta_keys();
        $this->init_product_filter_condition();
        $this->init_product_filter_then();
    }


    /**
     * Initialize Filter from feed post_meta.
     *
     * @since    1.1.10
     * @param string $feed_filter The Conditions Of Feeds
     */
    protected function init_feed_filter_mappings( $feed_filter ){
        if ( !empty($feed_filter) ) {
            $this->filter_mappings = $feed_filter;
        }else {
            $this->init_default_filter_mappings();
        }
    }

    /**
     * Get Filter Attributes
     * @return array $attributes
     */
    protected function getFilterAttribute() {
        $attributes = [
            'Primary Attributes' => [
                'id'                    => 'Product Id',
                'title'                 => 'Product Title',
                'description'           => 'Product Description',
                'short_description'     => 'Product Short Description',
                'total_sales'           => 'Total Sales',
                'featured_image'        => 'Featured Image',
                'product_cats'          => 'Product Category',
                'product_tags'          => 'Product Tag',
                'sku'                   => 'SKU',
                'availability'          => 'Availability',
                'quantity'              => 'Quantity',
                'price'                 => 'Regular Price',
                'sale_price'            => 'Sale price',
                'weight'                => 'Weight',
                'width'                 => 'Width',
                'height'                => 'Height',
                'length'                => 'Length',
                'rating_total'          => 'Total Rating',
                'rating_average'        => 'Average Rating',
                'sale_price_dates_from' => 'Sale Start Date',
                'sale_price_dates_to'   => 'Sale End Date',
                'manufacturer'          => 'Manufacturer',
                'post_date_gmt'         => 'Product Creation Date',
                'post_modified_gmt'     => 'Product Last Modified Date',
                'gtin'                  => 'GTIN',
                'product_shipping_class'        => 'Shipping Class',
                'shipping_cost'         => 'Shipping Cost',
                'visibility'            => 'Visibility',
                'catalog_visibility'   => 'Catalog Visibility',
                'downloadable'          => 'Downloadable Product',
                'virtual'               => 'Virtual Product',
            ]
        ];

        if( rexfeed_is_woocommerce_brand_active() ) {
            $attributes[ 'Primary Attributes' ][ 'product_brands' ] = 'Product Brand';
        }

        if ( 'yes' === get_option( 'woocommerce_calc_taxes', 'no' ) ) {
            $attributes[ 'Primary Attributes' ][ 'tax_class' ] = 'Tax Class';
            $attributes['Primary Attributes' ][ 'tax_status' ] = 'Tax Status';
        }

        if ( 'yes' === get_option( 'woocommerce_manage_stock', 'no' ) ) {
            $attributes[ 'Primary Attributes' ][ 'backorders' ] = 'Backorder option';
            $attributes[ 'Primary Attributes' ][ 'low_stock_threshold' ] = 'Low stock threshold';
        }

        return $attributes;
    }


    /**
     * Initialize Product Meta Attributes
     *
     * @since    1.1.10
     */
    protected function init_product_meta_keys() {
        $this->product_meta_keys = $this->getFilterAttribute();
        $product_attributes      = self::get_product_attributes();
        $pr_var_attributes       = self::get_product_variation_attributes();

        if ( is_array( $product_attributes ) && !empty( $product_attributes ) ) {
            $this->product_meta_keys = array_merge( $this->product_meta_keys, $product_attributes );
        }
        if ( is_array( $pr_var_attributes ) && !empty( $pr_var_attributes ) ) {
            $this->product_meta_keys = array_merge( $this->product_meta_keys, $pr_var_attributes );
        }

        if ( defined( 'ACF_VERSION' ) ) {
            $acf_attributes          = Rex_Feed_Attributes::get_acf_fields();
            $this->product_meta_keys = array_merge( $this->product_meta_keys, $acf_attributes );
        }

        $this->product_rule_meta_keys = Rex_Feed_Attributes::get_attributes();

        if( isset( $this->product_rule_meta_keys[ 'Attributes Separator' ] ) ) {
            unset( $this->product_rule_meta_keys[ 'Attributes Separator' ] );
        }
    }


    /**
     * Initialize Product Filter Condition
     *
     * @since    1.1.10
     */
    protected function init_product_filter_condition(){
        $this->condition = array(
            '' => array(
                'contain'                  => __('Contains', 'rex-product-feed' ),
                'dn_contain'               => __('Does not contain', 'rex-product-feed' ),
                'equal_to'                 => __('Is equal to', 'rex-product-feed' ),
                'nequal_to'                => __('Is not equal to', 'rex-product-feed' ),
                'greater_than'             => __('Greater than', 'rex-product-feed' ),
                'greater_than_equal'       => __('Greater than or equal to', 'rex-product-feed' ),
                'less_than'                => __('Less than', 'rex-product-feed' ),
                'less_than_equal'          => __('Less than or equal to', 'rex-product-feed' ),
                'is_empty'                 => __('Is empty'),
                'is_not_empty'             => __('Is not empty')
            )
        );
    }


    /**
     * Initialize Product Filter Then
     *
     * @since    1.1.10
     */
    protected function init_product_filter_then() {
        $this->then = [
            '' => [
                'inc' => __('Include', 'rex-product-feed' ),
                'exc' => __('Exclude', 'rex-product-feed' )
            ]
        ];
    }


    /**
     * Initialize Default Filter Mappings with Attributes.
     *
     * @since    1.1.10
     */
    protected function init_default_filter_mappings(){
        $this->filter_mappings = array(
            array(
                array(
                    'if'        => '',
                    'condition' => '',
                    'value'     => '',
                    'then'      => 'exclude',
                )
            )
        );
    }


    /**
     * Return the filter_mappings
     *
     * @since    1.1.10
     */
    public function get_filter_mappings(){
        return $this->filter_mappings;
    }


    /**
     * Print attributes as select dropdown.
     *
     * @since    1.0.0
     * @param $key
     * @param $name
     * @param string $selected
     */
    public function print_select_dropdown( $key1, $key2, $name, $name_prefix = 'ff', $selected = '', $class = '', $style = '' ){
        if ( $name === 'if' ) {
            $items = $this->product_meta_keys;
        }
        elseif ( $name === 'condition' ) {
            $items = apply_filters( 'rex_feed_filter_conditions', $this->condition, $name);
        }
        elseif ( $name === 'then' ) {
            $items = $this->then;
        }
        else{
            return;
        }

        echo '<select class="' .esc_attr( $class ). '" name="'.esc_attr( $name_prefix ).'['.esc_attr( $key1 ).']['.esc_attr( $key2 ).'][' . esc_attr( $name ) . ']" style="' . esc_attr( $style ) . '">';
        if( 'rules' === $name) {
            echo "<option value='or'>".__( 'Please Select', 'rex-product-feed' )."</option>";
        }
        else {
            echo "<option value=''>".__( 'Please Select', 'rex-product-feed' )."</option>";
        }

        foreach ($items as $groupLabel => $group) {
            if ( !empty($groupLabel)) {
                echo "<optgroup label='".esc_html($groupLabel)."'>";
            }

            foreach ($group as $key => $item) {
                if ( $selected == $key ) {
                    echo "<option value='".esc_attr($key)."' selected='selected'>".esc_html($item)."</option>";
                }else{
                    echo "<option value='".esc_attr($key)."'>".esc_html($item)."</option>";
                }
            }

            if ( !empty($groupLabel)) {
                echo "</optgroup>";
            }
        }

        echo "</select>";
    }


    /**
     * Print Prefix input.
     *
     * @since    1.0.0
     * @param $key
     * @param string $name
     * @param string $val
     */
    public function print_input( $key1, $key2, $name, $name_prefix = 'ff', $val = '', $class = '', $style = '', $type = 'text' ){
        echo '<input type="'. esc_attr( $type ) .'" class="'. esc_attr( $class ) .'" name="'.esc_html( $name_prefix ).'['.esc_attr( $key1 ).']['.esc_attr( $key2 ).'][' . esc_attr( $name ) . ']" value="' . esc_attr( $val ) . '" style="' . esc_attr( $style ) . '">';
    }

    /**
     * Create custom where query with custom filters
     *
     * @param $filter_mappings
     * @return array
     * @since 1.0.0
     */
    public static function get_custom_filter_where_query( $filter_mappings ) {
        $where                  = '';
        $inner_where            = '';
        $term_exists            = false;
		$meta_keys			    = [];
        self::$meta_table_count = 0;
        self::$term_table_count = 0;

        foreach( $filter_mappings as $key1 => $filters ) {
            foreach( $filters as $key2 => $filter ) {
                if( !empty( $filter[ 'if' ] ) && !empty( $filter[ 'then' ] ) && !empty( $filter[ 'condition' ] ) && isset( $filter[ 'value' ] ) ) {
                    $if        = self::get_column_name( $filter[ 'if' ] );
                    $then      = htmlspecialchars( $filter[ 'then' ] );
                    $condition = htmlspecialchars( $filter[ 'condition' ] );
                    $value     = htmlspecialchars( $filter[ 'value' ] );

                    if ( self::is_unix_date( $if ) ) {
                        $value = strtotime( $value );
                    }
                    elseif ( Rex_Product_Feed_Actions::is_acf_field_type( $if, 'date_time_picker' )
                        || Rex_Product_Feed_Actions::is_acf_field_type( $if, 'date_picker' )
                        || Rex_Product_Feed_Actions::is_acf_field_type( $if, 'time_picker' )
                    ) {
                        $value = date( 'Y-m-d H:i:s', strtotime( $value ) );
                    }

                    $prefix = self::get_method_prefix( $filter[ 'if' ] );
;
                    if( 'postterm_' === $prefix ) {
                        $acf_attributes = [];
                        if ( defined( 'ACF_VERSION' ) ) {
                            $acf_attributes = Rex_Feed_Attributes::get_acf_fields();
                        }
                        self::$term_table_count++;

                        $column   = $filter[ 'if' ];

                        $shipping_properties = ['product_shipping_class', 'shipping_cost'];

                        $taxonomy = preg_match( '/^pa_/i', $column ) || in_array($column, $shipping_properties, true) || ( !empty( $acf_attributes[ 'ACF Taxonomies' ] ) && array_key_exists( $column, $acf_attributes[ 'ACF Taxonomies' ] ) ) ? $column : substr( $column, 0, -1 );

                        $value    = self::get_term_id( $value, $taxonomy, $condition, $then );

                        if( !$value && !in_array($column, $shipping_properties, true)) {
                            continue;
                        }

                        $term_exists = true;

                    } elseif ( 'postmeta_' === $prefix ) {
	                    self::$meta_table_count ++;
	                    $if          = preg_replace( '/^va_pa_/i', 'attribute_pa_', $if );
	                    $meta_keys[] = $if;
                    }

                    $function = "{$prefix}{$condition}";
                    if( method_exists( __CLASS__, $function ) ) {
                        $temp_where = self::$function( $if, $value, $then );
                        if( $temp_where ) {
                            $inner_where .= $key2 > 0 && $inner_where ? " AND ({$temp_where})" : "({$temp_where})";
                        }
                    }
                }
            }

            if( $inner_where ) {
                $where .= $key1 > 0 && $where ? " OR ({$inner_where})" : "({$inner_where})";
                $inner_where = '';
            }
        }

        return [
            'where'       => $where,
            'term_exists' => $term_exists,
            'meta_keys'   => $meta_keys
        ];
    }

    /**
     * Checks if a given column is a date-related column.
     *
     * @param string $column The column name to check.
     *
     * @return bool True if the column is a date-related column, false otherwise.
     * @since 7.3.28
     */
    private static function is_unix_date( $column ) {
        return in_array( $column, [
            '_sale_price_dates_from',
            '_sale_price_dates_to',
            'sale_price_dates_from',
            'sale_price_dates_to'
        ] );
    }

    /**
     * Checks if a given column is a date-related column.
     *
     * @param string $column The column name to check.
     *
     * @return bool True if the column is a date-related column, false otherwise.
     * @since 7.3.28
     */
    public static function is_date_column( $column ) {
        return in_array( $column, [
            'sale_price_dates_from',
            'sale_price_dates_to',
            '_sale_price_dates_from',
            '_sale_price_dates_to',
            'post_date_gmt',
            'post_modified_gmt',
        ] );
    }

    /**
     * Gets the method prefix for a column.
     *
     * @param string $column Column name.
     *
     * @return string Method prefix.
     * @since 7.3.0
     */
    private static function get_method_prefix( $column ) {
        $meta_table_attr = [
            'manufacturer',
            'featured_image',
            'availability',
            'sku',
            'quantity',
            'price',
            'sale_price',
            'weight',
            'width',
            'height',
            'length',
            'rating_total',
            'rating_average',
            'sale_price_dates_from',
            'sale_price_dates_to',
            'total_sales',
            'gtin',
            'tax',
            'visibility',
            'tax_status',
            'tax_class',
            'backorders',
            'low_stock_threshold',
            'catalog_visibility',
            'downloadable',
            'virtual',
        ];
        $term_rel_table_attr = [
            'product_cats',
            'product_tags',
            'product_brands',
            'product_shipping_class',
            'shipping_cost'
        ];

        $acf_attributes = [];
        if ( defined( 'ACF_VERSION' ) ) {
            $acf_attributes = Rex_Feed_Attributes::get_acf_fields();
        }

        elseif( in_array( $column, $meta_table_attr, true )
            || ( !empty( $acf_attributes[ 'ACF Attributes' ] ) && array_key_exists( $column, $acf_attributes[ 'ACF Attributes' ] ) )
            || preg_match( '/^va_pa_/i', $column )
        ) {
            return 'postmeta_';
        }
        elseif( in_array( $column, $term_rel_table_attr, true )
            || preg_match( '/^pa_/i', $column )
            || ( !empty( $acf_attributes[ 'ACF Taxonomies' ] ) && array_key_exists( $column, $acf_attributes[ 'ACF Taxonomies' ] ) )
        ) {
            return 'postterm_';
        }
        return 'post_';
    }

    /**
     * Get database column name
     *
     * @param $column
     * @return mixed|string
     * @since 7.3.0
     */
    private static function get_column_name( $column ) {
        if( preg_match( '/^pa_/i', $column ) ) {
            return 'term_taxonomy_id';
        }

        if ( defined( 'ACF_VERSION' ) ) {
            $acf_attributes = Rex_Feed_Attributes::get_acf_fields();
            if ( !empty( $acf_attributes[ 'ACF Taxonomies' ] ) && array_key_exists( $column, $acf_attributes[ 'ACF Taxonomies' ] ) ) {
                return 'term_taxonomy_id';
            }
        }

        switch( $column ) {
            case 'id':
                return 'ID';
            case 'title':
                return 'post_title';
            case 'description':
                return 'post_content';
            case 'short_description':
                return 'post_excerpt';
            case 'manufacturer':
                return '_wpfm_product_brand';
            case 'featured_image':
                return '_thumbnail_id';
            case 'availability':
                return '_stock_status';
            case 'sku':
                return '_sku';
            case 'quantity':
                return '_stock';
            case 'price':
                return '_regular_price';
            case 'sale_price':
                return '_sale_price';
            case 'weight':
                return '_weight';
            case 'width':
                return '_width';
            case 'height':
                return '_height';
            case 'length':
                return '_length';
            case 'rating_total':
                return '_wc_review_count';
            case 'rating_average':
                return '_wc_average_rating';
            case 'sale_price_dates_from':
                return '_sale_price_dates_from';
            case 'sale_price_dates_to':
                return '_sale_price_dates_to';
            case 'product_cats':
            case 'product_tags':
            case 'product_brands':
            case 'shipping_cost':
            case 'product_shipping_class':
                return 'term_taxonomy_id';
            case 'gtin':
                return '_global_unique_id';
            case 'tax_class':
                return '_tax_class';
            case 'visibility':
                return '_visibility';
            case 'tax_status':
                return '_tax_status';
            case 'backorders':
                return '_backorders';
            case 'low_stock_threshold':
                return '_low_stock_amount';
             case 'catalog_visibility':
                return '_catalog_visibility';
            case 'downloadable':
                return '_downloadable';
            case 'virtual':
                return '_virtual';
            default:
                return $column;
        }
    }

    /**
     * Get term id by slug or name
     *
     * @param $slug
     * @param $taxonomy
     * @return mixed
     */
    private static function get_term_id( $slug, $taxonomy, $condition, $then ) {
        $empty_array = ['is_empty', 'is_not_empty'];
        if('product_shipping_class' === $taxonomy && !in_array($condition, $empty_array, true)){
            $term = get_term_by( 'name', $slug, $taxonomy );
        }else if('product_shipping_class' === $taxonomy && in_array($condition, $empty_array, true)){
            return self::get_product_ids_on_shipping_class($condition, $then);
        }else if('shipping_cost' === $taxonomy){
            return self::handle_shipping_cost_filter_by_terms($slug, $condition, $then);
        }else{
            $term = get_term_by( 'slug', $slug, $taxonomy );
        }
        return !empty( $term->term_id ) ? $term->term_id : null;
    }

    /**
     * Helper method to create custom where query for value `Contains` in `wp_post` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function post_contain( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? 'NOT LIKE' : 'LIKE';
        return "{$wpdb->posts}.{$column} {$op} '%{$wpdb->esc_like( $value )}%'";
    }

    /**
     * Helper method to create custom where query for value `Does not contain` in `wp_post` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function post_dn_contain( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? 'LIKE' : 'NOT LIKE';
        return "{$wpdb->posts}.{$column} {$op} '%{$wpdb->esc_like( $value )}%'";
    }

    /**
     * Helper method to create custom where query for value `Is equal to` in `wp_post` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function post_equal_to( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '<>' : '=';
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        return "{$wpdb->posts}.{$column} {$op} {$value}";
    }

    /**
     * Helper method to create custom where query for value `Is not equal to` in `wp_post` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function post_nequal_to( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '=' : '<>';
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        return "{$wpdb->posts}.{$column} {$op} {$value}";
    }

    /**
     * Helper method to create custom where query for value `Greater than` in `wp_post` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function post_greater_than( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '<' : '>';
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        return "{$wpdb->posts}.{$column} {$op} {$value}";
    }

    /**
     * Helper method to create custom where query for value `Greater than or equal to` in `wp_post` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function post_greater_than_equal( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '<=' : '>=';
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        return "{$wpdb->posts}.{$column} {$op} {$value}";
    }

    /**
     * Helper method to create custom where query for value `Less than` in `wp_post` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function post_less_than( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '>' : '<';
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        return "{$wpdb->posts}.{$column} {$op} {$value}";
    }

    /**
     * Helper method to create custom where query for value `Less than or equal to` in `wp_post` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function post_less_than_equal( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '<=' : '>=';
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        return "{$wpdb->posts}.{$column} {$op} {$value}";
    }

    /**
     * Helper method to create custom where query for value `Contains` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function postmeta_contain( $column, $value, $operator ) {
        global $wpdb;

        if (self::is_tax_class_column($column)) {
            $value = self::process_tax_class_value($value);

            $is_empty_or_standard = (is_null($value) || $value === '' || $value === 'standard');

            if ($is_empty_or_standard) {
                if ('exc' === $operator) {
                    $condition = "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta" . self::$meta_table_count . ".meta_value IS NOT NULL AND RexMeta" . self::$meta_table_count . ".meta_value != '' AND RexMeta" . self::$meta_table_count . ".meta_value != 'standard')";

                    return $condition;
                } else {
                    $condition = "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' AND (RexMeta" . self::$meta_table_count . ".meta_value IS NULL OR RexMeta" . self::$meta_table_count . ".meta_value = '' OR RexMeta" . self::$meta_table_count . ".meta_value = 'standard'))";
                    return $condition;
                }
            } else {
                if ('exc' === $operator) {
                    $condition = "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' AND (RexMeta" . self::$meta_table_count . ".meta_value IS NULL OR RexMeta" . self::$meta_table_count . ".meta_value = '' OR RexMeta" . self::$meta_table_count . ".meta_value = 'standard' OR RexMeta" . self::$meta_table_count . ".meta_value != '{$value}'))";
                    return $condition;
                } else {
                    $condition = "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta" . self::$meta_table_count . ".meta_value = '{$value}')";
                    return $condition;
                }
            }
        }

        if ( self::is_backorders_column( $column ) ) {
            $value = self::process_backorders_value( $value );
            // Include products with no backorders explicitly set
            if ( $value === 'no' ) {
                if ( 'exc' === $operator ) {
                    // Exclude products where backorders are not allowed
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value != 'no')";
                } else {
                    // Include products where backorders are not allowed (explicit 'no' or missing)
                    return "(
                (RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                 AND RexMeta" . self::$meta_table_count . ".meta_value = 'no')
                OR RexMeta" . self::$meta_table_count . ".meta_key IS NULL
            )";
                }
            } else {
                if ( 'exc' === $operator ) {
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value != '{$value}')";
                } else {
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value = '{$value}')";
                }
            }
        }


        if(self::is_tax_status_column($column)){
            $value = self::process_tax_status_value($value);
        }

        if ( self::is_catalog_visibility_column( $column ) ) {
            $value = self::process_catalog_visibility_value( $value );
            if ( empty( $value ) || ( is_array( $value ) && count( $value ) === 0 ) ) {
                return ( 'exc' === $operator ) ? '1=1' : '1=0';
            }

            if ( !is_array( $value ) ) {
                $value = array( $value );
            }

            $product_ids = array_filter( array_map( 'absint', $value ) );

            if ( empty( $product_ids ) ) {
                return ( 'exc' === $operator ) ? '1=1' : '1=0';
            }

            global $wpdb;
            $id_list = implode( ',', $product_ids );
            $op = ( 'exc' === $operator ) ? 'NOT IN' : 'IN';
            $condition = "{$wpdb->posts}.ID {$op} ({$id_list})";
            return $condition;
        }

        $op = 'exc' === $operator ? 'NOT LIKE' : 'LIKE';
        return '(RexMeta' . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} '%{$wpdb->esc_like( $value )}%')";
    }

    /**
     * Helper method to create custom where query for value `Does not contain` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function postmeta_dn_contain( $column, $value, $operator ) {
        global $wpdb;

        // Process tax class values if the column is a tax class
        if (self::is_tax_class_column($column)) {
            $value = self::process_tax_class_value($value);
        }

        if ( self::is_backorders_column( $column ) ) {
            $value = self::process_backorders_value( $value );

            // Include products with no backorders explicitly set
            if ( $value === 'no' ) {
                if ( 'inc' === $operator ) {
                    // Exclude products where backorders are not allowed
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value != 'no')";
                } else {
                    // Include products where backorders are not allowed (explicit 'no' or missing)
                    return "(
                (RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                 AND RexMeta" . self::$meta_table_count . ".meta_value = 'no')
                OR RexMeta" . self::$meta_table_count . ".meta_key IS NULL
            )";
                }
            } else {
                if ( 'inc' === $operator ) {
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value != '{$value}')";
                } else {
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value = '{$value}')";
                }
            }
        }

        if(self::is_tax_status_column($column)){
            $value = self::process_tax_status_value($value);
        }

        if ( self::is_catalog_visibility_column( $column ) ) {
            $value = self::process_catalog_visibility_value( $value );
            if ( empty( $value ) || ( is_array( $value ) && count( $value ) === 0 ) ) {
                return ( 'exc' === $operator ) ? '1=1' : '1=0';
            }

            if ( !is_array( $value ) ) {
                $value = array( $value );
            }

            $product_ids = array_filter( array_map( 'absint', $value ) );

            if ( empty( $product_ids ) ) {
                return ( 'exc' === $operator ) ? '1=1' : '1=0';
            }

            global $wpdb;
            $id_list = implode( ',', $product_ids );
            $op = ( 'exc' === $operator ) ? 'IN' : 'NOT IN';

            $condition = "{$wpdb->posts}.ID {$op} ({$id_list})";
            return $condition;
        }

        $op = 'exc' === $operator ? 'LIKE' : 'NOT LIKE';
        return '(RexMeta' . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} '%{$wpdb->esc_like( $value )}%')";
    }

    /**
     * Helper method to create custom where query for value `Is equal to` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function postmeta_equal_to( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '<>' : '=';

        // Process tax class values if the column is a tax class
        if (self::is_tax_class_column($column)) {
            $value = self::process_tax_class_value($value);
        }

        if ( self::is_backorders_column( $column ) ) {
            $value = self::process_backorders_value( $value );

            // Include products with no backorders explicitly set
            if ( $value === 'no' ) {
                if ( 'exc' === $operator ) {
                    // Exclude products where backorders are not allowed
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value != 'no')";
                } else {
                    // Include products where backorders are not allowed (explicit 'no' or missing)
                    return "(
                (RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                 AND RexMeta" . self::$meta_table_count . ".meta_value = 'no')
                OR RexMeta" . self::$meta_table_count . ".meta_key IS NULL
            )";
                }
            } else {
                if ( 'exc' === $operator ) {
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value != '{$value}')";
                } else {
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value = '{$value}')";
                }
            }
        }

        if(self::is_tax_status_column($column)){
            $value = self::process_tax_status_value($value);
        }

        if ( self::is_catalog_visibility_column( $column ) ) {
            $value = self::process_catalog_visibility_value( $value );
            if ( empty( $value ) || ( is_array( $value ) && count( $value ) === 0 ) ) {
                return ( 'exc' === $operator ) ? '1=1' : '1=0';
            }

            if ( !is_array( $value ) ) {
                $value = array( $value );
            }

            $product_ids = array_filter( array_map( 'absint', $value ) );

            if ( empty( $product_ids ) ) {
                return ( 'exc' === $operator ) ? '1=1' : '1=0';
            }

            global $wpdb;
            $id_list = implode( ',', $product_ids );
            $op = ( 'exc' === $operator ) ? 'NOT IN' : 'IN';

            $condition = "{$wpdb->posts}.ID {$op} ({$id_list})";
            return $condition;
        }

        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        return '<>' === $op ? "(RexMeta". self::$meta_table_count .".meta_value IS NULL OR RexMeta". self::$meta_table_count .".meta_value {$op} {$value})"
            : '(RexMeta' . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
    }

    /**
     * Helper method to create custom where query for value `Is not equal to` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function postmeta_nequal_to( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '=' : '<>';

        // Process tax class values if the column is a tax class
        if (self::is_tax_class_column($column)) {
            $value = self::process_tax_class_value($value);
        }

        if ( self::is_backorders_column( $column ) ) {
            $value = self::process_backorders_value( $value );

            // Include products with no backorders explicitly set
            if ( $value === 'no' ) {
                if ( 'inc' === $operator ) {
                    // Exclude products where backorders are not allowed
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value != 'no')";
                } else {
                    // Include products where backorders are not allowed (explicit 'no' or missing)
                    return "(
                (RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                 AND RexMeta" . self::$meta_table_count . ".meta_value = 'no')
                OR RexMeta" . self::$meta_table_count . ".meta_key IS NULL
            )";
                }
            } else {
                if ( 'inc' === $operator ) {
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value != '{$value}')";
                } else {
                    return "(RexMeta" . self::$meta_table_count . ".meta_key = '{$column}' 
                     AND RexMeta" . self::$meta_table_count . ".meta_value = '{$value}')";
                }
            }
        }


        if(self::is_tax_status_column($column)){
            $value = self::process_tax_status_value($value);
        }

        if ( self::is_catalog_visibility_column( $column ) ) {
            $value = self::process_catalog_visibility_value( $value );
            if ( empty( $value ) || ( is_array( $value ) && count( $value ) === 0 ) ) {
                return ( 'exc' === $operator ) ? '1=1' : '1=0';
            }

            if ( !is_array( $value ) ) {
                $value = array( $value );
            }

            $product_ids = array_filter( array_map( 'absint', $value ) );

            if ( empty( $product_ids ) ) {
                return ( 'exc' === $operator ) ? '1=1' : '1=0';
            }

            global $wpdb;
            $id_list = implode( ',', $product_ids );
            $op = ( 'exc' === $operator ) ? 'IN' : 'NOT IN';

            $condition = "{$wpdb->posts}.ID {$op} ({$id_list})";

            return $condition;
        }

        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
	    return '<>' === $op ? "(RexMeta". self::$meta_table_count .".meta_value IS NULL OR RexMeta". self::$meta_table_count .".meta_value {$op} {$value})"
		    : '(RexMeta' . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
    }

    /**
     * Helper method to create custom where query for value `Greater than` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function postmeta_greater_than( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '<=' : '>';
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        
        if ( 'exc' === $operator ) {
            return "(RexMeta". self::$meta_table_count .".meta_key = '{$column}' AND (RexMeta". self::$meta_table_count .".meta_value IS NULL OR RexMeta". self::$meta_table_count .".meta_value {$op} {$value}))";
        } else {
            return "(RexMeta". self::$meta_table_count .".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
        }
    }

    /**
     * Helper method to create custom where query for value `Greater than or equal to` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function postmeta_greater_than_equal( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '<' : '>=';
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        
        if ( 'exc' === $operator ) {
            return "(RexMeta". self::$meta_table_count .".meta_key = '{$column}' AND (RexMeta". self::$meta_table_count .".meta_value IS NULL OR RexMeta". self::$meta_table_count .".meta_value {$op} {$value}))";
        } else {
            return "(RexMeta". self::$meta_table_count .".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
        }
    }

    /**
     * Helper method to create custom where query for value `Less than` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function postmeta_less_than( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '>=' : '<';
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        
        if ( 'exc' === $operator ) {
            return "(RexMeta". self::$meta_table_count .".meta_key = '{$column}' AND (RexMeta". self::$meta_table_count .".meta_value IS NULL OR RexMeta". self::$meta_table_count .".meta_value {$op} {$value}))";
        } else {
            return "(RexMeta". self::$meta_table_count .".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
        }
    }

    /**
     * Helper method to create custom where query for value `Less than or equal to` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.0
     */
    private static function postmeta_less_than_equal( $column, $value, $operator ) {
        global $wpdb;
        $op = 'exc' === $operator ? '>' : '<=';
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        
        if ( 'exc' === $operator ) {
            return "(RexMeta". self::$meta_table_count .".meta_key = '{$column}' AND (RexMeta". self::$meta_table_count .".meta_value IS NULL OR RexMeta". self::$meta_table_count .".meta_value {$op} {$value}))";
        } else {
            return "(RexMeta". self::$meta_table_count .".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
        }
    }

    /**
     * Helper method to create custom where query for value `Contains` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.5
     */
    private static function postterm_contain( $column, $value, $operator ) {
        global $wpdb;

        $table_column = 'RexTerm' . self::$term_table_count . ".{$column}";
        $op = 'IN';

        if ( 'exc' === $operator ) {
            $op = 'NOT IN';
            $ids = self::get_term_product_ids( $value );

            if ( is_array( $ids ) ) {
                $ids = array_map( 'intval', $ids );
                $value = implode( ',', $ids );
            } elseif ( is_string( $ids ) ) {
                $ids_array = array_map( 'intval', array_filter( array_map( 'trim', explode( ',', $ids ) ) ) );
                $value = implode( ',', $ids_array );
            } else {
                $value = '';
            }
            $table_column = "$wpdb->posts.ID";
        }

        if ( empty( $value ) ) {
            return '(1=0)';
        }

        return "({$table_column} {$op} ({$value}))";
    }


    /**
     * Helper method to create custom where query for value `Does not contain` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.5
     */
    private static function postterm_dn_contain( $column, $value, $operator ) {
        global $wpdb;
        $table_column = 'RexTerm' . self::$term_table_count . ".{$column}";
        $op = 'IN';

        if ( 'inc' === $operator ) {
            $op = 'NOT IN';
            $ids = self::get_term_product_ids( $value );
            if ( is_array( $ids ) ) {
                $ids = array_map( 'intval', $ids );
                $value = implode( ',', $ids );
            } elseif ( is_string( $ids ) ) {
                $ids_array = array_map( 'intval', array_filter( array_map( 'trim', explode( ',', $ids ) ) ) );
                $value = implode( ',', $ids_array );
            } else {
                $value = '';
            }

            $table_column = "$wpdb->posts.ID";
        }

        if ( empty( $value ) ) {
            return '(1=0)';
        }

        return "({$table_column} {$op} ({$value}))";
    }


    /**
     * Helper method to create custom where query for value `Is equal to` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.5
     */
    private static function postterm_equal_to( $column, $value, $operator ) {
        global $wpdb;

        $table_column = 'RexTerm' . self::$term_table_count . ".{$column}";
        $op = 'IN';
        if( 'exc' === $operator ) {
            $op = 'NOT IN';
            $value = self::get_term_product_ids( $value ) ; // Comma separated
            $table_column = "$wpdb->posts.ID";
        }
        return $value ? "({$table_column} {$op} ({$value}))" : '';
    }

    /**
     * Process tax class values for filtering
     *
     * @param string $value The tax class value provided by the user
     * @return string The processed tax class value
     * @since 7.4.48
     */
    private static function process_tax_class_value($value) {
        $standard_tax_classes = [
            'standard' => '',
            '' => '',
            'reduced-rate' => 'reduced-rate',
            'zero-rate' => 'zero-rate'
        ];

        $normalized_value = strtolower(trim($value));
        foreach ($standard_tax_classes as $key => $class_value) {
            if ($normalized_value === $key) {
                return $class_value;
            }
        }

        $all_tax_classes = self::get_all_tax_classes();

        foreach ($all_tax_classes as $class_slug => $class_name) {
            if (strtolower($class_name) === $normalized_value || strtolower($class_slug) === $normalized_value) {
                return $class_slug;
            }
        }

        foreach ($all_tax_classes as $class_slug => $class_name) {
            if (strpos(strtolower($class_name), $normalized_value) !== false ||
                strpos(strtolower($class_slug), $normalized_value) !== false) {
                return $class_slug;
            }
        }

        return $value;
    }

    /**
     * Get all available tax classes including standard and custom ones
     *
     * @return array Array of tax classes with slug => name format
     * @since 7.4.48
     */
    private static function get_all_tax_classes() {
        $tax_classes = [
            '' => 'Standard',
            'reduced-rate' => 'Reduced Rate',
            'zero-rate' => 'Zero Rate'
        ];

        if (function_exists('WC')) {
            $wc_tax = new WC_Tax();
            $custom_classes = $wc_tax->get_tax_classes();

            foreach ($custom_classes as $class_name) {
                $slug = sanitize_title($class_name);
                $tax_classes[$slug] = $class_name;
            }
        }

        return $tax_classes;
    }

    /**
     * Handle shipping cost filtering using product terms
     * This method retrieves all shipping classes and checks their costs against the provided value.
     * It returns a comma-separated list of term IDs that match the specified shipping cost.
     * This is used to filter products based on their shipping class costs.
     * @param string $value The shipping cost to filter by, as a string.
     * @return string Comma-separated list of term IDs that match the shipping cost, or an empty string if no matches are found.
     * @since 7.4.48
     */
    private static function handle_shipping_cost_filter_by_terms( $value, $operator, $then ) {
        $target_cost = (string) (float) $value;
        if ( empty( $value ) && in_array( $operator, ['is_empty', 'is_not_empty'], true ) ) {
            self::$is_shipping_cost_filter = true;
        }
        $shipping_classes = get_terms( array(
            'taxonomy'   => 'product_shipping_class',
            'hide_empty' => false,
        ) );

        if ( empty( $shipping_classes ) || is_wp_error( $shipping_classes ) ) {
            return '';
        }

        $matching_ids = [];

        $zones_data = WC_Shipping_Zones::get_zones();
        $zones = [];
        foreach ( $zones_data as $zone_data ) {
            $zones[] = new WC_Shipping_Zone( $zone_data['id'] );
        }

        // Add "Rest of the world" zone (ID = 0)
        $zones[] = new WC_Shipping_Zone( 0 );

        foreach ( $shipping_classes as $class ) {
            $found_cost_for_class = false;

            foreach ( $zones as $zone ) {
                $methods = $zone->get_shipping_methods();
                foreach ( $methods as $method ) {
                    if ( 'flat_rate' !== $method->id || 'yes' !== $method->enabled ) {
                        continue;
                    }

                    $raw = $method->get_option( 'class_cost_' . $class->term_id );

                    if ( $raw === '' || $raw === null ) {
                        continue;
                    }

                    $is_number = is_numeric( trim( $raw ) ) && ! preg_match( '/[\[\]\*\+\-\/]/', $raw );
                    if ( ! $is_number ) {
                        continue;
                    }

                    $numeric_cost = (float) $raw;

                    $found_cost_for_class = true;
                    switch ( $operator ) {
                        case 'equal_to':
                            if ( $numeric_cost === (float) $target_cost ) {
                                $matching_ids[] = (int) $class->term_id;
                            }
                            break;

                        case 'nequal_to':
                            if ( $numeric_cost !== (float) $target_cost ) {
                                $matching_ids[] = (int) $class->term_id;
                            }
                            break;

                        case 'greater_than':
                            if ( $numeric_cost > (float) $target_cost ) {
                                $matching_ids[] = (int) $class->term_id;
                            }
                            break;

                        case 'greater_than_equal':
                            if ( $numeric_cost >= (float) $target_cost ) {
                                $matching_ids[] = (int) $class->term_id;
                            }
                            break;

                        case 'less_than':
                            if ( $numeric_cost < (float) $target_cost ) {
                                $matching_ids[] = (int) $class->term_id;
                            }
                            break;

                        case 'less_than_equal':
                            if ( $numeric_cost <= (float) $target_cost ) {
                                $matching_ids[] = (int) $class->term_id;
                            }
                            break;

                        case 'contain':
                            if ( str_contains( (string) $numeric_cost, $target_cost ) ) {
                                $matching_ids[] = (int) $class->term_id;
                            }
                            break;

                        case 'dn_contain':
                            if ( ! str_contains( (string) $numeric_cost, $target_cost ) ) {
                                $matching_ids[] = (int) $class->term_id;
                            }
                            break;

                        case 'is_empty':
                            break;

                        case 'is_not_empty':
                            $matching_ids[] = (int) $class->term_id;
                            break;
                    }
                }
            }

            if ( self::$is_shipping_cost_filter ) {
                if ( $operator === 'is_empty' && ! $found_cost_for_class ) {
                    $matching_ids[] = (int) $class->term_id;
                }
                if ( $operator === 'is_not_empty' && $found_cost_for_class ) {
                    $matching_ids[] = (int) $class->term_id;
                }
            } elseif ( ! $found_cost_for_class ) {
                if ( $operator === 'is_empty' ) {
                    $matching_ids[] = (int) $class->term_id;
                }
            }
        }

        $matching_ids = array_values( array_unique( $matching_ids ) );

        $contain_conditions    = ['contain', 'equal_to'];
        $dn_contain_conditions = ['dn_contain', 'nequal_to'];

        if ( $then === 'exc' && ( $operator === 'is_empty' || $operator === 'is_not_empty' || count( $matching_ids ) > 0 ) ) {
            $all_ids = wp_list_pluck( $shipping_classes, 'term_id' );

            if ( $operator === 'is_empty' ) {
                $matching_ids = array_values( array_diff( $all_ids, $matching_ids ) );
            } elseif ( $operator === 'is_not_empty' ) {
                $matching_ids = array_diff( $all_ids, $matching_ids );
            } elseif ( ! in_array( $operator, $contain_conditions, true ) ) {
                $matching_ids = array_diff( $all_ids, $matching_ids );
            }
        }

        if ( $then === 'inc' && count( $matching_ids ) > 0 && in_array( $operator, $dn_contain_conditions, true ) ) {
            $all_ids     = wp_list_pluck( $shipping_classes, 'term_id' );
            $matching_ids = array_diff( $all_ids, $matching_ids );
        }

        return empty( $matching_ids ) ? '' : implode( ',', $matching_ids );
    }



    /**
     * Check if the column is related to catalog visibility
     *
     * @param string $column Table column name.
     * @return bool Whether the column is related to catalog visibility.
     * @since 7.4.48
     */
    private static function is_catalog_visibility_column($column) {
        return $column === '_catalog_visibility';
    }

    /**
     * Process catalog visibility value to match WooCommerce's internal representation
     *
     * @param string $value The catalog visibility value.
     * @return string The processed catalog visibility value.
     * @since 7.4.48
     */
    private static function process_catalog_visibility_value($value) {

        // Clean and normalize the input value
        $visibility = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $value)));

        // Determine visibility setting from input
        if (strpos($visibility, 'visible') !== false ||
            strpos($visibility, 'shop and search') !== false ||
            strpos($visibility, 'both') !== false) {
            $visibility = 'visible';
        } elseif (strpos($visibility, 'shop only') !== false ||
            strpos($visibility, 'catalog') !== false ||
            strpos($visibility, 'shop') === 0) {
            $visibility = 'catalog';
        } elseif (strpos($visibility, 'search only') !== false ||
            strpos($visibility, 'search results only') !== false ||
            strpos($visibility, 'search') === 0) {
            $visibility = 'search';
        } elseif (strpos($visibility, 'hidden') !== false ||
            strpos($visibility, 'none') !== false ||
            strpos($visibility, 'no') === 0) {
            $visibility = 'hidden';
        }

        // Base query arguments for all product types
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'post_status'    => array('publish', 'private'), // Include private products
            'meta_query'     => array(
                'relation' => 'OR',
                // Include simple products
                array(
                    'key'     => '_product_type',
                    'value'   => 'simple',
                    'compare' => '='
                ),
                // Include variable products
                array(
                    'key'     => '_product_type',
                    'value'   => 'variable',
                    'compare' => '='
                ),
                // Include grouped products
                array(
                    'key'     => '_product_type',
                    'value'   => 'grouped',
                    'compare' => '='
                ),
                // Include external/affiliate products
                array(
                    'key'     => '_product_type',
                    'value'   => 'external',
                    'compare' => '='
                ),
                // Include subscription products (if WooCommerce Subscriptions is active)
                array(
                    'key'     => '_product_type',
                    'value'   => 'subscription',
                    'compare' => '='
                ),
                // Include variable subscription products
                array(
                    'key'     => '_product_type',
                    'value'   => 'variable-subscription',
                    'compare' => '='
                ),
                // Include booking products (if WooCommerce Bookings is active)
                array(
                    'key'     => '_product_type',
                    'value'   => 'booking',
                    'compare' => '='
                ),
                // Include bundle products (if WooCommerce Product Bundles is active)
                array(
                    'key'     => '_product_type',
                    'value'   => 'bundle',
                    'compare' => '='
                ),
                // Include composite products (if WooCommerce Composite Products is active)
                array(
                    'key'     => '_product_type',
                    'value'   => 'composite',
                    'compare' => '='
                ),
                // Fallback: include products without _product_type meta (defaults to simple)
                array(
                    'key'     => '_product_type',
                    'compare' => 'NOT EXISTS'
                )
            ),
            'tax_query'      => array()
        );

        // Apply visibility taxonomy filters based on the determined visibility
        if ($visibility == 'visible') {
            // Products visible in both shop and search
            $args['tax_query'][] = array(
                'taxonomy' => 'product_visibility',
                'field'    => 'slug',
                'terms'    => array('exclude-from-search', 'exclude-from-catalog'),
                'operator' => 'NOT IN',
            );
        } elseif ($visibility == 'catalog') {
            // Products visible in shop/catalog only (hidden from search)
            $args['tax_query'] = array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'slug',
                    'terms'    => array('exclude-from-search'),
                    'operator' => 'IN',
                ),
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'slug',
                    'terms'    => array('exclude-from-catalog'),
                    'operator' => 'NOT IN',
                ),
            );
        } elseif ($visibility == 'search') {
            // Products visible in search only (hidden from shop/catalog)
            $args['tax_query'] = array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'slug',
                    'terms'    => array('exclude-from-catalog'),
                    'operator' => 'IN',
                ),
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'slug',
                    'terms'    => array('exclude-from-search'),
                    'operator' => 'NOT IN',
                ),
            );
        } elseif ( $visibility == 'hidden' ) {
            // Products hidden from both shop (catalog) and search
            $args['tax_query'][] = array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'slug',
                    'terms'    => array( 'exclude-from-search' ),
                    'operator' => 'IN',
                ),
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'slug',
                    'terms'    => array( 'exclude-from-catalog' ),
                    'operator' => 'IN',
                ),
            );
        }

        // Execute the query
        $query = new WP_Query($args);

        // Clean up
        wp_reset_postdata();

        return $query->posts;
    }

    /**
     * Helper method to create custom where query for value `Is equal to` in `wp_postmeta` table
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.3.5
     */
    private static function postterm_nequal_to( $column, $value, $operator ) {
        global $wpdb;
        $table_column = 'RexTerm' . self::$term_table_count . ".{$column}";
        $op = 'IN';
        if( 'inc' === $operator ) {
            $op = 'NOT IN';
            $value = self::get_term_product_ids( $value ); // Comma separated
            $table_column = "$wpdb->posts.ID";
        }
        return $value ? "({$table_column} {$op} ({$value}))" : '';
    }

    /**
     * Build SQL condition for checking if a post column is empty.
     *
     * @param string $column   Column name to check.
     * @param string $operator Operator: 'inc' (include) or 'exc' (exclude).
     * @return string SQL fragment.
     * @since 7.4.45
     */
    private static function post_is_empty( $column, $value, $operator = 'inc' ) {
        global $wpdb;
        $condition = "({$wpdb->posts}.{$column} IS NULL OR {$wpdb->posts}.{$column} = '')";
        if ( 'exc' === $operator ) {
            $condition = "NOT ( {$condition} )";
        }
        return $condition;
    }

    /**
     * Build SQL condition for checking if a post column is not empty.
     *
     * @param string $column   Column name to check.
     * @param string $operator Operator: 'inc' (include) or 'exc' (exclude).
     * @return string SQL fragment.
     * @since 7.4.45
     */
    private static function post_is_not_empty( $column, $value, $operator = 'inc' ) {
        global $wpdb;
        $condition = "({$wpdb->posts}.{$column} IS NOT NULL AND {$wpdb->posts}.{$column} != '')";
        if ( 'exc' === $operator ) {
            $condition = "NOT ( {$condition} )";
        }
        return $condition;
    }

    /**
     * Build SQL condition for checking if a postmeta field is empty.
     *
     * @param string $column   Meta key name to check.
     * @param string $value    Unused in this context.
     * @param string $operator Operator: 'inc' (include) or 'exc' (exclude).
     * @return string SQL fragment.
     * @since 7.4.45
     */
    private static function postmeta_is_empty($column, $value, $operator = 'inc') {

        $meta_table = 'RexMeta' . self::$meta_table_count;
        $condition = "(($meta_table.meta_key = '{$column}' AND ($meta_table.meta_value IS NULL OR $meta_table.meta_value = '')))";
        if ('exc' === $operator) {
            $condition = "(($meta_table.meta_key = '{$column}' AND ($meta_table.meta_value IS NOT NULL AND $meta_table.meta_value != '')))";
        }

        return $condition;
    }

    /**
     * Build SQL condition for checking if a postmeta field is not empty.
     *
     * @param string $column   Meta key name to check.
     * @param string $value    Unused in this context.
     * @param string $operator Operator: 'inc' (include) or 'exc' (exclude).
     * @return string SQL fragment.
     * @since 7.4.45
     */
    private static function postmeta_is_not_empty($column, $value, $operator = 'inc') {
        $meta_table = 'RexMeta' . self::$meta_table_count;
        $condition = "($meta_table.meta_key = '{$column}' AND $meta_table.meta_value IS NOT NULL AND $meta_table.meta_value != '')";
        if ('exc' === $operator) {
            $condition = "($meta_table.meta_key = '{$column}' AND $meta_table.meta_value IS NULL OR $meta_table.meta_value = '')";
        }
        return $condition;
    }

    /**
     * Build SQL condition for checking if a taxonomy term relationship is empty.
     *
     * @param string $column   Term column name to check.
     * @param string $value    Unused in this context.
     * @param string $operator Operator: 'inc' (include) or 'exc' (exclude).
     * @return string SQL fragment.
     * @since 7.4.45
     */
    private static function postterm_is_empty($column, $value, $operator = 'inc') {
        global $wpdb;
        if('term_taxonomy_id' === $column && !self::$is_shipping_cost_filter){
            if('inc' === $operator){
                return "{$wpdb->posts}.ID NOT IN ({$value})";
            } else {
                return "{$wpdb->posts}.ID IN ({$value})";
            }
        }

        if('term_taxonomy_id' === $column && self::$is_shipping_cost_filter){
            $value = self::get_term_product_ids( $value );
            return "{$wpdb->posts}.ID IN ({$value})";
        }

        $term_table = 'RexTerm' . self::$term_table_count;
        if ('exc' === $operator) {
            return "$term_table.$column IS NOT NULL";
        } else {
            return "$term_table.$column IS NULL";
        }
    }

    /**
     * Build SQL condition for checking if a taxonomy term relationship is not empty.
     *
     * @param string $column   Term column name to check.
     * @param string $value    Unused in this context.
     * @param string $operator Operator: 'inc' (include) or 'exc' (exclude).
     * @return string SQL fragment.
     * @since 7.4.45
     */
    private static function postterm_is_not_empty($column, $value, $operator = 'inc') {
        global $wpdb;
        if('term_taxonomy_id' === $column && !self::$is_shipping_cost_filter){
            if('inc' === $operator){
                return "{$wpdb->posts}.ID IN ({$value})";
            } else {
                return "{$wpdb->posts}.ID NOT IN ({$value})";
            }
        }

        if('term_taxonomy_id' === $column && self::$is_shipping_cost_filter){
            if('inc' === $operator){
                $value = self::get_term_product_ids( $value );
                return "{$wpdb->posts}.ID IN ({$value})";
            } else {
                $value = self::get_term_product_ids( $value );
                return "{$wpdb->posts}.ID NOT IN ({$value})";
            }
        }


        $term_table = 'RexTerm' . self::$term_table_count;
        if ('exc' === $operator) {
            return "$term_table.$column IS NULL";
        } else {
            return "$term_table.$column IS NOT NULL";
        }
    }



    /**
     * Get product ids [comma separated] by term id
     *
     * @param int|string $term_id Taxonomy ID.
     *
     * @return string
     * @since 7.3.5
     */
    private static function get_term_product_ids( $taxonomy_ids ) {
        global $wpdb;

        if ( empty( $taxonomy_ids ) ) {
            return '';
        }

        // Handle different input formats for taxonomy IDs
        if ( is_string( $taxonomy_ids ) && strpos( $taxonomy_ids, ',' ) !== false ) {
            $taxonomy_id_array = array_map( 'absint', array_filter( array_map( 'trim', explode( ',', $taxonomy_ids ) ) ) );
        } elseif ( is_array( $taxonomy_ids ) ) {
            $taxonomy_id_array = array_map( 'absint', array_filter( $taxonomy_ids ) );
        } else {
            $taxonomy_id_array = [ absint( $taxonomy_ids ) ];
        }

        if ( empty( $taxonomy_id_array ) ) {
            return '';
        }

        $placeholders = implode( ',', array_fill( 0, count( $taxonomy_id_array ), '%d' ) );
        // Query using term_taxonomy_id directly
        $query = $wpdb->prepare(
            "SELECT DISTINCT object_id 
         FROM {$wpdb->term_relationships} 
         WHERE term_taxonomy_id IN ($placeholders)",
            $taxonomy_id_array
        );
        $product_ids = $wpdb->get_col( $query );

        return ! empty( $product_ids ) ? implode( ', ', $product_ids ) : '';
    }



    /**
     * Gets WooCommerce product attributes [Global]
     *
     * @since 7.2.18
     * @return array
     */
    protected static function get_product_attributes() {
        $taxonomies = wpfm_get_cached_data( 'product_attributes_custom_filter' );
        if( !is_array( $taxonomies ) && empty( $taxonomies ) ) {
            $taxonomies = [];
            $product_attributes = wc_get_attribute_taxonomies();

            if( is_array( $product_attributes ) && !empty( $product_attributes ) ) {
                foreach( $product_attributes as $attribute ) {
                    if( isset( $attribute->attribute_name, $attribute->attribute_label ) && $attribute->attribute_name && $attribute->attribute_label ) {
                        $taxonomies[ 'Product Attributes' ][ "pa_{$attribute->attribute_name}" ] = $attribute->attribute_label;
                    }
                }
            }
            wpfm_set_cached_data( 'product_attributes_custom_filter', $taxonomies );
        }
        return $taxonomies;
    }

    /**
     * Retrieve product variation attributes. [Global]
     *
     * This method retrieves the cached data for product variation attributes.
     * If the cached data is not an array or is empty, it fetches the product
     * attributes, processes them to create the product variation attributes,
     * and then caches this processed data.
     *
     * @return array The product variation attributes.
     *
     * @since 7.4.15
     */
    protected static function get_product_variation_attributes() {
        $var_attributes = wpfm_get_cached_data( 'product_variation_attributes_custom_filter' );
        if ( !is_array( $var_attributes ) ) {
            $var_attributes = [];
            $pr_attribtues = wpfm_get_cached_data( 'product_attributes_custom_filter' );
            if ( !empty( $pr_attribtues[ 'Product Attributes' ] ) ) {
                $var_attributes[ 'Product Variation Attributes' ] = [];
                foreach ( $pr_attribtues[ 'Product Attributes' ] as $key => $value ) {
                    $var_attributes[ 'Product Variation Attributes' ][ "va_{$key}" ] = $value;
                }
            }
            wpfm_set_cached_data( 'product_variation_attributes_custom_filter', $var_attributes );
        }
        return $var_attributes;
    }


    /**
     * Helper method to create custom where query for value `Greater than` in term relationships
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.4.48
     */
    private static function postterm_greater_than( $column, $value, $operator ) {
        global $wpdb;
        $op = 'IN';
        $value = self::get_term_product_ids( $value );
        $table_column = "$wpdb->posts.ID";
        return $value ? "({$table_column} {$op} ({$value}))" : '(1=0)';
    }

    /**
     * Helper method to create custom where query for value `Greater than or equal to` in term relationships
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.4.48
     */
    private static function postterm_greater_than_equal( $column, $value, $operator ) {
        global $wpdb;
        $op = 'IN';
        $value = self::get_term_product_ids( $value );
        $table_column = "$wpdb->posts.ID";
        return $value ? "({$table_column} {$op} ({$value}))" : '(1=0)';
    }

    /**
     * Helper method to create custom where query for value `Less than` in term relationships
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.4.48
     */
    private static function postterm_less_than( $column, $value, $operator ) {
        global $wpdb;
        $op = 'IN';
        $value = self::get_term_product_ids( $value );
        $table_column = "$wpdb->posts.ID";
        return $value ? "({$table_column} {$op} ({$value}))" : '(1=0)';
    }

    /**
     * Helper method to create custom where query for value `Less than or equal to` in term relationships
     *
     * @param string $column Table column name.
     * @param string|int $value Attribute value.
     * @param string $operator MySQL operator.
     *
     * @return string
     * @since 7.4.48
     */
    private static function postterm_less_than_equal( $column, $value, $operator ) {
        global $wpdb;
        $op = 'IN';
        $value = self::get_term_product_ids( $value );
        $table_column = "$wpdb->posts.ID";
        return $value ? "({$table_column} {$op} ({$value}))" : '(1=0)';
    }

    /**
     * Helper method to check if a column is a tax class
     *
     * @param string $column The column name
     * @return bool Whether the column is a tax class
     * @since 7.4.48
     */
    private static function is_tax_class_column($column) {
        return in_array($column, ['_tax_class', 'tax_class']);
    }

    /**
     * Identify backorders column keys.
     * This method checks if the provided column name is related to backorders.
     * It recognizes both the meta key `_backorders` and the human-readable `backorders`.
     * @param string $column The column name to check.
     * @return bool True if the column is a backorders column, false otherwise.
     * @since 7.4.48
     */
    private static function is_backorders_column($column)
    {
        return in_array($column, ['_backorders', 'backorders'], true);
    }

    /**
     * Normalize backorders value from human input to WooCommerce storage.
     * - "Do not allow" -> "no"
     * - "Allow, but notify customer" -> "notify"
     * - "Allow" -> "yes"
     * Also accepts "no"/"notify"/"yes" directly (case-insensitive).
     * This method processes the backorders value to ensure it matches WooCommerce's expected values.
     * @param string $value The backorders value provided by the user
     * @return string The processed backorders value
     * @since 7.4.48
     */
    private static function process_backorders_value($value)
    {
        $normalized = strtolower(trim($value));

        if (in_array($normalized, ['no', 'notify', 'yes'], true)) {
            return $normalized;
        }

        if ($normalized === 'do not allow' || $normalized === 'not allow' || $normalized === 'don\'t allow') {
            return 'no';
        }
        if ($normalized === 'allow, but notify customer' || $normalized === 'allow but notify customer' || $normalized === 'notify') {
            return 'notify';
        }
        if ($normalized === 'allow' || $normalized === 'allowed') {
            return 'yes';
        }

        return $value;
    }

    /**
     * Check if the column is a tax status column
     *
     * @param string $column The column name
     * @return bool Whether the column is a tax status column
     * @since 7.4.48
     */
    private  static function is_tax_status_column( $column ) {
        return in_array( $column, [ '_tax_status', 'tax_status' ], true );
    }

    /**
     * Normalize tax status values from human input to WooCommerce storage.
     * - "Taxable" -> "taxable"
     * - "Shipping" -> "shipping"
     * - "None" -> "none"
     * Also accepts "taxable"/"shipping"/"none" directly (case-insensitive).
     *
     * @param string $value The tax status value provided by the user
     * @return string The processed tax status value
     * @since 7.4.48
     */
    private static function process_tax_status_value( $value ) {

        $normalized = strtolower( trim( preg_replace( '/\s+/', ' ', $value ) ) );
        $patterns = [
            'taxable'  => '/^(taxable|taxable product|tax|taxable goods)$/i',
            'shipping' => '/^(shipping|shipping only|ship only|shipping charges)$/i',
            'none'     => '/^(none|no tax|not taxable|no taxes|tax-free)$/i',
        ];

        foreach ( $patterns as $canonical => $pattern ) {
            if ( preg_match( $pattern, $normalized ) ) {
                return $canonical;
            }
        }

        if ( in_array( $normalized, array_keys( $patterns ), true ) ) {
            return $normalized;
        }

        return $normalized;
    }

    /**
     * Get product IDs based on catalog visibility type.
     *
     * @param string $visibility_type The visibility type: 'visible', 'catalog', 'search', or 'hidden'.
     * @return array Array of product IDs.
     * @since 7.4.48
     */
    private static function rex_get_products_by_catalog_visibility( $visibility_type = 'visible' ) {
        global $wpdb;

        $product_ids = [];

        // WooCommerce 3.0+ uses taxonomy product_visibility
        if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
            $term_ids = wc_get_product_visibility_term_ids();
            $tax_query = [];

            switch ( $visibility_type ) {
                case 'visible': // Shop & Search
                    $tax_query[] = [
                        'taxonomy' => 'product_visibility',
                        'field'    => 'term_id',
                        'terms'    => [ $term_ids['exclude-from-catalog'], $term_ids['exclude-from-search'] ],
                        'operator' => 'NOT IN',
                    ];
                    break;

                case 'catalog': // Shop only
                    $tax_query[] = [
                        'taxonomy' => 'product_visibility',
                        'field'    => 'term_id',
                        'terms'    => [ $term_ids['exclude-from-search'] ],
                        'operator' => 'NOT IN',
                    ];
                    $tax_query[] = [
                        'taxonomy' => 'product_visibility',
                        'field'    => 'term_id',
                        'terms'    => [ $term_ids['exclude-from-catalog'] ],
                        'operator' => 'IN',
                    ];
                    break;

                case 'search': // Search only
                    $tax_query[] = [
                        'taxonomy' => 'product_visibility',
                        'field'    => 'term_id',
                        'terms'    => [ $term_ids['exclude-from-catalog'] ],
                        'operator' => 'NOT IN',
                    ];
                    $tax_query[] = [
                        'taxonomy' => 'product_visibility',
                        'field'    => 'term_id',
                        'terms'    => [ $term_ids['exclude-from-search'] ],
                        'operator' => 'IN',
                    ];
                    break;

                case 'hidden': // Excluded from both
                    $tax_query[] = [
                        'taxonomy' => 'product_visibility',
                        'field'    => 'term_id',
                        'terms'    => [ $term_ids['exclude-from-catalog'], $term_ids['exclude-from-search'] ],
                        'operator' => 'AND',
                    ];
                    break;
            }

            $query = new WP_Query([
                'post_type'      => 'product',
                'fields'         => 'ids',
                'posts_per_page' => -1,
                'tax_query'      => $tax_query,
            ]);

            $product_ids = $query->posts;

        } else {
            // Legacy WooCommerce (<3.0) fallback using _visibility meta
            $meta_value = 'visible';
            if ( in_array( $visibility_type, [ 'visible', 'catalog', 'search', 'hidden' ], true ) ) {
                $meta_value = $visibility_type;
            }

            $query = new WP_Query([
                'post_type'      => 'product',
                'fields'         => 'ids',
                'posts_per_page' => -1,
                'meta_query'     => [
                    [
                        'key'   => '_visibility',
                        'value' => $meta_value,
                    ]
                ],
            ]);

            $product_ids = $query->posts;
        }

        return $product_ids;
    }

    private static function get_product_ids_on_shipping_class($condition, $then){
        $terms = get_terms( [
            'taxonomy'   => 'product_shipping_class',
            'hide_empty' => false, // set true if you only want classes used by products
            'fields'     => 'ids', // only return IDs
        ] );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return [];
        }
        return self::get_term_product_ids($terms);
    }

}
