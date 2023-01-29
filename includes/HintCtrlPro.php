<?php

namespace PPRH;

use PPRH\Utils\Utils;
use PPRH\Utils\Sanitize;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HintCtrlPro {

	private $show_posts_on_front;

	public function set_filters( bool $show_posts_on_front ) {
		$this->show_posts_on_front = $show_posts_on_front;

		\add_filter( 'pprh_append_hint', array( $this, 'append_hint' ), 10, 2 );
		\add_filter( 'pprh_resolve_duplicate_hints', array( $this, 'resolve_duplicate_hints' ), 10, 2 );
		\add_filter( 'pprh_get_pro_duplicate_hints', array( $this, 'get_pro_duplicate_hints' ), 10, 2 );
	}

	public function append_hint( $new_hint, $raw_hint ) {
		if ( isset( $raw_hint['post_id'] ) ) {
			$new_hint['post_id'] = Sanitize::strip_non_alphanums( $raw_hint['post_id'] );
		}

		return $new_hint;
	}

	public function resolve_duplicate_hints( array $candidate_hint, array $duplicate_hints ) {
		if ( ! isset( $candidate_hint['post_id'] ) || empty( $duplicate_hints ) ) {
			return $candidate_hint;
		}

		$candidate_hint_post_id = $candidate_hint['post_id'];
		$is_duplicate_hint_present = $this->is_duplicate_hint_present( $duplicate_hints, $candidate_hint_post_id );

		if ( $is_duplicate_hint_present ) {
			return array();
		}

//		$remove_dups = $this->resolve_duplicate_hints_ctrl( $candidate_hint, $duplicate_hints );

		if ( 'prerender' !== $candidate_hint['hint_type'] ) {
			$this->delete_duplicate_hints( $duplicate_hints );
			$candidate_hint['post_id'] = 'global';
		}

		return $candidate_hint;
	}

//	public function resolve_duplicate_hints_ctrl( $candidate_hint, $duplicate_hints ):bool {
//		$candidate_hint_post_id = $candidate_hint['post_id'];
//		$dup_hints_count        = count( $duplicate_hints );
//		$remove_dups = $this->dup_hint_ratio_ctrl( $dup_hints_count, $this->show_posts_on_front );
//		return ( $remove_dups || 'global' === $candidate_hint_post_id );
//	}

//	public function dup_hint_ratio_ctrl( int $dup_hints_count, bool $show_posts_on_front ):bool {
//		$dao_pro                = new \PPRH\DAO\DAOPro();
//		$active_post_ids        = $dao_pro->get_all_active_posts( $show_posts_on_front );
//		$active_post_count      = count( $active_post_ids );
//		$clear_dup_hints_option = ( 'true' === \PPRH\Utils\Utils::get_json_option_value( 'pprh_pro_options', 'clear_dup_nonglobals' ) );
//		$dup_hint_to_active_post_ratio = $dup_hints_count / $active_post_count;
//		$max_ratio = (int) ( \PPRH\Utils\Utils::get_json_option_value( 'pprh_pro_options', 'duplicate_hint_removal_percent' ) ) / 100;
//		return ( $clear_dup_hints_option && ( $dup_hint_to_active_post_ratio >= $max_ratio ) );
//	}



	/**
	 * @param array $dup_hints
	 * @param string $candidate_post_id
	 * @return bool
	 */
	public function is_duplicate_hint_present( array $dup_hints, string $candidate_post_id ):bool {

		foreach ( $dup_hints as $dup_hint ) {
			$dup_hint_post_id = $dup_hint['post_id'] ?? '';
			$existing_dup_global_hint = ( 'global' === $dup_hint_post_id );
			$existing_dup_post_hint = ( $dup_hint_post_id === $candidate_post_id );

			if ( $existing_dup_global_hint || $existing_dup_post_hint ) {
				return true;
			}
		}

		return false;
	}


	private function delete_duplicate_hints( array $duplicate_hints ) {
		$hint_ctrl   = new \PPRH\HintController();
		$hint_id_arr = $this->get_hint_ids( $duplicate_hints );
		$hint_ids    = Utils::array_to_csv( $hint_id_arr );

		$data = array(
			'op_code'  => 2,
			'hint_ids' => $hint_ids
		);

		$result = $hint_ctrl->hint_ctrl_init( $data );
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
