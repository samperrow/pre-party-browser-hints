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
		$this->ajax_ops = new \PPRH\AjaxOps();
	}

	public function test_pprh_update_hints() {
		$expected = \wp_doing_ajax();
		$expected_nonce = TestUtils::create_nonce('pprh_table_nonce');
		$_POST['pprh_data'] = '{"url":"tester","hint_type":"dns-prefetch","action":"create","hint_ids":null"}';
		$_POST['action'] = 'pprh_update_hints';
		$_REQUEST['nonce'] = $expected_nonce;
		$initiated = $this->ajax_ops->pprh_update_hints();
		self::assertEquals($expected, $initiated);
	}

	// also need to verify update, delete, enable, disable, bulk operations work properly.

	public function test_init() {
		$test_data_1 = TestUtils::create_hint_array( 'https://testAjaxOps1.com', 'dns-prefetch' );
		$test_data_1['op_code'] = 0;
		$actual_1 = $this->ajax_ops->init( $test_data_1 );
		$expected_1 = \PPRH\DAO::create_db_result( true, $test_data_1['op_code'], 0 );
		self::assertEquals( $expected_1, $actual_1 );

		$test_data_2 = TestUtils::create_hint_array( 'https://testAjaxOps2.com', 'dns-prefetch' );
		$test_data_2['op_code'] = 1;
		$actual_2 = $this->ajax_ops->init( $test_data_2 );
		$expected_2 = \PPRH\DAO::create_db_result( true, $test_data_2['op_code'], 0 );
		self::assertEquals($expected_2, $actual_2);

		$test_data_3 = array('op_code' => 2, 'hint_ids' => '100');
		$actual_3 = $this->ajax_ops->init( $test_data_3 );
		$expected_3 = \PPRH\DAO::create_db_result( true, $test_data_3['op_code'], 0 );
		self::assertEquals($expected_3, $actual_3);

		$test_data_4 = array('op_code' => 3, 'hint_ids' => '100');
		$actual_4 = $this->ajax_ops->init( $test_data_4 );
		$expected_4 = \PPRH\DAO::create_db_result( true, $test_data_4['op_code'], 0 );
		self::assertEquals( $expected_4, $actual_4 );

		$test_data_5 = array('op_code' => 4, 'hint_ids' => '100');
		$actual_5 = $this->ajax_ops->init( $test_data_5 );
		$expected_5 = \PPRH\DAO::create_db_result( true, $test_data_5['op_code'], 0 );
		self::assertEquals( $expected_5, $actual_5 );

		// reset post preconnect, with valid post ID.
		$test_data_6 = array( 'post_id' => '1', 'action' => 'reset_single_post_preconnects' );
		$actual_6 = $this->ajax_ops->init( $test_data_6 );
		$expected_6 = \PPRH\DAO::create_db_result( true, 5, 0 );
		self::assertEquals( $expected_6, $actual_6 );

		// reset post preconnect, with bad value for post ID ('global').
		$test_data_7 = array( 'post_id' => 'global', 'action' => 'reset_single_post_preconnects' );
		$actual_7 = $this->ajax_ops->init( $test_data_7 );
		$expected_7 = \PPRH\DAO::create_db_result( false, 5, 1 );
		self::assertEquals( $expected_7, $actual_7 );

		// prerender config with fake post ID.
		$test_data_8 = array( 'post_id' => '1', 'action' => 'prerender_config' );
		$actual_8 = $this->ajax_ops->init( $test_data_8 );
		$expected_8 = \PPRH\DAO::create_db_result( false, 6, 2 );
		self::assertEquals( $expected_8, $actual_8 );
	}



}


