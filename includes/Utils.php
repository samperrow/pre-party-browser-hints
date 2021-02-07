<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

	public static function admin_notice() {
		?>
		<div id="pprhNotice" class="notice is-dismissible">
			<p></p>
		</div>
		<?php
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

	public static function get_opt_val( $opt ) {
		$val = get_option( $opt );
		return ( ! empty( $val ) ) ? $val : '';
	}

	public static function array_into_csv( $hint_ids ) {
		if ( ! is_array( $hint_ids ) || count( $hint_ids ) === 0 ) {
			return false;
		}

		return implode( ',', array_map( 'absint', $hint_ids ) );
	}

	public static function get_option_status( $option, $val ) {
	    $opt = get_option( $option );
		return esc_html( $opt === $val ? 'selected=selected' : '');
	}

	// need to account for ajax
	public static function on_pprh_page() {
	    global $pagenow;
		return
			( ( isset( $_GET['page'] ) && 'pprh-plugin-settings' === $_GET['page'] ) && 'admin.php' === $pagenow )
            || ( ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) && 'post.php' === $pagenow );
	}

	public static function esc_get_option( $option ) {
	    return esc_html( get_option( $option ) );
	}

	public static function pprh_is_plugin_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || is_plugin_active_for_network( $plugin );
	}

}