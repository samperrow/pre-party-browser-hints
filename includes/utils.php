<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

//    public function __construct() {}

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



	// hint creation utils
	public static function create_pprh_hint( $raw_data ) {
		$create_hints = new Create_Hints();
		$dao = new DAO();
		$new_hint = $create_hints->create_hint( $raw_data );

		if ( is_array( $new_hint ) ) {
			$duplicate_hints_exist = $create_hints->duplicate_hints_exist( $new_hint );

			if ( $duplicate_hints_exist ) {
//				$arr = array(
//					'result'     => false,
//					'hint_id'    => '',
//					'last_error' => 'A duplicate hint already exists!'
//				);
				return $dao->create_db_result( false, '', 'A duplicate hint already exists!', 'created', null );
			}
            return $new_hint;
		}
        return false;
	}

	public static function create_raw_hint_array( $url, $hint_type, $auto_created = 0, $as_attr = '', $type_attr = '', $crossorigin = '', $post_id = '', $post_url = '' ) {
		$arr = array(
			'url'          => $url,
			'as_attr'      => $as_attr,
			'hint_type'    => $hint_type,
			'type_attr'    => $type_attr,
			'crossorigin'  => $crossorigin,
			'auto_created' => $auto_created
		);

		$arr = apply_filters( 'pprh_append_hint_array', $arr, $post_id, $post_url );
		return $arr;
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

}
