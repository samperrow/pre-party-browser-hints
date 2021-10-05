<?php
declare(strict_types=1);

namespace PPRH\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Debug {

	public static function get_debug_info():string {
		$browser = self::get_browser();
		$text    = "DEBUG INFO: \n";
		$data = array(
			'Datetime'     => Utils::get_current_datetime(),
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

	public static function get_browser():string {
		$user_agent = Utils::get_server_prop( 'HTTP_USER_AGENT' );
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
			$browser = 'unknown browser.';
		}

		return $browser;
	}

}
