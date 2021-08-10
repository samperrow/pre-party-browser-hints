<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAOControllerTest extends TestCase {

	public static $dao_controller;
	
	public function test_start() {
		self::$dao_controller = new \PPRH\DAOController();
	}

	public function test_hint_controller() {
		// insert_hint
		$raw_data_1 = array('url' => 'test.com', 'hint_type' => 'dns-prefetch', 'op_code' => 0 );
		$actual_1 = self::$dao_controller->hint_controller( $raw_data_1 );
		$expected_1 = \PPRH\DAO::create_db_result( true, $raw_data_1['op_code'], 0, $actual_1->new_hint );
		self::assertEquals($expected_1, $actual_1);

		// update_hint
		$raw_data_2 = array( 'url' => 'test2.com', 'hint_type' => 'dns-prefetch', 'op_code' => 1, 'hint_ids' => '100' );
		$actual_2 = self::$dao_controller->hint_controller( $raw_data_2 );
		$expected_2 = \PPRH\DAO::create_db_result( true, $raw_data_2['op_code'], 0, $actual_2->new_hint );
		self::assertEquals($expected_2, $actual_2);

		// delete_hint
		$raw_data_3 = array('url' => 'test3.com', 'hint_type' => 'preconnect', 'op_code' => 2, 'hint_ids' => '100' );
		$actual_3 = self::$dao_controller->hint_controller( $raw_data_3 );
		$expected_3 = \PPRH\DAO::create_db_result( true, $raw_data_3['op_code'], 0, null );
		self::assertEquals($expected_3, $actual_3);

		// bulk_update - enabled status
		$raw_data_4 = array('url' => 'test4.com', 'hint_type' => 'preconnect', 'op_code' => 3, 'hint_ids' => '105' );
		$actual_4 = self::$dao_controller->hint_controller( $raw_data_4 );
		$expected_4 = \PPRH\DAO::create_db_result( true, $raw_data_4['op_code'], 0, null );
		self::assertEquals($expected_4, $actual_4);

		// bulk_update - disabled status
		$raw_data_5 = array('url' => 'test5.com', 'hint_type' => 'preconnect', 'op_code' => 4, 'hint_ids' => '110' );
		$actual_5 = self::$dao_controller->hint_controller( $raw_data_5 );
		$expected_5 = \PPRH\DAO::create_db_result( true, $raw_data_5['op_code'], 0, null );
		self::assertEquals($expected_5, $actual_5);

		// dup hint exists..
//		$raw_data_6 = array('url' => 'https://fonts.gstatic.com', 'hint_type' => 'preconnect', 'op_code' => 0, 'hint_ids' => '' );
//		$actual_6 = self::$dao_controller->hint_controller( $raw_data_6 );
//		$expected_6 = \PPRH\DAO::create_db_result( false, $raw_data_6['op_code'], 1, null );
//		self::assertEquals( $expected_6, $actual_6 );

	}






//	public function test_db_controller() {
//
//	}


}
