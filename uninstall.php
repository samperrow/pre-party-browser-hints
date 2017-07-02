<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
     die();
}

function gktpp_uninstall_plugin() {
     global $wpdb;

     delete_option( 'gktpp_preconnect_status' );
     delete_option( 'gktpp_reset_preconnect' );
     delete_option( 'gktpp_send_in_header' );
     delete_user_meta( get_current_user_id(), 'gktpp_screen_options' );

     $table1 = $wpdb->prefix . 'gktpp_table';

     $sql = $wpdb->prepare( 'DROP TABLE IF EXISTS %1s', $table1 );

     $wpdb->query( $sql );
}
gktpp_uninstall_plugin();
