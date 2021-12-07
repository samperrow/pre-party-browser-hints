<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

if ( function_exists( 'pprh_uninstall_plugin' ) ) {
	pprh_uninstall_plugin();
}

function pprh_uninstall_plugin() {
	global $wpdb;
	$pprh_table = $wpdb->prefix . 'pprh_table';
	$wpdb->query("DROP TABLE $pprh_table" );

	$option_names = array(
		'pprh_disable_wp_hints',
		'pprh_html_head',
		'pprh_prefetch_enabled',
		'pprh_prefetch_delay',
		'pprh_prefetch_ignoreKeywords',
		'pprh_prefetch_maxRPS',
		'pprh_prefetch_hoverDelay',
		'pprh_prefetch_max_prefetches',
		'pprh_prefetch_disableForLoggedInUsers',
		'pprh_preconnect_allow_unauth',
		'pprh_preconnect_autoload',
		'pprh_preconnect_set',
		'pprh_version'
	);

	foreach ($option_names as $option_name ) {
		\delete_option( $option_name );
	}
}
