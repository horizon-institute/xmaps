<?php
/**
 * Plugin entry point.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

/*
 * Plugin Name: xMaps
 * Plugin URI: https://github.com/horizon-institute/xmaps
 * Description: A platform for crowd-sourcing of geo-located cultural information.
 * Version: 1.0
 * Author: Dominic Price
 * Author URI: https://github.com/dominicjprice
 * License: AGPL3
 * License URI: http://www.gnu.org/licenses/agpl-3.0.en.html
 */
if ( ! defined( 'ABSPATH' ) ) {
	throw new Exception( 'Access error' );
}

require_once 'lib/UUID.php';
use RobotSnowfall\UUID;
require_once 'xmaps-constants.php';
require_once 'xmaps-ajax.php';
require_once 'xmaps-database.php';
require_once 'xmaps-map-object.php';
require_once 'xmaps-post-type.php';
require_once 'xmaps-settings.php';

register_activation_hook( __FILE__,  function() {
	XMapsDatabase::create_tables( null );
	if ( is_multisite() ) {
		foreach ( wp_get_sites() as $site ) {
			XMapsDatabase::create_tables( $site['blog_id'] );
		}
	}
} );

add_action( 'wpmu_new_blog', function( $blog_id ) {
	XMapsDatabase::create_tables( $blog_id );
} );

add_action( 'init', function () {
	XMapsPostType::register_post_types();
	remove_role( 'xmapper' );
	add_role( 'xmapper', 'Mapper', array(
			'read' => true,
			'upload_files' => true,
			'publish_xmaps' => true,
			'read_private_xmaps' => false,
			'edit_xmaps' => true,
			'edit_published_xmaps' => true,
			'edit_others_xmaps' => false,
			'edit_private_xmaps' => true,
			'delete_xmaps' => true,
			'delete_published_xmaps' => true,
			'delete_others_xmaps' => false,
			'delete_private_xmaps' => true,
			'manage_xmaps_tags' => true,
	));
	global $wp_roles;
	$roles = $wp_roles->get_names();
	foreach ( $roles as $k => $v ) {
		if ( 'xmapper' == $k ) {
			continue;
		}
		$role = $wp_roles->get_role( $k );
		if ( $role->has_cap( 'publish_posts' ) ) {
			$role->add_cap( 'publish_xmaps' );
		}
		if ( $role->has_cap( 'read_private_posts' ) ) {
			$role->add_cap( 'read_private_xmaps' );
		}
		if ( $role->has_cap( 'edit_posts' ) ) {
			$role->add_cap( 'edit_xmaps' );
		}
		if ( $role->has_cap( 'edit_published_posts' ) ) {
			$role->add_cap( 'edit_published_xmaps' );
		}
		if ( $role->has_cap( 'edit_others_posts' ) ) {
			$role->add_cap( 'edit_others_xmaps' );
		}
		if ( $role->has_cap( 'edit_private_posts' ) ) {
			$role->add_cap( 'edit_private_xmaps' );
		}
		if ( $role->has_cap( 'delete_posts' ) ) {
			$role->add_cap( 'delete_xmaps' );
		}
		if ( $role->has_cap( 'delete_published_posts' ) ) {
			$role->add_cap( 'delete_published_xmaps' );
		}
		if ( $role->has_cap( 'delete_others_posts' ) ) {
			$role->add_cap( 'delete_others_xmaps' );
		}
		if ( $role->has_cap( 'delete_private_posts' ) ) {
			$role->add_cap( 'delete_private_xmaps' );
		}
		if ( $role->has_cap( 'manage_categories' ) ) {
			$role->add_cap( 'manage_xmaps_tags' );
		}
	}
}, 0 );

add_action( 'admin_init', function() {
	XMapsSettings::register_settings();
} );

add_action( 'admin_menu', function() {
	XMapsSettings::add_option_pages();
} );

add_action( 'add_meta_boxes', function() {
	XMapsPostType::add_meta_boxes();
} );

add_action( 'save_post_map-object', function( $post_id, $post, $update ) {
	XMapsMapObject::on_save_map_object( $post_id, $post, $update );
}, 10, 3 );

add_action( 'admin_enqueue_scripts', function() {
	$akey = get_option( 'xmaps-google-maps-api-key' );
	if ( $akey ) {
		wp_register_script(
			'xmaps-google-maps',
			"https://maps.googleapis.com/maps/api/js?key=$akey",
			false,
			'1.0.0',
		true );
		wp_register_script(
			'wicket',
			plugin_dir_url( __FILE__ ) . 'js/lib/wicket.js',
			false,
			'1.3.2',
		true );
		wp_register_script(
			'wicket-gmap3',
			plugin_dir_url( __FILE__ ) . 'js/lib/wicket-gmap3.js',
			array( 'wicket' ),
			'1.3.2',
		true );
		wp_enqueue_script( 'xmaps-google-maps' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wicket-gmap3' );
		wp_enqueue_style( 'xmaps-admin', plugin_dir_url( __FILE__ )
		. 'css/admin.css', false, '1.0.0' );
	}
} );

add_action( 'wp_enqueue_scripts', function() {
	$akey = get_option( 'xmaps-google-maps-api-key' );
	if ( $akey ) {
		wp_register_script(
			'xmaps-google-maps',
			"https://maps.googleapis.com/maps/api/js?key=$akey",
			false,
			'1.0.0',
		true );
		wp_register_script(
			'xmaps-xmap',
			plugin_dir_url( __FILE__ ) . 'js/xmap.js',
			array( 'xmaps-google-maps', 'jquery' ),
			'1.0.0',
		true );
		wp_localize_script( 'xmaps-xmap', 'XMAPS', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );
		wp_enqueue_script( 'xmaps-xmap' );

	}
} );

add_action('wp_ajax_xmaps.get_map_objects_in_bounds', function() {
	XMapsAJAX::get_map_objects_in_bounds();
} );

add_action('wp_ajax_nopriv_xmaps.get_map_objects_in_bounds', function() {
	XMapsAJAX::get_map_objects_in_bounds();
} );

add_shortcode( 'xmap', function( $attrs ) {
	$attrs = shortcode_atts( array(
			'width' => '100%',
			'height' => '480px',
	), $attrs );
	$uuid = UUID::v4();
	return '<div id="xmap-' . $uuid . '" style="width:' . $attrs['width']
			. '; height:' . $attrs['height'] . '"></div>
			<script>
			jQuery(function($) {
				XMAPS.XMap($("#xmap-' . $uuid . '"));
			});
			</script>
			';
} );

?>
