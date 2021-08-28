<?php
/**
 * Plugin Name: Folders / ZoulouX Patched
 * Description: Arrange media, pages, custom post types and posts into folders
 * Version: 2.6.1 - Patched
 * Author: Premio
 * Author URI: https://premio.io/downloads/folders/
 * Text Domain: folders
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if(!defined("WCP_FOLDERS_PLUGIN_FILE")) {
    define('WCP_FOLDERS_PLUGIN_FILE', __FILE__);
}
if(!defined("WCP_FOLDERS_PLUGIN_BASE")) {
    define('WCP_FOLDERS_PLUGIN_BASE', plugin_basename(WCP_FOLDERS_PLUGIN_FILE));
} 
if(!defined("WCP_FOLDER")) {
    define('WCP_FOLDER', 'folders');
}
if(!defined("WCP_FOLDER_VAR")) {
    define('WCP_FOLDER_VAR', 'folders_settings');
}
if(!defined("WCP_DS")) {
    define("WCP_DS", DIRECTORY_SEPARATOR);
}
if(!defined("WCP_FOLDER_URL")) {
    define('WCP_FOLDER_URL', plugin_dir_url(__FILE__));
}
if(!defined("WCP_FOLDER_VERSION")) {
    define('WCP_FOLDER_VERSION', "2.6.1");
}

include_once plugin_dir_path(__FILE__)."includes/folders.class.php";
register_activation_hook( __FILE__, array( 'WCP_Folders', 'activate' ) );
WCP_Folders::get_instance();

// Break folder limit
if (is_admin())
	update_option("folder_old_plugin_folder_status", 999);