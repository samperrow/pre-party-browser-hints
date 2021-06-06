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
			$pprh_data = json_decode( wp_unslash( $_POST['pprh_data'] ), true );
			$json = $this->init( $pprh_data );

			if ( PPRH_TESTING ) {
				return true;
			}

			wp_die( $json );
		}
	}

	public function init( $pprh_data ) {
		if ( is_array( $pprh_data ) ) {
			$db_result = $this->handle_action( $pprh_data );

			if ( is_object( $db_result ) ) {
				$display_hints = new DisplayHints();
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
		} elseif ( 'reset_single_post_prerender' === $data['action'] ) {
			$result = \apply_filters( 'pprh_reset_and_create_auto_prerender_hint', $data );
		}

		// TODO: if error, return generic error msg if no error from db is there.
		return ( is_object( $result ) ? $result : false );
	}

}
