<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Preconnects {

	public $load_adv = false;

	public function __construct() {
		do_action( 'pprh_load_preconnects_child' );

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

//		if ( $this->load_adv ) {
			$preconnects = apply_filters( 'pprh_perform_reset', $preconnects );
//		}

		if ( is_array( $preconnects ) )  {
			wp_register_script( 'pprh-find-domain-names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
			wp_localize_script( 'pprh-find-domain-names', 'pprh_data', $preconnects );
			wp_enqueue_script( 'pprh-find-domain-names' );
		}

	}

	public function pprh_post_domain_names() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );

			$raw_hint_data = json_decode( wp_unslash( $_POST['pprh_data'] ), false );

			if ( count( $raw_hint_data->hints ) > 0 ) {
				$this->create_hint( $raw_hint_data );
			}

			$this->update_options( $raw_hint_data );
			wp_die();
		} else {
			exit();
		}
	}

	public function create_hint( $hint_data ) {
		$dao = new DAO();
//		$dao->remove_prev_auto_preconnects();

		foreach ( $hint_data->hints as $url ) {
			$obj = (object) array(
				'url'          => $url,
				'hint_type'    => 'preconnect',
				'auto_created' => 1,
			);

			$hint_obj = apply_filters( 'pprh_create_hint_array', $obj, $hint_data );

			$hint_result = Utils::create_pprh_hint( $hint_obj );

			if ( is_array( $hint_result ) && is_object( $hint_result['new_hint'] ) ) {
				$dao->create_hint( $hint_result, null );
			}
		}
	}

	public function update_options( $data ) {
//		update_option( 'pprh_preconnect_set', 'true' );
		apply_filters( 'pprh_preconnect_update_options', $data );

		if ( defined( 'PPRH_PRO_ABS_DIR' ) ) {
			include_once PPRH_PRO_ABS_DIR . 'includes/preconnects-child.php';
			$pc = new Preconnects_Child();
			$pc->update_options($data);
		}

	}
}