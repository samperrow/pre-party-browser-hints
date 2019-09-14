<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PPRH_Ajax {

	public $reset_global_prec_str     = 'pprh_reset_global_preconnects';
	public $reset_home_preconnect_str = 'pprh_reset_home_preconnect';
	public $reset_prec_meta           = 'pprh_reset_preconnects';

	public $global_prec_opt     = '';
	public $reset_home_prec_opt = '';
	public $post_id             = '';

	public function __construct() {
		$this->global_prec_opt     = get_option( $this->reset_global_prec_str );
		$this->reset_home_prec_opt = get_option( $this->reset_home_preconnect_str );

		if ( 'true' === get_option( 'pprh_allow_unauth' ) ) {
			$this->load();
			add_action( 'wp_ajax_nopriv_pprh_post_domain_names', array( $this, 'pprh_post_domain_names' ) );
		} elseif ( is_user_logged_in() ) {
			$this->load();
		}
	}

	public function load() {
		add_action( 'wp_footer', array( $this, 'initialize' ) );
		add_action( 'wp_ajax_pprh_post_domain_names', array( $this, 'pprh_post_domain_names' ) );
	}


	public function initialize() {
		$this->post_id = $this->get_post_id();
		$option        = $this->get_option_val();

		if ( 'true' === $option && ! is_null( $this->post_id ) ) {
			$ajax_nonce = wp_create_nonce( 'pprh_ajax_nonce' );

			$data_to_retrieve = array(
				'post_id'   => $this->post_id,
				'url'       => array(),
				'hint_type' => 'preconnect',
				'nonce'     => $ajax_nonce,
			);

			wp_register_script( 'pprh-find-domain-names', plugins_url( PPRH_PLUGIN_FILENAME . '/admin/js/find-external-domains.js' ), null, PPRH_VERSION, true );
			wp_localize_script( 'pprh-find-domain-names', 'hint_data', $data_to_retrieve );
			wp_enqueue_script( 'pprh-find-domain-names' );
		}
	}

	public function get_post_id() {
		global $wp_query;
		return ( is_page() || is_single() ) ? (string) $wp_query->queried_object_id : ( is_home() ? '0' : null );
	}

	public function get_option_val() {
		$reset_post_preconnect = ( '0' !== $this->post_id )
			? get_post_meta( $this->post_id, $this->reset_prec_meta, true )
			: $this->reset_home_prec_opt;
		return ( 'true' === $this->global_prec_opt || preg_match( '/(true|^$)/', $reset_post_preconnect ) ) ? 'true' : ( is_home() ? $this->reset_home_prec_opt : 'false' );
	}

	public function pprh_post_domain_names() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );
			include_once PPRH_PLUGIN_DIR . '/class-pprh-misc.php';
			include_once PPRH_PLUGIN_DIR . '/class-pprh-create-hints.php';
			new PPRH_Create_Hints( 'pprh_ajax_nonce', 'nonce' );
			wp_die();
		} else {
			exit();
		}
	}
}
