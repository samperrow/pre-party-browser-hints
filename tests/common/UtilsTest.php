<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
//use PPRH\PPRH_PRO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class UtilsTest extends TestCase {




	public function test_strip_non_alphanums() {
		$str1 = '!f_a#FED__=26 5b-2tb(&YT^>"28352';
		$test1 = \PPRH\Utils::strip_non_alphanums($str1);
		self::assertEquals( 'faFED265b2tbYT28352', $test1 );

		$str2 = 'sfjsdlfj4w9tu3wofjw93u3';
		$test2 = \PPRH\Utils::strip_non_alphanums($str2);
		self::assertEquals( $str2, $test2 );
	}

	public function test_strip_non_numbers() {
		$str1 = '!f_a#FED__=26 5b-2tb(&YT^>"28352';
		$test1 = \PPRH\Utils::strip_non_numbers($str1);
		self::assertEquals( '265228352', $test1 );
	}

	public function test_clean_hint_type() {
		$str1 = 'DNS-prefetch';
		$test1 = \PPRH\Utils::clean_hint_type($str1);
		self::assertEquals( $str1, $test1 );

		$str2 = 'pre*(con@"><nect';
		$test2 = \PPRH\Utils::clean_hint_type($str2);
		self::assertEquals( 'preconnect', $test2 );
	}

	public function test_clean_url() {
		$str1 = 'https://www.espn.com';
		$test1 = \PPRH\Utils::clean_url($str1);
		self::assertEquals( $str1, $test1 );

		$str2 = 'https"://<script\>test.com<script>';
		$test2 = \PPRH\Utils::clean_url($str2);
		self::assertEquals( 'https://scripttest.comscript', $test2 );

		$str_3 = "https://asdf.com/asf /'<>^\"";
		$test_3 = \PPRH\Utils::clean_url($str_3);
		self::assertEquals( 'https://asdf.com/asf/', $test_3 );
	}

	public function test_clean_url_path() {
		$str1 = 'https://www.espn.com';
		$test1 = \PPRH\Utils::clean_url_path($str1);
		self::assertEquals( $str1, $test1 );

		$test2 = \PPRH\Utils::clean_url_path('/?testdsdf/blah&');
		self::assertEquals( 'testdsdf/blah', $test2 );
	}

	public function test_clean_hint_attr() {
		$str1 = 'font/woff2';
		$test1 = \PPRH\Utils::clean_hint_attr($str1);
		self::assertEquals( $str1, $test1 );

		$test2 = \PPRH\Utils::clean_hint_attr('f<\/asdlf  kj43*#t935u23" asdflkj3');
		self::assertEquals( 'f/asdlfkj43t935u23asdflkj3', $test2 );
	}

	public function test_isArrayAndNotEmpty() {
		$test_1 = array();
		$actual_1 = \PPRH\Utils::isArrayAndNotEmpty($test_1);
		self::assertFalse( $actual_1 );

		$test_2 = '';
		$actual_2 = \PPRH\Utils::isArrayAndNotEmpty($test_2);
		self::assertFalse( $actual_2 );

		$test_3 = null;
		$actual_3 = \PPRH\Utils::isArrayAndNotEmpty($test_3);
		self::assertFalse( $actual_3 );

		$test_4 = (object) array( 'asdf' );
		$actual_4 = \PPRH\Utils::isArrayAndNotEmpty($test_4);
		self::assertFalse( $actual_4 );

		$test_5 = 21;
		$actual_5 = \PPRH\Utils::isArrayAndNotEmpty($test_5);
		self::assertFalse( $actual_5 );

		$test_6 = array( 'asdf' );
		$actual_6 = \PPRH\Utils::isArrayAndNotEmpty($test_6);
		self::assertTrue( $actual_6 );
	}

//	public function test_create_db_result() {
//
//
//		$test1 = PPRH\Utils::create_db_result($str1);
//
//		self::assertEquals( '', $test2 );
//	}

