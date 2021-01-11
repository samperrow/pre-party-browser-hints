<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PPRH\PPRH_Pro;
use PPRH\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DAOTest extends TestCase {

	public function test_create_hint():void {
		$dao = new PPRH\DAO();
		$hint_obj = Utils::create_raw_hint_object( 'https://www.espn.com/foozball', 'preconnect', 1 );
		$hint_result = Utils::create_pprh_hint( $hint_obj );

		$wpdb_result = $dao->create_hint( $hint_result, null );
		$res = array(
			'last_error' => '',
			'success'    => true,
			'status'     => 'success',
			'msg'        => "Resource hint created successfully."
		);

		$this->assertEquals( $res, $wpdb_result );
	}

}
