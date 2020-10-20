<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Verify_Hints {

	private $html_head;
	private $hints;

	public function __construct () {
//		do_action( 'pprh_load_verify_hints_child' );
		$this->html_head = get_option('pprh_html_head');
		$this->hints = $this->get_hints();
		$valid = $this->are_hints_valid();
		$this->notice( $valid );
	}

	public function get_hints() {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		$query = array(
			'sql' => "SELECT * FROM $table WHERE status = %s",
			'args' => array( 'enabled' ),
		);

		$query = apply_filters( 'pprh_vh_append_sql', $query );

		return $wpdb->get_results(
			$wpdb->prepare( $query['sql'], $query['args'] )
		);
	}

	public function are_hints_valid() {
		$hints = $this->hints;
		$response = $this->get_ajax_response();

		if ( ! empty( $response ) ) {
			return $this->sort_data( $response, $hints );
		}
	}

	private function notice( $valid ) {
		$msg = ( $valid ) ? 'Resource hints are being delivered to your website\'s front end properly.' : 'Resource hints are failing to appear on your front end. Please reset and clear your cache, and try again.';
		$t_msg = translate( $msg, 'pprh' );
		echo '<script>alert("' . $t_msg . '");</script>';
	}


	public function get_ajax_response() {

		$response = wp_remote_get(
			home_url(),
			array(
				'timeout'   => 20,
				'sslverify' => false
			)
		);

		if ( isset( $response['response'] ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			return $response;
		} else {
			return false;
		}
	}

	public function sort_data( $response, $hints ) {
		$hints_on_page = ( 'true' === $this->html_head ) ? wp_remote_retrieve_body( $response ) : wp_remote_retrieve_header( $response, 'link' );

		foreach ( $hints as $hint ) {
			$str = $this->create_str( $hint );

			if ( ! $this->is_hint_active( $hints_on_page, $str ) ) {
				return false;
			}
		}
		return true;
	}


	public function is_hint_active( $response, $str ) {
		return ( 'true' === $this->html_head ) ? $this->hints_in_head( $response, $str ) : $this->hints_in_header( $response, $str );
	}

	public function hints_in_head( $body, $str ) {
		return ( strpos( $body, $str ) > 0 );
	}

	public function hints_in_header( $links, $str ) {

		foreach ( $links as $link ) {
			if ( strpos( $link, $str ) >= 0 ) {
				return true;
			}
		}
		return false;
	}

	public function create_str( $hint ) {
		if ( 'true' === $this->html_head ) {
			$str = '<link href="' . $hint->url . '"' . ' rel="' . $hint->hint_type . '"';
		} else {
			$str = '<' . $hint->url . '>;' . " rel=$hint->hint_type";
		}
		return $str;
	}

}