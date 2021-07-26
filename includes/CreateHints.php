<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CreateHints {

	public function create_hint( array $raw_hint ) {
		if ( empty( $raw_hint['url'] ) || empty( $raw_hint['hint_type'] ) ) {
			return false;
		}

		$current_user = \wp_get_current_user()->display_name;

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

	public function new_hint_ctrl( array $raw_hint ):array {
		$dao = new DAO();
		$candidate_hint = $this->create_hint( $raw_hint );
		$pprh_hint = array();

		if ( is_array( $candidate_hint ) && isset( $raw_hint['op_code'] ) ) {
			$op_code = (int) $raw_hint['op_code'];
			$hint_ids = ( ! empty( $raw_hint['hint_ids'] ) ? $raw_hint['hint_ids'] : '' );
			$duplicate_hints = $dao->get_duplicate_hints( $candidate_hint['url'], $candidate_hint['hint_type'], $op_code, $hint_ids );
			$pprh_hint = $this->new_hint_controller( $op_code, $candidate_hint, $duplicate_hints );
		}

		return $pprh_hint;
	}

	/**
	 * An empty return value means there is a duplicate hint.
	 * @param int $op_code
	 * @param array $candidate_hint
	 * @param array $duplicate_hints
	 * @return array
	 */
	public function new_hint_controller( int $op_code, array $candidate_hint, array $duplicate_hints ):array {

		if ( $op_code <= 2 && isset( $candidate_hint['post_id'] ) ) {
			$candidate_hint = \apply_filters( 'pprh_resolve_duplicate_hints', $candidate_hint, $duplicate_hints );
		} elseif ( ! empty( $duplicate_hints ) ) {
			return array();
		}

		return $candidate_hint;
	}

}
