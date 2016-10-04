<?php
/**
 * The template for displaying comments.
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package xmaps
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php
	// You can start editing here -- including this comment!
	if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
				printf( // WPCS: XSS OK.
					esc_html( _nx( 'One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'xmaps' ) ),
					number_format_i18n( get_comments_number() ),
					'<span>' . get_the_title() . '</span>'
				);
			?>
		</h2>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
		<nav id="comment-nav-above" class="navigation comment-navigation" role="navigation">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'xmaps' ); ?></h2>
			<div class="nav-links">

				<div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'xmaps' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'xmaps' ) ); ?></div>

			</div><!-- .nav-links -->
		</nav><!-- #comment-nav-above -->
		<?php endif; // Check for comment navigation. ?>

		<ol class="comment-list">
			<?php
				wp_list_comments( array(
					'style'      => 'ol',
					'short_ping' => true,
				) );
			?>
		</ol><!-- .comment-list -->

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
		<nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'xmaps' ); ?></h2>
			<div class="nav-links">

				<div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'xmaps' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'xmaps' ) ); ?></div>

			</div><!-- .nav-links -->
		</nav><!-- #comment-nav-below -->
		<?php
		endif; // Check for comment navigation.

	endif; // Check for have_comments().


	// If comments are closed and there are comments, let's leave a little note, shall we?
	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>

		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'xmaps' ); ?></p>
	<?php
	endif;

	comment_form( array(
		'id_form' => 'map-object-comment-form',
		'title_reply' => '<a href="#">Leave a reply</a>',
	) );
	?>
	<script type="text/javascript">
		jQuery( function($) {

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
			var map_div = $( "#comment-map" );
			var map = new google.maps.Map(map_div.get(0), conf);
			var markers = [];

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
			
			$( "#reply-title" ).click( function() {
				$( "#map-object-comment-form" ).slideToggle( 400, function() {
					$( window ).scrollTo( "#map-object-comment-form" );
					google.maps.event.trigger(map, 'resize');
					map.setCenter( new google.maps.LatLng(0, 0) );
				} );
				return false;
			} );
						
		} );
	</script>
</div><!-- #comments -->
