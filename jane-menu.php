<?php

/**
 * Jane Menu
 *
 * @package IHeartJane
 *
 * @wordpress-plugin
 * Plugin Name:         Jane Menu
 * Plugin URI:          https://www.iheartjane.com/plugins/jane-menu-plugin
 * Description:         Dispensary manager to display partner product menu.
 * Version:             1.4.6
 * Requires at least:   5.9
 * Requires PHP:        7.4
 * Author:              Jane Technologies, Inc.
 * Author URI:          https://www.iheartjane.com
 * License:             GPLv2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         iheartjane
 */

/**
 * The current plugin version.
 * Saved in the database to be able to manually check if the table structure needs an update.
 *
 * @since 1.3.0
 *
 * @var string
 */
define('IHeartJane\WebMenu\Constants\PLUGIN_VER', "1.4.6");

/**
 * The filename of the main plugin file including the path.
 * Used for plugin activation/deactivation hooks.
 *
 * @since 1.3.0
 *
 * @var string
 */
define('IHeartJane\WebMenu\Constants\PLUGIN_ID', __FILE__);

// The rest of the plugin's constants
require_once "constants.php";

// Include all the .php files in the /includes/ folder
foreach (glob(plugin_dir_path(__FILE__) . 'includes/' . "*.php") as $file) {

    require_once $file;
}

// Autoload the classes
$namespaces = [
    "IHeartJane\\WebMenu\\" => "classes",
];

IHeartJane\WebMenu\Core\Autoloader\load_package(__DIR__, $namespaces);
