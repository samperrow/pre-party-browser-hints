<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CreateHints {

//	public function __construct() {}

//	public $duplicate_hints = array();

	public function create_hint( $raw_hint ) {
		if ( empty( $raw_hint['url'] ) || empty( $raw_hint['hint_type'] ) ) {
			return false;
		}

		$new_hint = array(
			'url'          => Utils::clean_url( $raw_hint['url'] ),
			'hint_type'    => Utils::clean_hint_type( $raw_hint['hint_type'] ),
			'as_attr'      => ( ! empty( $raw_hint['as_attr'] )     ? Utils::clean_hint_attr( $raw_hint['as_attr'] ) : '' ),
			'type_attr'    => ( ! empty( $raw_hint['type_attr'] )   ? Utils::clean_hint_attr( $raw_hint['type_attr'] ) : '' ),
			'crossorigin'  => ( ! empty( $raw_hint['crossorigin'] ) ? 'crossorigin' : '' ),
			'media'        => ( ! empty( $raw_hint['media'] )       ? Utils::clean_url( $raw_hint['media'] ) : '' ),
		);

		return apply_filters( 'pprh_ch_append_hint', $new_hint, $raw_hint );
	}

	public function new_hint_controller( $raw_hint ) {
		$dao = new DAO();
		$candidate_hint = $this->create_hint( $raw_hint );
		$all_hints = \PPRH\Utils::get_pprh_hints(0);

		if ( count( $all_hints ) > 0 ) {
			$duplicate_hint_warning = $this->handle_duplicate_hints( $all_hints, $candidate_hint );

			if ( is_object( $duplicate_hint_warning ) ) {
				return $duplicate_hint_warning;						// duplicate hints exist
			}
		}

		return ( is_array( $candidate_hint ) )
			? $candidate_hint
			: $dao->create_db_result( false, '', 'Failed to create hint.', 0, null );
	}

	// tested
	public function handle_duplicate_hints( $all_hints, $candidate_hint ) {
		$duplicate_hints = $this->get_duplicate_hints( $all_hints, $candidate_hint );
		$duplicate_hints_exist = $this->duplicate_hints_exist( $duplicate_hints );

		if ( $duplicate_hints_exist ) {
			$resolved = $this->resolve_duplicate_hints( $duplicate_hints, $candidate_hint );

			if ( false === $resolved ) {
				$dao = new DAO();
				return $dao->create_db_result( false, '', 'A duplicate hint already exists!', 0, null );
			}
		}

		return true;
	}

	// tested
	public function get_duplicate_hints( $all_hints, $candidate_hint ) {
		$dups = array();

		foreach ( $all_hints as $hint ) {
			if ( $hint['url'] === $candidate_hint['url'] && $hint['hint_type'] === $candidate_hint['hint_type'] ) {
				$dups[] = $hint;
			}
		}

		$dups = apply_filters( 'pprh_filter_duplicate_hints', $dups, $candidate_hint );

		return array_values( $dups );		// re-index array so the first value has an index of 0.
	}

	// tested
	public function duplicate_hints_exist( $dup_hints ) {
		return ( count( $dup_hints ) > 0 );
	}


	public function resolve_duplicate_hints( $duplicate_hints, $candidate_hint ) {
		if ( ! empty( $candidate_hint['post_id'] ) && count( $duplicate_hints ) > 0 ) {
			return apply_filters( 'pprh_ch_resolve_duplicate_hints', $duplicate_hints, $candidate_hint );
		}

		return false;
	}


}
