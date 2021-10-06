<?php

register_api_endpoint('bowl', '/icons-manifest.json', ['methods' => 'GET'], function ( WP_REST_Request $request ) {

	// TODO

	/*// If we have some favicon data
	$global = FrontData::getGlobalData();
	if ( isset($global['theme']['favicon']) )
	{
		$faviconConfig = $global['theme']['favicon'];

		// Minimal json data set, with name and pinned as browser bookmark
		$defaultName = Instances::config()->get('site.title');
		$jsonData = [
			'name' => $defaultName,
			'short_name' => $faviconConfig['name'] ?? $defaultName,
			'display' => 'browser'
		];

		// Set favicon for android
		if ( isset($faviconConfig['file']) )
		{
			$faviconSizes = $global['theme']['media'][ $faviconConfig['file'] ]['sizes'];
			$base = $this->_envConfig->base;
			$jsonData['icons'] = [
				[
					'src' => $base.$faviconSizes['_favicon-192'],
					'sizes' =>  '192x192', 'type' =>  'image/png'
				], [
					'src' => $base.$faviconSizes['_favicon-512'],
					'sizes' =>  '512x512', 'type' =>  'image/png'
				]
			];
		}

		// Theme color is Android's browser tab color
		if ( isset($faviconConfig['themeColor']) )
			$jsonData['theme_color'] = $faviconConfig['themeColor'];

		// Background color is behind launch icon
		if ( isset($faviconConfig['backgroundColor']) )
			$jsonData['background_color'] = $faviconConfig['backgroundColor'];

		if ( isset($faviconConfig['webapp']) )
		{
			// Force to start at home, even if user pinned a specific page
			$jsonData['start_url'] = $this->_envConfig->scheme.'://'.$this->_envConfig->absoluteBase;

			// Override display mode for android
			$jsonData['display'] = $faviconConfig['webapp']['androidDisplay'];

			// Set orientation
			$jsonData['orientation'] = $faviconConfig['webapp']['orientation'];
		}

		HttpIO::jsonResponse( $jsonData );
	*/

	return [
		'icons' => 'manifest'
	];
});