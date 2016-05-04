<?php
/**
 * Map Object custom post type.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

/**
 * Map Object custom post type.
 */
class XMapsMapObject {

	/**
	 * Save post metadata when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
	public static function on_save_map_object( $post_id, $post, $update ) {
		XMapsDatabase::add_or_update_map_object_location(
				$post_id, 'map-object', $_REQUEST['xmaps-location-entry'] );
	}

}
?>
