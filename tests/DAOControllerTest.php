<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAOControllerTest extends TestCase {

	public function test_hint_controller_1() {
		$dao_ctrl = new \PPRH\DAOController();

		$raw_data_1 = array(
			'op_code'   => 0,
			'url'       => 'test.com',
			'hint_type' => 'dns-prefetch',
		);

		$actual = $dao_ctrl->hint_controller( $raw_data_1 );
		$expected = $dao_ctrl->create_db_result( true, '', '', $raw_data_1['op_code'], $actual->new_hint );
		$this->assertEquals($expected, $actual);
	}

	public function test_hint_controller_2() {
		$dao_ctrl = new \PPRH\DAOController();

		$raw_data_1 = array(
			'op_code'   => 1,
			'url'       => 'test2.com',
			'hint_type' => 'dns-prefetch',
			'hint_ids'  => ''
		);

		$actual = $dao_ctrl->hint_controller( $raw_data_1 );
		$expected = $dao_ctrl->create_db_result( true, $raw_data_1['hint_ids'], '', $raw_data_1['op_code'], $actual->new_hint );
		$this->assertEquals($expected, $actual);
	}






	public function test_db_controller() {

	}


}
