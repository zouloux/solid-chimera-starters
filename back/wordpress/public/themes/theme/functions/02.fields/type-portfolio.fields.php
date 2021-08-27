<?php

use WordPlate\Acf\Fields\ButtonGroup;
use WordPlate\Acf\Fields\Repeater;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\Textarea;
use WordPlate\Acf\Fields\Wysiwyg;

// -----------------------------------------------------------------------------

register_custom_screen('post_type', [
    'name' => 'portfolio',
    'labels' => ['un portfolio', 'Portfolios'],
    'icon' => 'dashicons-camera'
], [
	'introduction' => create_intro_group_field([
		'image' => create_image_group_field(),
		'sidebar-aligned' => ButtonGroup::make('Aligner barre latérale', 'sidebar-align')
			->instructions("Va pousser le contenu sous la barre latérale du menu pour éviter les collisions.")
		    ->choices([
		        0 => 'Non',
		        1 => 'oui'
		    ])
		    ->defaultValue(0)
	]),

    'sidebar' => [
    	'title' => 'Informations latérales',
		'asGroup' => true,
		'fields' => [
			Textarea::make('Sous-titre', translate_field_name('sub-title'))->rows(2)
				->instructions("Affiché sous le menu, et aussi sur l'encart ciblant ce portfolio."),
			Repeater::make("Paragraphes", translate_field_name('paragraphs'))->buttonLabel("Ajouter un paragraphe")->layout('row')->fields([
				Text::make('Titre', 'title')
					->instructions('Optionnel. Sépare le contenu avec un trait.'),
				/*Wysiwyg::make(' ', 'content')
					->wrapper(['class' => 'clean'])
					->mediaUpload(false)
					->required()*/
				Textarea::make('Contenu du paragraphe', 'content')
					->rows(5)
					->required()
			])
		]
	],

	'flexible' => create_flexible_content_fields(['page', 'image']),

	'contact-button' => create_contact_button_group(),
	'contact' => create_contact_block_group_field(),
]);
