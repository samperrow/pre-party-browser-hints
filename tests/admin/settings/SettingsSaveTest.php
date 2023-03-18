<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SettingsSaveTest extends TestCase {

	public static \PPRH\Settings\SettingsSave $settings_save;

	/**
	 * @before Class
	 */
	public function init() {
		self::$settings_save = new \PPRH\Settings\SettingsSave();
	}

	public function test_save_general_settings() {
		$post_1 = array('pprh_disable_wp_hints' => 'true', 'pprh_html_head' => 'true' );
		$actual_1 = self::$settings_save->save_general_settings( $post_1 );
		self::assertSame( array( 'true', 'true' ), $actual_1 );

		$post_2 = array();
		$actual_2 = self::$settings_save->save_general_settings($post_2);
		self::assertSame(array('false', 'false' ), $actual_2);
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

}
