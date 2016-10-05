<?php
if( ! is_user_logged_in() ) {
	auth_redirect();
}
$api_key = XMapsUser::get_api_key_by_user( wp_get_current_user() );
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title></title>
		<?php wp_head(); ?>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/URI.js/1.18.1/URI.min.js"></script>
		<style type="text/css">
		html, body {
			height: 100%;
		}
		</style>
	</head>
	<body>
		<div id="description"></div>
		<div id="map" style="height: 100%; width: 100%;"></div>
		<script type="text/javascript">
			jQuery( function( $ ) {

				var geometries = [];
				
				var mapconf = {
					"center": new google.maps.LatLng( 0, 0 ),
					"streetViewControl": false,
					"zoom": 1,
					"minZoom": 1,
					"mapTypeId": google.maps.MapTypeId.TERRAIN,
					"panControl": false,
					"mapTypeControl": false,
					"zoomControl": false
				};
				
				var map = new google.maps.Map( 
					$( "#map" ).get( 0 ), mapconf );
				
				var uri = new URI( location.href );
				var uri_map = uri.search( true );

				$.get("/xmaps-api/collections/", {
					"key" : "<?php echo $api_key ?>",
					"id" : uri_map.id
				}, function( data ) {
					var d = data.data[0];
					document.title = d.name;
					$( "#description" ).html(
							"<h3>" + d.name + "</h3><p>"
							+ d.description + "</p>");
				} );
				

				$.get("/xmaps-api/posts/", {
					"key" : "<?php echo $api_key ?>",
					"collection-id" : uri_map.id
				}, function( data ) {
					var bounds = new google.maps.LatLngBounds();
					$.each( data.data, function( i, e ) {
						var wkt = new Wkt.Wkt();
						wkt.read( e.location_wkt );
						var marker = wkt.toObject();
						if ( marker instanceof google.maps.Polygon ) {
							geometries.push( {
								"post" : e,
								"geom" : marker 
							} );
						} else if ( marker instanceof google.maps.Marker ) {
							geometries.push( {
								"post" : e,
								"geom" : new google.maps.Circle( {
								"center": marker.getPosition(),
								"radius": 20
							} ) } );
						}
						marker.setMap( map );
						var extend = function( j, obj ) {
							if ( Array.isArray( obj ) ) {
								$.each( obj, extend );		
							} else {
								bounds.extend( new google.maps.LatLng( {
									"lat" : obj.y,
									"lng" : obj.x
								}) );
							}
						}
						$.each( wkt.components, extend );
					} );
					map.fitBounds( bounds );
					
				} );

				if ("geolocation" in navigator) {
					navigator.geolocation.watchPosition( function( position ) {
						var pos = new google.maps.LatLng( {
							"lat" : parseFloat( position.coords.latitude ),
							"lng" : parseFloat( position.coords.longitude )
						} );
						$.each( geometries, function( i, g ) {
							if ( g.geom.getBounds ) {
								if (  g.geom.getBounds().contains( pos ) ) {
									document.location = "<?php echo get_site_url( null, 'xmaps-browser/post'); ?>?id=" + g.post.ID;
								}
							} else {
								if ( google.maps.geometry.poly.containsLocation( pos, g.geom ) ) {
									document.location = "<?php echo get_site_url( null, 'xmaps-browser/post'); ?>?id=" + g.post.ID;
								}
							}
						} );
						
					}, function() {}, { "enableHighAccuracy" : true } );
				}
								
			} );
		</script>
		<?php wp_footer(); ?>
	</body>
</html>