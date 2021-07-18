<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

if ( function_exists( 'pprh_uninstall_plugin' ) ) {
	pprh_uninstall_plugin();
}

function pprh_uninstall_plugin() {
	\delete_option( 'pprh_disable_wp_hints' );
	\delete_option( 'pprh_html_head' );

	\delete_option( 'pprh_prefetch_enabled' );
	\delete_option( 'pprh_prefetch_delay' );
	\delete_option( 'pprh_prefetch_ignoreKeywords' );
	\delete_option( 'pprh_prefetch_maxRPS' );
	\delete_option( 'pprh_prefetch_hoverDelay' );
	\delete_option( 'pprh_prefetch_max_prefetches' );
	\delete_option( 'pprh_prefetch_disableForLoggedInUsers' );

	\delete_option( 'pprh_preconnect_allow_unauth' );
	\delete_option( 'pprh_preconnect_autoload' );
	\delete_option( 'pprh_preconnect_set' );

	\delete_option( 'pprh_version' );
}
