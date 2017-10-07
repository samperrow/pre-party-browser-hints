<?php
/**
 * Plugin Name: Pre* Party Resource Hints
 * Plugin URI: https://www.linkedin.com/in/sam-perrow-53782b10b?trk=hp-identity-name
 * Description: Take advantage of W3C browser resource hints to improve page load time, automatically and manually.
 * Version: 1.3.3
 * Author: Sam Perrow
 * Author URI: https://www.linkedin.com/in/sam-perrow-53782b10b?trk=hp-identity-name
 * License: GPL2
 * last edited Oct 3, 2017
 *
 * Copyright 2017  Sam Perrow  (email : sam.perrow399@gmail.com)
 *
 *    This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program; if not, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GKT_PREP_PLUGIN', __FILE__ );
define( 'GKT_PREP_PLUGIN_DIR', untrailingslashit( dirname( GKT_PREP_PLUGIN ) ) );

require_once GKT_PREP_PLUGIN_DIR . '/class-gktpp-insert-to-db.php';
require_once GKT_PREP_PLUGIN_DIR . '/class-gktpp-table.php';
require_once GKT_PREP_PLUGIN_DIR . '/class-gktpp-options.php';
require_once GKT_PREP_PLUGIN_DIR . '/class-gktpp-enter-data.php';
require_once GKT_PREP_PLUGIN_DIR . '/class-gktpp-send-hints.php';
require_once GKT_PREP_PLUGIN_DIR . '/class-gktpp-ajax.php';


// register and call the CSS and JS we need only on the needed page
add_action( 'admin_menu', 'gktpp_register_admin_files' );
function gktpp_register_admin_files() {
	global $pagenow;

	if ( isset( $_GET['page'] ) && $_GET['page'] === 'gktpp-plugin-settings' ) {
		wp_register_script( 'gktpp_admin_js', plugin_dir_url( __FILE__ ) . 'js/admin.js', null, 1.1, false );
		wp_register_style( 'gktpp-styles-css', plugin_dir_url( __FILE__ ) . 'css/styles.css', null, 1.1, 'all' );

		wp_enqueue_script( 'gktpp_admin_js' );
		wp_enqueue_style( 'gktpp-styles-css' );
	}
}


register_activation_hook( __FILE__, 'gktpp_install_db_table' );
function gktpp_install_db_table() {
	if ( ! is_admin() ) {
		exit;
	}
     global $wpdb;

	update_option( 'gktpp_preconnect_status', 'Yes', '', 'yes' );
	update_option( 'gktpp_reset_preconnect', 'notset', '', 'yes' );
	update_option( 'gktpp_send_in_header', 'HTTP Header', '', 'yes' );
	update_option( 'gktpp_disable_wp_hints', 'No', '', 'yes' );

	$table = $wpdb->prefix . 'gktpp_table';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table (
	    id INT(9) NOT NULL AUTO_INCREMENT,
	    url VARCHAR(150) DEFAULT '' NOT NULL,
	    hint_type VARCHAR(55) DEFAULT '' NOT NULL,
	    status VARCHAR(55) DEFAULT 'Enabled' NOT NULL,
	    ajax_domain TINYINT(2) DEFAULT 0 NOT NULL,
	    PRIMARY KEY  (id)
    ) $charset_collate;";

    if ( ! function_exists( 'dbDelta' ) ) {
	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    }

    dbDelta( $sql, true );
}


add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'gktpp_set_admin_links' );
function gktpp_set_admin_links( $links ) {
   $gktpp_links = array(
	   '<a href="https://github.com/sarcastasaur/pre-party-browser-hints">View on GitHub</a>',
	   '<a href="https://www.paypal.me/samperrow">Donate</a>' );
   return array_merge( $links, $gktpp_links );
}


add_action( 'wp_head', 'gktpp_disable_wp_hints', 1, 0 );
function gktpp_disable_wp_hints() {
	$option = get_option( 'gktpp_disable_wp_hints' );

	if ( $option === 'Yes' ) {
		remove_action('wp_head', 'wp_resource_hints', 2);
	}
}

?>
