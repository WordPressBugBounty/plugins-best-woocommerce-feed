<?php
/**
 * Feed Validator Factory
 *
 * Factory class for instantiating merchant-specific validators.
 * 
 * @since 7.4.58
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Feed Validator Factory.
 *
 * This class creates the appropriate validator instance based on the merchant type.
 *
 * @since 7.4.58
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-validator
 */
class Rex_Feed_Validator_Factory {

    /**
     * Map of merchant names to their validator class names.
     *
     * @since 7.4.58
     * @access protected
     * @var    array
     */
    protected static $validator_map = array(
        'google'              => 'Rex_Feed_Validator_Google',
        'google_shopping'     => 'Rex_Feed_Validator_Google',
        'google_local'        => 'Rex_Feed_Validator_Google',
        'google_local_inventory' => 'Rex_Feed_Validator_Google',
        'facebook'            => 'Rex_Feed_Validator_Facebook',
        'facebook_marketplace' => 'Rex_Feed_Validator_Facebook',
        'instagram'           => 'Rex_Feed_Validator_Instagram',
        'instagram_shopping'  => 'Rex_Feed_Validator_Instagram',
        'openai'              => 'Rex_Feed_Validator_OpenAI',
        'openai_commerce'     => 'Rex_Feed_Validator_OpenAI',
        'tiktok'              => 'Rex_Feed_Validator_TikTok',
        'tiktok_ads'          => 'Rex_Feed_Validator_TikTok',
        'tiktok_catalog'      => 'Rex_Feed_Validator_TikTok',
        'pinterest'           => 'Rex_Feed_Validator_Pinterest',
        'pinterest_catalog'   => 'Rex_Feed_Validator_Pinterest',
        'pinterest_ads'       => 'Rex_Feed_Validator_Pinterest',
        'yandex'              => 'Rex_Feed_Validator_Yandex',
        'yandex_yml'          => 'Rex_Feed_Validator_Yandex',
        'yandex_direct'       => 'Rex_Feed_Validator_Yandex',
        'yandex_market'       => 'Rex_Feed_Validator_Yandex',
    );

    /**
     * Supported merchants with validation.
     *
     * @since 7.4.58
     * @access protected
     * @var    array
     */
    protected static $supported_merchants = array(
        'google',
        'google_shopping',
        'google_local',
        'google_local_inventory',
        'facebook',
        'facebook_marketplace',
        'instagram',
        'instagram_shopping',
        'openai',
        'openai_commerce',
        'tiktok',
        'tiktok_ads',
        'tiktok_catalog',
        'pinterest',
        'pinterest_catalog',
        'pinterest_ads',
        'yandex',
        'yandex_yml',
        'yandex_direct',
        'yandex_market',
    );

    /**
     * Create a validator instance for the given merchant.
     *
     * @since 7.4.58
     * @access public
     * @param  string $merchant The merchant name (e.g., 'google', 'facebook').
     * @param  int    $feed_id  The feed ID (optional).
     * @return Rex_Feed_Abstract_Validator|null The validator instance or null if not supported.
     */
    public static function create( $merchant, $feed_id = 0 ) {
        $merchant = self::normalize_merchant_name( $merchant );

        if ( ! self::is_supported( $merchant ) ) {
            return null;
        }

        $class_name = self::$validator_map[ $merchant ];

        if ( ! class_exists( $class_name ) ) {
            self::load_validator_class( $merchant );
        }

        if ( class_exists( $class_name ) ) {
            return new $class_name( $feed_id );
        }

        return null;
    }

    /**
     * Check if a merchant has validator support.
     *
     * @since 7.4.58
     * @access public
     * @param  string $merchant The merchant name.
     * @return bool
     */
    public static function is_supported( $merchant ) {
        $merchant = self::normalize_merchant_name( $merchant );
        return in_array( $merchant, self::$supported_merchants, true );
    }

