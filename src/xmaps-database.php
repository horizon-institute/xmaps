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

	const LOCATION_TABLE_SUFFIX = 'map_object_locations';

	/**
	 * Creates custom tables for the specified blog.
	 *
	 * @param integer $blog_id Blog id number.
	 */
	public static function create_tables( $blog_id ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( $blog_id )
		. self::LOCATION_TABLE_SUFFIX;
		$sql = "
		CREATE TABLE $tbl_name (
		id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		reference_id BIGINT(20) NOT NULL,
		reference_type VARCHAR(20) NOT NULL,
		location GEOMETRY
		);";
		dbDelta( $sql, true );
	}

	/**
	 * Gets map object locations.
	 *
	 * @param integer $reference_id Map object reference id.
	 * @return array Map object locations.
	 */
	public static function get_map_object_locations( $reference_id ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( $blog_id )
		. self::LOCATION_TABLE_SUFFIX;
		return $wpdb->get_results( $wpdb->prepare(
			'SELECT id, reference_id, reference_type, 
			ST_AsText(location) AS location
		FROM %s WHERE reference_id = %d',
		array( $tbl_name, $reference_id ) ), OBJECT );
	}

	/**
	 * Adds or updates a map object location.
	 *
	 * @param integer $reference_id Map object reference id.
	 * @param string  $reference_type Map object reference type.
	 * @param string  $location Location formatted as well known text.
	 */
	public static function add_or_update_map_object_location(
			$reference_id,
			$reference_type,
			$location ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( $blog_id )
		. self::LOCATION_TABLE_SUFFIX;
		$wpdb->delete( $tbl_name, array( 'reference_id' => $reference_id ),
		array( '%d' ) );
		if ( empty( $location ) ) {
			return;
		}
		$wpdb->query( $wpdb->prepare(
			'INSERT INTO %s (reference_id, reference_type, location)
		VALUES (%d, \'%s\', ST_GeomFromText(\'%s\'))',
		array( $tbl_name, $reference_id, $reference_type, $location ) ) );
	}

	/**
	 * Searches for map object within specified bounds.
	 *
	 * @param float $north Northmost bound.
	 * @param float $south Southmost bound.
	 * @param float $east Eastmost bound.
	 * @param float $west Westmost bound.
	 */
	public static function get_map_objects_in_bounds(
			$north, $south, $east, $west ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( $blog_id )
		. self::LOCATION_TABLE_SUFFIX;
		$area = "POLYGON(($west $south, $east $south, 
		$east $north, $west $north, $west $south))";
		return $wpdb->get_results( $wpdb->prepare(
			'SELECT id, reference_id, reference_type, 
			ST_AsText(location) AS location
			FROM %s WHERE ST_Intersects(location, 
		ST_GeomFromText(POLYGON((%F %F, %F %F, 
		%F %F, %F %F, %F %F))))',
			array(
				$tbl_name,
				$west,
				$south,
				$east,
				$south,
				$east,
				$north,
				$west,
				$north,
				$west,
				$south,
			)
		), OBJECT );
	}
}
?>
