<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Preconnects {

	public $is_admin;

	public $config;

	public $testing = false;

	// tested
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'init_controller' ), 10, 0 );

		$this->testing = ( defined( 'PPRH_TESTING' ) && PPRH_TESTING );

		$this->config = array(
			'is_admin' => is_admin(),
			'reset_data' => array(
				'autoload'        => get_option( 'pprh_preconnect_autoload' ),
				'allow_unauth'    => get_option( 'pprh_preconnect_allow_unauth' ),
				'preconnects_set' => get_option( 'pprh_preconnect_set' )
			)
		);
	}

	public function init_controller() {
		$this->config['reset_data']['reset_pro'] = apply_filters( 'pprh_preconnects_do_reset_init', null );
		return $this->initialize( $this->config );
	}

	// tested
	public function initialize( $args ) {
		$perform_reset = $this->check_to_perform_reset( $args['reset_data'] );
		$allow_unauth = $args['reset_data']['allow_unauth'];
		$this->load_ajax_actions( $allow_unauth );

		if ( false === $perform_reset ) {
			return false;
		}

		$allow_unauth_users = $this->allow_unauth_users( $allow_unauth );
		$this->check_to_enqueue_scripts( $allow_unauth_users );
		return true;
	}

	private function check_to_perform_reset( $reset_data ) {
		if ( empty( $reset_data['reset_pro'] ) || null === $reset_data['reset_pro'] ) {
			$perform_reset = $this->perform_reset( $reset_data );
		} else {
			$perform_reset = $this->perform_pro_reset( $reset_data['reset_pro'] );
		}

		return $perform_reset;
	}

	// tested
	public function check_to_enqueue_scripts( $allow_unauth_users ) {
		if ( $allow_unauth_users ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		return $allow_unauth_users;
	}

	private function perform_reset( $reset_data ) {
		return ( 'true' === $reset_data['autoload'] && 'false' === $reset_data['preconnects_set'] );
	}

	private function perform_pro_reset( $reset_pro ) {
		return ( ! empty( $reset_pro ) && $reset_pro['perform_reset'] );
	}

	// both admin and client
	// tested
	public function load_ajax_actions( $allow_unauth ) {
		$ajax_cb = 'pprh_post_domain_names';

		if ( 'true' === $allow_unauth ) {
			add_action( "wp_ajax_nopriv_{$ajax_cb}", array( $this, $ajax_cb ) );		// not logged in
		}
		add_action( "wp_ajax_{$ajax_cb}", array( $this, $ajax_cb ) );					// for logged in users
	}

	// tested
	public function enqueue_scripts() {
		if ( $this->config['is_admin'] ) {
			return false;
		}

		$js_object = $this->create_js_object();

		wp_register_script( 'pprh_create_hints_js', PPRH_REL_DIR . 'js/create-hints.js', null, PPRH_VERSION, true );
		wp_register_script( 'pprh-find-domain-names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
		wp_localize_script( 'pprh-find-domain-names', 'pprh_data', $js_object );
		wp_enqueue_script( 'pprh_create_hints_js' );
		wp_enqueue_script( 'pprh-find-domain-names' );
	}

	// tested
	public function create_js_object() {
		$js_arr = array(
			'hints'      => array(),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'hint_type'  => 'preconnect',
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => time()
		);

		if ( ! empty( $this->config['reset_data']['reset_pro'] ) ) {
			$js_arr = apply_filters( 'pprh_preconnects_append_hint_object', $js_arr, $this->config['reset_data']['reset_pro'] );
		}

		return $js_arr;
	}

	// tested
	// Returns false if the user is not logged in, and the admin chooses not to allow unauthenticated users from settings hints.
	public function allow_unauth_users( $allow_unauth = 'true', $user_logged_in = null ) {
		if ( null === $user_logged_in ) {
			$user_logged_in = is_user_logged_in();
		}

		$allow_unauth_bool = ( 'true' === $allow_unauth );
		return ( $allow_unauth_bool || $user_logged_in );
	}




	public function pprh_post_domain_names() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			check_ajax_referer('pprh_ajax_nonce', 'nonce');
			$allow_unauth = $this->config['reset_data']['allow_unauth'];

			if ( $this->allow_unauth_users( $allow_unauth ) ) {
				return $this->do_ajax_callback( $_POST['pprh_data'] );
			}

			return false;
		}

		if ( ! $this->testing ) {
			wp_die();
		}
	}

	private function do_ajax_callback( $pprh_data ) {
		$raw_hint_data = json_decode( wp_unslash( $pprh_data ), true );
		$results = array();
		$raw_hint_count = count( $raw_hint_data['hints'] );

		if ( $raw_hint_count > 0 ) {
			$results = $this->process_hints( $raw_hint_data );
		}

		$this->update_options( $raw_hint_data );
		return ( $raw_hint_count === count( $results ) );
	}

	// tested
	public function process_hints( $hint_data ) {
		$dao_ctrl = new DAOController();
		$results = array();

		foreach ( $hint_data['hints'] as $new_hint ) {
			$new_hint['op_code'] = 0;

			if ( isset( $hint_data['post_id'] ) ) {
				$new_hint['post_id'] = $hint_data['post_id'];
			}

			$result = $dao_ctrl->hint_controller( $new_hint );

			if ( is_object( $result ) ) {
				$results[] = $result;
			}
		}

		return $results;
	}

	private function update_options( $raw_hint_data ) {
		$updated = apply_filters( 'pprh_preconnects_update_options', $raw_hint_data );

		if ( is_array( $updated ) ) {
			update_option( 'pprh_preconnect_set', 'true' );
		}
	}

}