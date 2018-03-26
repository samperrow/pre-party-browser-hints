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
		wp_register_script( 'gktpp-find-domain-names', plugins_url( '/pre-party-browser-hints/js/find-external-domains.js' ), null, null, true );
		wp_enqueue_script( 'gktpp-find-domain-names' );

		wp_localize_script('gktpp-find-domain-names', 'ajax_object', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		) );
	}

	public function post_domain_names() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			global $wpdb;
	     	$table = $wpdb->prefix . 'gktpp_table';
			$domains = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
			$crossorigins = isset( $_POST['crossorigin'] ) ? wp_unslash( $_POST['crossorigin'] ) : '';

			if ( is_array( $domains ) ) {

				$wpdb->delete( $table, array( 'ajax_domain' => 1 ), array( '%s' ) );

				foreach ( $domains as $key => $domain ) {

					$wpdb->insert( $table, array(
											'url' => $domain,
											'hint_type' => 'Preconnect',
											'ajax_domain' => 1,
											'crossorigin' => $crossorigins[$key] ),

											array(
												'%s', '%s', '%d', '%s' ) );

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
