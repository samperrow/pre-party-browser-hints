<?php

namespace PPRH;

//use PPRH\Utils\Utils;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ActivatePlugin {

	public function activate_plugin() {
		$this->add_options();
		$this->setup_tables();
		$this->plugin_activated = true;
	}

	private function add_options() {
		$default_prefetch_ignore_links = array( '/wp-admin', '/wp-login.php', '/cart', '/checkout', '/add-to-cart', '/logout', '#', '?', '.png', '.jpeg', '.jpg', '.gif', '.svg', '.webp' );

		// general settings
		\add_option( 'pprh_disable_wp_hints', 'true', '', 'yes' );
		\add_option( 'pprh_html_head', 'true', '', 'yes' );

		// prefetch
		\add_option( 'pprh_prefetch_disableForLoggedInUsers', 'true', '', 'yes' );
		\add_option( 'pprh_prefetch_enabled', 'false', '', 'yes' );
		\add_option( 'pprh_prefetch_delay', '0', '', 'yes' );
		\add_option( 'pprh_prefetch_ignoreKeywords', $default_prefetch_ignore_links, '', 'yes' );
		\add_option( 'pprh_prefetch_maxRPS', '3', '', 'yes' );
		\add_option( 'pprh_prefetch_hoverDelay', '50', '', 'yes' );
		\add_option( 'pprh_prefetch_max_prefetches', '10', '', 'yes' );

		// preconnects
		\add_option( 'pprh_preconnect_allow_unauth', 'false', '', 'yes' );
		\add_option( 'pprh_preconnect_autoload', 'true', '', 'yes' );
		\add_option( 'pprh_preconnect_set', 'false', '', 'yes' );
	}

	public function convert_prefetch_string_to_array( string $orig_keywords ) {
		return explode( ', ', $orig_keywords );
	}

	// Multisite install/delete db table.
	private function setup_tables() {
		$pprh_tables = \PPRH\DAO::get_all_db_tables( \is_multisite() );

		foreach ( $pprh_tables as $pprh_table ) {
			\PPRH\DAO::create_table( $pprh_table );
		}
	}

}
