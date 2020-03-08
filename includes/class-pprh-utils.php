<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

	// public function __construct() {
	// }

	public static function shorten_url( $str ) {
		return esc_html( ( strlen( $str ) > 25 ) ? substr( $str, 0, 25 ) . '...' : $str );
	}

	public static function pprh_strip_non_alphanums( $text ) {
		return preg_replace( '/[^A-z0-9]/', '', $text );
	}

	public static function clean_hint_type( $text ) {
		return preg_replace( '/[^A-z\-]/', '', $text );
	}

	public static function pprh_notice() {
		?>
			<div id="pprhNotice" class="inline notice is-dismissible">
				<p></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text">Dismiss this notice.</span>
				</button>
			</div>
		<?php
	}

}
