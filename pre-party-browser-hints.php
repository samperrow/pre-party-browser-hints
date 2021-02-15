<?php
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.7.5
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

define( 'PPRH_ABS_DIR', WP_PLUGIN_DIR . '/pre-party-browser-hints/' );
define( 'PPRH_REL_DIR', plugins_url() . '/pre-party-browser-hints/' );
define( 'PPRH_HOME_URL', admin_url() . 'admin.php?page=pprh-plugin-setttings' );
define( 'PPRH_DEBUG', true );

$pprh_load = new \PPRH\Pre_Party_Browser_Hints();

register_activation_hook( __FILE__, array( $pprh_load, 'activate_plugin' ) );
add_action( 'wpmu_new_blog', array( $pprh_load, 'activate_plugin' ) );

class Pre_Party_Browser_Hints {

    private $on_pprh_page = false;

	public function __construct() {
		$this->load_common_files();
		$this->create_constants();

		$this->on_pprh_page = Utils::on_pprh_page();

		if ( is_admin() ) {
            $this->load_admin();
		} else {
			$this->load_client();
		}

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
		include_once PPRH_ABS_DIR . 'includes/Preconnects.php';
	}

	public function activate_plugin() {
		include_once PPRH_ABS_DIR . 'includes/ActivatePlugin.php';
		$activate_plugin = new ActivatePlugin();
		$activate_plugin->init();
	}

	public function load_admin() {
		add_action( 'admin_menu', array( $this, 'load_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
		add_filter( 'set-screen-option', array( $this, 'pprh_set_screen_option' ), 10, 3 );
		load_plugin_textdomain( 'pprh', false, PPRH_REL_DIR . 'languages' );

		if ( $this->on_pprh_page || wp_doing_ajax() || defined( 'PPRH_TESTING' ) ) {
			include_once PPRH_ABS_DIR . 'includes/DisplayHints.php';
			include_once PPRH_ABS_DIR . 'includes/AjaxOps.php';
			new AjaxOps();
			do_action( 'pprh_load_admin' );
		}
	}

    public function load_client() {

		include_once PPRH_ABS_DIR . 'includes/LoadClient.php';
		new LoadClient();
		do_action( 'pprh_load_client' );
    }

	public function create_constants() {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';
		$plugin_version = get_option( 'pprh_version' );
		$pprh_pro_active = Utils::pprh_is_plugin_active();

		if ( ! defined( 'PPRH_VERSION' ) ) {
			define( 'PPRH_VERSION', $plugin_version );
			define( 'PPRH_DB_TABLE', $table );
			define( 'PPRH_IS_PRO_PLUGIN_ACTIVE', $pprh_pro_active );
        }
	}

	public function load_common_files() {
		include_once PPRH_ABS_DIR . 'includes/Utils.php';
		include_once PPRH_ABS_DIR . 'includes/DAO.php';
		include_once PPRH_ABS_DIR . 'includes/CreateHints.php';
		include_once PPRH_ABS_DIR . 'includes/NewHint.php';
	}

	public function load_admin_menu() {
		$settings_page = add_menu_page(
			'Pre* Party Settings',
			'Pre* Party',
			'update_plugins',
			'pprh-plugin-settings',
			array( $this, 'load_admin_page' ),
			PPRH_REL_DIR . 'images/lightning.png'
		);

		add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
		add_action( "load-{$settings_page}", array( $this, 'check_to_upgrade' ) );
//		add_action( 'admin_init', array( $this, 'create_meta_boxes' ) );
	}


//	public function create_meta_boxes() {
//		add_meta_box( 'pprhMEta', 'pprh meta box', array( $this, 'test_cb'), 'toplevel_page_pprh-plugin-settings', 'side', 'low');
//	}

//	public function test_cb() {
//		include_once PPRH_ABS_DIR . 'includes/views/Settings.php';
//		$settings = new Settings();
//
//		echo '<div id="pprh-settings" class="pprh-content">';
//
//		$settings->general_settings();
//        echo '</div>';
//	}


    public function load_admin_page() {
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		include_once PPRH_ABS_DIR . 'includes/LoadAdmin.php';
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

	public function check_to_upgrade() {
		$desired_version = '1.8.0';
		$current_version = get_option( 'pprh_version' );

		if ( empty( $current_version ) || version_compare( $current_version, $desired_version ) < 0 ) {
//			$this->activate_plugin();
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

}
