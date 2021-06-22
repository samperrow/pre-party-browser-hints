<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DAOTest extends TestCase {

	public function test_create_db_result():void {
		$dao = new \PPRH\DAO();
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

		$test1 = \PPRH\DAO::create_db_result( true, '', null, 0, null );

		self::assertEquals( $expected, $test1 );
	}

	public function test_create_hint(): int {
		$dao = new PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();
		$hint_arr = TestUtils::create_hint_array( 'https://www.asdf.com/foozball', 'preconnect', '', '', '');

		$new_hint = $create_hints->create_hint($hint_arr);

		$create_hint = $dao->insert_hint($new_hint);
		$hint_id = $create_hint->db_result['hint_id'];
		$expected = \PPRH\DAO::create_db_result( true, $hint_id, '', 0, $new_hint );
		self::assertEquals($expected, $create_hint);
		return $hint_id;
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_update_hint( int $hint_id ): void {
		$dao = new PPRH\DAO();
		$new_hint = TestUtils::create_hint_array( 'https://www.asdf2.com/foozball/blah.css', 'dns-prefetch', 'font', 'font/woff2', '');

		$result = $dao->update_hint( $new_hint, $hint_id );
		$expected = \PPRH\DAO::create_db_result( true, $hint_id, '', 1, $new_hint );
		self::assertEquals($expected, $result);
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_bulk_update( int $hint_ids ): void {
		$hint_id_str = (string) $hint_ids;
		$dao = new \PPRH\DAO();
		$result = $dao->bulk_update( $hint_id_str, 4 );
		$expected = \PPRH\DAO::create_db_result( true, $hint_id_str, '', 4, null );
		self::assertEquals($expected, $result);
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_delete_hint( int $hint_ids ): void {
		$hint_id_str = (string) $hint_ids;
		$dao = new \PPRH\DAO();
		$result = $dao->delete_hint( $hint_id_str );
		$expected = \PPRH\DAO::create_db_result( true, $hint_id_str, '', 2, null );
		self::assertEquals($expected, $result);
	}


//
//	public function test_get_hints(): void {
//		$dao = new PPRH\DAO();
//		$hint_arr = Create_Hints::create_raw_hint_array('https://www.asdf.com/foozball', 'preconnect', 1);
//		$new_hint = Create_Hints::create_pprh_hint($hint_arr);
//		$expected = $dao->insert_hint($new_hint);
//		$id = $expected->db_result['hint_id'];
//
//		$expected = array_merge( array('id' => $id, 'status' => 'enabled', 'created_by' => '' ), $expected->new_hint );
//		$actual = $dao->get_hints()['0'];
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