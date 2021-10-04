<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DAOTest extends TestCase {

	public static $dao;
	public static $hint_ctrl;

	public function test_init() {
		self::$dao       = new \PPRH\DAO();
		self::$hint_ctrl = new \PPRH\HintController();
	}

	public function test_create_db_result() {
		$actual_1 = \PPRH\DAO::create_db_result( true, 0, 0, null );
		self::assertTrue( $actual_1->db_result['status'] );

		$actual_2 = \PPRH\DAO::create_db_result(false, 0, 0, null );
		self::assertFalse( $actual_2->db_result['status'] );

		$actual_3 = \PPRH\DAO::create_db_result(true, 1, 0, null );
		self::assertTrue( $actual_3->db_result['status'] );

		$actual_4 = \PPRH\DAO::create_db_result(true, 4, 0, null );
		self::assertTrue( $actual_4->db_result['status'] );

		$actual_6 = \PPRH\DAO::create_db_result(false, 5, 1, null );
		self::assertFalse( $actual_6->db_result['status'] );

		$actual_9 = \PPRH\DAO::create_db_result(true, 7, 0, null );
		self::assertTrue( $actual_9->db_result['status'] );

		$actual_10 = \PPRH\DAO::create_db_result(true, 7, 1, null );
		self::assertTrue( $actual_10->db_result['status'] );

		$actual_11 = \PPRH\DAO::create_db_result(false, 7, 2, null );
		self::assertFalse( $actual_11->db_result['status'] );
	}


	public function test_get_duplicate_hints() {
		$url_1 = 'https://asdfasdfadsf.com';
		$hint_type_1 = 'preconnect';
		$actual_1 = self::$dao->get_duplicate_hints( $url_1, $hint_type_1, 1, '100' );
		self::assertEmpty( $actual_1 );

		$actual_2 = self::$dao->get_duplicate_hints( $url_1, $hint_type_1, 0, '' );
		self::assertEmpty( $actual_2 );
	}

	public function test_get_admin_hints_query() {
		if ( PPRH_PRO_PLUGIN_ACTIVE ) {
			return;
		}

		$table = PPRH_DB_TABLE;

		$actual_1 = self::$dao->get_admin_hints_query();
		$expected_1 = array(
			'sql'  => "SELECT * FROM $table ORDER BY url ASC",
			'args' => array()
		);
		self::assertEquals( $expected_1, $actual_1 );

		$_REQUEST['orderby'] = 'hint_type';
		$_REQUEST['order'] = 'asc';
		$actual_2 = self::$dao->get_admin_hints_query();
		$expected_2 = array(
			'sql'  => "SELECT * FROM $table ORDER BY hint_type ASC",
			'args' => array()
		);
		self::assertEquals( $expected_2, $actual_2 );

		$_REQUEST['orderby'] = 'url';
		$_REQUEST['order'] = 'desc';
		$actual_3 = self::$dao->get_admin_hints_query();
		$expected_3 = array(
			'sql'  => "SELECT * FROM $table ORDER BY url DESC",
			'args' => array()
		);
		self::assertEquals( $expected_3, $actual_3 );
		unset( $_REQUEST['orderby'], $_REQUEST['order'] );

		$_REQUEST['orderby'] = '';
		$_REQUEST['order'] = '';
		$actual_4 = self::$dao->get_admin_hints_query();
		$expected_4 = array(
			'sql'  => "SELECT * FROM $table ORDER BY url ASC",
			'args' => array()
		);
		self::assertEquals( $expected_4, $actual_4 );
		unset( $_REQUEST['orderby'], $_REQUEST['order'] );

		$_REQUEST['orderby'] = 'hint-asdf<"asdf/';
		$_REQUEST['order'] = 'asdf';
		$actual_5 = self::$dao->get_admin_hints_query();
		$expected_5 = array(
			'sql'  => "SELECT * FROM $table ORDER BY url ASC",
			'args' => array()
		);
		self::assertEquals( $expected_5, $actual_5 );
		unset( $_REQUEST['orderby'], $_REQUEST['order'] );

		$_REQUEST['orderby'] = 'HINT_TYPE';
		$_REQUEST['order'] = 'DESC';
		$actual_6 = self::$dao->get_admin_hints_query();
		$expected_6 = array(
			'sql'  => "SELECT * FROM $table ORDER BY hint_type DESC",
			'args' => array()
		);
		self::assertEquals( $expected_6, $actual_6 );
		unset( $_REQUEST['orderby'], $_REQUEST['order'] );
	}

	public function test_get_client_hints_query() {
		$table = PPRH_DB_TABLE;

		$actual_1 = self::$dao->get_client_hints_query( array() );
		$sql = "SELECT * FROM $table WHERE status = %s";
		self::assertEquals( 'enabled', $actual_1['args'][0] );
		self::assertTrue( str_contains( $actual_1['sql'], $sql ) );
	}

	public function test_insert_hint() {
		$hint_builder = new \PPRH\HintBuilder();

		$hint_1 = PPRH\HintBuilder::create_raw_hint( 'https://www.asdf.com/foozball', 'preconnect', '', '', '', '', '','2145' );
		$new_hint_1 = $hint_builder->create_pprh_hint($hint_1);
		$actual_1 = self::$dao->insert_hint( $new_hint_1 );
		self::assertTrue( $actual_1['success'] );
	}

	public function test_update_hint() {
		$new_hint = \PPRH\HintBuilder::create_raw_hint( 'https://www.asdf2.com/foozball/blah.css', 'dns-prefetch', 'font', 'font/woff2', '');
		$actual_1 = self::$dao->update_hint( $new_hint, 10 );
		self::assertTrue( $actual_1['success'] );
	}

	public function test_delete_hint() {
		$actual_1 = self::$dao->delete_hint( '10' );
		self::assertTrue( $actual_1['success'] );
	}

	public function test_bulk_update() {
		$actual_1 = self::$dao->bulk_update( '10', 3 );
		self::assertTrue($actual_1['success']);

		$actual_2 = self::$dao->bulk_update( '11', 4 );
		self::assertTrue($actual_2['success']);
	}





//
//	public function test_get_hints() {
//		$hint_arr = Create_Hints::create_raw_hint_array('https://www.asdf.com/foozball', 'preconnect', 1);
//		$new_hint = Create_Hints::create_pprh_hint($hint_arr);
//		$expected = self::$dao->insert_hint($new_hint);
//		$id = $expected->db_result['hint_id'];
//
//		$expected = array_merge( array('id' => $id, 'status' => 'enabled', 'created_by' => '' ), $expected->new_hint );
//		$actual = self::$dao->get_hints()['0'];
//		self::assertEquals($expected, $actual);
//	}

//
//	public function test_get_multisite_tables() {
//		self::assertEquals(true, true);
//
//	}
//
//	public function test_create_table() {
//		self::assertEquals(true, true);
//
//	}

}
