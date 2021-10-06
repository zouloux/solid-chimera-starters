<?php

/**
 * Get all dictionaries if $id is null.
 * Get dictionary key / values if $id is not null.
 * Get translated value if $id and $key are not null.
 */
function get_dictionary ( $id = null, $key = null ) {
	// Init dictionaries only once and cache them
	global $_bowlDictionaries;
	if ( is_null($_bowlDictionaries) ) {
		// Create a clean dictionaries list
		$_bowlDictionaries = [];
		// Browse stored dictionaries
		$dictionariesEntries = option( 'dictionaries' );
		// Translated data key
		$translatedDataKey = translate_field_name('data');
		if ( !is_null($dictionariesEntries) )
			foreach ( $dictionariesEntries as $localDictionary ) {
				if ( is_null($localDictionary) || is_null($localDictionary[ $translatedDataKey ]) ) continue;
				$dictionary = [];
				foreach ( $localDictionary[ $translatedDataKey ] as $dictionaryEntry )
					$dictionary[ $dictionaryEntry['key'] ] = $dictionaryEntry[ 'value' ];
				$_bowlDictionaries[ $localDictionary['id'] ] = $dictionary;
			}
	}

	// No id, no key, return all dictionaries
	if ( is_null($id) && is_null($key) )
		return $_bowlDictionaries;
	// We got a valid dictionary id but no key, return selected dictionary
	else if ( !is_null($id) && is_null($key) && !is_null($_bowlDictionaries[$id]) )
		return $_bowlDictionaries[ $id ];
	// We go a valid dictionary and a valid key, return translated value
	else if ( !is_null($id) && !is_null($key) && !is_null($_bowlDictionaries[$id]) && !is_null($_bowlDictionaries[$id][$key]) )
		return $_bowlDictionaries[ $id ][ $key ];
	// We got some arguments but something is not found, return nothing
	else
		return null;
}

/**
 * Get site option API keys
 */
function get_key ( $key = null ) {
	$keys = option('keys');
	if (!is_array($keys)) return [];
	$output = [];
	foreach ( $keys as $keyAssociation ) {
		$k = $keyAssociation['key'];
		$v = $keyAssociation['value'];
		if ( $k == $key ) return $v;
		$output[$k] = $v;
	}
	return $output;
}

/**
 * Get theme options from site options.
 */
function get_theme_options () {
	global $_bowlThemeOptions;
	if (is_null($_bowlThemeOptions))
		$_bowlThemeOptions = option('options-site-themeOptions');
	return $_bowlThemeOptions;
}