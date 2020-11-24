<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Preconnects {

	public $load_adv = false;

	public function __construct() {
		if ( 'true' === get_option( 'pprh_preconnect_allow_unauth' ) ) {
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

		$preconnects = array(
			'hints'      => array(),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => time(),
		);

		wp_register_script( 'pprh-find-domain-names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
		wp_localize_script( 'pprh-find-domain-names', 'pprh_data', $preconnects );
		wp_enqueue_script( 'pprh-find-domain-names' );
	}



	public function pprh_post_domain_names() {
		if ( wp_doing_ajax() ) {
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

		include_once PPRH_ABS_DIR . '/includes/create-hints.php';

		$dao = new DAO();
		define( 'CREATING_HINT', true );
		$create_hints = new Create_Hints();
		$dao->remove_prev_auto_preconnects();

		foreach ( $hint_data->hints as $hint ) {
			$obj = new \stdClass();
			$obj->url = $hint;
			$obj->hint_type = 'preconnect';
			$obj->auto_created = 1;
			$new_hint = $create_hints->init( $obj );
			$dao->create_hint( $new_hint );
		}

	}

	private function update_options() {
		update_option( 'pprh_preconnect_set', 'true' );
	}
}