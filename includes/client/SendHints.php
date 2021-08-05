<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SendHints {

	public $hints = array();
	public $hint_str = '';
	public $send_hints_in_html = '';

	public function __construct() {
		$this->send_hints_in_html = ( 'true' === \get_option( 'pprh_html_head', 'true' ) );
	}

	public function init_ctrl( array $data ) {
		$enabled_hints = DAO::get_pprh_hints( false, $data );
		$headers_sent = \headers_sent();
		$send_in_http_header = $this->add_action_ctrl( $this->send_hints_in_html, $headers_sent );
		$this->init( $enabled_hints, $send_in_http_header );
	}

	public function init( $enabled_hints, $send_in_http_header ) {
		return $this->init_private( $enabled_hints, $send_in_http_header );
	}

	private function init_private( $enabled_hints, $send_in_http_header ) {
		if ( ! Utils::isArrayAndNotEmpty( $enabled_hints ) ) {
			return false;
		}

		if ( $send_in_http_header ) {
			$this->hint_str = $this->send_in_http_header( $enabled_hints );
			\add_action( 'send_headers', array( $this, 'send_in_header' ), 1, 0 );
		} else {
			$this->hint_str = $this->send_to_html_head( $enabled_hints );
			\add_action( 'wp_head', array( $this, 'send_html_head' ), 1, 0 );
		}

		return true;
	}


	public function add_action_ctrl( $hints_in_html, $headers_sent ) {
		return ( ! $hints_in_html && ! $headers_sent );
	}

	public function send_in_header() {
		header( $this->hint_str );
	}

	public function send_html_head() {
		echo $this->hint_str;
	}

	public function send_to_html_head( $hints ) {
		$str = '';

		foreach ( $hints as $key => $val ) {
			$attrs = $this->get_attrs( $val );
			$str .= sprintf( '<link href="%1$s" rel="%2$s"%3$s>', $val['url'], $val['hint_type'], $attrs );
		}

		return $str;
	}

	public function send_in_http_header( $hints ) {
		$output = '';

		foreach ( $hints as $key => $val ) {
			$attrs = $this->get_attrs( $val );
			$str = sprintf( '<%s>; rel=%s;%s', $val['url'], $val['hint_type'], $attrs );
			$str = rtrim( $str, ';' );
			$output .= $str . ', ';
		}

		$output = rtrim( $output, ';, ' );
		return 'Link: ' . $output;
	}

	public function get_attrs($hint) {
		$attrs = '';

		if ( ! empty( $hint['as_attr'] ) ) {
			$attr = Utils::clean_hint_attr( $hint['as_attr'] );
			$attrs .= $this->add_attr( 'as', $attr );
		}

		if ( ! empty( $hint['type_attr'] ) ) {
			$attr = Utils::clean_hint_attr( $hint['type_attr'] );
			$attrs .= $this->add_attr( 'type', $attr );
		}

		if ( ! empty( $hint['media'] ) ) {
			$attr = Utils::clean_url( $hint['media'] );
			$attrs .= $this->add_attr( 'media', $attr );
		}

		if ( ! empty( $hint['crossorigin'] ) ) {
			$attr = Utils::clean_hint_attr( $hint['crossorigin'] );
			$attrs .= $this->add_attr( 'crossorigin', $attr );
		}

		return $attrs;
	}


	private function add_attr( $name, $attr_value ) {
		if ( 'crossorigin' === $name ) {
			$str = ' ' . $name;
		} else {
			$attr = ( $this->send_hints_in_html ) ? "\"$attr_value\"" : "$attr_value;";
			$str = ' ' .  "$name=" . $attr;
		}

		return $str;
	}

}
