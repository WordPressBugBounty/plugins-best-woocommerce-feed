<?php
/**
 * Generic Feed Validator
 *
 * Fallback validator for merchants without a dedicated custom validator.
 * Provides centralized mapping anomaly validation and baseline feed validation.
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
 * Generic validator for any merchant feed.
 *
 * @since 7.4.58
 */
class Rex_Feed_Validator_Generic extends Rex_Feed_Abstract_Validator {

    /**
     * Merchant name.
     *
     * @var string
     */
    protected $merchant;

    /**
     * Constructor.
     *
     * @param int    $feed_id  The feed ID.
     * @param string $merchant Merchant name (optional).
     */
    public function __construct( $feed_id = 0, $merchant = '' ) {
        if ( ! empty( $merchant ) ) {
            $this->merchant = $merchant;
        }
        parent::__construct( $feed_id );
    }

    /**
     * Initialize validation rules for generic merchant.
     *
     * @since 7.4.58
     * @access protected
     * @return void
     */
    protected function init_rules() {
        if ( empty( $this->merchant ) && $this->feed_id > 0 ) {
            $this->merchant = get_post_meta( $this->feed_id, '_rex_feed_merchant', true );
            if ( empty( $this->merchant ) ) {
                $this->merchant = get_post_meta( $this->feed_id, 'rex_feed_merchant', true );
            }
        }

        // Try to load merchant template to extract any required attributes
        if ( ! empty( $this->merchant ) ) {
            $template = $this->get_merchant_template();
            if ( $template && method_exists( $template, 'get_default_template_mappings' ) ) {
                $default_mappings = $template->get_default_template_mappings();
                if ( is_array( $default_mappings ) ) {
                    foreach ( $default_mappings as $mapping ) {
                        if ( isset( $mapping['attr'] ) && ! empty( $mapping['attr'] ) && ! isset( $this->required_attributes[ $mapping['attr'] ] ) ) {
                            // Standard default mapped attributes
                            $this->required_attributes[ $mapping['attr'] ] = array(
                                'severity'    => self::SEVERITY_WARNING,
                                'description' => $mapping['attr'],
                            );
                        }
                    }
                }
            }
        }
    }
}
