<?php
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.7.5.4
 * Requires at least: 4.4
 * Requires PHP:      5.6.30
 * Author:            Sam Perrow
 * Author URI:        https://www.linkedin.com/in/sam-perrow
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pprh
 * Domain Path:       /languages
 *
 * last edited April 18, 2021
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

register_activation_hook( __FILE__, array( $pprh_load, 'activate_plugin' ) );
add_action( 'wpmu_new_blog', array( $pprh_load, 'activate_plugin' ) );

class Pre_Party_Browser_Hints {

	public function __construct() {
	    add_action( 'init', array( $this, 'load_plugin' ) );
	}

	public function load_plugin() {
		$this->load_common_files();
		$this->create_constants();
		do_action( 'pprh_load_plugin' );

		if ( ! function_exists( 'wp_doing_ajax' ) ) {
			apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
		}

		if ( is_admin() ) {
			add_action( 'wp_loaded', array( $this, 'load_admin' ) );
		} else {
			add_action( 'wp_loaded', array( $this, 'load_client' ) );
		}

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
		include_once 'includes/Preconnects.php';
		$preconnects = new Preconnects();
	}

	public function load_admin() {
		include_once 'includes/admin/LoadAdmin.php';
		$load_admin = new LoadAdmin();
		$load_admin->init();
		add_action( 'pprh_check_to_upgrade', array( $this, 'check_to_upgrade' ), 10, 1 );
	}

    public function load_client() {
		include_once 'includes/client/LoadClient.php';
		$load_client = new LoadClient();
        $load_client->init();
    }

	public function create_constants() {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';
		$plugin_version = get_option( 'pprh_version' );
		$pprh_pro_active = Utils::pprh_is_plugin_active();

		if ( ! defined( 'PPRH_VERSION' ) ) {
			define( 'PPRH_VERSION', $plugin_version );
			define( 'PPRH_DB_TABLE', $table );
			define( 'PPRH_ABS_DIR', WP_PLUGIN_DIR . '/pre-party-browser-hints/' );
			define( 'PPRH_REL_DIR', plugins_url() . '/pre-party-browser-hints/' );
			define( 'PPRH_HOME_URL', admin_url() . 'admin.php?page=pprh-plugin-setttings' );
			define( 'PPRH_PRO_PLUGIN_ACTIVE', $pprh_pro_active );
			define( 'PPRH_DEBUG', true );
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

	private function load_activate_plugin() {
		$this->load_common_files();
		$this->create_constants();
		include_once 'includes/admin/ActivatePlugin.php';
	}

	public function check_to_upgrade( $new_version ) {
		if ( $new_version !== PPRH_VERSION ) {
			$this->do_upgrade();
			update_option( 'pprh_version', $new_version );
		}
	}

	public function do_upgrade() {
		$previous_version = PPRH_VERSION;
		$this->load_activate_plugin();
		$activate_plugin = new ActivatePlugin();
		$activate_plugin->upgrade_plugin( $previous_version );
		$this->upgrade_notice();
	}

	public function upgrade_notice() {
		if ( defined( 'PPRH_TESTING' ) && PPRH_TESTING ) {
			return;
		}
		?>
		<div class="notice notice-info is-dismissible">
			<p><?php _e('Upgrade Notes: Fixed issue relating to hint creation (crossorigin attribute), automatic attribute creation, and the auto-prefetch feature.' ); ?></p>
		</div>
		<?php
	}

	public function activate_plugin() {
		$this->load_activate_plugin();
		$activate_plugin = new ActivatePlugin();
		$activate_plugin->activate_plugin();
	}

}