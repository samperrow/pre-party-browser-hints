<?php

namespace PPRH;

use PPRH\settings\SettingsView;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoadAdminPro {

	private $show_posts_on_front;

//	public $license_verified;
	public $post_args;

	public function __construct( bool $show_posts_on_front ) {
//		$this->license_verified    = $license_verified;
		$this->show_posts_on_front = $show_posts_on_front;
//		\add_action( 'pprh_load_license_view', array( $this, 'load_license_view' ), 10, 0 );
		\add_filter( 'pprh_load_tabs', array( $this, 'load_tabs' ), 10, 2 );
		\add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ), 10, 1 );
		//	\add_action( 'admin_footer', array( $this, 'check_permlink_change' ) );
	}

	public function load( int $plugin_page ):bool {
//		if ( ! $this->license_verified ) {
//			return false;
//		}

		$display_hints  = new DisplayHintsPro();
		$new_hint_child = new NewHintChild( $plugin_page );

		// on pprh admin page.
		if ( 1 === $plugin_page ) {
			$settings_view = new SettingsView( $this->show_posts_on_front );
			$prerender = new Prerender( $this->show_posts_on_front );
			unset( $settings_view, $prerender );
		} elseif ( 2 === $plugin_page ) {
			$posts = new Posts( $this->show_posts_on_front );
			unset( $posts );
		}

		unset( $display_hints, $new_hint_child );
		return true;
	}

	// Register and call the CSS and JS we need only on the needed page.
	public function register_admin_files( $hook ) {
//		if ( $this->license_verified ) {
			\wp_register_script( 'pprh_pro_admin_js', PPRH_PRO_REL_DIR . 'js/pprh-pro-admin.js', array( 'pprh_admin_js' ), PPRH_VERSION, true );
			\wp_enqueue_script( 'pprh_pro_admin_js' );
			return true;
//		}

		return false;
	}

	public function load_tabs( $tabs ) {
		unset( $tabs['faq'] );
//		$tabs['license'] = 'License';
		$tabs['faq']     = 'FAQ';
		return $tabs;
	}

//	public function load_license_view() {
//		$license_view = new LicenseView();
//		$license_view->markup();
//	}

}
