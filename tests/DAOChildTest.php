<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAOChildTest extends TestCase {

	public function test_get_active_post_count():void {

	}

	public function test_get_post_id_from_url(): void {
		$dao_child = new \PPRH\PRO\DAOChild();

		$test_url_1 = 'send-blind-cc-email-addresses-from-workflows';
		$actual_1 = $dao_child->get_post_id_from_url( $test_url_1 );

		$test_url_2 = '/sp-calendar-pro/calendar-filters/is-time-conflict/';
		$actual_2 = $dao_child->get_post_id_from_url( $test_url_2 );

		$test_url_3 = '/no-such-post-exists';
		$actual_3 = $dao_child->get_post_id_from_url( $test_url_3 );

		$test_url_4 = '/';
		$actual_4 = $dao_child->get_post_id_from_url( $test_url_4 );

		$this->assertEquals('2192', $actual_1);
		$this->assertEquals('2142', $actual_2);
		$this->assertEquals( 'global', $actual_3);
		$this->assertEquals( 'global', $actual_4);
	}

	public function test_reset_preconnect_post_reset() {

	}

	public function test_get_post_link() {

	}
}
