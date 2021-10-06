<?php

// ----------------------------------------------------------------------------- FIELDS

/**
 * Get a value from an object with a key, and default it to something if it's not found.
 */
function get_default_value ( $object, $key, $defaultValue ) {
	return (
		( isset($object[ $key ]) && !is_null($object[ $key ]) && !empty($object[ $key ]) )
		? $object[ $key ]
		: (is_callable($defaultValue) ? $defaultValue() : $defaultValue)
	);
}

/**
 * Rename a field inside an array.
 */
function filter_rename_field ( array &$object, string $fromName, string $toName ):array {
	if ( !isset($object[$fromName]) ) return $object;
	$object[ $toName ] = $object[ $fromName ];
	unset( $object[$fromName] );
	return $object;
}