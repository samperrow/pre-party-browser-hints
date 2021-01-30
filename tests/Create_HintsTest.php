<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use PPRH\Create_Hints;
use PPRH\PPRH_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Create_HintsTest extends TestCase {

//	public function __construct() {}

	public function testConstructor(): void {
//		define('CREATING_HINT', true);
		$create_hints = new \PPRH\Create_Hints();

		$new_hint = array();

		$result = array(
			'new_hint' => $new_hint,
			'response' => array(
				'msg'     => '',
				'status'  => '',
				'success' => false
			),
		);


		$this->assertClassHasAttribute('result', \PPRH\Create_Hints::class);
		$this->assertEquals($create_hints->result, $result);

		if ( is_plugin_active( 'pprh-pro' ) ) {
			$this->assertTrue( class_exists(\PPRH\Create_Hints_Child::class ) );
		}
	}

	public function test_duplicate_hints_exist() {
		$dao = new \PPRH\DAO();
		$dup_hint = \PPRH\Create_Hints::create_raw_hint_array( 'https://duplicate-hint.com', 'dns-prefetch', 0 );
		$error = 'A duplicate hint already exists!';

		$dummy_hint = \PPRH\Create_Hints::create_pprh_hint( $dup_hint );
		$dummy_hint_result = $dao->create_hint( $dummy_hint, null );

		$dup_hint_error = \PPRH\Create_Hints::create_pprh_hint( $dup_hint );
		$expected = $dao->create_db_result( false, '', $error, 'create', null );

		$this->assertEquals( $expected, $dup_hint_error );
		$dao->delete_hint( $dummy_hint_result->db_result['hint_id'] );
	}


	public function test_create_hint_success(): void {
		$create_hints = new \PPRH\Create_Hints();
		$test1 = \PPRH\Create_Hints::create_raw_hint_array('https://www.espn.com', 'dns-prefetch');
		$test2 = \PPRH\Create_Hints::create_raw_hint_array('ht<tps://www.e>\'sp"n.com', 'dns-prefetch');
		$test3 = \PPRH\Create_Hints::create_raw_hint_array('//espn.com', 'dns-prefetch');

		$test_hint1 = $create_hints->create_hint($test1);
		$this->assertEquals($test_hint1, $test1);

		$test_hint2 = $create_hints->create_hint($test2);
		$this->assertEquals($test_hint2, $test1);

		$test_hint3 = $create_hints->create_hint($test3);
		$this->assertEquals($test_hint3, $test3);
	}


	public function test_create_hint_fails(): void {
		$create_hints = new \PPRH\Create_Hints();

		$data1 = array(
			'url'       => '',
			'hint_type' => 'dns-prefetch'
		);

		$bool1 = $create_hints->create_hint($data1);
		$this->assertEquals(false, $bool1);
	}

	public function testGet_Url(): void {
		$create_hints = new \PPRH\Create_Hints();
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
		$create_hints = new \PPRH\Create_Hints();
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
		$create_hints = new \PPRH\Create_Hints();
		$url1 = 'espn.com';
		$url2 = 'https://example.com/asdflkasjd/asfdstest:8080';
		$url3 = '//example.co.uk';

		$this->assertEquals($create_hints->parse_for_domain_name($url1), '//espn.com');
		$this->assertEquals($create_hints->parse_for_domain_name($url2), 'https://example.com');
		$this->assertEquals($create_hints->parse_for_domain_name($url3), '//example.co.uk');
	}

//	public function testGetFileType(): void {
//		$create_hints = new \PPRH\Create_Hints();
//	}

//	public function testSetCrossorigin(): void {
//		$create_hints = new \PPRH\Create_Hints();
//	}

	public function testSetAsAttr(): void {
		$create_hints = new \PPRH\Create_Hints();

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
//		$create_hints = new \PPRH\Create_Hints();
//	}
//
//	public function testGetFileTypeMime(): void {
//		$create_hints = new \PPRH\Create_Hints();
//	}
//
//
//	public function testGetDuplicateHints(): void {
//		$create_hints = new \PPRH\Create_Hints();
//	}

	// make sure 'https://www.espn.com' as a preconnect is added to db prior to running this.
//	public function testDuplicateHintAttemptFails(): void {
//		$create_hints = new \PPRH\Create_Hints();
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
