<?php

// Do nothing on front app
if (!is_admin()) return null;

// ----------------------------------------------------------------------------- STYLE

// Inject patched admin style
add_action('admin_head', function () {
	$stylePath = get_template_directory_uri().'/../bowl/assets/admin-style.css';
	echo '<link rel="stylesheet" href="'.$stylePath.'" />';
});
add_action('admin_footer', function () {
	$scriptPath = get_template_directory_uri().'/../bowl/assets/admin-script.js';
	echo '<script src="'.$scriptPath.'"></script>';
});

// ----------------------------------------------------------------------------- NESTED PAGES

// Remove page attribute panel to disable nested pages (parenting)
if ( defined('BOWL_DISABLE_NESTED_PAGES') && BOWL_DISABLE_NESTED_PAGES ) {
	add_action( 'init', function () {
		remove_post_type_support('page','page-attributes');
	});
}

// ----------------------------------------------------------------------------- DISABLE BLOG

// Disable blog feature
if ( defined('BOWL_DISABLE_NEWS') && BOWL_DISABLE_NEWS ) {
	add_action('admin_menu', function () {
		remove_menu_page('edit.php');
	});
	add_action('wp_before_admin_bar_render', function () {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('new-post');
	});
	add_action('wp_dashboard_setup', function () {
		global $wp_meta_boxes;
		unset($wp_meta_boxes[ 'dashboard' ][ 'side' ][ 'core' ][ 'dashboard_quick_press' ]);
		unset($wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ][ 'dashboard_recent_comments' ]);
	});
}

// ----------------------------------------------------------------------------- DISABLE GUTENBERG

// Disable Gutenberg everywhere
if ( defined('BOWL_DISABLE_GUTENBERG') && BOWL_DISABLE_GUTENBERG ) {
	add_filter('use_block_editor_for_post', function () { return false; }, 10, 0 );
}

// ----------------------------------------------------------------------------- META BOXES

// Disable meta box draggable. Custom admin-script will add back open / close feature
if ( defined('BOWL_DISABLE_META_BOX_DRAGGABLE') && BOWL_DISABLE_META_BOX_DRAGGABLE ) {
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
}

// Add main image meta box on articles
if ( defined('BOWL_ADD_IMAGE_META_BOX') && BOWL_ADD_IMAGE_META_BOX ) {
	add_action( 'current_screen', function () {
		$screen = get_current_screen();
		if (isset($screen->id) && $screen->id === 'post')
			add_theme_support( 'post-thumbnails' );
	});
}

// Remove excerpt meta box
if ( defined('BOWL_DISABLE_EXCERPT') && BOWL_DISABLE_EXCERPT ) {
	add_action( 'admin_init', function () {
		remove_post_type_support( 'post', 'excerpt' );
	});
}

// Clean meta box on sidebar
add_action('add_meta_boxes', function () {
	global $wp_meta_boxes;
	//dump($wp_meta_boxes);exit;
	foreach ( $wp_meta_boxes as $key => $value ) {
		// Move author meta box on side
		if ( defined('BOWL_AUTHOR_META_BOX_ON_SIDE') && BOWL_AUTHOR_META_BOX_ON_SIDE ) {
			if ( isset($wp_meta_boxes[ $key ]['normal']['core']['authordiv']) ) {
				$wp_meta_boxes[ $key ]['side']['core']['authordiv'] = $wp_meta_boxes[ $key ]['normal']['core']['authordiv'];
				unset($wp_meta_boxes[ $key ]['normal']['core']['authordiv']);
			}
		}
		// Remove tags box
		if ( defined('BOWL_DISABLE_TAGS') && BOWL_DISABLE_TAGS )
			unset($wp_meta_boxes[ $key ]['side']['core']['tagsdiv-post_tag']);
		// Remove slug box
		if ( defined('BOWL_DISABLE_SLUG') && BOWL_DISABLE_SLUG )
			unset($wp_meta_boxes[ $key ]['normal']['core']['slugdiv']);
	}
}, 0);

// ----------------------------------------------------------------------------- TINY MCE EDITOR

add_filter( 'mce_buttons', function ($buttons) { return bowl_mce_buttons(0, $buttons); });
add_filter( 'mce_buttons_2', function ($buttons) { return bowl_mce_buttons(1, $buttons); });

// Generate style formats from config
add_filter('tiny_mce_before_init', function ( $init ) {
	// Declare new styles formats
	$mceStylesFormats = defined('BOWL_MCE_STYLES') ? BOWL_MCE_STYLES : [];
	$init['style_formats'] = wp_json_encode( $mceStylesFormats );

	// Init style rendering of those formats in TinyMCE
	if (!isset($init['content_style']))
		$init['content_style'] = '';

	// Browser formats
	foreach ( $mceStylesFormats as $format ) {
		// Generate style for TinyMce
		$computedStyle = '';
		foreach ( $format['style'] as $key => $value )
			$computedStyle .= $key.': '.$value.'; ';
		$init['content_style'] .= " .".$format['classes']." {".$computedStyle."} ";
	}

	return $init;
});

// ----------------------------------------------------------------------------- MENU SEPARATOR

// https://wordpress.stackexchange.com/questions/2666/add-a-separator-to-the-admin-menu
function add_admin_menu_separator ($position) {
	add_action( 'admin_init', function () use ($position) {
		global $menu;
		$index = 0;
		foreach ( $menu as $offset => $section ) {
			if ( substr( $section[2], 0, 9 ) == 'separator' )
				$index++;
			if ( $offset>=$position )
				break;
		}
		array_splice($menu, $position, 0, [
			['','read',"separator{$index}",'','wp-menu-separator']
		]);
		ksort( $menu );
	});
}