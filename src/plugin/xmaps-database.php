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

	const LOCATION_TABLE_SUFFIX = 'xmaps_object_locations';

	const COLLECTION_TABLE_SUFFIX = 'xmaps_collections';

	/**
	 * Creates custom tables for the specified blog.
	 *
	 * @param integer $blog_id Blog id number.
	 */
	public static function create_tables( $blog_id ) {
		self::create_location_table( $blog_id );
		self::create_collection_table( $blog_id );
	}

	/**
	 * Creates the location table for the specified blog.
	 *
	 * @param integer $blog_id Blog id number.
	 */
	private static function create_location_table( $blog_id ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( $blog_id )
		. self::LOCATION_TABLE_SUFFIX;
		$sql = "
		CREATE TABLE $tbl_name (
		id BIGINT(20) NOT NULL AUTO_INCREMENT,
		reference_id BIGINT(20) NOT NULL,
		reference_type VARCHAR(20) NOT NULL,
		location GEOMETRY,
		PRIMARY KEY  (id)
		);";
		dbDelta( $sql, true );
	}

	/**
	 * Creates the collection table for the specified blog.
	 *
	 * @param integer $blog_id Blog id number.
	 */
	private static function create_collection_table( $blog_id ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( $blog_id )
		. self::COLLECTION_TABLE_SUFFIX;
		$sql = "
		CREATE TABLE $tbl_name (
		id BIGINT(20) NOT NULL AUTO_INCREMENT,
		collection_id BIGINT(20) NOT NULL,
		map_object_id BIGINT(20) NOT NULL,
		PRIMARY KEY  (id)
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
		$tbl_name = $wpdb->get_blog_prefix( get_current_blog_id() )
		. self::LOCATION_TABLE_SUFFIX;
		$sql = 'SELECT id, reference_id, reference_type, 
			ST_AsText(location) AS location
		FROM ' . $tbl_name . ' WHERE reference_id = %d';
		return $wpdb->get_results( $wpdb->prepare($sql, // WPCS: unprepared SQL ok.
		array( $reference_id ) ), OBJECT );
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
		$tbl_name = $wpdb->get_blog_prefix( get_current_blog_id() )
		. self::LOCATION_TABLE_SUFFIX;
		$wpdb->delete( $tbl_name, array( 'reference_id' => $reference_id ),
		array( '%d' ) );
		if ( empty( $location ) ) {
			return;
		}
		$sql = 'INSERT INTO ' . $tbl_name .
			' (reference_id, reference_type, location)
		VALUES (%d, \'%s\', ST_GeomFromText(\'%s\'))';
		$wpdb->query( $wpdb->prepare( $sql, // WPCS: unprepared SQL ok.
		array( $reference_id, $reference_type, $location ) ) );
	}

	/**
	 * Searches for map object within specified bounds.
	 *
	 * @param float $north Northmost bound.
	 * @param float $east Eastmost bound.
	 * @param float $south Southmost bound.
	 * @param float $west Westmost bound.
	 */
	public static function get_map_objects_in_bounds(
			$north, $east, $south, $west ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( get_current_blog_id() )
		. self::LOCATION_TABLE_SUFFIX;
		$sql = 'SELECT id, reference_id, reference_type,
			ST_AsText(location) AS location
			FROM ' . $tbl_name . ' WHERE ST_Intersects(location, 
		ST_GeomFromText(\'POLYGON((%f %f, %f %f, 
		%f %f, %f %f, %f %f))\'))';
		return $wpdb->get_results( $wpdb->prepare($sql, // WPCS: unprepared SQL ok.
			array(
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

	/**
	 * Gets map object collection.
	 *
	 * @param integer $map_object_id Map object reference id.
	 * @return array Map object collections.
	 */
	public static function get_map_object_collections( $map_object_id ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( get_current_blog_id )
		. self::COLLECTION_TABLE_SUFFIX;
		$sql = 'SELECT collection_id FROM ' . $tbl_name . ' WHERE map_object_id = %d';
		$collection_ids = $wpdb->get_col( // WPCS: unprepared SQL ok.
			$wpdb->prepare( $sql, // WPCS: unprepared SQL ok.
			array( $map_object_id ) ), 0
		);
		$collections = get_posts( array(
				'posts_per_page'   => PHP_INT_MAX,
				'orderby'          => 'title',
				'order'            => 'DESC',
				'post_type'        => 'map-collection',
				'post_status'      => 'publish',
				'suppress_filters' => true,
		) );
		$result = array();
		foreach ( $collections as $collection ) {
			$result[] = array( $collection, in_array( $collection->ID, $collection_ids ) );
		}
		return $result;
	}

	/**
	 * Adds or updates map object collections.
	 *
	 * @param integer $map_object_id Map object reference id.
	 * @param array   $collection_ids Map collection IDs.
	 */
	public static function add_or_update_map_object_collections(
			$map_object_id,
			$collection_ids ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( get_current_blog_id() )
		. self::COLLECTION_TABLE_SUFFIX;
		$wpdb->delete( $tbl_name, array( 'map_object_id' => $map_object_id ),
		array( '%d' ) );
		if ( empty( $collection_ids )  || ! is_array( $collection_ids ) ) {
			return;
		}
		foreach ( $collection_ids as $id ) {
			$sql = 'INSERT INTO ' . $tbl_name .
			' (collection_id, map_object_id)
			VALUES (%d, %d)';
			$wpdb->query( $wpdb->prepare( $sql, // WPCS: unprepared SQL ok.
			array( $id, $map_object_id ) ) );
		}
	}

	/**
	 * Gets all map objects in a collection.
	 *
	 * @param integer $collection_id Collection ID.
	 * @return array Map objects in collection.
	 */
	public static function get_collection_map_objects( $collection_id ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( get_current_blog_id )
		. self::COLLECTION_TABLE_SUFFIX;
		$sql = 'SELECT map_object_id FROM ' . $tbl_name . ' WHERE collection_id = %d';
		$ids = $wpdb->get_col( // WPCS: unprepared SQL ok.
			$wpdb->prepare( $sql, // WPCS: unprepared SQL ok.
			array( $collection_id ) ), 0
		);
		$result = array();
		foreach ( $ids as $id ) {
			$result[] = array(
					get_post( $id ),
					self::get_map_object_locations( $id )[0],
			);
		}
		return $result;
	}
}
?>
