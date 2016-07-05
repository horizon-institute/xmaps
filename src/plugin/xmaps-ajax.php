<?php
/**
 * AJAX functions.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

/**
 * AJAX functions.
 */
class XMapsAJAX {

	/**
	 * Searches for map object within specified bounds.
	 */
	public static function get_map_objects_in_bounds() {
		$north = stripslashes_deep( $_REQUEST['data']['north'] );
		$east = stripslashes_deep( $_REQUEST['data']['east'] );
		$south = stripslashes_deep( $_REQUEST['data']['south'] );
		$west = stripslashes_deep( $_REQUEST['data']['west'] );
		header( 'Content-Type: application/json' );
		echo json_encode(
			XMapsDatabase::get_map_objects_in_bounds(
			$north, $east, $south, $west)
		);
		exit;

	}
}
?>
