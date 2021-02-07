<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

new Preconnects();

class Preconnects {

	public $reset_pro = null;

	public function __construct() {
		// wp_loaded
		add_action( 'wp_loaded', array( $this, 'asdf' ), 9, 0 );
		add_action( 'wp_loaded', array( $this, 'initialize' ), 10, 0 );
	}

	public function asdf() {
		do_action('pprh_load_preconnects_child');
		$this->reset_pro = apply_filters('pprh_perform_reset', null);
	}

	public function initialize() {
		if ( ! $this->load_auto_preconnects( $this->reset_pro ) ) {
			return false;
		}

		$allow_unauth = get_option( 'pprh_preconnect_allow_unauth' );
		$this->load_ajax_actions( $allow_unauth );

		if ( ! is_admin() && ( 'true' === $allow_unauth || is_user_logged_in() ) ) {
			$this->load_js_files();
		}
	}

	public function load_auto_preconnects( $reset_pro ) {
		$autoload = get_option( 'pprh_preconnect_autoload' );
		$preconnects_set = get_option( 'pprh_preconnect_set' );
		$load_free = ( 'true' === $autoload && 'false' === $preconnects_set );

		$load_pro = ( null !== $reset_pro && ! empty( $reset_pro['reset'] ) && $reset_pro['reset'] );

		return ( (null === $reset_pro && $load_free) || $load_pro );
	}

	public function load_ajax_actions( $allow_unauth ) {
		$ajax_cb = 'pprh_post_domain_names';

		if ( 'true' === $allow_unauth ) {
			add_action( "wp_ajax_nopriv_$ajax_cb", array( $this, $ajax_cb ) );		// not logged in
		}
		add_action( "wp_ajax_$ajax_cb", array( $this, $ajax_cb ) );					// for logged in users
	}

	public function load_js_files() {
		$js_object = $this->create_js_object();
		wp_register_script( 'pprh-find-domain-names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
		wp_localize_script( 'pprh-find-domain-names', 'pprh_data', $js_object );
		wp_enqueue_script( 'pprh-find-domain-names' );
	}

	public function create_js_object() {
		$arr = array(
			'hints'      => array(),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => time()
		);
		return apply_filters( 'pprh_append_js_object', $arr );
	}

	public function pprh_post_domain_names() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );
			$results = array();
			$raw_hint_data = json_decode( wp_unslash( $_POST['pprh_data'] ), false );

			if ( count( $raw_hint_data->hints ) > 0 ) {
				$results = $this->process_hints( $raw_hint_data );
			}

			if ( PPRH_IS_PRO_PLUGIN_ACTIVE ) {
				apply_filters( 'pprh_update_options', $raw_hint_data );
			} else {
				$this->update_options();
			}

			if ( defined( 'PPRH_TESTING' ) && PPRH_TESTING ) {
				return json_encode($results, JSON_THROW_ON_ERROR);
			}

		}
		wp_die();
	}

	public function process_hints( $hint_data ) {
		$dao = new DAO();
		$dao->remove_prev_auto_preconnects();
		$results = array();

		foreach ( $hint_data->hints as $url ) {
			$hint_arr = array(
				'url'          => $url,
				'hint_type'    => 'preconnect',
				'auto_created' => 1
			);

			$hint_arr['post_url'] = ( ! empty( $hint_data->post_url ) ? $hint_data->post_url : '' );
			$hint_arr['post_id'] = ( ! empty( $hint_data->post_id ) ? $hint_data->post_id : '' );

			$hint = CreateHints::create_pprh_hint( $hint_arr );

			if ( is_array( $hint ) ) {
				$res = $dao->create_hint( $hint );
				$results[] = $res->db_result['success'];
			}
		}
		return $results;
	}

	private function update_options() {
		update_option( 'pprh_preconnect_set', 'true' );
	}
}