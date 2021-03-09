<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DisplayHintsTest extends TestCase {

	public function test_on_post_page_and_global_hint():void {
		if ( ! PPRH_IS_ADMIN ) return;

		$all_hints = \PPRH\Utils::get_all_hints();

		$display_hints_1 = new \PPRH\DisplayHints(true, $all_hints);
		$display_hints_2 = new \PPRH\DisplayHints(false, $all_hints);

		$test_1 = array('post_id' => 'global');
		$actual_1 = $display_hints_1->on_post_page_and_global_hint( $test_1 );

		$test_2 = array('post_id' => '2138');
		$actual_2 = $display_hints_1->on_post_page_and_global_hint( $test_2 );

		$test_3 = array('post_id' => 'global');
		$actual_3 = $display_hints_2->on_post_page_and_global_hint( $test_3 );

		$test_4 = array('post_id' => '2138');
		$actual_4 = $display_hints_2->on_post_page_and_global_hint( $test_4 );

		$test_5 = array('url' => 'http://espn.com');
		$actual_5 = $display_hints_2->on_post_page_and_global_hint( $test_5 );

		$this->assertEquals( false, $actual_1 );
		$this->assertEquals( false, $actual_2 );

		$this->assertEquals( false, $actual_4 );
		$this->assertEquals( false, $actual_5 );

		if ( PPRH_PRO_PLUGIN_ACTIVE )  {
			$this->assertEquals( true, $actual_3 );
		} else {
			$this->assertEquals( false, $actual_3 );
		}
	}

}
