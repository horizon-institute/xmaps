<?php
/**
 * Display fragment for Map Object post edit screen.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

require_once XMAPS_LIB_DIR . '/lib/geoPHP/geoPHP.inc';
$locations = array_map( function( $e ) {
	$geom = geoPHP::load($e->location, 'wkb');
	$geom->setSRID( XMAPS_SRID );
	$e->location = $geom;
	return $e;
}, XMapsDatabase::get_map_object_locations( get_the_ID() ) );

foreach($locations as $loc) {
	// Display points/areas
	//print_r($loc);
	//print_r($p->y() / (10**8));
}
?>
<div id="xmaps-controls">
	<span id="xmaps-add-point" class="js-link">Add Point</span> |
	<span id="xmaps-add-area" class="js-link">Add Area</span>
</div>
<div id="xmaps-map" style="height: 480px;"></div>
<script>
 jQuery( function( $ ) {
		var conf = {
            "center": new google.maps.LatLng(0, 0),
            "streetViewControl": false,
            "zoom": 1,
            "minZoom": 1,
            "maxZoom": 20,
            "mapTypeId": google.maps.MapTypeId.TERRAIN,
            "panControl": true,
            "mapTypeControl": true
	        };
        var map_div = $("#xmaps-map");
		var map = new google.maps.Map(map_div.get(0), conf);

		$("#xmaps-add-point").click(function() {
			var con = jQuery(document.createElement("div"));
			var marker = new google.maps.Marker({
				"draggable" : true
			});
			var infowin = new google.maps.InfoWindow({
				"content" : con.get(0)
			});
			var ok = jQuery(document.createElement("span"));
			ok.text("OK").addClass("js-link").click(function() {
				infowin.close();
				var pos = marker.getPosition(); //TODO Save the point
				marker.setMap(null);
			});
			var cancel = jQuery(document.createElement("span"));
			cancel.text("Cancel").addClass("js-link").click(function() {
				infowin.close();
				marker.setMap(null);
			});
			con.append(ok).append(jQuery("<span> | </span>")).append(cancel);
			google.maps.event.addListener(infowin, "closeclick", function() {
				marker.setMap(null);
			});
			marker.setMap(map);
			marker.setPosition(map.getCenter());
			infowin.open(map, marker);
		});

		$("#xmaps-add-area").click(function() {
			var markers = [];
			var path = new google.maps.MVCArray;
			var poly = new google.maps.Polygon({
				"strokeWeight" : 3,
				"fillColor" : "#5555FF"
			});
			poly.setMap(map);
			poly.setPaths(new google.maps.MVCArray([path]));
			google.maps.event.addListener(map, "click", function(event) {
				var i = path.length;
				path.insertAt(i, event.latLng);
				var marker = new google.maps.Marker({
					"position" : event.latLng,
					"map" : map,
					"draggable" : true
				});
				markers.push(marker);
				google.maps.event.addListener(marker, "dragend", function() {
					path.setAt(i, marker.getPosition());
				});
			});
		});
		
	} );
</script>
