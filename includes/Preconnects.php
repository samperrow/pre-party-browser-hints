<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Preconnects {

	public $config;

	public function __construct() {
		\add_action( 'wp_loaded', array( $this, 'init_controller' ), 10, 0 );
	}

	public function init_controller() {
		$reset_pro = \apply_filters( 'pprh_preconnects_do_reset_init', false );

		$this->config = array(
			'reset_pro'              => $reset_pro,
			'allow_unauth_opt'       => ( 'true' === \get_option( 'pprh_preconnect_allow_unauth' ) ),
			'is_user_logged_in'      => \is_user_logged_in(),
			'preconnects_set_option' => ( 'true' === \get_option( 'pprh_preconnect_set' ) )
		);

		if ( \is_admin() ) {
			$this->load_ajax_callbacks( $this->config['allow_unauth_opt'] );
		} else {
			$this->initialize( $this->config );
		}

	}

	public function initialize( array $config ):bool {
		$allow_user = $this->allow_user( $config['allow_unauth_opt'], $config['is_user_logged_in'] );

		if ( ! $allow_user ) {
			return false;
		}

		$perform_reset = $this->check_to_perform_reset( $config['preconnects_set_option'], $config['reset_pro'] );

		if ( false === $perform_reset ) {
			return false;
		}

		$this->load_ajax_callbacks( $config['allow_unauth_opt'] );
		\add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		return true;
	}

	public function check_to_perform_reset( bool $preconnects_set_option, bool $reset_pro ):bool {
		return ( ! $preconnects_set_option || $reset_pro );
	}


	public function load_ajax_callbacks( $allow_unauth_opt ) {
		$callback = 'pprh_post_domain_names';

		if ( $allow_unauth_opt ) {
			\add_action( "wp_ajax_nopriv_{$callback}", array( $this, $callback ) );		// not logged in
		}
		\add_action( "wp_ajax_{$callback}", array( $this, $callback ) );				// for logged in users
	}

	public function enqueue_scripts() {
		if ( is_admin() ) {
			return;
		}

		$time = time();
		$js_object = $this->create_js_object( $time );

		wp_register_script( 'pprh_create_hints_js', PPRH_REL_DIR . 'js/create-hints.js', null, PPRH_VERSION, true );
		wp_register_script( 'pprh-find-domain-names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
		wp_localize_script( 'pprh-find-domain-names', 'pprh_data', $js_object );
		wp_enqueue_script( 'pprh_create_hints_js' );
		wp_enqueue_script( 'pprh-find-domain-names' );
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

	// tested
	// Returns false if the user is not logged in, and the admin chooses not to allow unauthenticated users from settings hints.
	public function allow_user( bool $allow_unauth_opt = true, $is_user_logged_in = null ) {
		return ( $allow_unauth_opt || $is_user_logged_in );
	}




	public function pprh_post_domain_names() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			\check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );
			$this->post_domain_names_ctrl();
			wp_die();
		}
	}

	private function post_domain_names_ctrl() {
		$config = $this->config ?? array( 'allow_unauth_opt' => false, 'is_user_logged_in' => false );
		return $this->post_domain_names( $_POST['pprh_data'], $config );
	}



	public function post_domain_names( $pprh_data, array $config ):bool {
		if ( ! is_array( $pprh_data ) ) {
			$pprh_data = Utils::json_to_array( $pprh_data );
		}

		$success      = false;
		$allow_unauth = $this->allow_user( $config['allow_unauth_opt'], $config['is_user_logged_in'] );
		$hints        = $pprh_data['hints'] ?? array();

		if ( $allow_unauth && Utils::isArrayAndNotEmpty( $hints ) && Utils::isArrayAndNotEmpty( $pprh_data ) ) {
			$results = $this->get_hint_results( $pprh_data );
			$this->update_options( $pprh_data );
			$success = ( count( $hints ) === count( $results ) );
		}

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

	private function update_options( $raw_hint_data ) {
		$updated = \apply_filters( 'pprh_preconnects_update_options', $raw_hint_data );

		if ( is_array( $updated ) ) {
			Utils::update_option( 'pprh_preconnect_set', 'true' );
		}
	}

}
