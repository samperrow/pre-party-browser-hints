<?php

namespace PPRH;

use PPRH\Utils\Sanitize;
use PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SettingsSave {

	public function save_user_options() {
		if ( isset( $_POST['pprh_save_options'] ) || isset( $_POST['pprh_preconnect_set'] ) ) {
			\check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
			$this->save_options( $_POST );
		}
	}

	private function save_options( array $post ) {
		$results = array();

		// GENERAL
		$this->save_general_settings( $post );

		// PRECONNECT
		$this->save_preconnect_settings( $post );

		// PREFETCH
		$this->save_prefetch_settings( $post );

		return $results;
	}

	public function save_general_settings( array $post ) {
		$results = array();

		$results[] = $this->update_checkbox_option( $post, 'pprh_disable_wp_hints' );

		$html_head = Sanitize::strip_non_alphanums( $post[ 'pprh_html_head' ] ?? 'false' );
		Utils::update_option( 'pprh_html_head', $html_head );
		$results[] = $html_head;

		return $results;
	}

	public function save_preconnect_settings( array $post ) {
		$results = array();

		$results[] = $this->update_checkbox_option( $post, 'pprh_preconnect_autoload' );
		$results[] = $this->update_checkbox_option( $post, 'pprh_preconnect_allow_unauth' );

		if ( isset( $post['pprh_preconnect_set'] ) && 'Reset' === $post['pprh_preconnect_set'] ) {
			Utils::update_option( 'pprh_preconnect_set', 'false' );
			$results[] = DAO::delete_auto_created_hints( 'preconnect', '' );
			\add_action( 'pprh_notice', array( $this, 'auto_preconnect_notice' ) );
		}

		return $results;
	}

	public function auto_preconnect_notice() {
		$msg = esc_html( 'Your automatically generated preconnect hints have been removed. Please reload a front-end page to generate new preconnect hints.', 'pre-party-browser-hints' );
		Utils::show_notice( $msg, true );
	}

	public function save_prefetch_settings( array $post ) {
		$results = array();

		$results[] = $this->update_checkbox_option( $post, 'pprh_prefetch_enabled' );
		$results[] = $this->update_checkbox_option( $post, 'pprh_prefetch_disableForLoggedInUsers' );

		if ( isset( $post['pprh_prefetch_delay'] ) ) {
			$prefetch_delay = Sanitize::strip_non_numbers( $post['pprh_prefetch_delay'] );
			Utils::update_option( 'pprh_prefetch_delay', $prefetch_delay );
			$results[] = $prefetch_delay;
		}

		if ( isset( $post[ 'pprh_prefetch_ignoreKeywords' ] ) ) {
			$ignore_keywords = $this->turn_textarea_to_array( $post['pprh_prefetch_ignoreKeywords'] );
			Utils::update_option( 'pprh_prefetch_ignoreKeywords', $ignore_keywords );
			$results[] = $ignore_keywords;
		}

		if ( isset( $post['pprh_prefetch_maxRPS'] ) ) {
			$max_rps = Sanitize::strip_non_numbers( $post['pprh_prefetch_maxRPS'] );
			Utils::update_option( 'pprh_prefetch_maxRPS', $max_rps );
			$results[] = $max_rps;
		}

		if ( isset( $post['pprh_prefetch_hoverDelay'] ) ) {
			$hover_delay = Sanitize::strip_non_numbers( $post['pprh_prefetch_hoverDelay'] );
			Utils::update_option( 'pprh_prefetch_hoverDelay', $hover_delay );
			$results[] = $hover_delay;
		}

		if ( isset( $post['pprh_prefetch_max_prefetches'] ) ) {
			$max_prefetches = Sanitize::strip_non_numbers( $post['pprh_prefetch_max_prefetches'] );
			Utils::update_option( 'pprh_prefetch_max_prefetches', $max_prefetches );
			$results[] = $max_prefetches;
		}

		return $results;
	}

	public function does_option_match( string $option, string $match, string $output ) {
		$option_value = $this->esc_get_option( $option );
		return ( ( $option_value === $match ) ? $output : '' );
	}

	public function update_checkbox_option( array $post, string $option_name ):string {
		$update_val = $post[ $option_name ] ?? 'false';
		\PPRH\Utils\Utils::update_option( $option_name, $update_val );
		return $update_val;
	}

	public function turn_textarea_to_array( $text ) {
		$clean_text = preg_replace( '/[\'<>^\"\\\]/', '', $text );
		return explode( "\r\n", $clean_text );
	}

	protected function esc_get_option( string $option ) {
		return \esc_html( \get_option( $option ) );
	}

}
