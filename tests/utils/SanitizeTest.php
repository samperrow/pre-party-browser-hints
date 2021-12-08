<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PPRH\Utils\Utils;
use PPRH\Utils\Sanitize;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SanitizeTest extends TestCase {

	public static $sanitize;

	/**
	 * @before Class
	 */
	public function init() {
		self::$sanitize = new Sanitize();
	}

	public function test_strip_non_alphanums() {
		$str1 = '!f_a#FED__=26 5b-2tb(&YT^>"28352';
		$test1 = Sanitize::strip_non_alphanums($str1);
		self::assertEquals( 'faFED265b2tbYT28352', $test1 );

		$str2 = 'sfjsdlfj4w9tu3wofjw93u3';
		$test2 = Sanitize::strip_non_alphanums($str2);
		self::assertEquals( $str2, $test2 );
	}

	public function test_strip_non_numbers() {
		$str1 = '!f_a#FED__=26 5b-2tb(&YT^>"28352';
		$test1 = Sanitize::strip_non_numbers( $str1, true );
		self::assertEquals( '265228352', $test1 );
	}

	public function test_clean_hint_type() {
		$str1 = 'DNS-prefetch';
		$test1 = Sanitize::clean_hint_type($str1);
		self::assertEquals( $str1, $test1 );

		$str2 = 'pre*(con@"><nect';
		$test2 = Sanitize::clean_hint_type($str2);
		self::assertEquals( 'preconnect', $test2 );
	}

	public function test_clean_url() {
		$str1 = 'https://www.espn.com';
		$test1 = Sanitize::clean_url($str1);
		self::assertEquals( $str1, $test1 );

		$str2 = 'https"://<script\>test.com<script>';
		$test2 = Sanitize::clean_url($str2);
		self::assertEquals( 'https://scripttest.comscript', $test2 );

		$str_3 = "https://asdf.com/asf /'<>^\"";
		$test_3 = Sanitize::clean_url($str_3);
		self::assertEquals( 'https://asdf.com/asf/', $test_3 );
	}

	public function test_strip_bad_chars() {
		$str1 = 'https://www.espn.com';
		$test1 = Sanitize::strip_bad_chars($str1);
		self::assertEquals( $str1, $test1 );

		$str2 = 'https"://<script\>test.com<script>';
		$test2 = Sanitize::strip_bad_chars($str2);
		self::assertEquals( 'https://scripttest.comscript', $test2 );

		$str_3 = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15';
		$test_3 = Sanitize::strip_bad_chars($str_3);
		self::assertEquals( $str_3, $test_3 );
	}


	public function test_clean_url_path() {
		$str1 = 'https://www.espn.com';
		$test1 = Sanitize::clean_url_path($str1);
		self::assertEquals( $str1, $test1 );

		$test2 = Sanitize::clean_url_path('/?testdsdf/blah&');
		self::assertEquals( 'testdsdf/blah', $test2 );
	}

	public function test_clean_hint_attr() {
		$str1 = 'font/woff2';
		$test1 = Sanitize::clean_hint_attr($str1);
		self::assertEquals( $str1, $test1 );

		$test2 = Sanitize::clean_hint_attr('f<\/asdlf  kj43*#t935u23" asdflkj3');
		self::assertEquals( 'f/asdlfkj43t935u23asdflkj3', $test2 );
	}


}
