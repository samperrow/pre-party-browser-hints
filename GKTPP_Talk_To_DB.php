<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Talk_To_DB {

	public static function install_db_table() {
	    global $wpdb;

		$table = $wpdb->prefix . 'gktpp_table';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table (
              id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
              url text NOT NULL,
              hint_type VARCHAR(55) NOT NULL,
              status VARCHAR(55) NOT NULL DEFAULT 'Enabled',
		    ajax_domain TINYINT(1) NOT NULL DEFAULT 0,
              UNIQUE KEY id (`id`)
	    ) $charset_collate";

	    if ( ! function_exists( 'dbDelta' ) ) {
		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	    }

	    dbDelta( $sql, true );
	}

	public static function insert_data_to_db() {
	    global $wpdb;

		$table = $wpdb->prefix . 'gktpp_table';
		$url = isset( $_POST['url'] ) ? self::validate_DNS_prefetch_url() : '';
		$hint_type = isset( $_POST['hint_type'] ) ? stripslashes( $_POST['hint_type'] ) : '';

	     $sql = $wpdb->prepare( "INSERT INTO %1s ( url, hint_type ) VALUES ( '%s', '%s' )", $table, $url, $hint_type );
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

	// public static function create_ajax_table() {
	//      global $wpdb;
	//
	//      $table = $wpdb->prefix . 'gktpp_ajax_domains';
	//      $charset_collate = $wpdb->get_charset_collate();
	//
	//      $sql = "CREATE TABLE IF NOT EXISTS $table (
	// 		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	// 		domain TEXT NOT NULL,
	// 		UNIQUE KEY id (`id`)
	//      ) $charset_collate";
	//
	// 	if ( ! function_exists( 'dbDelta' ) ) {
	// 		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	// 	}
	//
	//      dbDelta( $sql, true );
	// }
}
