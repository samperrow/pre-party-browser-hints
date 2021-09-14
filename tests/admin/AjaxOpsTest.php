<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AjaxOpsTest extends TestCase {

	public static $ajax_ops;

	/**
	 * @before Class
	 */
	public function init() {
		self::$ajax_ops = new \PPRH\AjaxOps( 2 );
	}

	// also need to verify update, delete, enable, disable, bulk operations work properly.

	public function test_update_hints() {
		$test_data_1 = Utils::create_raw_hint( 'https://testAjaxOps1.com', 'dns-prefetch' );
		$test_data_1['op_code'] = 0;
		$actual_1 = self::$ajax_ops->update_hints( $test_data_1 );
		$expected_1 = \PPRH\DAO::create_db_result( true, $test_data_1['op_code'], 0, $actual_1['result']->new_hint );
		self::assertEquals( $expected_1, $actual_1['result'] );

		$test_data_2 = Utils::create_raw_hint( 'https://testAjaxOps2.com', 'dns-prefetch' );
		$test_data_2['op_code'] = 1;
		$actual_2 = self::$ajax_ops->update_hints( $test_data_2 );
		$expected_2 = \PPRH\DAO::create_db_result( true, $test_data_2['op_code'], 0, $actual_2['result']->new_hint );
		self::assertEquals( $expected_2, $actual_2['result'] );

		$test_data_3 = array( 'op_code' => 2, 'hint_ids' => '100' );
		$actual_3 = self::$ajax_ops->update_hints( $test_data_3 );
		$expected_3 = \PPRH\DAO::create_db_result( true, $test_data_3['op_code'], 0, array() );
		self::assertEquals( $expected_3, $actual_3['result'] );

		$test_data_4 = array( 'op_code' => 3, 'hint_ids' => '100' );
		$actual_4 = self::$ajax_ops->update_hints( $test_data_4 );
		$expected_4 = \PPRH\DAO::create_db_result( true, $test_data_4['op_code'], 0, array() );
		self::assertEquals( $expected_4, $actual_4['result'] );

		$test_data_5 = array( 'op_code' => 4, 'hint_ids' => '100' );
		$actual_5 = self::$ajax_ops->update_hints( $test_data_5 );
		$expected_5 = \PPRH\DAO::create_db_result( true, $test_data_5['op_code'], 0, array() );
		self::assertEquals( $expected_5, $actual_5['result'] );
	}



}


