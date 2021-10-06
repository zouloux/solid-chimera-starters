<?php

// ----------------------------------------------------------------------------- MENU OPTIONS

$screen = register_custom_screen('options_page', [
	'name' => 'options-menu',
	'label' => "Menu",
	'icon' => 'dashicons-editor-alignleft',
	'position' => 1
], [

	'main' => [
		'title' => '1. Menu principal',
		'asGroup' => false,
		'fields' => [
			...create_menu_fields('menu')
		]
	],

	'footer' => [
		'title' => '2. Menu en pied de page',
		'asGroup' => false,
		'fields' => [
			...create_menu_fields('footer')
		]
	],
]);
