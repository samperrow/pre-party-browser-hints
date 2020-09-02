<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {

	public $hash;

	public function __construct() {

		if ( 'true' === get_option( 'pprh_allow_unauth' ) ) {
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
		$hash = uniqid( '', false );
		update_option( 'pprh_preconnect_hash', $hash );

		$preconnects = array(
			'hints'     => array(),
			'nonce'     => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url' => admin_url() . 'admin-ajax.php',
			'hash'      => $hash,
		);

		wp_register_script( 'pprh-find-domain-names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
		wp_localize_script( 'pprh-find-domain-names', 'pprh_data', $preconnects );
		wp_enqueue_script( 'pprh-find-domain-names' );
	}

	private function remove_prev_auto_preconnects() {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		$wpdb->query(
			$wpdb->prepare( "DELETE FROM $table WHERE auto_created = %d AND hint_type = %s", 1, 'preconnect' )
		);
	}

	public function pprh_post_domain_names() {
		if ( wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );

			$arr  = array();
			$data = json_decode( wp_unslash( $_POST['pprh_data'] ), false );
			$prev_hash = get_option( 'pprh_preconnect_hash' );
			$recd_hash = ( ! empty( $data->hash ) ? Utils::pprh_strip_non_alphanums( $data->hash ) : 'false' );

			if ( $prev_hash !== $recd_hash ) {
				wp_die();
			}

			define( 'CREATING_HINT', true );
			include_once PPRH_ABS_DIR . '/includes/class-pprh-utils.php';
			include_once PPRH_ABS_DIR . '/includes/class-pprh-create-hints.php';

			foreach ( $data->hints as $hint ) {
				$obj = new \stdClass();
				$obj->url = $hint;
				$obj->hint_type = 'preconnect';
				$obj->auto_created = true;
				array_push($arr, $obj );
			}

			$this->remove_prev_auto_preconnects();
			new Create_Hints( $arr );
			$this->update_options();
			wp_die();
		} else {
			exit();
		}
	}

	private function update_options() {
		update_option( 'pprh_preconnects_set', 'true' );
		update_option( 'pprh_preconnect_hash', '' );
	}
}
