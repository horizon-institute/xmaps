<?php
/**
 * Display fragment for Map Object post edit screen.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

$locations = XMapsDatabase::get_map_object_locations( get_the_ID(), 'map-object' );
$location = '';
if ( ! empty( $locations ) ) {
	$location = $locations[0];
}
?>
<div id="xmaps-controls">
	Drawing mode: 
	<select id="xmaps-controls-mode">
		<option value="point">Point</option>
		<option value="area">Area</option>
	</select>
	<button id="xmaps-controls-draw">Draw</button>
	<button id="xmaps-controls-clear">Clear Map</button>
	<input id="xmaps-controls-search" type="text" 
			placeholder="Search for a place...">
</div>
<input type="hidden" name="xmaps-location-entry" id="xmaps-location-entry" 
		value="<?php echo esc_attr( $location->location ); ?>" />
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
		var markers = [];
		var location = "<?php echo esc_js( $location->location ); ?>";
		if(location != null && location != "") {
			var wkt = new Wkt.Wkt();
			wkt.read(location);
			var marker = wkt.toObject();
			marker.setMap(map);
			markers.push(marker);
		}

        var search_box = new google.maps.places.SearchBox(
                $("#xmaps-controls-search").get(0));
        map.addListener('bounds_changed', function() {
            search_box.setBounds(map.getBounds());
        });
        search_box.addListener('places_changed', function() {
            var places = search_box.getPlaces();
            if (places.length == 0) {
            	return;
            }
            var place = places[0];
            if(!place.geometry) {
                return;
            }
            map.setCenter(place.geometry.location);            
        });
        
		var clear_map = function() {
			google.maps.event.clearInstanceListeners(map);
			$.each(markers, function(i, e) {
				e.setMap(null);
			});
			markers = [];
			$("#xmaps-location-entry").val("");
			map.setOptions({
				"draggableCursor": null
			});
		};

		var draw_point = function() {
			map.setOptions({
				"draggableCursor": "crosshair"
			});
			var l;
			l = google.maps.event.addListener(map, "click", function(event) {
				google.maps.event.removeListener(l);
				map.setOptions({
					"draggableCursor": null
				});
				var marker = new google.maps.Marker({
					"draggable" : true,
					"map" : map,
					"position" : event.latLng
				});
				markers.push(marker);
				var wkt = new Wkt.Wkt();
				wkt.fromObject(marker);
				$("#xmaps-location-entry").val(wkt.write());
				marker.addListener("dragend", function() {
					var wkt = new Wkt.Wkt();
					wkt.fromObject(marker);
					$("#xmaps-location-entry").val(wkt.write());
				});		
			});
		};

		var draw_area = function() {
			map.setOptions({
				"draggableCursor": "crosshair"
			});
			var path = new google.maps.MVCArray;
			var poly = new google.maps.Polygon({
				"strokeWeight" : 3,
				"fillColor" : "#5555FF"
			});
			poly.setMap(map);
			markers.push(poly);
			poly.setPaths(new google.maps.MVCArray([path]));
			google.maps.event.addListener(map, "click", function(event) {
				var i = path.length;
				path.insertAt(i, event.latLng);
				var wkt = new Wkt.Wkt();
				wkt.fromObject(poly);
				$("#xmaps-location-entry").val(wkt.write());
			});
		};

		$("#xmaps-controls-clear").click(function() {
			clear_map();
			return false;
		});

		$("#xmaps-controls-draw").click(function() {
			clear_map();
			switch($("#xmaps-controls-mode").val()) {
			case "area":
				draw_area();
				break;
			case "point":
				draw_point();
				break;
			}
			return false;
		});
		
	} );
</script>
