<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AjaxOps {

//	public function __construct() {
//		\add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
//	}

	public function set_actions() {
		\add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
	}

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_table_nonce', 'nonce' );
			$pprh_data = Utils::json_to_array( $_POST['pprh_data'] );
			$db_result = '';

			if ( ! empty( $pprh_data ) ) {
				$db_result = $this->init( $pprh_data );
			}

			if ( PPRH_RUNNING_UNIT_TESTS ) {
				return true;
			}

			wp_die( $db_result );
		}
	}

	public function init( $pprh_data ) {
		if ( is_array( $pprh_data ) ) {
			$db_result = $this->handle_action( $pprh_data );

			if ( is_object( $db_result ) ) {
				$display_hints = new DisplayHints( true );
				$json = $display_hints->ajax_response( $db_result );
				return $this->return_values( $json, $db_result );
			}
		}

		return false;
	}

	private function return_values( $json, \stdClass $db_result ) {
		return ( PPRH_RUNNING_UNIT_TESTS ) ? $db_result : $json;
	}

	private function handle_action( array $data ):\stdClass {

		if ( isset( $data['action'] ) ) {
			$db_result = Utils::apply_pprh_filters( 'pprh_apply_ajaxops_action', array( $data['post_id'], $data['action'] ) );
		} else {
			$dao_ctrl = new DAOController();
			$db_result = $dao_ctrl->hint_controller( $data );
		}

		return $db_result;
	}
	


}
