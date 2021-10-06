<?php

// Do nothing on backend
if (is_admin()) return null;

// ----------------------------------------------------------------------------- WP HEAD FILTERING

// Remove all WP Junk
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('wp_head', 'rsd_link');
add_filter('the_generator', function () { return ''; });
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'wc-block-style' );
}, 100 );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links');
add_action('wp_print_styles', function () {
	global $wp_styles;
	$wp_styles->queue = [];
}, 100);
add_filter( 'wpseo_debug_markers', '__return_false' );
remove_action( 'wp_head', 'wp_resource_hints', 2 );
remove_action('wp_head', 'wp_shortlink_wp_head', 10);
add_filter( 'rank_math/frontend/remove_credit_notice', '__return_true' );
add_theme_support( 'admin-bar', array( 'callback' => '__return_false' ) );

// ----------------------------------------------------------------------------- INJECT LAYOUT DATA

register_context_data('dictionaries', function () { return get_dictionary(); });
register_context_data('keys', function () { return get_key(); });
register_context_data('themeOptions', function () { return get_theme_options(); });

register_context_data('imageSizes', function () {
	$output = [];
	foreach ( BOWL_IMAGE_SIZES as $key => $sizeValues )
		$output[ $sizeValues[0] ] = $key;
	return $output;
});

register_context_data('preloads', function ( $appData) {
	$filePath = ABSPATH.'../static/assets-manifest.json';

	if ( !file_exists($filePath) )
		return [];

	$content = json_decode( file_get_contents( $filePath ), true );

	$extensionsToAs = [
		'woff' => 'font'
	];

	$base = env('WP_URL').'/';
	$filesToPreload = [];
	foreach ($content as $path) {
		$path = substr($path, 1, strlen($path));
		$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?? '');
		if ( in_array($extension, array_keys($extensionsToAs)) ) {
			$fullPath = $base.$path;
			$filesToPreload[] = [
				'path' => $fullPath,
				'as' => $extensionsToAs[$extension]
			];
		}
	}

	return $filesToPreload;
});