<?php

use WordPlate\Acf\Fields\ButtonGroup;
use WordPlate\Acf\Fields\Repeater;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\Textarea;
use WordPlate\Acf\Fields\Wysiwyg;

// -----------------------------------------------------------------------------

register_custom_screen('post_type', [
    'name' => 'hub',
    'labels' => ['un hub', 'Hubs'],
    'icon' => 'dashicons-networking'
], [
	'introduction' => create_intro_group_field([
		'image' => create_image_group_field()
	]),

	'flexible' => create_flexible_content_fields(['page', 'home', 'text-columns']),

	'contact-button' => create_contact_button_group(),
]);
