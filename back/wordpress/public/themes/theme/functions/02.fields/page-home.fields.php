<?php

// -----------------------------------------------------------------------------

$screen = register_custom_screen('page', [
    'name' => 'home-page',
    'page' => get_custom_page_id('home'),
    'editor' => false,
    'restrictDeletion' => true
], [

    'introduction' => create_intro_group_field([
		'image' => create_image_group_field()
	]),

	'flexible' => create_flexible_content_fields(['page', 'home']),

	'contact-button' => create_contact_button_group(),
]);
