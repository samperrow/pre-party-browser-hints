<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Ajax {

	public function check_or_send_ajax() {
		// global $wpdb;
		// $table = $wpdb->prefix . 'gktpp_table';
		// $sql = $wpdb->prepare( 'SELECT url FROM %1s', array( $table ) );
		// $sql_check_if_id_set = $wpdb->get_col( $sql, 0 );

		if ( get_option( 'gktpp_preconnect_status' ) === 'Yes' ) {

			if ( get_option( 'gktpp_reset_preconnect' ) === 'notset' ) {
				add_action( 'wp_footer', array( $this, 'gktpp_add_domain_js' ) );
				add_action( 'wp_ajax_gktpp_post_domain_names', array( $this, 'gktpp_post_domain_names' ) );
				add_action( 'wp_ajax_nopriv_gktpp_post_domain_names', array( $this, 'gktpp_post_domain_names' ) );

			} else {
				add_action( 'wp_head', array( $this, 'send_preconnect_hints' ), 1, 0 );
			}
		}
	}

	public function gktpp_add_domain_js() {
		wp_register_script( 'gktpp-find-domain-names', plugins_url( '/pre-party-browser-hints/js/find-external-domains.js' ), array( 'jquery' ), null, true );
		wp_enqueue_script( 'gktpp-find-domain-names' );

		wp_localize_script('gktpp-find-domain-names', 'ajax_object', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		) );
	}

	public function gktpp_post_domain_names() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			global $wpdb;
	     	$table = $wpdb->prefix . 'gktpp_table';
			$domains = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';

			if ( is_array( $domains ) ) {
				$sql1 = $wpdb->prepare( "DELETE FROM %1s WHERE ajax_domain = %d", array( $table, 1 ) );
				$wpdb->query( $sql1 );

				foreach ( $domains as $domain ) {
					$sql = $wpdb->prepare( "INSERT INTO %1s (url, hint_type, ajax_domain) VALUES ( %s, %s, %d )", array( $table, $domain, 'Preconnect', 1 ) );
					$wpdb->query( $sql );
				}
			}

			update_option( 'gktpp_reset_preconnect', 'set', 'yes' );
			wp_die();
		} else {
			wp_safe_redirect( get_permalink( wp_unslash( $_REQUEST['post_id'] ) ) );
			exit();
		}
	}

	public function send_preconnect_hints() {
		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_table';
		$sql = $wpdb->prepare( 'SELECT url FROM %1s', $table );
		$row = $wpdb->get_row( $sql, ARRAY_N, 0 );
		$domains = json_decode( $row[0] );

		// continue only if the sql row is empty and the ajax call was successful
		if ( ! empty( $sql ) && is_array( $domains ) ) {
			foreach ( $domains as $key ) {
				$crossorigin = ( 'fonts.googleapis.com' === $key ) ? ' crossorigin' : '';
				echo sprintf( '<link rel="preconnect" href="%1$s"%2$s>', $key, $crossorigin );
			}
		}
	}

	// public function update_ajax_info_on_post_save( $post_id ) {
	// 	if ( wp_is_post_revision( $post_id ) || ( empty( $post_id ) ) )
	// 		return;
	// 	global $wpdb;
	// 	$table = $wpdb->prefix . 'gktpp_ajax_domains';
	// 	$sql = $wpdb->prepare( 'DELETE FROM %1s', array( $table ) );
	// 	$delete_row = $wpdb->query( $sql );
	// }

}

$check_preconnect = new GKTPP_Ajax();
$check_preconnect->check_or_send_ajax();
