<?php

use WordPlate\Acf\Fields\Accordion;
use WordPlate\Acf\Fields\ButtonGroup;
use WordPlate\Acf\Fields\Email;
use WordPlate\Acf\Fields\Link;
use WordPlate\Acf\Fields\Repeater;
use WordPlate\Acf\Fields\Select;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\Textarea;
use WordPlate\Acf\Fields\TrueFalse;


$screen = register_custom_screen('options_page', [
	'name' => 'options-networks',
	'label' => "Contact & Réseaux",
	'icon' => 'dashicons-phone',
	'position' => 3
], [

	// ------------------------------------------------------------------------- CONTACT

	'contact' => [
		'title' => 'Contact',
		'asGroup' => false,
		'fields' => [
			Repeater::make(' ', 'contact')
				->min(1)->max(4)
				->instructions("Listez les moyens de contact.")
				->layout('row')
				->fields([
					Text::make('Nom du service', 'title')->required(),
					Accordion::make("Informations"),
					ButtonGroup::make('Contact principal', 'main')
						->instructions("Le contact principal est affiché en footer.")
						->choices([
							0 => 'Non',
							1 => 'Oui'
						]),
					Textarea::make('Adresse', 'address')
						->instructions("Adresse sur plusieurs lignes, max 3, optionnel")
						->rows(3),
					Text::make('Téléphone', 'phone')
						->instructions("Numéro de téléphone, optionnel"),
					Link::make('Lien', 'link')
						->instructions("Lien internet, optionnel"),
					Email::make('Adresse e-mail', 'email')
						->instructions("Adresse e-mail, optionnel"),
				])
		]
	],

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
//							'linkedin' => 'Linkedin',
//							'linkedin-page' => 'Linkedin Page',
							'twitter' => 'Twitter',
							'facebook' => 'Facebook',
//							'facebook-page' => 'Facebook Page',
						]),
					Accordion::make("Informations"),
					Text::make('Lien', 'link')
						->required(),
					TrueFalse::make("Header", 'header')
						->required()
						->instructions("Afficher en header.")
						->stylisedUi(),
					TrueFalse::make("Page Contact", 'contact')
						->required()
						->instructions("Afficher sur la page contact.")
						->stylisedUi()
				])
		]
	],
]);
