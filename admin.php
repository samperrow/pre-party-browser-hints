<?php
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.7.0
 * Requires at least: 4.4
 * Requires PHP:      5.3
 * Author:            Sam Perrow
 * Author URI:        https://www.linkedin.com/in/sam-perrow
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pre-party-browser-hints
 * Domain Path:       /languages
 *
 * @package asfdfsad
 * last edited February 9, 2020
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

final class Init {

	public function __construct() {
		add_action( 'init', array( $this, 'initialize' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
		add_filter( 'set-screen-option', array( $this, 'apply_wp_screen_options' ), 10, 3 );
		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		add_action( 'wpmu_new_blog', array( $this, 'activate_plugin' ) );
		add_action( 'delete_post', array( $this, 'delete_post_hints' ) );
        add_action( 'admin_footer', array( $this, 'check_permlink_change' ) );
    }

	public function initialize() {
		$this->create_constants();

		include_once PPRH_PLUGIN_DIR . '/class-pprh-utils.php';
		include_once PPRH_PLUGIN_DIR . '/class-pprh-updater.php';

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'load_admin_page' ) );
            add_action( 'save_post', array( $this, 'pprh_update_url' ) );
            include_once PPRH_PLUGIN_DIR . '/class-pprh-ajax-ops.php';
		} else {
			$this->pprh_disable_wp_hints();
			include_once PPRH_PLUGIN_DIR . '/class-pprh-send-hints.php';
		}

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
		if ( 'true' === get_option( 'pprh_autoload_preconnects' ) ) {
			include_once PPRH_PLUGIN_DIR . '/class-pprh-ajax.php';
			new Ajax();
		}
	}

	public function check_permlink_change() {
	    $wp_perm_links = get_option( 'permalink_structure' );
	    $pprh_perm_links = get_option( 'pprh_permalink_copy' );

	    if ( $wp_perm_links !== $pprh_perm_links ) {
	        $this->update_perm_links();
	        update_option( 'pprh_permalink_copy', $wp_perm_links );
        }
    }

    private function update_perm_links() {
        global $wpdb;
        $table = PPRH_DB_TABLE;

        $post_hints = $wpdb->get_results(
            $wpdb->prepare( "SELECT id, post_id FROM $table WHERE post_id != %s", 'global' )
        );

        foreach( $post_hints as $post_hint ) {
            $post_id = (int) $post_hint->post_id;
            $hint_id = (int) $post_hint->id;
            $post_url = Utils::get_url_query_from_post_id( $post_id );
            $wpdb->query(
                $wpdb->prepare("UPDATE $table SET post_url = %s WHERE id = %d", $post_url, $hint_id )
            );
        }
    }


	public function load_admin_page() {
		$settings_page = add_menu_page(
			'Pre* Party Settings',
			'Pre* Party',
			'manage_options',
			'pprh-plugin-settings',
			array( $this, 'load_files' ),
			plugins_url( PPRH_PLUGIN_FILENAME . '/images/lightning.png' )
		);

		add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
		add_action( 'load-post.php', array( $this, 'load_files' ) );
	}

	public function create_constants() {
		if ( ! defined( 'PPRH\TABLE' ) ) {
			global $wpdb;
			$table = $wpdb->prefix . 'pprh_table';
			define( 'PPRH_DB_TABLE', $table );
			define( 'PPRH_VERSION', '2.0.0' );
			define( 'PPRH_PLUGIN_FILENAME', '/pprh-pro' );
			define( 'PPRH_PLUGIN_DIR', untrailingslashit( __DIR__ ) . '/includes' );
			define( 'PPRH_CHECK_PAGE', $this->check_page() );
			define( 'PPRH_CHECKOUT_LINK', 'https://sphacks.io/checkout' );
		}
	}

	public function load_files() {
		include_once PPRH_PLUGIN_DIR . '/class-pprh-create-hints.php';
		include_once PPRH_PLUGIN_DIR . '/class-pprh-display-hints.php';
		include_once PPRH_PLUGIN_DIR . '/class-pprh-add-new-hint.php';

		if ( 'pprhAdmin' === PPRH_CHECK_PAGE ) {
			include_once PPRH_PLUGIN_DIR . '/class-pprh-admin-tabs.php';
		} elseif ( 'pprhPostEdit' === PPRH_CHECK_PAGE ) {
			include_once PPRH_PLUGIN_DIR . '/class-pprh-posts.php';
		}
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

	public function check_page() {
		global $pagenow;
		$page = '';

		if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) ) {
			$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
			if ( 'pprh-plugin-settings' === $page ) {
				$page = 'pprhAdmin';
			}
		} elseif ( 'post.php' === $pagenow && isset( $_GET['action'] ) ) {
			$page = sanitize_text_field( wp_unslash( $_GET['action'] ) );
			if ( 'edit' === $page ) {
				$page = 'pprhPostEdit';
			}
		}
		return $page;
	}

	// Register and call the CSS and JS we need only on the needed page.
	public function register_admin_files( $hook ) {
		$ajax_data = array(
			'val' => wp_create_nonce( 'pprh_table_nonce' ),
		);

		$css_changed_time = filemtime( __DIR__ . '/css/styles.css' );
		$js_changed_time = filemtime( __DIR__ . '/js/admin.js' );

		wp_register_script( 'pprh_admin_js', plugin_dir_url( __FILE__ ) . 'js/admin.js', null, '', true );
		wp_localize_script( 'pprh_admin_js', 'pprh_nonce', $ajax_data );
		wp_register_style( 'pprh_styles_css', plugin_dir_url( __FILE__ ) . '/css/styles.css', null, $css_changed_time, 'all' );

		if ( preg_match( '/toplevel_page_pprh-plugin-settings|post.php/i', $hook ) ) {
			wp_enqueue_script( 'pprh_admin_js' );
			wp_enqueue_style( 'pprh_styles_css' );
		}
	}

	// Implement option to disable automatically generated resource hints.
	public function pprh_disable_wp_hints() {
		return ( 'true' === get_option( 'pprh_disable_wp_hints' ) ) ? remove_action( 'wp_head', 'wp_resource_hints', 2 ) : false;
	}

	public function activate_plugin() {
		$this->create_constants();

		// If upgrading from 1.7, set those hints' post id values to 'global'.
		if ( get_option( 'pprh_autoload_preconnects' ) !== '' && empty( get_option( 'pprh_license_key' ) ) ) {
			$this->set_previous_hints_to_global();
		}

		$this->set_options();
		$this->setup_tables();
	}

	public function set_previous_hints_to_global() {
		global $wpdb;
		$table = PPRH_DB_TABLE;
		$count = $wpdb->prepare( "SELECT COUNT(*) FROM $table" );

		if ( $count > 0 ) {
			$wpdb->query(
				$wpdb->prepare( "UPDATE $table SET post_id = %s", 'global' )
			);
		}
	}

	public function set_options() {
        include_once PPRH_PLUGIN_DIR . '/class-pprh-utils.php';
        $perm_structure = get_option( 'permalink_structure' );
        $default_modal_pages = Utils::get_default_modal_post_types();

		add_option( 'pprh_autoload_preconnects', 'true', '', 'yes' );
		add_option( 'pprh_reset_home_preconnects', 'true', '', 'yes' );
		add_option( 'pprh_reset_global_preconnects', 'true', '', 'yes' );

		add_option( 'pprh_allow_unauth', 'true', '', 'yes' );
		add_option( 'pprh_disable_wp_hints', 'true', '', 'yes' );
		add_option( 'pprh_html_head', 'true', '', 'yes' );
		add_option( 'pprh_post_modal_types', $default_modal_pages, '', 'yes' );
        add_option( 'pprh_permalink_copy', $perm_structure, '', 'yes' );

		add_option( 'pprh_license_key', '', '', 'yes' );
		add_option( 'pprh_license_status', '', '', 'yes' );
	}

	// Multisite install/delete db table.
	public function setup_tables() {
		global $wpdb;

		if ( ! function_exists( 'dbDelta' ) ) {
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$pprh_tables = array( PPRH_DB_TABLE );

		if ( is_multisite() ) {
			$pprh_tables[] = $this->get_multisite_tables();
		}

		foreach ( $pprh_tables as $pprh_table ) {
			$this->table_sql( $pprh_table );
		}
	}

	private function get_multisite_tables() {
		$blog_table = $wpdb->base_prefix . 'blogs';
		$ms_tables = array();

		$data = $wpdb->get_results(
			$wpdb->prepare( 'SELECT blog_id FROM %s WHERE blog_id != %d', $blog_table, 1 )
		);

		if ( ! empty( $data ) ) {
			foreach ( $data as $object ) {
				$site_pp_table = $wpdb->base_prefix . $object->blog_id . '_pprh_table';
				$ms_tables[] = $site_pp_table;
			}
		}
		return $ms_tables;
	}


	private function table_sql( $table_name ) {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id INT(9) NOT NULL AUTO_INCREMENT,
			url VARCHAR(255) DEFAULT '' NOT NULL,
			hint_type VARCHAR(55) DEFAULT '' NOT NULL,
			status VARCHAR(55) DEFAULT 'enabled' NOT NULL,
			as_attr VARCHAR(55) DEFAULT '',
			type_attr VARCHAR(55) DEFAULT '',
			crossorigin VARCHAR(55) DEFAULT '',
			post_id VARCHAR(55) DEFAULT '0' NOT NULL,
			created_by VARCHAR(55) DEFAULT '' NOT NULL,
			post_url VARCHAR(255) DEFAULT '' NOT NULL,
			PRIMARY KEY  (id)
		) $charset;";

		dbDelta( $sql, true );
	}


	// when a post is deleted, this will delete hints that have the same post ID which is being deleted.
	public function delete_post_hints( $post_id ) {
		global $wpdb;
		$post_id_str = (string) $post_id;
		$action = current_action();

		if ( 'delete_post' === $action ) {
			$wpdb->delete(
				PPRH_DB_TABLE,
				array(
					'post_id' => $post_id_str,
				),
				array( '%s' )
			);
			delete_post_meta( $post_id, 'pprh_reset_post_preconnects' );
		}
	}

    public function pprh_update_url( $post_id ) {
	    if ( isset( $_POST['pprh_link_changed'] ) && 'true' === $_POST['pprh_link_changed'] ) {
            global $wpdb;
            $new_link = Utils::get_url_query_from_post_id( $post_id );

            $wpdb->update(
                PPRH_DB_TABLE,
                array( 'post_url' => $new_link ),
                array( 'post_id' => (string) $post_id )
            );
        }
    }

}
