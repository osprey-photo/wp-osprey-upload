<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Wp_Osprey_Upload
 * @subpackage Wp_Osprey_Upload/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Osprey_Upload
 * @subpackage Wp_Osprey_Upload/includes
 * @author     Your Name <email@example.com>
 */
class Wp_Osprey_Upload_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
			// create the custom table
	global $wpdb;
	
	$uploads_name = $wpdb->prefix . 'osprey_uploads';
	$purposes_name = $wpdb->prefix . 'osprey_purposes';
	$archives_name = $wpdb->prefix . 'osprey_archives';
	$charset_collate = $wpdb->get_charset_collate();	
	
	$uploads_sql = "CREATE TABLE IF NOT EXISTS $uploads_name (
		id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		username VARCHAR(30) NOT NULL,
		displayname VARCHAR(30) NOT NULL,
		filename VARCHAR(50) NOT NULL,
		title VARCHAR(50) NOT NULL,
		purpose INT(6) UNSIGNED NOT NULL ,
		status VARCHAR(30), 
		reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		)  $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $uploads_sql );

	$purposes_sql = "CREATE TABLE IF NOT EXISTS $purposes_name (
		id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		title VARCHAR(30) NOT NULL,
		description VARCHAR(50) NOT NULL,
		enabled TINYINT(1)
		)  $charset_collate;";
	dbDelta( $purposes_sql );

	$archive_sql = "CREATE TABLE IF NOT EXISTS $archives_name (
		id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		filename VARCHAR(50) NOT NULL,
		size  INT(6) NOT NULL
		)  $charset_collate;";
	dbDelta( $archive_sql );

	}
}
