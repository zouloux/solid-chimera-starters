<?php

// Do nothing on front app
if (!is_admin()) return null;

// ----------------------------------------------------------------------------- MESSAGES

/**
 * Show a fatal error message on admin.
 * @param $message
 */
function show_admin_error_message ( $message )
{
    $html = <<<HTML
        <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
            <p style="flex-grow:1; background: #eee;padding: 1% 3%;max-width: 500px;border-radius: 10px;text-align: center;font-family: Arial;">${message}</p>
        </div>
HTML;
    die( $html );
}

// ----------------------------------------------------------------------------- RESOURCES

// Inject custom CSS / JS for a screen ID (page / options / etc ...)
function inject_custom_admin_resource_for_screen ( $screenID, $style = null, $script = null)
{
    add_action('admin_head', function () use ($screenID, $style, $script) {
        $screen = get_current_screen();
        if ( !is_null($screenID) && $screen->id != $screenID ) return;
        if ( !is_null($style) )   echo '<style type="text/css">'.$style.'</style>';
        if ( !is_null($script) )  echo '<script type="text/javascript">'.$script.'</script>';
    });
}

// ----------------------------------------------------------------------------- SCREEN PATCH

// Patch admin title for a specific screen
function patch_admin_custom_screen ( $screen )
{
    $titleClass = "h1.wp-heading-inline";

    // If we have labels, this screen is a custom post type
    if ( isset($screen['labels']) )
    {
        // Set titles for add or update actions
        $titles = [
            "Ajouter ".$screen['labels'][0],
            "Modifier ".$screen['labels'][0]
        ];

        // Inject script which will inject correct title and show it
        $script = <<<JS
            jQuery(function ($) {
                $('.post-new-php ${titleClass}').text("{$titles[0]}").css({ opacity: 1 });
                $('.post-php ${titleClass}').text("{$titles[1]}").css({ opacity: 1 });
            });
JS;
        inject_custom_admin_resource_for_screen( $screen['id'], null, $script );
    }

    // Show title
    else
        inject_custom_admin_resource_for_screen( null, "${titleClass} { opacity: 1; } ");
}