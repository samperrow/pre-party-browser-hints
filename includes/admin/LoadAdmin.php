<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoadAdmin {

	public function init() {
		add_action( 'admin_menu', array( $this, 'load_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'meta_boxes' ) );

		$on_pprh_page = Utils::on_pprh_page();

		if ( ! $on_pprh_page ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
		add_filter( 'set-screen-option', array( $this, 'pprh_set_screen_option' ), 10, 3 );
		load_plugin_textdomain( 'pprh', false, PPRH_REL_DIR . 'languages' );

		include_once 'NewHint.php';
		include_once 'DisplayHints.php';
		include_once 'AjaxOps.php';
		new AjaxOps();

		do_action( 'pprh_pro_load_admin' );
	}

	public function load_admin_menu() {
		$settings_page = add_menu_page(
			'Pre* Party Settings',
			'Pre* Party',
			'update_plugins',
			'pprh-plugin-settings',
			array( $this, 'load_dashboard' ),
			PPRH_REL_DIR . 'images/lightning.png'
		);

		add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
	}

	public function load_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$on_pprh_admin = Utils::on_pprh_admin();
		include_once 'Dashboard.php';

		$dashboard = new Dashboard( $on_pprh_admin );
		$dashboard->load_plugin_admin_files();
		$dashboard->show_plugin_dashboard();
	}

	public function screen_option() {
		$args = array(
			'label'   => 'Resource hints per page: ',
			'option'  => 'pprh_per_page',
			'default' => 10,
		);

		add_screen_option( 'per_page', $args );
	}

	public function pprh_set_screen_option( $status, $option, $value ) {
		return ( 'pprh_per_page' === $option ) ? $value : $status;
	}

	// Register and call the CSS and JS we need only on the needed page.
	public function register_admin_files( $hook ) {
		$str = apply_filters( 'pprh_append_string', 'toplevel_page_pprh-plugin-settings', '|post.php' );

		if ( strpos( $str, $hook, 0 ) !== false ) {
			$ajax_data = array(
				'nonce'     => wp_create_nonce( 'pprh_table_nonce' ),
				'admin_url' => admin_url()
			);

			wp_register_script( 'pprh_create_hints_js', PPRH_REL_DIR . 'js/create-hints.js', null, PPRH_VERSION, true );

			wp_register_script( 'pprh_admin_js', PPRH_REL_DIR . 'js/admin.js', array( 'jquery', 'pprh_create_hints_js' ), PPRH_VERSION, true );
			wp_localize_script( 'pprh_admin_js', 'pprh_data', $ajax_data );

			wp_register_style( 'pprh_styles_css', PPRH_REL_DIR . 'css/styles.css', null, PPRH_VERSION, 'all' );

			wp_enqueue_script( 'pprh_create_hints_js' );
			wp_enqueue_script( 'pprh_admin_js' );
			wp_enqueue_style( 'pprh_styles_css' );

			// needed for metaboxes
			wp_enqueue_script( 'post' );
		}
	}

	public function meta_boxes() {
		include_once 'views/settings/GeneralSettings.php';
		include_once 'views/settings/PreconnectSettings.php';
		include_once 'views/settings/PrefetchSettings.php';

		$general_settings = new GeneralSettings();
		$preconnect_settings = new PreconnectSettings(true);
		$prefetch_settings = new PrefetchSettings();

		add_meta_box(
			'pprh_general_settings_metabox',
			'General Settings',
			array( $general_settings, 'show_settings' ),
			'toplevel_page_pprh-plugin-settings',
			'normal',
			'low'
		);

		add_meta_box(
			'pprh_preconnect_settings_metabox',
			'Auto Preconnect Settings',
			array( $preconnect_settings, 'show_settings' ),
			'toplevel_page_pprh-plugin-settings',
			'normal',
			'low'
		);

		add_meta_box(
			'pprh_prefetch_settings_metabox',
			'Auto Prefetch Settings',
			array( $prefetch_settings, 'show_settings' ),
			'toplevel_page_pprh-plugin-settings',
			'normal',
			'low'
		);

		do_action( 'pprh_add_prerender_metabox' );
	}


}
