<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CreateHintsTest extends TestCase {

//	public function __construct() {}

	public function test_new_hint_controller():void {
		$dao = new \PPRH\DAO();
		$create_hints_util = new \PPRH\CreateHintsUtil();

		$dummy_hint = TestUtils::create_hint_array( 'https://free-hint.com', 'dns-prefetch', '', '', '' );
		var_dump($dummy_hint);
		$dummy_pprh_hint = $create_hints_util->new_hint_controller( $dummy_hint );

		$dummy_hint_result = $dao->insert_hint( $dummy_pprh_hint );

		$actual = $create_hints_util->new_hint_controller( $dummy_hint );
		$expected = $dao->create_db_result( false, '', 'A duplicate hint already exists!', 'create', null );


//		var_dump($actual);
		$this->assertEquals( $expected, $actual );
		$hint_id = $dummy_hint_result->db_result['hint_id'];
		$dao->delete_hint( $hint_id );
	}

	public function test_new_hint_controller_2():void {
		$dao = new \PPRH\DAO();
		$create_hints_util = new \PPRH\CreateHintsUtil();

		$dummy_hint = TestUtils::create_hint_array( 'https://global-hint.com', 'dns-prefetch', '', '', '', 'global', '/' );
		$dummy_pprh_hint = $create_hints_util->new_hint_controller( $dummy_hint );

		$dummy_hint_result = $dao->insert_hint( $dummy_pprh_hint );

		$actual = $create_hints_util->new_hint_controller( $dummy_hint );
//		$expected = $dao->create_db_result( false, '', 'A duplicate hint already exists!', 'create', null );


		$this->assertEquals( $dummy_pprh_hint, $actual );
		$hint_id = $dummy_hint_result->db_result['hint_id'];
		$dao->delete_hint( $hint_id );
	}

	public function test_new_hint_controller_3():void {
		$dao = new \PPRH\DAO();
		$create_hints_util = new \PPRH\CreateHintsUtil();
		$url = 'https://GlobalAndPost-hint.com';

		$post_hint = TestUtils::create_hint_array( $url, 'dns-prefetch', '', '', '', '2138', '/sitemap' );
		$dummy_pprh_post_hint = $create_hints_util->new_hint_controller( $post_hint );

		$dummy_post_hint_result = $dao->insert_hint( $dummy_pprh_post_hint );

		$dummy_global_hint = TestUtils::create_hint_array( $url, 'dns-prefetch', '', '', '', 'global', '/' );

		$actual = $create_hints_util->new_hint_controller( $dummy_global_hint );
//		$expected = $dao->create_db_result( false, '', 'A duplicate hint already exists!', 'create', null );


		$this->assertEquals( $dummy_global_hint, $actual );
		$hint_id = $dummy_post_hint_result->db_result['hint_id'];
		$dao->delete_hint( $hint_id );
	}

	public function test_duplicate_hints_exist_free() {
		$dao = new \PPRH\DAO();
		$create_hints_util = new \PPRH\CreateHintsUtil();

		$dup_hint = TestUtils::create_hint_array( 'https://duplicate-hint.com', 'dns-prefetch', '', '', '' );
		$error = 'A duplicate hint already exists!';

		$dummy_hint = $create_hints_util->new_hint_controller( $dup_hint );
		$dummy_hint_result = $dao->insert_hint( $dummy_hint );

		$dup_hint_error = $create_hints_util->new_hint_controller( $dup_hint );
		$expected = $dao->create_db_result( false, '', $error, 'create', null );

		$this->assertEquals( $expected, $dup_hint_error );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}

	public function test_duplicate_hints_exist_pro_1() {
		$dao = new \PPRH\DAO();
		$create_hints_util = new \PPRH\CreateHintsUtil();
		$dummy_hint = TestUtils::create_hint_array( 'https://test-get-duplicate-hints-pro-1.com', 'preconnect', '', '', '', '2326', '/test-page' );

		$dummy_hint_result = $dao->insert_hint( $dummy_hint );

		$actual = $create_hints_util->duplicate_hints_exist( $dummy_hint );

		$this->assertEquals( true, $actual );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}

	public function test_duplicate_hints_exist_pro_2() {
		$dao = new \PPRH\DAO();
		$create_hints_util = new \PPRH\CreateHintsUtil();
		$dummy_hint = TestUtils::create_hint_array( 'https://test-get-duplicate-hints-pro-2.com', 'preconnect', '', '', '', 'global', '/' );

		$dummy_hint_result = $dao->insert_hint( $dummy_hint );

		$new_dummy_hint = TestUtils::create_hint_array( 'https://test-get-duplicate-hints-pro-2.com', 'preconnect', '', '', '', '2326', '/test-page' );
		$actual_1 = $create_hints_util->duplicate_hints_exist( $new_dummy_hint );

		$new_dummy_hint_2 = TestUtils::create_hint_array( 'https://test-get-duplicate-hints-pro-asdf.com', 'preconnect', '', '', '', '2326', '/test-page' );
		$actual_2 = $create_hints_util->duplicate_hints_exist( $new_dummy_hint_2 );

		$this->assertEquals( true, $actual_1 );
		$this->assertEquals( false, $actual_2 );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}

	public function test_duplicate_hints_exist_pro_3() {
		if ( ! \PPRH\Utils::pprh_is_plugin_active() ) {
			return;
		}

		$dao = new \PPRH\DAO();
		$create_hints_util = new \PPRH\CreateHintsUtil();
		$dup_url = 'https://test-get-duplicate-hints-pro-3.com';
		$dummy_hint_1 = TestUtils::create_hint_array( $dup_url, 'preconnect', '', '', '', '2326', '/test-page' );

		$dummy_hint_result = $dao->insert_hint( $dummy_hint_1 );

		$dummy_hint_2 = TestUtils::create_hint_array( $dup_url, 'preconnect', '', '', '', '2326', '/test-page' );

		$actual = $create_hints_util->duplicate_hints_exist( $dummy_hint_2 );

		$this->assertEquals( true, $actual );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}

	public function test_get_duplicate_hints():void {
		global $wpdb;
		$dao = new \PPRH\DAO();
		$create_hints_util = new \PPRH\CreateHintsUtil();
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

		$actual = $create_hints_util->get_duplicate_hints( $test_hint );

		$this->assertEquals( $expected, $actual );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}

	public function test_get_duplicate_hints_pro():void {
		if ( ! \PPRH\Utils::pprh_is_plugin_active() ) {
			return;
		}

		global $wpdb;
		$dao = new \PPRH\DAO();
		$create_hints_util = new \PPRH\CreateHintsUtil();
		$table = PPRH_DB_TABLE;

		$test_hint = TestUtils::create_hint_array( 'https://test-get-duplicate-hints-pro.com', 'preconnect', '', '', '', '2326', '/test-page' );
		$dummy_hint_result = $dao->insert_hint( $test_hint );

		$expected = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE url = %s and hint_type = %s AND (post_id = %s OR post_id = %s)",
				$test_hint['url'],
				$test_hint['hint_type'],
				'global',
				$test_hint['post_id']
			), ARRAY_A
		);

		$actual = $create_hints_util->get_duplicate_hints( $test_hint );

		$this->assertEquals( $expected, $actual );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}



	public function test_create_hint_success(): void {
		$create_hints_util = new \PPRH\CreateHintsUtil();
		$test1 = TestUtils::create_hint_array( 'https://www.espn.com', 'dns-prefetch' );
		$test2 = TestUtils::create_hint_array( 'ht<tps://www.e>\'sp"n.com', 'dns-prefetch' );
		$test3 = TestUtils::create_hint_array( '//espn.com', 'dns-prefetch' );

		$test_hint1 = $create_hints_util->create_hint($test1);
		$this->assertEquals($test_hint1, $test1);

		$test_hint2 = $create_hints_util->create_hint($test2);
		$this->assertEquals($test_hint2, $test1);

		$test_hint3 = $create_hints_util->create_hint($test3);
		$this->assertEquals($test_hint3, $test3);
	}


	public function test_create_hint_fails(): void {
		$create_hints_util = new \PPRH\CreateHintsUtil();
		$data1 = TestUtils::create_hint_array( '', 'dns-prefetch' );
		$bool1 = $create_hints_util->create_hint($data1);
		$this->assertEquals(false, $bool1);
	}

	public function testGet_Url(): void {
		$create_hints = new \PPRH\CreateHints();
		$domain = 'https://ajax.googleapis.com';
		$long_url = 'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js';

		// these two hint types should only have the domain name
		$new_url1 = $create_hints->get_url($long_url, 'preconnect');
		$this->assertEquals($domain, $new_url1);

		$new_url2 = $create_hints->get_url($long_url, 'dns-prefetch');
		$this->assertEquals($domain, $new_url2);

		// for the 3 hint types below, the full URL shoul be used
		$new_url3 = $create_hints->get_url($long_url, 'preload');
		$this->assertEquals($long_url, $new_url3);

		$new_url4 = $create_hints->get_url($long_url, 'prerender');
		$this->assertEquals($long_url, $new_url4);

		$new_url5 = $create_hints->get_url($long_url, 'prefetch');
		$this->assertEquals($long_url, $new_url5);
	}



	public function testGetHintType(): void {
		$create_hints = new \PPRH\CreateHints();
		$hint1 = 'd$ns-prefetch';
		$hint2 = 'pre\'con>nect';
		$hint3 = 'pre#fetch';
		$hint4 = 'prelo1ad';

		$this->assertEquals($create_hints->get_hint_type($hint1), 'dns-prefetch');
		$this->assertEquals($create_hints->get_hint_type($hint2), 'preconnect');
		$this->assertEquals($create_hints->get_hint_type($hint3), 'prefetch');
		$this->assertEquals($create_hints->get_hint_type($hint4), 'preload');

	}

	public function testParseForDomainName(): void {
		$create_hints = new \PPRH\CreateHints();
		$url1 = 'espn.com';
		$url2 = 'https://example.com/asdflkasjd/asfdstest:8080';
		$url3 = '//example.co.uk';

		$this->assertEquals($create_hints->parse_for_domain_name($url1), '//espn.com');
		$this->assertEquals($create_hints->parse_for_domain_name($url2), 'https://example.com');
		$this->assertEquals($create_hints->parse_for_domain_name($url3), '//example.co.uk');
	}

