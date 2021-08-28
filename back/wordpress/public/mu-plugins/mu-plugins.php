<?php

/**
 * Plugin Name: Must Use Plugins
 * Plugin URI: https://github.com/roots/bedrock-autoloader
 * Description: Must-use plugins (a.k.a. mu-plugins) are plugins installed in a special directory inside the content folder and which are automatically enabled on all sites in the installation.
 * Version: 1.0.0
 * Author: Roots
 * Author URI: https://roots.io/
 * License: MIT
 */

if ( is_blog_installed() )
{
	// Use default Bedrock autoloader
    $loader = new Roots\Bedrock\Autoloader();

	// Do not exec custom loader
	if ( isset($loader) ) return;

	require_once(ABSPATH.'../themes/yscorporate/custom-mu-loader.php');
	custom_mu_loader();
}
