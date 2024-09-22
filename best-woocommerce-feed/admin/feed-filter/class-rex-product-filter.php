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
                'post_date_gmt'             => 'Product Creation Date',
                'post_modified_gmt'         => 'Product Last Modified Date'
            ]
        ];
        if( rexfeed_is_woocommerce_brand_active() ) {
            $attributes[ 'Primary Attributes' ][ 'product_brands' ] = 'Product Brand';
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
                'less_than_equal'          => __('Less than or equal to', 'rex-product-feed' )
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
        $meta_exists            = false;
        $term_exists            = false;
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

                    if( 'postterm_' === $prefix ) {
                        $acf_attributes = [];
                        if ( defined( 'ACF_VERSION' ) ) {
                            $acf_attributes = Rex_Feed_Attributes::get_acf_fields();
                        }
                        self::$term_table_count++;
                        $column   = $filter[ 'if' ];
                        $taxonomy = preg_match( '/^pa_/i', $column ) || ( !empty( $acf_attributes[ 'ACF Taxonomies' ] ) && array_key_exists( $column, $acf_attributes[ 'ACF Taxonomies' ] ) ) ? $column : substr( $column, 0, -1 );
                        $value    = self::get_term_id( $value, $taxonomy );

                        if( !$value ) {
                            continue;
                        }
                        $term_exists = true;
                    }
                    elseif( 'postmeta_' === $prefix ) {
                        self::$meta_table_count++;
                        $meta_exists = true;
                        $if = preg_replace('/^va_pa_/i', 'attribute_pa_', $if );
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
            'meta_exists' => $meta_exists,
            'term_exists' => $term_exists,
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
     * Get method prefix for custom filter helper methods
     *
     * @param $column
     * @return string
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
        ];
        $term_rel_table_attr = [
            'product_cats',
            'product_tags',
            'product_brands',
        ];

        $acf_attributes = [];
        if ( defined( 'ACF_VERSION' ) ) {
            $acf_attributes = Rex_Feed_Attributes::get_acf_fields();
        }

        if( in_array( $column, $meta_table_attr, true )
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
                return 'term_taxonomy_id';
            default:
                return $column;
        }
    }

    /**
     * Get term id by slug or name
     *
     * @param $slug
     * @param $taxonomy
     * @return int|null
     */
    private static function get_term_id( $slug, $taxonomy ) {
        $term = get_term_by( 'slug', $slug, $taxonomy );
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
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        return '(RexMeta' . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
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
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        return '(RexMeta' . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
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
        $op = 'exc' === $operator ? '<' : '>';
        $value = is_numeric( $value ) ? $wpdb->esc_like( $value ) : "'{$wpdb->esc_like( $value )}'";
        return '(RexMeta' . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
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
        return '(RexMeta' . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
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
        return '(RexMeta' . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
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
        return '(RexMeta' . self::$meta_table_count . ".meta_key = '{$column}' AND RexMeta". self::$meta_table_count .".meta_value {$op} {$value})";
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
        if( 'exc' === $operator ) {
            $op = 'NOT IN';
            $value = self::get_term_product_ids( $value ); // Comma separated
            $table_column = "$wpdb->posts.ID";
        }
        return $value ? "({$table_column} {$op} ({$value}))" : '';
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
        if( 'inc' === $operator ) {
            $op = 'NOT IN';
            $value = self::get_term_product_ids( $value ); // Comma separated
            $table_column = "$wpdb->posts.ID";
        }
        return $value ? "({$table_column} {$op} ({$value}))" : '';
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
            $value = self::get_term_product_ids( $value ); // Comma separated
            $table_column = "$wpdb->posts.ID";
        }
        return $value ? "({$table_column} {$op} ({$value}))" : '';
    }

    /**
     * Helper method to create custom where query for value `Is not equal to` in `wp_postmeta` table
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
     * Get product ids [comma separated] by term id
     *
     * @param int|string $term_id Taxonomy ID.
     *
     * @return string
     * @since 7.3.5
     */
    private static function get_term_product_ids( $term_id ) {
        global $wpdb;
        if( empty( $term_id ) ) {
            return '';
        }
        $product_ids = $wpdb->get_col( $wpdb->prepare( "SELECT `object_id` FROM %i WHERE `term_taxonomy_id` IN (%d)", [$wpdb->term_relationships, $term_id] ) );
        return is_array( $product_ids ) && !empty( $product_ids ) ? implode( ', ', $product_ids ) : '';
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
        if ( !is_array( $var_attributes ) || empty( $var_attributes ) ) {
            $pr_attribtues = wpfm_get_cached_data( 'product_attributes_custom_filter' );
            if ( !empty( $pr_attribtues[ 'Product Attributes' ] ) ) {
                foreach ( $pr_attribtues[ 'Product Attributes' ] as $key => $value ) {
                    $var_attributes[ 'Product Variation Attributes' ][ "va_{$key}" ] = $value;
                }
            }
            wpfm_set_cached_data( 'product_variation_attributes_custom_filter', $var_attributes );
        }
        return $var_attributes;
    }
}