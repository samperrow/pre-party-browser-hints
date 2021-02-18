<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AjaxOps {

	public function __construct() {
		add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
    }

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_table_nonce', 'nonce' );
			$data = json_decode( wp_unslash( $_POST['pprh_data'] ), true );

			if ( is_array( $data ) ) {
				$action = $data['action'];
				$result = $this->handle_action( $data, $action );

				if ( is_object( $result ) ) {
					$on_pprh_admin = Utils::on_pprh_admin();
					$display_hints = new DisplayHints($on_pprh_admin);
					$json = $display_hints->ajax_response( $result );

					if ( defined( 'PPRH_TESTING' ) && PPRH_TESTING ) {
						return $json;
					}

					die( $json );
				}
			}
		}
		wp_die();
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

		return ( is_object( $result ) ? $result : false );
	}

	protected function create_update_hint( $data, $action ) {
		$dao = new DAO();
		$new_hint = CreateHints::create_pprh_hint( $data );

		if ( is_array( $new_hint ) ) {
			return ( 'create' === $action )
				? $dao->insert_hint( $new_hint )
				: $dao->update_hint( $new_hint, $data['hint_ids'] );
		}
		return false;
	}


}
