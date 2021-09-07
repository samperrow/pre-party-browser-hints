<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class SettingsViewTest extends TestCase {

	public static $settings_view;

	public function test_start() {
		self::$settings_view = new \PPRH\SettingsView();
	}

}
