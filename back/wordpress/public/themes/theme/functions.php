<?php

if (!is_blog_installed()) return;

// ----------------------------------------------------------------------------- LOAD FUNCTIONS FILES

function auto_load_functions ( $directory )
{
    $files = scandir( $directory );
    foreach ( $files as $file )
    {
        if ( $file == '.' || $file == '..' ) continue;
        $path = $directory.'/'.$file;
        if ( is_dir($path) )
            auto_load_functions( $path );
        else
            require_once( $directory.'/'.$file );
    }
}

// Auto-load recursively all functions
auto_load_functions( __DIR__.'/functions' );

// Call a hook after all functions are loaded / executed
// Mandatory for custom meta box order
apply_filters('after_functions', null);

// -----------------------------------------------------------------------------
