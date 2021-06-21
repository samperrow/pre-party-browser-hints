<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

	public static function show_notice( string $msg, bool $success ):void {
		$alert = ( $success ) ? 'success' : 'error';
		$class = ( empty( $msg ) ? '' : 'active' );
		echo sprintf( '<div id="pprhNoticeBox"><div id="pprhNotice" class="notice notice-%1$s is-dismissible %2$s"><p>%3$s</p></div></div>', $alert, $class, $msg );
	}

	public static function add_pprh_notice( $callback ) {
		\add_action( 'pprh_notice', $callback );
	}

	public static function json_to_array( $json ) {
		$array = false;

		try {
			$array = json_decode( wp_unslash( $json ), true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $error ) {
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
		return preg_replace( '/[\'<>^\"\\\]/', '', $url );
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



	public static function array_into_csv( $hint_ids ) {
		if ( is_array( $hint_ids ) && count( $hint_ids ) > 0 ) {
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

	public static function pprh_is_plugin_active() {
		$plugin = 'pprh-pro/pprh-pro.php';
		$active_plugins = (array) get_option( 'active_plugins', array() );
		$site_active = ( in_array( $plugin, $active_plugins, true ) );
		$network_active = function_exists( 'is_plugin_active_for_network' ) && is_plugin_active_for_network( $plugin );
		return ( $site_active || $network_active );
	}

	public static function get_all_pprh_hints( $query_code ) {
		$dao = new DAO();
		return $dao->get_all_pprh_hints( $query_code );
	}

	public static function get_duplicate_hints( $url, $hint_type ) {
		$dao = new DAO();
		return $dao->get_duplicate_hints( $url, $hint_type );
	}


	public static function get_referrer() {
		return ( isset( $_SERVER['HTTP_REFERER'] ) ? self::clean_url( $_SERVER['HTTP_REFERER'] ) : '' );
	}

	public static function on_pprh_admin_page( $doing_ajax, $referer = null ) {
		if ( null === $referer ) {
			$referer = self::get_referrer();
		}
		return ( $doing_ajax ? str_contains( $referer, PPRH_MENU_SLUG ) : ( PPRH_MENU_SLUG === ( $_GET['page'] ?? '' ) ) );
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