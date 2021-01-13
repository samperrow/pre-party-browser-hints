<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PPRH\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DAOTest extends TestCase {

//	/**
//	 * @before
//	 */

	public function asdf() {

	}


	public function test_create_hint(): int {
		$dao = new PPRH\DAO();
		$hint_obj = Utils::create_raw_hint_object('https://www.asdf.com/foozball', 'preconnect', 1);
		$new_hint = Utils::create_pprh_hint($hint_obj);
		$create_hint = $dao->create_hint($new_hint, null);
		$hint_id = $create_hint['db_result']['hint_id'];
		$expected = Utils::create_db_response( true, $hint_id, '', 'created', $new_hint );
		$this->assertEquals($expected, $create_hint);
		return $hint_id;
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_update_hint( int $hint_id): void {
		$dao = new PPRH\DAO();
		$new_hint = Utils::create_raw_hint_object( 'https://www.asdf2.com/foozball/blah.css', 'dns-prefetch', 0, 'font', 'font/woff2', '' );
		$result = $dao->update_hint( $new_hint, $hint_id );
		$expected = Utils::create_db_response( true, $hint_id, '', 'updated', $new_hint );
		$this->assertEquals($expected, $result);
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_bulk_update( int $hint_ids ): void {
		$hint_id_str = (string) $hint_ids;
		$dao = new PPRH\DAO();
		$result = $dao->bulk_update( $hint_id_str, 'disabled' );
		$expected = Utils::create_db_response( true, $hint_id_str, '', 'disabled', null );
		$this->assertEquals($expected, $result);
	}

	/**
	 * @depends test_create_hint
	 */
	public function test_delete_hint( int $hint_ids ): void {
		$hint_id_str = (string) $hint_ids;
		$dao = new PPRH\DAO();
		$result = $dao->delete_hint( $hint_id_str );
		$expected = Utils::create_db_response( true, $hint_id_str, '', 'deleted', null );
		$this->assertEquals($expected, $result);
	}


//
//	public function test_remove_prev_auto_preconnects(): void {
//		$this->assertEquals(true, true);
//
//	}
//
//	public function test_get_hints(): void {
//		$this->assertEquals(true, true);
//
//	}
//
//	public function test_get_hints_query(): void {
//		$this->assertEquals(true, true);
//
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