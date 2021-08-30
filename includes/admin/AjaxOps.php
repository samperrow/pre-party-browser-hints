<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AjaxOps {

	private $plugin_page;

	public function __construct( int $plugin_page ) {
		$this->plugin_page = $plugin_page;
	}

	public function set_actions() {
		\add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
	}

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && \wp_doing_ajax() ) {
			\check_ajax_referer( 'pprh_table_nonce', 'nonce' );
			$pprh_data = Utils::json_to_array( $_POST['pprh_data'] );

			$ajax_response = $this->priv_update_hints( $pprh_data  );
			$is_proper_response = $this->is_proper_response( $ajax_response );

			$response = ( $is_proper_response ) ? json_encode( $ajax_response, true ) : 'Error parsing request. Please contact support to resolve this issue.';
			\wp_die( $response );
		}
	}

	public function is_proper_response( array $ajax_response ):bool {
		$values_set = ( isset( $ajax_response['rows'], $ajax_response['pagination'], $ajax_response['column_headers'], $ajax_response['total_pages'], $ajax_response['result'] ) );
		return ( $values_set && is_object( $ajax_response['result'] ) );
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
			$display_hints = new DisplayHints( true, $this->plugin_page );
			return $display_hints->ajax_response( $db_result );
		}

		return array();
	}

	private function handle_action( array $data ):\stdClass {
		if ( isset( $data['post_id'], $data['action'] ) ) {
			$db_result = \apply_filters( 'pprh_apply_ajaxops_action', $data['post_id'], $data['action'] );
		} else {
			$dao_ctrl  = new DAOController();
			$db_result = $dao_ctrl->hint_controller( $data );
		}

		if ( ! is_object( $db_result ) ) {
			$db_result = DAO::create_db_result( false,0, 0, null );
		}

		return $db_result;
	}

}
