<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SendHints {

	public $hints = array();

	public $hint_str = '';

	protected $send_hints_in_html = '';

	public function init( $all_hints ) {
		$hints = $this->filter_hints( $all_hints );
		$this->send_hints_in_html = get_option( 'pprh_html_head' );

		if ( ! is_array( $hints ) || count( $hints ) === 0 ) {
			return false;
		}

		if ( 'false' === $this->send_hints_in_html && ! headers_sent() ) {
			$this->hint_str = $this->send_in_http_header( $hints );
			add_action( 'send_headers', array( $this, 'send_header' ), 1, 0 );
		} else {
			$this->hint_str = $this->send_to_html_head( $hints );
			add_action( 'wp_head', array( $this, 'send_html_head' ), 1, 0 );
		}

		return true;
	}

	public function send_header() {
		header( $this->hint_str );
	}

	public function send_html_head() {
		echo $this->hint_str;
	}

	public function filter_hints( $all_hints ) {
		$hints = array();

		foreach( $all_hints as $hint ) {
			if ( ! empty( $hint['status'] ) && 'enabled' === $hint['status'] ) {
				$hints[] = $hint;
			}
		}

		return $hints;
	}

	public function send_to_html_head( $hints ) {
		$str = '';

		foreach ( $hints as $key => $val ) {
			$attrs = $this->get_attrs( $val );

			$str .= sprintf( '<link href="%s" rel="%s"%s>', $val['url'], $val['hint_type'], $attrs );
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
		$header_str = 'Link: ' . $output;
		return $header_str;
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
			return ' ' . $name;
		} else {
			$attr = ( 'true' === $this->send_hints_in_html ) ? "\"$attr_value\"" : "$attr_value;";
			return ' ' .  "$name=" . $attr;
		}
	}

}
