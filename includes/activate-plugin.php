<?php

namespace PPRH;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Activate_Plugin {

	public function __construct() {
		$this->add_options();
		$this->update_option_names();

		if ( 'activate_pre-party-browser-hints/pre-party-browser-hints.php' === current_action() ) {
			$this->setup_tables();
		}
	}

	private function add_options() {
		add_option( 'pprh_disable_wp_hints', 'true', '', 'yes' );
		add_option( 'pprh_html_head', 'true', '', 'yes' );
		add_option( 'pprh_prefetch_enabled', 'false', '', 'yes' );
		add_option( 'pprh_prefetch_delay', '0', '', 'yes' );
		add_option( 'pprh_prefetch_ignoreKeywords', '', '', 'yes' );
		add_option( 'pprh_prefetch_maxRPS', '3', '', 'yes' );
		add_option( 'pprh_prefetch_hoverDelay', '50', '', 'yes' );
	}

	private function update_option_names() {
		$preconnect_allow_unauth = get_option('pprh_allow_unauth');
		$preconnect_autoload = get_option( 'pprh_autoload_preconnects' );
		$preconnects_set = get_option( 'pprh_preconnects_set' );

		if ( ! empty( $preconnect_allow_unauth ) ) {
			add_option('pprh_preconnect_allow_unauth', $preconnect_allow_unauth, '', 'yes');
			delete_option('pprh_allow_unauth');
		} else {
			add_option('pprh_preconnect_allow_unauth', 'true', '', 'yes');
		}

		if ( ! empty( $preconnect_autoload ) ) {
			add_option('pprh_preconnect_autoload', $preconnect_autoload, '', 'yes');
			delete_option('pprh_autoload_preconnects');
		} else {
			add_option( 'pprh_preconnect_autoload', 'true', '', 'yes' );
		}

		if ( ! empty( $preconnects_set ) ) {
			add_option('pprh_preconnect_set', $preconnects_set, '', 'yes');
			delete_option('pprh_preconnects_set');
		} else {
			add_option( 'pprh_preconnect_set', 'false', '', 'yes' );
		}
	}

	// Multisite install/delete db table.
	public function setup_tables() {
		$pprh_tables = array();

		if ( is_multisite() ) {
			$dao = new DAO();
			$pprh_tables = $dao->get_multisite_tables();
		}

		$pprh_tables[] = PPRH_DB_TABLE;
		$dao = new DAO();

		foreach ( $pprh_tables as $pprh_table ) {
			$dao->create_table( $pprh_table );
		}
	}

}