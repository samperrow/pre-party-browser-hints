<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

new Verify_Hints();

class Verify_Hints {

	private $html_head;
	private $hints;
	private $home_url;
	private $count;

	public function __construct () {
		$this->html_head = get_option('pprh_html_head');
		$this->home_url = home_url();
		$this->hints = $this->get_hints();
//		$this->count = 0;
		$this->init( $this->count );
	}

	public function get_hints() {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		return $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM $table WHERE status = %s", 'enabled' )
		);
	}

	public function init() {
		$hints = $this->hints;

		$this->do_ajax( $hints );
	}

	public function do_ajax( $hints ) {

		$response = wp_remote_get(
			$this->home_url,
			array(
				'timeout'   => 20,
				'sslverify' => false
			)
		);

		if ( isset( $response['response'] ) ) {
			$this->sort_data( $response, $hints );
		}
//		return $obj;
	}

	public function sort_data( $response, $hint ) {
		if ( 'true' === $this->html_head ) {
			$valid = $this->hints_in_head( $response, $hint );
		} else {
			$valid = $this->hints_in_header( $response, $hint );
		}
		return $valid;
	}

	public function hints_in_head( $response, $hint ) {
		$body = wp_remote_retrieve_body( $response );
		$str = '<link href="' . $hint->url . '"' . ' rel="' . $hint->hint_type . '"';

		return ( strpos( $body, $str ) > 0 );
	}

	public function hints_in_header( $response, $hint ) {
		$links = wp_remote_retrieve_header( $response, 'link' );

		foreach ( $links as $link ) {
			$str = '<' . $hint->url . '>;' . " rel=$hint->hint_type";
			if ( strpos( $link, $str ) >= 0 ) {
				return true;
			}
		}
		return false;
	}


}