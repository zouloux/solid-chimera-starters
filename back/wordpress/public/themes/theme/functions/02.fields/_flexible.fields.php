<?php

use WordPlate\Acf\ConditionalLogic;
use WordPlate\Acf\Fields\ButtonGroup;
use WordPlate\Acf\Fields\File;
use WordPlate\Acf\Fields\FlexibleContent;
use WordPlate\Acf\Fields\Image;
use WordPlate\Acf\Fields\Layout;
use WordPlate\Acf\Fields\Message;
use WordPlate\Acf\Fields\PostObject;
use WordPlate\Acf\Fields\Repeater;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\Textarea;
use WordPlate\Acf\Fields\TrueFalse;
use WordPlate\Acf\Fields\WysiwygEditor;

// ----------------------------------------------------------------------------- FLEXIBLE

/**
 * Créer le layout du contenus flexible de base.
 * @return array
 */
function create_flexible_content_fields ( $allowedLayouts = [] )
{
    $layouts = [];

	// ------------------------------------------------------------------------- RICH TEXT FIELD
	// S'affiche si on a les champs image et page

    if (
    	(in_array('image', $allowedLayouts) && in_array('page', $allowedLayouts))
        || in_array('text-columns', $allowedLayouts)
    )
	{
		// Block contenu riche
		$layouts[] = Layout::make('Texte riche en colonnes', 'rich-text-columns')->layout('block')->fields([
			create_columns_group_field([
				WysiwygEditor::make(' ', translate_field_name('content-1'))
					->mediaUpload( false )
					->wrapper(['class' => 'clean']),
				WysiwygEditor::make(' ', translate_field_name('content-2'))
					->mediaUpload( false )
					->wrapper(['class' => 'clean']),
			]),
			create_internal_page_link_field(),
			create_internal_page_link_text_field()
		]);
	}

	// ------------------------------------------------------------------------- PAGE FIELDS
	// Les blocs de base des pages

    if ( in_array('page', $allowedLayouts) )
	{
		// Block citation
		$layouts[] = Layout::make('Citation', 'quote')->layout('row')->fields([
			Textarea::make('Contenu', translate_field_name('quote'))
				->rows(3)
				->instructions("Évitez de dépasser 5 lignes."),
			ButtonGroup::make('Couleur de fond', 'background')->choices([
				'none' => 'Pas de fond',
				'bright' => 'Fond clair'
			]),
			ButtonGroup::make('Alignement', 'alignment')->choices([
				'left' => 'Gauche',
				'center' => 'Centré'
			])
		]);

		$layouts[] = create_separator_layout();

		// Bloc liste d'arguments
		$layouts[] = Layout::make("Liste d'arguments", 'argument-list')->layout('row')->fields([
			Image::make('Image', 'image')
				->instructions("Optionnelle. Pleine largeur, placée au dessus des arguments."),
			Textarea::make('Argument principal', translate_field_name('title'))
				->rows(4)
				->instructions("Texte devant le chiffre, sur la gauche.")
				->required(),
			Repeater::make("Arguments", translate_field_name('arguments'))
				->instructions("Minimum 2, maximum 3 points.")
				->required()->min(2)->max(3)
				->layout('row')
				->buttonLabel("Ajouter un argument")
				->fields([
					Text::make("Titre", 'title')->required(),
					Textarea::make("Contenu", 'content')
						->rows(4)
						->required()
				])
		]);

		// Bloc testimonial
		$layouts[] = Layout::make('Témoignage', 'testimonial')->layout('row')->fields([
			Image::make('Image', 'image')
				->required(),
			ButtonGroup::make("Position de l'image", 'position')
				->required()
				->choices([
					'left' => 'Gauche',
					'right' => 'Droite'
				]),
			ButtonGroup::make("Format de l'image", 'format')
				->required()
				->choices([
					'slim' => 'Fin (verticale)',
					'large' => 'Large (horizontale)'
				]),
			Text::make("Titre", translate_field_name('title')),
			Textarea::make("Sous-titre", translate_field_name('sub-title'))
				->rows(2)
				->instructions("Optionnel, maximum 2 lignes."),
			Textarea::make("Description", translate_field_name('description'))
				->instructions("Ce texte peut-être plus long, jusqu'à une dizaine de lignes.")
				->rows(6),
			create_internal_page_link_field(),
			create_internal_page_link_text_field()
		]);
	}

    // ------------------------------------------------------------------------- HOME FIELDS
	// Les champs dispo uniquement sur la home

	if ( in_array('home', $allowedLayouts) )
	{
		$layouts[] = create_separator_layout();

		// Block vers un portfolio
		$layouts[] = Layout::make('Lien portfolio', 'portfolio')->layout('row')->fields([
			PostObject::make('Sélection du portfolio', 'link')
				->postTypes([ 'portfolio' ])
				->required(),
			create_image_group_field([], false, false, false),
			ButtonGroup::make("Position de l'image", 'position')
				->instructions("En mode compact, 2 portfolio peuvent-être affichés en juxtaposition sur desktop.")
				->choices([
					'left' => 'Gauche',
					'right' => 'Droite',
					'compact' => 'Compact'
				])
				->required(),
			TrueFalse::make('Décalage', 'offset')
				->instructions("En mode compact, permet de décaler ce bloc verticalement par rapport à son prédécesseur.")
				->stylisedUi(),
			Text::make('Titre', translate_field_name('title'))
				->instructions("Optionnel, utilisera le titre du portfolio ciblé si non renseigné."),
			Text::make('Sous titre', translate_field_name('sub-title'))
				->instructions("Optionnel, utilisera le sous-titre du portfolio ciblé si non renseigné."),
			Textarea::make('Description', translate_field_name('description'))
				->instructions("Optionnel mais conseillé. Plutôt court.")->rows(3),
		]);
	}

	// ------------------------------------------------------------------------- IMAGE FIELDS

	if ( in_array('image', $allowedLayouts) )
	{
		$layouts[] = create_separator_layout();

		// Block vers un portfolio
		$layouts[] = Layout::make('Images', 'images')->layout('block')->fields([
			create_columns_group_field([
				create_image_group_field([], true, true, false, 'image-1', ' ')->wrapper(['class' => 'clean']),
				create_image_group_field([], true, false, false, 'image-2', ' ')->wrapper(['class' => 'clean'])
			]),
			ButtonGroup::make("Dimension des images", 'size')
				->instructions("1080 et le plein format ne sont utilisable qu'avec une image sur la ligne.")
				->required()
				->defaultValue('60-60')
				->choices([
					'36-84' => '360 / 840',
					'48-72' => '480 / 720',
					'54-66' => '540 / 660',
					'60-60' => '600 / 600',
					'66-54' => '660 / 540',
					'72-48' => '720 / 480',
					'84-36' => '840 / 360',
					'1080' => '1080',
					'full' => 'Plein format'
				]),
			ButtonGroup::make("Alignement vertical", 'vertical-align')
				->instructions("Permet de décaler verticalement une image par rapport à l'autre.")
				->choices([
					'left' => 'Gauche en haut',
					'right' => 'Droite en haut',
					'center' => 'Centré',
					'top' => 'Aligné en haut',
				]),
			ButtonGroup::make("Alignement horizontal", 'horizontal-align')
				->instructions("Pour centrer horizontalement les images seules sur une ligne.")
				->choices([
					'left' => 'À gauche',
					'right' => 'À droite',
					'center' => 'Centré'
				])
		]);
	}

    // ------------------------------------------------------------------------- ARTICLE FIELDS

	if ( in_array('article', $allowedLayouts) )
	{
		if ( !empty($layouts) )
			$layouts[] = create_separator_layout();

		$layouts[] = Layout::make("Titre", 'article-title')->layout('row')->fields([
			Textarea::make("Contenu", translate_field_name('content'))
				->instructions("Maximum 3 lignes")
				->required()
				->rows(3),
			ButtonGroup::make("Taille", 'size')
				->required()
				->choices([
					'small' => 'Petit',
					'medium' => 'Moyen',
					'big' => 'Grand'
				])
		]);
		$layouts[] = Layout::make("Grand paragraphe", 'article-regular-text')->layout('row')->fields([
			Textarea::make("Contenu", translate_field_name('content'))
				->required()
				->rows(5),
			ButtonGroup::make("Rendu", 'rendering')
				->required()
				->choices([
					'regular' => 'Normal',
					'alternative' => 'Alternatif'
				])
		]);
		$layouts[] = Layout::make("Texte riche", 'article-rich-text')->fields([
			WysiwygEditor::make(' ', translate_field_name('content'))
				->required()
				->mediaUpload( false )
				->wrapper(['class' => 'clean']),
		]);
		$layouts[] = Layout::make("Image de contenu", 'article-image')->layout('row')->fields([
			Image::make('Image', 'image')
				->instructions("Une image alignée dans le contenu.")
				->required(),
			ButtonGroup::make("Débordement de l'image", 'overflow')
				->required()
				->choices([
					'none' => 'Aucun',
					'small' => 'Léger',
					'large' => 'Grand'
				])
		]);
	}

	// ------------------------------------------------------------------------- GALLERY FIELD

	if ( in_array('gallery', $allowedLayouts) )
	{
		$layouts[] = Layout::make("Galerie", 'gallery')->fields([
			Repeater::make(' ', 'images')->buttonLabel("Ajouter une image")->fields([
				Image::make('Image', 'image')->wrapper(['class' => 'smallImage'])
					->required(),
			])
		]);

		$layouts[] = Layout::make("Vidéo", 'video')->layout("row")->fields([
			File::make('Fichier video', 'video')->mimeTypes(['mp4'])->required(),
			File::make('Image poster', 'poster')
				->instructions("Optionnel, l'image affichée avant d'avoir cliqué sur play.")
		]);
	}

	// ------------------------------------------------------------------------- PAGE TYPE FIELD

	if ( in_array('type', $allowedLayouts) )
	{
		$layouts[] = create_separator_layout();

		$layouts[] = Layout::make("Ancres", 'anchors')->layout('row')->fields([
			Message::make(' ', 'm')->message("Affiche les ancres de tous les blocs page type ci-dessous.")
		]);

		$layouts[] = Layout::make("Bloc page type", 'type-bloc')->layout('row')->fields([
			Text::make("Titre", translate_field_name('title'))
				->required(),
			Text::make("Ancre", translate_field_name('anchor'))
				->instructions("Ajoute une entrée dans le bloc 'ancres' précédent."),
			WysiwygEditor::make('Contenu', translate_field_name('content'))
				->required()
				->mediaUpload( false ),
			Image::make('Image', 'image')
				->instructions("Optionnelle."),
			Textarea::make("Texte d'accroche", translate_field_name('calling'))
				->rows(2)
				->instructions("Optionnel. Sur 2 lignes pour inviter à cliquer sur le bouton."),
			create_internal_page_link_field(),
			ButtonGroup::make("Type de lien", "link-type")
				->required()
				->choices([
					'link' => 'Lien',
					'button' => 'Bouton'
				])->conditionalLogic([
					ConditionalLogic::if('link')->notEmpty()
				]),
			create_internal_page_link_text_field()
		]);
	}

	// ------------------------------------------------------------------------- END

    return [
        'title' => " ",
        'noBorders' => true,
        'asGroup' => false,
        'fields' => [
            FlexibleContent::make('Blocs de contenu', 'flexible')
                ->buttonLabel("Ajouter un bloc")
                ->layouts( $layouts )
        ]
    ];
}
