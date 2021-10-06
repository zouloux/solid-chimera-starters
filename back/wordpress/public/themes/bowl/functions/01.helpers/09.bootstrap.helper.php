<?php

use Timber\Timber;

// ----------------------------------------------------------------------------- TIMBER

function bootstrap_timber_template ()
{
	// Get page data
	$pageData = create_page_data_from_post();

	// Configure Timber template directories
	Timber::$dirname = ['templates', '../bowl/templates'];

	// Create data bags
	$contextAndAppData = create_context_and_app_data();
	$contextAndAppData['context']['appData'] = $contextAndAppData['appData'];
	$contextAndAppData['context']['pageData'] = $pageData;
//	dump($contextAndAppData['context']);exit;

	// Target twig file to load from post type
	$twigFile = $pageData['type'].'.twig';

	// Enable json mode
	if (
		defined('BOWL_ENABLE_PAGE_DATA_API') && BOWL_ENABLE_PAGE_DATA_API
		&& isset($_GET['json']) && !!$_GET['json']
	) {
		header('Content-type: application/json');
		print json_encode( $pageData, JSON_OBJECT_AS_ARRAY );
		exit;
	}

	// Enable ajax mode
	else if (
		defined('BOWL_ENABLE_AJAX_API') && BOWL_ENABLE_AJAX_API
		&& isset($_GET['ajax']) && !!$_GET['ajax']
	) {
		$contextAndAppData['context']['ajax'] = 1;
	}

//	dd($contextAndAppData['context']);

	// FIXME : Cache is worse than no cache ? Truncated html output, slower ...
	Timber::render($twigFile, $contextAndAppData['context']);
}
