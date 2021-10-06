<?php

// ----------------------------------------------------------------------------- WP CONFIG

// Disable plugins installation, WordPress update, theme installation
const DISALLOW_FILE_MODS = true;

// ----------------------------------------------------------------------------- ADMIN BOWL CONFIG

// All meta box are in place and can't be moved by user
const BOWL_DISABLE_META_BOX_DRAGGABLE = true;

// Sentence added next to translatable fields
// TODO : Automate
const BOWL_TRANSLATE_HINT = '';//' <span class="bowl_translated">(translated field)</span>';

// ----------------------------------------------------------------------------- ADMIN NEWS BOWL CONFIG
// BOWL_DISABLE_NEWS needs to be set at false to be used

// Add meta image meta box on side
const BOWL_ADD_IMAGE_META_BOX = false;

// Disable excerpt edition in news
const BOWL_DISABLE_EXCERPT = true;

// Move author meta box on right side
const BOWL_AUTHOR_META_BOX_ON_SIDE = true;

// Disable tags or slug meta boxes
const BOWL_DISABLE_TAGS = true;
const BOWL_DISABLE_SLUG = true;

// ----------------------------------------------------------------------------- ADMIN EDITOR BOWL CONFIG

// Disable Gutenberg editor everywhere and enable classic editor
const BOWL_DISABLE_GUTENBERG = true;

// List allowed buttons in TinyMCE editor, everywhere
function bowl_mce_buttons ( $lineIndex, $buttons ) {
	// Line 1
	if ( $lineIndex === 0 )
		return [
			"styleselect",
//			"formatselect",
			"bold",
			"italic",
			"bullist",
			"numlist",
			// "blockquote",
			"alignleft", "aligncenter", "alignright",
			"link",
			//"wp_more",
			"spellchecker",
			"fullscreen",
			// "wp_adv",

			// Second line buttons, placed on first line
			// "strikethrough", "hr", "forecolor",
			"pastetext",
			"removeformat",
			"charmap",
			"outdent",
			"indent",
			"undo",
			"redo",
			"wp_help",
			"emoticons"
		];

	// Line 2
	else if ( $lineIndex === 1 )
		return [];
}

// ----------------------------------------------------------------------------- THEME CUSTOMIZER

// Remove useless theme customizer options
add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->remove_section( 'themes' );
	$wp_customize->remove_section( 'wporg_themes' );
	$wp_customize->remove_section( 'installed_themes' );
	$wp_customize->remove_section( 'add_menu' );
	$wp_customize->remove_section( 'custom_css' );
	$wp_customize->remove_section( 'wpseo_breadcrumbs_customizer_section' );
	$wp_customize->remove_section( 'menu_locations' );
	$wp_customize->remove_section( 'header_image' );
	$wp_customize->remove_section( 'colors' );
}, 30);

// ----------------------------------------------------------------------------- TINY MCE CONFIG

const BOWL_MCE_STYLES = [
	[
		'title' => 'Normal',
		'block' => 'p',
		'classes' => '',
		'wrapper' => false,
		'style' => [
			'font-size' => '1em',
		]
	], [
		'title' => 'Citation',
		'block' => 'p',
		'classes' => 'Rich_quote',
		'wrapper' => false,
		'style' => [
			'font-size' => '1.2em',
			'font-style' => 'italic'
		]
	], [
		'title' => 'Pied de page',
		'block' => 'p',
		'classes' => 'Rich_pageFooter',
		'wrapper' => false,
		'style' => [
			'font-size' => '.8em',
		]
	], [
		'title' => 'Légende',
		'block' => 'p',
		'classes' => 'Rich_caption',
		'wrapper' => false,
		'style' => [
			'font-size' => '1em',
			'text-align' => 'right',
			'font-weight' => 'bold',
		]
	],
];
