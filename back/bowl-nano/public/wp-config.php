<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/dot-envs.php';
require_once __DIR__ . '/nano-init.php';

use Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

// Load the environment variables.
Dotenv::createImmutable( realpath(__DIR__ . '/../') )->safeLoad();

// Detect HTTPS behind a reverse proxy or a load balancer.
if ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' )
	$_SERVER['HTTPS'] = 'on';

// Set the absolute path to the WordPress directory.
if ( !defined('ABSPATH') )
	define('ABSPATH', sprintf('%s/%s/', __DIR__, env('WP_DIR', 'wordpress')));

// Set the database table prefix. ( This is a global )
$table_prefix = env('DB_TABLE_PREFIX', 'wp_');

// Some reused envs
$isDebugModeEnabled = env('WP_DEBUG', false);
$wpHome = env('WP_HOME', Request::createFromGlobals()->getSchemeAndHttpHost());

// Define those envs from the dot env file.
// Value is defined for default if not found in dot env.
defineEnvs([
	// Set the environment type.
	'WP_ENVIRONMENT_TYPE' => 'production',
	// Set the default WordPress theme.
	'WP_DEFAULT_THEME', 'WP_THEME',
	// For developers: WordPress debugging mode.
	'WP_DEBUG' => $isDebugModeEnabled,
	'WP_DEBUG_DISPLAY' => $isDebugModeEnabled,
	'SCRIPT_DEBUG' => $isDebugModeEnabled,
	// The database configuration with database name, username, password,
	// hostname charset and database collate type.
	'DB_NAME', 'DB_USER', 'DB_PASSWORD',
	'DB_HOST' => '127.0.0.1',
	'DB_CHARSET' => 'latin1',
	'DB_COLLATE' => 'latin1_general_ci',
	// Set the unique authentication keys and salts.
	'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
	'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT',
	// Set the home url to the current domain.
	'WP_HOME' => $wpHome,
	// Set the WordPress directory path.
	'WP_SITEURL' => sprintf('%s/%s', $wpHome, env('WP_DIR', 'wordpress')),
	// Set the WordPress content directory path.
	'WP_CONTENT_DIR' => __DIR__,
	'WP_CONTENT_URL' => $wpHome,
	// Disable WordPress auto updates.
	'AUTOMATIC_UPDATER_DISABLED' => true,
	// Disable WP-Cron (wp-cron.php) for faster performance.
	'DISABLE_WP_CRON' => false,
	// Prevent file editing from the dashboard.
	'DISALLOW_FILE_EDIT' => true,
	// Disable plugin and theme updates and installation from the dashboard.
	'DISALLOW_FILE_MODS' => true,
	// Cleanup WordPress image edits.
	'IMAGE_EDIT_OVERWRITE' => true,
	// Disable technical issue emails.
	'WP_DISABLE_FATAL_ERROR_HANDLER' => false,
	// Limit the number of post revisions.
	'WP_POST_REVISIONS' => 0,
]);

require_once ABSPATH . 'wp-settings.php';
