<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://
 * @since             1.0.0
 * @package           Resme
 *
 * @wordpress-plugin
 * Plugin Name:       Reserve Me
 * Plugin URI:        http://
 * Description:       This plug in can be added to allow management of reservations for courts, rooms, or other assets.
 * Version:           1.0.0
 * Author:            Dave Blosser
 * Author URI:        http://
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       resme
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RESME_NAME_VERSION', '1.0.0.34' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-resme-activator.php
 */
function activate_resme() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-resme-activator.php';
	Resme_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-resme-deactivator.php
 */
function deactivate_resme() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-resme-deactivator.php';
	Resme_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_resme' );
register_deactivation_hook( __FILE__, 'deactivate_resme' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-resme.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_resme() {

	$plugin = new Resme();
	$plugin->run();

}
run_resme();
