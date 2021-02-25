<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PPRH\Create_Hints;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DAOTest extends TestCase {

//	public $url = '';


	public function test_create_hint(): int {
		$dao = new PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();
		$hint_arr = TestUtils::create_hint_array( 'https://www.asdf.com/foozball', 'preconnect', '', '', '', 1 );

		$new_hint = $create_hints->create_hint($hint_arr);

		$create_hint = $dao->insert_hint($new_hint);
		$hint_id = $create_hint->db_result['hint_id'];
		$expected = $dao->create_db_result( true, $hint_id, '', 'create', $new_hint );
		$this->assertEquals($expected, $create_hint);
		return $hint_id;
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_update_hint( int $hint_id ): void {
		$dao = new PPRH\DAO();
		$new_hint = TestUtils::create_hint_array( 'https://www.asdf2.com/foozball/blah.css', 'dns-prefetch', 'font', 'font/woff2', '', 0 );

		$result = $dao->update_hint( $new_hint, $hint_id );
		$expected = $dao->create_db_result( true, $hint_id, '', 'update', $new_hint );
		$this->assertEquals($expected, $result);
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_bulk_update( int $hint_ids ): void {
		$hint_id_str = (string) $hint_ids;
		$dao = new \PPRH\DAO();
		$action = 'disabled';
		$result = $dao->bulk_update( $hint_id_str, $action );
		$expected = $dao->create_db_result( true, $hint_id_str, '',$action, null );
		$this->assertEquals($expected, $result);
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_delete_hint( int $hint_ids ): void {
		$hint_id_str = (string) $hint_ids;
		$dao = new \PPRH\DAO();
		$result = $dao->delete_hint( $hint_id_str );
		$expected = $dao->create_db_result( true, $hint_id_str, '', 'delete', null );
		$this->assertEquals($expected, $result);
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
//		$this->assertEquals($expected, $actual);
//	}

//
//	public function test_get_multisite_tables(): void {
//		$this->assertEquals(true, true);
//
//	}
//
//	public function test_create_table(): void {
//		$this->assertEquals(true, true);
//
//	}

}