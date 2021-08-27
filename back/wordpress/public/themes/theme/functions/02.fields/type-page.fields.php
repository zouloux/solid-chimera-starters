<?php

use WordPlate\Acf\Fields\Textarea;

// -----------------------------------------------------------------------------

register_custom_screen('post_type', [
	'name' => 'page',
	'not' => array_keys(CUSTOM_PAGE_TEMPLATES_BY_ID),
	'editor' => false // NOTE : Remove editor on all pages
], [

	'introduction' => create_intro_group_field([
		'text' => Textarea::make("Texte d'introduction", translate_field_name('intro-text'))
			->rows(6),
		'image' => create_image_group_field()
	]),

	'flexible' => create_flexible_content_fields(['page', 'gallery', 'article', 'type']),

	'contact-button' => create_contact_button_group(),
	'contact' => create_contact_block_group_field(),
]);