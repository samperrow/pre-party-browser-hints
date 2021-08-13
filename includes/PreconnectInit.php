<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PreconnectInit {

	private $allow_user = false;
	private $doing_ajax = false;

	public $reset_pro;

	public function load_actions() {
		\add_action( 'wp_loaded', array( $this, 'initialize' ), 10, 0 );
	}

	public function initialize() {
		$allow_unauth_option       = ( 'true' === \get_option( 'pprh_preconnect_allow_unauth' ) );
		$user_logged_in            = \is_user_logged_in();
		$reset_preconnects_option  = ( 'false' === \get_option( 'pprh_preconnect_set' ) );
		$this->reset_pro           = \apply_filters( 'pprh_preconnect_check_to_reset', null );

		$this->allow_user = ( $allow_unauth_option || $user_logged_in );
		$this->doing_ajax = \wp_doing_ajax();

		$this->initialize_ctrl( $this->allow_user, $reset_preconnects_option, $this->doing_ajax, $this->reset_pro );
	}

	public function initialize_ctrl( bool $allow_user, bool $reset_preconnects_option, bool $doing_ajax, $reset_pro ):bool {
		$reset_preconnects = ( is_null( $reset_pro ) ) ? $reset_preconnects_option : $reset_pro;
		$perform_reset     = ( $allow_user && $reset_preconnects );

		if ( $doing_ajax ) {
			$this->load_ajax_callbacks( $allow_user );
			return false;
		}

		if ( ! $perform_reset ) {
			return false;
		}

		\add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		return true;
	}


	public function load_ajax_callbacks( bool $allow_user ) {
		$callback = 'pprh_post_domain_names';

		if ( $allow_user ) {
			\add_action( "wp_ajax_nopriv_{$callback}", array( $this, $callback ) );		// not logged in
		}
		\add_action( "wp_ajax_{$callback}", array( $this, $callback ) );				// for logged in users
	}

	public function enqueue_scripts() {
		$js_object = $this->create_js_object( time() );

		\wp_register_script( 'pprh_create_hints_js', PPRH_REL_DIR . 'js/create-hints.js', null, PPRH_VERSION, true );
		\wp_register_script( 'pprh-find-domain-names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
		\wp_localize_script( 'pprh-find-domain-names', 'pprh_data', $js_object );
		\wp_enqueue_script( 'pprh_create_hints_js' );
		\wp_enqueue_script( 'pprh-find-domain-names' );
	}

	public function create_js_object( int $time ):array {
		$js_arr = array(
			'hints'      => array(),
			'nonce'      => \wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => \admin_url() . 'admin-ajax.php',
			'start_time' => $time
		);

		if ( $this->reset_pro ) {
			$js_arr = \apply_filters( 'pprh_preconnects_append_hint_object', $js_arr );
		}

		return $js_arr;
	}


	public function pprh_post_domain_names() {
		if ( isset( $_POST['pprh_data'] ) && $this->doing_ajax && $this->allow_user ) {
			\check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );

			$preconnect_response = new PreconnectResponse();
			$results = $preconnect_response->protected_post_domain_names();

			if ( PPRH_IN_DEV ) {
				echo \wp_json_encode( $results );
			}

			\wp_die();
		}
	}

}
