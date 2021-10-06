<?php

// ----------------------------------------------------------------------------- STRUCTURAL CONFIGS

// Disable emoji support.
// Will disable ability to add them items in backend, and will also disable
// library injection in front-end.
const BOWL_DISABLE_OEMBED = true;

// Disable page nesting
const BOWL_DISABLE_NESTED_PAGES = false;

// Disable news editing in admin. Page is still accessible but removed from menu.
const BOWL_DISABLE_NEWS = true;

// ----------------------------------------------------------------------------- PAGES

// List pages ids associated to custom post types
const CUSTOM_PAGE_TEMPLATES_BY_ID = [
	//13 => 'home',
];

// Enables ?json=1 on all pages
const BOWL_ENABLE_PAGE_DATA_API = true;

// Enables ?ajax=1 on all pages
const BOWL_ENABLE_AJAX_API = true;

// Will inject current page data into window.__pageData
// Convenient for SPA with react or vue rendering.
// Will show page data in javascript when debug=1 even it this flag is disabled.
const BOWL_INJECT_PAGE_DATA_INTO_JAVSCRIPT = false;

// ----------------------------------------------------------------------------- SITEMAP

// Disable tags in sitemap
const BOWL_DISABLE_SITEMAP_TAGS = true;

// Disable users in sitemap !
const BOWL_DISABLE_SITEMAP_USERS = true;

// ----------------------------------------------------------------------------- IMAGE SIZES

// Global JPG Quality
const BOWL_JPEG_QUALITY = 74;

// Ordered list of all available image sizes.
// From small to large
// [?width, ?height, ?crop]
// NOTE : Limited to 2048x2048 by resize-image-after-upload plugin
const BOWL_IMAGE_SIZES = [
	//'thumbnail' => [128, 128], // override
	'thumbnail' => [150],
	'small' 	=> [512],
	//'medium' 	=> [1024],
	'large' => [ 1024, 1024 ], // override after
	"1536x1536" => [ 1536, 1536 ], // override before
//	"1920x1920" => [ 1920, 1920 ],
	"2048x2048" => [ 2048, 2048 ], // override before
];

// All post previews are 4 / 3 format HD
set_post_thumbnail_size(1024, 768);