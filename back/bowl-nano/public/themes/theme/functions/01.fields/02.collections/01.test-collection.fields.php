<?php


use Extended\ACF\Fields\File;
use Extended\ACF\Fields\Text;

BowlFields::register(function () {
	$fields = BowlFields::createCollectionFields("test-collection");
	$fields->menu(["Un test", "Tests"], null );
	$fields->addGroup("test", "Test")->fields([
		Text::make("Title", "title"),
		File::make("File", "file")
			->returnFormat('object')
	]);
	$fields->attachGroup( "meta", bowl_create_meta_fields_group() );
	$fields->addFilter(function ( $data ) {
		$data['collection'] = true;
		return $data;
	});
	return $fields;
});
