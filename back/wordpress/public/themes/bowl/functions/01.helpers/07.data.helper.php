<?php

use Timber\Timber;

// ----------------------------------------------------------------------------- THEME DATA & FILTERS

/**
 * Register context data.
 *
 * register_context_data('datakey', function ($context) {
 *  return 42;
 * });
 *
 * In twig : {{ dataKey }} -> 42
 *
 * @param string $key will be the name of injected data into view.
 * @param Callback $handler is executed to generate data before view.
 * @throws Exception if context data is already registered.
 */
function register_context_data ( $key, $handler ) {
	global $_bowlContextDataHandlers;
	if ( isset($_bowlContextDataHandlers[ $key ]) )
		throw new Exception("Unable to register context data, key already taken.");
	$_bowlContextDataHandlers[ $key ] = $handler;
}

/**
 * Register app data for javascript usage.
 *
 * register_app_data('datakey', function ($context) {
 *  return 42;
 * });
 *
 * In JS : console.log(__appData.datakey); -> 42
 *
 * @param string $key will be the name of injected data into app data.
 * @param Callback $handler is executed to generate data before app data.
 * @throws Exception if app data is already registered.
 */
function register_app_data ( $key, $handler ) {
	global $_bowlAppDataHandlers;
	if ( isset($_bowlAppDataHandlers[ $key ]) )
		throw new Exception("Unable to register app data, key already taken.");
	$_bowlAppDataHandlers[ $key ] = $handler;
}

/**
 * Register page data filter.
 * Handler will be called each time a twig template is requested.
 *
 * register_page_data_filter('home-page', function ($pageData, $context) {
 *  // Will override title for all pages with template 'home-page'
 *   $pageData['title'] = 'new title';
 *   return $pageData;
 * });
 *
 * @param string $templateName Requested template key from bootstrap_timber_template($templateName, ...)
 * @param Callback $handler is executed to filter data each time the template is requested.
 *
 * @throws Exception if filter with this templateName is already taken.
 */
function register_page_data_filter ( $templateName, $handler ) {
	global $_bowlPageDataFilters;
	if ( isset($_bowlPageDataFilters[ $templateName ]) )
		throw new Exception("Unable to register page data filter, templateName already taken.");
	$_bowlPageDataFilters[ $templateName ] = $handler;
}

// ----------------------------------------------------------------------------- CONTEXT AND APP DATA

function create_context_and_app_data ()
{
	global $_bowlContext;
	global $_bowlAppData;

	// ------------------------------------------------------------------------- CONTEXT DATA
	if ( is_null($_bowlContext) )
	{
		// Init context
		$_bowlContext = Timber::context();

		// Read version from static directory
		$versionPath = realpath( ABSPATH.'../static/version' );
		$_bowlContext['version'] = (
			$versionPath !== false
			? trim(file_get_contents($versionPath))
			: '0.0.0'
		);

		// Get all context data handlers, execute and inject them into context
		global $_bowlContextDataHandlers;
		if ( isset($_bowlContextDataHandlers) )
			foreach ( $_bowlContextDataHandlers as $key => $handler )
				$_bowlContext[ $key ] = $handler( $_bowlContext );
	}

	// ------------------------------------------------------------------------- APP DATA
	if ( is_null($_bowlAppData) )
	{
		// Get regular base
		$base = rtrim(env('WP_URL'), '/').'/';

		// Format app data
		$_bowlAppData = [
			'base' => $base,
			'locales' => [
				'list' => get_locale_list(),
				'current' => get_current_locale(),
			],
			'debug' => !!env('WP_DEBUG'),
			'version' => $_bowlContext['version'],
			'global' => [],
		];

		// Get all app data handlers, execute and inject them into app data
		global $_bowlAppDataHandlers;
		if ( isset($_bowlAppDataHandlers) )
			foreach ( $_bowlAppDataHandlers as $key => $handler )
				$_bowlContext[ $key ] = $handler( $_bowlContext );

		// Filter app data
		if ( function_exists('filter_app_data') )
			$_bowlAppData = filter_app_data( $_bowlAppData, $_bowlContext );
	}

	return [
		'context' => $_bowlContext,
		'appData' => $_bowlAppData,
	];
}

