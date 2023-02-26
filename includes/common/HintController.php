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
			$hint_ids = ( ! empty( $raw_data['hint_ids'] ) ) ? Utils::array_to_csv( $raw_data['hint_ids'] ) : '';
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

		return $result;
	}

	private function insert_or_update_hint( int $op_code, array $raw_data, $hint_ids = null ):\stdClass {
		$pprh_hint = $this->new_hint_ctrl( $raw_data, $op_code );

		if ( ! empty( $pprh_hint ) ) {
			$result = ( 0 === $op_code ) ? $this->insert_hint( $pprh_hint ) : $this->update_hint( $pprh_hint, $hint_ids );
		} else {
			$result = self::create_db_result( 'Failed to update hint.', false, $op_code, array() );
		}

		return $result;
	}


	public function new_hint_ctrl( array $raw_hint, int $op_code ):array {
		$hint_builder   = new HintBuilder();
		$candidate_hint = $hint_builder->create_pprh_hint( $raw_hint );
		$pprh_hint      = array();

		if ( ! empty( $candidate_hint ) ) {
			$hint_ids        = ( ! empty( $raw_hint['hint_ids'] ) ? $raw_hint['hint_ids'] : '' );
			$duplicate_hints = $this->get_duplicate_hints( $candidate_hint, $op_code, $hint_ids );
			$pprh_hint       = $this->handle_duplicate_hints( $candidate_hint, $duplicate_hints );
		}

		return $pprh_hint;
	}

	public function handle_duplicate_hints( array $candidate_hint, array $duplicate_hints ):array {
		$candidate_hint = $this->resolve_duplicate_hints( $candidate_hint, $duplicate_hints );


		if ( empty( $duplicate_hints ) ) {
			return $candidate_hint;
		}

		return array();
	}

	public function resolve_duplicate_hints( array $candidate_hint, array $duplicate_hints ) {
//		if ( empty( $duplicate_hints ) ) {
//			return $candidate_hint;
//		}

//		$candidate_hint_post_id = $candidate_hint['post_id'];
//		$is_duplicate_hint_present = $this->is_duplicate_hint_present( $duplicate_hints, $candidate_hint_post_id );

//		if ( $is_duplicate_hint_present ) {
//			return array();
//		}

//		$remove_dups = $this->resolve_duplicate_hints_ctrl( $candidate_hint, $duplicate_hints );

		if ( 'prerender' !== $candidate_hint['hint_type'] ) {
			$this->delete_duplicate_hints( $duplicate_hints );
			$candidate_hint['post_id'] = 'global';
		}

		return $candidate_hint;
	}

//	public function is_duplicate_hint_present( array $dup_hints, string $candidate_post_id ):bool {
//
//		foreach ( $dup_hints as $dup_hint ) {
//			$dup_hint_post_id = $dup_hint['post_id'] ?? '';
//			$existing_dup_global_hint = ( 'global' === $dup_hint_post_id );
//			$existing_dup_post_hint = ( $dup_hint_post_id === $candidate_post_id );
//
//			if ( $existing_dup_global_hint || $existing_dup_post_hint ) {
//				return true;
//			}
//		}
//
//		return false;
//	}

	private function delete_duplicate_hints( array $duplicate_hints ) {
		$hint_id_arr = $this->get_hint_ids( $duplicate_hints );
		$hint_ids    = Utils::array_to_csv( $hint_id_arr );

		$data = array(
			'op_code'  => 2,
			'hint_ids' => $hint_ids
		);

		$result = $this->hint_ctrl_init( $data );
		return ( 'success' === $result->db_result['status'] );
	}

	public function get_hint_ids( array $hints ): array {
		$hint_ids = array();

		foreach ( $hints as $hint ) {
			if ( ! empty( $hint['id'] ) ) {
				$hint_ids[] = $hint['id'];
			}
		}

		return $hint_ids;
	}

}
