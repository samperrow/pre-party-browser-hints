<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Auto_Preconnects {

	public $load_adv = false;

	public function __construct( $load_adv ) {

		if ( 'true' === get_option( 'pprh_prec_allow_unauth' ) ) {
			$this->load();
			add_action( 'wp_ajax_nopriv_pprh_post_domain_names', array( $this, 'pprh_post_domain_names' ) );
		} elseif ( is_user_logged_in() ) {
			$this->load();
		}

		if ( $load_adv ) {
			$this->load_adv = $load_adv;
//			do_action( 'pprh_load_auto_prec_child' );
		}
	}

	public function load() {
		add_action( 'wp_footer', array( $this, 'initialize' ) );
		add_action( 'wp_ajax_pprh_post_domain_names', array( $this, 'pprh_post_domain_names' ) );
	}

	public function initialize() {

		$preconnects = array(
			'hints'     => array(),
			'nonce'     => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url' => admin_url() . 'admin-ajax.php',
		);

		if ( $this->load_adv ) {
			$preconnects = apply_filters( 'pro_perform_reset', $preconnects );
		}

		if ( ! empty( $preconnects ) ) {
			wp_register_script( 'pprh_find_domain_names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
			wp_localize_script( 'pprh_find_domain_names', 'pprh_data', $preconnects );
			wp_enqueue_script( 'pprh_find_domain_names' );
		}
	}

	public function pprh_post_domain_names() {
		if ( wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );
			define( 'CREATING_HINT', true );
			include_once PPRH_ABS_DIR . '/includes/class-pprh-utils.php';
			include_once PPRH_ABS_DIR . '/includes/class-pprh-create-hints.php';

			$hint_arr  = array();
			$data = json_decode( wp_unslash( $_POST['pprh_data'] ), false );

			foreach ( $data->hints as $hint ) {
				$obj = new \stdClass();
				$obj->url = $hint;
				$obj->hint_type = 'preconnect';
				$obj->auto_created = true;
				$obj = apply_filters( 'pprh_prec_verify', $obj, $data );
				array_push($hint_arr, $obj );
			}

			$this->remove_prev_auto_preconnects();
			new Create_Hints( $hint_arr );
			$this->update_options();
			apply_filters( 'pprh_prec_update', $data );
			wp_die();
		} else {
			exit;
		}
	}

	private function update_options() {
		update_option( 'pprh_prec_preconnects_set', 'true' );
	}

	private function remove_prev_auto_preconnects() {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		$wpdb->query(
			$wpdb->prepare( "DELETE FROM $table WHERE auto_created = %d AND hint_type = %s", 1, 'preconnect' )
		);
	}
}