    /**
     * Get list of supported merchants.
     *
     * @since 7.4.58
     * @access public
     * @return array
     */
    public static function get_supported_merchants() {
        return self::$supported_merchants;
    }

    /**
     * Normalize merchant name for consistency.
     *
     * @since 7.4.58
     * @access protected
     * @param  string $merchant The merchant name.
     * @return string
     */
    protected static function normalize_merchant_name( $merchant ) {
        $merchant = strtolower( trim( $merchant ) );
        $merchant = str_replace( array( ' ', '-' ), '_', $merchant );
        return $merchant;
    }

    /**
     * Load the validator class file for a merchant.
     *
     * @since 7.4.58
     * @access protected
     * @param  string $merchant The merchant name.
     * @return void
     */
    protected static function load_validator_class( $merchant ) {
        // Map normalized merchant names to file names
        $file_map = array(
            'google'                 => 'google',
            'google_shopping'        => 'google',
            'google_local'           => 'google',
            'google_local_inventory' => 'google',
            'facebook'               => 'facebook',
            'facebook_marketplace'   => 'facebook',
            'instagram'              => 'instagram',
            'instagram_shopping'     => 'instagram',
            'openai'                 => 'openai',
            'openai_commerce'        => 'openai',
            'tiktok'                 => 'tiktok',
            'tiktok_ads'             => 'tiktok',
            'tiktok_catalog'         => 'tiktok',
            'pinterest'              => 'pinterest',
            'pinterest_catalog'      => 'pinterest',
            'pinterest_ads'          => 'pinterest',
            'yandex'                 => 'yandex',
            'yandex_yml'             => 'yandex',
            'yandex_direct'          => 'yandex',
            'yandex_market'          => 'yandex',
        );

        $file_name = isset( $file_map[ $merchant ] ) ? $file_map[ $merchant ] : $merchant;
        $file_path = plugin_dir_path( __FILE__ ) . 'class-rex-feed-validator-' . $file_name . '.php';

        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }

    /**
     * Create validator from feed ID by detecting the merchant.
     *
     * @since 7.4.58
     * @access public
     * @param  int $feed_id The feed ID.
     * @return Rex_Feed_Abstract_Validator|null The validator instance or null if not supported.
     */
    public static function create_from_feed( $feed_id ) {
        $merchant = get_post_meta( $feed_id, '_rex_feed_merchant', true );

        if ( empty( $merchant ) ) {
            $merchant = get_post_meta( $feed_id, 'rex_feed_merchant', true );
        }

        if ( empty( $merchant ) ) {
            return null;
        }

        return self::create( $merchant, $feed_id );
    }

    /**
     * Register a custom validator for a merchant.
     *
     * Allows plugins/themes to register their own validators.
     *
     * @since 7.4.58
     * @access public
     * @param  string $merchant   The merchant name.
     * @param  string $class_name The fully qualified class name.
     * @return bool
     */
    public static function register_validator( $merchant, $class_name ) {
        $merchant = self::normalize_merchant_name( $merchant );

        if ( ! class_exists( $class_name ) ) {
            return false;
        }

        // Verify the class extends the abstract validator
        if ( ! is_subclass_of( $class_name, 'Rex_Feed_Abstract_Validator' ) ) {
            return false;
        }

        self::$validator_map[ $merchant ]      = $class_name;
        self::$supported_merchants[]           = $merchant;
        self::$supported_merchants             = array_unique( self::$supported_merchants );

        return true;
    }

    /**
     * Unregister a validator for a merchant.
     *
     * @since 7.4.58
     * @access public
     * @param  string $merchant The merchant name.
     * @return bool
     */
    public static function unregister_validator( $merchant ) {
        $merchant = self::normalize_merchant_name( $merchant );

        if ( isset( self::$validator_map[ $merchant ] ) ) {
            unset( self::$validator_map[ $merchant ] );
            self::$supported_merchants = array_values(
                array_diff( self::$supported_merchants, array( $merchant ) )
            );
            return true;
        }

        return false;
    }
}
