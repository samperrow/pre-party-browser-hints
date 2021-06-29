<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

	public static function show_notice( $msg, $success ) {
//		if ( PPRH_RUNNING_UNIT_TESTS ) {
//			return;
//		}
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

	public static function json_to_array( $json ):array {
		$array = array();

		try {
			$unslashed_json = wp_unslash( $json );
			$array = json_decode( $unslashed_json, true );
		} catch ( \Exception $error ) {
			// log error..
		}

		return $array;
    }

    public static function strip_non_alphanums( $text ) {
		return preg_replace( '/[^a-z\d]/imu', '', $text );
	}

	public static function strip_non_numbers( $text ) {
		return preg_replace( '/\D/', '', $text );
	}

	public static function clean_hint_type( $text ) {
		return preg_replace( '/[^a-z|\-]/i', '', $text );
	}

	public static function clean_url( $url ) {
		return preg_replace( '/[\s\'<>^\"\\\]/', '', $url );
	}

	public static function clean_url_path( $path ) {
		return strtolower( trim( $path, '/?&' ) );
	}

	public static function clean_hint_attr( $attr ) {
		return strtolower( preg_replace( '/[^a-z0-9|\/]/i', '', $attr ) );
	}

	public static function is_null_or_empty_string( $str ) {
		return ( null === $str || '' === $str );
	}

	public static function isArrayAndNotEmpty( $arr ) {
		return ( is_array( $arr ) && ! empty( $arr ) );
	}



	public static function array_into_csv( $hint_ids ) {
		if ( self::isArrayAndNotEmpty( $hint_ids ) ) {
			return implode( ',', array_map( 'absint', $hint_ids ) );
		}

		if ( (int) $hint_ids > 0 ) {
			return $hint_ids;
		}

		return false;
	}

	public static function esc_get_option( $option ) {
		return \esc_html( \get_option( $option ) );
	}

	public static function get_option_status( $option, $val ) {
		$value = self::esc_get_option( $option );
		return ( ( $value === $val ) ? 'selected=selected' : '' );
	}

	public static function is_option_checked( $option ) {
		$value = self::esc_get_option( $option );
		return ( 'true' === $value ? 'checked' : '' );
	}

	public static function get_pprh_hints( $is_admin ) {
		$dao = new DAO();
		return $dao->get_pprh_hints( $is_admin );
	}

	public static function get_duplicate_hints( string $url, string $hint_type ):array {
		$dao = new DAO();
		return $dao->get_duplicate_hints( $url, $hint_type );
	}


	public static function get_referrer() {
		return ( isset( $_SERVER['HTTP_REFERER'] ) ? self::clean_url( $_SERVER['HTTP_REFERER'] ) : '' );
	}

	public static function on_pprh_admin_page( bool $doing_ajax, $referer = null ):bool {
		if ( null === $referer ) {
			$referer = self::get_referrer();
		}
		return ( $doing_ajax ? str_contains( $referer, PPRH_MENU_SLUG ) : ( PPRH_MENU_SLUG === ( $_GET['page'] ?? '' ) ) );
	}

	public static function string_in_array( array $array, string $test_col ):bool {
		foreach( $array as $item ) {
			if ( 0 === strcasecmp( $item, $test_col ) ) {
				return true;
			}
		}

		return false;
	}

}

if ( ! function_exists( 'wp_doing_ajax' ) ) {
	\apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
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