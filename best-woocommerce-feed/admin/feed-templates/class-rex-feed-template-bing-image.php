<?php
/**
 * Class Rex_Feed_Template_Bing_image
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Bing_image
 * @author     RexTheme <info@rextheme.com>
 */

/**
 * Defines the attributes and template for Bing image feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Bing_image
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Bing_image extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Attributes' => array(
				'hostPageUrl'   => 'Host page [link]',
				'contentUrl'    => 'Main Image [link]',
				'image_height'  => 'Image height',
				'image_width'   => 'Image width',
				'datePublished' => 'Published date',
			),
			'Optional Attributes' => array(
				'image_name'        => 'Image Title',
				'image_author'      => 'Image author',
				'image_description' => 'Image description/caption',
				'image_format'      => 'Image file format',
				'image_size_bytes'  => 'Image size [bytes]',
				'image_thumbnail'   => 'Thumbnail image [url]',
				'family_friendly'   => 'Family friendly',
				'keywords'          => 'Keywords',
				'category'          => 'Category',
				'modified_date'     => 'Modified date',
			),
		);
	}

	/**
	 * Define merchant's default attributes
	 *
	 * @return void
	 */
	protected function init_default_template_mappings() {
		$this->template_mappings = array(
			array(
				'attr'     => 'hostPageUrl',
				'type'     => 'meta',
				'meta_key' => 'current_page',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'contentUrl',
				'type'     => 'meta',
				'meta_key' => 'main_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'image_height',
				'type'     => 'meta',
				'meta_key' => 'height',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'image_width',
				'type'     => 'meta',
				'meta_key' => 'width',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'datePublished',
				'type'     => 'static',
				'meta_key' => 'id',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
		);
	}
}
