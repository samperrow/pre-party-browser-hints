<?php
declare(strict_types=1);
namespace PPRH\PRO;
use PHPUnit\Framework\TestCase;

class SettingsViewTest extends TestCase {

	public static $settings_view;

	public function test_start() {
		self::$settings_view = new \PPRH\PRO\SettingsView( true );
	}

}
