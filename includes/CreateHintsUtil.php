<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CreateHintsUtil extends CreateHints {

	public $duplicate_hints = array();

//	public function create_raw_hint_arr( $url, $hint_type, $post_id = '' ) {
//		$arr = array(
//			'url'          => $url,
//			'hint_type'    => $hint_type,
//		);
//
//
//		if ( \PPRH\Utils::pprh_is_plugin_active() ) {
//			$arr['post_id'] = $post_id;
//		}
//
//		return $arr;
//	}


//	public function process_data_array( $raw_hint_arr ) {
//		$hint_arr = array();
//
//		foreach( $raw_hint_arr as $raw_hint ) {
//			$raw_hint = $this->create_raw_hint_arr( $raw_hint['url'], $raw_hint['hint_type'] );
//			$pprh_hint = $this->create_hint( $raw_hint );
//			$hint_arr[] = $pprh_hint;
//		}
//
//		return $hint_arr;
//	}

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

	// tested
	public function get_duplicate_hints( $hint ) {
		$table = PPRH_DB_TABLE;
		$dao = new DAO();

		$query = array(
			'sql'  => "SELECT * FROM $table WHERE url = %s AND hint_type = %s",
			'args' => array( $hint['url'], $hint['hint_type'] )
		);

		$query = apply_filters( 'pprh_ch_duplicate_hint_query', $query, $hint );

		return $dao->get_hints( $query );
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