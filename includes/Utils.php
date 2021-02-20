<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

	public static function admin_notice() {
		?>
        <div id="pprhNoticeBox"></div>
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
		return esc_html( $opt === $val ? 'selected=selected' : '' );
	}

	public static function is_option_checked( $option ) {
		$value = get_option( $option );
		return esc_html( 'true' === $value ? 'checked' : '' );
	}

	public static function esc_get_option( $option ) {
	    $value = get_option( $option );
	    return esc_html( $value );
	}

	public static function json_to_array( $json ) {
		$arr = explode( ', ', $json );
		return wp_unslash( json_encode( $arr ) );
	}

	public static function pprh_is_plugin_active() {
		$plugin = 'pprh-pro/pprh-pro.php';
		$site_active = ( in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) );
		$network_active = ( function_exists( 'is_plugin_active_for_network' ) ) ? is_plugin_active_for_network( $plugin ) : false;
		return ( $site_active || $network_active );
	}

	public function on_pprh_page() {
		global $pagenow;
		$referrer = ( ! empty( $_SERVER['HTTP_REFERER'] ) ? self::clean_url( $_SERVER['HTTP_REFERER'] ) : '' );
		$pprh_page = 'pprh-plugin-settings';
		$doing_ajax = ( false !== stripos( $referrer, $pprh_page ) && wp_doing_ajax() );
		$on_pprh_admin = ( ( isset( $_GET['page'] ) && $pprh_page === $_GET['page'] ) && 'admin.php' === $pagenow );
		$pro_on_pprh_page = apply_filters( 'pprh_utils_pro_on_pprh_page', $pagenow, $referrer );
		return ( $on_pprh_admin || $doing_ajax || $pro_on_pprh_page);
	}

	public static function on_pprh_admin() {
	    $screen = get_current_screen();
	    $pprh_screen = 'toplevel_page_pprh-plugin-settings';
	    return ( null !== $screen && ! empty( $screen->id ) && $pprh_screen === $screen->id );
	}

}
