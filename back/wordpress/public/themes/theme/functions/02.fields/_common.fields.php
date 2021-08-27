<?php

use WordPlate\Acf\ConditionalLogic;
use WordPlate\Acf\Fields\ButtonGroup;
use WordPlate\Acf\Fields\Group;
use WordPlate\Acf\Fields\Image;
use WordPlate\Acf\Fields\PageLink;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\Textarea;
use WordPlate\Acf\Fields\TrueFalse;
use WordPlate\Acf\Fields\WysiwygEditor;

// ----------------------------------------------------------------------------- ENABLED CONDITIONAL

function create_enabled_conditional_fields ( $key = 'enabled' )
{
	return [
		ButtonGroup::make( "Afficher ce bloc", $key )
			->choices([
				0 => 'Non',
				1 => 'Oui'
			]),
		ConditionalLogic::if('enabled')->equals( 1 )
	];
}

// ----------------------------------------------------------------------------- COLUMNS GROUP

function create_columns_group_field ( $fields)
{
	return Group::make(' ', 'columns')->wrapper(['class' => 'columns-group clean'])->fields( $fields );
}

// ----------------------------------------------------------------------------- INTERNAL PAGE LINK
// Créer un lien vers une page interne

function create_internal_page_link_field ( $options = [] )
{
	$options = SolidUtils::defaultOptions($options, [
		'types' => ['portfolio', 'hub', 'page', 'post'],
		'label' => 'Lien optionnel'
	]);

	$field = PageLink::make($options['label'], 'link')
		->allowArchives(false)->allowNull()
		->postTypes( $options['types'] );

	if (isset($options['instructions']))
		$field->instructions($options['instructions']);

	return $field;
}

function create_internal_page_link_text_field ( $instructions = "Optionnel. Si non renseigné, utilisera le dictionnaire." )
{
	return Text::make('Texte du lien', translate_field_name('link-text'))
		->instructions( $instructions )
		->conditionalLogic([
			ConditionalLogic::if('link')->notEmpty()
		]);
}

// ----------------------------------------------------------------------------- IMAGE
// Créer un group upload d'image, avec texte et alignement en option.

function create_image_group_field ( $additionalFields = [], $content = true, $required = false, $width = true, $key = 'image', $label = 'Image' )
{
	// Le champs image toujours là
	$fields = [
		Image::make('Fichier', $key)->wrapper(['class' => 'smallImage'])
	];
	if ( $required )
		$fields[0]->required();

	// Injecter le bouton pour définir la largeur de l'image
	if ( $width )
		$fields[] = ButtonGroup::make('Largeur', $key.'-width')
			->choices([
				'regular' => 'Normal',
				'thin' => 'Fin'
			]);

	// Si on a du contenu, et son alignement
	if ( $content ) {
		$fields[] = Textarea::make('Contenu', translate_field_name('content'))
			->instructions("Optionnel. 2 lignes max.")
			->rows(2);
		$fields[] = ButtonGroup::make('Alignement du contenu', 'alignment')
			->choices([
				'left' => 'Gauche',
				'right' => 'Droite',
			])
			->conditionalLogic([
				ConditionalLogic::if( translate_field_name('content') )->notEmpty()
			]);
	}

	// Créer le groupe
	return Group::make( $label, 'parent-'.$key )->layout('row')
		->fields( array_merge($fields, $additionalFields) );
}

// ----------------------------------------------------------------------------- INTRO
// Créer un groupe intro. Le titre custom est obligatoire.

function create_intro_group_field ( $fields = [] )
{
	return [
		'title' => 'Introduction',
		'fields' => array_merge([
			'custom-title' => Textarea::make("Titre custom", translate_field_name('custom-title'))
				->instructions("Optionnel, rensigner pour contrôler le saut de ligne du titre affiché sur la page, ou pour en changer son contenu.<br>Maximum 3 lignes.")
				->rows(3)
		], $fields)
	];
}

// ----------------------------------------------------------------------------- CONTACT BUTTON

function create_contact_button_group ()
{
	return [
		'title' => 'Bouton contactez-moi',
		'key' => 'contact-button',
		'fields' => [
			TrueFalse::make("Desktop et tablette", 'show-contact-button')
				->stylisedUi(),
			TrueFalse::make("Mobile", 'show-contact-button-mobile')
			    ->stylisedUi()
		]
	];
}

// ----------------------------------------------------------------------------- CONTACT TOGGLES
// Créer un groupe avec les boutons pour activer / désactiver les infos de contact à afficher

function create_contact_toggles_group ( $key = 'contact' )
{
	return Group::make('Information de contact', $key)->layout('table')->fields([
		TrueFalse::make("Afficher le numéro de téléphone", 'showPhone')->stylisedUi(),
		TrueFalse::make("Afficher l'adresse e-mail", 'showEmail')->stylisedUi(),
		TrueFalse::make("Afficher l'adresse postale", 'showAddress')->stylisedUi()
	]);
}


// ----------------------------------------------------------------------------- CONTACT FOOTER BLOC
// Créer le groupe pour afficher le bloc contact en bas de page

function create_contact_block_group_field ()
{
	[ $enabledBlock, $enabledCondition ] = create_enabled_conditional_fields();
	return [
		'title' => 'Contact',
		'fields' => [
			$enabledBlock,
			Textarea::make("Titre", translate_field_name('title'))
				->instructions("Maximum 2 lignes")->rows(2)
				->conditionalLogic([ $enabledCondition ]),
			WysiwygEditor::make("Mention", translate_field_name('mention'))
				->mediaUpload(false)
				->instructions("Petite mention sous le bouton contact")
				->conditionalLogic([ $enabledCondition ]),
		]
	];
}
