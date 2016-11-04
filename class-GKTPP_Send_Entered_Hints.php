<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Send_Entered_Hints {

	public function __construct() {
		add_action( 'wp_head', array( $this, 'gktpp_print_hint_info_selected_pages' ), 1, 0 );
	}

	public function gktpp_print_hint_info_selected_pages() {

		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_table';

		$sql = $wpdb->prepare( 'SELECT * FROM %1s', $table );
		$result = $wpdb->get_results( $sql, ARRAY_A, 0 );

		if ( count( $result ) < 1 || ( ! is_array( $result ) ) ) {
			return;
		}

		foreach ( $result as $key => $value ) {

			if ( ( 'Enabled' === $result[ $key ]['status'] ) ) {

				$hint_url = $result[ $key ]['url'];
				$hint_type = strtolower( $result[ $key ]['hint_type'] );
				$current_id = get_the_ID();

				$requested_page_ids = json_decode( sanitize_text_field( $result[ $key ]['pageOrPostID'] ) );
				$compared_array = array_intersect( $requested_page_ids, self::gktpp_get_page_post_ids() );	// compare $requestedPageIDs array with $pagePostIdArray array and return all matching values into a new array

				foreach ( $compared_array as $key ) {
					if ( ( $current_id ) && ( $key == $current_id ) ) {

						if ( ! preg_match( '/(http|https)/i', $hint_url ) ) {	// if the supplied URL does not have HTTP or HTTPS given, add a '//' to not confuse the browser
							$hint_url = '//' . $hint_url;
						}

						$crossorigin = ( ( 'preconnect' === $hint_type ) && ( 'fonts.googleapis.com' === $hint_url ) ) ? ' crossorigin' : '';

						printf( '<link rel="%1$s" href="%2$s"%3$s>', $hint_type, $hint_url, $crossorigin );
					}
				}
			}
		}
	}

	public function gktpp_get_page_post_ids() {
		$pages_and_posts = array_merge( get_pages(), get_posts() );
		$page_post_id_array = array();

		foreach ( $pages_and_posts as $key => $value ) {
			$page_post_id_array[] = $value->ID;
		}
		return $page_post_id_array;
	}

}

$asdf = new GKTPP_Send_Entered_Hints();
