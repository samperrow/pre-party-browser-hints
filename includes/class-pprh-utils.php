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

	public static function getPostID() {
		return ( isset( $_GET['post'] ) ) ? self::clean_post_id( $_GET['post'] ) : null;
	}

	public static function clean_post_id( $post_id ) {
		return preg_replace( '/[^0-9]/', '', $post_id );
	}

	public static function pprh_strip_non_alphanums( $text ) {
		return preg_replace( '/[^A-z0-9]/', '', $text );
	}

	public static function clean_hint_type( $text ) {
		return preg_replace( '/[^A-z\-]/', '', $text );
	}

	public static function verify_license() {
		return (bool) get_option( 'pprh_license_key' );
	}

	public static function get_url_query_from_post_id( $id ) {
	    $url = wp_parse_url( get_permalink( $id ) );
        $newurl = ( isset( $url['path'] ) && '/' !== $url['path'] ) ? $url['path'] : ( ( isset( $url['query'] ) ) ? $url['query'] : '' );
	    return Utils::clean_url_path( $newurl );
    }

    public static function clean_url_path( $path ) {
	    return strtolower( trim( $path, '/?&' ) );
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

    public static function get_default_modal_post_types() {
        global $wp_post_types;
        $results = array();

        foreach ( $wp_post_types as $post_type ) {
            if ( $post_type->public && 'attachment' !== $post_type->name ) {
                $results[] = $post_type->name;
            }
        }
        return json_encode( ( count( $results ) > 0 ) ? $results :  array( 'page', 'post' ) );
    }

	// public static function db_error_log( $db_data ) {
	// 	if ( $db_data->result ) {

	// 		$data = array(
	// 			'Datetime'    => gmdate( 'Y-m-d T h:m:s' ),
	// 			'PHP Version' => PHP_VERSION,
	// 			'WP version'  => get_bloginfo( 'version' ),
	// 			'home_url'    => home_url(),
	// 			'last_query'  => $db_data->last_query,
	// 			'last_error'  => $db_data->last_error,
	// 		);
	// 		$text = "\n";
	// 		foreach ( $data as $item => $val ) {
	// 			$text .= "$item: $val\n";
	// 		}

	// 		$file = PPRH_PLUGIN_DIR . '/.errors.txt';
	// 		file_put_contents( $file, $text . PHP_EOL, FILE_APPEND | LOCK_EX );
	// 	}
	// }

}
