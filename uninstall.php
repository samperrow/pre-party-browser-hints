<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

pprh_uninstall_plugin();

function pprh_uninstall_plugin() {
//	global $wpdb;

	delete_option( 'pprh_autoload_preconnects' );
	delete_option( 'pprh_allow_unauth' );
	delete_option( 'pprh_disable_wp_hints' );
	delete_option( 'pprh_html_head' );
	delete_option( 'pprh_preconnects_set' );

//	$pprh_table      = $wpdb->prefix . 'pprh_table';
//	$pprh_tables     = array( $pprh_table );
//
//	if ( is_multisite() ) {
//		$blog_table = $wpdb->base_prefix . 'blogs';
//		$data = $wpdb->get_results(
//			$wpdb->prepare( "SELECT blog_id FROM $blog_table WHERE blog_id != %d", 1 )
//		);
//
//		if ( $data ) {
//			foreach ( $data as $object ) {
//				$multisite_table = $wpdb->base_prefix . $object->blog_id . '_pprh_table';
//				$pprh_tables[] = $multisite_table;
//			}
//		}
//	}
//
//	foreach ( $pprh_tables as $table ) {
//		$wpdb->query( "DROP TABLE IF EXISTS $table" );
//	}

}
