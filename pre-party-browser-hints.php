<?php
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.7.3
 * Requires at least: 4.4
 * Requires PHP:      5.6.30
 * Author:            Sam Perrow
 * Author URI:        https://www.linkedin.com/in/sam-perrow
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pprh
 * Domain Path:       /languages
 *
 * last edited August 2, 2020
 *
 * Copyright 2016  Sam Perrow  (email : sam.perrow399@gmail.com)
 *
*/

namespace PPRH;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

new Init();

class Init {

	public $load_adv = false;

	public function __construct() {
		if ( isset( $_REQUEST['action'] ) && 'heartbeat' === $_REQUEST['action'] ) {
			return;
		}
		add_action( 'init', array( $this, 'initialize' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
		add_filter( 'set-screen-option', array( $this, 'apply_wp_screen_options' ), 10, 3 );
		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		add_action( 'wpmu_new_blog', array( $this, 'activate_plugin' ) );

		do_action( 'pprh_pro_init' );
	}

	public function initialize() {
		$this->create_constants();
		include_once PPRH_ABS_DIR . '/includes/class-pprh-utils.php';

		if ( is_admin() ) {
			include_once PPRH_ABS_DIR . '/includes/class-pprh-ajax-ops.php';
			add_action( 'admin_menu', array( $this, 'load_admin_page' ) );
		} elseif ( ! wp_doing_ajax() ) {
			$this->pprh_disable_wp_hints();
			include_once PPRH_ABS_DIR . '/includes/class-pprh-send-hints.php';
		}

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
		if ( $this->load_auto_preconnects() ) {
			include_once PPRH_ABS_DIR . '/includes/class-pprh-auto-preconnects.php';
			new Auto_Preconnects( $this->load_adv );
		}
	}

	public function load_auto_preconnects() {
		$preconnects_set = get_option( 'pprh_prec_preconnects_set' );
		$autoload = get_option( 'pprh_prec_autoload_preconnects' );
		$this->load_adv = apply_filters( 'pprh_prec_autoload', null );

		return ( 'true' === $autoload && ('false' === $preconnects_set || $this->load_adv ) );
	}

	public function load_admin_page() {
		$settings_page = add_menu_page(
			'Pre* Party Settings',
			'Pre* Party',
			'manage_options',
			'pprh-plugin-settings',
			array( $this, 'load_files' ),
			PPRH_REL_DIR . 'images/lightning.png'
		);

		add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
	}

	public function load_files() {
		include_once PPRH_ABS_DIR . '/includes/class-pprh-admin-tabs.php';
	}

	public function create_constants() {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';
		$abs_dir = untrailingslashit( __DIR__ );
		$rel_dir = plugins_url() . '/pre-party-browser-hints/';

		define( 'PPRH_VERSION', '1.7.3' );
		define( 'PPRH_DB_TABLE', $table );
		define( 'PPRH_ABS_DIR', $abs_dir );
		define( 'PPRH_REL_DIR', $rel_dir );
	}

	// Register and call the CSS and JS we need only on the needed page.
	public function register_admin_files( $hook ) {
		$regex = apply_filters( 'pprh_get_register_admin_files_regex', 'toplevel_page_pprh-plugin-settings' );

		if ( preg_match( '/' . $regex . '/i', $hook ) ) {
			$ajax_data = array(
				'val'       => wp_create_nonce( 'pprh_table_nonce' ),
				'admin_url' => admin_url()
			);

			wp_register_script( 'pprh_admin_js', PPRH_REL_DIR . 'js/admin.js', null, PPRH_VERSION, true );
			wp_localize_script( 'pprh_admin_js', 'pprh_admin', $ajax_data );
			wp_enqueue_style( 'pprh_styles_css', PPRH_REL_DIR . 'css/styles.css', null, PPRH_VERSION, 'all' );
		}
	}

	public function activate_plugin() {
		$this->create_constants();
		$this->set_options();
		$this->setup_tables();
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

	public function set_options() {
		add_option( 'pprh_disable_wp_hints', 'true', '', 'yes' );
		add_option( 'pprh_html_head', 'true', '', 'yes' );

//		add_option( 'pprh_prec_allow_unauth', 'true', '', 'yes' );
//		add_option( 'pprh_autoload_preconnects', 'true', '', 'yes' );
//		add_option( 'pprh_preconnects_set', 'false', '', 'yes' );

		$allow_unauth = get_option( 'pprh_prec_allow_unauth' );
		$autoload = get_option( 'pprh_autoload_preconnects' );
		$prec_set = get_option( 'pprh_preconnects_set' );

		add_option( 'pprh_prec_allow_unauth', $allow_unauth, '', 'yes' );
		add_option( 'pprh_prec_autoload_preconnects', $autoload, '', 'yes' );
		add_option( 'pprh_prec_preconnects_set', $prec_set, '', 'yes' );

		delete_option( 'pprh_prec_allow_unauth' );
		delete_option( 'pprh_autoload_preconnects' );
		delete_option( 'pprh_preconnects_set' );

	}

	// Multisite install/delete db table.
	public function setup_tables() {
		$pprh_tables = array();

		if ( is_multisite() ) {
			$pprh_tables = $this->get_multisite_tables();
		}

		array_push( $pprh_tables, PPRH_DB_TABLE );

		foreach ( $pprh_tables as $pprh_table ) {
			$this->create_table( $pprh_table );
		}
	}

	private function get_multisite_tables() {
		global $wpdb;
		$blog_table = $wpdb->base_prefix . 'blogs';
		$ms_table_names = array();

		$ms_blog_ids = $wpdb->get_results(
			$wpdb->prepare( "SELECT blog_id FROM $blog_table WHERE blog_id != %d", 1 )
		);

		if ( ! empty( $ms_blog_ids ) && count( $ms_blog_ids ) > 0 ) {
			foreach ( $ms_blog_ids as $ms_blog_id ) {
				$ms_table_name = $wpdb->base_prefix . $ms_blog_id->blog_id . '_pprh_table';
				$ms_table_names[] = $ms_table_name;
			}
		}
		return $ms_table_names;
	}

	private function create_table( $table_name ) {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		if ( ! function_exists( 'dbDelta' ) ) {
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$sql = "CREATE TABLE $table_name (
            id INT(9) NOT NULL AUTO_INCREMENT,
            url VARCHAR(255) DEFAULT '' NOT NULL,
            hint_type VARCHAR(55) DEFAULT '' NOT NULL,
            status VARCHAR(55) DEFAULT 'enabled' NOT NULL,
            as_attr VARCHAR(55) DEFAULT '',
            type_attr VARCHAR(55) DEFAULT '',
            crossorigin VARCHAR(55) DEFAULT '',
            created_by VARCHAR(55) DEFAULT '' NOT NULL,
            auto_created INT(2) DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id)
        ) $charset;";

		dbDelta( $sql, true );
	}

	public function pprh_disable_wp_hints() {
		if ( 'true' === get_option( 'pprh_disable_wp_hints' ) ) {
			return remove_action( 'wp_head', 'wp_resource_hints', 2 );
		}
	}
}
