<?php
/**
 * The WhatsApp Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      7.4.65
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Rex_Feed_Template_Facebook' ) ) {
	require_once dirname( __FILE__ ) . '/class-rex-feed-template-facebook.php';
}

/**
 * Defines the attributes and template for WhatsApp Business Catalog feed.
 * WhatsApp Business catalogs run on Meta Commerce Manager using the same product data specifications as Facebook.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Whatsapp
 * @author     RexTheme <info@rextheme.com>
 * @since      7.4.65
 */
class Rex_Feed_Template_Whatsapp extends Rex_Feed_Template_Facebook {
}
