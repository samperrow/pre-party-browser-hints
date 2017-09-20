<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Ajax {

	public function check_or_send_ajax() {

		if ( ( get_option( 'gktpp_preconnect_status' ) === 'Yes' ) && ( get_option( 'gktpp_reset_preconnect' ) === 'notset') ) {
			add_action( 'wp_footer', array( $this, 'gktpp_add_domain_js' ) );
			add_action( 'wp_ajax_gktpp_post_domain_names', array( $this, 'gktpp_post_domain_names' ) );
			add_action( 'wp_ajax_nopriv_gktpp_post_domain_names', array( $this, 'gktpp_post_domain_names' ) );
		}
	}

	public function gktpp_add_domain_js() {
		wp_register_script( 'gktpp-find-domain-names', plugins_url( '/pre-party-browser-hints/js/find-external-domains.js' ), null, null, true );
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

				$sql1 = $wpdb->delete( $table, array( 'ajax_domain' => 1 ), array( '%s' ) );
				$wpdb->query( $sql1 );

				foreach ( $domains as $domain ) {

					$sql = $wpdb->insert( $table,
									  array(
										  'url' => $url,
										  'hint_type' => $hint_type ),
								  	  array( '%s', '%s' ) );

					$sql = $wpdb->insert( $table, array(
											'url' => $domain,
											'hint_type' => 'Preconnect',
											'ajax_domain' => 0 ),
											array(
												'%s', '%s', '%d' ) );
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
}

$check_preconnect = new GKTPP_Ajax();
$check_preconnect->check_or_send_ajax();
