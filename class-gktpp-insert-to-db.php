<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Insert_To_DB {

	public static function insert_data_to_db() {

		if ( ! is_admin() ) {
			exit;
		}

	    global $wpdb;

		$table = $wpdb->prefix . 'gktpp_table';
		$hint_type = isset( $_POST['hint_type'] ) ? stripslashes( $_POST['hint_type'] ) : '';
		$url = isset( $_POST['url'] ) ? self::validate_domain( $_POST['url'], $hint_type ) : '';

		$sql = "INSERT INTO $table ( id, url, hint_type, status, ajax_domain ) VALUES ( null, %s, %s, 'Enabled', 0 )";

		$wpdb->query( $wpdb->prepare( $sql, array( $url, $hint_type ) ) );
     }

	private static function validate_domain( $url, $hint_type ) {
		return ( $hint_type === 'DNS-Prefetch' || $hint_type === 'Preconnect' ) ? self::filter_for_domain_name( $url ) : esc_url( $url );
	}

	private static function filter_for_domain_name( $url ) {
		return ( preg_match( '/(http|https)/i', $url ) ) ? parse_url( $url, PHP_URL_SCHEME ) . '://' . parse_url( $url, PHP_URL_HOST ) : $url;
	}
}
