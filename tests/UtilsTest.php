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
		$str2 = 'sfjsdlfj4w9tu3wofjw93u3';

		$test1 = \PPRH\Utils::strip_non_alphanums($str1);
		$test2 = \PPRH\Utils::strip_non_alphanums($str2);

		$this->assertEquals( 'faFED265b2tbYT28352', $test1 );
		$this->assertEquals( $str2, $test2 );
	}

	public function test_strip_non_numbers():void {
		$str1 = '!f_a#FED__=26 5b-2tb(&YT^>"28352';
		$test1 = \PPRH\Utils::strip_non_numbers($str1);

		$this->assertEquals( '265228352', $test1 );
	}

	public function test_clean_hint_type():void {
		$str1 = 'DNS-prefetch';
		$str2 = 'pre*(con@"><nect';

		$test1 = \PPRH\Utils::clean_hint_type($str1);
		$test2 = \PPRH\Utils::clean_hint_type($str2);

		$this->assertEquals( $str1, $test1 );
		$this->assertEquals( 'preconnect', $test2 );
	}

	public function test_clean_url():void {
		$str1 = 'https://www.espn.com';
		$str2 = 'https"://<script\>test.com<script>';

		$test1 = \PPRH\Utils::clean_url($str1);
		$test2 = \PPRH\Utils::clean_url($str2);

		$this->assertEquals( $str1, $test1 );
		$this->assertEquals( 'https://scripttest.comscript', $test2 );
	}

//	public function test_clean_url_path():void {
//		$str1 = 'https://www.espn.com';
//		$str2 = 'https"://<script\>test.com<script>';
//
//		$test1 = PPRH\Utils::clean_url_path($str1);
//		$test2 = PPRH\Utils::clean_url_path($str2);
//
//		$this->assertEquals( $str1, $test1 );
//		$this->assertEquals( 'https://scripttest.comscript', $test2 );
//	}

	public function test_clean_hint_attr():void {
		$str1 = 'font/woff2';
		$str2 = 'f<\/asdlfkj43*#t935u23" asdflkj3';

		$test1 = \PPRH\Utils::clean_hint_attr($str1);
		$test2 = \PPRH\Utils::clean_hint_attr($str2);

		$this->assertEquals( $str1, $test1 );
		$this->assertEquals( 'f/asdlfkj43t935u23asdflkj3', $test2 );
	}

	public function test_get_opt_val():void {
		$str1 = 'pprh_preconnect_allow_unauth';
		$str2 = 'pprh_not_real_option';

		$test_1 = \PPRH\Utils::get_opt_val($str1);
		$actual_test_1 = ( 'true' === $test_1 || 'false' === $test_1 );
		$test2 = \PPRH\Utils::get_opt_val($str2);

		$this->assertEquals( true, $actual_test_1 );
		$this->assertEquals( '', $test2 );
	}

//	public function test_create_db_result():void {
//
//
//		$test1 = PPRH\Utils::create_db_result($str1);
//
//		$this->assertEquals( '', $test2 );
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
//		$this->assertEquals( $result1, $test1 );
//		$this->assertEquals( $result2, $test2 );
//	}

	public function test_get_option_status():void {
		$option_name = 'pprh_prefetch_enabled';
		$prefetch_enabled = get_option( $option_name );

		update_option( $option_name, 'false' );
		$test2 = \PPRH\Utils::get_option_status($option_name, 'true' );
		$this->assertEquals( '', $test2 );


		update_option( $option_name, 'true' );
		$test1 = \PPRH\Utils::get_option_status($option_name, 'true' );
		$this->assertEquals( 'selected=selected', $test1 );

		update_option( $option_name, $prefetch_enabled );
	}

