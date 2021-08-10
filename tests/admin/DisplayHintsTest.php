<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DisplayHintsTest extends TestCase {

	public function test_on_post_page_and_global_hint() {
		$args = array(
			'plural'       => '',
			'singular'     => '',
			'ajax'         => true,
			'screen'       => PPRH_ADMIN_SCREEN,
			'on_pprh_page' => true
		);

		$wp_list_table = new \PPRH\WP_List_Table( $args );

		$wp_list_table->on_pprh_post_page = false;
		$actual_1 = $wp_list_table->on_post_page_and_global_hint( array('post_id' => 'global'), 0 );
		self::assertFalse( $actual_1 );

		$actual_2 = $wp_list_table->on_post_page_and_global_hint( array('post_id' => '2138'), 1 );
		self::assertFalse( $actual_2 );

		$actual_3 = $wp_list_table->on_post_page_and_global_hint( array('post_id' => '2138'), 2 );
		self::assertFalse( $actual_3 );

		$actual_4 = $wp_list_table->on_post_page_and_global_hint( array('url' => 'https://espn.com'), 2 );
		self::assertFalse( $actual_4 );

		if ( PPRH_PRO_PLUGIN_ACTIVE ) {
			$test_5 = array('post_id' => 'global');

			$actual_5 = $wp_list_table->on_post_page_and_global_hint( $test_5, 2 );
			self::assertTrue( $actual_5 );

			$actual_6 = $wp_list_table->on_post_page_and_global_hint( $test_5, 1 );
			self::assertFalse( $actual_6 );
		} else {
			$actual_3 = $wp_list_table->on_post_page_and_global_hint( array('post_id' => ''), 0 );
			self::assertFalse( $actual_3 );
		}
	}

}
