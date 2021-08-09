<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Preconnects {

	private $allow_user = false;

	public function __construct() {
		\add_action( 'wp_loaded', array( $this, 'initialize_ctrl' ), 10, 0 );
	}

	public function initialize_ctrl() {
		$reset_pro                 = \apply_filters( 'pprh_preconnects_do_reset_init', null );
		$reset_preconnects_option  = ( 'false' === \get_option( 'pprh_preconnect_set' ) );
		$allow_unauth_users_option = ( 'true' === \get_option( 'pprh_preconnect_allow_unauth' ) );

		$this->initialize( $allow_unauth_users_option, $reset_preconnects_option, $reset_pro );
	}

	public function initialize( bool $allow_unauth_users_option, bool $reset_preconnects_option, $reset_pro ):bool {
		$reset_preconnects = ( is_null( $reset_pro ) ) ? $reset_preconnects_option : $reset_pro;
		$perform_reset = $this->check_to_perform_reset( $allow_unauth_users_option, \is_user_logged_in(), $reset_preconnects );

		if ( ! $perform_reset ) {
			return false;
		}

		$this->load_ajax_callbacks( $allow_unauth_users_option );
		\add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		return true;
	}


	public function check_to_perform_reset( bool $allow_unauth_users_option, bool $is_user_logged_in, bool $reset_preconnects ):bool {
		$this->allow_user = ( $allow_unauth_users_option || $is_user_logged_in );
		return ( $this->allow_user && $reset_preconnects );
	}


	public function load_ajax_callbacks( bool $allow_unauth_opt ) {
		$callback = 'pprh_post_domain_names';

		if ( $allow_unauth_opt ) {
			\add_action( "wp_ajax_nopriv_{$callback}", array( $this, $callback ) );		// not logged in
		}
		\add_action( "wp_ajax_{$callback}", array( $this, $callback ) );				// for logged in users
	}

	public function enqueue_scripts() {
		$time = time();
		$js_object = $this->create_js_object( $time );

		\wp_register_script( 'pprh_create_hints_js', PPRH_REL_DIR . 'js/create-hints.js', null, PPRH_VERSION, true );
		\wp_register_script( 'pprh-find-domain-names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
		\wp_localize_script( 'pprh-find-domain-names', 'pprh_data', $js_object );
		\wp_enqueue_script( 'pprh_create_hints_js' );
		\wp_enqueue_script( 'pprh-find-domain-names' );
	}

	public function create_js_object( int $time ) {
		$js_arr = array(
			'hints'      => array(),
			'nonce'      => \wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => \admin_url() . 'admin-ajax.php',
			'start_time' => $time
		);

		if ( isset( $this->config['reset_pro'] ) ) {
			$js_arr = \apply_filters( 'pprh_preconnects_append_hint_object', $js_arr );
		}

		return $js_arr;
	}






	public function pprh_post_domain_names() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			\check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );

			if ( $this->allow_user ) {
				$this->private_post_domain_names();
			}

			wp_die();
		}
	}

	private function private_post_domain_names() {
		$this->post_domain_names( $_POST['pprh_data'] );
	}


	public function post_domain_names( $pprh_data ):bool {
		if ( ! is_array( $pprh_data ) ) {
			$pprh_data = Utils::json_to_array( $pprh_data );
		}

		$hints   = $pprh_data['hints'] ?? array();
		$success = false;

		if ( Utils::isArrayAndNotEmpty( $hints ) && Utils::isArrayAndNotEmpty( $pprh_data ) ) {
			$results = $this->get_hint_results( $pprh_data );
			$success = ( count( $hints ) === count( $results ) );
		}

		$this->update_options( $pprh_data );

		return $success;
	}

	// tested
	public function get_hint_results( array $hint_data ):array {
		$dao_ctrl = new DAOController();
		$results = array();

		foreach ( $hint_data['hints'] as $new_hint ) {
			$new_hint['op_code']      = 0;
			$new_hint['hint_type']    = 'preconnect';
			$new_hint['auto_created'] = 1;

			if ( isset( $hint_data['post_id'] ) ) {
				$new_hint['post_id'] = $hint_data['post_id'];
			}

			$result = $dao_ctrl->hint_controller( $new_hint );

			if ( is_object( $result ) && isset( $result->db_result['status'] ) && $result->db_result['status'] ) {
				$results[] = $result;
			}
		}

		return $results;
	}

	private function update_options( array $raw_hint_data ):bool {
		$updated = \apply_filters( 'pprh_preconnects_update_options', $raw_hint_data );

		if ( is_array( $updated ) || false === $updated ) {
			return Utils::update_option( 'pprh_preconnect_set', 'true' );
		}

		return false;
	}

}
