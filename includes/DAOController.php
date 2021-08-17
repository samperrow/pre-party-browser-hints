<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( DAO::class ) ) {
	include_once 'DAO.php';
}

class DAOController extends DAO {

	public function hint_controller( $raw_data ):\stdClass {
		$op_code  = (int) $raw_data['op_code'];
		$hint_ids = ( ! empty( $raw_data['hint_ids'] ) ) ? Utils::array_into_csv( $raw_data['hint_ids'] ) : '';
		return $this->db_controller( $op_code, $raw_data, $hint_ids );
	}

	/**
	 * insert_hint = 0
	 * update_hint = 1
	 * delete_hint = 2
	 * bulk_update = 3
	 */
	private function db_controller( int $op_code, array $raw_data, $hint_ids = null ):\stdClass {
		$db_result = self::create_db_result( false, $op_code, 0, null );

		if ( 0 === $op_code || 1 === $op_code ) {
			$db_result = $this->insert_or_update_hint( $op_code, $raw_data, $hint_ids );
		} elseif ( 2 === $op_code ) {
			$db_result = self::delete_hint( $hint_ids );
		} elseif ( 3 === $op_code || 4 === $op_code ) {
			$db_result = $this->bulk_update( $hint_ids, $op_code );
		}

		return $db_result;
	}

	public function insert_or_update_hint( int $op_code, $raw_data, $hint_ids = null ):\stdClass {
		$create_hints  = new CreateHints();
		$new_hint_data = $create_hints->new_hint_ctrl( $raw_data );
		$response      = self::create_db_result( false, $op_code, 0, null );

		// duplicate hint exists, or error.
		if ( empty( $new_hint_data ) ) {
			$response = \PPRH\DAO::create_db_result( false, 0, 1, null );
		} elseif ( isset( $new_hint_data['url'], $new_hint_data['hint_type'] ) ) {
			$response = ( 0 === $op_code ) ? $this->insert_hint( $new_hint_data ) : $this->update_hint( $new_hint_data, $hint_ids );
		}

		return $response;
	}

}
