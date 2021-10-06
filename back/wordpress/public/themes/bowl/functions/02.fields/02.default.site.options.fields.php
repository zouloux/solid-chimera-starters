<?php

use WordPlate\Acf\Fields\Accordion;
use WordPlate\Acf\Fields\ButtonGroup;
use WordPlate\Acf\Fields\ColorPicker;
use WordPlate\Acf\Fields\Image;
use WordPlate\Acf\Fields\Repeater;
use WordPlate\Acf\Fields\Text;

// ----------------------------------------------------------------------------- SITE OPTIONS GROUPS

function create_default_site_options_groups ( $fields ) {
	return array_merge($fields, [
		'dictionaries' => [
			'title' => 'Dictionaries',
			'asGroup' => false,
			'fields' => [
				Repeater::make(' ', 'dictionaries')
					->buttonLabel("Add dictionary")
					->layout('row')
					->fields([
						create_title_field('Dictionary ID', 'id'),
						Accordion::make('Translations'.BOWL_TRANSLATE_HINT, 'accordion'),
						Repeater::make(' ', translate_field_name('data'))
							->buttonLabel("Add translation")
							->layout('table')
							->wrapper(['class' => 'clean'])
							->fields([
								Text::make('Key', 'key')->required(),
								Text::make('Value', 'value')->required(),
							])
					])
			]
		],
		'keys' => [
			'title' => 'API and product keys',
			'asGroup' => false,
			'fields' => [
				Repeater::make(' ', 'keys')
					->instructions("List API and product keys here")
					->buttonLabel("Add key")
					->layout('table')
					->fields([
						Text::make('Key', 'key')->required(),
						Text::make('Value', 'value')->required(),
					])
			]
		],
		'themeOptions' => [
			'title' => 'Theme options',
			'asGroup' => true,
			'fields' => [
				Text::make("Page title template", 'pageTitleTemplate')
					->placeholder("{{site}} - {{page}}")
					->instructions("<strong>{{site}}</strong> for site name<br><strong>{{page}}</strong> for page name."),
				Image::make("Favicon 32", "favicon32")->instructions("32x32px, png"),
				...create_conditional_group("Enable web-app capabilities", "webAppCapabilities", [
					'Off' => [],
					'On' => [
						Text::make("App title", 'appTitle'),
						Image::make("App icon 1024", "favicon1024")->instructions("1024x1024px, png"),
						ColorPicker::make("App icon background color"),
						ColorPicker::make("Theme color"),
						ButtonGroup::make("iOS title bar color", 'iosTitleBar')
							->choices([
								'default' => 'Default',
								'black' => 'Black',
								'translucent' => 'Translucent',
							]),
						ButtonGroup::make("Display type", 'displayType')
							->choices([
								'browser' => 'Browser',
								'fullscreen' => 'Fullscreen',
								'color' => 'Color',
							]),
						ButtonGroup::make("Allowed web-app orientation", 'allowedOrientation')
							->choices([
								'any' => 'Any',
								'auto' => 'Auto',
								'portrait' => 'Portrait',
								'landscape' => 'Landscape',
							]),
					]
				])
			]
		]
	]);
}

