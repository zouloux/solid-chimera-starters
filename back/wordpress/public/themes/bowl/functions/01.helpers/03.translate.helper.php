<?php

// ----------------------------------------------------------------------------- LOCALES

/**
 * Get current locale code, as "fr" or "en"
 */
function get_current_locale () {
    global $_bowlCurrentLocale;
    if ( !isset($_bowlCurrentLocale) ) {
		$_bowlCurrentLocale = (
			function_exists('wpm_get_language')
			? wpm_get_language()
			: ''
		);
	}
    return $_bowlCurrentLocale;
}

/**
 * Get current locale object with more info
 */
function get_current_locale_object () {
    global $_bowlCurrentIsoLocale;
    if ( !isset($_bowlCurrentIsoLocale) ) {
        $locale = get_current_locale();
		if ( function_exists('wpm') )
        	$languages = wpm()->setup->get_languages();
		else
			$languages = [];
        $_bowlCurrentIsoLocale = $languages[ $locale ] ?? null;
    }
    return $_bowlCurrentIsoLocale;
}

/**
 * Get list of all locales.
 */
function get_locale_list () {
	if ( function_exists('wpm') )
		return wpm()->setup->get_languages();
	else
		return [];
}

// ----------------------------------------------------------------------------- TRANSLATIONS

/**
 * Get translated field key.
 * For example : "description" in french locale will give "fr_description"
 */
function translate_field_name ( $fieldName ) {
    return get_current_locale().'_'.$fieldName;
}

/**
 * Get translated field with field() function.
 * Can have a prefix for some groups, like :
 * group_fr_my-field
 */
function get_translated_field ( $fieldName, $prefix = '', $postID = null ) {
    return field( (!empty($prefix) ? $prefix.'_' : '') . translate_field_name( $fieldName ), $postID );
}

/**
 * Get a translation form an array :
 * $array = [
 *  "fr_description" => "translated"
 * ]
 * get_translation($array, 'description'); -> translated
 */
function get_translation ( $object, $key, $defaultValue = null ) {
    return get_default_value( $object, translate_field_name($key), $defaultValue );
}
