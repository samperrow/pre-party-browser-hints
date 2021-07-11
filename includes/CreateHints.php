<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CreateHints {

	public function create_hint( $raw_hint ) {
		if ( empty( $raw_hint['url'] ) || empty( $raw_hint['hint_type'] ) ) {
			return false;
		}

		$current_user = wp_get_current_user()->display_name;

		$new_hint = array(
			'url'          => Utils::clean_url( $raw_hint['url'] ),
			'hint_type'    => Utils::clean_hint_type( $raw_hint['hint_type'] ),
			'as_attr'      => ( ! empty( $raw_hint['as_attr'] )     ? Utils::clean_hint_attr( $raw_hint['as_attr'] ) : '' ),
			'type_attr'    => ( ! empty( $raw_hint['type_attr'] )   ? Utils::clean_hint_attr( $raw_hint['type_attr'] ) : '' ),
			'crossorigin'  => ( ! empty( $raw_hint['crossorigin'] ) ? 'crossorigin' : '' ),
			'media'        => ( ! empty( $raw_hint['media'] )       ? Utils::clean_url( $raw_hint['media'] ) : '' ),
			'current_user' => ( ! empty( $current_user ) ? $current_user : '' ),
			'auto_created' => ( $raw_hint['auto_created'] ?? 0 )
		);

		return \apply_filters( 'pprh_append_hint', $new_hint, $raw_hint );
	}

	public function new_hint_ctrl( $raw_hint ) {
		$candidate_hint = $this->create_hint( $raw_hint );
		$op_code = $raw_hint['op_code'];
		$duplicate_hints = \PPRH\Utils::get_duplicate_hints( $candidate_hint['url'], $candidate_hint['hint_type'] );
		$resolved = $this->new_hint_controller( $op_code, $candidate_hint, $duplicate_hints );

		if ( $resolved ) {
			return $candidate_hint;
		}
	}

	/**
	 * A true response means any duplicates have been taken care of. false means do not proceed.
	 * @param int $op_code
	 * @param array $candidate_hint
	 * @param array $duplicate_hints
	 * @return bool
	 */
	public function new_hint_controller( int $op_code, array $candidate_hint, array $duplicate_hints ):bool {
		$resolved = true;
//		$response_obj = \PPRH\DAO::create_db_result( false,0, 0, null );

		if ( empty( $duplicate_hints ) ) {
			return $resolved;
		}

		elseif ( $op_code <= 2 ) {												// need to check for duplicates when creating or updating a hint.
			$resolved = $this->handle_duplicate_hints( $duplicate_hints, $candidate_hint );

//			if ( ! $resolved ) {
//				$response_obj = \PPRH\DAO::create_db_result( false,0, 1, null );
//			}
		}

//		return ( $resolved ) ? $candidate_hint : $response_obj;
		return $resolved;
	}


	public function handle_duplicate_hints( array $duplicate_hints, array $candidate_hint ):bool {
		$resolved = false;

		if ( isset( $candidate_hint['post_id'] ) ) {
			$resolved = \apply_filters( 'pprh_resolve_duplicate_hints', $duplicate_hints, $candidate_hint );
		}

		return $resolved;
	}


}
