<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoadAdmin {

	public $all_hints = array();

    public function __construct( $all_hints ) {
        $this->all_hints = $all_hints;
	}

	public function init() {
		$utils = new Utils();
		$on_pprh_page = $utils->on_pprh_page();
		add_action( 'admin_menu', array( $this, 'load_admin_menu' ) );

		if ( ! $on_pprh_page ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
		add_filter( 'set-screen-option', array( $this, 'pprh_set_screen_option' ), 10, 3 );
		load_plugin_textdomain( 'pprh', false, PPRH_REL_DIR . 'languages' );
		add_action( 'pprh_load_dashboard', array( $this, 'load_dashboard' ) );

		include_once PPRH_ABS_DIR . 'includes/admin/DisplayHints.php';
		include_once PPRH_ABS_DIR . 'includes/AjaxOps.php';
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
//		add_action( "load-{$settings_page}", array( $this, 'check_to_upgrade' ) );
	}

	public function load_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$on_pprh_admin = Utils::on_pprh_admin();
		include_once PPRH_ABS_DIR . 'includes/admin/Dashboard.php';

		$dashboard = new Dashboard( $this->all_hints, $on_pprh_admin );
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
		$str = apply_filters( 'pprh_la_load_scripts', 'toplevel_page_pprh-plugin-settings' );

		if ( strpos( $str, $hook, 0 ) !== false ) {
			$ajax_data = array(
				'nonce'     => wp_create_nonce( 'pprh_table_nonce' ),
				'admin_url' => admin_url()
			);

			wp_register_script( 'pprh_create_hints_js', PPRH_REL_DIR . 'js/create-hints.js', array( 'jquery' ), PPRH_VERSION, true );

			wp_register_script( 'pprh_admin_js', PPRH_REL_DIR . 'js/admin.js', array( 'jquery', 'pprh_create_hints_js' ), PPRH_VERSION, true );
			wp_localize_script( 'pprh_admin_js', 'pprh_data', $ajax_data );

//			wp_localize_script( 'pprh_create_hints_js', 'pprh_data', $ajax_data );

			wp_register_style( 'pprh_styles_css', PPRH_REL_DIR . 'css/styles.css', null, PPRH_VERSION, 'all' );

			wp_enqueue_script( 'pprh_create_hints_js' );
			wp_enqueue_script( 'pprh_admin_js' );
			wp_enqueue_style( 'pprh_styles_css' );
//			do_action( 'pprh_register_admin_files' );
		}
	}




}
