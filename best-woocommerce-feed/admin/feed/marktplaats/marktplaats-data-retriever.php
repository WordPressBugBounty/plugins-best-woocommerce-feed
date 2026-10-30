<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class for retriving product data based on user selected feed configuration.
 *
 * Get the product data based on feed config selected by user.
 *
 * @package    Rex_Product_Marktplaats_Data_Retriever
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Product_Marktplaats_Data_Retriever extends Rex_Product_Data_Retriever {


	/**
	 * Retrieve and setup all data for every feed rule.
	 *
	 * @since 2.5
	 * @since 7.14.2 Explode comma-separated media URLs into individual entries.
	 *
	 * @return void
	 */
	public function set_all_value(): void {
		$this->data = array();
		foreach ( $this->feed_config as $rule ) {
			if ( isset( $rule['attr'] ) && 'media' === $rule['attr'] ) {
				$value = $this->set_val( $rule );
				if ( is_array( $value ) ) {
					$urls = array_filter( array_map( 'trim', $value ) );
				} elseif ( is_string( $value ) && '' !== $value ) {
					$urls = array_filter( array_map( 'trim', explode( ',', $value ) ) );
				} else {
					$urls = array();
				}
				foreach ( $urls as $url ) {
					$this->data['media'][] = $url;
				}
			} elseif ( isset( $rule['attr'] ) ) {
				$this->data[ $rule['attr'] ] = $this->set_val( $rule );
			}
		}
	}


    public function get_random_key() {
        return md5(uniqid(rand(), true));
    }
}