<?php

use WordPlate\Acf\Fields\Group;
use WordPlate\Acf\Fields\Image;
use WordPlate\Acf\Fields\Layout;
use WordPlate\Acf\ConditionalLogic;
use WordPlate\Acf\Fields\ButtonGroup;
use WordPlate\Acf\Fields\PageLink;
use WordPlate\Acf\Fields\Repeater;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\Textarea;
use WordPlate\Acf\Fields\WysiwygEditor;

// ----------------------------------------------------------------------------- TITLE FIELD

function create_title_field ( $label = "Titre", $key = 'title' ) {
	return Text::make( $label, $key );
}

// ----------------------------------------------------------------------------- WYSIWYG FIELD

function create_wysiwyg_field ( $label = "Contenu", $allowMedia = false, $class = 'clean', $key = "content" ) {
	return WysiwygEditor::make( $label, $key )
		->tabs('visual')
		->mediaUpload( $allowMedia )
		->wrapper(['class' => $class]);
}

// ----------------------------------------------------------------------------- LAYOUT FLEXIBLE SEPARATOR
// Create a separator layout for flexibles

$_layoutSeparatorIndex = 0;
function create_separator_layout () {
	global $_layoutSeparatorIndex;
	return Layout::make('', '--'.(++$_layoutSeparatorIndex));
}

// ----------------------------------------------------------------------------- ENABLED CONDITIONAL
// Create a boolean with its condition

function create_enabled_fields ( $title = "Activé", $offLabel = "Off", $onLabel = "On", $default = 1, $key = 'enabled') {
	return ButtonGroup::make( $title, $key )
		->defaultValue( $default )
		->choices([
			0 => $offLabel,
			1 => $onLabel
		]);
}

function create_enabled_conditional_fields ( $label = "Activé", $key = 'enabled', $offLabel = "Off", $onLabel = "On", $default = 1 ) {
	return [
		create_enabled_fields( $label, $offLabel, $onLabel, $default, $key ),
		ConditionalLogic::if( $key )->equals( 1 )
	];
}

// ----------------------------------------------------------------------------- CONDITIONAL GROUP

function create_conditional_group ( $label, $key, $choiceFields ) {
	// Key for button group
	$enabledKey = $key.'_buttons';

	// Convert choices to "choice" => "choice"
	$choices = [];
	foreach ( $choiceFields as $choice => $fields )
		$choices[$choice] = $choice;

	// Generate button group
	$output = [
		ButtonGroup::make( $label, $enabledKey )->choices( $choices ),
	];

	// Browse choices
	foreach ( $choiceFields as $choice => $fields ) {
		// Do not create empty groups
		if (empty($fields)) continue;
		// Create group and connect it to correct choice
		$output[] = Group::make(' ', $key.'_'.$choice)
			->layout("row")
			->wrapper(['class' => 'conditionalGroup'])
			->fields( $fields )
			->conditionalLogic([
				ConditionalLogic::if( $enabledKey )->equals( $choice )
			]);
	}

	return $output;
}

// ----------------------------------------------------------------------------- COLUMNS GROUP
// Create a clean group field

function create_columns_group_field ( $fields = [], $name = "columns", $layout = 'row') {
	return Group::make(' ', $name)
		->layout( $layout )
		->wrapper(['class' => 'columns-group clean'])
		->fields( $fields );
}

// ----------------------------------------------------------------------------- PAGE LINK FIELD

function create_page_link_field ( $title = "Lien vers la page", $key = 'link', $postTypes = ['page'], $allowArchives = false ) {
	return PageLink::make( $title, $key )
		->allowArchives($allowArchives)
		->allowNull()->required()
		->postTypes( $postTypes );
}

// ----------------------------------------------------------------------------- MENU FIELD

function create_menu_fields ( $id ) {
	return [
		Repeater::make(' ', $id)
			->fields([
				create_page_link_field(),
				Text::make(' ', translate_field_name('title'))
					->instructions('Titre de remplacement (Optionnel)'.BOWL_TRANSLATE_HINT) // TODO argument
			])
	];
}
// ----------------------------------------------------------------------------- IMAGE FIELD

function create_image_field ( $label = "image", $key = 'image', $class = 'smallImage' ) {
	return Image::make($label, $key)
		->wrapper(['class' => $class]);
}
// ----------------------------------------------------------------------------- FLEXIBLE LAYOUT

function create_flexible_layout ( $title, $id, $layout, $fields ) {
	return Layout::make( $title, $id )
		->layout( $layout )
		->fields( $fields );
}

// ----------------------------------------------------------------------------- META FIELDS

function create_meta_fields_group () {
	return [
		'title' => 'SEO and share.',
		'fields' => [
			Textarea::make("Meta description".BOWL_TRANSLATE_HINT, translate_field_name('description'))->rows(3)
				->instructions("For SEO only. Optional."),
			Text::make("Share title".BOWL_TRANSLATE_HINT, translate_field_name('shareTitle'))
				->instructions("For Facebook and Twitter share.<br>Will use page title by default."),
			Textarea::make("Share description".BOWL_TRANSLATE_HINT, translate_field_name('shareDescription'))->rows(3)
				->instructions("For Facebook and Twitter share.<br>Will use meta description by default."),
			Image::make("Share image", 'shareImage')
				->instructions("For Facebook and Twitter"),
		]
	];
}