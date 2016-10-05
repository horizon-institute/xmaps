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
		<title>Collections</title>
		<?php wp_head(); ?>
	</head>
	<body>
		<div id="collection-list"></div>
		<script type="text/javascript">
			jQuery( function( $ ) {

				function render_list( d ) {
					var ul = $( document.createElement( "ul" ) );
					$.each( d.data, function( i, e ) {
						var li = $( document.createElement( "li" ) );
						li.html( "<h3><a href=\"collection?id=" + e.term_id 
								+ "\">" + e.name + "</a></h3><p>" 
								+ e.description + "</p>" );
						ul.append( li );
					} );
					$( "#collection-list" ).append( ul );
				}
				
				function with_location( pos ) {
					$.get("/xmaps-api/collections/", {
						"key" : "<?php echo $api_key ?>",
						"lat" : pos.coords.latitude,
						"lon" : pos.coors.longitude
					}, render_list );
				}

				function without_location() {
					$.get("/xmaps-api/collections/", {
						"key" : "<?php echo $api_key ?>" 
					}, render_list );
				}
				
				if ("geolocation" in navigator) {
					navigator.geolocation.getCurrentPosition(
							with_location, without_location, 
							{ "enableHighAccuracy" : true } ); 
				} else {
					without_location();
				}				
			} );
		</script>
	</body>
</html>