<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DisplayHintsTest extends TestCase {

	public static $display_hints;

	/**
	 * @before Class
	 */
	public function init() {
		$this->setOutputCallback(function() {});
	}

	public function test_begin() {
		$this->setOutputCallback(function() {});
		$GLOBALS['hook_suffix'] = 'toplevel_page_pprh-plugin-settings';
		self::$display_hints = new \PPRH\DisplayHints( false, 0 );
	}

	public function test_on_plugin_page_and_global_hint() {
		$args = array(
			'plural'       => '',
			'singular'     => '',
			'ajax'         => true,
			'screen'       => PPRH_ADMIN_SCREEN,
			'plugin_page' => true
		);

		$wp_list_table = new \PPRH\WP_List_Table( $args );

		$wp_list_table->on_pprh_post_page = false;
		$actual_1 = $wp_list_table->on_plugin_page_and_global_hint( array('post_id' => 'global'), 0 );
		self::assertFalse( $actual_1 );

		$actual_2 = $wp_list_table->on_plugin_page_and_global_hint( array('post_id' => '2138'), 1 );
		self::assertFalse( $actual_2 );

		$actual_3 = $wp_list_table->on_plugin_page_and_global_hint( array('post_id' => '2138'), 2 );
		self::assertFalse( $actual_3 );

		$actual_4 = $wp_list_table->on_plugin_page_and_global_hint( array('url' => 'https://espn.com'), 2 );
		self::assertFalse( $actual_4 );

		$actual_3 = $wp_list_table->on_plugin_page_and_global_hint( array('post_id' => ''), 0 );
		self::assertFalse( $actual_3 );
	}


	public function test_ajax_response() {
		// not doing ajax.
		$display_hints_1 = new \PPRH\DisplayHints( false, 0 );
		$db_result_1 = \PPRH\DAO::create_db_result( '', true, 0, array() );
		$actual_1 = $display_hints_1->ajax_response( $db_result_1 );
		self::assertCount( 7, $actual_1 );

		// doing ajax.
		$display_hints_2 = new \PPRH\DisplayHints( true, 0 );
		$db_result_2 = \PPRH\DAO::create_db_result( '', true, 0, array() );
		$actual_2 = $display_hints_2->ajax_response( $db_result_2 );
		self::assertCount( 7, $actual_2 );
	}

}
