<?php

declare(strict_types=1);

use Nano\core\Nano;
use Nano\renderers\native\NativeRenderer;

require_once __DIR__ . '/nano-init.php';
// Render templates with native php tags
Nano::$renderer = new NativeRenderer();
// Load routes responders from app/responders/*.responder.php
Nano::loadResponders();
// Start router and execute matching route
Nano::start();
