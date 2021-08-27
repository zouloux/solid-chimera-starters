<?php

// ----------------------------------------------------------------------------- ALL PAGES AFTER FILTER

register_page_data_filter('*', function ( $data ) {
	0 && env('WP_DEBUG', false) && dump($data);
	return $data;
});

// ----------------------------------------------------------------------------- HOME PAGE

register_page_data_filter('home', function ( $data ) {
	//	dd($data['home-page-introduction']);
    return [
        'title' => $data['title'],
		'introduction' => filter_introduction( $data['home-page-introduction'], $data['title'] ),
		'flexible' => filter_flexible( $data['flexible'] )
    ];
});

// ----------------------------------------------------------------------------- PORTFOLIO PAGE

register_page_data_filter('portfolio', function ( $data ) {

	//	dd($data);

	$sidebar = $data['portfolio-sidebar'];

    return [
        'title' => $data['title'],
		'introduction' => filter_introduction( $data['portfolio-introduction'], $data['title'] ),
		'flexible' => filter_flexible( $data['flexible'] ),
		'sidebar' => [
			'title' => get_translation( $sidebar, 'sub-title', []),
			'paragraphs' => get_translation( $sidebar, 'paragraphs', [])
		],
		'contact' => filter_contact($data, 'portfolio-contact'),
    ];
});

// ----------------------------------------------------------------------------- HUB PAGE

register_page_data_filter('hub', function ( $data ) {

    return [
	    'title' => $data['title'],
	    'introduction' => filter_introduction( $data['hub-introduction'], $data['title'] ),
	    'flexible' => filter_flexible( $data['flexible'] )
    ];
});

// ----------------------------------------------------------------------------- DEFAULT PAGE TYPE

register_page_data_filter('default', function ( $data ) {
//	dd($data);

	return [
		'title' => $data['title'],
		'introduction' => filter_introduction( $data['page-introduction'], $data['title'] ),
		'flexible' => filter_flexible( $data['flexible'] ),
		'contact' => filter_contact($data, 'page-contact'),
	];
});


// ----------------------------------------------------------------------------- CONTACT PAGE

register_page_data_filter('contact', function ( $data ) {
//	dump($data);

	$contactIntro = $data['contact-page-introduction'];
	$map = $data['contact-page-map'];
	$studio = $data['contact-page-studio'];

	return [
		'title' => $data['title'],
		'introduction' => filter_introduction( $contactIntro, $data['title'], [
			'topTitle' => get_translation($contactIntro, 'top-title'),
			'contact' => $contactIntro['contact']
		]),
		'map' => [
			'enabled' => !!$map['enabled'],
			'title' => get_translation($map, 'title'),
			'image' => $map['image'],
			'link' => $map['link'],
		],
		'studio' => [
			'enabled' => !!$studio['enabled'],
			'title' => get_translation($studio, 'title'),
			'image' => $studio['image']
		]
	];
});


// ----------------------------------------------------------------------------- NEWS PAGE

register_page_data_filter('news', function ($data)
{
	$blogPageData = get_blog_page_data();
	$newsPageFields = $blogPageData['fields'];

	// Get all posts category names
	$allPostsCategoriesName = HAS_ALL_ARTICLES_CATEGORY ? get_dictionary('news.all') : '';

	// If our page have categories data in fields
	$dataHasCategories = ( isset($data['categories']) && !empty($data['categories']) );

	// Check if we need to query page options (on categories or search for ex)
	if (!$dataHasCategories)
	{
		if ( isset($newsPageFields['categories']) && !empty($newsPageFields['categories']) )
		{
			$data['categories'] = $newsPageFields['categories'];
			$dataHasCategories = true;
		}
	}

	// If we have some categories data
	if ( $dataHasCategories )
	{
		// Process them for the menu
		$categories = [];
		$categoriesFromNewsPage = $data['categories'];
		$allCategories = get_categories();
		foreach ( $allCategories as $currentCategory ) foreach ( $categoriesFromNewsPage as $catID )
		{
			if ( $currentCategory->term_id === $catID )
			{
				$categories[] = [
					'name' => $currentCategory->name,
					'href' => get_category_link( $currentCategory )
				];
			}
		}
	}

	// Get categories from site options
	else $categories = get_preferred_news_categories();

	// Add 'all posts' article
	if ( HAS_ALL_ARTICLES_CATEGORY ) {
		$categories = array_merge(
			[
				[
					'name' => $allPostsCategoriesName,
					'href' => get_permalink( get_custom_page_id('news') )
				]
			],
			$categories
		);
	}

	$pageData = [
		'title' => $blogPageData['title'],
		'customTitle' => nl2br($blogPageData['customTitle']),
		'type' => 'category',
		'currentCategory' => $allPostsCategoriesName,
		'newsList' => [],
		'categories' => $categories,
		'pagination' => $data['pagination']
	];

	// If we have a category
	if ( $data['newsMode'] == 'category' )
	{
		$pageData['title'] = $data['category']->cat_name;
		$pageData['currentCategory'] = $data['category']->cat_name;
	}

	// Search news
	else if ( $data['newsMode'] == 'search' )
	{
		$pageData['title'] = $data['search'];
		$pageData['type'] = 'search';
		$pageData['currentSearch'] = $data['search'];
		$pageData['currentCategory'] = "";
	}

	// Process all posts into post filter
	foreach ( $data['posts'] as $post )
		$pageData['newsList'][] = filter_post( $post );

//	dump($data);dd($pageData);
	return $pageData;
});

// ----------------------------------------------------------------------------- ARTICLE PAGE

register_page_data_filter('article', function ($data) {

	$blogPageData = get_blog_page_data();

	// Convert WP post and filter it
	$postData = filter_post( $data['post'], false );

	// Init post-introduction object if not defined (because of imported articles)
	if (!isset($data['post-introduction']) || is_null($data['post-introduction']))
		$data['post-introduction'] = [];

	// Filter introduction block with custom title from blog page
	$postData['introduction'] = filter_introduction( $data['post-introduction'], $blogPageData['customTitle']);

	// Get image from article if introduction image is not defined
	if ( !isset($postData['introduction']['image']) || !$postData['introduction']['image'] )
		$postData['introduction']['image'] = $postData['image'];

	// Inject converted flexible if available
	$postData['flexible'] = ( isset( $data['flexible'] ) ? filter_flexible( $data['flexible'] ) : [] );

	// Default image width is regular (for article image without introduction)
	if ( !isset($postData['introduction']['imageWidth']) )
		$postData['introduction']['imageWidth'] = 'regular';

	// If image is not available, set as empty string to show a gray square and not and empty zone
	if ( !$postData['introduction']['image'] )
		$postData['introduction']['image'] = '';

	// Back link to blog page
	$postData['backLink'] = get_permalink( get_custom_page_id('news') );

	//dump($data);dd($postData);
	return $postData;
});
