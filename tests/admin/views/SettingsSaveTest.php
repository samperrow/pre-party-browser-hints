<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class SettingsSaveTest extends TestCase {

	public static $settings_save;

	/**
	 * @before Class
	 */
	public function init() {
		self::$settings_save = new \PPRH\SettingsSave();
	}

	public function test_save_general_settings() {
		$post_1 = array('pprh_disable_wp_hints' => 'true', 'pprh_html_head' => 'true', 'pprh_debug_enabled' => 'true' );
		$actual_1 = self::$settings_save->save_general_settings( $post_1 );
		self::assertSame( array( 'true', 'true', 'true' ), $actual_1 );

		$post_2 = array();
		$actual_2 = self::$settings_save->save_general_settings($post_2);
		self::assertSame(array('false', 'false', 'false' ), $actual_2);
	}

	public function test_save_preconnect_settings() {
		$post_1 = array();
		$actual_1 = self::$settings_save->save_preconnect_settings( $post_1 );
		self::assertSame( array( 'false', 'false' ), $actual_1 );

		$post_2 = array( 'pprh_preconnect_autoload' => 'true', 'pprh_preconnect_allow_unauth' => 'true', 'pprh_preconnect_set' => 'Reset' );
		$actual_2 = self::$settings_save->save_preconnect_settings( $post_2 );
		self::assertSame( array( 'true', 'true', true ), $actual_2 );
	}

	public function test_save_prefetch_settings() {
		$post_1 = array();
		$actual_1 = self::$settings_save->save_prefetch_settings( $post_1 );
		self::assertSame( array( 'false', 'false' ), $actual_1 );

		$post_2 = array(
			'pprh_prefetch_enabled'                 => 'true',
			'pprh_prefetch_disableForLoggedInUsers' => 'true',
			'pprh_prefetch_delay'                   => '1',
			'pprh_prefetch_ignoreKeywords'          => 'asdf',
			'pprh_prefetch_maxRPS'                  => '5',
			'pprh_prefetch_hoverDelay'              => '3',
			'pprh_prefetch_max_prefetches'          => '10'
		);
		$actual_2 = self::$settings_save->save_prefetch_settings( $post_2 );
		self::assertSame( array( 'true', 'true', '1', array( 'asdf' ), '5', '3', '10' ), $actual_2 );
	}

	public function test_turn_textarea_to_array() {
		$text_1 = '/asdf<script>/asdf/';
		$actual_1 = self::$settings_save->turn_textarea_to_array( $text_1 );
		$expected_1 = array( '/asdfscript/asdf/' );
		self::assertEquals( $expected_1, $actual_1);

		$text_2 = "/as'dfscript/asdf\r\n/asdfasdf";
		$actual_2 = self::$settings_save->turn_textarea_to_array( $text_2 );
		$expected_2 = array( '/asdfscript/asdf', '/asdfasdf' );
		self::assertEquals( $expected_2, $actual_2);

		$text_3 = '';
		$actual_3 = self::$settings_save->turn_textarea_to_array( $text_3 );
		$expected_3 = array( '' );
		self::assertEquals( $expected_3, $actual_3);

		$text_4 = "\r\n";
		$actual_4 = self::$settings_save->turn_textarea_to_array( $text_4 );
		$expected_4 = array( '', '' );
		self::assertEquals( $expected_4, $actual_4);

		$text_5 = "/asdf\r\n/test";
		$actual_5 = self::$settings_save->turn_textarea_to_array( $text_5 );
		$expected_5 = array( '/asdf', '/test' );
		self::assertEquals( $expected_5, $actual_5);
	}

}
