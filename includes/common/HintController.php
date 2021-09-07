<?php

namespace PPRH;

use PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( DAO::class ) ) {
	include_once 'DAO.php';
}

class HintController extends DAO {

	public function hint_ctrl_init( array $raw_data ):\stdClass {
		$op_code = (int) $raw_data['op_code'];

		if ( 0 <= $op_code && $op_code < 5 ) {
			$hint_ids = ( ! empty( $raw_data['hint_ids'] ) ) ? Utils::array_into_csv( $raw_data['hint_ids'] ) : '';
			return $this->db_controller( $op_code, $raw_data, $hint_ids );
		}

		return new \stdClass();
	}

	/**
	 * insert_hint = 0
	 * update_hint = 1
	 * delete_hint = 2
	 * bulk_update = 3
	 */
	private function db_controller( int $op_code, array $raw_data, $hint_ids = null ):\stdClass {
		if ( $op_code < 2 ) {
			$result = $this->insert_or_update_hint( $op_code, $raw_data, $hint_ids );
		} elseif ( $op_code === 2 ) {
			$result = $this->delete_hint( $hint_ids );
		} else {
			$result = $this->bulk_update( $hint_ids, $op_code );
		}

		$new_hint = $result['new_hint'] ?? array();
		return self::create_db_result( $result['success'], $op_code, 0, $new_hint );
	}

	private function insert_or_update_hint( int $op_code, array $raw_data, $hint_ids = null ):array {
		$pprh_hint = $this->new_hint_ctrl( $raw_data, $op_code );
		$result = array( 'success' => false );

		if ( ! empty( $pprh_hint ) ) {
			if ( 0 === $op_code ) {
				$result = $this->insert_hint( $pprh_hint );
			} else {
				$result = $this->update_hint( $pprh_hint, $hint_ids );
			}
		}

		return $result;
	}


	public function new_hint_ctrl( array $raw_hint, int $op_code ):array {
		$hint_builder   = new HintBuilder();
		$candidate_hint = $hint_builder->create_pprh_hint( $raw_hint );
		$pprh_hint      = array();

		if ( ! empty( $candidate_hint ) ) {
			$hint_ids        = ( ! empty( $raw_hint['hint_ids'] ) ? $raw_hint['hint_ids'] : '' );
			$duplicate_hints = $this->get_duplicate_hints( $candidate_hint['url'], $candidate_hint['hint_type'], $op_code, $hint_ids );
			$pprh_hint       = $this->handle_duplicate_hints( $candidate_hint, $duplicate_hints );
		}

		return $pprh_hint;
	}

	/**
	 *
	 * @param int   $op_code
	 * @param array $candidate_hint
	 * @param array $duplicate_hints
	 * @return array
	 */
	public function handle_duplicate_hints( array $candidate_hint, array $duplicate_hints ):array {
		if ( isset( $candidate_hint['post_id'] ) ) {
			$candidate_hint = \apply_filters( 'pprh_resolve_duplicate_hints', $candidate_hint, $duplicate_hints );
		} elseif ( ! empty( $duplicate_hints ) ) {
			return array();
		}

		return $candidate_hint;
	}

}
