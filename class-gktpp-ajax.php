<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Ajax {

	public function __construct() {
		add_action( 'wp_footer', array( $this, 'add_domain_js' ) );
		add_action( 'wp_ajax_post_domain_names', array( $this, 'post_domain_names' ) );
		add_action( 'wp_ajax_nopriv_post_domain_names', array( $this, 'post_domain_names' ) );
	}

	public function add_domain_js() {
		wp_register_script( 'gktpp-find-domain-names', plugins_url( '/pre-party-browser-hints/js/find-external-domains.js' ), null, '1.5.3', true );
		wp_enqueue_script( 'gktpp-find-domain-names' );

		wp_localize_script('gktpp-find-domain-names', 'ajax_object', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		) );
	}

	public function post_domain_names() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			global $wpdb;
	     	$table = $wpdb->prefix . 'gktpp_table';
			$urls = isset( $_POST['urls'] ) ? wp_unslash( $_POST['urls'] ) : '';

			if ( is_array( $urls ) ) {

				$wpdb->delete( $table, array( 'ajax_domain' => 1 ), array( '%s' ) );

				foreach ( $urls as $key => $url ) {

					$gktpp_insert_to_db = new GKTPP_Insert_To_DB();
					$gktpp_insert_to_db->get_attributes( $url );
					

					$as_attr = $gktpp_insert_to_db->as_attr;
					$type_attr = $gktpp_insert_to_db->type_attr;
					$crossorigin = $gktpp_insert_to_db->crossorigin;

					$gktpp_insert_to_db->create_str( $url, 'Preconnect', $as_attr, $type_attr, $crossorigin );

					$header_string = $gktpp_insert_to_db->header_str;
					$head_string = $gktpp_insert_to_db->head_str;

					$wpdb->insert( $table, array(
											'url' => $url,
											'hint_type' => 'Preconnect',
											'ajax_domain' => 1,
											'as_attr' => $as_attr,
											'type_attr' => $type_attr,
											'crossorigin' => $crossorigin,
											'header_string' => $header_string,
											'head_string' => $head_string ),

											array(
												'%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' ) );

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

new GKTPP_Ajax();
