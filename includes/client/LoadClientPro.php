<?php
declare(strict_types=1);

namespace PPRH;

//use PPRH\DAO\DAOPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoadClientPro {

	public function __construct( $client_post_id ) {

		$this->init( $client_post_id );
	}

	public function init( $client_post_id ) {

		$str_client_post_id = (string) $client_post_id;
		$hints_to_get_arr = DAO::get_post_auto_hints( $str_client_post_id );

		$get_preloads    = ( true === $hints_to_get_arr['preloads'] );
		$get_preconnects = ( true === $hints_to_get_arr['preconnects'] );

		if ( $get_preconnects ) {

		}
	}

}
