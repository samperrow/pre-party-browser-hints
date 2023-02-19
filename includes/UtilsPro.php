<?php

namespace PPRH;

use PPRH\Utils\Utils;
use PPRH\Utils\Sanitize;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UtilsPro {

	public static function get_admin_post_id() {
		$doing_ajax = \wp_doing_ajax();
		$_get_post = $_GET['post'] ?? null;
		$_get_page = $_GET['page'] ?? null;
		$referrer = Utils::get_referer();
		return self::get_admin_post_id_ctrl( $doing_ajax, $_get_page, $_get_post, $referrer );
	}

	public static function get_admin_post_id_ctrl( bool $doing_ajax, string $_get_page = null, string $_get_post = null, string $referrer = '' ):string {
		$post_id = '';

		if ( isset( $_get_post ) ) {
			$post_id = Sanitize::strip_non_numbers( $_get_post, true );
		} elseif ( isset( $_get_page ) && PPRH_MENU_SLUG === $_get_page ) {
			$post_id = 'global';
		} elseif ( $doing_ajax ) {
			$pprh_admin_page = 'admin.php?page=' . PPRH_MENU_SLUG;

			if ( str_contains( $referrer, 'post.php?post=' ) ) {
				$post_id = Sanitize::strip_non_numbers( $referrer, true );
			} elseif ( str_contains( $referrer, $pprh_admin_page ) ) {
				$post_id = 'global';
			}
		}

		return $post_id;
	}

	public static function get_client_post_id( string $page_on_front, string $request_uri ):string {
		$request_uri    = self::trim_end_slash( $request_uri );
		$site_url       = self::trim_end_slash( PPRH_SITE_URL );
		$home_url       = self::trim_end_slash( \get_option( 'home' ) );
		$client_post_id = '0';

		if ( $request_uri !== $home_url && $request_uri !== $site_url ) {
			$client_post_id = \PPRH\DAO::get_post_id_from_url( $request_uri );

			if ( $client_post_id === $page_on_front ) {
				$client_post_id = '0';
			}
		}

		return $client_post_id;
	}

	public static function is_date( $date ):bool {
		return ( preg_match( '/\d{4}?-?\d{2}?-?\d{2}/', $date ) > 0 );
	}

	public static function count_equals( array $arr_1, array $arr_2 ):bool {
		return count( $arr_1 ) === count( $arr_2 );
	}

	// tested
	public static function trim_end_slash( string $str ):string {
		$url_length_minus_1 = strlen( $str ) - 1;

		if ( str_ends_with( $str, '/' ) ) {
			$str = substr( $str, 0, $url_length_minus_1 );
		}

		return $str;
	}


	public static function get_request_uri() {
		$site_url    = PPRH_SITE_URL;
		$request_uri = Utils::get_server_prop( 'REQUEST_URI' );

		if ( '' !== $request_uri ) {
			return ( '/' === $request_uri ? $site_url : $request_uri );
		}

		return $site_url;
	}


	public static function remote_get( string $remote_url, array $args ):array {
		$response = \wp_safe_remote_get( $remote_url, $args );

		if ( ( \is_wp_error( $response ) && method_exists( $response, 'get_error_message' ) ) || ! isset( $response['body'] ) ) {
			DebugLogger::logger( true, $response->get_error_message() );
		}

		else {
			$json_body = \wp_remote_retrieve_body( $response );
			$json_array = Utils::json_to_array( $json_body );

			if ( is_string( $json_array ) ) {
				DebugLogger::logger( true, "$json_array\n: $json_body" );
			}

			return $json_array;
		}

		return array();
	}

	public static function create_post_metadata_obj( $post_id, \stdClass $metadata ):\stdClass {
		return (object) array(
			'post_id'  => (int) $post_id,
			'metadata' => $metadata
		);
	}

}

if ( ! function_exists( 'str_contains' ) ) {
	function str_contains( $haystack, $needle ) {
		return ( '' === $needle || false !== strpos( $haystack, $needle ) );
	}
}

if ( ! function_exists( 'str_starts_with' ) ) {
	function str_starts_with( $haystack, $needle) {
		return 0 === strncmp( $haystack, $needle, \strlen( $needle ) );
	}
}

if ( ! function_exists( 'str_ends_with' ) ) {
	function str_ends_with( $haystack, $needle ) {
		return ( '' === $needle || ( '' !== $haystack && 0 === substr_compare( $haystack, $needle, -\strlen( $needle ) ) ) );
	}
}
