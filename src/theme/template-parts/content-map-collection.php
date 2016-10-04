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

		if ( 'post' === get_post_type() ) : ?>
		<div class="entry-meta">
			<?php xmaps_posted_on(); ?>
		</div><!-- .entry-meta -->
		<?php
		endif; ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php
			the_content( sprintf(
				/* translators: %s: Name of current post. */
				wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'xmaps' ), array( 'span' => array( 'class' => array() ) ) ),
				the_title( '<span class="screen-reader-text">"', '"</span>', false )
			) );

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'xmaps' ),
				'after'  => '</div>',
			) );
			
			$map_objects = XMapsDatabase::get_collection_map_objects( get_the_ID() );
			foreach ( $map_objects as $mo ) {
				$mo[0]->permalink = get_permalink( $mo[0]->ID );
			}
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
				var locations = <?php echo json_encode( $map_objects ); ?>;
				var urlbase = "<?php echo esc_js( get_template_directory_uri() ); ?>";
				var overlay = new google.maps.OverlayView();
		        overlay.draw = function() {};
		        overlay.setMap( map );
				$.each( locations, function( i, loc ) {
					var wkt = new Wkt.Wkt();
					wkt.read(loc[1].location);
					var marker = wkt.toObject();
					marker.setMap(map);
					google.maps.event.addListener( marker, "click", function( e ) {
						var p = overlay.getProjection();
						var xy = p.fromLatLngToContainerPixel(e.latLng);
						if ( ! marker.jbox ) {
							var a = $("<li><a href=\"" + loc[0].permalink
								+ "\">" + loc[0].post_title + "</a></li>");
							marker.jbox = new jBox( "Tooltip", {
								"content" : a,
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
								"target" : map_div,
								"offset" : {
									"x" : xy.x,
									"y" : xy.y
								},
								"onClose" : function() {
									marker.jbox = null;
								}
							} );
						}
						marker.jbox.toggle();
					} );

					if ( marker.setOptimized ) {
						marker.setOptimized(false); 
					}
					if ( marker.setTitle ) {
						marker.setTitle("boo"); 
					}
					if ( marker.setIcon ) {
						marker.setIcon({
							"url" : urlbase + "/images/markers/red.png"							
						});
					}
					markers.push(marker);
				} );
			} );
		</script>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php xmaps_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->
