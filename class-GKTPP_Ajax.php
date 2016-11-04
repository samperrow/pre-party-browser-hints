<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Ajax {

	public function check_preconnect_status() {
		if ( get_option( 'gktpp_preconnect_status' ) === 'Yes' ) {
			add_action( 'wp_head', array( $this, 'check_or_send_ajax' ), 1, 0 );
	          add_action( 'wp_ajax_gktpp_post_domain_names', array( $this, 'gktpp_post_domain_names' ) );
	          add_action( 'wp_ajax_nopriv_gktpp_post_domain_names', array( $this, 'gktpp_post_domain_names' ) );
			add_action( 'wp_head', array( $this, 'send_preconnect_hints' ), 1, 0 );
			add_action( 'save_post', array( $this, 'update_ajax_info_on_post_save' ) );
		}
	}

	public function check_or_send_ajax() {
		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_ajax_domains';
		$this_page_id = get_the_ID();

		if ( $this_page_id ) {
			$sql = $wpdb->prepare( 'SELECT pageOrPostID FROM %1s WHERE pageOrPostID = %2s', array( $table, $this_page_id ) );

			$sql_check_if_id_set = $wpdb->get_col( $sql, 0 );

			if ( empty( $sql_check_if_id_set ) ) {
				add_action( 'wp_footer', array( $this, 'gktpp_add_domain_js' ) );
			}
		}
	}

	public function gktpp_add_domain_js() {
		wp_register_script( 'gktpp-find-domain-names', plugin_dir_url( __FILE__ ) . 'js/find-external-domains.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'gktpp-find-domain-names' );

		wp_localize_script('gktpp-find-domain-names', 'ajax_object', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'homeURL'  => home_url(),
			'pagePostID'   => get_the_ID(),
		) );
	}

	public function gktpp_post_domain_names() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			global $wpdb;
	     	$table = $wpdb->prefix . 'gktpp_ajax_domains';

			$page_id = wp_unslash( sanitize_text_field( $_POST['pageOrPostIDValue'] ) );
			settype( $page_id, 'int' );

			$page_or_post_id = isset( $page_id ) ? $page_id : '';
			$domain_names = isset( $_POST['data'] ) ? json_encode( wp_unslash( $_POST['data'] ) ) : '';

			$sql = $wpdb->prepare( "INSERT INTO %1s (pageOrPostID, domain) VALUES ( '%d', '%s' )", array( $table, $page_or_post_id, $domain_names ) );

			$wpdb->query( $sql );

			wp_die();
		} else {
			wp_safe_redirect( get_permalink( wp_unslash( $_REQUEST['post_id'] ) ) );
			exit();
		}
	}

	public function send_preconnect_hints() {
		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_ajax_domains';
		$present_id = get_the_ID();

		$sql = $wpdb->prepare( 'SELECT domain FROM %1s WHERE pageOrPostID = %2s', $table, $present_id );
		$row = $wpdb->get_row( $sql, ARRAY_N, 0 );

		$domains = json_decode( $row[0] );

		if ( ! empty( $row ) && ( is_array( $domains ) ) ) {		// continue only if the sql row is empty and the ajax call was successful
			foreach ( $domains as $key ) {
				$crossorigin = ( 'fonts.googleapis.com' === $key ) ? ' crossorigin' : '';
				echo sprintf( '<link rel="preconnect" href="%1$s"%2$s>', $key, $crossorigin );
			}
		}
	}

	public function update_ajax_info_on_post_save( $post_id ) {
		if ( wp_is_post_revision( $post_id ) || ( empty( $post_id ) ) )
			return;

		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_ajax_domains';

		$sql = $wpdb->prepare( 'DELETE FROM %1s WHERE pageOrPostID = %2s', array( $table, $post_id ) );
		$delete_row = $wpdb->query( $sql );
	}

}

$check_preconnect = new GKTPP_Ajax();
$check_preconnect->check_preconnect_status();
