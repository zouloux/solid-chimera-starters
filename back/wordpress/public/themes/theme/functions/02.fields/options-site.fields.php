<?php

use WordPlate\Acf\Fields\Repeater;
use WordPlate\Acf\Fields\Text;

// ----------------------------------------------------------------------------- SITE OPTIONS

$screen = register_custom_screen('options_page', [
    'name' => 'options-site',
    'label' => "Options du site",
    'icon' => 'dashicons-admin-generic'
], [


	// ------------------------------------------------------------------------- DICTIONARY

    'dictionary' => [
        'title' => 'Dictionnaire',
        'asGroup' => false,
        'fields' => [
            Repeater::make(' ', translate_field_name('dictionary'))
                ->instructions("Listez les textes traduits à travers le site.")
                ->layout('table')
                ->fields([
                    Text::make('Clé', 'key')->required(),
                    Text::make('Valeur', 'value')->required(),
                ])
        ]
    ],

    // ------------------------------------------------------------------------- ANALYTICS

    'analytics' => [
        'title' => 'Analytics & GTM',
        'asGroup' => false,
        'fields' => [
			Text::make('Code GTM', 'google-gtm')
				->instructions("Spécifiez un code de tracking GTM. Laissez vide pour désactiver."),
			Text::make('Code Google Analytics', 'google-analytics')
				->instructions("Spécifiez un code de tracking Google Analytics. Laissez vide pour désactiver.")
        ]
    ]
]);
