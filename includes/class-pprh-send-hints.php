<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PPRH_Send_Hints {

	public $hints = array();

	public function __construct() {
		add_action( 'parse_query', array( $this, 'get_resource_hints' ) );
		add_action( 'wp_head', array( $this, 'send_to_html_head' ), 1, 0 );
	}

	public function get_resource_hints() {
		global $wpdb;

		$post_ID = ( is_home() ) ? '0' : (string) get_queried_object_id();
		$table   = $wpdb->prefix . 'pprh_table';
		$this->hints   = $wpdb->get_results(
			$wpdb->prepare( "SELECT url, hint_type, as_attr, type_attr, crossorigin FROM $table WHERE post_id = %s OR post_id = %s AND status = %s", $post_ID, 'global', 'enabled' )
		);

	}

	// need to sanitize by removing anything other than link elems.
	public function send_to_html_head() {
		if ( count( $this->hints ) < 1 || ( ! is_array( $this->hints ) ) ) {
			return;
		}

		foreach ( $this->hints as $key => $val ) {
			$str = '';

			if ( ! empty( $val->as_attr ) ) {
				$str .= " as=\"$val->as_attr\"";
			}

			if ( ! empty( $val->type_attr ) ) {
				$str .= " type=\"$val->type_attr\"";
			}

			if ( ! empty( $val->crossorigin ) ) {
				$str .= ' crossorigin';
			}
			echo sprintf( '<link href="%s" rel="%s"%s>', $val->url, $val->hint_type, $str );
		}
	}

}
