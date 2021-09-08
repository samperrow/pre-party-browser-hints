<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
