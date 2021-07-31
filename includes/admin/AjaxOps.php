<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AjaxOps {

	private $on_pprh_page;

	public function __construct( int $on_pprh_page ) {
		$this->on_pprh_page = $on_pprh_page;
	}

	public function set_actions() {
		\add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
	}

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			\check_ajax_referer( 'pprh_table_nonce', 'nonce' );
			$pprh_data = Utils::json_to_array( $_POST['pprh_data'] );

			$ajax_response = $this->priv_update_hints( $pprh_data  );
			$response_json = json_encode( $ajax_response, true );
			wp_die( $response_json );
		}
	}

	private function priv_update_hints( $pprh_data ):array {
		if ( Utils::isArrayAndNotEmpty( $pprh_data ) ) {
			return $this->update_hints( $pprh_data );
		}

		return array();
	}

	public function update_hints( array $pprh_data ):array {
		$db_result = $this->handle_action( $pprh_data );

		if ( is_object( $db_result ) ) {
			$display_hints = new DisplayHints( true, $this->on_pprh_page );
			return $display_hints->ajax_response( $db_result );
		}

		return array();
	}

	private function handle_action( array $data ):\stdClass {
		if ( isset( $data['action'] ) ) {
			$db_result = \apply_filters( 'pprh_apply_ajaxops_action', $data['post_id'], $data['action'] );
		} else {
			$dao_ctrl = new DAOController();
			$db_result = $dao_ctrl->hint_controller( $data );
		}

		return $db_result;
	}

}
