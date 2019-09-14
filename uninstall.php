<?php
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}


pprh_uninstall_plugin();

function pprh_uninstall_plugin() {
	global $wpdb;

	delete_option( 'pprh_autoload_preconnects' );
	delete_option( 'pprh_reset_home_preconnect' );
	delete_option( 'pprh_reset_global_preconnects' );
	delete_option( 'pprh_disable_wp_hints' );
	delete_option( 'pprh_allow_unauth' );

	$pprh_table      = $wpdb->prefix . 'pprh_table';
	$post_meta_table = $wpdb->prefix . 'postmeta';
	$pprh_tables     = array( $pprh_table );

	$wpdb->query(
		$wpdb->prepare( "DELETE FROM $post_meta_table WHERE meta_key = %s", 'pprh_reset_preconnects' )
	);

	if ( is_multisite() ) {
		$blog_table = $wpdb->base_prefix . 'blogs';
		$data = $wpdb->get_results(
			$wpdb->prepare( "SELECT blog_id FROM $blog_table WHERE blog_id != %d", 1 )
		);

		if ( $data ) {
			foreach ( $data as $object ) {
				$multisite_table = $wpdb->base_prefix . $object->blog_id . '_pprh_table';
				array_push( $pprh_tables, $multisite_table );
			}
		}
	}

	foreach ( $pprh_tables as $pprh_table ) {
		$wpdb->query( "DROP TABLE IF EXISTS $pprh_table" );
	}

}
