<?php
declare(strict_types=1);
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.7.7.1
 * Requires at least: 4.4
 * Requires PHP:      7.0.0
 * Author:            Sam Perrow
 * Author URI:        https://www.linkedin.com/in/sam-perrow
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pprh
 * Domain Path:       /languages
 *
 * last edited June 29, 2021
 *
 * Copyright 2016  Sam Perrow  (email : sam.perrow399@gmail.com)
 *
 */

namespace PPRH;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pprh_load = new Pre_Party_Browser_Hints();
$pprh_load->init();

register_activation_hook( __FILE__, array( $pprh_load, 'activate_plugin' ) );
add_action( 'wpmu_new_blog', array( $pprh_load, 'activate_plugin' ) );

class Pre_Party_Browser_Hints {

//	public function __construct() {}

	public function init() {
		\add_action( 'init', array( $this, 'load_plugin' ) );
	}

	public function load_plugin() {
		$this->load_common_files();
		$this->create_constants();

		\load_plugin_textdomain( 'pprh', false, PPRH_REL_DIR . 'languages' );
		\do_action( 'pprh_load_plugin' );

        $is_admin = \is_admin();
		$this->load( $is_admin );

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
		include_once 'includes/Preconnects.php';
		$preconnects = new Preconnects();
	}

	public function load( $is_admin ) {
		if ( $is_admin ) {
			\add_action( 'wp_loaded', array( $this, 'load_admin' ) );
		} else {
			\add_action( 'wp_loaded', array( $this, 'load_client' ) );
		}
    }

	public function load_admin() {
		include_once 'includes/admin/LoadAdmin.php';
		$load_admin = new LoadAdmin();
		$load_admin->init();
	}

    public function load_client() {
		include_once 'includes/client/LoadClient.php';
		$load_client = new LoadClient();
        $load_client->init();
    }

	public function create_constants() {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';
		$postmeta_table = $wpdb->prefix . 'postmeta';
		$plugin_version = \get_option( 'pprh_version', '' );
        $site_url = \get_option( 'siteurl' );
		$in_dev_testing = ( 'https://sphacks.local' === $site_url );
		$unit_testing = defined( 'PPRH_UNIT_TESTING' ) && PPRH_UNIT_TESTING;

		if ( ! defined( 'PPRH_DB_TABLE' ) ) {
			define( 'PPRH_DB_TABLE', $table );
			define( 'PPRH_VERSION', $plugin_version );
			define( 'PPRH_VERSION_NEW', '1.7.7.1' );
			define( 'PPRH_POSTMETA_TABLE', $postmeta_table );
			define( 'PPRH_ABS_DIR', WP_PLUGIN_DIR . '/pre-party-browser-hints/' );
			define( 'PPRH_REL_DIR', plugins_url() . '/pre-party-browser-hints/' );
			define( 'PPRH_MENU_SLUG', 'pprh-plugin-settings' );
			define( 'PPRH_ADMIN_SCREEN', 'toplevel_page_' . PPRH_MENU_SLUG );
			define( 'PPRH_HOME_URL', admin_url() . 'admin.php?page=' . PPRH_MENU_SLUG );
			define( 'PPRH_SITE_URL', $site_url );
			define( 'PPRH_IN_DEV', $in_dev_testing );
			define( 'PPRH_RUNNING_UNIT_TESTS', $unit_testing );
		}
	}

	public function load_common_files() {
		if ( ! class_exists( \PPRH\Utils::class ) ) {
			include_once 'includes/Utils.php';
		}

		if ( ! class_exists( \PPRH\DAOController::class ) ) {
			include_once 'includes/DAOController.php';
		}

		if ( ! class_exists( \PPRH\CreateHints::class ) ) {
			include_once 'includes/CreateHints.php';
		}
	}

	public function activate_plugin() {
		include_once 'includes/admin/ActivatePlugin.php';
		$activate_plugin = new ActivatePlugin();
		$this->load_common_files();
		$this->create_constants();
		$activate_plugin->activate_plugin();
		return $activate_plugin->plugin_activated;
	}


}