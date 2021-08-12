<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SettingsSave extends Settings {

	public function save_settings() {
		if ( isset( $_POST ) && \PPRH\Utils::isArrayAndNotEmpty( $_POST ) ) {
			\check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
			$this->save_options( $_POST );
		}
	}

	public function save_options( array $post ) {
		$results = array();

		// GENERAL
		$results[] = Utils::update_checkbox_option1( $post, 'pprh_disable_wp_hints' );
		$html_head = Utils::strip_non_alphanums( $post[ 'pprh_html_head' ] ?? 'false' );
		$results[] = Utils::update_option( 'pprh_html_head', $html_head );

		// PRECONNECT
		$results[] = Utils::update_checkbox_option1( $post, 'pprh_preconnect_autoload' );
		$results[] = Utils::update_checkbox_option1( $post, 'pprh_disable_wp_hints' );

		if ( isset( $_POST[ 'pprh_preconnect_set' ] ) && 'Reset' === $_POST[ 'pprh_preconnect_set' ] ) {
			$results[] = DAO::delete_auto_preconnects( '' );
			\add_action( 'pprh_notice', array( $this, 'auto_preconnect_notice' ) );
		}

		// PREFETCH
		$this->save_prefetch_settings( $post );

		return $results;
	}

	public function auto_preconnect_notice() {
		$msg = 'Your automatically generated preconnect hints have been removed. Please reload a front-end page to generate new preconnect hints.';
		\PPRH\Utils::show_notice( $msg, true );
	}

	public function save_prefetch_settings( array $post ) {
		$results = array();

		$results[] = Utils::update_checkbox_option1( $post, 'pprh_prefetch_enabled' );
		$results[] = Utils::update_checkbox_option1( $post, 'pprh_prefetch_disableForLoggedInUsers' );

		if ( isset( $post['pprh_prefetch_delay'] ) ) {
			$prefetch_delay = Utils::strip_non_numbers( $post['pprh_prefetch_delay'] );
			Utils::update_option( 'pprh_prefetch_delay', $prefetch_delay );
			$results[] = $prefetch_delay;
		}

		if ( isset( $_POST[ 'pprh_prefetch_ignoreKeywords' ] ) ) {
			$ignore_keywords = $this->turn_textarea_to_array( $_POST['pprh_prefetch_ignoreKeywords'] );
			Utils::update_option( 'pprh_prefetch_ignoreKeywords', $ignore_keywords );
			$results[] = $ignore_keywords;
		}

		if ( isset( $post['pprh_prefetch_maxRPS'] ) ) {
			$max_rps = Utils::strip_non_numbers( $post['pprh_prefetch_maxRPS'] );
			Utils::update_option( 'pprh_prefetch_maxRPS', $max_rps );
			$results[] = $max_rps;
		}

		if ( isset( $post['pprh_prefetch_hoverDelay'] ) ) {
			$hover_delay = Utils::strip_non_numbers( $post['pprh_prefetch_hoverDelay'] );
			Utils::update_option( 'pprh_prefetch_hoverDelay', $hover_delay );
			$results[] = $hover_delay;
		}

		if ( isset( $post['pprh_prefetch_max_prefetches'] ) ) {
			$max_prefetches = Utils::strip_non_numbers( $post['pprh_prefetch_max_prefetches'] );
			Utils::update_option( 'pprh_prefetch_max_prefetches', $max_prefetches );
			$results[] = $max_prefetches;
		}

		return $results;
	}

}
