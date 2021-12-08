<?php
declare(strict_types=1);

namespace PPRH\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sanitize {

//	public function __construct () {}

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

}
