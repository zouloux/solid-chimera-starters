<?php

use Twig\TwigFilter;
use Twig\TwigFunction;

add_filter( 'timber/twig', function ( $twig )
{
	// ------------------------------------------------------------------------- DICTIONARY FUNCTION

	/**
	 * TODO : Usage
	 */
	$twig->addFunction(
		new TwigFunction( 'dictionary', function ( $key, $values = [] ) {
			$template = get_dictionary( $key );
			return (
				count($values) > 0
				? BowlUtils::quickMustache( $template, $values )
				: $template
			);
		})
	);

	// ------------------------------------------------------------------------- TEMPLATE FILTER

	/**
	 * Use BowlUtils::quickMustache
	 * {% set templateString = "Bonjour, mon nom est {{name}}, {{fullName}}" %}
	 * {{
	 *      templateString|template([
	 *          'name' : 'Bond',
	 *          'fullName' : 'James Bond'
	 *      ])
	 * }}
	 */
	$twig->addFilter(
		new TwigFilter( 'template', function ( $string, $values ) {
			return BowlUtils::quickMustache( $string, $values);
		})
	);

	// ------------------------------------------------------------------------- IMAGE FILTER

	/**
	 * Permet de récupérer les données pour une taille d'image.
	 * Ex :
	 * {% set introImage = pageData.introduction.image|image('large') %}
	 * src="{{ introImage.href }}"
	 */
	$twig->addFilter(
		new TwigFilter( 'image', function ( $imageObject, $preferredSize )
		{
			// Return null if image object is not complete
			if ( is_null($imageObject) ) return null;
			if ( !isset($imageObject['sizes']) ) return null;

			// Select correct size if found
			if ( isset($imageObject['sizes'][ $preferredSize ]) )
				$selectedHref = $imageObject['sizes'][ $preferredSize ];

			// Check nearest previous size
			else foreach ( BOWL_IMAGE_SIZES as $key => $imageSize ) {
				if ( $imageSize == $preferredSize && isset( BOWL_IMAGE_SIZES[ $key - 1 ] ) )
				{
					$preferredSize = BOWL_IMAGE_SIZES[ $key - 1 ];
					$selectedHref = $imageObject['sizes'][ $preferredSize ];
					break;
				}
			}

			// If not found, never return native image link for security reasons
			if ( !isset($selectedHref) || !$selectedHref ) return null;

			// Return image data
			return [
				'width' => $imageObject['sizes'][ $preferredSize.'-width' ] ?? 0,
				'height' => $imageObject['sizes'][ $preferredSize.'-height' ] ?? 0,
				'size' => $preferredSize,
				'href' => $selectedHref,
				'ratio' => $imageObject['width'] / $imageObject['height'],
				'name' => $imageObject['name']
			];
		})
	);

	// ------------------------------------------------------------------------- SPLIT TEXT FILTER

	function renderSplitterPart ( $part, $tag, $className, $spanInSpan = false ) {
		$output = "<${tag} class=\"${className}\">";
		if ($spanInSpan) $output .= '<span>';
		$output .= $part;
		if ($spanInSpan) $output .= '</span>';
		return $output."</${tag}>";
	}

	$twig->addFilter(
		new TwigFilter( 'splitter', function ( $string, $type = 'br', $tag = 'span', $spanInSpan = false, $className = '', $insertBreaks = true ) {
			$string = str_replace("\r\n", "\n", $string);
			$string = str_replace('<br>', '<br/>', $string);
			$string = str_replace('<br />', '<br/>', $string);

			if ( $type == 'br' || $type == 'word' )
				$lines = explode('<br/>', $string);
			else if ( $type == 'nl' )
				$lines = explode("\n", $string);
			else
				throw new Exception("Invalid splitter type${type}.");

			$outputLines = [];
			foreach ( $lines as $line ) {
				if ( $type == 'word' ) {
					$words = explode(' ', $line);
					$line = '';
					foreach ( $words as $word )
						$line .= renderSplitterPart( $word, $tag, $className, $spanInSpan );
					$outputLines[] = $line;
				}
				else
					$outputLines[] = renderSplitterPart( $line, $tag, $className, $spanInSpan);
			}

			return implode( $insertBreaks ? "<br/>" : '', $outputLines );
		})
	);

	// ------------------------------------------------------------------------- STRING TEST

	/**
	 * Check if a value is a string
	 * {% if myVar is string *}
	 */
	$twig->addTest(
		new Twig_SimpleTest('string', function ($value) {
			return is_string($value);
		})
	);


	return $twig;
});