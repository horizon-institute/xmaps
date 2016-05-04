<?php
/**
 * Custom post types.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

/**
 * Custom post types.
 */
class XMapsPostType {

	/**
	 * Adds custom post type meta boxes (currently map) to
	 * custom post type pages.
	 */
	public static function add_meta_boxes() {
		if ( get_option( 'xmaps-google-maps-api-key' ) === false ) {
			add_meta_box( 'xmaps-location', 'Location', function() {
				require 'fragments/post-type/nolocation.php';
			}, 'map-object', 'advanced' );
		} else {
			add_meta_box( 'xmaps-location', 'Location', function() {
				require 'fragments/post-type/location.php';
			}, 'map-object', 'advanced' );
		}
	}

	/**
	 * Register custom post types.
	 */
	public static function register_post_types() {
		XMapsPostType::register_map_object_type();
	}

	/**
	 * Register Map Object custom post type.
	 */
	private static function register_map_object_type() {
		$labels = array(
			'name' => 'Map Objects',
			'singular_name' => 'Map Object',
			'menu_name' => 'Map Objects',
			'parent_item_colon' => 'Parent Map Object:',
			'all_items' => 'All Map Objects',
			'view_item' => 'View Map Object',
			'add_new_item' => 'Add New Map Object',
			'add_new' => 'New Map Object',
			'edit_item' => 'Edit Map Object',
			'update_item' => 'Update Map Object',
			'search_items' => 'Search map objects',
			'not_found' => 'No map objects found',
			'not_found_in_trash' => 'No map objects found in Trash',
		);
		$rewrite = array(
			'slug' => 'map-object',
			'with_front' => false,
			'pages' => false,
			'feeds' => true,
		);
		$args = array(
			'labels' => $labels,
			'supports' => array(
				'title',
				'author',
				'editor',
				'custom-fields',
				'comments',
			),
			'taxonomies' => array( 'category', 'post_tag' ),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'show_in_admin_bar' => true,
			'menu_position' => 5,
			'can_export' => true,
			'has_archive' => true,
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'rewrite' => $rewrite,
			'capability_type' => 'post',
		);
		register_post_type( 'map-object', $args );
	}
}
?>
