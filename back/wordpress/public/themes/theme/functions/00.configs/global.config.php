<?php

// ----------------------------------------------------------------------------- SITEMAP

// Disable tags in sitemap
add_filter( 'wpseo_sitemap_exclude_taxonomy', function ($value, $taxonomy) {
	if ( $taxonomy == 'post_tag' ) return true;
}, 10, 2 );

// ----------------------------------------------------------------------------- IMAGE SIZES

// Global JPG Quality
add_filter('jpeg_quality', function() { return 80; });

// Ordered list of all available image sizes.
// From small to large
// [?width, ?height, ?crop]
define('IMAGE_SIZES', [
	//'thumbnail' => [128, 128], // override
	//'thumbnail' => [150],
	//'small' 	=> [512],
	//'medium' 	=> [1024],
	'large' 	=> [1024, 1024], // override after
	"1536x1536" => [1536, 1536], // override before
	"1920x1920" => [1920, 1920],
	"2048x2048"	=> [2048, 2048], // override before
]);
// NOTE : Limited to 2048x2048 by resize-image-after-upload plugin

// All post previews are 4 / 3 format HD
set_post_thumbnail_size(1024, 768);

// Register those sizes
global $_wp_additional_image_sizes;
foreach ( IMAGE_SIZES as $key => $value ) {
	if ( isset($_wp_additional_image_sizes[$key]) ) continue;
	add_image_size( $key, $value[0] ?? 0, $value[1] ?? 0, $value[2] ?? false );
}

