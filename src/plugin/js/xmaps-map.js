/**
 * XMap.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

var XMAPS = XMAPS || {}

XMAPS.ObjectXMap = function( element, mapconf, clusterconf ) {
	jQuery( function( $ ) {

		if ( mapconf === undefined ) {
			mapconf = {};
		}

		if ( clusterconf === undefined ) {
			clusterconf = {};
		}

		mapconf = $.extend( {
			"center": new google.maps.LatLng( 0, 0 ),
			"streetViewControl": false,
			"zoom": 1,
			"minZoom": 1,
			"mapTypeId": google.maps.MapTypeId.TERRAIN,
			"panControl": true,
			"mapTypeControl": true
		}, mapconf );

		clusterconf = $.extend( {
			"gridSize": 100,
			"averageCenter": true,
			"minimumClusterSize": 1,
			"zoomOnClick": false,
			"imagePath" : XMAPS.pluginurl + "images/m"
		}, clusterconf );

		var map = new google.maps.Map( element.get( 0 ), mapconf );
		var clusterer = new MarkerClusterer( map, [], clusterconf );
		google.maps.event.addListener( clusterer, "clusterclick", function( cluster ) {
			if ( ! cluster.jbox ) {
				var ul = $( document.createElement( 'ul' ) );
				ul.addClass( "xmap-object-list" );
				$.each(cluster.getMarkers(), function(i, e) {
					var mo = e.map_object;
					ul.append( $("<li><a href=\"" + mo.permalink
					+ "\">" + mo.post_title + "</a></li>") );
				});
				cluster.jbox = new jBox( "Tooltip", {
					"content" : ul,
					"trigger" : "click",
					"closeOnClick" : "body",
					"closeButton" : "box",
					"animation" : "move",
					"zIndex" : 8000,
					"position" : {
						"x" : "left",
						"y" : "top"
					},
					"outside" : "y",
					"offset" : {
						"x" : 25
					},
					"target" : $( cluster.clusterIcon_.div_ )
				} );
			}
			cluster.jbox.toggle();
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
							if ( e.reference_type != "map-object" ) {
								return;
							}
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

XMAPS.CollectionXMap = function( element ) {
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
			if ( ! cluster.jbox ) {
				var ul = $( document.createElement( 'ul' ) );
				ul.addClass( "xmap-object-list" );
				$.each(cluster.getMarkers(), function(i, e) {
					var mo = e.map_object;
					ul.append( $("<li><a href=\"" + mo.permalink
					+ "\">" + mo.post_title + "</a></li>") );
				});
				cluster.jbox = new jBox( "Tooltip", {
					"content" : ul,
					"trigger" : "click",
					"closeOnClick" : "body",
					"closeButton" : "box",
					"animation" : "move",
					"zIndex" : 8000,
					"position" : {
						"x" : "left",
						"y" : "top"
					},
					"outside" : "y",
					"offset" : {
						"x" : 25
					},
					"target" : $( cluster.clusterIcon_.div_ )
				} );
			}
			cluster.jbox.toggle();
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
						"action" : "xmaps.get_map_collections_in_bounds",
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
