<?php

/**

 *
 * @link              https://github.com/osprey-photo/wp-osprey-upload
 * @since             1.0.0
 * @package           Wp_Osprey_Upload
 *
 * @wordpress-plugin
 * Plugin Name:       Osprey Image Uploader WordPress Plugin
 * Plugin URI:        https://github.com/osprey-photo/wp-osprey-upload
 * Description:       Wordpress Plugin for reliable image upload
 * Version:           1.0.0
 * Author:            Matthew B white
 * Author URI:        https://github.com/osprey-photo
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-osprey-upload
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
define( 'PLUGIN_NAME_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-osprey-upload-activator.php
 */
function activate_wp_osprey_upload() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-osprey-upload-activator.php';
	Wp_Osprey_Upload_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-osprey-upload-deactivator.php
 */
function deactivate_wp_osprey_upload() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-osprey-upload-deactivator.php';
	Wp_Osprey_Upload_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_osprey_upload' );
register_deactivation_hook( __FILE__, 'deactivate_wp_osprey_upload' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-osprey-upload.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 */
function run_wp_osprey_upload() {

	$plugin = new Wp_Osprey_Upload();
	$plugin->run();

}
run_wp_osprey_upload();

