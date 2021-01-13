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

	public static function create_db_result( $wpdb, $action, $new_hint = null ) {
		if ( ! ( strrpos( $action, 'd' ) === strlen( $action ) -1 ) ) {
			$action .= 'd';
		}
		$result     = (bool) $wpdb->result;
		$hint_id  = ( ! empty( $wpdb->insert_id ) ? $wpdb->insert_id : null );
		$last_error = $wpdb->last_error;
		$result = self::create_db_response( $result, $hint_id, $last_error, $action, $new_hint );
		$wpdb->flush();

		return $result;
	}

	public static function create_db_response( $result, $hint_id, $last_error, $action, $new_hint = null ) {
		return array(
            'new_hint'  => $new_hint,
			'db_result' => array(
				'last_error' => $last_error,
				'hint_id'    => $hint_id,
				'success'    => $result,
				'status'     => ( $result ) ? 'success' : 'error',
				'msg'        => ( $result ) ? "Resource hint $action successfully." : "Failed to $action hint."
            )
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



	public static function array_into_csv( $hint_ids ) {
		if ( ! is_array( $hint_ids ) || count( $hint_ids ) === 0 ) {
			return false;
		}

		return implode( ',', array_map( 'absint', $hint_ids ) );
	}

}