<?php

// Do nothing on front app
if (!is_admin()) return null;

// ----------------------------------------------------------------------------- ABSOLUTE WP CONFIG

// Disable plugins installation, wordpress update, theme installation
//define('DISALLOW_FILE_MODS', true);

// ----------------------------------------------------------------------------- STYLE

// Inject patched admin style
add_action('admin_head', function () {
    $stylePath = get_template_directory_uri().'/admin/admin-style.css';
    echo '<link rel="stylesheet" href="'.$stylePath.'" />';
});
add_action('admin_footer', function () {
    $scriptPath = get_template_directory_uri().'/admin/admin-script.js';
    echo '<script src="'.$scriptPath.'"></script>';
});

// ----------------------------------------------------------------------------- NESTED PAGES

// Remove page attribute panel to disable nested pages (parenting)
add_action( 'init', function () {
    remove_post_type_support('page','page-attributes');
});

// ----------------------------------------------------------------------------- GUTENBERG

// Disable Gutenberg everywhere
add_filter('use_block_editor_for_post', function () { return false; }, 10, 0 );

// ----------------------------------------------------------------------------- TINY MCE CONFIG

// Gestion de la première ligne d'outils de l'éditeur TinyMCE
add_filter( 'mce_buttons', function ($buttons) {
    return [
        // Boutons de la première ligne, triés
//        "styleselect", // On remplace le format de base par les styles custom
        //"formatselect",
        "bold",
        "italic",
        "bullist",
        "numlist",
        //"blockquote",
        //"alignleft",
        //"aligncenter",
        //"alignright",
        "link",
        //"wp_more",
        "spellchecker",
        //"fullscreen",
        //"wp_adv",

        // Boutons de la seconde ligne, repassés sur la première ligne
        //"strikethrough",
//        "hr",
        //"forecolor",
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
});

// Supprimer la seconde ligne
add_filter( 'mce_buttons_2', function ($buttons) { return []; });

// Supprimer les formats de textes
add_filter('tiny_mce_before_init', function ( $init ) {
	$init['style_formats'] = '[]';
	if (!isset($init['content_style'])) $init['content_style'] = '';
	return $init;
	/*
    // Spécifier les styles customs
    $init['style_formats'] = wp_json_encode([
        [
            'title' => 'Paragraphe 1',
            'block' => 'p',
            'classes' => 'big',
            'wrapper' => false
        ],
        [
            'title' => 'Paragraphe 2',
            'block' => 'p',
            'classes' => 'small',
            'wrapper' => false
        ],
        [
            'title' => 'Sous-titre',
            'block' => 'h3',
            'classes' => 'title',
            'wrapper' => false
        ],
    ]);

    // Injecter le style pour les différents styles custom
    if (!isset($init['content_style'])) $init['content_style'] = '';
    $init['content_style'] .= " .big { font-size: 1.2em } ";
    $init['content_style'] .= " .small { font-size: 1em } ";
    $init['content_style'] .= " .title { font-size: 1.4em; font-weight: bold } ";

    return $init;*/
});

// ----------------------------------------------------------------------------- META BOXES

// Disable meta box draggable. Custom admin-script will add back open / close feature
add_action( 'admin_init', function () {
    // Check if we are on an edit / create page in admin
    global $pagenow;
    if ( !in_array($pagenow, ['post-new.php', 'post.php', 'admin.php']) ) return;

    // Remove original drag and drop script
    wp_deregister_script('postbox');

    // Disable drag cursor on postboxes
    // And tell custom script to enable
    $style = ".postbox .hndle { cursor: auto !important; }\n";
    $style .= ".postbox .handle-order-higher, .postbox .handle-order-lower { display: none }\n";
    $script = "window._customMetaboxBehavior = true;";
    inject_custom_admin_resource_for_screen(null, $style, $script);
});

// Add main image meta box on articles
add_action( 'current_screen', function () {
    $screen = get_current_screen();
    if (isset($screen->id) && $screen->id === 'post')
        add_theme_support( 'post-thumbnails' );
} );

// Remove excerpt meta box
add_action( 'admin_init', function () {
	remove_post_type_support( 'post', 'excerpt' );
});


// Clean meta box on sidebar
add_action( 'add_meta_boxes', 'move_tags_metabox_location', 0 );
function move_tags_metabox_location(){
    global $wp_meta_boxes;
    //dump($wp_meta_boxes);exit;
    foreach ( $wp_meta_boxes as $key => $value )
    {
        // Author meta box on side
        if (isset($wp_meta_boxes[ $key ]['normal']['core']['authordiv']))
        {
            $wp_meta_boxes[ $key ]['side']['core']['authordiv'] = $wp_meta_boxes[ $key ]['normal']['core']['authordiv'];
            unset($wp_meta_boxes[ $key ]['normal']['core']['authordiv']);
        }

        // Remove tags box
        unset($wp_meta_boxes[ $key ]['side']['core']['tagsdiv-post_tag']);

        // Remove slug box
        unset($wp_meta_boxes[ $key ]['normal']['core']['slugdiv']);
    }
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