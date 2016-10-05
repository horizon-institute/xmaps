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
		<div id="canvas"></div>
		<script type="text/javascript">
			jQuery( function( $ ) {
				var uri = new URI( location.href );
				var uri_map = uri.search( true );

				$.get("/xmaps-api/posts/", {
					"post-id" : uri_map.id,
					"key" : "<?php echo $api_key ?>" 
				}, function( post ) {
					post = post.data;
					document.title = post.post_title;
					$.get("/xmaps-api/content/", {
						"post-id" : uri_map.id,
						"key" : "<?php echo $api_key ?>"
					}, function( content ) {
						content = content.data;
						$( "#canvas" ).html(
							"<h3>"  + post.post_title + "</h3>"
							+ "<p>" + content.post_content + "</p>" );
					} );
					
				} );
				
			} );
		</script>
		<?php wp_footer(); ?>
	</body>
</html>