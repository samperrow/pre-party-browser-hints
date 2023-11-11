<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class DashboardTest extends TestCase {

	public static $dashboard;

	/**
	 * @before Class
	 */
	public function init() {
		$this->setOutputCallback(function() {});
		self::$dashboard = new \PPRH\Dashboard();
	}

//	public function test_check_to_upgrade() {
//		$actual_1 = self::$dashboard->plugin_upgrade_notice( '1.7.7', '1.7.6.3' );
//		self::assertTrue( $actual_1 );
//
//		$actual_2 = self::$dashboard->plugin_upgrade_notice( '1.7.7', '1.7.7' );
//		self::assertFalse( $actual_2 );
//
//		$actual_3 = self::$dashboard->plugin_upgrade_notice( '1.7.7', '1.7.7.1' );
//		self::assertTrue( $actual_3 );
//	}

	public function test_show_plugin_dashboard() {
		$_SERVER['REQUEST_URI'] = 'https://sptrix.local/wp-admin/admin.php?page=pprh-plugin-settings';

		$actual_1 = \has_action( 'pprh_notice' );
		self::assertTrue( $actual_1 );

		$actual_2 = self::$dashboard->show_plugin_dashboard( 0 );
		self::assertTrue( $actual_2 );

		unset( $_SERVER['REQUEST_URI'] );
	}


}
