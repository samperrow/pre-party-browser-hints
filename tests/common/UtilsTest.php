<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
//use PPRH\PPRH_PRO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class UtilsTest extends TestCase {




	public function test_strip_non_alphanums():void {
		$str1 = '!f_a#FED__=26 5b-2tb(&YT^>"28352';
		$test1 = \PPRH\Utils::strip_non_alphanums($str1);
		self::assertEquals( 'faFED265b2tbYT28352', $test1 );

		$str2 = 'sfjsdlfj4w9tu3wofjw93u3';
		$test2 = \PPRH\Utils::strip_non_alphanums($str2);
		self::assertEquals( $str2, $test2 );
	}

	public function test_strip_non_numbers():void {
		$str1 = '!f_a#FED__=26 5b-2tb(&YT^>"28352';
		$test1 = \PPRH\Utils::strip_non_numbers($str1);
		self::assertEquals( '265228352', $test1 );
	}

	public function test_clean_hint_type():void {
		$str1 = 'DNS-prefetch';
		$test1 = \PPRH\Utils::clean_hint_type($str1);
		self::assertEquals( $str1, $test1 );

		$str2 = 'pre*(con@"><nect';
		$test2 = \PPRH\Utils::clean_hint_type($str2);
		self::assertEquals( 'preconnect', $test2 );
	}

	public function test_clean_url():void {
		$str1 = 'https://www.espn.com';
		$test1 = \PPRH\Utils::clean_url($str1);
		self::assertEquals( $str1, $test1 );

		$str2 = 'https"://<script\>test.com<script>';
		$test2 = \PPRH\Utils::clean_url($str2);
		self::assertEquals( 'https://scripttest.comscript', $test2 );
	}

	public function test_clean_url_path():void {
		$str1 = 'https://www.espn.com';
		$test1 = \PPRH\Utils::clean_url_path($str1);
		self::assertEquals( $str1, $test1 );

		$test2 = \PPRH\Utils::clean_url_path('/?testdsdf/blah&');
		self::assertEquals( 'testdsdf/blah', $test2 );
	}

	public function test_clean_hint_attr():void {
		$str1 = 'font/woff2';
		$test1 = \PPRH\Utils::clean_hint_attr($str1);
		self::assertEquals( $str1, $test1 );

		$test2 = \PPRH\Utils::clean_hint_attr('f<\/asdlf  kj43*#t935u23" asdflkj3');
		self::assertEquals( 'f/asdlfkj43t935u23asdflkj3', $test2 );
	}

//	public function test_create_db_result():void {
//
//
//		$test1 = PPRH\Utils::create_db_result($str1);
//
//		self::assertEquals( '', $test2 );
//	}

//	public function test_get_wpdb_result():void {
//		$action1 = 'created';
//		$hint_id = null;
//		$wpdb1 = (object) array(
//			'result'     => true,
//			'last_error' => '',
//		);
//		$result1 = array(
//			'msg'        => "Resource hint $action1 successfully.",
//			'status'     => 'success',
//			'success'    => true,
//			'last_error' => '',
//			'hint_id'  => $hint_id,
//		);
//
//		$action2 = 'deleted';
//		$wpdb2 = (object) array(
//			'result'     => false,
//			'last_error' => 'Failed!',
//			'hint_id'  => $hint_id,
//		);
//		$result2 = array(
//			'msg'        => "Failed to $action2 hint.",
//			'status'     => 'error',
//			'success'    => false,
//			'last_error' => 'Failed!',
//			'hint_id'  => $hint_id,
//		);
//
//		$test1 = PPRH\Utils::get_wpdb_result($wpdb1, $action1);
//		$test2 = PPRH\Utils::get_wpdb_result($wpdb2, $action2 );
//
//		self::assertEquals( $result1, $test1 );
//		self::assertEquals( $result2, $test2 );
//	}

	public function test_get_option_status():void {
		$option_name = 'pprh_test_option';
		\add_option( $option_name, 'true' );
		$actual_1 = \PPRH\Utils::get_option_status($option_name, 'true' );
		self::assertEquals( 'selected=selected', $actual_1 );

		\update_option( $option_name, 'false' );
		$test_2 = \PPRH\Utils::get_option_status( $option_name, 'true' );
		self::assertEquals( '', $test_2 );

		delete_option( $option_name );
	}




	public function test_esc_get_option():void {
		$test_option_name1 = 'pprh_test_option1';
		\add_option( $test_option_name1, 'https://<test.com>/asdfasdf', '', 'true' );
		$actual1 = \PPRH\Utils::esc_get_option( $test_option_name1 );
		self::assertEquals( 'https://&lt;test.com&gt;/asdfasdf', $actual1 );
		\delete_option( $test_option_name1 );

		$test_option_name2 = 'pprh_test_option2';
		\add_option( $test_option_name2, 'https://test.com/asdfasdf', '', 'true' );
		$actual2 = \PPRH\Utils::esc_get_option( $test_option_name2 );
		self::assertEquals( 'https://test.com/asdfasdf', $actual2 );
		\delete_option( $test_option_name2 );
	}

	public function test_on_pprh_admin_page() {

		$actual_1 = \PPRH\Utils::on_pprh_admin_page(true, PPRH_MENU_SLUG);
		self::assertEquals( true, $actual_1 );

		$actual_2 = \PPRH\Utils::on_pprh_admin_page(true, 'post.php');
		self::assertEquals( false, $actual_2 );

		$actual_3 = \PPRH\Utils::on_pprh_admin_page(true, '');
		self::assertEquals( false, $actual_3 );

		$_GET['page'] = PPRH_MENU_SLUG;
		$actual_4 = \PPRH\Utils::on_pprh_admin_page(false);
		self::assertEquals( true, $actual_4 );

		$_GET['page'] = 'post.php';
		$actual_5 = \PPRH\Utils::on_pprh_admin_page(false);
		self::assertEquals( false, $actual_5 );

		$_GET['page'] = '';
		$actual_6 = \PPRH\Utils::on_pprh_admin_page(false);
		self::assertEquals( false, $actual_6 );

		$actual_7 = \PPRH\Utils::on_pprh_admin_page(false);
		self::assertEquals( false, $actual_7 );



		$_GET['page'] = null;
		$_GET['post'] = '2128';
		$test2 = \PPRH\Utils::on_pprh_admin_page( false, null );
		self::assertEquals(false, $test2);

		$test2 = \PPRH\Utils::on_pprh_admin_page( true, null );
		self::assertEquals(false, $test2);
	}


}
