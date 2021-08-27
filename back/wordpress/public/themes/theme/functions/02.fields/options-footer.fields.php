<?php

use WordPlate\Acf\ConditionalLogic;
use WordPlate\Acf\Fields\ButtonGroup;
use WordPlate\Acf\Fields\Repeater;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\Textarea;
use WordPlate\Acf\Fields\TrueFalse;

$screen = register_custom_screen('options_page', [
	'name' => 'options-footer',
	'label' => "Footer",
	'icon' => 'dashicons-editor-table'
], [

	// ------------------------------------------------------------------------- LISTS

	'lists' => [
		'title' => 'Listes',
		'asGroup' => false,
		'fields' => [
			Repeater::make(' ', translate_field_name('footer-lists'))
				->max(2)
				->buttonLabel("Ajouter une liste")
				->layout('row')
				->fields([
					TrueFalse::make('Activé', 'enabled')->defaultValue(true)->stylisedUi(),
					Text::make('Titre', 'title')->required(),
					ButtonGroup::make( "Type de bloc", 'list-type' )
						->choices([
							'link' => 'Liste de liens',
							'text' => 'Texte libre'
						]),
					Repeater::make('Contenu', 'content-link')
						->required()
						->fields([
							create_internal_page_link_field([
								'label' => 'Lien interne',
								'instructions' => 'Optionnel.'
							]),
							Text::make('Texte', 'link-text')
								->instructions('Optionnel si lien défini.')
						])
						->conditionalLogic([
							ConditionalLogic::if('list-type')->equals('link')
						]),
					Textarea::make('Contenu', 'content-text')
						->required()
						->rows(5)
						->conditionalLogic([
							ConditionalLogic::if('list-type')->equals('text')
						]),
				])
		]
	],

	// ------------------------------------------------------------------------- ABOUT

	'about' => [
		'title' => 'À propos',
		'asGroup' => true,
		'key' => translate_field_name('footer-about'),
		'fields' => [
			Text::make('Titre', 'title')
				->required(),
			Textarea::make('Contenu', 'content')
				->required(),
			create_contact_toggles_group()
		]
	],
]);
