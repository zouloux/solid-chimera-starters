<?php

use Nano\core\Nano;
use Pecee\SimpleRouter\SimpleRouter;

// ----------------------------------------------------------------------------- WEBSITE RESPONDERS

SimpleRouter::get('/robots.txt', function () {
	// TODO : Add an override in .env
	Nano::action("Bowl", "printRobots");

});
SimpleRouter::get('/sitemap.xml', function () {
	// TODO : Check if post exists in other languages
	// TODO : Add pages with other locales
	Nano::action("Bowl", "printSitemap");
});
