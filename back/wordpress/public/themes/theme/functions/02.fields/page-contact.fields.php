<?php

use WordPlate\Acf\Fields\Image;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\Textarea;

// -----------------------------------------------------------------------------

[ $mapEnabledBlock, $mapEnabledCondition ] = create_enabled_conditional_fields();
[ $studioEnabledBlock, $studioEnabledCondition ] = create_enabled_conditional_fields();

$screen = register_custom_screen('page', [
	'name' => 'contact-page',
	'page' => get_custom_page_id('contact'),
	'editor' => false,
	'restrictDeletion' => true
], [
	'introduction' => create_intro_group_field([
		Text::make('Titre', translate_field_name('top-title'))
			->instructions("Affiché au dessus des informations de contact")
			->required(),
		Textarea::make('Introduction', translate_field_name('intro-text'))
			->instructions("Optionnel. Sous le titre.")->rows(3),
		create_contact_toggles_group(),
		Textarea::make('Description', translate_field_name('description'))
			->instructions("Optionnel. Texte court sous les éléments de contact."),
	]),

	'map' => [
		'title' => 'Image de la carte',
		'fields' => [
			$mapEnabledBlock,
			Textarea::make('Titre', translate_field_name('title'))
				->instructions("Optionnel.")->rows(2)
				->conditionalLogic([ $mapEnabledCondition ]),
			Image::make('Fichier', 'image')
				->instructions("Optionnelle. Image de la carte, au format png si possible.")
				->conditionalLogic([ $mapEnabledCondition ]),
			Text::make('Lien google maps', 'link')
				->instructions("Lien vers l'adresse dans google map")
				->conditionalLogic([ $mapEnabledCondition ]),
		]
	],

	'studio' => [
		'title' => 'Photo du studio',
		'fields' => [
			$studioEnabledBlock,
			Textarea::make('Titre', translate_field_name('title'))
				->instructions("Optionnel.")->rows(2)
				->conditionalLogic([ $studioEnabledCondition ]),
			Image::make('Fichier', 'image')
				->instructions("Optionnelle. Image du studio")
				->conditionalLogic([ $studioEnabledCondition ])
		]
	]
]);
