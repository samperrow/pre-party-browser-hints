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
			$data = json_decode( wp_unslash( $_POST['pprh_data'] ), true );

			if ( is_array( $data ) ) {
				$action = $data['action'];

				if ( preg_match( '/create|update/', $action ) ) {
					$this->result = $this->create_hint( $data, $action );
				} elseif (preg_match( '/enabled|disabled|delete/', $action ) ) {
					$this->result = $this->handle_action( $data, $action );
				}
				elseif ( 'reset_single_post_preconnects' === $action ) {
					$this->result = apply_filters( 'pprh_reset_single_post_preconnect', $data );
				}
				// TODO
//				elseif ( 'reset_single_post_prerenders' === $action ) {
//					$this->result['response'] = apply_filters( 'pprh_reset_single_post_preconnect', $data );
//				}

				$display_hints = new Display_Hints();
				$json = $display_hints->ajax_response( $this->result );

				if ( defined( 'PPRH_TESTING' ) && PPRH_TESTING ) {
					return $json;
				} else {
					die( $json );
				}
			}
			wp_die();
		}
	}

	private function handle_action( $data, $action ) {
		$dao = new DAO();
		$wp_db = null;

		if ( ! is_array( $data['hint_ids'] ) || count( $data['hint_ids'] ) === 0 ) {
			return false;
		}

		$concat_ids = Utils::array_into_csv( $data['hint_ids'] );

		if ( preg_match( '/enabled|disabled/', $action ) ) {
			$wp_db = $dao->bulk_update( $concat_ids, $action );
		} elseif ( 'delete' === $action ) {
			$wp_db = $dao->delete_hint( $concat_ids );
		}
		return $wp_db;
	}

	private function create_hint( $data, $action ) {
		$dao = new DAO();
		$create_hints = new Create_Hints();
		$new_hint = $create_hints->create_hint( $data );

		return ( is_array( $new_hint ) ) ? $dao->{$action . '_hint'}($new_hint, $data['hint_id'] ) : false;
	}

}
