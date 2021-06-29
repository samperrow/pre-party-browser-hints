<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Preconnects {

	private $config;

	// tested
	public function __construct() {
		\add_action( 'wp_loaded', array( $this, 'init_controller' ), 10, 0 );
	}

	public function init_controller() {
		$reset_pro = \apply_filters( 'pprh_preconnects_do_reset_init', null );

		$this->config = array(
			'reset_pro'           => $reset_pro ?? null,
			'do_autoload_opt'     => ( 'true' === \get_option( 'pprh_preconnect_autoload' ) ),
			'allow_unauth_opt'    => ( 'true' === \get_option( 'pprh_preconnect_allow_unauth' ) ),
			'is_user_logged_in'   => \is_user_logged_in(),
			'preconnects_set_opt' => ( 'true' === \get_option( 'pprh_preconnect_set' ) )
		);

		if ( is_admin() ) {
			$this->load_ajax_callbacks( $this->config['allow_unauth_opt'] );
		} else {
			$this->initialize( $this->config );
		}

	}

	// tested
	public function initialize( array $config ):bool {
		$allow_user = $this->allow_user( $config['allow_unauth_opt'], $config['is_user_logged_in'] );

		if ( ! $allow_user ) {
			return false;
		}

		$perform_reset = $this->check_to_perform_reset( $config['do_autoload_opt'], $config['preconnects_set_opt'], $config['reset_pro'] );

		if ( false === $perform_reset ) {
			return false;
		}

		$this->load_ajax_callbacks( $config['allow_unauth_opt'] );
		\add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		return true;
	}

	public function check_to_perform_reset( bool $do_autoload_opt, bool $preconnects_set_opt, $reset_pro = null ):bool {
		if ( null === $reset_pro ) {
			$perform_reset = ( $do_autoload_opt && ! $preconnects_set_opt );
		} else {
			$perform_reset = $reset_pro;
		}

		return $perform_reset;
	}


	// both admin and client
	// tested
	public function load_ajax_callbacks( $allow_unauth_opt ) {
		$callback = 'pprh_post_domain_names';

		if ( $allow_unauth_opt ) {
			\add_action( "wp_ajax_nopriv_{$callback}", array( $this, $callback ) );		// not logged in
		}
		\add_action( "wp_ajax_{$callback}", array( $this, $callback ) );				// for logged in users
	}

	// tested
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

	// tested
	public function create_js_object( int $time ) {
		$js_arr = array(
			'hints'      => array(),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
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
			\check_ajax_referer('pprh_ajax_nonce', 'nonce');

			$pprh_data = Utils::json_to_array( $_POST['pprh_data'] );

			if ( ! empty( $pprh_data ) ) {
				$config = $this->config;
				$this->post_domain_names( $pprh_data, $config );
			}


			if ( ! PPRH_RUNNING_UNIT_TESTS ) {
				wp_die();
			}
		}
	}

	public function post_domain_names( array $pprh_data, array $config ):bool {
		$allow_unauth = $config['allow_unauth_opt'] ?? false;
		$user_logged_in = $config['is_user_logged_in'] ?? false;

		if ( $this->allow_user( $allow_unauth, $user_logged_in ) ) {
			return $this->do_ajax_callback( $pprh_data );
		}

		return false;
	}

	private function do_ajax_callback( $pprh_data ):bool {
		$results = array();
		$hints = $pprh_data['hints'] ?? array();
		$raw_hint_count = count( $hints );

		if ( $raw_hint_count > 0 ) {
			$results = $this->process_hints( $pprh_data );
		}

//		$this->update_options( $raw_hint_data );
		return ( $raw_hint_count === count( $results ) );
	}

	// tested
	public function process_hints( $hint_data ) {
		$dao_ctrl = new DAOController();
		$results = array();

		foreach ( $hint_data['hints'] as $new_hint ) {
			$new_hint['op_code'] = 0;
			$new_hint['auto_created'] = 1;
			$new_hint['hint_type'] = 'preconnect';

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