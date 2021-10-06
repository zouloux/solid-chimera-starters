<?php

// -----------------------------------------------------------------------------

register_custom_screen('post_type', [
	'name' => 'page',
	'not' => array_keys(CUSTOM_PAGE_TEMPLATES_BY_ID),
	//'editor' => false // NOTE : Remove editor on all pages
], [
	//'flexible' => create_flexible_fields()
]);