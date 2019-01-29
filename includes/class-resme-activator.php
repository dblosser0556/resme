<?php

/**
 * Fired during plugin activation
 *
 * @link       http://
 * @since      1.0.0
 *
 * @package    Resme
 * @subpackage Resme/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Resme
 * @subpackage Resme/includes
 * @author     Dave Blosser <blosserdl@gmail.com>
 */
class Resme_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{

	

		// create tables
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		if (!function_exists('dbDelta')) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		}

		// courts table
		$table_facilities = $wpdb->prefix . 'resme_facilities';
		$sql = "CREATE TABLE $table_facilities (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			open smallint(2) NOT NULL CHECK (open<=23),
			close smallint(2) NOT NULL CHECK (close<=23),
			days smallint(2) NOT NULL CHECK (days<365),
			history smallint(2) NOT NULL CHECK (days<365),
			allowedtypes varchar(512) NULL,
			CHECK (open<close),
			UNIQUE KEY id (id)
		) $charset_collate;";
		dbDelta($sql);

		// reservations table
		$table_name = $wpdb->prefix . 'resme_reservations';
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			facilityid mediumint(9) NOT NULL,
			type varchar(63) NOT NULL,
			userid mediumint(9) NOT NULL,
			date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			time smallint(2) NOT NULL,
			FOREIGN KEY (facilityid) REFERENCES {$table_facilities}(id) ON DELETE CASCADE,
			UNIQUE KEY id (id)
		) $charset_collate;";
		dbDelta($sql);

		// role table
		$table_name = $wpdb->prefix . 'resme_roles';
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(20) NOT NULL,
			slug varchar(20) NOT NULL,
			maxdays smallint(2) NOT NULL CHECK (maxdays<365),
			maxres mediumint(9) NOT NULL,
			standardrole varchar(40),
			UNIQUE KEY id (id)
		) $charset_collate;";
		dbDelta($sql);

		//add default roles
		$table_name = $wpdb->prefix . 'resme_roles';
		$wpdb->insert(
			$table_name,
			array(
				'name' => 'Member',
				'slug' => 'member',
				'maxdays' => 5,
				'maxres' => 5,
				'standardrole' => 'Subscriber'
			),
			array('%s', '%s', '%d', '%d', '%s')
		);


		$wpdb->insert(
			$table_name,
			array(
				'name' => 'Team Captain',
				'slug' => 'teamcaptain',
				'maxdays' => 5,
				'maxres' => 5,
				'standardrole' => 'Subscriber'
			),
			array('%s', '%s', '%d', '%d', '%s')
		);
		
			// create role and capabilities
		$cap = 'place_reservation';
		

		add_role(
			'member',
			__('Member', 'resme'),
			array('place_reservation' => true, 
			'read' => true)
		);

		add_role(
			'teamcaptain',
			__('Team Captain', 'resme'),
			array('place_reservation' => true, 
				'read' => true)
		);
		
		$role = get_role('administrator');
		$role->add_cap('place_reservation');

	}

}
