<?php

// ----------------------------------------------------------------------------- SITEMAP

// Disable tags in sitemap
if ( defined('BOWL_DISABLE_SITEMAP_TAGS') && BOWL_DISABLE_SITEMAP_TAGS ) {
	add_filter( 'wpseo_sitemap_exclude_taxonomy', function ($value, $taxonomy) {
		if ( $taxonomy == 'post_tag' ) return true;
	}, 10, 2 );
}

// Disable some post types in sitemap
add_filter( 'wp_sitemaps_post_types', function ( $postTypes ) {
	global $_bowlSitemapRemovedPostTypes;
	foreach ( $postTypes as $key => $value ) {
		if ( !is_array($_bowlSitemapRemovedPostTypes) ) continue;
		if ( !in_array($key, $_bowlSitemapRemovedPostTypes) ) continue;
		unset( $postTypes[$key] );
	}
	return $postTypes;
});

// Remove authors from sitemap
if ( defined('BOWL_DISABLE_SITEMAP_USERS') && BOWL_DISABLE_SITEMAP_USERS ) {
	add_filter('wp_sitemaps_add_provider', function ($provider, $name) {
		return $name === 'users' ? false : $provider;
	}, 10, 2);
}

// ----------------------------------------------------------------------------- IMAGES

// Override jpeg quality
add_filter('jpeg_quality', function() { return BOWL_JPEG_QUALITY; });

// Register those sizes
global $_wp_additional_image_sizes;
foreach ( BOWL_IMAGE_SIZES as $key => $value ) {
	if ( isset($_wp_additional_image_sizes[$key]) ) continue;
	add_image_size( $key, $value[0] ?? 0, $value[1] ?? 0, $value[2] ?? false );
}

// ----------------------------------------------------------------------------- REMOVE OEMBED
// = emoji support in back and front-end

// https://kinsta.com/fr/base-de-connaissances/desactiver-embeds-wordpress/#disable-embeds-code
if ( defined('BOWL_DISABLE_OEMBED') && BOWL_DISABLE_OEMBED ) {
	add_filter('init', function () {
		remove_action( 'rest_api_init', 'wp_oembed_register_route' );
		add_filter( 'embed_oembed_discover', '__return_false' );
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		add_filter( 'tiny_mce_plugins', function ($plugins) {
			return array_diff($plugins, array('wpembed'));
		});
		add_filter( 'rewrite_rules_array', function ($rules) {
			foreach($rules as $rule => $rewrite) {
				if(false !== strpos($rewrite, 'embed=true')) {
					unset($rules[$rule]);
				}
			}
			return $rules;
		});
		remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
	}, '999');
}
