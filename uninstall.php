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

     $table = $wpdb->prefix . 'gktpp_table';
     $sql = "DROP TABLE IF EXISTS $table";

     $wpdb->query( $wpdb->prepare( $sql, null ) );
}
gktpp_uninstall_plugin();
