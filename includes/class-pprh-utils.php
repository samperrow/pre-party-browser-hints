<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

	public static function pprh_strip_non_alphanums( $text ) {
		return preg_replace( '/[^A-z0-9]/', '', $text );
	}

	public static function clean_hint_type( $text ) {
		return preg_replace( '/[^A-z\-]/', '', $text );
	}

	public static function clean_url_path( $path ) {
		return strtolower( trim( $path, '/?&' ) );
	}

	public static function clean_hint_attr( $attr ) {
		return strtolower( preg_replace( '/[^A-z|\/]/', '', $attr ) );
	}

	public static function strip_non_digits( $str ) {
		return strtolower( preg_replace( '/[^0-9]/', '', $str ) );
	}

	public static function shorten_url( $str ) {
		return esc_html( ( strlen( $str ) > 25 ) ? substr( $str, 0, 25 ) . '...' : $str );
	}

	public static function strip_non_alpha( $str ) {
		return preg_replace( '/[^a-z]/', '', $str );
	}

	public static function getPostID() {
		return ( isset( $_GET['post'] ) ) ? self::strip_non_digits( $_GET['post'] ) : null;
	}

	public static function pprh_show_update_result( $notice ) {
		$msg = ( 'success' === $notice['result'] )
			? 'Resource hints ' . $notice['action'] . ' successfully.'
			: 'Resource hints failed to update. Please try again or submit a bug report in the form below.';

		if ( ! empty( $notice['removedDupHint'] ) ) {
			$msg .= ' A duplicate hint was removed that was not needed.';
		}

		if ( ! empty( $notice['url_parsed'] ) ) {
			$msg .= ' Only the domain name of the URL you entered is necessary for DNS prefetch and preconnect hints.';
		}

		if ( ! empty( $notice['globalHintExists'] ) ) {
			$msg = 'A duplicate global hint already exists, so there is no need to add another.';
		}

		echo '<div style="margin: 10px 0;" class="inline notice notice-' . $notice['result'] . ' is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
	}

	public static function on_pprh_home() {
	    global $pagenow;
	    return ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'pprh-plugin-settings' === $_GET['page'] );
	}

	public static function admin_notice( $msg, $status ) {
		?>
		<script>
            function pprhShowNotice() {
                var msg = "<?php echo $msg; ?>";
                var status = "<?php echo $status; ?>";
                pprhAdminJS.ToggleAdminNotice(status, msg);

                setTimeout(function() {
                    pprhAdminJS.ToggleAdminNotice(status, '');
                }, 10000 );

            }
            pprhShowNotice();
		</script>
		<?php
	}

}
