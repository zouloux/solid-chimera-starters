<?php

BowlFields::register(function () {
	$fields = BowlFields::createSingletonFields( "site-options" );
	$fields->menu( ["Options générales"], "dashicons-admin-generic" );

	// ------------------------------------------------------------------------- META
	$fields->attachGroup( "meta", bowl_create_meta_fields_group("Default meta") );

	// ------------------------------------------------------------------------- THEME OPTIONS
	$fields->attachGroup( "theme", bowl_create_theme_options_fields_group());

	// ------------------------------------------------------------------------- DICTIONARIES
	$fields->attachGroup( "dictionaries", bowl_create_dictionaries_fields_group() );
	$fields->addFilter( bowl_create_dictionaries_filter("dictionaries") );

	// ------------------------------------------------------------------------- KEYS
	$fields->attachGroup( "keys", bowl_create_keys_fields_group() );
	$fields->addFilter( bowl_create_keys_filter("keys") );

	return $fields;
});
