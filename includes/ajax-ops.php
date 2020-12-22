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
//		do_action( 'pprh_load_ajax_ops_child' );
    }

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {

			check_ajax_referer( 'pprh_table_nonce', 'val' );
			$data = json_decode( wp_unslash( $_POST['pprh_data'] ), false );

			if ( is_object( $data ) ) {
				$action = $data->action;
				$this->response = $this->handle_action( $data, $action );
				$display_hints = new Display_Hints();
				$display_hints->ajax_response( $this->response );
			}

			wp_die();
		}
	}

	private function handle_action( $data, $action ) {
		$wp_db = null;
		$dao = new DAO();

		if ( 'create' === $action ) {
			$hint_result = Utils::create_pprh_hint( $data );

			if ( $hint_result['success'] && is_object( $hint_result['new_hint'] ) ) {
				$wp_db = $dao->create_hint( $hint_result['new_hint'] );
			}
		} elseif ( 'update' === $action ) {
			$pprh_hint = Utils::create_pprh_hint( $data );
			$wp_db = $dao->update_hint( $pprh_hint, $data->hint_id );
		} elseif ( preg_match( '/enabled|disabled/', $action ) ) {
			$wp_db = $dao->bulk_update( $data, $action );
		} elseif ( 'delete' === $action ) {
			$wp_db = $dao->delete_hint( $data );
		}
		return $wp_db;
	}

}
