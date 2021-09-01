<?php
declare(strict_types=1);
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.8.0
 * Requires at least: 4.4
 * Requires PHP:      7.0.0
 * Author:            Sam Perrow
 * Author URI:        https://www.linkedin.com/in/sam-perrow
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pprh
 * Domain Path:       /languages
 *
 * last edited August 20, 2021
 *
 * Copyright 2016  Sam Perrow  (email : info@sphacks.io)
 *
 */

namespace PPRH;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\add_action( 'init', function() {
	$pprh_load = new Pre_Party_Browser_Hints();
	$pprh_load->init();
}, 10, 0 );

class Pre_Party_Browser_Hints {

	private $plugin_page;
	private static $preconnect_enabled;

	protected $client_data;

	public function __construct() {
//		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
//			return;
//		}

		\register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		\add_action( 'wpmu_new_blog', array( $this, 'activate_plugin' ) );
		\add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'plugin_updater' ), 10, 1 );
	}

	public function init() {
		$this->load_common_files();
		$this->create_constants();
		$this->load_plugin_main();

		\load_plugin_textdomain( 'pprh', false, PPRH_REL_DIR . 'languages' );
		\do_action( 'pprh_load_plugin' );

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
		if ( self::$preconnect_enabled ) {
			include_once 'includes/client/ClientAjaxInit.php';
			$client_ajax_init = new ClientAjaxInit();
		}
	}

	private function load_common_files() {
		include_once 'includes/Utils.php';
		include_once 'includes/HintController.php';
		include_once 'includes/HintBuilder.php';
		include_once 'includes/admin/ActivatePlugin.php';
	}

	private function create_constants() {
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
			define( 'PPRH_VERSION_NEW', '1.8.0' );
		}

		if ( ! defined( 'PPRH_VERSION' ) ) {
			define( 'PPRH_VERSION', \get_option( 'pprh_version', '' ) );
		}
	}

	public function load_plugin_main() {
		self::$preconnect_enabled = ('true' === \get_option('pprh_preconnect_autoload') || PPRH_RUNNING_UNIT_TESTS);
		$is_admin = \is_admin();

		if ( $is_admin ) {
			$this->plugin_page = Utils::get_plugin_page( \wp_doing_ajax(), '' );
			\add_action( 'wp_loaded', array( $this, 'load_main_admin' ) );
		} else {
			$this->plugin_page = -1;
			\add_action( 'wp_loaded', array( $this, 'load_main_client' ) );
		}

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
		include_once 'includes/client/ClientAjaxInit.php';
		include_once 'includes/admin/ClientAjaxResponse.php';

		if ( self::$preconnect_enabled ) {
			$client_ajax_init = new ClientAjaxInit();
			unset($client_ajax_init);
		}
	}

	public function load_main_admin() {
		self::load_plugin_files( true );
		$load_admin = new LoadAdmin();
		$load_admin->init( $this->plugin_page );
	}

	public static function load_main_client() {
		self::load_plugin_files( false );
		$load_client = new LoadClient();
		$load_client->init( self::$preconnect_enabled );
	}


	private static function load_plugin_files( bool $is_admin ) {
		if ( $is_admin ) {
			include_once 'includes/admin/LoadAdmin.php';
			include_once 'includes/admin/Updater.php';
			include_once 'includes/admin/Dashboard.php';
			include_once 'includes/admin/views/Settings.php';
			include_once 'includes/admin/views/SettingsSave.php';
			include_once 'includes/admin/views/SettingsView.php';
			include_once 'includes/admin/views/FAQ.php';
			include_once 'includes/admin/NewHint.php';
			include_once 'includes/admin/DisplayHints.php';
			include_once 'includes/admin/AjaxOps.php';
			include_once 'includes/admin/views/InsertHints.php';
		} else {
			include_once 'includes/client/LoadClient.php';
			include_once 'includes/client/SendHints.php';
		}
	}


	public function activate_plugin() {
		$this->load_common_files();
		$this->create_constants();
		$activate_plugin = new ActivatePlugin();
		$activate_plugin->activate_plugin();
		return $activate_plugin->plugin_activated;
	}

	public function plugin_updater( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		include_once 'includes/admin/Updater.php';
		$updater = new Updater();
		return $updater->init( $transient );
	}

}
