<?php

// Get category and search parameters
use Timber\Timber;

// Get news mode from parent requesting page
global $newsMode;

// Default news mode to all
if (!isset($newsMode) || is_null($newsMode)) $newsMode = 'all';

// Get search query
$search = get_search_query();

// If search query is empty but we are in search mode
// Use all mode (to avoid empty title on empty search)
if ( empty($search) && $newsMode == 'search' ) $newsMode = 'all';

// Get requested category
$category = get_query_var( 'cat' ) ? get_category( get_query_var( 'cat' ) ) : null;

// Default WP query options
$postOptions = [
	// Remove all other post types than articles
	'post_type' => 'post',
	// Current page
	'paged' =>  (get_query_var('paged')) ? get_query_var('paged') : 1
];

// Search query
if ( $newsMode == 'search' )
	$postOptions['s'] = $search;

// Category query
else if ( $newsMode === 'category' )
	$postOptions['cat'] = $category->term_id;

// Get all posts from this query
$posts = Timber::get_posts( $postOptions );

// Request template
bootstrap_timber_template('news', [
	'newsMode' => $newsMode,
	'category' => $category,
	'search' => $search,
	'posts' => $posts,
	'pagination' => Timber::get_pagination()
]);
