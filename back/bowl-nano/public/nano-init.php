<?php

use Nano\core\Nano;

require_once __DIR__ . '/../vendor/autoload.php';

// Init app
Nano::init( __DIR__ );
// Load parent directory .env
Nano::loadEnvs();
