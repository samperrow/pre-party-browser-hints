<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CreateHintsTest extends TestCase {

//	public function __construct()

	public function test_create_hint_success(): void {
		$create_hints = new \PPRH\CreateHints();
		$test1 = TestUtils::create_hint_array( 'https://www.espn.com', 'dns-prefetch' );
		$test2 = TestUtils::create_hint_array( 'ht<tps://www.e>\'sp"n.com', 'dns-prefetch' );
		$test3 = TestUtils::create_hint_array( '//espn.com', 'dns-prefetch' );

		$test_hint1 = $create_hints->create_hint($test1);
		$this->assertEquals($test_hint1, $test1);

		$test_hint2 = $create_hints->create_hint($test2);
		$this->assertEquals($test_hint2, $test1);

		$test_hint3 = $create_hints->create_hint($test3);
		$this->assertEquals($test_hint3, $test3);
	}


	public function test_create_hint_fails(): void {
		$create_hints = new \PPRH\CreateHints();
		$data1 = TestUtils::create_hint_array( '', 'dns-prefetch' );
		$bool1 = $create_hints->create_hint($data1);
		$this->assertEquals(false, $bool1);
	}

	public function test_handle_duplicate_hints_free() {
		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();

		$hint_1 = TestUtils::create_hint_array( 'https://dup-hint1.com', 'dns-prefetch', '', '', '' );

		$pprh_hint_1 = $create_hints->new_hint_controller( $hint_1 );
		$db_response = $dao->insert_hint($pprh_hint_1);

		$hint_2 = TestUtils::create_hint_array( 'https://dup-hint1.com', 'dns-prefetch', '', '', '');

		$create_hints = new \PPRH\CreateHints();
		$actual = $create_hints->new_hint_controller( $hint_2 );

		$expected = $dao->create_db_result( false, '', 'A duplicate hint already exists!', 'create', null );

		// attempt to create post hint when global hint is already in place should fail.
		$this->assertEquals( $expected, $actual );
		$dao->delete_hint( $db_response->db_result['hint_id'] );
	}



	public function test_handle_duplicate_hints_1() {
		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();

		$hint_1 = TestUtils::create_hint_array( 'https://dup-hint1.com', 'dns-prefetch', '', '', '', 'global' );

		$pprh_hint_1 = $create_hints->new_hint_controller( $hint_1 );
		$db_response = $dao->insert_hint($pprh_hint_1);

		$hint_2 = TestUtils::create_hint_array( 'https://dup-hint1.com', 'dns-prefetch', '', '', '', '2138' );
		$create_hints = new \PPRH\CreateHints();
		$actual = $create_hints->new_hint_controller( $hint_2 );

		$expected = $dao->create_db_result( false, '', 'A duplicate hint already exists!', 'create', null );

		// attempt to create post hint when global hint is already in place should fail.
		$this->assertEquals( $expected, $actual );
		$dao->delete_hint( $db_response->db_result['hint_id'] );
	}

	public function test_handle_duplicate_hints_2() {
		if ( ! \PPRH\Utils::pprh_is_plugin_active() ) {
			return;
		}

		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();

		$hint_1 = TestUtils::create_hint_array( 'https://dup-hint2.com', 'dns-prefetch', '', '', '', '', '2138');
		$hint_2 = TestUtils::create_hint_array( 'https://dup-hint2.com', 'dns-prefetch', '', '', '', '', 'global');

		$pprh_hint_1 = $create_hints->new_hint_controller( $hint_1 );
		$db_response = $dao->insert_hint($pprh_hint_1);

		$actual = $create_hints->new_hint_controller( $hint_2 );
		$expected = $create_hints->create_hint($hint_2);

		$this->assertEquals( $expected, $actual );
		$dao->delete_hint( $db_response->db_result['hint_id'] );
	}

	public function test_new_hint_controller():void {
		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();

		$dummy_hint = TestUtils::create_hint_array( 'https://free-hint.com', 'dns-prefetch', '', '', '' );
		$dummy_pprh_hint = $create_hints->new_hint_controller( $dummy_hint );

		$dummy_hint_result = $dao->insert_hint( $dummy_pprh_hint );

		$create_hints = new \PPRH\CreateHints();
		$actual = $create_hints->new_hint_controller( $dummy_hint );
		$expected = $dao->create_db_result( false, '', 'A duplicate hint already exists!', 'create', null );


		$this->assertEquals( $expected, $actual );
		$hint_id = $dummy_hint_result->db_result['hint_id'];
		$dao->delete_hint( $hint_id );
	}

	public function test_new_hint_controller_2():void {
		if ( ! \PPRH\Utils::pprh_is_plugin_active() ) {
			return;
		}

		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();

		$dummy_hint = TestUtils::create_hint_array( 'https://global-hint.com', 'dns-prefetch', '', '', '', '', 'global');
		$dummy_pprh_hint = $create_hints->new_hint_controller( $dummy_hint );

		$dummy_hint_result = $dao->insert_hint( $dummy_pprh_hint );
		$actual = $create_hints->new_hint_controller( $dummy_hint );

		$this->assertEquals( $dummy_pprh_hint, $actual );
		$hint_id = $dummy_hint_result->db_result['hint_id'];
		$dao->delete_hint( $hint_id );
	}

	public function test_new_hint_controller_3():void {
		if ( ! \PPRH\Utils::pprh_is_plugin_active() ) {
			return;
		}

		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();
		$url = 'https://GlobalAndPost-hint.com';

		$post_hint = TestUtils::create_hint_array( $url, 'dns-prefetch', '', '', '', '', '2138' );
		$dummy_pprh_post_hint = $create_hints->new_hint_controller( $post_hint );

		$dummy_post_hint_result = $dao->insert_hint( $dummy_pprh_post_hint );

		$dummy_global_hint = TestUtils::create_hint_array( $url, 'dns-prefetch', '', '', '', '', 'global' );

		$actual = $create_hints->new_hint_controller( $dummy_global_hint );

		$this->assertEquals( $dummy_global_hint, $actual );
		$hint_id = $dummy_post_hint_result->db_result['hint_id'];
		$dao->delete_hint( $hint_id );
	}


	public function test_duplicate_hints_exist_free() {
		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();

		$dup_hint = TestUtils::create_hint_array( 'https://duplicate-hint.com', 'dns-prefetch', '', '', '' );
		$error = 'A duplicate hint already exists!';

		$dummy_hint = $create_hints->new_hint_controller( $dup_hint );
		$dummy_hint_result = $dao->insert_hint( $dummy_hint );

		$create_hints = new \PPRH\CreateHints();
		$dup_hint_error = $create_hints->new_hint_controller( $dup_hint );
		$expected = $dao->create_db_result( false, '', $error, 'create', null );

		$this->assertEquals( $expected, $dup_hint_error );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}

	public function test_duplicate_hints_exist_pro_1() {
		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();
		$dummy_hint = TestUtils::create_hint_array( 'https://test-get-duplicate-hints-pro-1.com', 'preconnect', '', '', '', '', '2326' );

		$dummy_hint_result = $dao->insert_hint( $dummy_hint );

		$actual = $create_hints->duplicate_hints_exist( $dummy_hint );

		$this->assertEquals( true, $actual );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}

	public function test_duplicate_hints_exist_pro_2() {
		$create_hints = new \PPRH\CreateHints();
		$dummy_hint = TestUtils::create_hint_array( 'https://test-get-duplicate-hints-pro-2.com', 'preconnect', '', '', '', '', 'global' );
		$dummy_hint_2 = TestUtils::create_hint_array( 'https://test-get-duplicate-hints-pro-asdf.com', 'preconnect', '', '', '', '', '2326' );

		$arr = array( $dummy_hint, $dummy_hint_2 );
		$actual_1 = $create_hints->duplicate_hints_exist( $arr );
		$actual_2 = $create_hints->duplicate_hints_exist( array() );

		$this->assertEquals( true, $actual_1 );
		$this->assertEquals( false, $actual_2 );
	}

	public function test_duplicate_hints_exist_pro_3() {
		if ( ! \PPRH\Utils::pprh_is_plugin_active() ) {
			return;
		}

		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();
		$dup_url = 'https://test-get-duplicate-hints-pro-3.com';
		$dummy_hint_1 = TestUtils::create_hint_array( $dup_url, 'preconnect', '', '', '', '', '2326' );

		$dummy_hint_result = $dao->insert_hint( $dummy_hint_1 );

		$dummy_hint_2 = TestUtils::create_hint_array( $dup_url, 'preconnect', '', '', '', '', '2326' );

		$actual = $create_hints->duplicate_hints_exist( $dummy_hint_2 );

		$this->assertEquals( true, $actual );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}

	public function test_get_duplicate_hints():void {
		global $wpdb;
		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();
		$table = PPRH_DB_TABLE;

		$test_hint = TestUtils::create_hint_array( 'https://test-get-duplicate-hints.com', 'preconnect', '', '', '');
		$dummy_hint_result = $dao->insert_hint( $test_hint );

		$expected = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE url = %s and hint_type = %s",
				$test_hint['url'],
				$test_hint['hint_type']
			), ARRAY_A
		);

		$actual = $create_hints->get_duplicate_hints( $test_hint );

		$this->assertEquals( count($expected), count($actual) );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}

	public function test_get_duplicate_hints_pro():void {
		if ( ! \PPRH\Utils::pprh_is_plugin_active() ) {
			return;
		}

		global $wpdb;
		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();
		$table = PPRH_DB_TABLE;

		$test_hint = TestUtils::create_hint_array( 'https://test-get-duplicate-hints-pro.com', 'preconnect', '', '', '', '', '2326' );
		$dummy_hint_result = $dao->insert_hint( $test_hint );

		$expected = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE url = %s and hint_type = %s AND (post_id = %s OR post_id = %s)",
				array( $test_hint['url'], $test_hint['hint_type'], 'global', $test_hint['post_id'] )
			), ARRAY_A
		);

		$actual = $create_hints->get_duplicate_hints( $test_hint );

		$this->assertEquals( $expected, $actual );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}

//	public function test_duplicate_hints_exist() {
//
//	}

	public function test_resolve_duplicate_hints() {
		$create_hints = new \PPRH\CreateHints();

		$hint_1 = TestUtils::create_hint_array( 'asdf.com', 'dns-prefetch', '', '', '');
		$hint_2 = TestUtils::create_hint_array( 'asdf.com', 'dns-prefetch', '', '', '', '', 'global');

		$actual_1 = $create_hints->resolve_duplicate_hints( $hint_1 );
		$actual_2 = $create_hints->resolve_duplicate_hints( $hint_2 );


		$expected = \PPRH\Utils::pprh_is_plugin_active();


		$this->assertEquals( false, $actual_1 );
		$this->assertEquals( $expected, $actual_2 );
	}

}
