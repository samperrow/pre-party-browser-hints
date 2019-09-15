<?php
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.6.2
 * Requires at least: 4.4
 * Requires PHP:      5.3
 * Author:            Sam Perrow
 * Author URI:        https://www.linkedin.com/in/sam-perrow
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pprh
 * Domain Path:       /languages
 *
 * Copyright 2016  Sam Perrow  (email : sam.perrow399@gmail.com)
 *
*/

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'COMET_CACHE_ALLOWED', false );

function pprh_check_page() {
	global $pagenow;

	if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) ) {
		$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
		if ( 'pprh-plugin-settings' === $page ) {
			return 'pprhAdmin';
		}
	}
}


new PPRH_Init();

final class PPRH_Init {

	public function __construct() {
		add_action( 'init', array( $this, 'initialize' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
		register_activation_hook( __FILE__, array( $this, 'install_db_table' ) );
		add_action( 'wpmu_new_blog', array( $this, 'install_db_table' ) );
		add_filter( 'set-screen-option', array( $this, 'apply_wp_screen_options' ), 10, 3 );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'set_admin_links' ) );
	}

	public function initialize() {

		$this->create_constants();
		$this->check_for_update();

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'load_admin_page' ) );
		} else {
			$this->pprh_disable_wp_hints();
			include_once PPRH_PLUGIN_DIR . '/class-pprh-send-hints.php';
			new PPRH_Send_Hints();
		}

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
		if ( 'true' === get_option( 'pprh_autoload_preconnects' ) ) {
			include_once PPRH_PLUGIN_DIR . '/class-pprh-ajax.php';
			new PPRH_Ajax();
		}
	}

	public function load_admin_page() {

		$settings_page = add_menu_page(
			'Pre* Party Settings',
			'Pre* Party',
			'manage_options',
			'pprh-plugin-settings',
			array( $this, 'show_tabs' ),
			plugins_url( PPRH_PLUGIN_FILENAME . '/images/lightning.png' )
		);

		add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
		add_action( "load-{$settings_page}", array( $this, 'load_admin_files' ) );
	}

	public function create_constants() {
		if ( ! defined( 'PPRH_VERSION' ) ) {
			define( 'PPRH_VERSION', '1.6.0' );
		}
		if ( ! defined( 'PPRH_PLUGIN_FILENAME' ) ) {
			define( 'PPRH_PLUGIN_FILENAME', '/pre-party-browser-hints' );
		}
		if ( ! defined( 'PPRH_PLUGIN_DIR' ) ) {
			define( 'PPRH_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) . '/includes' );
		}
		if ( ! defined( 'PPRH_CHECK_PAGE' ) ) {
			define( 'PPRH_CHECK_PAGE', pprh_check_page() );
		}
	}

	public function load_admin_files() {
		include_once PPRH_PLUGIN_DIR . '/class-pprh-misc.php';
		include_once PPRH_PLUGIN_DIR . '/class-pprh-create-hints.php';
		include_once PPRH_PLUGIN_DIR . '/class-pprh-display-hints.php';
	}

	public function show_tabs() {
		include_once PPRH_PLUGIN_DIR . '/class-pprh-admin-tabs.php';
		new PPRH_Admin_Tabs();
	}

	public function apply_wp_screen_options( $status, $option, $value ) {
		return ( 'pprh_screen_options' === $option ) ? $value : $status;
	}

	public function screen_option() {
		$option = 'per_page';
		$args   = array(
			'label'   => 'URLs',
			'default' => 10,
			'option'  => 'pprh_screen_options',
		);

		add_screen_option( $option, $args );
	}

	// Register and call the CSS and JS we need only on the needed page.
	public function register_admin_files( $hook ) {

		wp_register_script( 'pprh_admin_js', plugin_dir_url( __FILE__ ) . 'js/admin.js', null, PPRH_VERSION, true );
		wp_register_style( 'pprh_styles_css', plugin_dir_url( __FILE__ ) . 'css/styles.css', null, PPRH_VERSION, 'all' );

		if ( 'toplevel_page_pprh-plugin-settings' === $hook ) {
			wp_enqueue_script( 'pprh_admin_js' );
			wp_enqueue_style( 'pprh_styles_css' );
		}
	}

	public function set_admin_links( $links ) {
		$pprh_links = array(
			'<a href="https://github.com/samperrow/pre-party-browser-hints">View on GitHub</a>',
			'<a href="https://www.paypal.me/samperrow">Donate</a>',
		);
		return array_merge( $links, $pprh_links );
	}

	// Implement option to disable automatically generated resource hints.
	public function pprh_disable_wp_hints() {
		if ( 'true' === get_option( 'pprh_disable_wp_hints' ) ) {
			return remove_action( 'wp_head', 'wp_resource_hints', 2 );
		}
	}

	public function check_for_update() {
		global $wpdb;
		$new_table = $wpdb->prefix . 'pprh_table';
		$old_table = $wpdb->prefix . 'gktpp_table';

		$query = $wpdb->query(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $old_table )
		);

		// user is upgrading to new version.
		if ( 1 === $query ) {
			$wpdb->query( "RENAME TABLE $old_table TO $new_table" );
			$wpdb->query( "ALTER TABLE $new_table ADD created_by varchar(55), DROP COLUMN header_string, DROP COLUMN head_string" );

			$this->update_option( 'gktpp_reset_preconnect', 'pprh_preconnects_set', 'set' );
			$this->update_option( 'gktpp_disable_wp_hints', 'pprh_disable_wp_hints', 'Yes' );
			$this->update_option( 'gktpp_preconnect_status', 'pprh_autoload_preconnects', 'Yes' );

			delete_option( 'gktpp_send_in_header' );
			add_option( 'pprh_allow_unauth', 'true', '', 'yes' );
		}
	}

	public function update_option( $old_option_name, $new_option_name, $prev_value ) {
		$new_value = ( $prev_value === get_option( $old_option_name ) ) ? 'true' : 'false';
		add_option( $new_option_name, $new_value, '', 'yes' );
		delete_option( $old_option_name );
	}

	// Multisite install/delete db table.
	public function install_db_table() {
		global $wpdb;

		add_option( 'pprh_autoload_preconnects', 'true', '', 'yes' );
		add_option( 'pprh_allow_unauth', 'true', '', 'yes' );
		add_option( 'pprh_preconnects_set', 'false', '', 'yes' );

		$charset_collate = $wpdb->get_charset_collate();

		if ( ! function_exists( 'dbDelta' ) ) {
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$pprh_tables = array( $new_table );

		if ( is_multisite() ) {
			$blog_table = $wpdb->base_prefix . 'blogs';

			$data = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT blog_id FROM %s WHERE blog_id != %d',
					$blog_table,
					1
				)
			);

			if ( ! empty( $data ) ) {
				foreach ( $data as $object ) {
					$site_pp_table = $wpdb->base_prefix . $object->blog_id . '_pprh_table';
					array_push( $pprh_tables, $site_pp_table );
				}
			}
		}

		foreach ( $pprh_tables as $pprh_table ) {

			$sql = "CREATE TABLE IF NOT EXISTS $pprh_table (
				id INT(9) NOT NULL AUTO_INCREMENT,
				url VARCHAR(255) DEFAULT '' NOT NULL,
				hint_type VARCHAR(55) DEFAULT '' NOT NULL,
				status VARCHAR(55) DEFAULT 'enabled' NOT NULL,
				as_attr VARCHAR(55) DEFAULT '',
				type_attr VARCHAR(55) DEFAULT '',
				crossorigin VARCHAR(55) DEFAULT '',
				ajax_domain TINYINT(1) DEFAULT 0 NOT NULL,
				created_by VARCHAR(55) DEFAULT '' NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

			dbDelta( $sql, true );
		}

	}

}
