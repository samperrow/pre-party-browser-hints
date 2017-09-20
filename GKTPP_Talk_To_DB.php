<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Talk_To_DB {

	public static function insert_data_to_db() {

		if ( ! is_admin() ) {
			exit;
		}

	    global $wpdb;

		$table = $wpdb->prefix . 'gktpp_table';
		$url = isset( $_POST['url'] ) ? self::validate_DNS_prefetch_url() : '';
		$hint_type = isset( $_POST['hint_type'] ) ? stripslashes( $_POST['hint_type'] ) : '';

		$sql = $wpdb->insert( $table,
						  array(
							  'url' => $url,
							  'hint_type' => $hint_type ),
					  	  array( '%s', '%s' ) );
		$wpdb->query( $sql );
     }

	private static function validate_DNS_prefetch_url() {

		if ( ! isset( $_POST['url'] ) ) {
			return;
		}

		$filteredURL = esc_url( $_POST['url'] );

		switch ( $_POST['hint_type'] ) {

			case 'DNS-Prefetch':
				return self::filter_scheme_and_domain_name();

			case 'Prefetch':
				return $filteredURL;

			case 'Prerender':
				return $filteredURL;

			case 'Preconnect':
				return self::filter_scheme_and_domain_name();

			case 'Preload':
				return $filteredURL;

			default:
				return $filteredURL;
		}

	}

	private static function filter_scheme_and_domain_name() {

		$postURL = esc_url( $_POST['url'] );

		if ( isset( $postURL ) ) {
			$firstSlashIndex = strpos( $postURL, "/" );
			$urlWithoutFirstSlash = substr( $postURL, 0, $firstSlashIndex );

			if ( preg_match( '/(http|https)/i', $postURL ) ) {
				return parse_url( $postURL, PHP_URL_SCHEME ) . '://' . parse_url( $postURL, PHP_URL_HOST );
			}

			elseif ( preg_match( '#/#', $postURL ) ) {
				return $urlWithoutFirstSlash;
			}

			else {
				return $postURL;
			}
		}
	}
}
