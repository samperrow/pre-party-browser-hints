<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DisplayHintsTest extends TestCase {

	public function test_on_post_page_and_global_hint():void {
		if ( ! WP_ADMIN ) return;

		$args = array(
			'plural'   => '',
			'singular' => '',
			'ajax'     => true,
			'screen'   => 'toplevel_page_pprh-plugin-settings',
		);

		$wp_list_table = new \PPRH\WP_List_Table( $args );

		$test_1 = array('post_id' => 'global');
		$actual_1 = $wp_list_table->on_post_page_and_global_hint( $test_1 );

		$test_2 = array('post_id' => '2138');
		$actual_2 = $wp_list_table->on_post_page_and_global_hint( $test_2 );



		$test_3 = array('post_id' => '2138');
		$actual_3 = $wp_list_table->on_post_page_and_global_hint( $test_3 );

		$test_4 = array('url' => 'http://espn.com');
		$actual_4 = $wp_list_table->on_post_page_and_global_hint( $test_4 );

		self::assertEquals( false, $actual_1 );
		self::assertEquals( false, $actual_2 );

		self::assertEquals( false, $actual_3 );
		self::assertEquals( false, $actual_4 );

		if ( PPRH_PRO_PLUGIN_ACTIVE )  {
			$test_5 = array('post_id' => 'global');
			$wp_list_table->on_pprh_admin = false;
			$actual_5 = $wp_list_table->on_post_page_and_global_hint( $test_5 );

			$wp_list_table->on_pprh_admin = true;
			$actual_6 = $wp_list_table->on_post_page_and_global_hint( $test_5 );

			self::assertEquals( true, $actual_5 );
			self::assertEquals( false, $actual_6 );
		} else {
			$test_3 = array('post_id' => '');
			$wp_list_table->on_pprh_admin = false;
			$actual_3 = $wp_list_table->on_post_page_and_global_hint( $test_3 );
			self::assertEquals( false, $actual_3 );
		}
	}

//	public function test_dh_on_posts_page_and_global() {
//		$display_hints = new \PPRH\DisplayHints();
//
//		$actual1 = $display_hints->on_post_page_and_global_hint( array( 'post_id' => '2128' ) );
//		$actual2 = $display_hints->on_post_page_and_global_hint( array( 'post_id' => 'global' ) );
//		self::assertEquals( false, $actual1 );
//		self::assertEquals( false, $actual2 );
//
//		$display_hints2 = new \PPRH\DisplayHints();
////		$actual3 = $display_hints2->on_post_page_and_global_hint( array( 'post_id' => 'global' ) );
//
//		$actual4 = $display_hints2->on_post_page_and_global_hint( array( 'post_id' => '2128' ) );
//
////		self::assertEquals( true, $actual3 );
//		self::assertEquals( false, $actual4 );
//	}

}
