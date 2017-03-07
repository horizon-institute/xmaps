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
			}, 'map-object', 'normal', 'high' );
		} else {
			add_meta_box( 'xmaps-location', 'Location', function() {
				require 'fragments/post-type/location.php';
			}, 'map-object', 'normal', 'high' );
		}

		add_meta_box( 'xmaps-collections', 'Collections',
			function( $post ) {
					$collections = XMapsDatabase::get_map_object_collections(
					$post->ID );
				require 'fragments/post-type/collections.php';
			},
		'map-object', 'side', 'default' );
	}

	/**
	 * Register custom post types.
	 */
	public static function register_post_types() {
		self::register_xmaps_taxonomy();
		self::register_map_object_type();
		self::register_map_collection_type();
	}

	/**
	 * Register a new taxonomy for map objects and collections.
	 */
	private static function register_xmaps_taxonomy() {
		$labels = array(
				'name'	=> 'Tags',
				'singular_name' => 'Tag',
				'search_items' => 'Search Tags',
				'popular_items' => 'Popular Tags',
				'all_items' => 'All Tags',
				'parent_item' => null,
				'parent_item_colon' => null,
				'edit_item' => 'Edit Tag',
				'update_item' => 'Update Tag',
				'add_new_item' => 'Add New Tag',
				'new_item_name' => 'New Tag Name',
				'separate_items_with_commas' => 'Separate tags with commas',
				'add_or_remove_items' => 'Add or remove tags',
				'choose_from_most_used' => 'Choose from the most used tags',
				'not_found' => 'No tags found.',
				'menu_name' => 'Tags',
		);

		$args = array(
				'hierarchical'          => false,
				'labels'                => $labels,
				'show_ui'               => true,
				'show_in_menu'			=> true,
				'show_admin_column'     => true,
				'query_var'             => true,
				'rewrite'               => array( 'slug' => 'xmaps-tag' ),
				'capabilities'			=> array(
						'manage_terms' => 'manage_categories',
						'edit_terms' => 'manage_xmaps_tags',
						'delete_terms' => 'manage_xmaps_tags',
						'assign_terms' => 'manage_xmaps_tags',
				),
		);
		register_taxonomy( 'xmap_tags', null, $args );
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
				'excerpt',
				'thumbnail',
			),
			'taxonomies' => array( 'xmap_tags', 'category' ),
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
			'capability_type' => array( 'xmap', 'xmaps' ),
			'map_meta_cap' => true,
		);
		register_post_type( 'map-object', $args );
	}

	/**
	 * Register Map Collection custom post type.
	 */
	private static function register_map_collection_type() {
		$labels = array(
			'name' => 'Map Collections',
			'singular_name' => 'Map Collection',
			'menu_name' => 'Map Collections',
			'parent_item_colon' => 'Parent Map Collection:',
			'all_items' => 'All Map Collections',
			'view_item' => 'View Map Collections',
			'add_new_item' => 'Add New Map Collection',
			'add_new' => 'New Map Collection',
			'edit_item' => 'Edit Map Collection',
			'update_item' => 'Update Map Collection',
			'search_items' => 'Search map collections',
			'not_found' => 'No map collections found',
			'not_found_in_trash' => 'No map collections found in Trash',
		);
		$rewrite = array(
			'slug' => 'map-collection',
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
				'excerpt',
				'thumbnail',
			),
			'taxonomies' => array( 'xmap_tags' ),
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
			'capability_type' => array( 'xmap', 'xmaps' ),
			'map_meta_cap' => true,
		);
		$r = register_post_type( 'map-collection', $args );
	}
}
?>
