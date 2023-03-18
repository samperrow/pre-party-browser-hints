<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SettingsViewTest extends TestCase {

	public static \PPRH\Settings\SettingsView $settings_view;

	/**
	 * @before Class
	 */
	public function init() {
		$this->setOutputCallback(function() {});

		$show_posts_on_front = ( 'posts' === \get_option( 'show_on_front', '' ) );
		self::$settings_view = new \PPRH\Settings\SettingsView( $show_posts_on_front );
	}

	public function test_prefetch_markup() {
		$actual_1 = self::$settings_view->prefetch_markup();
		self::assertTrue( $actual_1 );
	}


}
