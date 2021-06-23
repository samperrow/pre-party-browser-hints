<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DAOTest extends TestCase {

	public $dao;

	/**
	 * @before
	 */
	public function test_init():void {
		$this->dao = new \PPRH\DAO();
	}


	public function test_create_db_result():void {
		$result = true;
		$new_hint = null;

		$expected = (object) array(
			'new_hint'  => $new_hint,
			'db_result' => array(
				'msg'        => 'Resource hint created successfully.',
				'status'     => ( $result ) ? 'success' : 'error',
				'success'    => $result,
				'hint_id'    => '',
				'last_error' => '',
			)
		);

		$actual_1 = \PPRH\DAO::create_db_result( true, '', '', 0, null );
		self::assertEquals( $expected, $actual_1 );
	}

	public function test_create_msg():void {
		$actions_1 = array( 'create', 'created' );
		$actual_1 = \PPRH\DAO::create_msg( true, $actions_1 );
		$expected_1 = "Resource hint $actions_1[1] successfully.";
		self::assertEquals( $expected_1, $actual_1 );

		$actions_2 = array( 'delete', 'deleted' );
		$actual_2 = \PPRH\DAO::create_msg( false, $actions_2 );
		$expected_2 = "Failed to $actions_2[0] hint.";
		self::assertEquals( $expected_2, $actual_2 );
	}

	public function test_get_admin_hints_query():void {
		if ( PPRH_PRO_PLUGIN_ACTIVE ) {
			return;
		}

		$table = $this->dao->table;

		$actual_1 = $this->dao->get_admin_hints_query();
		$expected_1 = array(
			'sql'  => "SELECT * FROM $table ORDER BY url ASC",
			'args' => array()
		);
		self::assertEquals( $expected_1, $actual_1 );

		$_REQUEST['orderby'] = 'hint_type';
		$_REQUEST['order'] = 'asc';
		$actual_2 = $this->dao->get_admin_hints_query();
		$expected_2 = array(
			'sql'  => "SELECT * FROM $table ORDER BY hint_type asc",
			'args' => array()
		);
		self::assertEquals( $expected_2, $actual_2 );

		$_REQUEST['orderby'] = 'url';
		$_REQUEST['order'] = 'desc';
		$actual_3 = $this->dao->get_admin_hints_query();
		$expected_3 = array(
			'sql'  => "SELECT * FROM $table ORDER BY url desc",
			'args' => array()
		);
		self::assertEquals( $expected_3, $actual_3 );

		unset( $_REQUEST['orderby'], $_REQUEST['order'] );
	}

	public function test_get_client_hints_query():void {
		if ( PPRH_PRO_PLUGIN_ACTIVE ) {
			return;
		}

		$table = $this->dao->table;

		$actual_1 = $this->dao->get_client_hints_query();
		$expected_1 = array(
			'sql' => "SELECT * FROM $table WHERE status = %s",
			'args' => array( 'enabled' )
		);
		self::assertEquals( $expected_1, $actual_1 );
	}

	public function test_create_hint(): int {
		$create_hints = new \PPRH\CreateHints();
		$hint_arr = TestUtils::create_hint_array( 'https://www.asdf.com/foozball', 'preconnect', '', '', '');

		$new_hint = $create_hints->create_hint($hint_arr);

		$create_hint = $this->dao->insert_hint($new_hint);
		$hint_id = $create_hint->db_result['hint_id'];
		$expected = \PPRH\DAO::create_db_result( true, $hint_id, '', 0, $new_hint );
		self::assertEquals($expected, $create_hint);
		return $hint_id;
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_update_hint( int $hint_id ): void {
		$new_hint = TestUtils::create_hint_array( 'https://www.asdf2.com/foozball/blah.css', 'dns-prefetch', 'font', 'font/woff2', '');

		$result = $this->dao->update_hint( $new_hint, $hint_id );
		$expected = \PPRH\DAO::create_db_result( true, $hint_id, '', 1, $new_hint );
		self::assertEquals($expected, $result);
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_bulk_update( int $hint_ids ): void {
		$hint_id_str = (string) $hint_ids;
		$result = $this->dao->bulk_update( $hint_id_str, 4 );
		$expected = \PPRH\DAO::create_db_result( true, $hint_id_str, '', 4, null );
		self::assertEquals($expected, $result);
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_delete_hint( int $hint_ids ): void {
		$hint_id_str = (string) $hint_ids;
		$result = $this->dao->delete_hint( $hint_id_str );
		$expected = \PPRH\DAO::create_db_result( true, $hint_id_str, '', 2, null );
		self::assertEquals($expected, $result);
	}


//
//	public function test_get_hints(): void {
//		$hint_arr = Create_Hints::create_raw_hint_array('https://www.asdf.com/foozball', 'preconnect', 1);
//		$new_hint = Create_Hints::create_pprh_hint($hint_arr);
//		$expected = $this->dao->insert_hint($new_hint);
//		$id = $expected->db_result['hint_id'];
//
//		$expected = array_merge( array('id' => $id, 'status' => 'enabled', 'created_by' => '' ), $expected->new_hint );
//		$actual = $this->dao->get_hints()['0'];
//		self::assertEquals($expected, $actual);
//	}

//
//	public function test_get_multisite_tables(): void {
//		self::assertEquals(true, true);
//
//	}
//
//	public function test_create_table(): void {
//		self::assertEquals(true, true);
//
//	}

}