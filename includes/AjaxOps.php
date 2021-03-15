<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AjaxOps {

//	public $all_hints = array();

	public $testing = false;

	public function __construct() {
		add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
		$this->testing = ( defined( 'PPRH_TESTING' ) && PPRH_TESTING );
	}

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_table_nonce', 'nonce' );
			$this->init( $_POST['pprh_data'] );
			return true;
		}

		if ( ! $this->testing ) {
			wp_die();
		}
	}

	public function init( $pprh_data ) {
		$data = json_decode( wp_unslash( $pprh_data ), true );

		if ( is_array( $data ) ) {
			$action = $data['action'];
			$result = $this->handle_action( $data, $action );

			if ( is_object( $result ) ) {
				$on_pprh_admin = Utils::on_pprh_admin();
				$display_hints = new DisplayHints( $on_pprh_admin, false );
				$json = $display_hints->ajax_response( $result );

				if ( $this->testing ) {
					return $json;
				}

				wp_die( $json );
			}
		}
	}


	public function handle_action( $data, $action ) {
		$dao = new DAO();
		$concat_ids = Utils::array_into_csv( $data['hint_ids'] );

		if ( preg_match( '/create|update/', $action ) ) {
			$result = $this->create_update_hint( $data, $action );
		} elseif ( preg_match( '/enable|disable/', $action ) ) {
			$result = $dao->bulk_update( $concat_ids, $action );
		} elseif ( 'delete' === $action ) {
			$result = $dao->delete_hint( $concat_ids );
		}

//		TODO
//		elseif ( 'reset_single_post_preconnects' === $action ) {
//			$result = apply_filters( 'pprh_reset_single_post_preconnect', $data );
//		}
//		elseif ( 'reset_single_post_prerender' === $action ) {
//			$result = apply_filters( 'pprh_reset_single_post_prerender', $data );
//		}

		// TODO: if error, return generic error msg if no error from db is there.
		return ( is_object( $result ) ? $result : false );
	}

	protected function create_update_hint( $data, $action ) {
		$dao = new DAO();
		$create_hints = new CreateHints();

		if ( 'create' === $action ) {
			$pprh_hint = $create_hints->new_hint_controller( $data );
			$response = $dao->insert_hint( $pprh_hint );
		} else {
			$pprh_hint = $create_hints->create_hint( $data );
			$response = $dao->update_hint( $pprh_hint, $data['hint_ids'] );
		}

		return $response;
	}


}
