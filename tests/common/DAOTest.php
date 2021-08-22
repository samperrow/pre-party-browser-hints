<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DAOTest extends TestCase {

	public static $dao;

	public function test_init() {
		self::$dao = new \PPRH\DAO();
	}

	public function test_create_db_result() {

		$success_1 = true;
		$actual_1 = \PPRH\DAO::create_db_result( $success_1, 0, 0, null );
		$expected_1 = array( 'msg' => 'Resource hint created successfully.', 'status' => $success_1 );
		self::assertEquals( $expected_1, $actual_1->db_result );

		$success_2 = false;
		$actual_2 = \PPRH\DAO::create_db_result($success_2, 0, 0, null );
		$expected_2 = array( 'msg' => 'Failed to create hint.', 'status' => $success_2 );
		self::assertEquals( $expected_2, $actual_2->db_result );

		$success_3 = true;
		$actual_3 = \PPRH\DAO::create_db_result($success_3, 1, 0, null );
		$expected_3 = array( 'msg' => 'Resource hint updated successfully.', 'status' => $success_3 );
		self::assertEquals( $expected_3, $actual_3->db_result );

		$success_4 = true;
		$actual_4 = \PPRH\DAO::create_db_result($success_4, 4, 0, null );
		$expected_4 = array( 'msg' => 'Resource hint disabled successfully.', 'status' => $success_4 );
		self::assertEquals( $expected_4, $actual_4->db_result );

		$success_5 = true;
		$actual_5 = \PPRH\DAO::create_db_result($success_5, 5, 0, null );
		$expected_5 = array( 'msg' => 'Auto preconnect hints for this post have been reset. Please load this page on the front end to re-create the hints.', 'status' => $success_5 );
		self::assertEquals( $expected_5, $actual_5->db_result );

		$success_6 = false;
		$actual_6 = \PPRH\DAO::create_db_result($success_6, 5, 1, null );
		$expected_6 = array( 'msg' => 'Failed to reset this post\'s preconnect hint data. Please refresh the page and try again.', 'status' => $success_6 );
		self::assertEquals( $expected_6, $actual_6->db_result );

//		$success_7 = true;
//		$actual_7 = \PPRH\DAO::create_db_result($success_7, 6, 0, null );
//		$expected_7 = array( 'msg' => 'Preload hints for this post have been reset. Please load this page on the front end to re-create the hints.', 'status' => $success_7 );
//		self::assertEquals( $expected_7, $actual_7->db_result );

//		$success_8 = false;
//		$actual_8 = \PPRH\DAO::create_db_result($success_8, 6, 1, null );
//		$expected_8 = array( 'msg' => 'Failed to reset this post\'s preload hint data. Please refresh the page and try again.', 'status' => $success_8 );
//		self::assertEquals( $expected_8, $actual_8->db_result );

		$success_9 = true;
		$actual_9 = \PPRH\DAO::create_db_result($success_9, 7, 0, null );
		$expected_9 = array( 'msg' => 'Prerender hint successfully created for this post.', 'status' => $success_9 );
		self::assertEquals( $expected_9, $actual_9->db_result );

		$success_10 = true;
		$actual_10 = \PPRH\DAO::create_db_result($success_10, 7, 1, null );
		$expected_10 = array( 'msg' => 'Prerender hints have been successfully set for all posts with sufficiently available data.', 'status' => $success_10 );
		self::assertEquals( $expected_10, $actual_10->db_result );

		$success_11 = false;
		$actual_11 = \PPRH\DAO::create_db_result($success_11, 7, 2, null );
		$expected_11 = array( 'msg' => 'There is not enough analytics data for this page to generate accurate prerender hints yet. Please try again soon.', 'status' => $success_11 );
		self::assertEquals( $expected_11, $actual_11->db_result );
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

		$table = self::$dao->table;

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
		$table = self::$dao->table;

		$actual_1 = self::$dao->get_client_hints_query( array() );
		$sql = "SELECT * FROM $table WHERE status = %s";
		self::assertEquals( 'enabled', $actual_1['args'][0] );
		self::assertTrue( str_contains( $actual_1['sql'], $sql ) );
	}

	public function test_insert_hint() {
		$create_hints = new \PPRH\CreateHints();

		$hint_1 = TestUtils::create_hint_array( 'https://www.asdf.com/foozball', 'preconnect', '', '', '', '', '2145' );
		$new_hint_1 = $create_hints->create_hint($hint_1);
		$actual_1 = self::$dao->insert_hint( $new_hint_1 );
		$expected = \PPRH\DAO::create_db_result( true, 0, 0, $new_hint_1 );
		self::assertEquals( $expected, $actual_1 );
	}

	public function test_update_hint() {
		$new_hint = TestUtils::create_hint_array( 'https://www.asdf2.com/foozball/blah.css', 'dns-prefetch', 'font', 'font/woff2', '');
		$result = self::$dao->update_hint( $new_hint, 10 );
		$expected = \PPRH\DAO::create_db_result( true, 1, 0, $new_hint );
		self::assertEquals($expected, $result);
	}

	public function test_delete_hint() {
		$actual_1 = \PPRH\DAO::delete_hint( '10' );
		$expected_1 = \PPRH\DAO::create_db_result( true, 2, 0, null );
		self::assertEquals($expected_1, $actual_1);
	}

	public function test_bulk_update() {
		$actual_1 = self::$dao->bulk_update( '10', 3 );
		$expected_1 = \PPRH\DAO::create_db_result( true, 3, 0, null );
		self::assertEquals($expected_1, $actual_1);

		$actual_2 = self::$dao->bulk_update( '11', 4 );
		$expected_2 = \PPRH\DAO::create_db_result( true, 4, 0, null );
		self::assertEquals($expected_2, $actual_2);
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
