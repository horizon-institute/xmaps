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

require_once 'xmaps-constants.php';
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
?>
