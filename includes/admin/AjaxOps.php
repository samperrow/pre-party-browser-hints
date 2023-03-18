<?php

namespace PPRH;

use PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AjaxOps {

	private $on_plugin_page;

	public function __construct( bool $on_plugin_page ) {
		$this->on_plugin_page = $on_plugin_page;
	}

	public function set_actions() {
		\add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
	}

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && \wp_doing_ajax() ) {
			\check_ajax_referer( 'pprh_table_nonce', 'nonce' );

			$pprh_data          = Utils::json_to_array( $_POST['pprh_data'] );
			$ajax_response      = $this->update_hints( $pprh_data );
			$is_proper_response = $this->is_proper_response( $ajax_response );

			$response = ( $is_proper_response ) ? $ajax_response : array( 'error' => "Error parsing request: $pprh_data. Please contact support to resolve this issue." );
			$json = json_encode( $response );
			\wp_die( $json );
		}
	}

	public function update_hints( array $pprh_data ):array {
		$db_result = $this->handle_action( $pprh_data );

		if ( is_object( $db_result ) ) {
			$display_hints = new DisplayHints( true, $this->on_plugin_page );
			return $display_hints->ajax_response( $db_result );
		}

		return array();
	}

	private function handle_action( array $data ):\stdClass {
		if ( isset( $data['post_id'], $data['action'] ) ) {
//			$db_result = \apply_filters( 'pprh_apply_ajaxops_action', $data['post_id'], $data['action'] );
			$db_result = $this->post_reset_action( $data['post_id'], $data['action'] );
		} else {
			$hint_ctrl  = new HintController();
			$db_result = $hint_ctrl->hint_ctrl_init( $data );
		}

		if ( ! is_object( $db_result ) ) {
			$db_result = DAO::create_db_result( false,0, 0, array() );
		}

		return $db_result;
	}

	public function is_proper_response( array $ajax_response ):bool {
		$values_set = ( isset( $ajax_response['rows'], $ajax_response['pagination'], $ajax_response['column_headers'], $ajax_response['total_pages'], $ajax_response['result'] ) );
		return ( $values_set && is_object( $ajax_response['result'] ) );
	}



	public function post_reset_action( $post_id, $action ):\stdClass {
		if ( str_contains( $action, 'preconnect' ) ) {
			$hint_type = 'preconnect';
			$op_code = 5;
		} elseif ( str_contains( $action, 'preload' ) ) {
			$hint_type = 'preload';
			$op_code = 6;
		} elseif ( str_contains( $action, 'prerender' ) ) {
			$hint_type = 'prerender';
			$op_code = 7;
		}

		if ( isset( $hint_type, $op_code ) ) {
			$settings_save = new \PPRH\Settings\SettingsSave( false );
			$new_hint_data = $settings_save->reset_autoset_hints( $hint_type, $post_id, $op_code );
			return \PPRH\DAO::create_db_result( '', $op_code, 0, array() );
		}

		$msg = "error resetting this post's $hint_type value. Please either clear your cache and try again, or report the issue to support.";
		return \PPRH\DAO::create_db_result( $msg, 0, 0, array() );
	}

}
