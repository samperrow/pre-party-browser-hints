<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class DashboardTest extends TestCase {

	public $dashboard;

	/**
	 * @before
	 */
	public function test_start() {
		$this->dashboard = new \PPRH\Dashboard();
	}

	public function test_check_to_upgrade() {
		$actual_1 = $this->dashboard->check_to_upgrade( '1.7.7', '1.7.6.3' );
		self::assertTrue( $actual_1 );

		$actual_2 = $this->dashboard->check_to_upgrade( '1.7.7', '1.7.7' );
		self::assertFalse( $actual_2 );

		$actual_3 = $this->dashboard->check_to_upgrade( '1.7.7', '1.7.7.1' );
		self::assertTrue( $actual_3 );
	}


}
