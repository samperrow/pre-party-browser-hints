<?php
declare(strict_types=1);
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.9.0
 * Requires at least: 4.4
 * Requires PHP:      7.0.0
 * Author:            Sam Perrow
 * Author URI:        https://www.linkedin.com/in/sam-perrow
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pre-party-browser-hints
 * Domain Path:       /languages
 *
 * Last edited March 26, 2023
 *
 * Copyright 2023  Sam Perrow  (email : info@sptrix.com)
 *
 */

namespace PPRH;

use PPRH\Utils\Utils;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\add_action( 'init', function() {
	$pprh_load = new Pre_Party_Browser_Hints();
	$pprh_load->init();
}, 10, 0 );

function pprh_activate_plugin() {
	$pprh_load = new Pre_Party_Browser_Hints();
	include_once 'includes/admin/ActivatePlugin.php';
	$pprh_load->create_constants();
	$activate_plugin = new ActivatePlugin();
	$activate_plugin->activate_plugin();
	return $activate_plugin->plugin_activated;
}
\register_activation_hook( __FILE__, '\PPRH\pprh_activate_plugin' );
\add_action( 'wpmu_new_blog', '\PPRH\pprh_activate_plugin' );

class Pre_Party_Browser_Hints {

	private $plugin_page;
	private static $preconnect_enabled;

	protected $client_data;

	private $prerender_enabled;
	private $show_posts_on_front;

	public static $client_post_id;
	public static $request_uri;

	public function __construct() {
		include_once 'includes/utils/Utils.php';
		include_once 'includes/utils/Sanitize.php';
		include_once 'includes/utils/Debug.php';
		include_once 'includes/common/HintController.php';
		include_once 'includes/common/HintBuilder.php';
		include_once 'includes/DebugLogger.php';
	}

	public function init() {
		$this->create_constants();
		$this->load_plugin_main();

		\do_action( 'pprh_load_plugin' );

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
		if ( self::$preconnect_enabled ) {
			include_once 'includes/client/ClientAjaxInit.php';
			$client_ajax_init = new ClientAjaxInit();
		}
	}

	public function create_constants() {
		global $wpdb;
		$table          = $wpdb->prefix . 'pprh_table';
		$postmeta_table = $wpdb->prefix . 'postmeta';
		$post_table     = $wpdb->prefix . 'posts';
		$site_url       = \get_option( 'siteurl' );
		$in_dev_testing = str_contains( $site_url, 'sptrix.local' );
		$unit_testing   = defined( 'PPRH_UNIT_TESTING' ) && PPRH_UNIT_TESTING;

		if ( ! defined( 'PPRH_DB_TABLE' ) ) {
			define( 'PPRH_DB_TABLE', $table );
			define( 'PPRH_POSTMETA_TABLE', $postmeta_table );
			define( 'PPRH_POST_TABLE', $post_table );
			define( 'PPRH_ABS_DIR', WP_PLUGIN_DIR . '/pre-party-browser-hints/' );
			define( 'PPRH_REL_DIR', plugins_url() . '/pre-party-browser-hints/' );
			define( 'PPRH_MENU_SLUG', 'pprh-plugin-settings' );
			define( 'PPRH_ADMIN_SCREEN', 'toplevel_page_' . PPRH_MENU_SLUG );
			define( 'PPRH_SITE_URL', $site_url );
			define( 'PPRH_IN_DEV', $in_dev_testing );
			define( 'PPRH_RUNNING_UNIT_TESTS', $unit_testing );
			define( 'PPRH_EMAIL', 'info@sptrix.com' );

			define( 'PPRH_PRO_REL_DIR', plugins_url() . '/pprh-pro/' );
		}

		if ( ! defined( 'PPRH_VERSION_NEW' ) ) {
			define( 'PPRH_VERSION_NEW', '1.9.0' );
		}

		if ( ! defined( 'PPRH_VERSION' ) ) {
			define( 'PPRH_VERSION', \get_option( 'pprh_version', '' ) );
		}
	}

