<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HintControllerTest extends TestCase {

	public static $hint_ctrl;

	public $hint_builder;

	/**
	 * @before Class
	 */
	public function init() {
		self::$hint_ctrl = new \PPRH\HintController();
		$this->hint_builder = new \PPRH\HintBuilder();
	}

	public function test_hint_ctrl_init() {
		// insert_hint
		$raw_data_1 = \PPRH\HintBuilder::create_raw_hint( 'test.com', 'dns-prefetch', '', '', '', 'crossorigin', '' );
		$raw_data_1['op_code'] = 1;
		$actual_1 = self::$hint_ctrl->hint_ctrl_init( $raw_data_1 );
		$expected_1 = \PPRH\DAO::create_db_result( '', true, $raw_data_1['op_code'], $actual_1->new_hint );
		self::assertEquals($expected_1, $actual_1);

		// update_hint
		$raw_data_2 = \PPRH\HintBuilder::create_raw_hint( 'test2.com', 'dns-prefetch', '', '', '', 'crossorigin', '' );
		$raw_data_2['op_code'] = 1;
		$raw_data_2['hint_ids'] = '100';
		$actual_2 = self::$hint_ctrl->hint_ctrl_init( $raw_data_2 );
		$expected_2 = \PPRH\DAO::create_db_result(  '', true, $raw_data_2['op_code'], $actual_2->new_hint );
		self::assertEquals($expected_2, $actual_2);

		// delete_hint
		$raw_data_3 = array('url' => 'test3.com', 'hint_type' => 'preconnect', 'op_code' => 2, 'hint_ids' => '100' );
		$actual_3 = self::$hint_ctrl->hint_ctrl_init( $raw_data_3 );
		$expected_3 = \PPRH\DAO::create_db_result( '', true, $raw_data_3['op_code'], array() );
		self::assertEquals($expected_3, $actual_3);

		// bulk_update - enabled status
		$raw_data_4 = array('url' => 'test4.com', 'hint_type' => 'preconnect', 'op_code' => 3, 'hint_ids' => '105' );
		$actual_4 = self::$hint_ctrl->hint_ctrl_init( $raw_data_4 );
		$expected_4 = \PPRH\DAO::create_db_result(  '', true, $raw_data_4['op_code'], array() );
		self::assertEquals($expected_4, $actual_4);

		// bulk_update - disabled status
		$raw_data_5 = array('url' => 'test5.com', 'hint_type' => 'preconnect', 'op_code' => 4, 'hint_ids' => '110' );
		$actual_5 = self::$hint_ctrl->hint_ctrl_init( $raw_data_5 );
		$expected_5 = \PPRH\DAO::create_db_result( '', true, $raw_data_5['op_code'], array() );
		self::assertEquals($expected_5, $actual_5);

		// dup hint exists..
//		$raw_data_6 = array('url' => 'https://fonts.gstatic.com', 'hint_type' => 'preconnect', 'op_code' => 0, 'hint_ids' => '' );
//		$actual_6 = self::$hint_ctrl->hint_ctrl_init( $raw_data_6 );
//		$expected_6 = \PPRH\DAO::create_db_result( false, $raw_data_6['op_code'], 1, null );
//		self::assertEquals( $expected_6, $actual_6 );
	}

	public function test_new_hint_ctrl() {
		$dummy_hint = \PPRH\HintBuilder::create_raw_hint( 'https://free-hint.com', 'dns-prefetch', '', '', '', '' );

		$actual_1 = self::$hint_ctrl->new_hint_ctrl( $dummy_hint, 0 );
		self::assertCount( 9, $actual_1 );

		$actual_2 = self::$hint_ctrl->new_hint_ctrl( $dummy_hint, 1 );
		self::assertCount( 9, $actual_2 );

		$raw_data_4 = \PPRH\HintBuilder::create_raw_hint( '', '' );
		$actual_4 = self::$hint_ctrl->new_hint_ctrl( $raw_data_4, 0 );
		self::assertEmpty( $actual_4 );
	}

	public function test_handle_duplicate_hints() {
		$hint_1 = \PPRH\HintBuilder::create_raw_hint( 'https://test.com', 'dns-prefetch', '', '', '', 'screen' );
		$dup_hints_1 = array( $hint_1 );
		$candidate_hint_1 = $hint_1;
		$actual_1 = self::$hint_ctrl->handle_duplicate_hints( $candidate_hint_1, $dup_hints_1 );
		self::assertEmpty( $actual_1 );

		$candidate_hint_2 = \PPRH\HintBuilder::create_raw_hint( 'https://test2.com', 'dns-prefetch', '', '', '', 'screen' );
		$dup_hints_2 = array();
		$actual_2 = self::$hint_ctrl->handle_duplicate_hints( $candidate_hint_2, $dup_hints_2 );
		self::assertNotEmpty( $actual_2 );

		$candidate_hint_3 = \PPRH\HintBuilder::create_raw_hint( 'https://asdf.com', 'preconnect', '', '', 'crossorigin', 'screen' );
		$dup_hints_3 = array( $candidate_hint_3 );
		$actual_3 = self::$hint_ctrl->handle_duplicate_hints( $candidate_hint_3, $dup_hints_3 );
		self::assertEmpty( $actual_3 );

		$candidate_hint_4 = \PPRH\HintBuilder::create_raw_hint( 'https://asdf.com', 'preconnect', '', '', 'crossorigin', 'screen', '', '10' );
		$dup_hints_4 = array( $candidate_hint_4 );
		$actual_4 = self::$hint_ctrl->handle_duplicate_hints( $candidate_hint_4, $dup_hints_4 );
		self::assertEmpty( $actual_4 );

		$candidate_hint_5 = \PPRH\HintBuilder::create_raw_hint( 'https://asdf.com', 'preconnect', '', '', 'crossorigin', 'screen', '', '2128' );
		$dup_hints_5 = array(
			\PPRH\HintBuilder::create_raw_hint( 'https://asdf.com', 'preconnect', '', '', 'crossorigin', 'screen', '', '10' ),
			\PPRH\HintBuilder::create_raw_hint( 'https://asdf.com', 'preconnect', '', '', 'crossorigin', 'screen', '', '11' ),
			\PPRH\HintBuilder::create_raw_hint( 'https://asdf.com', 'preconnect', '', '', 'crossorigin', 'screen', '', 'global' )
		);
		$actual_5 = self::$hint_ctrl->handle_duplicate_hints( $candidate_hint_5, $dup_hints_5 );
		self::assertEmpty( $actual_5 );
	}

	public function test_get_post_id() {
		unset($_REQUEST['pprh_data']);

		$_GET['page'] = 'pprh-plugin-settings';
		$actual_1 = $this->hint_builder->get_post_id( false );
		self::assertEquals( 'global', $actual_1);
		unset($_GET['page']);

		$_REQUEST['pprh_data'] = "{\"url\":\"asdf.com\",\"hint_type\":\"dns-prefetch\",\"media\":\"\",\"as_attr\":\"\",\"type_attr\":\"\",\"crossorigin\":false,\"post_id\":\"2128\",\"op_code\":0,\"hint_ids\":[]}";
		$actual_2 = $this->hint_builder->get_post_id( true );
		self::assertEquals( '2128', $actual_2);

		$_REQUEST['pprh_data'] = "{\"url\":\"asdf2.com\",\"hint_type\":\"dns-prefetch\",\"media\":\"\",\"as_attr\":\"\",\"type_attr\":\"\",\"crossorigin\":false,\"post_id\":\"10\",\"op_code\":0,\"hint_ids\":[]}";
		$actual_3 = $this->hint_builder->get_post_id( true );
		self::assertEquals( '10', $actual_3);
		unset($_REQUEST['pprh_data']);

	}


}
