<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

	public static function show_notice( string $msg, string $success ) {
		if ( PPRH_RUNNING_UNIT_TESTS ) {
			return;
		}
		$alert = ( $success ) ? 'success' : 'error';
		$class = ( empty( $msg ) ? '' : 'active' );
		echo sprintf( '<div id="pprhNoticeBox"><div id="pprhNotice" class="notice notice-%1$s is-dismissible %2$s"><p>%3$s</p></div></div>', $alert, $class, $msg );
	}

	public static function add_pprh_notice( $callback ) {
		\add_action( 'pprh_notice', $callback );
	}

	public static function update_option( string $option, $value ) {
		return PPRH_RUNNING_UNIT_TESTS || \update_option( $option, $value );
	}

	public static function json_to_array( string $json ):array {
		$array = array();

		try {
			$array = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
		} catch( \JsonException $exception ) {
			self::log_error( $json );
		}

		if ( ! is_array( $array ) ) {
			self::log_error( "Failed at Utils::json_to_array()" );
			return array();
		}

		return $array;
    }

    public static function strip_non_alphanums( string $text ):string {
		return preg_replace( '/[^a-z\d]/imu', '', $text );
	}

	public static function strip_non_numbers( string $text ):string {
		return preg_replace( '/\D/', '', $text );
	}

	public static function clean_hint_type( string $text ):string {
		return preg_replace( '/[^a-z|\-]/i', '', $text );
	}

	public static function clean_url( string $url ):string {
		return preg_replace( '/[\s\'<>^\"\\\]/', '', $url );
	}

	public static function clean_url_path( string $path ):string {
		return strtolower( trim( $path, '/?&' ) );
	}

	public static function clean_hint_attr( string $attr ):string {
		return strtolower( preg_replace( '/[^a-z0-9|\/]/i', '', $attr ) );
	}

	public static function clean_string_array( array $str_array ):array {
		foreach( $str_array as $item => $val ) {
			$str_array[$item] = self::strip_non_alphanums( $val );
		}

		return $str_array;
	}

	public static function is_null_or_empty_string( string $str ):bool {
		return ( null === $str || '' === $str );
	}

	public static function isArrayAndNotEmpty( $arr ):bool {
		return ( is_array( $arr ) && ! empty( $arr ) );
	}

	public static function get_current_datetime():string {
		$offset = new \DateTimeZone( 'America/Denver' );
		$datetime = new \DateTime( 'now', $offset );
		$timezone_offset = (string) ($datetime->getOffset() / 3600) . ' hours';
		return date( 'Y-m-d H:m:s', strtotime( $timezone_offset ) );
	}



	public static function array_into_csv( $hint_ids ) {
		if ( self::isArrayAndNotEmpty( $hint_ids ) ) {
			return implode( ',', array_map( 'absint', $hint_ids ) );
		}

		if ( (int) $hint_ids > 0 ) {
			return $hint_ids;
		}

		return '';
	}


	public static function esc_get_option( string $option ) {
		return \esc_html( \get_option( $option ) );
	}

	public static function does_option_match( string $option, string $match, string $output ) {
		$option_value = self::esc_get_option( $option );
		return ( ( $option_value === $match ) ? $output : '' );
	}

	public static function get_server_prop( string $prop ):string {
		return ( isset( $_SERVER[$prop] ) ? self::clean_url( $_SERVER[$prop] ) : '' );
	}

	public static function on_pprh_page( bool $doing_ajax, string $referer ):int {
		if ( '' === $referer ) {
			$referer = self::get_server_prop( 'HTTP_REFERER' );
		}

		$request_uri = self::get_server_prop( 'REQUEST_URI' );
		return self::on_pprh_page_ctrl( $doing_ajax, $referer, $request_uri );
	}

	public static function log_error( $message ) {
		$debugger = new DebugLogger();
		$debugger->log_error( $message );
	}


	/**
	 * @param bool $doing_ajax
	 * @param string $referer
	 * @param string $request_uri
	 * @return int: 0 means the current page does NOT use PPRH; 1 means current page is PPRH ADMIN; 2 means current page is POST EDIT.
	 */
	public static function on_pprh_page_ctrl( bool $doing_ajax, string $referer, string $request_uri ):int {
		$matcher = ( $doing_ajax ) ? $referer : $request_uri;
		$val = 0;

		if ( str_contains( $matcher, PPRH_MENU_SLUG ) ) {
			$val = 1;
		}
//		elseif ( str_contains( $matcher, 'post.php' ) ) {
//			$val = 2;
//		}

		return $val;
	}


	public static function get_browser():string {
		$user_agent = self::get_server_prop( 'HTTP_USER_AGENT' );
		return self::get_browser_name( $user_agent );
	}

	public static function get_browser_name( $user_agent ):string {
		$browser = '';

		if ( str_contains( $user_agent, 'Edg' ) ) {
			$browser = 'Edge';
		} elseif ( str_contains( $user_agent, 'OPR' ) ) {
			$browser = 'Opera';
		} elseif ( str_contains( $user_agent, 'Chrome' ) ) {
			$browser = 'Chrome';
		} elseif ( str_contains( $user_agent, 'Safari' ) ) {
			$browser = 'Safari';
		} elseif ( str_contains( $user_agent, 'Firefox' ) ) {
			$browser = 'Firefox';
		} elseif ( ( str_contains( $user_agent, 'Trident' ) ) || ( str_contains( $user_agent, 'MSIE' ) && str_contains( $user_agent, 'Opera' ) ) ) {
			$browser = 'MSIE';
		} elseif ( str_contains( $user_agent, 'Netscape' ) ) {
			$browser = 'Netscape';
		}

		return $browser;
	}

	public static function apply_pprh_filters( string $filter_name, array $args ) {
		$val = '';

		if ( PPRH_PRO_ACTIVE ) {
			$arr_len = count( $args );

			if ( 1 === $arr_len ) {
				$val = \apply_filters( $filter_name, $args[0] );
			} elseif ( 2 === $arr_len ) {
				$val = \apply_filters( $filter_name, $args[0], $args[1] );
			} elseif ( 3 === $arr_len ) {
				$val = \apply_filters( $filter_name, $args[0], $args[1], $args[2] );
			}
		} else {
			$val = ( empty( $args ) ? $args : $args[0] );
		}

		return $val;
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