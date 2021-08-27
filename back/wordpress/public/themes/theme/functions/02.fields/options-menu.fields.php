<?php

use WordPlate\Acf\Fields\FlexibleContent;
use WordPlate\Acf\Fields\Layout;
use WordPlate\Acf\Fields\TrueFalse;

// ----------------------------------------------------------------------------- MENU OPTIONS

$screen = register_custom_screen('options_page', [
	'name' => 'options-menu',
	'label' => "Menu",
	'icon' => 'dashicons-editor-alignleft'
], [
	'menus' => [
		'title' => 'Menu principal',
		'asGroup' => false,
		'fields' => [
			FlexibleContent::make(' ', translate_field_name('menu'))
				->layouts([
					Layout::make('Page', 'page')
						->layout('table')
						->fields([
							create_internal_page_link_field([
								'instructions' => null,
								'label' => 'Lien vers la page'
							]),
							TrueFalse::make('Présent en footer', 'footer')
								->stylisedUi()
								->defaultValue(true)
						]),
					Layout::make('Séparateur', 'separator')
						->layout('row'),
				])
		]
	],
]);
