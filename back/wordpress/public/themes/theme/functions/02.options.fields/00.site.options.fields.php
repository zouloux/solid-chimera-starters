<?php

// ----------------------------------------------------------------------------- SITE OPTIONS

$screen = register_custom_screen('options_page', [
    'name' => 'options-site',
    'label' => "Site options",
    'icon' => 'dashicons-admin-generic',
	'position' => 1
], create_default_site_options_groups([

]));
