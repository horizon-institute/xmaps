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

	const LOCATION_LOG_TABLE_SUFFIX = 'xmaps_location_log';

	const FINDS_TABLE_SUFFIX = 'xmaps_finds';

	/**
	 * Creates custom tables for the specified blog.
	 *
	 * @param integer $blog_id Blog id number.
	 */
	public static function create_tables( $blog_id ) {
		self::create_location_table( $blog_id );
		self::create_collection_table( $blog_id );
		self::create_location_log_table( $blog_id );
		self::create_finds_table( $blog_id );
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
	 * Creates the location log table for the specified blog.
	 *
	 * @param integer $blog_id Blog id number.
	 */
	private static function create_location_log_table( $blog_id ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( $blog_id )
		. self::LOCATION_LOG_TABLE_SUFFIX;
		$sql = "
		CREATE TABLE $tbl_name (
		id BIGINT(20) NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) NOT NULL,
		timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		app_key VARCHAR(256) NOT NULL,
		collection_id BIGINT(20) NOT NULL,
		location GEOMETRY,
		accuracy FLOAT NOT NULL,
		PRIMARY KEY  (id)
		)";
		dbDelta( $sql, true );
	}

	/**
	 * Creates the finds table for the specified blog.
	 *
	 * @param integer $blog_id Blog id number.
	 */
	private static function create_finds_table( $blog_id ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( $blog_id )
		. self::FINDS_TABLE_SUFFIX;
		$sql = "
		CREATE TABLE $tbl_name (
		id BIGINT(20) NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) NOT NULL,
		timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		app_key VARCHAR(256) NOT NULL,
		post_id BIGINT(20) NOT NULL,
		PRIMARY KEY  (id)
		)";
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
		$sql = 'SELECT l.id, l.reference_id, l.reference_type,
			ST_AsText(l.location) AS location, p.post_title
			FROM ' . $tbl_name . ' l
			LEFT JOIN ' . $wpdb->posts . ' p ON p.id = l.reference_id
			WHERE ST_Intersects(l.location, 
			ST_GeomFromText(\'POLYGON((%f %f, %f %f, 
		%f %f, %f %f, %f %f))\'))';
		$results = $wpdb->get_results( $wpdb->prepare($sql, // WPCS: unprepared SQL ok.
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
		foreach ( $results as $result ) {
			$result->permalink = get_permalink( $result->reference_id );
		}
		return $results;
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

	/**
	 * Logs a location.
	 *
	 * @param integer $user_id User ID.
	 * @param string  $app_key User app key.
	 * @param integer $collection_id Collection ID.
	 * @param string  $location Location formatted as well known text.
	 * @param float   $accuracy Accuracy in meters.
	 */
	public static function log_location(
			$user_id, $app_key, $collection_id, $location, $accuracy ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( get_current_blog_id() )
		. self::LOCATION_LOG_TABLE_SUFFIX;
		$sql = 'INSERT INTO ' . $tbl_name .
			' (user_id, app_key, collection_id, location, accuracy)
		VALUES (%d, \'%s\', %d, ST_GeomFromText(\'%s\'), %f)';
		$wpdb->query( $wpdb->prepare( $sql, // WPCS: unprepared SQL ok.
		array( $user_id, $app_key, $collection_id, $location, $accuracy ) ) );
	}

	/**
	 * Logs a find.
	 *
	 * @param integer $user_id User ID.
	 * @param string  $app_key User app key.
	 * @param integer $post_id Post ID.
	 */
	public static function log_find( $user_id, $app_key, $post_id ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( get_current_blog_id() )
		. self::FINDS_TABLE_SUFFIX;
		$sql = 'INSERT INTO ' . $tbl_name .
			' (user_id, app_key, post_id) VALUES (%d, \'%s\', %d)';
		$wpdb->query( $wpdb->prepare( $sql, // WPCS: unprepared SQL ok.
		array( $user_id, $app_key, $post_id ) ) );
	}

	/**
	 * Gets the 'find' history for a user within a time period.
	 *
	 * @param integer $user_id User ID.
	 * @param integer $period Time period in minutes.
	 * @return array User's 'find' history.
	 */
	public static function get_find_history( $user_id, $period ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( get_current_blog_id )
		. self::FINDS_TABLE_SUFFIX;

		$sql = 'SELECT 
		p.id as ID,
		p.post_title,
		p.post_author as author_id,
		u.display_name,
		p.post_date_gmt,
		f.timestamp as found
		FROM ' . $tbl_name . ' f
		LEFT JOIN ' . $wpdb->posts . ' p ON f.post_id = p.id
		LEFT JOIN ' . $wpdb->users . ' u ON p.post_author = u.id
		WHERE f.user_id = %d
		AND f.timestamp >= DATE_SUB(NOW(), INTERVAL %d MINUTE)
		AND p.post_type = \'map-object\'
		AND p.post_status = \'publish\'
		ORDER BY p.post_date DESC';
		$results = $wpdb->get_results( // WPCS: unprepared SQL ok.
			$wpdb->prepare( $sql, // WPCS: unprepared SQL ok.
			array( $user_id, $period ) )
		);
		return $results;
	}

	/**
	 * Determines whether a post has been found by a user.
	 *
	 * @param integer $post_id Post ID.
	 * @param integer $user_id User ID.
	 * @param integer $period Time period to search in minutes.
	 * @return mixed False if the post has not been found,
	 * most recent timestamp otherwise
	 */
	public static function has_post_been_found( $post_id, $user_id, $period ) {
		global $wpdb;
		$tbl_name = $wpdb->get_blog_prefix( get_current_blog_id )
		. self::FINDS_TABLE_SUFFIX;
		$sql = 'SELECT timestamp from ' . $tbl_name . ' WHERE
		post_id = %d AND user_id = %d 
		AND timestamp >= DATE_SUB(NOW(), INTERVAL %d MINUTE)
		ORDER BY timestamp DESC LIMIT 1';
		return $wpdb->get_var( // WPCS: unprepared SQL ok.
			$wpdb->prepare( $sql, // WPCS: unprepared SQL ok.
			array( $post_id, $user_id, $period ) )
		);
	}
}
?>
