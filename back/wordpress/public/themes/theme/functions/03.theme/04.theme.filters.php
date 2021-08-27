<?php

use Timber\URLHelper;

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


// ----------------------------------------------------------------------------- LAYOUT REQUIREMENTS

register_theme_data('preloads', function ($appData) {
	$filePath = ABSPATH.'../static/parcel-manifest.json';

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


// Inject resources to load
register_theme_data('resources', function () {
    return [
        'head' => [
            'scripts' => [],
            'styles' => [],
        ],
        'body' => [
            'scripts' => ['index.js'],
            'styles' => ['index.css'],
        ]
    ];
});

// Inject GTM & Analytics codes
register_theme_data('gtm', function () { return option('google-gtm'); });
register_theme_data('analytics', function () { return option('google-analytics'); });

register_theme_data('imageSizes', function () {
	$output = [];

	foreach ( IMAGE_SIZES as $key => $sizeValues )
		$output[ $sizeValues[0] ] = $key;

	return $output;
});

register_theme_data('contactButton', function ($appData, $pageData) {
	return [
		'visible' => (
			(isset($pageData['contact-button']) && isset($pageData['contact-button']['show-contact-button']))
			&& !!$pageData['contact-button']['show-contact-button']
		),
		'mobile' => (
			(isset($pageData['contact-button']) && isset($pageData['contact-button']['show-contact-button-mobile']))
			&& !!$pageData['contact-button']['show-contact-button-mobile']
		),
		'href' => get_permalink( get_custom_page_id('contact') )
	];
});

// Track current URL for menu
register_theme_data('menuCurrentURL', function ($appData) {

	// Any news sub-page need to activate news item in menu
	if ( $appData['template'] === 'news' || $appData['template'] === 'article' )
		return get_permalink( get_custom_page_id('news') );

	else
		return URLHelper::get_current_url();
});

// ----------------------------------------------------------------------------- MENU

// Inject menu from option page
register_theme_data('menu', function ($a, $b, $context) {
	// Get menu for current locale and browse items
	$menu = option( translate_field_name('menu') );
	$output = [];
	foreach ( $menu as $item )
	{
		// Separator
		if ( $item['acf_fc_layout'] == 'separator' )
			$output[] = [ 'type' => 'separator' ];

		// Page
		else if ( $item['acf_fc_layout'] == 'page' )
		{
			// FIXME Si jamais ça retourne 0 sur un nouveau post type
			// FIXME C'est sûrement RankMathSEO qui fout la merde ...
			// FIXME Désactiver le plug + refresh + ré-activer le plug
			$postID = url_to_postid( $item['link'] );
			if ( $postID === 0 ) {
				// FIXME home_url ajoute /en en anglais non ?
				$path = substr($item['link'], strlen(env('WP_URL')), -1 );
				if ( is_null($path) ) continue;
				$page = get_page_by_path($path);
				if (!$page) continue;
				$postID = $page->ID;
			}

			$output[] = [
				'type' => 'page',
				'title' => get_the_title( $postID ),
				'footer' => $item['footer'],
				'href' => $item['link']
			];
		}
	}
	return $output;
});

// ----------------------------------------------------------------------------- CONTACT INFOS

register_theme_data('contact', function () {
	$contact = option('contact');
	$values = [];
	$templates = [];

	foreach ( $contact as $item ) {
		$values[ $item['network'] ] = $item['value'];

		$template = get_dictionary('contact.template.'.$item['network'] );
		$templates[ $item['network'] ] = nl2br(
			is_null($template)
			? $item['value']
			: SolidUtils::quickMustache( $template, ['value' => $item['value']] )
		);
	}

	return [
		'values' => $values,
		'templates' => $templates,
	];
});

// ----------------------------------------------------------------------------- FOOTER

register_theme_data('footer', function () {

	// Parcourir les listes de footer depuis les options
	$rawFooterLists = option( translate_field_name('footer-lists') );
	$footerLists = [];
	foreach ( $rawFooterLists as $listKey => $footerList ) {
		if ( !$footerList['enabled'] ) continue;
		$footerLists[] = $footerList;
		// Pour tous les liens
		if ( $footerList['list-type'] != 'link') continue;
		foreach ( $footerList['content-link'] as $linkKey => $footerLink ) {
			// Récupérer le nom de la page si le texte n'est pas défini
			if ( !isset($footerLink['link-text']) || empty($footerLink['link-text']) ) {
				$postId = url_to_postid($footerLink['link']);
				$footerLists[ $listKey ]['content-link'][ $linkKey ]['link-text'] = acf_get_post_title( $postId );
			}
		}
	}

	// On récupère la liste des réseaux sociaux depuis la page de options réseaux sociaux
	$networks = option('networks');
	$footerNetworks = [];
	foreach ( $networks as $network ) {
		// On vire tous ceux qu'on ne veut pas voir dans le footer
		if (!$network['footer']) continue;
		// Get network translation from dictionary ex : "network.facebook"
		$translation = get_dictionary('network.'.$network['network']);
		$network['title'] = (
			!is_null($translation) ? $translation
			// Remove after dash and uppercase first char
			: ucfirst( explode('-', $network['network'])[0] )
		);
		$footerNetworks[] = $network;
	}

	return [
		'lists' => $footerLists,
		'about' => option(translate_field_name('footer-about')),
		'networks' => $footerNetworks,
		'copy' => SolidUtils::quickMustache(
			get_dictionary('footer.copy'),
			[ 'year' => date('Y') ]
		),
		'by' => get_dictionary('footer.by')
	];
});
