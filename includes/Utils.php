<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

	public static function show_notice( string $msg, bool $success ) {
		if ( PPRH_RUNNING_UNIT_TESTS ) {
			return;
		}
		$alert = ( $success ) ? 'success' : 'error';
		$class = ( empty( $msg ) ? '' : 'active' );
		echo sprintf( '<div id="pprhNoticeBox"><div id="pprhNotice" class="notice notice-%1$s is-dismissible %2$s"><p>%3$s</p></div></div>', $alert, $class, $msg );
	}

	public static function update_option( string $option, $value, $autoload = 'yes' ):bool {
		return PPRH_RUNNING_UNIT_TESTS || \update_option( $option, $value, $autoload );
	}

	public static function json_to_array( string $json ) {
		$result = false;

		if ( 1 === strpos( $json, '\\', 0 ) ) {
			$json = str_replace( '\\', '', $json );
		}

		try {
			$result = json_decode( $json, true );
		} catch ( \Exception $exception ) {
			$result = array();
			self::log_error( "$json\n$exception" );
		}

		if ( ! is_array( $result ) ) {
			self::log_error( 'Failed at Utils::json_to_array()' );
		}

		return $result;
	}

    public static function strip_non_alphanums( string $text ):string {
		return preg_replace( '/[^a-z\d]/imu', '', $text );
	}

	public static function strip_non_numbers( $text, bool $as_str = true ) {
		$str = preg_replace( '/\D/', '', $text );
		return ( $as_str ) ? $str : (int) $str;
	}

	public static function clean_hint_type( string $text ):string {
		return preg_replace( '/[^a-z|\-]/i', '', $text );
	}

	public static function clean_url( string $url ):string {
		return preg_replace( '/[\s\'<>^\"\\\]/', '', $url );
	}

	public static function strip_bad_chars( string $url ):string {
		return preg_replace( '/[\'<>^\"\\\]/', '', $url );
	}

	public static function clean_url_path( string $path ):string {
		return strtolower( trim( $path, '/?&' ) );
	}

	public static function clean_hint_attr( string $attr ):string {
		return strtolower( preg_replace( '/[^a-z0-9|\/]/i', '', $attr ) );
	}

	public static function clean_string_array( array $str_array ):array {
		foreach ( $str_array as $item => $val ) {
			$str_array[ $item ] = self::strip_bad_chars( $val );
		}

		return $str_array;
	}



	public static function isArrayAndNotEmpty( $arr ):bool {
		return ( is_array( $arr ) && ! empty( $arr ) );
	}

	public static function get_current_datetime( string $added_time = '' ):string {
		$offset          = new \DateTimeZone( 'America/Denver' );
		$datetime        = new \DateTime( 'now', $offset );
		$timezone_offset = (string) ( $datetime->getOffset() / 3600 ) . ' hours';
		$offset          = ( empty( $added_time ) ? $timezone_offset : $added_time );
		return date( 'Y-m-d H:i:s', strtotime( $offset ) );
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


	public static function update_checkbox_option( array $post, string $option_name ):string {
		$update_val = $post[ $option_name ] ?? 'false';
		self::update_option( $option_name, $update_val );
		return $update_val;
	}


	public static function esc_get_option( string $option ) {
		return \esc_html( \get_option( $option ) );
	}

	public static function does_option_match( string $option, string $match, string $output ) {
		$option_value = self::esc_get_option( $option );
		return ( ( $option_value === $match ) ? $output : '' );
	}

	public static function get_server_prop( string $prop ):string {
		return ( isset( $_SERVER[ $prop ] ) ? self::clean_url( $_SERVER[ $prop ] ) : '' );
	}

	public static function get_plugin_page( bool $doing_ajax, string $referer ):int {
		if ( '' === $referer ) {
			$referer = self::get_referer();
		}

		$request_uri = self::get_server_prop( 'REQUEST_URI' );
		return self::get_plugin_page_ctrl( $doing_ajax, $referer, $request_uri );
	}

	/**
	 * @param bool $doing_ajax
	 * @param string $referer
	 * @param string $request_uri
	 * @return int: -1: front end page; 0 means the current page does NOT use PPRH; 1 means current page is PPRH ADMIN; 2 means current page is POST EDIT.
	 */
	public static function get_plugin_page_ctrl( bool $doing_ajax, string $referer, string $request_uri ):int {
		$matcher = ( $doing_ajax ) ? $referer : $request_uri;
		$val = 0;

		if ( str_contains( $matcher, PPRH_MENU_SLUG ) ) {
			$val = 1;
		} elseif ( str_contains( $matcher, 'post.php' ) ) {
			$val = 2;
		}

		return $val;
	}

	public static function get_referer():string {
		$referer = \wp_get_referer();
		return ( false === $referer ) ? '' : $referer;
	}

	public static function get_domain_from_url( string $url ):string {
		$parsed_url = \wp_parse_url( $url );
		return $parsed_url['host'] ?? self::get_server_prop( 'HTTP_HOST' );
	}

	public static function log_error( $message ):bool {
		if ( 'true' !== \get_option( 'pprh_debug_enabled', 'false' ) ) {
			return false;
		}

		if ( ! class_exists( \PPRH\DebugLogger::class ) ) {
			include_once 'DebugLogger.php';
		}

		$debugger = new DebugLogger();
		$debugger->log_error( $message );
		return true;
	}



	public static function get_browser():string {
		$user_agent = self::get_server_prop( 'HTTP_USER_AGENT' );
		return self::get_browser_name( $user_agent );
	}

	public static function get_browser_name( $user_agent ):string {

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
		} else {
			$browser = '';
		}

		return $browser;
	}

	public static function get_debug_info():string {
		$browser = self::get_browser();
		$text    = "DEBUG INFO: \n";
		$data = array(
			'Datetime'     => self::get_current_datetime(),
			'PHP Version'  => PHP_VERSION,
			'WP Version'   => get_bloginfo( 'version' ),
			'Home URL'     => home_url(),
			'Browser'      => $browser,
			'PPRH Version' => PPRH_VERSION
		);

		foreach ( $data as $item => $val ) {
			$text .= "$item: $val; ";
		}

		return $text;
	}

	public static function send_email( string $to, string $subject, string $message ):bool {
		try {
			\wp_mail( $to, $subject, $message );
		} catch ( \Exception $e ) {
			self::log_error( $e );
			return false;
		}

		return true;
	}


	public static function get_api_response_body( array $response, string $error_msg ):array {
		$response_body = array();

		if (\is_wp_error($response)) {
			self::log_error($response);
		} elseif (isset($response['response']) && 200 === \wp_remote_retrieve_response_code($response)) {
			$body = \wp_remote_retrieve_body($response);
			$response_body = self::json_to_array($body);
		}

		if (!self::isArrayAndNotEmpty($response_body)) {
			self::log_error($error_msg);
			$response_body = array();
		}

		return $response_body;
	}

	public static function create_raw_hint( $url, $hint_type, $auto_created = 0, $as_attr = '', $type_attr = '', $crossorigin = '', $media = '', $post_id = null ):array {
		$hint = array(
			'url'          => $url,
			'hint_type'    => $hint_type,
			'auto_created' => $auto_created,
			'as_attr'      => $as_attr,
			'type_attr'    => $type_attr,
			'crossorigin'  => $crossorigin,
			'media'        => $media
		);

		$hint['current_user'] = \wp_get_current_user()->display_name ?? '';

		if ( isset( $post_id ) ) {
			$hint['post_id'] = $post_id;
		}

		return $hint;
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
