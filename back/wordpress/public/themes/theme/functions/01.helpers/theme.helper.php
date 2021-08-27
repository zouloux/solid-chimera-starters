<?php

use Timber\Timber;

// ----------------------------------------------------------------------------- THEME DATA & FILTERS

/**
 * Register theme data.
 *
 * register_theme_data('datakey', function ($appData, $pageData, $context) {
 *  return 42;
 * });
 *
 * In twig : {{ appData.dataKey }} -> 42
 *
 * @param string $key will be the name of injected data into view.
 * @param Callback $handler is executed to generate data before view.
 * @throws Exception if theme data is already registered.
 */
function register_theme_data ( $key, $handler )
{
    global $themeDataHandlers;
    if (isset($themeDataHandlers[ $key ]))
        throw new Exception("Unable to register theme data, key already taken.");
    $themeDataHandlers[ $key ] = $handler;
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
function register_page_data_filter ( $templateName, $handler )
{
    global $pageDataFilters;
    if (isset($pageDataFilters[ $templateName ]))
        throw new Exception("Unable to register page data filter, templateName already taken.");
    $pageDataFilters[ $templateName ] = $handler;
}

// ----------------------------------------------------------------------------- CUSTOM PAGE TEMPLATES

/**
 * Get page post ID from name.
 * @param $templateName
 * @return int -1 if not found.
 */
function get_custom_page_id ( $templateName )
{
	foreach ( CUSTOM_PAGE_TEMPLATES_BY_ID as $key => $value )
		if ( $value === $templateName ) return $key;
	return -1;
}

/**
 * Get page template name from page ID
 * @param $pageID
 * @param $default : Will return this string if not found
 * @return string|null
 */
function get_custom_page_template_name ( $pageID, $default = null )
{
	return (
		isset(CUSTOM_PAGE_TEMPLATES_BY_ID[$pageID])
		? CUSTOM_PAGE_TEMPLATES_BY_ID[ $pageID ]
		: $default
	);
}

// ----------------------------------------------------------------------------- TIMBER

/**
 * Start a template with this key. Will search for $templateName.twig in templates/ folder.
 * @param string $templateName Template to load. Page data will be filtered with register_page_data_filter.
 * @param array $pageData Inject page data before processing and filtering it. For example process query parameters.
 */
function bootstrap_timber_template ( $templateName, $pageData = [] )
{
    // Inject current page title
	if ( !isset($pageData['title']) || is_null($pageData['title']) )
        $pageData['title'] = get_the_title();

    // Inject current page fields into page data
    $fields = get_fields();
    if ( $fields !== false && !is_null($fields) )
        $pageData += $fields;

    // Configure Timber and create a new context
    Timber::$dirname = 'templates';
    $context = Timber::context();
    $appData = [];
    $appData['template'] = $templateName;

    // Get all theme data handlers, execute and inject them into context
    global $themeDataHandlers;
    foreach ( $themeDataHandlers as $key => $handler )
        $appData[ $key ] = $handler( $appData, $pageData, $context );

    // Get filtered page data for this template
    global $pageDataFilters;
    $appData['page'] = (
        isset( $pageDataFilters[$templateName] )
        ? $pageDataFilters[$templateName]( $pageData, $context )
        : $pageData
    );

    // Filter all pages data
    if (isset( $pageDataFilters['*'] ))
		$appData['page'] = $pageDataFilters['*']( $appData['page'], $context );

    // Inject locale, dictionary, base ...
    $appData['locale'] = get_current_locale();
    $appData['dictionary'] = get_dictionary();
    $appData['base'] = env('WP_URL').'/';
    $appData['debug'] = !!env('WP_DEBUG');

	$appData['referer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	$appData['transition'] = stripos($appData['referer'], $appData['base']) === 0;

    // Read version from static json
    $solidJsonPath = realpath( ABSPATH.'../static/solid.json' );
    $appData['solid'] = (
        $solidJsonPath !== false
        ? json_decode( file_get_contents($solidJsonPath), true )
        : [ 'version' => '0.0.0' ]
    );

    // Inject app data into twig context
    $context['appData'] = $appData;
    foreach ( $appData as $key => $value )
        $context[ $key ] = $value;

    // Filter javascript app data
	$context['javascriptAppData'] = (
		function_exists('filter_javascript_app_data')
		? filter_javascript_app_data( $appData, $context )
		: $appData
	);

//    dump($templateName);dump($context);exit;

    // Render template with this context
	// FIXME : Cache is worse than no cache ? Truncated html output, slower ...
	// Timber::render( $templateName.'.twig', $context, env('WP_DEBUG') ? false : 600 );
    Timber::render( $templateName.'.twig', $context, false );
}
