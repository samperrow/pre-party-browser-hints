<?php
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.7.4
 * Requires at least: 4.4
 * Requires PHP:      5.6.30
 * Author:            Sam Perrow
 * Author URI:        https://www.linkedin.com/in/sam-perrow
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pprh
 * Domain Path:       /languages
 *
 * last edited December 6, 2020
 *
 * Copyright 2016  Sam Perrow  (email : sam.perrow399@gmail.com)
 *
 */

namespace PPRH;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function() {
	if ( ! class_exists( 'Pre_Party_Browser_Hints' ) ) {
		new Pre_Party_Browser_Hints();
	}
});

class Pre_Party_Browser_Hints {

	public function __construct() {
		$this->initialize();
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
		add_filter( 'set-screen-option', array( $this, 'apply_wp_screen_options' ), 10, 3 );
		add_action( 'wpmu_new_blog', array( $this, 'activate_plugin' ) );
		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
	}

	public function initialize() {
		$this->create_constants();
		include_once PPRH_ABS_DIR . 'includes/utils.php';
		include_once PPRH_ABS_DIR . 'includes/dao.php';
		include_once PPRH_ABS_DIR . 'includes/create-hints.php';

		$autoload = get_option( 'pprh_preconnect_autoload' );
		$preconnects_set = get_option( 'pprh_preconnect_set' );

		if ( is_admin() ) {
			include_once PPRH_ABS_DIR . 'includes/ajax-ops.php';
			include_once PPRH_ABS_DIR . 'includes/display-hints.php';
			add_action( 'admin_menu', array( $this, 'load_admin_page' ) );
		} else {
			$this->check_if_wp_hints_disabled();
			$this->load_flying_pages();
			include_once PPRH_ABS_DIR . 'includes/send-hints.php';
		}

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
		if ( 'true' === $autoload && 'false' === $preconnects_set ) {
			include_once PPRH_ABS_DIR . 'includes/preconnects.php';
			new Preconnects();
		}

//		do_action( 'pprh_pro_init' );
	}

	public function load_admin_page() {
		$settings_page = add_menu_page(
			'Pre* Party Settings',
			'Pre* Party',
			'manage_options',
			'pprh-plugin-settings',
			array( $this, 'load_plugin_page' ),
			PPRH_REL_DIR . 'images/lightning.png'
		);

		add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
	}

	public function load_plugin_page() {
		include_once PPRH_ABS_DIR . 'includes/admin-tabs.php';

		if ( is_admin() && ! class_exists( 'Admin_Tabs' ) ) {
			new Admin_Tabs();
		}
	}

	public function create_constants() {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';
		$abs_dir = WP_PLUGIN_DIR . '/pre-party-browser-hints/';
		$rel_dir = plugins_url() . '/pre-party-browser-hints/';
		$home_url = admin_url() . 'admin.php?page=pprh-plugin-setttings';

		define( 'PPRH_VERSION', '1.7.4' );
		define( 'PPRH_DB_TABLE', $table );
		define( 'PPRH_ABS_DIR', $abs_dir );
		define( 'PPRH_REL_DIR', $rel_dir );
		define( 'PPRH_HOME_URL', $home_url );
	}

	// Register and call the CSS and JS we need only on the needed page.
	public function register_admin_files( $hook ) {
		if ( false !== stripos( $hook, 'toplevel_page_pprh-plugin-settings' ) ) {
			$ajax_data = array(
				'val'       => wp_create_nonce( 'pprh_table_nonce' ),
				'admin_url' => admin_url()
			);

			wp_register_script( 'pprh_admin_js', PPRH_REL_DIR . 'js/admin.js', array( 'jquery' ), PPRH_VERSION, true );
			wp_localize_script( 'pprh_admin_js', 'pprh_nonce', $ajax_data );
			wp_register_style( 'pprh_styles_css', PPRH_REL_DIR . 'css/styles.css', null, PPRH_VERSION, 'all' );
			wp_enqueue_script( 'pprh_admin_js' );
			wp_enqueue_style( 'pprh_styles_css' );
//			do_action( 'pprh_pro_admin_enqueue_scripts' );
		}
	}

	private function load_flying_pages() {
		$load_flying_pages = get_option( 'pprh_prefetch_enabled' );

		if ( $load_flying_pages === 'true' ) {
			$fp_data = array(
				'delay'          => get_option( 'pprh_prefetch_delay', 0 ),
				'hoverDelay'     => get_option( 'pprh_prefetch_hoverDelay', 50 ),
				'maxRPS'         => get_option( 'pprh_prefetch_maxRPS', 3 ),
				'ignoreKeywords' => get_option( 'pprh_prefetch_ignoreKeywords', '' ),
			);

			wp_register_script( 'pprh_prefetch_flying_pages', PPRH_REL_DIR . 'js/flying-pages.min.js', null, PPRH_VERSION, true );
			wp_localize_script( 'pprh_prefetch_flying_pages', 'pprh_fp_data', $fp_data );
			wp_enqueue_script( 'pprh_prefetch_flying_pages' );
		}
	}

	public function activate_plugin() {
		include_once PPRH_ABS_DIR . 'includes/activate-plugin.php';
		new Activate_Plugin();
	}

	public function apply_wp_screen_options( $status, $option, $value ) {
		return ( 'pprh_screen_options' === $option ) ? $value : $status;
	}

	public function screen_option() {
		$args = array(
			'label'   => 'URLs',
			'default' => 10,
			'option'  => 'pprh_screen_options',
		);

		add_screen_option( 'per_page', $args );
	}

	public function check_if_wp_hints_disabled() {
		if ( 'true' === get_option( 'pprh_disable_wp_hints' ) ) {
			remove_action( 'wp_head', 'wp_resource_hints', 2 );
		}
	}

}