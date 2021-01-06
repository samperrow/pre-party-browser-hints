<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Preconnects {

	public function __construct() {
		if ( 'true' === get_option( 'pprh_preconnect_autoload' ) && 'false' === get_option( 'pprh_preconnect_set' ) ) {
			add_action( 'wp_loaded', array( $this, 'initialize' ) );
		}
	}

	public function initialize() {
		add_action( 'wp_ajax_pprh_post_domain_names', array( $this, 'pprh_post_domain_names' ) );				// for logged in users

		if ( 'true' === get_option( 'pprh_preconnect_allow_unauth' ) ) {										// not logged in
			$this->load();
			add_action( 'wp_ajax_nopriv_pprh_post_domain_names', array( $this, 'pprh_post_domain_names' ) );
		} elseif ( is_user_logged_in() ) {
			$this->load();
		}
	}

	public function load() {
		if ( ! is_admin() ) {
			$js_object = $this->set_js_object();
			wp_register_script( 'pprh-find-domain-names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
			wp_localize_script( 'pprh-find-domain-names', 'pprh_data', $js_object );
			wp_enqueue_script( 'pprh-find-domain-names' );
		}
	}

	public function set_js_object() {
		return array(
			'hints'      => array(),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => time()
		);
	}

	public function pprh_post_domain_names() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );

			$raw_hint_data = json_decode( wp_unslash( $_POST['pprh_data'] ), false );

			if ( count( $raw_hint_data->hints ) > 0 ) {
				$this->create_hint( $raw_hint_data );
			}

			$this->update_options();
			wp_die();
		} else {
			exit();
		}
	}

	public function create_hint( $hint_data ) {
		$dao = new DAO();
		$dao->remove_prev_auto_preconnects();

		foreach ( $hint_data->hints as $url ) {
			$hint_obj = Utils::create_hint_object( $url, 'preconnect', 1 );

			$hint_result = Utils::create_pprh_hint( $hint_obj );

			if ( is_array( $hint_result ) && is_object( $hint_result['new_hint'] ) ) {
				$dao->create_hint( $hint_result, null );
			}
		}
	}

	private function update_options() {
		update_option( 'pprh_preconnect_set', 'true' );
	}
}