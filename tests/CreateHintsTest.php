<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CreateHintsTest extends TestCase {

//	public function __construct()

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

	public function test_get_url(): void {
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



	public function test_getHintType(): void {
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

	public function test_parse_for_domain_name(): void {
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

	public function test_set_as_attr(): void {
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
