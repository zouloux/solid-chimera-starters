<?php

// ----------------------------------------------------------------------------- CATEGORIES

function get_preferred_news_categories ()
{
	// Cache preferred categories to avoid parsing it multiple times
	global $preferredNewsCategories;
	if ( isset($preferredNewsCategories) ) return $preferredNewsCategories;

	// Browse all categories and preferred categories IDs
	$preferredNewsCategories = [];
	$allCategories = get_categories();
	$categoryIDs = option('categories') ?? [];
	foreach ( $categoryIDs as $categoryID ) foreach ( $allCategories as $category )
		if ( $categoryID === $category->term_id )
			$preferredNewsCategories[] = [
				'name' => $category->name,
				'href' => get_category_link( $category )
			];
	return $preferredNewsCategories;
}

// -----------------------------------------------------------------------------

function get_blog_page_data ()
{
	global $_globalBlogData;

	if ( is_null($_globalBlogData) )
	{
		// Get news page fields and title
		$newsPageFields = get_fields( get_custom_page_id('news') );
		$pageTitle = get_the_title( get_custom_page_id('news') );
		if (!isset($newsPageFields['news-introduction']))
			$newsPageFields['news-introduction'] = [];
		$customTitle = get_translation($newsPageFields['news-introduction'], 'custom-title', '');
		if ( empty($customTitle) )
			$customTitle = $pageTitle;

		$_globalBlogData = [
			'fields' => $newsPageFields,
			'title' => $pageTitle,
			'customTitle' => $customTitle
		];
	}

	return $_globalBlogData;
}