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
		return $this->new_hint_controller( $op_code, $candidate_hint, $duplicate_hints );
	}

	public function new_hint_controller( int $op_code, array $candidate_hint, array $duplicate_hints ) {
		$resolved = true;
		$response_obj = \PPRH\DAO::create_db_result( false,0, 0, null );

		if ( 0 === $op_code && ! empty( $duplicate_hints ) ) {												// only need to check for duplicates when creating a hint.
			$resolved = $this->handle_duplicate_hints( $duplicate_hints, $candidate_hint );

			if ( ! $resolved ) {
				$response_obj = \PPRH\DAO::create_db_result( false,0, 1, null );
//				$msg = 'A duplicate hint already exists!';
			}
		}

		return ( $resolved ) ? $candidate_hint : $response_obj;
	}


	public function handle_duplicate_hints( $duplicate_hints, $candidate_hint ) {
		$resolved = false;

		if ( isset( $candidate_hint['post_id'] ) ) {
			$resolved = \apply_filters( 'pprh_resolve_duplicate_hints', $duplicate_hints, $candidate_hint );
		}

		return $resolved;
	}


}
