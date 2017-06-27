<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Send_Entered_Hints {

	private $str = '';

	public function __construct() {
		add_action( 'wp_head', array( $this, 'send_resource_hints' ), 1, 0 );
	}

	public function send_resource_hints() {

		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_table';

		$sql = $wpdb->prepare( 'SELECT * FROM %1s', $table );
		$result = $wpdb->get_results( $sql, ARRAY_A, 0 );

		if ( count( $result ) < 1 || ( ! is_array( $result ) ) ) {
			return;
		}

		if ( get_option( 'gktpp_header_option' ) === 'Send in head' ) {

			foreach ( $result as $key => $value ) {
				if ( ( 'Enabled' === $result[ $key ]['status'] ) ) {

					$hint_url = $result[ $key ]['url'];
					$hint_type = strtolower( $result[ $key ]['hint_type'] );

					// if the supplied URL does not have HTTP or HTTPS given, add a '//' to not confuse the browser
					if ( ! preg_match( '/(http|https)/i', $hint_url ) ) {
						$hint_url = '//' . $hint_url;
					}

					$crossorigin = ( ( 'preconnect' === $hint_type ) && ( 'https://fonts.googleapis.com' === $hint_url ) ) ? ' crossorigin' : '';

					printf( "<link href='$hint_url' rel='$hint_type'$crossorigin>", $hint_type, $hint_url, $crossorigin );

				}
			}
		} else {
			$lt = '<';
			$gt = '>';

			foreach ( $result as $key => $value ) {
				if ( ( 'Enabled' === $result[ $key ]['status'] ) ) {

					$hint_url = $result[ $key ]['url'];
					$hint_type = strtolower( $result[ $key ]['hint_type'] );

					if ( ! preg_match( '/(http|https)/i', $hint_url ) ) {
						$hint_url = '//' . $hint_url;
					}

					$this->str .=  $lt . $hint_url . $gt . ';' . ' rel="' . $hint_type . '",';
				}
			}
			return rtrim( $this->str, ',');
		}


	}
}
$send_hints = new GKTPP_Send_Entered_Hints();
if ( get_option( 'gktpp_header_option' ) === 'HTTP Header' ) {
	header('Link:' . $send_hints->send_resource_hints() );
}
