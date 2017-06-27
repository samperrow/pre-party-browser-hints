<?php
/**
 * Plugin Name: Pre * Party Resource Hints
 * Plugin URI: https://www.linkedin.com/in/sam-perrow-53782b10b?trk=hp-identity-name
 * Description: Take advantage of W3C browser resource hints to improve page load time, automatically and manually.
 * Version: 1.0.7
 * Author: Sam Perrow
 * Author URI: https://www.linkedin.com/in/sam-perrow-53782b10b?trk=hp-identity-name
 * License: GPL2
 * last edited June 11, 2017
 *
 * Copyright 2016  Sam Perrow  (email : sam.perrow399@gmail.com)
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

require_once GKT_PREP_PLUGIN_DIR . '/options.php';
require_once GKT_PREP_PLUGIN_DIR . '/GKTPP_Talk_To_DB.php';
require_once GKT_PREP_PLUGIN_DIR . '/GKTPP_Table.php';
require_once GKT_PREP_PLUGIN_DIR . '/GKTPP_Enter_Data.php';
require_once GKT_PREP_PLUGIN_DIR . '/class-GKTPP_Send_Entered_Hints.php';
require_once GKT_PREP_PLUGIN_DIR . '/class-GKTPP_Ajax.php';

// register and call the CSS and JS we need for the dropboxes
add_action( 'admin_menu', 'gktpp_reg_admin_stuff' );

function gktpp_reg_admin_stuff() {
	wp_register_script( 'gktpp_admin_js', plugin_dir_url( __FILE__ ) . 'js/admin.js' );
	wp_register_style( 'gktpp-styles-css', plugin_dir_url( __FILE__ ) . 'css/styles.css' );

	wp_enqueue_script( 'gktpp_admin_js' );
	wp_enqueue_style( 'gktpp-styles-css' );
}

register_activation_hook( __FILE__, array( 'GKTPP_Talk_To_DB', 'install_db_table' ) );
// register_activation_hook( __FILE__, array( 'GKTPP_Talk_To_DB', 'create_ajax_table' ) );
