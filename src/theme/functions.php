<?php
/**
 * XMaps functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package xmaps
 */

if ( ! function_exists( 'xmaps_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function xmaps_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on xmaps, use a find and replace
		 * to change 'xmaps' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'xmaps', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'primary' => esc_html__( 'Primary', 'xmaps' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'xmaps_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );
	}
endif;
add_action( 'after_setup_theme', 'xmaps_setup' );

add_action( 'after_setup_theme', function () {
	$GLOBALS['content_width'] = apply_filters( 'xmaps_content_width', 640 );
}, 0 );

add_action( 'widgets_init', function () {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'xmaps' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'xmaps' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'jbox',
			'https://cdnjs.cloudflare.com/ajax/libs/jBox/0.3.2/jBox.min.css' );
	wp_enqueue_script( 'jbox',
			'https://cdnjs.cloudflare.com/ajax/libs/jBox/0.3.2/jBox.min.js' );
	wp_enqueue_style( 'xmaps-style', get_stylesheet_uri() );
	wp_enqueue_style( 'xmaps-style-layout', get_template_directory_uri() . '/layouts/content-sidebar.css' );
	wp_enqueue_script( 'xmaps-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20151215', true );
	wp_enqueue_script( 'xmaps-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );
	wp_enqueue_script( 'jquery-scrollto',
	'//cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.0/jquery.scrollTo.min.js', array( 'jquery' ), '2.1.0', true );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
} );

add_action( 'wp_insert_comment', function( $comment_id, $comment_object ) {
	if ( array_key_exists( 'xmaps-location-entry' , $_POST ) ) {
		$location = wp_filter_nohtml_kses( $_POST['xmaps-location-entry'] );
		if ( $comment_object->comment_approved ) {
			XMapsDatabase::add_or_update_map_object_location(
				$comment_id,
				'map-object-comment',
			$location );
		} else {
			update_comment_meta( $comment_id, 'xmaps-location', $location );
		}
	}
}, 99, 2 );

add_action( 'comment_unapproved_to_approved', function( $comment ) {
	$location = get_comment_meta( $comment->comment_ID, 'xmaps-location', true );
	if ( false !== $location ) {
		XMapsDatabase::add_or_update_map_object_location(
			$comment->comment_ID,
			'map-object-comment',
		$location );
	}
} );

add_action( 'trashed_comment', function( $cid ) {
	XMapsDatabase::delete_map_object_location( $cid, 'map-object-comment' );
} );

add_action( 'comment_form_logged_in_after', function() {
	if ( get_post_type() == 'map-object' ) {
	?><input id="xmaps-location-entry" name="xmaps-location-entry" type="hidden" />
		<div id="xmaps-controls">
			Drawing mode:
			<select id="xmaps-controls-mode">
				<option value="point">Point</option>
				<option value="area">Area</option>
			</select>
			<button id="xmaps-controls-draw">Draw</button>
			<button id="xmaps-controls-clear">Clear Map</button>
		</div>
		<div id="comment-map"></div><?php
	}
} );

add_filter( 'comment_form_default_fields', function( $fields ) {
	if ( get_post_type() == 'map-object' ) {
		$fields['location'] = '
	 		<input id="xmaps-location-entry" name="xmaps-location-entry" type="hidden" />
			<div id="xmaps-controls">
				Drawing mode:
				<select id="xmaps-controls-mode">
					<option value="point">Point</option>
					<option value="area">Area</option>
				</select>
				<button id="xmaps-controls-draw">Draw</button>
				<button id="xmaps-controls-clear">Clear Map</button>
			</div>
			<div id="comment-map"></div>';
	}
	return $fields;
} );

add_action( 'parse_request', function( $wp ) {
	if ( strpos( $wp->request, 'xmaps-browser/' ) === 0 ) {
		$page = array_pop( explode( '/',parse_url($wp->request, PHP_URL_PATH) ) );
		$path = get_stylesheet_directory() . '/browser/' . $page . '.php';
		if ( file_exists( $path ) ) {
			include $path;
			exit();
		}
	}
} );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';
