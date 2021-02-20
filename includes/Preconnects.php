<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Preconnects {

	public $reset_data;

	public $is_admin;

	// tested
	public function __construct() {
//		add_action( 'wp_loaded', array( $this, 'initialize' ), 10, 0 );
		add_action( 'wp_loaded', array( $this, 'initialize' ), 10, 0 );

		$this->is_admin = is_admin();

		$this->reset_data = array(
			'autoload'        => get_option( 'pprh_preconnect_autoload' ),
			'allow_unauth'    => get_option( 'pprh_preconnect_allow_unauth' ),
			'preconnects_set' => get_option( 'pprh_preconnect_set' )
		);
	}

	// tested
	public function initialize() {
		$this->reset_data['reset_pro'] = apply_filters( 'pprh_preconnects_perform_reset', null);
		$perform_reset = $this->check_to_perform_reset( $this->reset_data );

		if ( false === $perform_reset ) {
			return false;
		}

		$allow_unauth = $this->reset_data['allow_unauth'];
		$user_logged_in = is_user_logged_in();

		$this->load_ajax_actions( $allow_unauth );
		$this->check_to_enqueue_scripts( $allow_unauth, $user_logged_in );
		return true;
	}

	// tested
	public function check_to_perform_reset( $reset_data ) {

		if ( null === $reset_data['reset_pro'] ) {
			$perform_reset = $this->perform_free_reset( $reset_data );
		} else {
			$perform_reset = $this->perform_pro_reset( $reset_data['reset_pro'] );
		}

		return $perform_reset;
	}

	// tested
	public function check_to_enqueue_scripts( $allow_unauth, $user_logged_in ) {
		$allow_unlogged_in_users = ( 'true' === $allow_unauth );
		$enqueue_scripts = ( $allow_unlogged_in_users || $user_logged_in );

		if ( $enqueue_scripts ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		return $enqueue_scripts;
	}

	// tested
	public function perform_free_reset( $reset_data ) {
		return ( 'true' === $reset_data['autoload'] && 'false' === $reset_data['preconnects_set'] );
	}

	// tested
	public function perform_pro_reset( $reset_pro ) {
		return ( ! empty( $reset_pro ) && $reset_pro['perform_reset'] );
	}

	// tested
	public function load_ajax_actions( $allow_unauth ) {
		$ajax_cb = 'pprh_post_domain_names';

		if ( 'true' === $allow_unauth ) {
			add_action( "wp_ajax_nopriv_$ajax_cb", array( $this, $ajax_cb ) );		// not logged in
		}
		add_action( "wp_ajax_$ajax_cb", array( $this, $ajax_cb ) );					// for logged in users
	}

	// tested
	public function enqueue_scripts() {
		if ( $this->is_admin ) {
			return false;
		}

		$js_object = $this->create_js_object();
		wp_register_script( 'pprh-find-domain-names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
		wp_localize_script( 'pprh-find-domain-names', 'pprh_data', $js_object );
		wp_enqueue_script( 'pprh-find-domain-names' );
	}

	// tested
	public function create_js_object() {
		$arr = array(
			'hints'      => array(),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => time()
		);
		return apply_filters( 'pprh_preconnects_append_js_object', $arr );
	}

	// tested
	public function pprh_post_domain_names() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );
			$results = array();
			$raw_hint_data = json_decode( wp_unslash( $_POST['pprh_data'] ), true );

			if ( count( $raw_hint_data['hints'] ) > 0 ) {
				$results = $this->process_hints( $raw_hint_data );
			}

//			$updated = apply_filters( 'pprh_preconnects_update_options', $raw_hint_data );
//			if ( is_object( $updated )) {
//				$this->update_options();
//			}

			if ( defined( 'PPRH_TESTING' ) && PPRH_TESTING ) {
				return json_encode($results);
			}

		}
		wp_die();
	}

	// tested
	public function process_hints( $hint_data ) {
		$dao = new DAO();
//		$dao->remove_prev_auto_preconnects();
		$results = array();

		$new_cols = apply_filters( 'pprh_preconnects_create_hint_array', $hint_data );

		foreach ( $hint_data['hints'] as $url ) {

			$hint_arr = $this->create_hint_array( $url, $new_cols );

			$hint = CreateHints::create_pprh_hint( $hint_arr );

			if ( is_array( $hint ) ) {
				$result = $dao->insert_hint( $hint );
				$results[] = $result->db_result['success'];
			}
		}
		return $results;
	}

	// tested
	public function create_hint_array( $url, $new_cols ) {
		$hint_arr['url'] = $url;
		$hint_arr['hint_type'] = 'preconnect';
		$hint_arr['auto_created'] = 1;

		if ( is_array( $new_cols ) ) {
			return array_merge( $hint_arr, $new_cols );
		}

		return $hint_arr;
	}

	private function update_options() {
		update_option( 'pprh_preconnect_set', 'true' );
	}

}