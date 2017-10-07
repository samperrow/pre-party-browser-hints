<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Send_Hints {

	private $header_str = '';
	private $head_str = '';

	public function __construct() {
		add_action( 'wp_head', array( $this, 'send_resource_hints' ), 1, 0 );
	}

	public function send_resource_hints() {
		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_table';

		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE status = %s", 'Enabled'), OBJECT );

		if ( count( $result ) < 1 || ( ! is_array( $result ) ) ) {
			return;
		}

		$crossorigin = '';

		$lt = '<';
		$gt = '>';
		$quote = '"';

		foreach ( $result as $key => $value ) {

			$hint_url = $value->url;
			$hint_type = strtolower( $value->hint_type );
			$as_value = '';

			// if the supplied URL does not have HTTP or HTTPS given, add a '//' to not confuse the browser
			if ( ! preg_match( '/(http|https)/i', $hint_url ) ) {
				$hint_url = '//' . $hint_url;
			}

			if ( 'preload' === $hint_type ) {

				if ( ! empty( strrpos( $hint_url, '.js', -3 ) ) ) {
					$as_value = ' as="script"';
				} elseif ( ! empty( strrpos( $hint_url, '.css', -4 ) ) ) {
					$as_value = ' as="style"';
				} elseif ( ! empty( strrpos( $hint_url, '.mp4', -4 ) ) ) {
					$as_value = ' as="video" type="video/mp4"';
				} elseif ( ! empty( strrpos( $hint_url, '.mp4', -4 ) === '.jpg' || strrpos( $hint_url, '.mp4', -4 ) === '.png' ) ) {
					$as_value = ' as="image" type="image/jpeg"';
				}

			}

			$crossorigin = ( ( 'preconnect' === $hint_type ) && ( 'https://fonts.googleapis.com' === $hint_url || 'https://fonts.gstatic.com' === $hint_url ) ) ? ' crossorigin' : '';
			$this->header_str .=  $lt . $hint_url . $gt . ';' . ' rel=' . $hint_type . ';' . $crossorigin . ';' . $as_value . ',';
			$this->head_str .= $lt . 'link rel="' . $hint_type . '"' . ' href="' . $hint_url . '"' . $crossorigin . $as_value . $gt;
		}

		return get_option( 'gktpp_send_in_header' ) === 'HTTP Header' ? $this->header_str : $this->head_str;
	}
}

function gktpp_send_hints() {
	$send_hints = new GKTPP_Send_Hints();
	return $send_hints->send_resource_hints();
}

if ( get_option( 'gktpp_send_in_header' ) === 'HTTP Header' ) {
	header( 'Link:' . gktpp_send_hints() );
} else {
	add_action( 'wp_head', function() {
		printf( gktpp_send_hints() );
	}, 1, 0 );
}
