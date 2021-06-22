<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AjaxOps {

	public function __construct() {
		\add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
	}

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_table_nonce', 'nonce' );
			$pprh_data = Utils::json_to_array( $_POST['pprh_data'] );
			$db_result = '';

			if ( false !== $pprh_data ) {
				$db_result = $this->init( $pprh_data );
			}

			if ( PPRH_TESTING ) {
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

	private function return_values( $json, $db_result ) {
		return ( PPRH_TESTING ) ? $db_result : $json;
	}

	private function handle_action( $data ) {
		$dao_ctrl = new DAOController();
		$result = $dao_ctrl->hint_controller( $data );

		if ( isset( $data['action'] ) && 'reset_single_post_preconnects' === $data['action'] ) {
			$result = \apply_filters( 'pprh_reset_post_preconnect', null );
		}

		elseif ( 'set_single_prerender_hint' === $data['action'] ) {
			$result = \apply_filters( 'pprh_single_prerender_config', $data );
		}

		$op_code = (int) $data['op_code'];
		$error = DAO::create_db_result( false, '', 'Error updating hints. Please try aggain or contact support.', $op_code, null );
		// TODO: if error, return generic error msg if no error from db is there.
		if ( is_object( $result ) ) {
			return $result;
		} else {
			return $error;
		}
	}

}
