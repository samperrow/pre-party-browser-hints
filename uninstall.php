<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
     die();
}

function gktpp_uninstall_plugin() {
     global $wpdb;

     delete_user_meta( get_current_user_id(), 'gktpp_page_count' );

     $table1 = $wpdb->prefix . 'gktpp_table';
     $table2 = $wpdb->prefix . 'gktpp_ajax_domains';

     $sql = $wpdb->prepare( 'DROP TABLE IF EXISTS %1s, %2s', $table1, $table2 );

     $wpdb->query( $sql );
}
gktpp_uninstall_plugin();
