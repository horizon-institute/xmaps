<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package xmaps
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php
		if ( is_single() ) {
			the_title( '<h1 class="entry-title">', '</h1>' );
		} else {
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		}
		?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php
			the_content( sprintf(
				/* translators: %s: Name of current post. */
				wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'xmaps' ), array( 'span' => array( 'class' => array() ) ) ),
				the_title( '<span class="screen-reader-text">"', '"</span>', false )
			) );

			the_meta();

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'xmaps' ),
				'after'  => '</div>',
			) );

			$locations = XMapsDatabase::get_map_object_locations( get_the_ID(), 'map-object' );
			$locations = array_merge( $locations,
			XMapsDatabase::get_map_object_comment_locations( get_the_ID() ));
		?>
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
				var locations = <?php echo json_encode( $locations ); ?>;
				var urlbase = "<?php echo esc_js( get_template_directory_uri() ); ?>";
				for ( var i = 0; i < locations.length; i++ ) {
					var wkt = new Wkt.Wkt();
					var loc = locations[i];
					wkt.read(loc.location);
					var marker_color = (function( t ) {
						switch ( t ) {
						case "map-object" : return "blue";
						case "map-object-comment" : return "purple";
						default : return "red"
						}
					}) ( loc.reference_type );
					var marker = wkt.toObject();
					marker.setMap(map);
					if ( loc.reference_type == "map-object-comment" ) {
						google.maps.event.addListener( marker, "click", function() {
							$( window ).scrollTo( $( "#comment-" + loc.reference_id ) );
						} );
					}
					if ( marker.setIcon ) {
						marker.setIcon({
							"url" : urlbase + "/images/markers/" + marker_color + ".png"							
						});
					}
					markers.push(marker);
				}
						
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
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php xmaps_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->
