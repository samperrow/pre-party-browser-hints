<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

//    public function __construct() {}

	public static function admin_notice( $msg = '' ) {
//		if ('' !== $msg) {
//			echo $msg;
//		}
		?>
		<div id="pprhNotice" class="inline notice is-dismissible">
			<p></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"></span>
			</button>
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

	public static function get_wpdb_result( $wp_db, $action ) {
	    if ( ! ( strrpos( $action, 'd' ) === strlen( $action ) -1 ) ) {
	        $action .= 'd';
		}

	    $result = $wp_db->result;

		return array(
			'last_error' => $wp_db->last_error,
			'success'    => ( $result ),
			'status'     => ( $result ) ? 'success' : 'error',
			'msg'        => ( $result ) ? "Resource hint $action successfully." : "Failed to $action hint.",
		);
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

	public static function create_pprh_hint( $raw_data ) {
//		define( 'CREATING_HINT', true );
		$create_hints = new Create_Hints();
		return $create_hints->initialize( $raw_data );
	}

	public static function create_raw_hint_object( $url, $hint_type, $auto_created = 0, $as_attr = '', $type_attr = '', $crossorigin = '', $post_id = '', $post_url = '' ) {
        $arr = array(
            'url'          => $url,
            'as_attr'      => $as_attr,
			'hint_type'    => $hint_type,
			'type_attr'    => $type_attr,
			'crossorigin'  => $crossorigin,
			'auto_created' => $auto_created
		);

        return (object) $arr;
	}

	public static function create_response( $msg, $status ) {
	    $success = ( 'success' === $status );
		return array(
			'msg' => $msg,
			'status' => $status,
			'success' => $success
		);
	}

}