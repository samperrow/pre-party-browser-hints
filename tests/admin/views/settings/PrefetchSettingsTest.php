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

	public function test_turn_textarea_to_csv() {
		$text_1 = '/asdf<script>/asdf/';
		$actual_1 = $this->prefetch_settings->turn_textarea_to_csv( $text_1 );
		$expected_1 = array( '/asdfscript/asdf/' );
		self::assertEquals( $expected_1, $actual_1);
	}

	public function test_save_options() {}

	public function test_show_settings() {}

	public function test_set_values() {}

	public function test_markup() {}

}