//	public function test_on_pprh_page():void {
//		global $pagenow;
//		$pagenow = 'admin.php';
//		$_GET['page'] = 'pprh-plugin-settings';
//
//		$test1 = \PPRH\Utils::on_pprh_page();
//
//		$this->assertEquals( true, $test1 );
//	}


	public function test_create_pprh_hint_fail():void {
		$create_hints = new \PPRH\CreateHints();
		$raw_data1 = TestUtils::create_hint_array( '', '' );
		$actual = $create_hints->create_hint( $raw_data1 );
		$this->assertEquals( false, $actual );
	}

	public function test_create_pprh_hint_dup_hints():void {
		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();
		$data1 = TestUtils::create_hint_array( 'blah.com', 'preconnect' );
		$hint1 = $create_hints->create_hint($data1);

		$actual1 = $dao->insert_hint( $hint1 );

		$hint2 = $create_hints->new_hint_controller( $hint1 );

		$this->assertEquals( true, $actual1->db_result['success'] );
		$this->assertEquals( false, $hint2->db_result['success'] );
		$dao->delete_hint( $actual1->db_result['hint_id'] );
	}

	public function test_create_hint_array():void {
		$expected = TestUtils::create_hint_array( 'test.com', 'preconnect', 'audio', 'font/woff2', 'crossorigin', 'screen' );

		$test1 = array(
			'url'          => 'test.com',
			'hint_type'    => 'preconnect',
			'as_attr'      => 'audio',
			'type_attr'    => 'font/woff2',
			'crossorigin'  => 'crossorigin',
			'media'        => 'screen'
		);

		$actual = apply_filters( 'pprh_ch_append_hint', $test1, array() );
		$this->assertEquals( $expected, $actual );
	}

	public function test_create_response():void {
		$dao = new \PPRH\DAO();
		$result = true;
		$new_hint = null;

		$expected = (object) array(
			'new_hint'  => $new_hint,
			'db_result' => array(
				'msg'        => 'Resource hint created successfully.',
				'status'     => ( $result ) ? 'success' : 'error',
				'success'    => $result,
				'hint_id'    => '',
				'last_error' => '',
			)
		);

		$test1 = $dao->create_db_result( true, '', '', 'create', null );

		$this->assertEquals( $expected, $test1 );
	}

//	public function test_db_op_success():void {
//
//
//	}

	public function test_esc_get_option():void {
		$test_option_name1 = 'pprh_test_option1';
		$test_option_name2 = 'pprh_test_option2';

		add_option( $test_option_name1, 'https://<test.com>/asdfasdf', '', 'true' );
		add_option( $test_option_name2, 'https://test.com/asdfasdf', '', 'true' );

		$actual1 = \PPRH\Utils::esc_get_option( $test_option_name1 );
		$actual2 = \PPRH\Utils::esc_get_option( $test_option_name2 );

		$this->assertEquals( 'https://&lt;test.com&gt;/asdfasdf', $actual1 );
		$this->assertEquals( 'https://test.com/asdfasdf', $actual2 );

		delete_option( $test_option_name1 );
		delete_option( $test_option_name2 );
	}

	public function test_on_pprh_admin() {
		if ( WP_ADMIN ) {
//			$_SERVER['PHP_SELF'] = '/Users/samperrow/repos/WordPress/wp-admin/admin.php';

			if ( wp_doing_ajax() ) {

				$_SERVER['HTTP_REFERER'] = 'pprh-plugin-settings';
				$actual_1 = \PPRH\Utils::on_pprh_admin();

				$_SERVER['HTTP_REFERER'] = 'post.php';
				$actual_2 = \PPRH\Utils::on_pprh_admin();

				$_SERVER['HTTP_REFERER'] = '';
				$actual_3 = \PPRH\Utils::on_pprh_admin();
			} else {
				$_GET['page'] = 'pprh-plugin-settings';
				$actual_1 = \PPRH\Utils::on_pprh_admin();

				$_GET['page'] = 'post.php';
				$actual_2 = \PPRH\Utils::on_pprh_admin();

				$_GET['page'] = '';
				$actual_3 = \PPRH\Utils::on_pprh_admin();
			}

			$this->assertEquals( true, $actual_1 );
			$this->assertEquals( false, $actual_2 );
			$this->assertEquals( false, $actual_3 );
		}

		else {
			$actual_4 = \PPRH\Utils::on_pprh_admin();
			$this->assertEquals( false, $actual_4 );
		}

	}


}
