<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( DAO::class ) ) {
	include_once 'DAO.php';
}

class DAOController extends DAO {


	// tested
	public function hint_controller( $raw_data ) {
		$create_hints = new CreateHints();
		$ctrl_data = array( 'op_code' => $raw_data['op_code'] );

		if ( ! empty( $raw_data['hint_ids'] ) ) {
			$ctrl_data['hint_ids'] = Utils::array_into_csv( $raw_data['hint_ids'] );
		}

		if ( ! empty( $raw_data['url'] ) && ! empty( $raw_data['hint_type'] ) ) {
			$pprh_hint = $create_hints->new_hint_controller( $raw_data );
			$ctrl_data['new_hint'] = $pprh_hint;
		}

//		elseif ( 2 === $raw_data['op_code'] ) {
//			$concat_ids = Utils::array_into_csv( $raw_data['hint_ids'] );
//			$ctrl_data['hint_ids'] = $concat_ids;
//		}

		if ( PPRH_TESTING ) {
			return $this->test_db_controller( $ctrl_data );
		}

		return $this->db_controller( $ctrl_data );
	}

	/*
	 * create_hint = 0
	 * update_hint = 1
	 * delete_hint = 2
	 * bulk_update = 3
	 */
	private function db_controller( $data ) {
		$code = $data['op_code'];
		$db_result = self::create_db_result( false, null, '', $code, null );

		if ( ! empty( $data['new_hint'] ) ) {
			$new_hint = $data['new_hint'];

			// duplicate hint exists
			if ( is_object( $new_hint ) ) {
				return $new_hint;
			}

			if ( 0 === $code ) {
				$db_result = $this->insert_hint( $new_hint );
			} elseif ( 1 === $code ) {
				$db_result = $this->update_hint( $new_hint, $data['hint_ids'] );
			}
		}

		elseif ( 2 === $code ) {
			$db_result = $this->delete_hint( $data['hint_ids'] );
		}

		elseif ( 3 === $code || 4 === $code ) {
			$db_result = $this->bulk_update( $data['hint_ids'], $code );
		}

		return $db_result;
	}


	protected function test_db_controller( $ctrl_data ) {
		if ( ! empty( $ctrl_data['new_hint'] ) ) {
			$test_result = self::create_db_result( true, '', '', $ctrl_data['op_code'], $ctrl_data['new_hint'] );
		} else {
			$test_result = self::create_db_result( true, $ctrl_data['hint_ids'], '', $ctrl_data['op_code'], null );
		}

		return $test_result;
	}

}