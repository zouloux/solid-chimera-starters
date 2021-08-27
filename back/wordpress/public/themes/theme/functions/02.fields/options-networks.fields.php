<?php

use WordPlate\Acf\Fields\Link;
use WordPlate\Acf\Fields\Repeater;
use WordPlate\Acf\Fields\Select;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\Textarea;
use WordPlate\Acf\Fields\TrueFalse;


$screen = register_custom_screen('options_page', [
	'name' => 'options-networks',
	'label' => "Contact & Réseaux",
	'icon' => 'dashicons-phone'
], [
	// ------------------------------------------------------------------------- SOCIAL NETWORKS

	'networks' => [
		'title' => 'Réseaux sociaux',
		'asGroup' => false,
		'fields' => [
			Repeater::make(' ', 'networks')
				->instructions("Listez les réseaux sociaux disponible sur le site.")
				->layout('row')
				->fields([
					Select::make('Réseau', 'network')->required()
						->choices([
							'instagram' => 'Instagram',
							'linkedin' => 'Linkedin',
							'linkedin-page' => 'Linkedin Page',
							'twitter' => 'Twitter',
							'facebook' => 'Facebook',
							'facebook-page' => 'Facebook Page',
						]),
					Text::make('Lien', 'link')
						->required(),
					TrueFalse::make("Footer", 'footer')
						->required()
						->instructions("Afficher en footer.")
						->stylisedUi()
				])
		]
	],

	'contact' => [
		'title' => 'Contact',
		'asGroup' => false,
		'fields' => [
			Repeater::make(' ', 'contact')
				->instructions("Listez les moyens de contact.")
				->layout('row')
				->fields([
					Select::make('Réseau', 'network')->required()
						->choices([
							'address' => 'Adresse postale',
							'email' => 'Adresse email',
							'phone' => 'Numéro de téléphone'
						]),
					TextArea::make('Valeur', 'value')
						->required()
						->rows(3)
				])
		]
	]
]);
