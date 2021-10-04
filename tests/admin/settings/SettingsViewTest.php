<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class SettingsViewTest extends TestCase {

	public static \PPRH\SettingsView $settings_view;

	/**
	 * @before Class
	 */
	public function init() {
		$this->setOutputCallback(function() {});
		self::$settings_view = new \PPRH\SettingsView();
	}

	public function test_prefetch_markup() {
		$actual_1 = self::$settings_view->prefetch_markup();
		self::assertTrue( $actual_1 );
	}


}
