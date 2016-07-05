<?php
/**
 * Geographic utility functions.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

use Location\Coordinate;
use Location\Distance\Vincenty;
use Location\Line;
use Location\Bearing\BearingEllipsoidal;

/**
 * Geographic utility functions.
 */
class XMapsGeo {

	/**
	 * Calculates the distance between 2 geometries in meters.
	 *
	 * @param Geometry $point_a The first point.
	 * @param Geometry $point_b The second point.
	 * @return float Distance in meters.
	 */
	public static function distance( $point_a, $point_b ) {
		return self::apply( $point_a, $point_b, function( $coor_a, $coor_b ) {
			$calculator = new Vincenty();
			return $calculator->getDistance( $coor_a, $coor_b );
		} );
	}

	/**
	 * Calculates the bearing between 2 geometries.
	 *
	 * @param Geometry $point_a The first point.
	 * @param Geometry $point_b The second point.
	 * @return float Bearing in degrees.
	 */
	public static function bearing( $point_a, $point_b ) {
		return self::apply( $point_a, $point_b, function( $coor_a, $coor_b ) {
			$line = new Line( $coor_a, $coor_b );
			return $line->getBearing( new BearingEllipsoidal() );
		} );
	}

	/**
	 * Calculates the centers of two points and
	 * applies the function to the two points,
	 *
	 * @param Geometry $point_a The first point.
	 * @param Geometry $point_b The second point.
	 * @param closure  $f Function to apply.
	 * @return float Result of application of $f.
	 */
	private static function apply( $point_a, $point_b, $f ) {
		$center_a = $point_a->centroid();
		$center_b = $point_b->centroid();
		$coor_a = new Coordinate( $center_a->y(), $center_a->x() );
		$coor_b = new Coordinate( $center_b->y(), $center_b->x() );
		return $f( $coor_a, $coor_b );
	}
}
?>
