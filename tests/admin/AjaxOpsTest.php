<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AjaxOpsTest extends TestCase {

	public $ajax_ops;

	/**
	 * @before
	 */
	public function test_start() {
		$this->ajax_ops = new \PPRH\AjaxOps( 2 );
	}

//	public function test_pprh_update_hints() {
//		$expected = \wp_doing_ajax();
//		$expected_nonce = TestUtils::create_nonce('pprh_table_nonce');
//		$_POST['pprh_data'] = '{"url":"tester","hint_type":"dns-prefetch","action":"create","hint_ids":null"}';
//		$_POST['action'] = 'pprh_update_hints';
//		$_REQUEST['nonce'] = $expected_nonce;
//		$initiated = $this->ajax_ops->pprh_update_hints();
//		self::assertEquals($expected, $initiated);
//		unset( $_POST['pprh_data'], $_POST['action'], $_REQUEST['nonce'] );
//	}

	// also need to verify update, delete, enable, disable, bulk operations work properly.

	public function test_update_hints() {
		$test_data_1 = TestUtils::create_hint_array( 'https://testAjaxOps1.com', 'dns-prefetch' );
		$test_data_1['op_code'] = 0;
		$actual_1 = $this->ajax_ops->update_hints( $test_data_1 );
		$expected_1 = \PPRH\DAO::create_db_result( true, $test_data_1['op_code'], 0, $actual_1['result']->new_hint );
		self::assertEquals( $expected_1, $actual_1['result'] );

		$test_data_2 = TestUtils::create_hint_array( 'https://testAjaxOps2.com', 'dns-prefetch' );
		$test_data_2['op_code'] = 1;
		$actual_2 = $this->ajax_ops->update_hints( $test_data_2 );
		$expected_2 = \PPRH\DAO::create_db_result( true, $test_data_2['op_code'], 0, $actual_2['result']->new_hint );
		self::assertEquals( $expected_2, $actual_2['result'] );

		$test_data_3 = array( 'op_code' => 2, 'hint_ids' => '100' );
		$actual_3 = $this->ajax_ops->update_hints( $test_data_3 );
		$expected_3 = \PPRH\DAO::create_db_result( true, $test_data_3['op_code'], 0, null );
		self::assertEquals( $expected_3, $actual_3['result'] );

		$test_data_4 = array( 'op_code' => 3, 'hint_ids' => '100' );
		$actual_4 = $this->ajax_ops->update_hints( $test_data_4 );
		$expected_4 = \PPRH\DAO::create_db_result( true, $test_data_4['op_code'], 0, null );
		self::assertEquals( $expected_4, $actual_4['result'] );

		$test_data_5 = array( 'op_code' => 4, 'hint_ids' => '100' );
		$actual_5 = $this->ajax_ops->update_hints( $test_data_5 );
		$expected_5 = \PPRH\DAO::create_db_result( true, $test_data_5['op_code'], 0, null );
		self::assertEquals( $expected_5, $actual_5['result'] );


	}



}


