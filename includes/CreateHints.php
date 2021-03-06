<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CreateHints {

//	public function __construct() {}

	public $duplicate_hints = array();

	public function create_hint( $raw_hint ) {
		if ( empty( $raw_hint['url'] ) || empty( $raw_hint['hint_type'] ) ) {
			return false;
		}

		$new_hint = array(
			'url'          => Utils::clean_url( $raw_hint['url'] ),
			'hint_type'    => Utils::clean_hint_type( $raw_hint['hint_type'] ),
			'as_attr'      => ( ! empty( $raw_hint['as_attr'] )     ? Utils::clean_hint_attr( $raw_hint['as_attr'] ) : '' ),
			'type_attr'    => ( ! empty( $raw_hint['type_attr'] )   ? Utils::clean_hint_attr( $raw_hint['as_attr'] ) : '' ),
			'crossorigin'  => ( ! empty( $raw_hint['crossorigin'] ) ? 'crossorigin' : '' ),
			'media'        => ( ! empty( $raw_hint['media'] )       ? Utils::clean_url( $raw_hint['media'] ) : '' ),
		);

		return apply_filters( 'pprh_ch_append_hint', $new_hint, $raw_hint );
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

		$dups = array_filter( $all_hints, function( $hint ) {
			return ( ( $this->new_pprh_hint['url'] === $hint['url'] ) && ( $this->new_pprh_hint['hint_type'] === $hint['hint_type'] ) );
		});

		return array_values( $dups );		// re-index array so the first value has an index of 0.
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
