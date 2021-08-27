<?php

use Timber\Timber;

$post = Timber::query_post();

// Redirect collaborateurs post_type to popup in cabinet page
/*if ( $post->post_type == 'collaborateur' )
{
	$permalink = get_permalink( get_custom_page_id('cabinet') );
	$permalink = $permalink.'#/collaborateur/'.$post->slug;
	header('location: '.$permalink);
	exit;
}*/

bootstrap_timber_template('article', [
    'post' => $post
]);
