/**
 * XMap.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

var XMAPS = XMAPS || {}

XMAPS.ObjectXMap = function( element ) {
	jQuery( function( $ ) {

		var mapconf = {
			"center": new google.maps.LatLng( 0, 0 ),
			"streetViewControl": false,
			"zoom": 1,
			"minZoom": 1,
			"mapTypeId": google.maps.MapTypeId.TERRAIN,
			"panControl": true,
			"mapTypeControl": true
		};

		var clusterconf = {
			"gridSize": 100,
			"averageCenter": true,
			"minimumClusterSize": 1,
			"zoomOnClick": false,
			"imagePath" : XMAPS.pluginurl + "images/m"
		};

		var map = new google.maps.Map( element.get( 0 ), mapconf );
		var clusterer = new MarkerClusterer( map, [], clusterconf );
		google.maps.event.addListener( clusterer, "clusterclick", function( cluster ) {
			var dialog = $( document.createElement( 'div' ) );
			dialog.attr( "id", "xmaps-map-overlay" );
			var ul = $( document.createElement( 'ul' ) );
			dialog.append( ul );
			dialog.css( {
				"overflow" : "scroll"
			} );
			dialog.dialog( {
				"modal" : true,
				"width" : ( $( window ).width() * 0.9 ),
				"height" : ( $( window ).height() * 0.9 )
			} );
			$.each(cluster.getMarkers(), function(i, e) {
				var mo = e.map_object;
				ul.append( $("<li><a href=\"" + mo.permalink
				+ "\">" + mo.post_title + "</a></li>") );
			});
		} );

		(function() {

			var runOnce = new XMaps.RunOnce(XMAPS.pluginurl
						+ "js/xmaps-do-post.js",
	            function(data, callback) {
					$.getJSON( data, function( j ) {
						callback( j );
					} );
				}
			);

			map.addListener( 'idle', function() {
				var bounds = map.getBounds();
				runOnce.queueTask({
					"url" : XMAPS.ajaxurl,
					"requestdata" : $.param( {
						"action" : "xmaps.get_map_objects_in_bounds",
						"data" : {
							"north" : bounds.getNorthEast().lat(),
							"east" : bounds.getNorthEast().lng(),
							"south" : bounds.getSouthWest().lat(),
							"west" : bounds.getSouthWest().lng()
						}
					} ),
					"contenttype" :
						"application/x-www-form-urlencoded; charset=utf-8"},
					function() { },
					function( data ) {
						clusterer.clearMarkers();
						var markers = [];
						$.each(data, function( i, e ) {
							var wkt = new Wkt.Wkt();
							wkt.read( e.location );
							var m = wkt.toObject();
							m.map_object = e;
							if ( m instanceof google.maps.Polygon ) {
								var bounds = new google.maps.LatLngBounds()
								m.getPath().forEach( function( element, index ) { bounds.extend( element ) } );
								m = new google.maps.Marker({
									"position" : bounds.getCenter()
								});
							}
							markers.push( clusterer.addMarker( m ) );
						});
					}
				);
			} );

		} )();

	} );
};
