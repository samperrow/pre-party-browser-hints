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
		$option        = get_option( 'pprh_autoload_preconnects' );

		if ( 'true' === $option ) {
			$ajax_nonce = wp_create_nonce( 'pprh_ajax_nonce' );

			$data_to_retrieve = array(
				'url'       => array(),
				'hint_type' => 'preconnect',
				'nonce'     => $ajax_nonce,
			);

			wp_register_script( 'pprh-find-domain-names', plugins_url( PPRH_PLUGIN_FILENAME . '/admin/js/find-external-domains.js' ), null, PPRH_VERSION, true );
			wp_localize_script( 'pprh-find-domain-names', 'hint_data', $data_to_retrieve );
			wp_enqueue_script( 'pprh-find-domain-names' );
		}
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
