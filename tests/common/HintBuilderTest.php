<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HintBuilderTest extends TestCase {

	public static $hint_builder;

	/**
	 * @before Class
	 */
	public function init() {
		self::$hint_builder = new \PPRH\HintBuilder();
	}

	public function test_create_pprh_hint() {
		$hint_1 = Utils::create_raw_hint( '', 'preload', 0, '', '', '', '' );
		$actual_1 = self::$hint_builder->create_pprh_hint( $hint_1 );
		self::assertEmpty( $actual_1 );

		$hint_2 = Utils::create_raw_hint( 'https://espn.com/asdf/script.js', 'preload', 0, '', '', '', '' );
		$actual_2 = self::$hint_builder->create_pprh_hint( $hint_2 );
		$expected_2 = Utils::create_raw_hint( 'https://espn.com/asdf/script.js', 'preload', 0, 'script', 'text/javascript', '', '' );
		self::assertSame( $expected_2, $actual_2 );

		$url_1 = 'https://sphacks.local/wp-content/themes/sphacks/images/icons/newspaper.woff?19';

		$test1 = Utils::create_raw_hint( 'https://www.espn.com', 'dns-prefetch' );
		$expected_1 = self::$hint_builder->create_pprh_hint($test1);
		self::assertEquals($expected_1, $test1);

		$test2 = Utils::create_raw_hint( 'ht<tps://www.e>\'sp"n.com', 'dns-prefetch', 1, 'font', 'font/woff2', 'crossorigin', '(max-width:600px)' );
		$actual_hint_2 = self::$hint_builder->create_pprh_hint($test2);
		$test2['url'] = 'https://www.espn.com';
		self::assertEquals($test2, $actual_hint_2);

		$test3 = Utils::create_raw_hint( '//espn.com', 'dns-prefetch' );
		$test_hint3 = self::$hint_builder->create_pprh_hint($test3);
		self::assertEquals($test_hint3, $test3);

		$test_4 = Utils::create_raw_hint( '//espn.com', '' );
		$test_hint_4 = self::$hint_builder->create_pprh_hint($test_4);
		self::assertEmpty($test_hint_4);

		$data1 = Utils::create_raw_hint( '', 'dns-prefetch' );
		$bool1 = self::$hint_builder->create_pprh_hint($data1);
		self::assertEmpty($bool1);

		$test_6 = Utils::create_raw_hint( $url_1, 'preload', 1, 'font', 'font/woff', 'crossorigin', '' );
		$actual_6 = self::$hint_builder->create_pprh_hint( $test_6 );
		self::assertEquals( $test_6, $actual_6);

		$test_7 = Utils::create_raw_hint( 'https://www.espn.com/asdf something/page', 'dns-prefetch' );
		$actual_7 = self::$hint_builder->create_pprh_hint($test_7);
		$expected_7 = Utils::create_raw_hint( 'https://www.espn.com', 'dns-prefetch' );
		self::assertEquals($expected_7, $actual_7);

		$test_8 = Utils::create_raw_hint( "https://www.es\tpn.com/asdf/something/page", 'dns-prefetch' );
		$actual_8 = self::$hint_builder->create_pprh_hint($test_8);
		$expected_8 = Utils::create_raw_hint( 'https://www.espn.com', 'dns-prefetch' );
		self::assertEquals($expected_8, $actual_8);

		$test_9 = Utils::create_raw_hint( 'https://fonts.gstatic.com/s/raleway/v14/1Ptrg8zYS_SKggPNwK4vWqZPANqczVs.woff2', 'preconnect', 1, '', '', '' );
		$actual_9 = self::$hint_builder->create_pprh_hint($test_9);
		$expected_9 = Utils::create_raw_hint( 'https://fonts.gstatic.com', 'preconnect', 1, '', '', 'crossorigin' );
		self::assertEquals($expected_9, $actual_9);

		$test_10 = Utils::create_raw_hint( 'https://sphacks.local/asdfasdf/asdf/image.jpg', 'preload', 1, '', '', '' );
		$actual_10 = self::$hint_builder->create_pprh_hint($test_10);
		$expected_10 = Utils::create_raw_hint( 'https://sphacks.local/asdfasdf/asdf/image.jpg', 'preload', 1, 'image', 'image/jpeg', '' );
		self::assertEquals($expected_10, $actual_10);

		$test_11 = Utils::create_raw_hint( 'https://sphacks.local/asdfasdf/asdf/doc.html', 'preload', 1, '', '', '' );
		$actual_11 = self::$hint_builder->create_pprh_hint($test_11);
		$expected_11 = Utils::create_raw_hint( 'https://sphacks.local/asdfasdf/asdf/doc.html', 'preload', 1, 'document', 'text/html', '' );
		self::assertEquals($expected_11, $actual_11);
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

}
