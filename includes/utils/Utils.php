<?php

namespace PPRH\Utils;

//use PPRH\Utils\Sanitize;

//use function PPRH\str_contains;
//use function PPRH\str_ends_with;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

	public static function get_json_option_value( string $option, string $option_prop ) {
		$option_array = \get_option( $option );

		if ( false === $option_array ) {
			$option_array = array();
		}

		return $option_array[$option_prop] ?? '';
	}

	public static function update_json_option( string $option_name, string $prop, $value ) {
		$options        = \get_option( $option_name );
		$options[$prop] = $value;
		self::update_option( $option_name, $options );
	}

	public static function json_to_array( string $json ) {
		$result = self::json_decode( $json );

		if ( is_null( $result ) && 1 === strpos( $json, '\\' ) ) {
			$json = stripslashes( $json );
			$result = self::json_decode( $json );
		}

		if ( ! is_array( $result ) ) {
			$result = json_last_error_msg();
		}

		return $result;
	}

	private static function json_decode( $json ) {
		return json_decode( $json, true );
	}

	public static function update_post_meta( int $post_id, string $metakey, $metadata ):bool {
		return PPRH_RUNNING_UNIT_TESTS || \update_post_meta( $post_id, $metakey, $metadata );
	}

	public static function update_option( string $option, $value, $autoload = 'yes' ):bool {
		return PPRH_RUNNING_UNIT_TESTS || \update_option( $option, $value, $autoload );
	}

	public static function show_notice( string $msg, bool $success ) {
		$alert = ( $success ) ? 'success' : 'error';
		$class = ( empty( $msg ) ? '' : 'active' );
		echo sprintf( '<div id="pprhNoticeBox"><div id="pprhNotice" class="notice notice-%1$s is-dismissible %2$s"><p>%3$s</p></div></div>', $alert, $class, $msg );
	}

	public static function isArrayAndNotEmpty( $arr ):bool {
		return ( is_array( $arr ) && ! empty( $arr ) );
	}

	public static function isSetAndNotEmpty( $item, $prop ):bool {
		return ( isset( $item[$prop] ) && ! empty( $item[$prop] ) );
	}

	public static function isObjectAndNotEmpty( $obj ):bool {
		return ( isset( $obj ) && ( ! is_object( $obj ) ) );
	}

	public static function get_current_datetime( string $added_time = '-6 hours' ):string {
		return date( 'Y-m-d H:i:s', strtotime( $added_time ) );
	}

	public static function array_to_csv( $hint_ids ) {
		if ( self::isArrayAndNotEmpty( $hint_ids ) ) {
			return implode( ',', array_map( 'absint', $hint_ids ) );
		}

		if ( (int) $hint_ids > 0 ) {
			return $hint_ids;
		}

		return '';
	}


	public static function get_server_prop( string $prop ):string {
		return ( isset( $_SERVER[ $prop ] ) ? Sanitize::clean_url( $_SERVER[ $prop ] ) : '' );
	}


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

	public static function create_post_metadata_obj( $post_id, \stdClass $metadata ):\stdClass {
		return (object) array(
			'post_id'  => (int) $post_id,
			'metadata' => $metadata
		);
	}

	public static function get_request_uri() {
		$site_url    = PPRH_SITE_URL;
		$request_uri = self::get_server_prop( 'REQUEST_URI' );

		if ( '' !== $request_uri ) {
			return ( '/' === $request_uri ? $site_url : $request_uri );
		}

		return $site_url;
	}
	public static function trim_end_slash( string $str ):string {
		$url_length_minus_1 = strlen( $str ) - 1;

		if ( str_ends_with( $str, '/' ) ) {
			$str = substr( $str, 0, $url_length_minus_1 );
		}

		return $str;
	}

	public static function get_referer():string {
		$referer = \wp_get_referer();
		return ( false === $referer ) ? '' : $referer;
	}

}

if ( ! function_exists( 'wp_doing_ajax' ) ) {
	$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
	\apply_filters( 'wp_doing_ajax', $doing_ajax );
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
