<?php

// -----------------------------------------------------------------------------

$screen = register_custom_screen('page', [
    'name' => 'home',
    'page' => get_custom_page_id('home'),
    'editor' => false,
    'restrictDeletion' => true
], [
]);
