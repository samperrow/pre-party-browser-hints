<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

pprh_uninstall_plugin();

function pprh_uninstall_plugin() {
	delete_option( 'pprh_allow_unauth' );
	delete_option( 'pprh_autoload_preconnects' );
	delete_option( 'pprh_disable_wp_hints' );
	delete_option( 'pprh_html_head' );
	delete_option( 'pprh_preconnects_set' );
}
