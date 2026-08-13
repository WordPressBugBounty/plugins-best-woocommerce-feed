<?php
/**
 * The Rex_Feed_Template_Factory class file that
 * returns a feed template class for feed configuration of various merchants.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Feed_Template_Factory
 * @subpackage admin/
 */

/**
 * The Rex_Feed_Template_Factory class file that
 * returns a feed template class for feed configuration of various merchants.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Feed_Template_Factory
 */
class Rex_Feed_Template_Factory {

	/**
	 * Build dynamic class names for feed merchant
	 *
	 * @param string $merchant Merchant name.
	 * @param array  $attribute_mappings Attribute mappings.
	 * @return mixed
	 * @throws Exception Throws exception if no matching merchant found.
	 */
	public static function build( $merchant, $attribute_mappings ) {
		$requested_merchant = $merchant;
        $merchant           = 'bing' === $merchant ? 'google' : $merchant;
        $class_name = 'Rex_Feed_Template_' . ucfirst( str_replace( ' ', '', $merchant ) );

		if ( !$merchant || ! class_exists( $class_name ) ) {
			throw new Exception( 'Invalid Merchant.' );
		} else {
			$template = new $class_name( $attribute_mappings );

			if ( 'bing' === $requested_merchant && method_exists( $template, 'remove_question_and_answer_attributes' ) ) {
				$template->remove_question_and_answer_attributes();
			}

			return $template;
		}
	}
}
