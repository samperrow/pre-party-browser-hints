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
		$op_code = (int) $raw_data['op_code'];
		$ctrl_data = array();

		if ( ! empty( $raw_data['hint_ids'] ) ) {
			$ctrl_data['hint_ids'] = Utils::array_into_csv( $raw_data['hint_ids'] );
		}

		if ( ! empty( $raw_data['url'] ) && ! empty( $raw_data['hint_type'] ) ) {
			$ctrl_data = $create_hints->new_hint_ctrl( $raw_data );
		}

		if ( PPRH_TESTING ) {
			return $this->test_db_controller( $op_code, $ctrl_data );
		}

		return $this->db_controller( $op_code, $ctrl_data );
	}

	/*
	 * create_hint = 0
	 * update_hint = 1
	 * delete_hint = 2
	 * bulk_update = 3
	 */
	private function db_controller( $op_code, $new_hint_data ) {
		$db_result = self::create_db_result( false, null, '', $op_code, null );

		// duplicate hint exists, or error.
		if ( is_object( $new_hint_data ) ) {
			return $new_hint_data;
		}

		if ( is_array( $new_hint_data ) ) {

			if ( 0 === $op_code ) {
				$db_result = $this->insert_hint( $new_hint_data );
			} elseif ( 1 === $op_code ) {
				$db_result = $this->update_hint( $new_hint_data, $new_hint_data['hint_ids'] );
			}
		}

		elseif ( 2 === $op_code ) {
			$db_result = $this->delete_hint( $new_hint_data['hint_ids'] );
		}

		elseif ( 3 === $op_code || 4 === $op_code ) {
			$db_result = $this->bulk_update( $new_hint_data['hint_ids'], $op_code );
		}

		return $db_result;
	}


	protected function test_db_controller( $op_code, $ctrl_data ) {
		if ( ! empty( $ctrl_data['new_hint'] ) ) {
			$test_result = self::create_db_result( true, '', '', $op_code, $ctrl_data['new_hint'] );
		} else {
			$test_result = self::create_db_result( true, $ctrl_data['hint_ids'] ?? '', '', $op_code, null );
		}

		return $test_result;
	}

}