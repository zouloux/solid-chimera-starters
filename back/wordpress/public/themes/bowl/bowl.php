<?php

/**
 * In your theme's function.php :
 *
 * const BOWL_THEME_DIR = __DIR__;
 * require(__DIR__.'/../bowl/bowl.php');
 */

// No overload when blog is not installed yet
if ( !is_blog_installed() ) return;

/**
 * Autoload php files in a directory.
 * Is recursive.
 * Will load files and directories in ascendant alphanumeric order.
 * Name your files like so :
 * - 00.my.first.file.php
 * - 01.loaded.after.php
 * - 02.you.got.it.php
 * Can also start at 01.
 */
function auto_load_functions ( $directory ) {
	$files = scandir( $directory );
	foreach ( $files as $file ) {
		if ( $file == '.' || $file == '..' ) continue;
		$path = $directory.'/'.$file;
		if ( is_dir($path) )
			auto_load_functions( $path );
		else
			require_once( $directory.'/'.$file );
	}
}

// Auto-load recursively in order
auto_load_functions( BOWL_THEME_DIR.'/configs' );
auto_load_functions( __DIR__.'/functions' );
auto_load_functions( BOWL_THEME_DIR.'/functions' );

// Call a hook after all functions are loaded / executed
// Mandatory for custom meta box order
apply_filters('after_functions', null);
