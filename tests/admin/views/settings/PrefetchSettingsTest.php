<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class PrefetchSettingsTest extends TestCase {

	public $prefetch_settings;

	/**
	 * @before
	 */
	public function test_start() {
		$this->prefetch_settings = new \PPRH\PrefetchSettings();
	}


//	public function test_get_each_keyword() {//
//		$prefetch_settings = new \PPRH\PrefetchSettings();
//		$keywords = array( 'testeroo/asdf', 'blah', 'wp-login.php', 'cart', '' );
//
//		$actual = $prefetch_settings->get_each_keyword( $keywords );
//		$expected = "testeroo/asdf\nblah\nwp-login.php\ncart\n";
//		self::assertEquals( $expected, $actual );
//	}

	public function test_turn_textarea_to_array() {
		$text_1 = '/asdf<script>/asdf/';
		$actual_1 = $this->prefetch_settings->turn_textarea_to_array( $text_1 );
		$expected_1 = array( '/asdfscript/asdf/' );
		self::assertEquals( $expected_1, $actual_1);

		$text_2 = "/as'dfscript/asdf\r\n/asdfasdf";
		$actual_2 = $this->prefetch_settings->turn_textarea_to_array( $text_2 );
		$expected_2 = array( '/asdfscript/asdf', '/asdfasdf' );
		self::assertEquals( $expected_2, $actual_2);

		$text_3 = '';
		$actual_3 = $this->prefetch_settings->turn_textarea_to_array( $text_3 );
		$expected_3 = array( '' );
		self::assertEquals( $expected_3, $actual_3);

		$text_4 = "\r\n";
		$actual_4 = $this->prefetch_settings->turn_textarea_to_array( $text_4 );
		$expected_4 = array( '', '' );
		self::assertEquals( $expected_4, $actual_4);

		$text_5 = "/asdf\r\n/test";
		$actual_5 = $this->prefetch_settings->turn_textarea_to_array( $text_5 );
		$expected_5 = array( '/asdf', '/test' );
		self::assertEquals( $expected_5, $actual_5);
	}

	public function test_save_options() {}

	public function test_show_settings() {}

	public function test_set_values() {}

	public function test_markup() {}

}
