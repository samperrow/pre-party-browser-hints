<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Send_Hints {

	private $resourceHintElemStr = '';

	public function __construct() {
		add_action( 'wp_head', array( $this, 'send_resource_hints' ), 1, 0 );
	}

	public function send_resource_hints() {
		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_table';
		$links = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE status = %s", 'Enabled'), OBJECT );

		if ( count( $links ) < 1 || ( ! is_array( $links ) ) ) {
			return;
		}
		
		$destination = get_option( 'gktpp_send_in_header' );

		foreach ( $links as $key => $value ) {

			// $hint_url = esc_url( $value->url );
			// $hint_type = sanitize_text_field( strtolower($value->hint_type) );
			// $as_attr = $value->as_attr;
			// $as_attr = !empty( $value->as_attr ) ? "as=$value->as_attr," : ',';

			// $crossorigin = $value->crossorigin;
			// $header_crossorigin = '';

			// if ( !empty( $crossorigin ) ) {
			// 	$header_crossorigin = ' ' . $crossorigin . ';';
			// 	$crossorigin = ' ' . $crossorigin;
			// }



			$this->resourceHintElemStr .= ( $destination === 'HTTP Header' )
				// ? "<$hint_url>; rel=$hint_type; $header_crossorigin $as_attr "
				// : "<link rel='$hint_type' href='$hint_url' as='$as_attr'$crossorigin>";
				? $value->header_string
				: $value->head_string;
		}
		return $this->resourceHintElemStr;
	}
}

function gktpp_send_hints() {
	$send_hints = new GKTPP_Send_Hints();
	return $send_hints->send_resource_hints();
}

get_option( 'gktpp_send_in_header' ) === 'HTTP Header'
	? header( 'Link:' . gktpp_send_hints() ) 
	: add_action( 'wp_head', function() { printf( gktpp_send_hints() ); }, 1, 0 );

