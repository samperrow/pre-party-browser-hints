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
    $sites = [ $table ];

    if ( is_multisite() ) {
        $blogTable = $wpdb->base_prefix . 'blogs';
        $data = $wpdb->get_results("SELECT blog_id FROM $blogTable WHERE blog_id != 1;");

        if ($data) {
            foreach ($data as $object) {
                $sitePpTable = $wpdb->base_prefix . $object->blog_id . '_gktpp_table';
                array_push( $sites, $sitePpTable );
            }
        }
    } 
   
    foreach ( $sites as $site ) {
        $sql = "DROP TABLE IF EXISTS $site";
        $wpdb->query( $sql, null );
    }

}
gktpp_uninstall_plugin();
