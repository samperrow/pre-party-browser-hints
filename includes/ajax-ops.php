<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax_Ops {

	public $result = array(
		'response' => array(),
	);

	public function __construct() {
		add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
    }

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			do_action( 'pprh_load_ajax_ops_child' );

			check_ajax_referer( 'pprh_table_nonce', 'val' );
			$data = json_decode( wp_unslash( $_POST['pprh_data'] ), false );

			if ( is_object( $data ) ) {
				$action = $data->action;

				if ( preg_match( '/create|update/', $action ) ) {
					$this->result = $this->create_hint( $data, $action );
				} elseif (preg_match( '/enabled|disabled|delete/', $action )) {
					$this->result['response'] = $this->handle_action( $data, $action );
				}
				elseif ( 'reset_single_post_preconnects' === $action ) {
					$this->result['response'] = apply_filters( 'pprh_reset_single_post_preconnect', $data );
				}
				// TODO
//				elseif ( 'reset_single_post_prerenders' === $action ) {
//					$this->result['response'] = apply_filters( 'pprh_reset_single_post_preconnect', $data );
//				}

				$display_hints = new Display_Hints();
				$display_hints->ajax_response( $this->result );
			}

			wp_die();
		}
	}

	private function handle_action( $data, $action ) {
		$dao = new DAO();
		$wp_db = null;

		if ( preg_match( '/enabled|disabled/', $action ) ) {
			$wp_db = $dao->bulk_update( $data, $action );
		} elseif ( 'delete' === $action ) {
			$wp_db = $dao->delete_hint( $data->hint_ids );
		}
		return $wp_db;
	}

	private function create_hint( $data, $action ) {
		$dao = new DAO();

		$hint_result = Utils::create_pprh_hint( $data );
		$this->result['new_hint'] = $hint_result;

		if ( $hint_result['response']['success'] && is_object( $hint_result['new_hint'] ) ) {
			$hint_result['response'] = $dao->{$action . '_hint'}($hint_result, $data->hint_id);
		}

		return $hint_result;
	}


}
