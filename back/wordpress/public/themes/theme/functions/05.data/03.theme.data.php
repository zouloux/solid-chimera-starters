<?php

// ----------------------------------------------------------------------------- CONTEXT DATA SETTERS
// Data for twig views

// Inject resources to load into twig context
register_context_data('resources', function ($context) {
    return [
        'head' => [
            'scripts' => [],
            'styles' => [],
        ],
        'body' => [
            'scripts' => ['index.js'],
            'styles' => ['index.css'],
        ]
    ];
});

//register_context_data('...', function ($context) { return null; });

// ----------------------------------------------------------------------------- APP DATA SETTERS
// Data for javascript

//register_app_data('...', function ($context) { return null; });


// -----------------------------------------------------------------------------