// ----------------------------------------------------------------------------- CUSTOM PAGE TEMPLATES

/**
 * Get page post ID from name.
 * @param $templateName
 * @return int|array -1 if not found.
 */
function get_custom_page_id ( $templateName ) {
	$found = [];
	foreach ( CUSTOM_PAGE_TEMPLATES_BY_ID as $key => $value )
		if ( $value === $templateName ) $found[] = $key;
	if ( count($found) == 0 )
		return -1;
	else if ( count($found) == 1 )
		return $found[0];
	else
		return $found;
}

/**
 * Get post type name from page ID
 * @param $pageID
 * @param $default : Will return this string if not found
 * @return string|null
 */
function get_custom_post_type_from_page_id ( $pageID, $default = null ) {
	return ( CUSTOM_PAGE_TEMPLATES_BY_ID[ $pageID ] ?? $default );
}

// ----------------------------------------------------------------------------- PAGE DATA

/**
 * Get a WP_Post object from a path.
 * Path can be with or without base :
 * ex : "http://my-website/my-post/" works as well as "/my-post/"
 * Trailing slash is not mandatory.
 * @param string $path
 * @return WP_Post|null
 */
function get_post_by_path ( string $path ) :? WP_Post
{
	// Get all declared post types to search in
	global $_bowlAllRegisteredPostTypes;
	$post = null;

	// Remove base from path
	$path = (
	stripos($path, env('WP_URL')) !== false
		? substr($path, strlen(env('WP_URL')), -1 ).'/'
		: rtrim($path, '/').'/'
	);

	// Check if path has 2 parts, it may be a custom post type
	$splitPathParts = explode('/', ltrim(rtrim(strtolower($path), '/'), '/'));
	if ( count($splitPathParts) === 2 && in_array($splitPathParts[0], $_bowlAllRegisteredPostTypes) )
		$post = get_page_by_path($splitPathParts[1], OBJECT, $splitPathParts[0]);

	// Post not found yet, try to search in pages and posts
	if ( is_null($post) )
		$post = get_page_by_path($path, OBJECT, ['page', 'post']);

	return $post;
}

/**
 * @private Recursively parse page data to patch structure.
 */
function _recursive_filter_patch_fields ( array &$data ) : array
{
	foreach ( $data as $key => &$node )
	{
		// Remove all data for a node when field enabled=false
		if ( is_array($node) && isset($node['enabled']) && $node['enabled'] === false )
			$data[ $key ] = [ 'enabled' => false ];

		// Filter posts
		else if ( $node instanceof WP_Post ) {
			$data[ $key ] = [
				'id' => $node->ID,
				'type' => $node->post_type,
				'title' => $node->post_title,
				'slug' => $node->post_name,
				'published' => $node->post_status === 'publish',
				'href' => get_permalink( $node ),
				'author' => $node->post_author, // TODO : Add option for this ?
			];
		}

		// Filter images
		else if (
			is_array($node) && isset($node['type']) && $node['type'] === 'image'
			&& isset($node['subtype']) && is_array($node['sizes'])
		) {
			$image = [
				'type' => 'image',
				'id' => $node['id'],
				'title' => $node['title'],
				'size' => $node['filesize'],
				'description' => $node['description'],
				'caption' => $node['caption'],
				'format' => $node['subtype'],
				'sizes' => [
					"original" => [ // FIXME : Add option for this ?
						"href" => $node['url'],
						'width' => $node['width'],
						'height' => $node['height'],
					]
				],
			];
			foreach ( BOWL_IMAGE_SIZES as $imageSize => $v ) {
				if (!isset($node['sizes'][$imageSize])) continue;
				$image['sizes'][$imageSize] = [
					'href' => $node['sizes'][$imageSize],
					'width' => $node['sizes'][$imageSize.'-width'],
					'height' => $node['sizes'][$imageSize.'-height'],
				];
			}
			$data[ $key ] = $image;
		}

		// FIXME : Filter other file types ? MP4 ? MP4 ? PDF ?

		// Convert flexible layouts to "type"
		else if ( $key === 'acf_fc_layout' ) {
			$data = array_merge(['type' => $data[$key]], $data);
			unset( $data[$key] );
		}

		// Recursive filter
		else if ( is_array($node) ) {
			$data[ $key ] = _recursive_filter_patch_fields( $node );
		}
	}

	return $data;
}

