<?php

use Nano\core\Nano;
use Nano\helpers\NanoUtils;
use Pecee\SimpleRouter\SimpleRouter;

// ----------------------------------------------------------------------------- ALL ROUTES

Nano::onBeforeRoute( function () {
	Nano::addControllerDirectory('mu-plugins/bowl/nano-controllers');
});

// ----------------------------------------------------------------------------- WP-JSON GATEWAY

// Open some wp-json endpoints
SimpleRouter::get('/wp-json/wp/v2/{request}', function ( $request ) {
	// Only open those endpoints to WordPress API
	if ( !in_array($request, ['media']) ) return;
	Nano::action("Bowl", "startWordpress");
});

// ----------------------------------------------------------------------------- REDIRECT LOCALE

// On home, redirect to correct locale code from browser
// FIXME : Only keep if website is multi-lang
SimpleRouter::get('/', function () {
	$userLocale = Nano::action("Bowl", "getUserLocale");
	Nano::redirect( Nano::getURL("wordpressPage", ['lang' => $userLocale]) );
});

// ----------------------------------------------------------------------------- WORDPRESS PAGE

// Get a WordPress page / post / custom post type
// FIXME : Remove {lang} if not multi-lang
SimpleRouter::get('/{lang}/{a?}/{b?}/{c?}', function ( $lang ) {
	/** @var BowlNanoController $bowlController */
	$bowlController = Nano::getController("Bowl");

	// FIXME : Remove next block if not multi-lang
	// Get and cache locale list
	// If locale does not exist, go to 404
	$localeData = $bowlController->getCachedLocaleData();
	$localeKeys = array_keys( $localeData['languages'] );
	if ( !in_array($lang, $localeKeys) )
		return null;

	// Cache key is the request URL as lower case
	$requestPath = strtolower( Nano::getRequestPath() );
	// Get current bowl post and cache it
	// Do not load WP when retrieving the cached post
	/** @var BowlPost $bowlPost */
	$bowlPost = Nano::cacheDefine( $requestPath, function () use ( $bowlController, $requestPath ) {
		$bowlController->loadWordpress();
		$bowlPost = BowlRequest::getBowlPostByPath( $requestPath );
		// Do not store in cache if post is not found
		if ( is_null($bowlPost) ) return null;
		return $bowlPost->toArray();
	});
	// Bowl post not found, continue
	if ( is_null($bowlPost) ) return null;
	// Render this bowl post
	return nano_render_page( $bowlPost, $lang );
})->setName('wordpressPage');

// ----------------------------------------------------------------------------- NOT FOUND

Nano::onNotFound(function () {
	// FIXME : Remove line if not multi-lang
	$userLocale = Nano::action("Bowl", "getUserLocale");
	return nano_render_page( null, $userLocale );
});

// ----------------------------------------------------------------------------- RENDER PAGE

// FIXME : Remove locale argument if not multi-lang
function nano_render_page ( $pageData, $locale ) {
	/** @var BowlNanoController $bowlController */
	$bowlController = Nano::getController("Bowl");
	// Get and cache global data
	$globals = Nano::cacheDefine("globals", function () use ( $bowlController ) {
		$bowlController->loadWordpress();
		return BowlData::getData("globals");
	});

	// Generate page title from site name and template
	$pageTitle = is_null($pageData) ? $globals["dictionaries"]["not-found"]["title"] : $pageData["title"];
	$titleTemplate = $globals["theme"]["pageTitleTemplate"] ?? "{{site}} - {{page}}";
	$title = NanoUtils::stache($titleTemplate, [
		"site" => $globals["siteName"],
		"page" => $pageTitle,
	]);

	// Merge page meta
	$meta = $globals["meta"];
	if ( !is_null($pageData) ) {
		$meta = array_merge( $meta, $pageData["fields"]["meta"] );
		unset( $pageData["fields"]["meta"] );
	}
	unset($globals["meta"]);

	// Render page
	return Nano::$renderer->render($pageData['template'], [
		'title' => $title,
		// FIXME : Remove locale if not multi-lang
		'locale' => $locale,
		'meta' => $meta,
		'globals' => $globals,
		'pageData' => $pageData,
	]);
}
