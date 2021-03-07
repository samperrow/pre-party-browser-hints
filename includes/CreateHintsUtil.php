<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CreateHintsUtil extends CreateHints {

	public $duplicate_hints = array();

	public $all_hints = array();

	public $new_pprh_hint = array();

	public function __construct() {
//		$this->all_hints = $all_hints;
	}

	public function new_hint_controller( $raw_hint ) {
		$dao = new DAO();
		$pprh_hint = $this->create_hint( $raw_hint );

		if ( is_array( $pprh_hint ) ) {
			$dups = $this->handle_duplicate_hints( $pprh_hint );

			if ( is_object( $dups ) ) {
				return $dups;
			}

			return $pprh_hint;
		}

		return $dao->create_db_result( false, '', 'Failed to create hint.', 'create', null );
	}

	// tested
	public function handle_duplicate_hints( $pprh_hint ) {
		$dao = new DAO();
		$this->duplicate_hints = $this->get_duplicate_hints( $pprh_hint );
		$duplicate_hints_exist = $this->duplicate_hints_exist( $this->duplicate_hints );

		if ( $duplicate_hints_exist ) {
			$dups = $this->resolve_duplicate_hints( $pprh_hint );

			if ( ! $dups ) {
				return $dao->create_db_result( false, '', 'A duplicate hint already exists!', 'create', null );
			}
		}

		return true;
	}

	public function get_duplicate_hints( $new_pprh_hint ) {
		$this->new_pprh_hint = $new_pprh_hint;
		$all_hints = \PPRH\Utils::get_all_hints();

		return array_filter( $all_hints, function( $hint ) {
			return ( $this->new_pprh_hint['url'] === $hint['url'] && $this->new_pprh_hint['hint_type'] === $hint['hint_type']  );
		});
	}

	// tested
	public function duplicate_hints_exist( $dup_hints ) {
		return ( count( $dup_hints ) > 0 );
	}


	public function resolve_duplicate_hints( $pprh_hint ) {

		if ( ! empty( $pprh_hint['post_id'] ) ) {
			$clear_duplicate_nonglobals = get_option( 'pprh_pro_clear_dup_nonglobals' );

			if ( 'global' === $pprh_hint['post_id'] ) {
				apply_filters('pprh_ch_resolve_duplicate_hints', $this->duplicate_hints);
				return true;

			} elseif ( 'true' === $clear_duplicate_nonglobals ) {
				apply_filters( 'pprh_ch_excessive_dup_hints_exist', $this->duplicate_hints );
			}

		}

		return false;
	}

}