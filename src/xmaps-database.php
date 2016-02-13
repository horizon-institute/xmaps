<?php
/**
 * Database manipulation functionality.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

/**
 * Database manipulation functionality.
 */
class XMapsDatabase {

	/**
	 * Creates custom tables for the specified blog.
	 *
	 * @param integer $blog_id Blog id number.
	 */
	public static function create_tables( $blog_id ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$dbname = $wpdb->get_blog_prefix( $blog_id ) . 'map_object_locations';
		$sql = "
		CREATE TABLE $dbname (
		id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		reference_id BIGINT(20) NOT NULL,
		reference_type VARCHAR(20) NOT NULL,
		center POINT
		);";
		dbDelta( $sql, true );
	}
}
?>
