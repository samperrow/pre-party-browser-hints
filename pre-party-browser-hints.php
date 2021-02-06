<?php
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.8.0
 * Requires at least: 4.4
 * Requires PHP:      5.6.30
 * Author:            Sam Perrow
 * Author URI:        https://www.linkedin.com/in/sam-perrow
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pprh
 * Domain Path:       /languages
 *
 * last edited December 23, 2020
 *
 * Copyright 2016  Sam Perrow  (email : sam.perrow399@gmail.com)
 *
 */

namespace PPRH;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Pre_Party_Browser_Hints' ) ) {
	new Pre_Party_Browser_Hints();
}

class Pre_Party_Browser_Hints {

    private $on_pprh_page = false;

	public function __construct() {
		$this->init();
	}

	public function init() {
		$this->create_constants();
		$this->load_common_files();
		$this->on_pprh_page = Utils::on_pprh_page();

		do_action( 'pprh_load_pro' );

		if ( is_admin() ) {
			add_action( 'wpmu_new_blog', array( $this, 'activate_plugin' ) );
			register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
			add_action( 'admin_menu', array( $this, 'load_admin_page' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
			add_filter( 'set-screen-option', array( $this, 'apply_wp_screen_options' ), 10, 3 );

			if ( $this->on_pprh_page || wp_doing_ajax() || defined( 'PPRH_TESTING' ) ) {
                include_once PPRH_ABS_DIR . 'includes/DisplayHints.php';
				include_once PPRH_ABS_DIR . 'includes/AjaxOps.php';
				new AjaxOps();
				do_action( 'pprh_load_admin' );
			}

		} else {
			include_once PPRH_ABS_DIR . 'includes/LoadClient.php';
			new LoadClient();
			do_action( 'pprh_load_client' );
		}

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
//		if ( self::load_auto_preconnects() ) {
//		    if ( ! defined( 'PPRH_DOING_AUTO_PRECONNECTS' ) ) {
//				define( 'PPRH_DOING_AUTO_PRECONNECTS', true );
//            }
			include_once PPRH_ABS_DIR . 'includes/Preconnects.php';
//		}
	}

	public function create_constants() {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';
		$plugin_version = get_option( 'pprh_version' );
		$abs_dir = WP_PLUGIN_DIR . '/pre-party-browser-hints/';
		$rel_dir = plugins_url() . '/pre-party-browser-hints/';
		$home_url = admin_url() . 'admin.php?page=pprh-plugin-setttings';

		if ( ! defined( 'PPRH_VERSION' ) ) {
			define( 'PPRH_VERSION', $plugin_version );
			define( 'PPRH_DB_TABLE', $table );
			define( 'PPRH_ABS_DIR', $abs_dir );
			define( 'PPRH_REL_DIR', $rel_dir );
			define( 'PPRH_HOME_URL', $home_url );
        }
	}

	public function load_common_files() {
		include_once PPRH_ABS_DIR . 'includes/Utils.php';
		include_once PPRH_ABS_DIR . 'includes/DAO.php';
		include_once PPRH_ABS_DIR . 'includes/CreateHints.php';
		include_once PPRH_ABS_DIR . 'includes/NewHint.php';
	}

//	public static function load_auto_preconnects() {
//		$autoload = get_option( 'pprh_preconnect_autoload' );
//		$preconnects_set = get_option( 'pprh_preconnect_set' );
//		$load_free = ( 'true' === $autoload && 'false' === $preconnects_set );
//		return ( $load_free );
//	}

	public function load_admin_page() {
		$settings_page = add_menu_page(
			'Pre* Party Settings',
			'Pre* Party',
			'manage_options',
			'pprh-plugin-settings',
			array( $this, 'load_admin' ),
			PPRH_REL_DIR . 'images/lightning.png'
		);

		add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
	}

	public function screen_option() {
		$args = array(
			'label'   => 'URLs',
			'option'  => 'pprh_screen_options',
			'default' => 10,
		);

		$this->check_to_upgrade();
		add_screen_option( 'per_page', $args );
	}

	public function load_admin() {
		if ( ! current_user_can( 'update_plugins' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once PPRH_ABS_DIR . 'includes/LoadAdmin.php';
	}

	public function check_to_upgrade() {
		$desired_version = '1.8.0';
		$current_version = get_option( 'pprh_version' );

		if ( empty( $current_version ) || version_compare( $current_version, $desired_version ) < 0 ) {
			$this->activate_plugin();
			update_option( 'pprh_version', $desired_version );
			add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );
		}
	}

	public function upgrade_notice() {
		?>
		<div class="notice notice-info is-dismissible">
			<p><?php _e('1.7.4.2 update info: ' ); ?></p>
		</div>
		<?php
	}

	// Register and call the CSS and JS we need only on the needed page.
	public function register_admin_files( $hook ) {
	    $str = apply_filters( 'pprh_load_scripts', 'toplevel_page_pprh-plugin-settings' );

		if ( strpos( $str, $hook, 0 ) !== false ) {
			$ajax_data = array(
				'nonce'     => wp_create_nonce( 'pprh_table_nonce' ),
				'admin_url' => admin_url()
			);

			wp_register_script( 'pprh_admin_js', PPRH_REL_DIR . 'js/admin.js', array( 'jquery' ), PPRH_VERSION, true );
			wp_localize_script( 'pprh_admin_js', 'pprh_data', $ajax_data );
			wp_register_style( 'pprh_styles_css', PPRH_REL_DIR . 'css/styles.css', null, PPRH_VERSION, 'all' );
			wp_enqueue_script( 'pprh_admin_js' );
			wp_enqueue_style( 'pprh_styles_css' );
			do_action( 'pprh_register_admin_files' );
		}
	}

	public function activate_plugin() {
		include_once PPRH_ABS_DIR . 'includes/ActivatePlugin.php';
		new ActivatePlugin();
	}

	public function apply_wp_screen_options( $status, $option, $value ) {
		return ( 'pprh_screen_options' === $option ) ? $value : $status;
	}

}