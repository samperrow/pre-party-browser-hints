<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HintBuilderTest extends TestCase {

	public static $hint_builder;

	public function test_start() {
		self::$hint_builder = new \PPRH\HintBuilder();
	}

	public function test_create_pprh_hint() {
		$hint_1 = \PPRH\Utils::create_raw_hint( '', 'preload', 0, '', '', '', '' );
		$actual_1 = self::$hint_builder->create_pprh_hint( $hint_1 );
		self::assertFalse( $actual_1 );

		$hint_2 = \PPRH\Utils::create_raw_hint( 'https://espn.com/asdf/script.js', 'preload', 0, '', '', '', '' );
		$actual_2 = self::$hint_builder->create_pprh_hint( $hint_2 );
		$expected_2 = \PPRH\Utils::create_raw_hint( 'https://espn.com/asdf/script.js', 'preload', 0, 'script', 'text/javascript', '', '' );
		self::assertSame( $expected_2, $actual_2 );

		$url_1 = 'https://sphacks.local/wp-content/themes/sphacks/images/icons/newspaper.woff?19';

		$test1 = \PPRH\Utils::create_raw_hint( 'https://www.espn.com', 'dns-prefetch' );
		$expected_1 = self::$hint_builder->create_pprh_hint($test1);
		self::assertEquals($expected_1, $test1);

		$test2 = \PPRH\Utils::create_raw_hint( 'ht<tps://www.e>\'sp"n.com', 'dns-prefetch', 1, 'font', 'font/woff2', 'crossorigin', '(max-width:600px)' );
		$actual_hint_2 = self::$hint_builder->create_pprh_hint($test2);
		$test2['url'] = 'https://www.espn.com';
		self::assertEquals($test2, $actual_hint_2);

		$test3 = \PPRH\Utils::create_raw_hint( '//espn.com', 'dns-prefetch' );
		$test_hint3 = self::$hint_builder->create_pprh_hint($test3);
		self::assertEquals($test_hint3, $test3);

		$test_4 = \PPRH\Utils::create_raw_hint( '//espn.com', '' );
		$test_hint_4 = self::$hint_builder->create_pprh_hint($test_4);
		self::assertEquals(false, $test_hint_4);

		$data1 = \PPRH\Utils::create_raw_hint( '', 'dns-prefetch' );
		$bool1 = self::$hint_builder->create_pprh_hint($data1);
		self::assertEquals(false, $bool1);

		$test_6 = \PPRH\Utils::create_raw_hint( $url_1, 'preload', 1, 'font', 'font/woff', 'crossorigin', '' );
		$actual_6 = self::$hint_builder->create_pprh_hint( $test_6 );
		self::assertEquals( $test_6, $actual_6);

		$test_7 = \PPRH\Utils::create_raw_hint( 'https://www.espn.com/asdf something/page', 'dns-prefetch' );
		$actual_7 = self::$hint_builder->create_pprh_hint($test_7);
		$expected_7 = \PPRH\Utils::create_raw_hint( 'https://www.espn.com', 'dns-prefetch' );
		self::assertEquals($expected_7, $actual_7);

		$test_8 = \PPRH\Utils::create_raw_hint( "https://www.es\tpn.com/asdf/something/page", 'dns-prefetch' );
		$actual_8 = self::$hint_builder->create_pprh_hint($test_8);
		$expected_8 = \PPRH\Utils::create_raw_hint( 'https://www.espn.com', 'dns-prefetch' );
		self::assertEquals($expected_8, $actual_8);
	}

	public function test_get_hint_type() {
		$actual_1 = self::$hint_builder->get_hint_type( 'dnsprefetch' );
		self::assertSame( '', $actual_1 );

		$actual_2 = self::$hint_builder->get_hint_type( 'dnsprefeasdfasdftch' );
		self::assertSame( '', $actual_2 );

		$actual_3 = self::$hint_builder->get_hint_type( 'dL KJF:#LITU#WVT F:J' );
		self::assertSame( '', $actual_3 );

		$actual_4 = self::$hint_builder->get_hint_type( 'prerender' );
		self::assertSame( 'prerender', $actual_4 );

		$actual_5 = self::$hint_builder->get_hint_type( 'dns-prefetch' );
		self::assertSame( 'dns-prefetch', $actual_5 );
	}

	public function test_get_url() {
		$actual_1 = self::$hint_builder->get_url( 'https://espn.com', 'preconnect' );
		self::assertSame( 'https://espn.com', $actual_1 );

		$actual_2 = self::$hint_builder->get_url( 'https://espn.com/sports/foozball/', 'dns-prefetch' );
		self::assertSame( 'https://espn.com', $actual_2 );

		$actual_3 = self::$hint_builder->get_url( 'https://espn.com/sports/foozball/', 'preload' );
		self::assertSame( 'https://espn.com/sports/foozball/', $actual_3 );

		$actual_4 = self::$hint_builder->get_url( 'https://e\sp<n.com/spo> rts/fo"ozball/', 'preload' );
		self::assertSame( 'https://espn.com/sports/foozball/', $actual_4 );

		$actual_5 = self::$hint_builder->get_url( 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAB+CAYAAADlYXudAAAA4ElEQVQoU2XIV0eGAQCG4a+9995777', 'preconnect' );
		self::assertSame( '', $actual_5 );

		$actual_6 = self::$hint_builder->get_url( '', 'preconnect' );
		self::assertSame( '', $actual_6 );

		$actual_7 = self::$hint_builder->get_url( '//', 'dns-prefetch' );
		self::assertSame( '', $actual_7 );
	}

	public function test_parse_for_domain_name() {
		$actual_1 = self::$hint_builder->parse_for_domain_name( 'https://espn.com' );
		self::assertSame( 'https://espn.com', $actual_1 );

		$actual_2 = self::$hint_builder->parse_for_domain_name( 'https://espn.com/sports/foozball/' );
		self::assertSame( 'https://espn.com', $actual_2 );

		$actual_3 = self::$hint_builder->parse_for_domain_name( '//asdf.com' );
		self::assertSame( '//asdf.com', $actual_3 );

		$actual_4 = self::$hint_builder->parse_for_domain_name( 'http://test.asdf.com' );
		self::assertSame( 'http://test.asdf.com', $actual_4 );

		$actual_5 = self::$hint_builder->parse_for_domain_name( 'https://www.tester.org' );
		self::assertSame( 'https://www.tester.org', $actual_5 );

		$actual_6 = self::$hint_builder->parse_for_domain_name( 'asdf.com' );
		self::assertSame( '//asdf.com', $actual_6 );
	}

	public function test_get_file_type() {

		$actual_1 = self::$hint_builder->get_file_type( 'https://asdf.com/adsf/sdflkasjfd/script.js?ver=23626262' );
		self::assertSame( '.js', $actual_1 );

		$actual_2 = self::$hint_builder->get_file_type( 'https://asdf.com/adsf/sdflkasjfd/image.jpg' );
		self::assertSame( '.jpg', $actual_2 );

		$actual_3 = self::$hint_builder->get_file_type( 'https://fonts.gstatic.com/s/raleway/v14/1Ptrg8zYS_SKggPNwK4vWqZPANqczVs.woff2' );
		self::assertSame( '.woff2', $actual_3 );

		$actual_4 = self::$hint_builder->get_file_type( 'https://sphacks.local/wp-content/uploads/2021/04/cropped-cropped-fish-32x32.png' );
		self::assertSame( '.png', $actual_4 );


	}

	public function test_set_crossorigin() {
		$xorigin = 'crossorigin';

		$actual_1 = self::$hint_builder->set_crossorigin( array( 'url' => 'https://asdf.com/' ), '' );
		self::assertSame( '', $actual_1 );

		$hint_2 = array( 'url' => 'https://fonts.gstatic.com/s/raleway/v14/1Ptrg8zYS_SKggPNwK4vWqZPANqczVs.woff2' );
		$actual_2 = self::$hint_builder->set_crossorigin( $hint_2, '' );
		self::assertSame( $xorigin, $actual_2 );

		$hint_3 = array( 'url' => 'https://fonts.googleapis.com/s/raleway/v14/1Ptrg8zYS_SKggPNwK4vWqZPANqczVs.woff2' );
		$actual_3 = self::$hint_builder->set_crossorigin( $hint_3, '' );
		self::assertSame( $xorigin, $actual_3 );

		$hint_4 = array( 'url' => 'https://fonts.asdfad.com/s/raleway/v14/1Ptrg8zYS_SKggPNwK4vWqZPANqczVs.woff2' );
		$actual_4 = self::$hint_builder->set_crossorigin( $hint_4, '' );
		self::assertSame( '', $actual_4 );

		$hint_5 = array( 'url' => 'https://fonts.asdfad.com/s/raleway/v14/1Ptrg8zYS_SKggPNwK4vWqZPANqczVs.woff2', $xorigin => $xorigin );
		$actual_5 = self::$hint_builder->set_crossorigin( $hint_5, '' );
		self::assertSame( $xorigin, $actual_5 );

		$hint_6 = array( 'url' => 'https://fonts.asdfad.com/s/rsdfa', $xorigin => $xorigin );
		$actual_6 = self::$hint_builder->set_crossorigin( $hint_6, '' );
		self::assertSame( $xorigin, $actual_6 );
	}

	public function test_set_as_attr() {
		$actual_1 = self::$hint_builder->set_as_attr( '', '.jpg' );
		self::assertSame( 'image', $actual_1 );

		$actual_2 = self::$hint_builder->set_as_attr( '', '.vtt' );
		self::assertSame( 'track', $actual_2 );

		$actual_3 = self::$hint_builder->set_as_attr( '', '.webp' );
		self::assertSame( 'image', $actual_3 );

		$actual_4 = self::$hint_builder->set_as_attr( '', '.css' );
		self::assertSame( 'style', $actual_4 );

		$actual_5 = self::$hint_builder->set_as_attr( '', '.swf' );
		self::assertSame( 'embed', $actual_5 );
	}

	public function test_set_mime_type_attr() {
		$actual_1 = self::$hint_builder->set_mime_type_attr( array( 'type_attr' => '' ), '.jpg' );
		self::assertSame( 'image/jpeg', $actual_1 );

		$actual_2 = self::$hint_builder->set_mime_type_attr( array( 'type_attr' => '' ), '.vtt' );
		self::assertSame( 'text/vtt', $actual_2 );

		$actual_3 = self::$hint_builder->set_mime_type_attr( array( 'type_attr' => '' ), '.webp' );
		self::assertSame( 'image/webp', $actual_3 );

		$actual_4 = self::$hint_builder->set_mime_type_attr( array( 'type_attr' => '' ), '.css' );
		self::assertSame( 'text/css', $actual_4 );

		$actual_5 = self::$hint_builder->set_mime_type_attr( array( 'type_attr' => '' ), '.swf' );
		self::assertSame( 'application/x-shockwave-flash', $actual_5 );

		$actual_6 = self::$hint_builder->set_mime_type_attr( array( 'type_attr' => '' ), '.woff' );
		self::assertSame( 'font/woff', $actual_6 );

		$actual_7 = self::$hint_builder->set_mime_type_attr( array( 'type_attr' => 'text/html' ), '.html' );
		self::assertSame( 'text/html', $actual_7 );
	}




}
