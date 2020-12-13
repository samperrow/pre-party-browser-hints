<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax_Ops {

	public $response = array(
		'query' => array(),
	);

	public function __construct() {
		add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
    }

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {

			check_ajax_referer( 'pprh_table_nonce', 'val' );
			$data = json_decode( wp_unslash( $_POST['pprh_data'] ), false );

			if ( is_object( $data ) ) {
				$action = $data->action;
				$this->response['query'] = $this->handle_action( $data, $action );
				$display_hints = new Display_Hints();
				$display_hints->ajax_response( $this->response );
			}

			wp_die();
		}
	}

	private function handle_action( $data, $action ) {
		$wp_db = null;
		$dao = new DAO();
		if ( preg_match( '/create|update|delete/', $action ) ) {
			$wp_db = $dao->{$action . '_hint'}( $data );
		} elseif ( preg_match( '/enabled|disabled/', $action ) ) {
			$wp_db = $dao->bulk_update( $data, $action );
		}
		return $wp_db;
	}

}
