<?php

namespace PPRH;

//use \PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoadAdmin {

	public $plugin_page;

	public function init( int $plugin_page ) {
		$this->plugin_page = $plugin_page;
		\add_action( 'admin_menu', array( $this, 'load_admin_menu' ) );

		if ( $this->plugin_page > 0 ) {
			\add_action( 'admin_init', array( $this, 'add_settings_meta_boxes' ) );
			\add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
			\add_filter( 'set-screen-option', array( $this, 'pprh_set_screen_option' ), 10, 3 );
			$this->load_common_content();
			\apply_filters( 'pprh_load_pro_admin', $this->plugin_page );
		}
	}

	public function load_common_content() {
		$ajax_ops = new AjaxOps( $this->plugin_page );
		$ajax_ops->set_actions();
	}

    // icon not appearing with "disable all wp updates" plugin.
	public function load_admin_menu() {
		$settings_page = \add_menu_page(
			'Pre* Party Settings',
			'Pre* Party',
			'manage_options',
			PPRH_MENU_SLUG,
			array( $this, 'load_dashboard' ),
			PPRH_REL_DIR . 'images/lightning.png'
		);

		\add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
	}

	public function load_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			\wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$dashboard = new Dashboard();
		$dashboard->show_plugin_dashboard( $this->plugin_page );
	}

	public function screen_option() {
		$args = array(
			'label'   => 'Resource hints per page: ',
			'option'  => 'pprh_per_page',
			'default' => 10
		);

		\add_screen_option( 'per_page', $args );
	}

	public function pprh_set_screen_option( $status, $option, $value ) {
		return ( 'pprh_per_page' === $option ) ? $value : $status;
	}

	// Register and call the CSS and JS we need only on the needed page.
	public function register_admin_files( string $hook ) {
//		if ( str_contains( PPRH_ADMIN_SCREEN, $hook ) ) {

			$ajax_data = array(
				'nonce'     => wp_create_nonce( 'pprh_table_nonce' ),
				'admin_url' => admin_url()
			);

			\wp_register_script( 'pprh_admin_js', PPRH_REL_DIR . 'js/admin.js', array( 'jquery' ), PPRH_VERSION, true );
			\wp_localize_script( 'pprh_admin_js', 'pprh_data', $ajax_data );

			\wp_register_style( 'pprh_styles_css', PPRH_REL_DIR . 'css/styles.css', null, PPRH_VERSION, 'all' );
			\wp_enqueue_style( 'pprh_styles_css' );
			\wp_enqueue_script( 'pprh_admin_js' );
			\wp_enqueue_script( 'post' );			// needed for metaboxes
//		}
	}

	public function add_settings_meta_boxes() {
		$settings_view = new SettingsView();

		\add_meta_box(
			'pprh_general_metabox',
			'General Settings',
			array( $settings_view, 'general_markup' ),
			PPRH_ADMIN_SCREEN,
			'normal',
			'low'
		);

		\add_meta_box(
			'pprh_preconnect_metabox',
			'Auto Preconnect Settings',
			array( $settings_view, 'preconnect_markup' ),
			PPRH_ADMIN_SCREEN,
			'normal',
			'low'
		);

		\add_meta_box(
			'pprh_prefetch_metabox',
			'Auto Prefetch Settings',
			array( $settings_view, 'prefetch_markup' ),
			PPRH_ADMIN_SCREEN,
			'normal',
			'low'
		);

		\add_meta_box(
			'pprh_preload_metabox',
			'Auto Preload Settings',
			array( $this, 'create_preload_metabox' ),
			PPRH_ADMIN_SCREEN,
			'normal',
			'low'
		);

		\add_meta_box(
			'pprh_prerender_metabox',
			'Auto Prerender Settings',
			array( $this, 'create_prerender_metabox' ),
			PPRH_ADMIN_SCREEN,
			'normal',
			'low'
		);
	}

	public function create_preload_metabox() {
		$load_metabox = \apply_filters( 'pprh_load_pro_metabox', 'preload' );

		if ( is_string( $load_metabox ) ) {
			?>
            <div style="text-align: center; max-width: 800px; margin: 0 auto;">
                <h3><?php \esc_html_e( 'This feature is only available after upgrading to the Pro version.', 'pre-party-browser-hints' ); ?></h3>
                <p><?php \esc_html_e( 'The Auto Preload feature will automatically create the proper preload hints for your entire site automatically, for each post on your website.', 'pre-party-browser-hints' ); ?></p>
                <input type="button" class="pprhOpenCheckoutModal button button-primary" value="Purchase License"/>
            </div>
			<?php
		}
	}


	public function create_prerender_metabox() {
		$load_metabox = \apply_filters( 'pprh_load_pro_metabox', 'prerender' );

		if ( is_string( $load_metabox ) ) {
			?>
			<div style="text-align: center; max-width: 800px; margin: 0 auto;">
				<h3><?php \esc_html_e( 'This feature is only available after upgrading to the Pro version.', 'pre-party-browser-hints' ); ?></h3>
				<p><?php \esc_html_e( 'The Auto Prerender feature will automatically create the proper prerender hints, for each post on your website.
		This feature works by implementing custom analytics to determine which page a visitor is most likely to navigate towards after from a given page, and a prerender hint is created pointing to that destination.
		The prerender hint allows a visitor to download an entire webpage in the background, allowing the page to load instantly.
		For example, if most visitors navigate to your /shop page from your home page, a prerender hint will be created for the /shop URL, and that page will be downloaded while the visitor is on the home page. ', 'pre-party-browser-hints' ); ?></p>
				<input type="button" class="pprhOpenCheckoutModal button button-primary" value="Purchase License"/>
			</div>
			<?php
		}
	}

}
