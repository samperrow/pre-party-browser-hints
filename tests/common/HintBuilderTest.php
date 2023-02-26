<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HintBuilderTest extends TestCase {

	public static \PPRH\HintBuilder $hint_builder;

	public function test_start() {
		self::$hint_builder = new \PPRH\HintBuilder();
	}

	public function test_create_pprh_hint() {
		$hint_1 = \PPRH\HintBuilder::create_raw_hint( '', 'preload', 0, '', '', '', '' );
		$actual_1 = self::$hint_builder->create_pprh_hint( $hint_1 );
		self::assertEmpty( $actual_1 );

		$hint_2 = \PPRH\HintBuilder::create_raw_hint( 'https://espn.com/asdf/script.js', 'preload', 0, '', '', '', '', );
		$actual_2 = self::$hint_builder->create_pprh_hint( $hint_2 );
		$expected_2 = \PPRH\HintBuilder::create_raw_hint( 'https://espn.com/asdf/script.js', 'preload', 0, 'script', 'text/javascript', '', '' );
		self::assertSame( $expected_2, $actual_2 );

		$url_1 = 'https://sptrix.local/wp-content/themes/sptrix/images/icons/newspaper.woff?19';

		$test1 = \PPRH\HintBuilder::create_raw_hint( 'https://www.espn.com', 'dns-prefetch' );
		$expected_1 = self::$hint_builder->create_pprh_hint($test1);
		self::assertEquals($expected_1, $test1);

		$test2 = \PPRH\HintBuilder::create_raw_hint( 'ht<tps://www.e>\'sp"n.com', 'dns-prefetch', 1, 'font', 'font/woff2', 'crossorigin', '(max-width:600px)' );
		$actual_hint_2 = self::$hint_builder->create_pprh_hint($test2);
		$test2['url'] = 'https://www.espn.com';
		self::assertEquals($test2, $actual_hint_2);

		$test3 = \PPRH\HintBuilder::create_raw_hint( '//espn.com', 'dns-prefetch' );
		$test_hint3 = self::$hint_builder->create_pprh_hint($test3);
		self::assertEquals($test_hint3, $test3);

		$test_4 = \PPRH\HintBuilder::create_raw_hint( '//espn.com', '' );
		$test_hint_4 = self::$hint_builder->create_pprh_hint($test_4);
		self::assertEmpty($test_hint_4);

		$data1 = \PPRH\HintBuilder::create_raw_hint( '', 'dns-prefetch' );
		$bool1 = self::$hint_builder->create_pprh_hint($data1);
		self::assertEmpty($bool1);

		$test_6 = \PPRH\HintBuilder::create_raw_hint( $url_1, 'preload', 1, 'font', 'font/woff', 'crossorigin', '' );
		$actual_6 = self::$hint_builder->create_pprh_hint( $test_6 );
		self::assertEquals( $test_6, $actual_6);

		$test_7 = \PPRH\HintBuilder::create_raw_hint( 'https://www.espn.com/asdf something/page', 'dns-prefetch' );
		$actual_7 = self::$hint_builder->create_pprh_hint($test_7);
		$expected_7 = \PPRH\HintBuilder::create_raw_hint( 'https://www.espn.com', 'dns-prefetch' );
		self::assertEquals($expected_7, $actual_7);

		$test_8 = \PPRH\HintBuilder::create_raw_hint( "https://www.es\tpn.com/asdf/something/page", 'dns-prefetch' );
		$actual_8 = self::$hint_builder->create_pprh_hint($test_8);
		$expected_8 = \PPRH\HintBuilder::create_raw_hint( 'https://www.espn.com', 'dns-prefetch' );
		self::assertEquals($expected_8, $actual_8);

		$test_9 = array(
			'url'          => 'https://fonts.gstatic.com/s/raleway/v14/1Ptrg8zYS_SKggPNwK4vWqZPANqczVs.woff2',
			'hint_type'    => 'preconnect',
			'auto_created' => 1
		);
		$actual_9 = self::$hint_builder->create_pprh_hint($test_9);
		$expected_9 = \PPRH\HintBuilder::create_raw_hint( 'https://fonts.gstatic.com', 'preconnect', 1, '', '', 'crossorigin' );
		self::assertEquals($expected_9, $actual_9);


		$test_10 = array( 'url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAB+CAYAAADlYXudAAAA4ElEQVQoU2XIV0eGAQCG4a+9995777', 'hint_type' => 'preconnect' );
		$actual_10 = self::$hint_builder->create_pprh_hint( $test_10 );
		self::assertEmpty( $actual_10 );


		$url_10 = 'https://fonts.googleapis.com/css?family=Source+Sans+Pro%3A300%2C400%2C500%2C600%2C700%7CLato%3A300%2C400%2C500%2C700%2C900%7CRaleway%3A300%2C400%2C500%2C700%2C900%7CRaleway&subset=latin%2Clatin-ext';
		$expected_10 = \PPRH\HintBuilder::create_raw_hint( $url_10, 'preload', 1, '', '', 'crossorigin' );
		$actual_10 = self::$hint_builder->create_pprh_hint( $expected_10 );
		self::assertSame( $expected_10, $actual_10 );
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
		$actual_1 = self::$hint_builder::get_file_type( 'https://asdf.com/adsf/sdflkasjfd/script.js?ver=23626262' );
		self::assertSame( '.js', $actual_1 );

		$actual_2 = self::$hint_builder::get_file_type( 'https://asdf.com/adsf/sdflkasjfd/image.jpg' );
		self::assertSame( '.jpg', $actual_2 );

		$actual_3 = self::$hint_builder::get_file_type( 'https://fonts.gstatic.com/s/raleway/v14/1Ptrg8zYS_SKggPNwK4vWqZPANqczVs.woff2' );
		self::assertSame( '.woff2', $actual_3 );

		$actual_4 = self::$hint_builder::get_file_type( 'https://sptrix.local/wp-content/uploads/2021/04/cropped-cropped-fish-32x32.png' );
		self::assertSame( '.png', $actual_4 );

		$actual_5 = self::$hint_builder::get_file_type( 'https://fonts.googleapis.com/css?family=Lato:300,300i,700,700i,900,400%7CPacifico:400&subset=latin,latin-ext&display=auto' );
		self::assertSame( '', $actual_5 );
	}


}
