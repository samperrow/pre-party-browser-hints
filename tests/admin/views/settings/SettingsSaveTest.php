<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class SettingsSaveTest extends TestCase {

	public static $settings_save;

	public function test_start() {
		self::$settings_save = new \PPRH\SettingsSave();
	}

}
