<?php
/**
 * Wander/anywhere API implementation.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

/**
 * Wander/anywhere API functions.
 */
class XMapsWAAPI {

	/**
	 * Parses an API request and calls the correct API function.
	 *
	 * @param WP $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	public static function parse_request( $wp ) {
		if ( ! self::check_api_key( $wp ) ) {
			return false;
		}
		$parts = explode( '/', $wp->request );
		switch ( $parts[1] ) {
			case 'collections' :
				return self::collections( $wp );
			case 'posts' :
				return self::posts( $wp );
			case 'content' :
				return self::content( $wp );
			case 'find' :
				return self::find( $wp );
			case 'history' :
				return self::history( $wp );
			case 'sun' :
				return self::sun( $wp );
			case 'location' :
				return self::location( $wp );
			default : return false;
		}
	}

	/**
	 * Checks that the API key in the request is valid.
	 *
	 * @param WP $wp Wordpress object.
	 * @return boolean True if the key is present and valid.
	 */
	private static function check_api_key( $wp ) {
		$key = $wp->query_vars['key'];
		if ( empty( $key ) ) {
			return false;
		}

		$user = XMapsUser::get_user_by_api_key( $key );
		if ( false === $user ) {
			return false;
		}

		return true;
	}

	/**
	 * Parses collection requests and determines the correct function to call.
	 *
	 * @param WP $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	private static function collections( $wp ) {
		if ( isset( $wp->query_vars['id'] )
				&& isset( $wp->query_vars['lat'] )
				&& isset( $wp->query_vars['lon'] ) ) {
			return self::collection_with_proximity(
				$wp->query_vars['id'],
				$wp->query_vars['lat'],
				$wp->query_vars['lon'],
			$wp );
		} else if ( isset( $wp->query_vars['id'] ) ) {
			return self::collection( $wp->query_vars['id'], $wp );
		} else if ( isset( $wp->query_vars['lat'] )
				&& isset( $wp->query_vars['lon'] ) ) {
			return self::all_collections_by_proximity(
				$wp->query_vars['lat'],
				$wp->query_vars['lon'],
			$wp );
		} else {
			return self::all_collections( $wp );
		}
	}

	/**
	 * Get a specific collection, including proximity.
	 *
	 * @param integer $id Collection ID.
	 * @param float   $lat Origin latitude.
	 * @param float   $lon Origin longitude.
	 * @param WP      $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	private static function collection_with_proximity( $id, $lat, $lon, $wp ) {
		$post = get_post( $id );
		if ( ! $post ) {
			return false;
		}
		$e = $c->post_content;
		if ( ! empty( $post->post_excerpt ) ) {
			$e = $post->post_excerpt;
		}
		$r = array(
				'term_id' => $post->ID,
				'name' => $post->post_title,
				'slug' => $post->post_name,
				'description' => wp_strip_all_tags(
				apply_filters( 'the_excerpt', $e ) ),
		);
		$map_objects = XMapsDatabase::get_collection_map_objects( $post->ID );
		$origin = new Point( $lon, $lat );
		$origin->setSRID( XMAPS_SRID );
		usort( $map_objects, function( $a, $b ) use ( $origin ) {
			$geo_a = geoPHP::load( $a[1]->location );
			$geo_a->setSRID( XMAPS_SRID );
			$geo_b = geoPHP::load( $b[1]->location );
			$geo_b->setSRID( XMAPS_SRID );
			$dist_a = XMapsGeo::distance( $geo_a, $origin );
			$dist_b = XMapsGeo::distance( $geo_b, $origin );
			return $dist_a - $dist_b;
		} );
		$dest_obj = $map_objects[0];
		$dest = geoPHP::load( $dest_obj[1]->location );
		$dest->setSRID( XMAPS_SRID );
		$r['distance'] = XMapsGeo::distance( $origin, $dest );
		$r['bearing'] = XMapsGeo::bearing( $origin, $dest );
		header( 'Content-Type: application/json' );
		echo json_encode( array(
				'data' => array( $r ),
				'request' => '/' . $wp->request . '?'. $_SERVER['QUERY_STRING'],
		) );
		return true;
	}

	/**
	 * Get a specific collection.
	 *
	 * @param integer $id Collection ID.
	 * @param WP      $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	private static function collection( $id, $wp ) {
		$post = get_post( $id );
		if ( ! $post ) {
			return false;
		}
		$e = $c->post_content;
		if ( ! empty( $post->post_excerpt ) ) {
			$e = $post->post_excerpt;
		}
		$r = array(
				'term_id' => $post->ID,
				'name' => $post->post_title,
				'slug' => $post->post_name,
				'description' => wp_strip_all_tags(
				apply_filters( 'the_excerpt', $e ) ),
		);
		header( 'Content-Type: application/json' );
		echo json_encode( array(
				'data' => array( $r ),
				'request' => '/' . $wp->request . '?'. $_SERVER['QUERY_STRING'],
		) );
		return true;
	}

	/**
	 * Get all collections.
	 *
	 * @param WP $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	private static function all_collections( $wp ) {
		$cs = get_posts( array(
				'posts_per_page' => PHP_INT_MAX,
				'post_type' => 'map-collection',
				'order' => 'DESC',
				'orderby' => 'modified',
				'post_status' => 'publish',
		) );
		$r = array();
		foreach ( $cs as $c ) {
			$e = $c->post_content;
			if ( ! empty( $c->post_excerpt ) ) {
				$e = $c->post_excerpt;
			}
			$r[] = array(
					'term_id' => $c->ID,
					'name' => $c->post_title,
					'slug' => $c->post_name,
					'description' => wp_strip_all_tags(
					apply_filters( 'the_excerpt', $e ) ),
			);
		}
		header( 'Content-Type: application/json' );
		echo json_encode( array(
				'data' => $r,
				'request' => '/' . $wp->request . '?'. $_SERVER['QUERY_STRING'],
		) );
		return true;
	}

	/**
	 * Get all collections by proximity.
	 *
	 * @param float $lat Origin latitude.
	 * @param float $lon Origin longitude.
	 * @param WP    $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	private static function all_collections_by_proximity( $lat, $lon, $wp ) {
		$cs = get_posts( array(
				'posts_per_page' => PHP_INT_MAX,
				'post_type' => 'map-collection',
				'post_status' => 'publish',
		) );
		$r = array();
		foreach ( $cs as $c ) {
			$e = $c->post_content;
			if ( ! empty( $c->post_excerpt ) ) {
				$e = $c->post_excerpt;
			}
			$e = array(
					'term_id' => $c->ID,
					'name' => $c->post_title,
					'slug' => $c->post_name,
					'description' => wp_strip_all_tags(
					apply_filters( 'the_excerpt', $e ) ),
			);
			$map_objects = XMapsDatabase::get_collection_map_objects( $c->ID );
			$origin = new Point( $lon, $lat );
			$origin->setSRID( XMAPS_SRID );
			usort( $map_objects, function( $a, $b ) use ( $origin ) {
				$geo_a = geoPHP::load( $a[1]->location );
				$geo_a->setSRID( XMAPS_SRID );
				$geo_b = geoPHP::load( $b[1]->location );
				$geo_b->setSRID( XMAPS_SRID );
				$dist_a = XMapsGeo::distance( $geo_a, $origin );
				$dist_b = XMapsGeo::distance( $geo_b, $origin );
				return $dist_a - $dist_b;
			} );
			$dest_obj = $map_objects[0];
			$dest = geoPHP::load( $dest_obj[1]->location );
			$dest->setSRID( XMAPS_SRID );
			$e['distance'] = XMapsGeo::distance( $origin, $dest );
			$e['bearing'] = XMapsGeo::bearing( $origin, $dest );
			$r[] = $e;
		}
		usort( $r, function( $a, $b ) {
			return $a['distance'] - $b['distance'];
		} );
		header( 'Content-Type: application/json' );
		echo json_encode( array(
				'data' => $r,
				'request' => '/' . $wp->request . '?'. $_SERVER['QUERY_STRING'],
		) );
		return true;
	}

	/**
	 * To be implemented.
	 *
	 * @param WP $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	private static function posts( $wp ) {
		return true;
	}

	/**
	 * To be implemented.
	 *
	 * @param WP $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	private static function content( $wp ) {
		return true;
	}

	/**
	 * To be implemented.
	 *
	 * @param WP $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	private static function find( $wp ) {
		return true;
	}

	/**
	 * To be implemented.
	 *
	 * @param WP $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	private static function history( $wp ) {
		return true;
	}

	/**
	 * To be implemented.
	 *
	 * @param WP $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	private static function sun( $wp ) {
		return true;
	}

	/**
	 * Logs a user's location.
	 *
	 * @param WP $wp Wordpress object.
	 * @return boolean True if the request was handled.
	 */
	private static function location( $wp ) {
		if ( isset( $wp->query_vars['lat'] )
				&& isset( $wp->query_vars['lon'] )
				&& isset( $wp->query_vars['acc'] )
				&& isset( $wp->query_vars['user-id'] )
				&& isset( $wp->query_vars['collection-id'] ) ) {

			$collection = get_post( $wp->query_vars['collection-id'] );
			if ( ! $collection ) {
				return false;
			}

			$user = XMapsUser::get_user_by_api_key( $wp->query_vars['key'] );
			if ( $user->id != $wp->query_vars['user-id'] ) {
				return false;
			}

			$geom = new Point(
				$wp->query_vars['lon'],
			$wp->query_vars['lat'] );
			$geom->setSRID( XMAPS_SRID );
			$wkt = new WKT();
			$location = $wkt->write( $geom );
			XMapsDatabase::log_location(
				$user->id,
				$wp->query_vars['key'],
				$wp->query_vars['collection-id'],
				$location,
			floatval( $wp->query_vars['acc'] ) );
			return true;
		}
		return false;
	}
}
?>
