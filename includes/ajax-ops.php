<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax_Ops {

//	public $result = array(
//		'response' => array(),
//	);

	public function __construct() {
		add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
    }

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			do_action( 'pprh_load_ajax_ops_child' );

			check_ajax_referer( 'pprh_table_nonce', 'nonce' );
			$data = json_decode( wp_unslash( $_POST['pprh_data'] ), true );

			if ( is_array( $data ) ) {
				$dao = new DAO();

				$action = $data['action'];
				$result = (object) array(
					'new_hint'  => array(),
					'db_result' => array(),
				);

				$concat_ids = Utils::array_into_csv( $data['hint_ids'] );

				if ( preg_match( '/create|update/', $action ) ) {
					$new_hint = Create_Hints::create_pprh_hint( $data );
					$result = ( is_array( $new_hint ) ? $dao->{$action . '_hint'}($new_hint, $data['hint_ids'] ) : $new_hint );
				} elseif ( preg_match( '/enable|disable/', $action ) ) {
					$result = $dao->bulk_update( $concat_ids, $action );
				} elseif ( 'delete' === $action ) {
					$result = $dao->delete_hint( $concat_ids );
				}
//				elseif ( 'reset_single_post_preconnects' === $action ) {
//					$result = apply_filters( 'pprh_reset_single_post_preconnect', $data );
//				}
				// TODO
//				elseif ( 'reset_single_post_prerenders' === $action ) {
//					$this->result['response'] = apply_filters( 'pprh_reset_single_post_preconnect', $data );
//				}


				$display_hints = new Display_Hints();
				$json = $display_hints->ajax_response( $result );

				if ( defined( 'PPRH_TESTING' ) && PPRH_TESTING ) {
					return $json;
				} else {
					die( $json );
				}
			}
			wp_die();
		}
	}


}
