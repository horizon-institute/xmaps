/**
 * XMap.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

var XMAPS = XMAPS || {}

XMAPS.XMap = function( element ) {
	jQuery( function( $ ) {
		var conf = {
			"center": new google.maps.LatLng( 0, 0 ),
			"streetViewControl": false,
			"zoom": 1,
			"minZoom": 1,
			"maxZoom": 20,
			"mapTypeId": google.maps.MapTypeId.TERRAIN,
			"panControl": true,
			"mapTypeControl": true
		};
		var map = new google.maps.Map( element.get( 0 ), conf );

		$.post( XMAPS.ajaxurl, {
			"action" : "xmaps.get_map_objects_in_bounds",
			"data" : {
				"north" : 90,
				"east" : 180,
				"south" : -90,
				"west" : -180
			} }, function( data ) {

			},
		"json" );
	} );
};