/**
 * Get page data from a post.
 * Will call page data filters for this post type.
 * Result is not cached and usage can be intensive.
 * @param WP_Post|null $post
 * @return array
 * @throws Exception
 */
function create_page_data_from_post ( WP_Post $post = null ) : array
{
	// Get global context and app data
	$contextAndAppData = create_context_and_app_data();

	// Get data from post or query it
	$post ??= get_post();

	// Post not found
	if ( is_null($post) ) return [
		'type' => 'not-found'
	];

	// Get custom post type
	$postType = get_custom_post_type_from_page_id( $post->ID, $post->post_type );

	// Base of page data
	$pageData = [
		'id' => $post->ID,
		'title' => $post->post_title,
		'type' => $postType,
	];

	// Inject current page fields into page data
	$fields = get_fields( $post );
	if ( $fields !== false && is_array($fields) )
		$pageData += $fields;

	// Inject page content if it exists
	$content = get_the_content(null, false, $post);
	if ( !empty($content) )
		$pageData['content'] = $content;

	// Patch all "${screenName}-${fieldName}" to $fieldName
	foreach ( $pageData as $key => $value ) {
		$prefix = $pageData['type'].'-';
		if ( stripos($key, $prefix) === 0 ) {
			$newKey = substr($key, strlen($prefix), strlen($key));
			if ( isset($pageData[ $newKey ]) )
				throw new Exception("Unable to process '${pageData['type']}' page data. Key '${newKey}' already exists.");
			$pageData[ $newKey ] = $value;
			unset( $pageData[$key] );
		}
	}

	// Patch page data recursively
	$pageData = _recursive_filter_patch_fields( $pageData );

	// Filter all pages data with wildcard before selector
	if ( isset( $_bowlPageDataFilters['*before'] ) )
		$pageData = $_bowlPageDataFilters['*before']( $pageData, $contextAndAppData['appData'], $contextAndAppData['context'] );

	// Debug page data, before filtering
	defined('BOWL_DEBUG_PAGE_DATA') && BOWL_DEBUG_PAGE_DATA && env('WP_DEBUG', false) && dump($pageData);

	// Get filtered page data for this template
	global $_bowlPageDataFilters;
	$pageData = (
		isset( $_bowlPageDataFilters[$postType] )
		? $_bowlPageDataFilters[$postType]( $pageData, $contextAndAppData['appData'], $contextAndAppData['context'] )
		: $pageData
	);

	// Filter all pages data with wildcard after selector
	if ( isset( $_bowlPageDataFilters['*after'] ) )
		$pageData = $_bowlPageDataFilters['*after']( $pageData, $contextAndAppData['appData'], $contextAndAppData['context'] );

	// Debug page data, after filtering
	defined('BOWL_DEBUG_PAGE_DATA') && BOWL_DEBUG_PAGE_DATA && env('WP_DEBUG', false) && dump($pageData);

	// Return parsed page data
	return $pageData;
}

/**
 * Get page data from a path.
 * Path can be with or without base :
 * ex : "http://my-website/my-post/" works as well as "/my-post/"
 * Trailing slash is not mandatory.
 * @param string $path
 * @return array|null
 * @throws Exception
 */
function get_page_data_by_path ( string $path ) :? array
{
	// Get post by path
	$post = get_post_by_path( $path );
	// Post not found
	if ( !$post ) return null;
	// Post found generate data
	return create_page_data_from_post( $post );
}
