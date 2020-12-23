<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Create_HintsTest extends TestCase{

//	public function __construct() {}

	public function testInit(): void {
		define('CREATING_HINT', true);
		$create_hints = new \PPRH\Create_Hints();
		$test1 = \PPRH\Utils::create_hint_object('https://www.espn.com', 'dns-prefetch');
		$test2 = \PPRH\Utils::create_hint_object('ht<tps://www.e>\'sp"n.com', 'dns-prefetch');
		$test3 = \PPRH\Utils::create_hint_object('//espn.com', 'dns-prefetch');

		$test_hint1 = $create_hints->initialize($test1);
		$this->assertEquals($test_hint1['new_hint'], $test1);

		$test_hint2 = $create_hints->initialize($test2);
		$this->assertEquals($test_hint2['new_hint'], $test1);

		$test_hint3 = $create_hints->initialize($test3);
		$this->assertEquals($test_hint3['new_hint'], $test3);
	}


	public function testEmptyDataFails(): void {
		$create_hints = new \PPRH\Create_Hints();

		$data1 = (object) array(
			'url' => '',
			'hint_type' => 'dns-prefetch'
		);

		$bool1 = $create_hints->initialize($data1);
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

	// make sure 'https://www.espn.com' as a preconnect is added to db prior to running this.
	public function testDuplicateHintAttemptFails(): void {
		$create_hints = new \PPRH\Create_Hints();
		$test1 = \PPRH\Utils::create_hint_object('https://www.espn.com', 'preconnect');
		$test_hint1 = $create_hints->initialize($test1);
		$arr = array(
			'success' => false,
			'msg'     => 'An identical resource hint already exists!',
			'status'  => 'warning'
		);

		$this->assertEquals( $test_hint1['response'], $arr );
	}

}
