<?php

use WordPlate\Acf\Fields\Taxonomy;

$screen = register_custom_screen('page', [
    'name' => 'news',
    'page' => get_custom_page_id('news'),
    'editor' => false,
    'restrictDeletion' => true
], [
	'introduction' => create_intro_group_field(),

	'categories' => [
		'title' => 'Catégories',
		'asGroup' => false,
		'fields' => [
			Taxonomy::make('Catégories', 'categories')
		        ->required()
		        ->instructions("Listez les catégories d'actualités affichées sur la page actualité.\nLaissez vide pour utiliser celles de menu principal (depuis les options du site)")
		        ->taxonomy('category')
		        ->appearance('multi_select')
		]
	]
]);
