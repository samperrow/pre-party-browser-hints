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



		$lt = '<';
		$gt = '>';
		$quote = '"';

		foreach ( $result as $key => $value ) {

			$hint_url = esc_url( $value->url );
			$hint_type = sanitize_text_field( strtolower( $value->hint_type ) );
			$as_value = $header_crossorigin = $head_crossorigin = '';

			// if the supplied URL does not have HTTP or HTTPS given, add a '//' to not confuse the browser
			if ( ! preg_match( '/(http|https)/i', $hint_url ) ) {
				$hint_url = '//' . $hint_url;
			}

			if ( 'preload' === $hint_type ) {
				$file_type = strrchr( $hint_url, '.' );

				if ( $file_type === '.js' ) {
					$as_value = ' as="script"';
				} elseif ( $file_type === '.css' ) {
					$as_value = ' as="style"';
				} elseif ( $file_type === '.mp4' ) {
					$as_value = ' as="video"';
				} elseif ( $file_type === '.jpg' || $file_type === '.png' ) {
					$as_value = ' as="image"';
				}

			}

			if ( ( 'preconnect' === $hint_type ) && ( ( stristr( $hint_url, 'fonts.googleapis.com' ) === 'fonts.googleapis.com' ) || ( stristr( $hint_url, 'fonts.gstatic.com' ) === 'fonts.gstatic.com' ) ) ) {
				$header_crossorigin = ' crossorigin;';
				$head_crossorigin = ' crossorigin';
			}

			$this->header_str .=  $lt . $hint_url . $gt . ';' . ' rel=' . $hint_type . ';' . $header_crossorigin . $as_value . ', ';
			$this->head_str .= $lt . 'link rel="' . $hint_type . '"' . ' href="' . $hint_url . '"' . $head_crossorigin . $as_value . $gt;
		}

		return get_option( 'gktpp_send_in_header' ) === 'HTTP Header' ? $this->header_str : $this->head_str;
	}
}

function gktpp_send_hints() {
	$send_hints = new GKTPP_Send_Hints();
	return $send_hints->send_resource_hints();
}

if ( get_option( 'gktpp_send_in_header' ) === 'HTTP Header' && ! is_admin() ) {
	header( 'Link:' . gktpp_send_hints() );
} else {
	add_action( 'wp_head', function() {
		printf( gktpp_send_hints() );
	}, 1, 0 );
}