	public function load_plugin_main() {
		self::$preconnect_enabled = ('true' === \get_option('pprh_preconnect_autoload') || PPRH_RUNNING_UNIT_TESTS);

		if ( \is_admin() ) {
			$this->plugin_page = $this->get_plugin_page( \wp_doing_ajax(), '' );
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

	public function get_plugin_page( bool $doing_ajax, string $referer ):int {
		if ( '' === $referer ) {
			$referer = Utils::get_referer();
		}

		$request_uri = Utils::get_server_prop( 'REQUEST_URI' );
		return $this->get_plugin_page_ctrl( $doing_ajax, $referer, $request_uri );
	}

	public function get_plugin_page_ctrl( bool $doing_ajax, string $referer, string $request_uri ):int {
		$matcher = ( $doing_ajax ) ? $referer : $request_uri;
		$val = 0;

		if ( str_contains( $matcher, PPRH_MENU_SLUG ) ) {
			$val = 1;
		} elseif ( str_contains( $matcher, 'post.php' ) ) {
			$val = 2;
		}

		return $val;
	}

	public function load_main_admin() {
		self::load_plugin_files( true );
		$load_admin = new LoadAdmin();
		$load_admin->init( $this->plugin_page );

		include_once 'includes/prerender/Prerender.php';

		$this->show_posts_on_front = ( 'posts' === \get_option( 'show_on_front', 'true' ) );

		if ( 1 === $this->plugin_page ) {
			$settings_view = new \PPRH\Settings\SettingsView( $this->show_posts_on_front );
			$prerender = new Prerender( $this->show_posts_on_front );
			unset( $settings_view, $prerender );
		} elseif ( 2 === $this->plugin_page ) {
			$posts = new Posts( $this->show_posts_on_front );
			unset( $posts );
		}
	}

	public static function load_main_client() {
		self::load_plugin_files( false );
		$load_client = new LoadClient();
		$load_client->init( self::$preconnect_enabled );
	}

	private static function load_plugin_files( bool $is_admin ) {
		if ( $is_admin ) {
			include_once 'includes/admin/LoadAdmin.php';
			include_once 'includes/admin/Dashboard.php';
			include_once 'includes/admin/views/settings/SettingsSave.php';
			include_once 'includes/admin/views/settings/SettingsView.php';
			include_once 'includes/admin/views/FAQ.php';
			include_once 'includes/admin/NewHint.php';
			include_once 'includes/admin/DisplayHints.php';
			include_once 'includes/admin/AjaxOps.php';
			include_once 'includes/admin/ActivatePlugin.php';
			include_once 'includes/Posts.php';
		} else {
			include_once 'includes/client/LoadClient.php';
			include_once 'includes/client/SendHints.php';
		}

	}


	public function load_pro_client( bool $preconnect_autoload ):array {
		include_once 'includes/client/LoadClientPro.php';
		new LoadClientPro( self::$client_post_id );

		$client_post = array();
		$this->prerender_init( $this->prerender_enabled );

		if ( $preconnect_autoload ) {
			$client_post = array(
				'post_id'     => self::$client_post_id,
				'request_uri' => self::$request_uri,
			);
		}

		return $client_post;
	}

	public function prerender_init( bool $prerender_enabled ) {
		if ( $prerender_enabled ) {
			include_once 'includes/prerender/PrerenderAnalytics.php';
			$int_post_id = (int) self::$client_post_id;
			$prerender_analytics = new PrerenderAnalytics();
			$prerender_analytics->config( $int_post_id );
			return true;
		}

		return false;
	}


	public function activate_plugin() {
		include_once 'includes/ActivatePlugin.php';
		$this->load_common_files();
		$activate_plugin = new ActivatePlugin();
		$activate_plugin->activate_plugin_init();
	}

}
