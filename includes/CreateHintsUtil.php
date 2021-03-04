<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CreateHintsUtil extends CreateHints {

	public $duplicate_hints = array();

//	public function create_raw_hint_arr( $url, $hint_type, $post_id = '', $post_url = '' ) {
//		$arr = array(
//			'url'          => $url,
//			'hint_type'    => $hint_type,
//		);
//
//
//		if ( \PPRH\Utils::pprh_is_plugin_active() ) {
//			$arr['post_id'] = $post_id;
//			$arr['post_url'] = $post_url;
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
			$this->duplicate_hints = array();
			$duplicate_hints_exist = $this->duplicate_hints_exist( $pprh_hint );

			if ( $duplicate_hints_exist ) {

//				if ( empty( $pprh_hint['post_id'] ) ) {
//					return $dao->create_db_result( false, '', 'A duplicate hint already exists!', 'create', null );
//				}

				$this->resolve_duplicate_hints( $pprh_hint );
			}

			return $pprh_hint;
		}

		return $dao->create_db_result( false, '', 'Failed to create hint.', 'create', null );
	}

	// hint creation utils

	public function duplicate_hints_exist( $pprh_hint ) {
		$this->duplicate_hints = $this->get_duplicate_hints( $pprh_hint );
		return ( count( $this->duplicate_hints ) > 0 );
	}


	public function resolve_duplicate_hints( $pprh_hint ) {
		$dao = new DAO();

		if ( empty( $pprh_hint['post_id'] ) ) {
			return $dao->create_db_result( false, '', 'A duplicate hint already exists!', 'create', null );
		}

		else {
//			$clear_duplicate_nonglobals = get_option( 'pprh_pro_clear_dup_nonglobals' );

			if ( 'global' === $pprh_hint['post_id'] ) {
				apply_filters( 'pprh_ch_resolve_duplicate_hints',  $this->duplicate_hints );
			}
//			elseif ( 'true' === $clear_duplicate_nonglobals ) {
//				apply_filters( 'pprh_ch_excessive_dup_hints_exist', $this->duplicate_hints );
//			}
		}

	}


	public function get_duplicate_hints( $hint ) {
		$table = PPRH_DB_TABLE;
		$dao = new DAO();

		$query = array(
			'sql'  => "SELECT * FROM $table WHERE url = %s AND hint_type = %s",
			'args' => array( $hint['url'], $hint['hint_type'] )
		);

//		if ( ! empty( $hint['post_id'] ) && 'global' !== $hint['post_id'] ) {
			$query = apply_filters( 'pprh_ch_duplicate_hint_query', $query, $hint );
//		}

//		var_dump($query);

		return $dao->get_hints( $query );
	}

}