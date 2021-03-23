<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AjaxOpsTest extends TestCase {
	
	public function test_start() {
		if ( ! WP_ADMIN ) return;

		$this->eval_pprh_update_hints();
		$this->eval_init_1();
		$this->eval_init_2();
		$this->eval_init_3();
		$this->eval_init_4();
		$this->eval_init_5();

	}

	public function eval_pprh_update_hints(): void {
		$expected = wp_doing_ajax();
		$ajax_ops = new \PPRH\AjaxOps();
		$expected_nonce = TestUtils::create_nonce('pprh_table_nonce');
		$_POST['pprh_data'] = '{"url":"tester","hint_type":"dns-prefetch","action":"create","hint_ids":null"}';
		$_POST['action'] = 'pprh_update_hints';
		$_REQUEST['nonce'] = $expected_nonce;
		$initiated = $ajax_ops->pprh_update_hints();
		$this->assertEquals($expected, $initiated);
	}

	// also need to verify update, delete, enable, disable, bulk operations work properly.

	public function eval_init_1():void {
		$ajax_ops = new \PPRH\AjaxOps();
		$dao_ctrl = new \PPRH\DAOController();

		$test_data_1 = TestUtils::create_hint_array( 'https://testAjaxOps1.com', 'dns-prefetch' );
		$test_data_1['op_code'] = 0;

		$actual = $ajax_ops->init( $test_data_1 );
		$expected = $dao_ctrl->create_db_result( true, '', '', $test_data_1['op_code'], $actual->new_hint );
		$this->assertEquals($expected, $actual);
	}

	public function eval_init_2():void {
		$ajax_ops = new \PPRH\AjaxOps();
		$dao_ctrl = new \PPRH\DAOController();

		$test_data_1 = TestUtils::create_hint_array( 'https://testAjaxOps2.com', 'dns-prefetch' );
		$test_data_1['op_code'] = 1;

		$actual = $ajax_ops->init( $test_data_1 );
		$expected = $dao_ctrl->create_db_result( true, '', '', $test_data_1['op_code'], $actual->new_hint );
		$this->assertEquals($expected, $actual);
	}

	public function eval_init_3():void {
		$ajax_ops = new \PPRH\AjaxOps();
		$dao_ctrl = new \PPRH\DAOController();

		$test_data_1 = array(
			'op_code'  => 2,
			'hint_ids' => ''
		);

		$actual = $ajax_ops->init( $test_data_1 );
		$expected = $dao_ctrl->create_db_result( true, '', '', $test_data_1['op_code'], null );
		$this->assertEquals($expected, $actual);
	}

	public function eval_init_4():void {
		$ajax_ops = new \PPRH\AjaxOps();
		$dao_ctrl = new \PPRH\DAOController();

		$test_data_1 = array(
			'op_code'  => 3,
			'hint_ids' => '100'
		);

		$actual = $ajax_ops->init( $test_data_1 );
		$expected = $dao_ctrl->create_db_result( true, $test_data_1['hint_ids'], '', $test_data_1['op_code'], null );
		$this->assertEquals($expected, $actual);
	}

	public function eval_init_5():void {
		$ajax_ops = new \PPRH\AjaxOps();
		$dao_ctrl = new \PPRH\DAOController();

		$test_data_1 = array(
			'op_code'  => 4,
			'hint_ids' => '100'
		);

		$actual = $ajax_ops->init( $test_data_1 );
		$expected = $dao_ctrl->create_db_result( true, $test_data_1['hint_ids'], '', $test_data_1['op_code'], null );
		$this->assertEquals($expected, $actual);
	}

}


