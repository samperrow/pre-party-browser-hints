<?php

namespace PPRH;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ActivatePlugin {

	public $plugin_activated = false;

	public function activate_plugin() {
		$this->add_options();
		$this->setup_tables();
//		$this->drop_columns();
		$this->update_option_names();
		$this->plugin_activated = true;
	}

	public function upgrade_plugin() {
		$this->update_option_names();
		$this->plugin_activated = true;
	}

	private function add_options() {
		$default_prefetch_ignore_links = '/wp-admin, /wp-login.php, /cart, /checkout, add-to-cart, logout, #, ?, .png, .jpeg, .jpg, .gif, .svg, .webp';

		add_option( 'pprh_disable_wp_hints', 'true', '', 'yes' );
		add_option( 'pprh_html_head', 'true', '', 'yes' );
		add_option( 'pprh_prefetch_disableForLoggedInUsers', 'true', '', 'yes' );
		add_option( 'pprh_prefetch_enabled', 'false', '', 'yes' );
		add_option( 'pprh_prefetch_delay', '0', '', 'yes' );
		add_option( 'pprh_prefetch_ignoreKeywords', $default_prefetch_ignore_links, '', 'yes' );
		add_option( 'pprh_prefetch_maxRPS', '3', '', 'yes' );
		add_option( 'pprh_prefetch_hoverDelay', '50', '', 'yes' );
		add_option( 'pprh_prefetch_max_prefetches', '10', '', 'yes' );
	}

	private function update_option_names() {
		$preconnect_allow_unauth = get_option('pprh_allow_unauth');
		$preconnect_autoload = get_option( 'pprh_autoload_preconnects' );
		$preconnects_set = get_option( 'pprh_preconnects_set' );

		if ( false !== $preconnect_allow_unauth ) {
			add_option('pprh_preconnect_allow_unauth', $preconnect_allow_unauth, '', 'yes');
			delete_option('pprh_allow_unauth');
		} else {
			add_option('pprh_preconnect_allow_unauth', 'false', '', 'yes');
		}

		if ( false !== $preconnect_autoload ) {
			add_option('pprh_preconnect_autoload', $preconnect_autoload, '', 'yes');
			delete_option('pprh_autoload_preconnects');
		} else {
			add_option( 'pprh_preconnect_autoload', 'true', '', 'yes' );
		}

		if ( false !== $preconnects_set ) {
			add_option('pprh_preconnect_set', $preconnects_set, '', 'yes');
			delete_option('pprh_preconnects_set');
		} else {
			add_option( 'pprh_preconnect_set', 'false', '', 'yes' );
		}
	}

	public function upgrade_prefetch_keywords() {
		$keywords = get_option( 'pprh_prefetch_ignoreKeywords' );
		$updated_keywords = $this->reformat_prefetch_keywords( $keywords );
		$clean_keywords = Utils::clean_url( $updated_keywords );
		update_option( 'pprh_prefetch_ignoreKeywords', $clean_keywords );
	}

	// update previous prefetch ignoreKeywords option to new format.
	public function reformat_prefetch_keywords( $keywords ) {
		$words = preg_replace( '/[\]|\[|\"\s]/', '', $keywords );
		return preg_replace( '/,/', ', ', $words );
	}


	// Multisite install/delete db table.
	private function setup_tables() {
		$pprh_tables = array();
		$dao = new DAO();

		if ( is_multisite() ) {
			$pprh_tables = $dao->get_multisite_tables();
		}

		$pprh_tables[] = PPRH_DB_TABLE;

		foreach ( $pprh_tables as $pprh_table ) {
			$dao->create_table( $pprh_table );
		}
	}


}