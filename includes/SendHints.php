<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SendHints {

	protected $hints = array();

	protected $send_hints_in_html = '';

	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'get_resource_hints' ) );
	}

	public function get_resource_hints() {
		$this->send_hints_in_html = get_option( 'pprh_html_head' );

		$dao = new DAO();
		$query = array(
			'sql'  => " WHERE status = %s",
			'args' => array( 'enabled' )
		);

		$this->hints = $dao->get_hints( $query );

		if ( ( ! is_array( $this->hints ) ) || count( $this->hints ) < 1 ) {
			return;
		}

		( 'false' === $this->send_hints_in_html && ! headers_sent() )
			? add_action( 'send_headers', array( $this, 'send_in_http_header' ), 1, 0 )
			: add_action( 'wp_head', array( $this, 'send_to_html_head' ), 1, 0 );
	}

	public function send_to_html_head() {
		foreach ( $this->hints as $key => $val ) {
			$attrs = '';
			$attrs .= $this->add_attr( 'as', $val['as_attr'] );
			$attrs .= $this->add_attr( 'type', $val['type_attr'] );
			$attrs .= $this->add_attr( 'crossorigin', trim( $val['crossorigin'] ) );
			echo sprintf( '<link href="%s" rel="%s"%s>', $val['url'], $val['hint_type'], $attrs );
		}
	}

	public function send_in_http_header() {
		$output = '';

		foreach ( $this->hints as $key => $val ) {
			$attrs = '';
			$attrs .= $this->add_attr( 'as', $val['as_attr'] );
			$attrs .= $this->add_attr( 'type', $val['type_attr'] );
			$attrs .= $this->add_attr( 'crossorigin', trim( $val['crossorigin'] ) );
			$str = sprintf( '<%s>; rel=%s;%s', $val['url'], $val['hint_type'], $attrs );
			$str = rtrim( $str, ';' );
			$output .= $str . ', ';
		}

		$header_str = 'Link: ' . rtrim( $output, ';' );
		header( $header_str );
	}

	private function add_attr( $name, $val ) {
		if ( ! empty( $val ) ) {
			$attr = Utils::clean_hint_attr( $val );
			$attr = ( 'true' === $this->send_hints_in_html ) ? "\"$attr\"" : "$attr;";
			return ' ' . ( ( 'crossorigin' === $name ) ? $name : "$name=" . $attr );
		}
		return '';
	}

}