//	public function test_get_wpdb_result() {
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

	public function test_does_option_match() {
		$selected = 'selected="selected"';
		$option_name = 'pprh_test_option';
		\add_option( $option_name, 'true' );

		$actual_1 = \PPRH\Utils::does_option_match($option_name, 'true', $selected );
		self::assertEquals( $selected, $actual_1 );

		\update_option( $option_name, 'false' );
		$test_2 = \PPRH\Utils::does_option_match( $option_name, 'true', $selected );
		self::assertEquals( '', $test_2 );

		\delete_option( $option_name );
	}




	public function test_esc_get_option() {
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

	public function test_get_server_prop() {
		$_SERVER['HTTP_REFERER'] = 'https://sphacks.local/wp-admin/edit.php?post_type=page';
		$actual_1 = \PPRH\Utils::get_server_prop( 'HTTP_REFERER' );
		self::assertEquals( 'https://sphacks.local/wp-admin/edit.php?post_type=page', $actual_1 );
		unset( $_SERVER['HTTP_REFERER'] );

		$_SERVER['REQUEST_URI'] = '/wp-admin/admin.php?page=pprh-plugin-settings';
		$actual_2 = \PPRH\Utils::get_server_prop( 'REQUEST_URI' );
		self::assertEquals( '/wp-admin/admin.php?page=pprh-plugin-settings', $actual_2 );
		unset( $_SERVER['REQUEST_URI'] );

		$_SERVER['REQUEST_URI'] = '/wp-ad<>min/plu^gins.php';
		$actual_3 = \PPRH\Utils::get_server_prop( 'REQUEST_URI' );
		self::assertEquals( '/wp-admin/plugins.php', $actual_3 );
		unset( $_SERVER['REQUEST_URI'] );
	}

	public function test_on_pprh_page_ctrl() {
		$actual_1 = \PPRH\Utils::on_pprh_page_ctrl( false, 'https://sphacks.local/wp-admin/plugins.php?plugin_status=all&paged=1&s', '/wp-admin/admin.php?page=pprh-plugin-settings' );
		self::assertEquals( true, $actual_1 );

//		$actual_2 = \PPRH\Utils::on_pprh_page_ctrl( false, 'https://sphacks.local/wp-admin/edit.php?post_type=page', 'post.php' );
//		self::assertEquals( true, $actual_2 );

		$actual_3 = \PPRH\Utils::on_pprh_page_ctrl( true, 'https://sphacks.local/wp-admin/admin.php?page=pprh-plugin-settings', 'admin-ajax.php' );
		self::assertEquals( true, $actual_3 );

//		$actual_4 = \PPRH\Utils::on_pprh_page_ctrl(true, 'https://sphacks.local/wp-admin/post.php?post=2128&action=edit', 'admin-ajax.php' );
//		self::assertEquals( true, $actual_4 );

		$actual_5 = \PPRH\Utils::on_pprh_page_ctrl(false, 'https://sphacks.local/wp-admin/admin.php?page=pprh-plugin-settings', '/wp-admin/upload.php' );
		self::assertEquals( false, $actual_5 );

		$actual_6 = \PPRH\Utils::on_pprh_page_ctrl(false, 'https://sphacks.local/wp-admin/admin.php?page=pprh-plugin-settings', '/wp-admin/themes.php' );
		self::assertEquals( false, $actual_6 );

		$actual_7 = \PPRH\Utils::on_pprh_page_ctrl(false, 'https://sphacks.local/wp-admin/themes.php', '/wp-admin/options-general.php' );
		self::assertEquals( false, $actual_7 );

		$actual_8 = \PPRH\Utils::on_pprh_page_ctrl( false, 'https://sphacks.local/', '' );
		self::assertEquals(false, $actual_8 );

		$actual_9 = \PPRH\Utils::on_pprh_page_ctrl( true, 'asdfasys4ygdadf<>######%', '?' );
		self::assertEquals( false, $actual_9 );
	}



//	public function test_get_hints_free() {
//		$actual_1 = \PPRH\DAO::get_admin_hints();
//		self::assertIsArray( $actual_1 );
//
//		$actual_2 = \PPRH\DAO::get_client_hints( array() );
//		self::assertIsArray( $actual_2 );
//	}

	public function test_get_browser_name() {
		$user_agent_1 = '';
		$actual_1 = \PPRH\Utils::get_browser_name( $user_agent_1 );
		self::assertEmpty( $actual_1 );

		$user_agent_2 = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:89.0) Gecko/20100101 Firefox/89.0';
		$actual_2 = \PPRH\Utils::get_browser_name( $user_agent_2 );
		self::assertEquals( 'Firefox', $actual_2 );

		$user_agent_3 = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15';
		$actual_3 = \PPRH\Utils::get_browser_name( $user_agent_3 );
		self::assertEquals( 'Safari', $actual_3 );

		$user_agent_4 = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_16_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36';
		$actual_4 = \PPRH\Utils::get_browser_name( $user_agent_4 );
		self::assertEquals( 'Chrome', $actual_4 );

		$user_agent_5 = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.106 Safari/537.36 Edg/91.0.864.53';
		$actual_5 = \PPRH\Utils::get_browser_name( $user_agent_5 );
		self::assertEquals( 'Edge', $actual_5 );

		$user_agent_6 = 'Mozilla/5.0 (Macintosh;IntelMacOSX10_16_0)AppleWebKit/537.36(KHTML,likeGecko)Chrome/85.0.4183.121Safari/537.36OPR/71.0.3770.228';
		$actual_6 = \PPRH\Utils::get_browser_name( $user_agent_6 );
		self::assertEquals( 'Opera', $actual_6 );
	}

	public function test_json_to_array() {
		$json_1 = '{"license":{"id":"32","license_key":"6100767bb59850.76443566","status":"activated","name":"sam p","email":"asdf@gmail.com","txn_id":"","manual_reset_count":"0","date_created":"2021-07-27 15:07:46","date_renewed":"0000-00-00 00:00:00","date_expiry":"2022-07-27 00:00:00","registered_domain":"asdf.com","datetime_last_checked":"2021-07-27 15:07:46","max_sites":"5","site_count":3,"domain_list":"a:1:{i:0;s:13:\"sphacks.local\";}"},"response_code":{"msg":"Your license key has been activated!","code":130,"success":true}}';
		$actual_1 = \PPRH\Utils::json_to_array( $json_1 );
		self::assertNotEmpty( $actual_1 );

		$json_2 = json_encode( array( 'asdf' => true, 'asdfwa' => 2352365, 'asdfe' => 'asdf34w' ) );
		$actual_2 = \PPRH\Utils::json_to_array( $json_2 );
		self::assertCount( 3, $actual_2 );
	}

}
