<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoadAdmin {

    public $on_pprh_admin_page = false;

	public function init() {
		\add_action( 'admin_menu', array( $this, 'load_admin_menu' ) );
		\add_action( 'admin_init', array( $this, 'add_settings_meta_boxes' ) );
		\add_action( 'load-post.php', array( $this, 'create_post_meta_box' ) );

		$this->on_pprh_admin_page = Utils::on_pprh_admin_page( \wp_doing_ajax() );

		$this->load_common_content();
		$this->load_admin_files();
	}

	public function load_common_content() {
		\add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
		\add_filter( 'set-screen-option', array( $this, 'pprh_set_screen_option' ), 10, 3 );

		include_once 'NewHint.php';
		include_once 'DisplayHints.php';
		include_once 'AjaxOps.php';
		include_once 'views/InsertHints.php';

		new AjaxOps();
		\do_action( 'pprh_pro_load_admin' );
	}

//    public function load_admin_screen_content( $on_pprh_admin_page ) {
//	    if ( ! $on_pprh_admin_page ) {
//	        return;
//        }
//
//		\add_action( 'admin_init', array( $this, 'add_settings_meta_boxes' ) );
//		$this->load_admin_files();
//	}


	public function load_admin_menu() {
		$settings_page = add_menu_page(
			'Pre* Party Settings',
			'Pre* Party',
			'update_plugins',
			PPRH_MENU_SLUG,
			array( $this, 'load_dashboard' ),
			PPRH_REL_DIR . 'images/lightning.png'
		);

		\add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
    }

	public function load_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

//		if ( $this->on_pprh_admin_page ) {
			$dashboard = new Dashboard();
			$dashboard->show_plugin_dashboard( $this->on_pprh_admin_page );
//		}
	}

	public function load_admin_files() {
		include_once 'Dashboard.php';
		include_once 'views/Settings.php';
		include_once 'views/HintInfo.php';
		include_once 'views/Upgrade.php';
		include_once 'views/settings/GeneralSettings.php';
		include_once 'views/settings/PreconnectSettings.php';
		include_once 'views/settings/PrefetchSettings.php';
	}

	public function screen_option() {
		$args = array(
			'label'   => 'Resource hints per page: ',
			'option'  => 'pprh_per_page',
			'default' => 10
		);

		add_screen_option( 'per_page', $args );
	}

	public function pprh_set_screen_option( $status, $option, $value ) {
		return ( 'pprh_per_page' === $option ) ? $value : $status;
	}

	// Register and call the CSS and JS we need only on the needed page.
	public function register_admin_files( $hook ) {
		$str = PPRH_ADMIN_SCREEN . 'post.php';

		if ( str_contains( $str, $hook ) ) {
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

	public function add_settings_meta_boxes() {
		$general_settings = new GeneralSettings();
		$preconnect_settings = new PreconnectSettings();
		$prefetch_settings = new PrefetchSettings();

		add_meta_box(
			'pprh_general_settings_metabox',
			'General Settings',
			array( $general_settings, 'show_settings' ),
			PPRH_ADMIN_SCREEN,
			'normal',
			'low'
		);

		add_meta_box(
			'pprh_preconnect_settings_metabox',
			'Auto Preconnect Settings',
			array( $preconnect_settings, 'show_settings' ),
			PPRH_ADMIN_SCREEN,
			'normal',
			'low'
		);

		add_meta_box(
			'pprh_prefetch_settings_metabox',
			'Auto Prefetch Settings',
			array( $prefetch_settings, 'show_settings' ),
			PPRH_ADMIN_SCREEN,
			'normal',
			'low'
		);


		add_meta_box(
			'pprh_prerender_settings_metabox',
			'Auto Prerender Settings',
			array( $this, 'create_prerender_metabox' ),
			PPRH_ADMIN_SCREEN,
			'normal',
			'low'
		);
	}

	public function create_prerender_metabox() {
		$load = \apply_filters( 'pprh_load_prerender_metabox', null );

		if ( null === $load ) {
			?>
			<div style="text-align: center; max-width: 800px; margin: 0 auto;">
				<h3><?php \esc_html_e( 'This feature is only available after upgrading to the Pro version.', 'pprh' ); ?></h3>
				<p><?php \esc_html_e( 'Auto Prerender will automatically create the proper prerender hints automatically, for each post on your website.
		This feature works by implementing custom analytics to determine which page a visitor is most likely to navigate towards after from a given page, and a prerender hint is created pointing to that destination.
		This prerender hint allows a visitor to download an entire webpage in the background, allowing the page to load instantly.
		For example, if most visitors navigate to your /shop page from your home page, a prerender hint will be created for the /shop URL, and that page will be downloaded while the visitor is on the home page. ', 'pprh' ); ?></p>
				<input id="pprhOpenCheckoutModal" type="button" class="button button-primary" value="Purchase License"/>
			</div>
			<?php
		}
	}

//	public function load_post_files() {
//		include_once 'Posts.php';

//		if ( ! class_exists( \PPRH\InsertHints::class ) ) {
//			include_once PPRH_ABS_DIR . 'includes/admin/views/InsertHints.php';
//		}
//
//		if ( ! class_exists( \PPRH\DisplayHints::class ) ) {
//			include_once PPRH_ABS_DIR . 'includes/admin/DisplayHints.php';
//		}

//		$posts = new Posts( $this->has_valid_license );
//		unset( $posts );
//	}

	public function create_post_meta_box() {
//		$callback_name = $this->post_metabox();
		$modal_types = \get_option( 'pprh_pro_post_modal_types', array( 'post', 'page' ) );
		$id       = 'pprh_post_meta';
		$title    = 'Pre* Party Resource Hints';
		$callback = array( $this, 'post_metabox' );
		$context  = 'normal';
		$priority = 'low';
		$screens = Utils::clean_string_array( $modal_types );

		if ( is_array( $screens ) && count( $screens ) > 0 ) {
			foreach ( $screens as $screen ) {
				\add_meta_box( $id, $title, $callback, $screen, $context, $priority );
			}
		}
	}

	public function post_metabox() {
	    $res = \apply_filters( 'pprh_posts_get_proper_callback', null );

	    if ( null === $res ) { ?>
            <div style="text-align: center;">
                <h3><?php \esc_html_e( 'Upgrade to Pre* Party Resource Hints Pro to enjoy these features:', 'pprh' ); ?></h3>
                <ul style="max-width: 500px; text-align: left; list-style-type: disc; display: block; margin: 0 auto;">
                    <li>Implement resource hints to specific posts and pages.</li>
                    <li>Automatic and post-specific creation of custom preconnect hints for each post/page.</li>
                </ul>
                <input id="pprhOpenCheckoutModal" type="button" class="button button-primary" value="Purchase License"/>
            </div>
        <?php }
	}



}
