<?php

// ----------------------------------------------------------------------------- LAYOUTS


// ----------------------------------------------------------------------------- FLEXIBLE

function create_flexible_fields ( $allowedFields = [], $onlyField = false )
{
	$layouts = [];

	$field = FlexibleContent::make('Contenus', 'flexible')
		->buttonLabel("Ajouter un contenu")
		->layouts( $layouts );
	if ( $onlyField ) return $field;

	return [
		'title' => " ",
		'noBorders' => true,
		'asGroup' => false,
		'fields' => [ $field ]
	];
}
