<?php
declare(strict_types=1);
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.7.8
 * Requires at least: 4.4
 * Requires PHP:      7.0.0
 * Author:            Sam Perrow
 * Author URI:        https://www.linkedin.com/in/sam-perrow
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pprh
 * Domain Path:       /languages
 *
 * last edited August 9, 2021
 *
 * Copyright 2016  Sam Perrow  (email : info@sphacks.io)
 *
 */

namespace PPRH;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function somecb_fn() {
	$pro_active = class_exists( \PPRH\PRO\PPRH_Pro::class );

	if ( ! $pro_active ) {
		$pprh_load = new Pre_Party_Browser_Hints();
		unset( $pprh_load );
	}
}
\add_action( 'init', '\PPRH\somecb_fn', 10, 0 );

class Pre_Party_Browser_Hints {

	protected static $pprh_preconnect_autoload;

	protected $client_data;

	protected $on_pprh_page;

	public function __construct() {
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		\register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		\add_action( 'wpmu_new_blog', array( $this, 'activate_plugin' ) );
		$this->init();
	}

	public function init() {

		$this->load_common_files();
		$this->create_constants();
		$this->load_plugin_main();

		if ( is_null( $this->on_pprh_page ) ) {
			$this->on_pprh_page = Utils::on_pprh_page( \wp_doing_ajax(), '' );
		}
	}

	protected function load_common_files() {
		include_once 'includes/Utils.php';
		include_once 'includes/DAOController.php';
		include_once 'includes/CreateHints.php';
		include_once 'includes/admin/ActivatePlugin.php';
	}

	protected function create_constants() {
		global $wpdb;
		$table          = $wpdb->prefix . 'pprh_table';
		$postmeta_table = $wpdb->prefix . 'postmeta';
		$site_url       = \get_option( 'siteurl' );
		$in_dev_testing = str_contains( $site_url, 'sphacks.local' );
		$unit_testing   = defined( 'PPRH_UNIT_TESTING' ) && PPRH_UNIT_TESTING;

		if ( ! defined( 'PPRH_DB_TABLE' ) ) {
			define( 'PPRH_DB_TABLE', $table );
			define( 'PPRH_POSTMETA_TABLE', $postmeta_table );
			define( 'PPRH_ABS_DIR', WP_PLUGIN_DIR . '/pre-party-browser-hints/' );
			define( 'PPRH_REL_DIR', plugins_url() . '/pre-party-browser-hints/' );
			define( 'PPRH_MENU_SLUG', 'pprh-plugin-settings' );
			define( 'PPRH_ADMIN_SCREEN', 'toplevel_page_' . PPRH_MENU_SLUG );
			define( 'PPRH_SITE_URL', $site_url );
			define( 'PPRH_IN_DEV', $in_dev_testing );
			define( 'PPRH_RUNNING_UNIT_TESTS', $unit_testing );
			define( 'PPRH_EMAIL', 'info@sphacks.io' );
		}

		if ( ! defined( 'PPRH_VERSION_NEW' ) ) {
			define( 'PPRH_VERSION_NEW', '1.7.8' );
		}

		if ( ! defined( 'PPRH_VERSION' ) ) {
			define( 'PPRH_VERSION', \get_option( 'pprh_version', '' ) );
		}

	}

	protected function load_plugin_main() {
		self::$pprh_preconnect_autoload = ( 'true' === \get_option( 'pprh_preconnect_autoload' ) || PPRH_RUNNING_UNIT_TESTS );
		\load_plugin_textdomain( 'pprh', false, PPRH_REL_DIR . 'languages' );

		$is_admin = \is_admin();

		$str = ( $is_admin ) ? 'admin' : 'client';
		\add_action( 'wp_loaded', array( $this, "load_main_$str" ) );

		if ( PPRH_RUNNING_UNIT_TESTS ) {
			\add_action( 'wp_loaded', array( $this, 'load_main_client' ), 10, 0 );
		}

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.

		if ( self::$pprh_preconnect_autoload ) {
			include_once 'includes/PreconnectInit.php';
			include_once 'includes/admin/PreconnectResponse.php';
			$preconnect_init = new PreconnectInit();
			$preconnect_init->load_actions();
		}
	}

	public function load_main_admin() {
		include_once 'includes/admin/LoadAdmin.php';
		$load_admin = new LoadAdmin();
		$load_admin->init( $this->on_pprh_page );
		$this->plugin_updater();
	}

	public function load_main_client() {
		include_once 'includes/client/LoadClient.php';
		include_once 'includes/client/SendHints.php';
		$load_client = new LoadClient();
		$load_client->init( $this->client_data );
	}





	public function activate_plugin() {
		$this->load_common_files();
		$this->create_constants();
		$activate_plugin = new ActivatePlugin();
		$activate_plugin->activate_plugin();
		return $activate_plugin->plugin_activated;
	}

	private function plugin_updater() {
		$api_endpoint = 'https://sphacks.io/wp-content/pprh/free/updater.json';
		$plugin_file = 'pre-party-browser-hints/pre-party-browser-hints.php';
		$transient_name = 'pprh_updater';

		if ( class_exists( \PPRH\PRO\Updater::class ) ) {
			$updater = new \PPRH\PRO\Updater( $api_endpoint, $plugin_file, $transient_name, PPRH_VERSION_NEW );
			$updater->set_filter();
		}
	}

}
