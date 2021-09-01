<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CreateHints extends HintBuilder {

	/*
	 * This method/class will do everything related to creating hints, except add it to the db.
	 */
	public function new_hint_ctrl( array $raw_hint ):array {
		$dao            = new DAO();
		$candidate_hint = $this->create_pprh_hint( $raw_hint );
		$pprh_hint      = array();

		if ( ! empty( $candidate_hint ) && isset( $raw_hint['op_code'] ) ) {
			$op_code         = (int) $raw_hint['op_code'];
			$hint_ids        = ( ! empty( $raw_hint['hint_ids'] ) ? $raw_hint['hint_ids'] : '' );
			$duplicate_hints = $dao->get_duplicate_hints( $candidate_hint['url'], $candidate_hint['hint_type'], $op_code, $hint_ids );
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