//	public function testGetFileType(): void {
//		$create_hints = new \PPRH\CreateHints();
//	}

//	public function testSetCrossorigin(): void {
//		$create_hints = new \PPRH\CreateHints();
//	}

	public function testSetAsAttr(): void {
		$create_hints = new \PPRH\CreateHints();

		$as_attr1 = $create_hints->set_as_attr( 'video', '.mp4' );
		$as_attr2 = $create_hints->set_as_attr( '', '.mp4' );
		$as_attr3 = $create_hints->set_as_attr( '', '.mp3' );
		$as_attr4 = $create_hints->set_as_attr( '', '.woff' );
		$as_attr5 = $create_hints->set_as_attr( '', '.jpg' );
		$as_attr6 = $create_hints->set_as_attr( '', '.js' );
		$as_attr7 = $create_hints->set_as_attr( '', '.css' );
		$as_attr8 = $create_hints->set_as_attr( '', '.webm' );

		$this->assertEquals( 'video', $as_attr1 );
		$this->assertEquals( 'video', $as_attr2 );
		$this->assertEquals( 'audio', $as_attr3 );
		$this->assertEquals( 'font', $as_attr4 );
		$this->assertEquals( 'image', $as_attr5 );
		$this->assertEquals( 'script', $as_attr6 );
		$this->assertEquals( 'style', $as_attr7 );
		$this->assertEquals( 'video', $as_attr8 );
	}

//	public function testSetTypeAttr(): void {
//		$create_hints = new \PPRH\CreateHints();
//	}
//
//	public function testGetFileTypeMime(): void {
//		$create_hints = new \PPRH\CreateHints();
//	}
//
//
//	public function testGetDuplicateHints(): void {
//		$create_hints = new \PPRH\CreateHints();
//	}

	// make sure 'https://www.espn.com' as a preconnect is added to db prior to running this.
//	public function testDuplicateHintAttemptFails(): void {
//		$create_hints = new \PPRH\CreateHints();
//		$test1 = \PPRH\Create_Hints::create_raw_hint_array('https://www.espn.com', 'preconnect');
//		$test_hint1 = $create_hints->duplicate_hints_exist($test1);
//		$arr = array(
//			'success' => false,
//			'msg'     => 'An identical resource hint already exists!',
//			'status'  => 'warning'
//		);
//
//		$this->assertEquals( $test_hint1['response'], $arr );
//	}

}
