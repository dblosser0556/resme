<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://
 * @since      1.0.0
 *
 * @package    Resme
 * @subpackage Resme/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Resme
 * @subpackage Resme/admin
 * @author     Dave Blosser <blosserdl@gmail.com>
 */
class Resme_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/resme-admin.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/resme-admin.js', array('jquery'), $this->version, false);
	}

	private function handleError($msg, $code = 400)
	{
		PC::debug($msg);
		status_header($code);
		echo $msg;
	}





	public function get_facility_schedule()
	{
		PC::debug($_REQUEST);
		// build the table header
		
		$startDate = $_REQUEST['startDate'];
		$displayDate = date_create($startDate);
		$endDate = $_REQUEST['endDate'];
		$displayDays = intval($_REQUEST['displayDays']);

		$header = "<thead><th>Time</th>";

		for ($i = 0; $i < $displayDays; $i++) {
			
			$header .= "<th>" . date_format($displayDate, 'm/d/y') . "</th>";
			date_add($displayDate, new DateInterval('P1D'));
		}
		$header .= "</thead>";

		// build the table body
		$court = $this->getCourtByID($_REQUEST['id']);
		$this->reservations = $this->getCurrentReservationsByID($court->id, $startDate, $endDate);

		$currentUser = $this->getCurrentResmeUser();
		PC::debug($currentUser);

		$body = "<tbody>";
		for ($j = $court->open; $j < $court->close; $j++) {
			$body .= "<tr><th>" . $j . " &ndash; " . ($j + 1) . "</th>";
			$displayDate = date_create($startDate);
			for ($i = 0; $i < $displayDays; $i++) {
				$body .= $this->getTD($court, $displayDate, $j, ($displayDate->format('Y-m-d') < $currentUser->maxDate));
				date_add($displayDate, new DateInterval('P1D'));
			}
			$body .= "</tr>";
		}
		$body .= "</tbody>";

		echo $header.$body;
		wp_die();
	}

	public function add_reservation()
	{
		PC::debug($_REQUEST);

		if (!current_user_can('place_reservation')) return $this->handleError('No permission.');

		if (isset($_REQUEST['delete']) && isset($_REQUEST['id'])) { // delete reservation
			$reservation = $this->getReservationByID($_REQUEST['id']);
			if ($reservation == null || $reservation->userid != wp_get_current_user()->ID) return $this->handleError('Wrong ID or no permissions.');
			$this->deleteReservationByID($reservation->id);
			status_header(200);
			echo $reservation->id;
			return;
		}

		// check if we got a full dataset
		if (!isset($_REQUEST['day']) ||
			!isset($_REQUEST['hour']) ||
			!isset($_REQUEST['type']) ||
			!isset($_REQUEST['courtid'])) return $this->handleError('Missing Data.');

		// check to see if current user can add reservation
		$currentUser = $this->getCurrentResmeUser();

		if (!$currentUser->canReserve) {
			return $this->handleError('Maximum Number of Reservations in Period', 400);
		}

		$court = $this->getCourtByID($_REQUEST['courtid']);

		// check court hour restrains
		$hour = (int)$_REQUEST['hour'];
		//PC::debug($hour);
		if ($hour < $court->open || $hour > $court->close) return $this->handleError('Invalid hour.');

		// check for reservations
		$day = new DateTimeImmutable($_REQUEST['day'] );
		$dateStr = $day->format('Y-m-d');
		
		$reservations = $this->getCurrentReservationsByID($court->id, $dateStr, $dateStr);
		foreach ($reservations as $res) {
			$restime = (new DateTime($res->date))->format('Y-m-d');
			if ($dateStr != $restime) continue;
			if ($hour == $res->time) return $this->handleError('Already reservated.');
		}

		// all good!
		//PC::debug($_POST);

		global $wpdb;
		$res_table = $this->getTable('reservations');
		//PC::debug($res_table);
		$wpdb->insert(
			$res_table,
			array(
				'facilityid' => (int)$_POST['courtid'],
				'type' => $_POST['type'],
				'userid' => wp_get_current_user()->ID,
				'date' => $dateStr,
				'time' => $hour
			),
			array(
				'%d',
				'%s',
				'%d',
				'%s',
				'%d'
			)
		);
		status_header(200);
		echo $wpdb->insert_id;
		//PC::debug($wpdb->insert_id);
	}
	public function add_resme_admin_menu()
	{

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 */
		//add_options_page( 'Reserve Me Court Setup', 'Add Courts', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page')
		//);
		add_menu_page(
			'Manage Reservations',
			__('Reservations', 'resme'),
			'manage_options',
			$this->plugin_name,
			array($this, 'load_admin_reservations'),
			'dashicons-calendar',
			6
		);

		add_submenu_page(
			$this->plugin_name,
			'Manage Courts and/or Facilities',
			__('Facilities', 'resme'),
			'manage_options',
			($this->plugin_name) . '-facilities',
			array($this, 'load_admin_facilities')
		);

		add_submenu_page(
			null,
			'Edit Facility',
			'Edit Facility',
			'manage_options',
			($this->plugin_name) . '-facility',
			array($this, 'load_admin_facility')
		);

		// add roles
		add_submenu_page(
			$this->plugin_name,
			'Manage Roles',
			__('Roles', 'resme'),
			'manage_options',
			($this->plugin_name) . '-roles',
			array($this, 'load_admin_roles')
		);

		add_submenu_page(
			null,
			'Edit Role',
			'Edit Role',
			'manage_options',
			($this->plugin_name) . '-role',
			array($this, 'load_admin_role')
		);
	}


	public function load_admin_facilities()
	{
		require_once plugin_dir_path(__FILE__) . 'partials/resme-facilities.php';
	}

	public function load_admin_facility()
	{
		require_once plugin_dir_path(__FILE__) . 'partials/resme-facility.php';
	}

	public function load_admin_roles()
	{
		require_once plugin_dir_path(__FILE__) . 'partials/resme-roles.php';
	}

	public function load_admin_role()
	{
		require_once plugin_dir_path(__FILE__) . 'partials/resme-role.php';
	}

	public function load_admin_reservations()
	{
		require_once plugin_dir_path(__FILE__) . 'partials/resme-reservations.php';
	}

	private function getTable($table)
	{
		global $wpdb;
		return "{$wpdb->prefix}resme_{$table}";
	}

	public $reservations;

	public function getCourtByID($courtID)
	{
		global $wpdb;
		$table_facilties = $this->getTable('facilities');
		return $wpdb->get_row("SELECT * FROM $table_facilties WHERE id = $courtID");
	}

	public function getReservations()
	{
		global $wpdb;
		return $wpdb->get_results("SELECT reservations.*, facilities.name as courtname FROM {$this->getTable('reservations')} as reservations,
	   {$this->getTable('facilities')} as facilities
	    WHERE facilities.id=reservations.facilityid AND reservations.date >= NOW() ORDER BY reservations.date, reservations.time");
	}

	public function getReservationByID($reservationID)
	{
		global $wpdb;
		return $wpdb->get_row("SELECT * FROM {$this->getTable('reservations')} WHERE id = $reservationID");
	}

	public function deleteReservationByID($reservationID)
	{
		global $wpdb;
		return $wpdb->delete($this->getTable('reservations'), array('id' => $reservationID), array('%d'));
	}

	public function cleanUpReservations()
	{
		global $wpdb;
		$wpdb->query("DELETE FROM {$this->getTable('reservations')} WHERE date < DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
	}

	/*public function getCurrentReservationsByID($courtID) {
		global $wpdb;
		return $wpdb->get_results("SELECT * FROM {$this->getTable('reservations')} WHERE facilityid = $courtID AND
			date >= CURDATE()
	    ORDER BY date, time");
	}
	 */

	public function getCurrentResmeUser() {
		$current_user = wp_get_current_user();
		PC::debug($current_user);

		$roles = $current_user->roles;
		
		

		if ($roles[0] === 'administrator') {
			$maxDate = new DateTime('2200-01-01');
			$currentResmeUser = new stdClass();
			$currentResmeUser->userid = $current_user->ID;
			$currentResmeUser->role = 'administrator';
			$currentResmeUser->maxDate = $maxDate->format('Y-m-d');
			$currentResmeUser->canReserve = true;
			return $currentResmeUser;
		}
		
		// assumes only one role if multiple roles this is a breaking issue
		global $wpdb;
		$query = "SELECT * FROM {$this->getTable('roles')} WHERE slug = '$roles[0]'";
		
		PC::debug($query);

		$role = $wpdb->get_results($query);
		PC::debug($role);

		if (!isset($role)) {
			$currentResmeUser = new stdClass();
			$currentResmeUser->userid = $current_user->ID;
			$currentResmeUser->role = '';
			$currentResmeUser->maxDate = '1900-01-01';
			$currentResmeUser->canReserve = false;
			return $currentResmeUser;
		}

		PC::debug($role);
		PC::debug($role[0]->maxdays);

		$startDate = new DateTimeImmutable();
		$endDate = $startDate->add(new DateInterval('P'. $role[0]->maxdays . 'D'));
		
		$count = $this->getReservationCountForUser($current_user->ID, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));


		$currentResmeUser = new stdClass();
		$currentResmeUser->userid = $current_user->ID;
		$currentResmeUser->role = $role[0]->name;
		$currentResmeUser->maxDate = $endDate->format('Y-m-d');
		$currentResmeUser->canReserve = ($count < $role[0]->maxres) ? true : false;
		return $currentResmeUser;

	}

	public function getReservationCountForUser($userId, $startDate, $endDate){

		$query = "SELECT * FROM {$this->getTable('reservations')} WHERE 
	    userid = '$userId' AND date >= '$startDate' AND date <= '$endDate'
		ORDER BY date, time";

		global $wpdb;
		$reservations = $wpdb->get_results($query);

		return count($reservations);
	}

	public function getCurrentReservationsByID($facilityID, $startDate, $endDate)
	{
		global $wpdb;
		$query = "SELECT * FROM {$this->getTable('reservations')} WHERE facilityid = $facilityID AND
	    date >= '$startDate' AND date <= '$endDate'
		ORDER BY date, time";

		$reservations = $wpdb->get_results($query);
		return $reservations;
	}


	private function doesOverlap($hour, $from, $to)
	{
		return ($hour >= $from && $hour <= $to);
	}


	private function isReserved($day, $hour)
	{
		
		$now = $day->format('Y-m-d');

		foreach ($this->reservations as $res) {
			$restime = (new DateTime($res->date))->format('Y-m-d');
			if ($now != $restime) continue;
			if ($hour == $res->time) return $res;
		}
		return null;
	}

	public function getTD($court, $day, $hour, $canReserve)
	{
		$expired = false;

		$reservation = $this->isReserved($day, $hour);

		$today = new DateTime();
		if ($day->format('Y-m-d') < $today->format('Y-m-d')) $expired = true;
		if ($day->format('Y-m-d') < $today->format('Y-m-d'))
		{ // check if already over
			if ($hour <= $today->format('h')) $expired = true;
		}

		if ($expired) {
			$output = "<td class=\"blocked\">";
			if ($reservation != null) {
				$output .= "<strong>" . (new WP_User($reservation->userid))->display_name . "<br/>";
				$output .= esc_html($reservation->type);
			}
			$output .= "</td>";
			return $output;
		}

		
		if ($reservation != null) {
			$output = "<td class=\"blocked\">";
			if ((int)$reservation->userid == wp_get_current_user()->ID) {
				$output .= '<a class="delete" data-id="' . $reservation->id . '" title="Delete">';
				$output .= (new WP_User($reservation->userid))->display_name . "<br/>";
				$output .= esc_html($reservation->type);
				$output .= '</a>';
			} else {
				$output .= "<strong>" . (new WP_User($reservation->userid))->display_name . "<br/>";
				$output .= esc_html($reservation->type);
			}
			
			
			 
			$output .= "</td>";
			return $output;
		}

		if (!$canReserve) {
			return __('<td class="blocked">free</td>', 'resme');
		}

		if (current_user_can('place_reservation')) {
			return '<td class="available"><a class="button btn btn-primary reservation" ' 
				. 'data-toggle="modal" data-target="#dialog" data-day="' 
				. $day->format('Y-m-d') . '" data-hour="' . $hour . '" data-date="' 
				. $day->format('Y-m-d') . '" data-time="' 
				. $hour . ' - ' . ($hour + 1) . ' ' . '">' . __('Reserve', 'resme')  . '</a></td>';
		} else {
			return __('<td class="available">free</td>', 'resme');
		}
	}

	private function getWeekdays()
	{
		return [
			__('Monday', 'resme'),
			__('Tuesday', 'resme'),
			__('Wednesday', 'resme'),
			__('Thursday', 'resme'),
			__('Friday', 'resme'),
			__('Saturday', 'resme'),
			__('Sunday', 'resme'),
		];
	}

}
