<?php

use Timber\Timber;

// Get custom template name. Default to "page"
$templateName = get_custom_page_template_name( $post->ID, 'default' );

// Special case, go to news
if ( $templateName === 'news' )
{
	$newsMode = 'all';
	require('news.php');
	exit;
}

// Load custom page template with current post injected
bootstrap_timber_template($templateName, [
	'post' => Timber::get_post()
]);
