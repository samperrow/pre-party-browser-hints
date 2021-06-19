<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AjaxOpsTest extends TestCase {

	public function test_pprh_update_hints(): void {
		$expected = \wp_doing_ajax();
		$ajax_ops = new \PPRH\AjaxOps();
		$expected_nonce = TestUtils::create_nonce('pprh_table_nonce');
		$_POST['pprh_data'] = '{"url":"tester","hint_type":"dns-prefetch","action":"create","hint_ids":null"}';
		$_POST['action'] = 'pprh_update_hints';
		$_REQUEST['nonce'] = $expected_nonce;
		$initiated = $ajax_ops->pprh_update_hints();
		self::assertEquals($expected, $initiated);
	}

	// also need to verify update, delete, enable, disable, bulk operations work properly.

	public function test_init():void {
		$ajax_ops = new \PPRH\AjaxOps();

		$test_data_1 = TestUtils::create_hint_array( 'https://testAjaxOps1.com', 'dns-prefetch' );
		$test_data_1['op_code'] = 0;
		$test_data_1['action'] = '';
		$actual_1 = $ajax_ops->init( $test_data_1 );
		$expected_1 = \PPRH\DAO::create_db_result( true, '', '', $test_data_1['op_code'], $actual_1->new_hint );
		self::assertEquals( $expected_1, $actual_1 );


		$test_data_2 = TestUtils::create_hint_array( 'https://testAjaxOps2.com', 'dns-prefetch' );
		$test_data_2['op_code'] = 1;
		$test_data_2['action'] = '';
		$actual_2 = $ajax_ops->init( $test_data_2 );
		$expected_2 = \PPRH\DAO::create_db_result( true, '', '', $test_data_2['op_code'], $actual_2->new_hint );
		self::assertEquals($expected_2, $actual_2);


		$test_data_3 = array(
			'op_code'  => 2,
			'hint_ids' => '100',
			'action' => ''
		);
		$actual_3 = $ajax_ops->init( $test_data_3 );
		$expected_3 = \PPRH\DAO::create_db_result( true, '100', '', $test_data_3['op_code'], null );
		self::assertEquals($expected_3, $actual_3);


		$test_data_4 = array(
			'op_code'  => 3,
			'hint_ids' => '100',
			'action' => ''
		);
		$actual_4 = $ajax_ops->init( $test_data_4 );
		$expected_4 = \PPRH\DAO::create_db_result( true, $test_data_4['hint_ids'], '', $test_data_4['op_code'], null );
		self::assertEquals( $expected_4, $actual_4 );


		$test_data_5 = array(
			'op_code'  => 4,
			'hint_ids' => '100',
			'action' => ''
		);
		$actual_5 = $ajax_ops->init( $test_data_5 );
		$expected_5 = \PPRH\DAO::create_db_result( true, $test_data_5['hint_ids'], '', $test_data_5['op_code'], null );
		self::assertEquals( $expected_5, $actual_5 );
	}



}


