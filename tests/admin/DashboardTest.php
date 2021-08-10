<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class DashboardTest extends TestCase {

	public static $dashboard;

	/**
	 * @before Class
	 */
	public function test_start() {
		self::$dashboard = new \PPRH\Dashboard();
	}

	public function test_check_to_upgrade() {
		$actual_1 = self::$dashboard->plugin_upgrade_notice( '1.7.7', '1.7.6.3' );
		self::assertTrue( $actual_1 );

		$actual_2 = self::$dashboard->plugin_upgrade_notice( '1.7.7', '1.7.7' );
		self::assertFalse( $actual_2 );

		$actual_3 = self::$dashboard->plugin_upgrade_notice( '1.7.7', '1.7.7.1' );
		self::assertTrue( $actual_3 );
	}


}
