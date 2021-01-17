<?php

declare(strict_types=1);

namespace PPRH;

use PHPUnit\Framework\TestCase;

//class PreconnectsTest extends TestCase {

	//	public function test__construct () {
//
//	}

//	public function test_initialize () {
//
//	}


//	public function test_load () {
//
//	}

//	public function test_set_js_object () {
//
//	}

//	public function test_pprh_post_domain_names () {
//		$preconnects = new \PPRH\Preconnects();
//
//		define( 'DOING_AJAX', true );
//		$_SERVER['HTTP_HOST'] = 'sphacks.local';
//		$_SERVER['REQUEST_URI'] = '/wp-admin/admin.php?page=pprh-plugin-settings';
//		$_SERVER['HTTP_REFERER'] = 'sphacks.local';
//		$token = wp_get_session_token();
//		$i     = wp_nonce_tick();
//		$user  = wp_get_current_user();
//		$uid   = (int) $user->ID;
//		$action = 'pprh_table_nonce';
//
//		// Nonce generated 0-12 hours ago.
//		$expected_nonce = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
//
//		$_POST['pprh_data'] = '{"hints":["https://fonts.gstatic.com"],"nonce":"9cbaa9b368","admin_url":"https://sphacks.local/wp-admin/admin-ajax.php","start_time":"1610918613"}';
//		$_POST['action'] = 'pprh_post_domain_names';
//
//	}
//
//
//	public function test_create_hint() {
//		update_option( 'pprh_preconnect_set', 'false' );
//		$preconnects = new \PPRH\Preconnects();
//		$url1 = 'https://auto-preconnect.com';
//
//		$hint_arr = Utils::create_raw_hint_array($url1, 'preconnect', 1 );
//		$hint1 = Utils::create_pprh_hint( $hint_arr );
//
//		$asdf = (object) array(
//			'hints' => array(
//				$url1
//			)
//		);
//		$result_arr = $preconnects->process_hints( $asdf );
//		$expected = Utils::create_db_result( true, $result_arr['0']->db_result['hint_id'], '', $hint1 );
//		$expected_arr = array( $expected );
//		$this->assertEquals( $expected_arr, $result_arr );
//	}

//}
