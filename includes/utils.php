<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

	public static function strip_non_alphanums( $text ) {
		return preg_replace( '/[^A-z0-9]/', '', $text );
	}

	public static function strip_non_numbers( $text ) {
		return preg_replace( '/\D/', '', $text );
	}

	public static function clean_hint_type( $text ) {
		return preg_replace( '/[^A-z\-]/', '', $text );
	}

	public static function clean_url( $url ) {
		return preg_replace( '/[\'<>^\"]/', '', $url );
	}

	public static function clean_url_path( $path ) {
		return strtolower( trim( $path, '/?&' ) );
	}

	public static function clean_hint_attr( $attr ) {
		return strtolower( preg_replace( '/[^A-z|\/]/', '', $attr ) );
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



//    public static function get_default_modal_post_types() {
//		global $wp_post_types;
//		$results = array();
//
//		foreach ( $wp_post_types as $post_type ) {
//			if ( $post_type->public && 'attachment' !== $post_type->name ) {
//				$results[] = $post_type->name;
//			}
//		}
//		return json_encode( ( count( $results ) > 0 ) ? $results : array( 'page', 'post' ) );
//	}

	public static function get_wpdb_result( $wp_db, $action ) {
	    if ( ! ( strrpos( $action, 'd' ) === strlen( $action ) -1 ) ) {
	        $action .= 'd';
		}

		return array(
			'last_error' => $wp_db->last_error,
			'success'    => ( $wp_db->result ),
			'status'     => ( $wp_db->result ) ? 'success' : 'error',
			'msg'        => ( $wp_db->result ) ? "Resource hint $action successfully." : "Failed to $action hint.",
		);
	}

	public static function get_option_status( $option, $val ) {
	    $opt = get_option( $option );
		echo esc_html( $opt === $val ? 'selected=selected' : '');
	}

	public static function on_pprh_home() {
		return ( isset( $_GET['page'] ) && 'pprh-plugin-settings' === $_GET['page'] );
	}

	public static function create_pprh_hint( $raw_data ) {
		if ( ! class_exists( Create_Hints::class ) ) {
			include_once PPRH_ABS_DIR . 'includes/create-hints.php';
//			do_action( 'pprh_load_create_hints_child' );
		}

		define( 'CREATING_HINT', true );
		$create_hints = new Create_Hints();
		return $create_hints->initialize( $raw_data );
	}

	public static function create_hint_object( $url, $hint_type, $auto_created = 0, $as_attr = '', $type_attr = '', $crossorigin = '' ) {
        return (object) array(
            'url'          => $url,
            'hint_type'    => $hint_type,
            'auto_created' => $auto_created,
            'as_attr'      => $as_attr,
            'type_attr'    => $type_attr,
            'crossorigin'  => $crossorigin
        );
	}

}