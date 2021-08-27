<?php

use WordPlate\Acf\Fields\Image;
use WordPlate\Acf\Fields\TrueFalse;

$screen = register_custom_screen('post_type', [
	'name' => 'post'
], [
	'introduction' => [
		'title' => 'Introduction',
		'fields' => [
			'image' => create_image_group_field([], false)
		]
	],

	// Ajouter la possibilité de champs flexibles par la suite
	'content' => create_flexible_content_fields(['article', 'gallery'])
]);
