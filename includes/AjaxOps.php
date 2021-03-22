<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AjaxOps {

	public $testing = false;

	public function __construct() {
		add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
		$this->testing = ( defined( 'PPRH_TESTING' ) && PPRH_TESTING );
	}

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_table_nonce', 'nonce' );
			$pprh_data = json_decode( wp_unslash( $_POST['pprh_data'] ), true );
			$this->init( $pprh_data );
			return true;
		}

		if ( ! $this->testing ) {
			wp_die();
		}
	}

	public function init( $pprh_data ) {
		if ( is_array( $pprh_data ) ) {
			$db_result = $this->handle_action( $pprh_data );

			if ( is_object( $db_result ) ) {
				$on_pprh_admin = Utils::on_pprh_admin();
				$display_hints = new DisplayHints( $on_pprh_admin, false );
				$json = $display_hints->ajax_response( $db_result );

				if ( $this->testing ) {
					return $db_result;
				}

				wp_die( $json );
			}
		}
	}


	private function handle_action( $data ) {
		$dao_ctrl = new DAOController();
		$result = $dao_ctrl->hint_controller( $data );

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

}
