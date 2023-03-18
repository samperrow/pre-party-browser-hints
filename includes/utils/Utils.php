<?php

namespace PPRH\Utils;

//use PPRH\Utils\Sanitize;

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







	public static function get_referer():string {
		$referer = \wp_get_referer();
		return ( false === $referer ) ? '' : $referer;
	}

}

