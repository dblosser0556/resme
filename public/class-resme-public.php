<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://
 * @since      1.0.0
 *
 * @package    Resme
 * @subpackage Resme/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Resme
 * @subpackage Resme/public
 * @author     Dave Blosser <blosserdl@gmail.com>
 */
class Resme_Public {

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

	public $reservations;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Resme_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Resme_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/resme-public.css', array(), date("h:i:s"), 'all' );
		//wp_enqueue_style( $this->plugin_name . '-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css', array(), $this->version, 'all' );
		//wp_enqueue_style( $this->plugin_name . '-ui-theme', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.theme.min.css', array(), $this->version, 'all' );
		//wp_enqueue_style( $this->plugin_name . '-ui-struct', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.structure.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-load-bsm', plugin_dir_url( __FILE__ ) . 'css/bootstrap.css', array(), date("h:i:s"), 'all'  );
		wp_enqueue_style( $this->plugin_name . '-load-fa', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css' );
		//wp_enqueue_style( $this->plugin_name . '-load-bs', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Resme_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Resme_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		//wp_enqueue_script( $this->plugin_name . '-ui', plugin_dir_url( __FILE__ ) . 'js/jquery-ui.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/resme-public.js', array( 'jquery' ), date("h:i:s"), false );
		wp_enqueue_script( $this->plugin_name  . '-bootstrap', plugin_dir_url( __FILE__ ) . 'js/bootstrap.js', array( 'jquery' ), date("h:i:s"), false );
		wp_localize_script( $this->plugin_name, 'my_ajax_object', array('ajax_url' => '/wp-admin/admin-ajax.php'));
		
	}



	private function getTable($table) {
		global $wpdb;
		return "{$wpdb->prefix}resme_{$table}";
	}

	public function getCourtByID($facilityID) {
		global $wpdb;
		$table_facilities = $this->getTable('facilities');
		return $wpdb->get_row("SELECT * FROM $table_facilities WHERE id = $facilityID");
	}

	public function public_shortcode( $atts, $content = null ) {
		ob_start();
		include_once( 'partials/'.$this->plugin_name.'-public-display.php' );
		return ob_get_clean();
	}

}
