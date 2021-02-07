<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class PreconnectsTest extends TestCase {

	public function test__constructor():void {
		$preconnects = new \PPRH\Preconnects();
		$loaded = has_action( 'wp_loaded', array($preconnects, 'initialize') );
		$this->assertEquals( true, $loaded );
	}

//	public function test_initialize() {
//		$defined = defined('PPRH_DOING_AUTO_PRECONNECTS');
//
//		$this->assertEquals(true, $defined);
//		$this->_load_ajax_actions1();
//		$this->_load_ajax_actions2();
//
//		if ( ! is_admin() ) {
//			$reg = wp_script_is( 'pprh-find-domain-names', 'enqueued' );
//		} else {
//			$reg = true;
//		}
//		$this->assertEquals(true, $reg);
//	}

	public function test_free_load_auto_preconnects():void {
		$preconnects = new \PPRH\Preconnects();
		$autoload_option = 'pprh_preconnect_autoload';
		$set = 'pprh_preconnect_set';
		$autoload_initial = get_option( $autoload_option );
		$preconnects_set_initial = get_option( $set );

		update_option( $autoload_option, 'true' );
		update_option( $set, 'false' );
		$load_preconnects = $preconnects->load_auto_preconnects(null);
		$this->assertEquals( true, $load_preconnects );

		update_option( $autoload_option, 'false' );
		$load_preconnects2 = $preconnects->load_auto_preconnects(null);
		$this->assertEquals( false, $load_preconnects2 );

		update_option( $set, 'true' );
		$load_preconnects3 = $preconnects->load_auto_preconnects(null);
		$this->assertEquals( false, $load_preconnects3 );

		update_option( $autoload_option, $autoload_initial );
		update_option( $set, $preconnects_set_initial );
	}

	public function test_pro_load_auto_preconnects():void {
		$preconnects = new \PPRH\Preconnects();

		$reset_data1 = array(
			'reset'   => true,
			'post_id' => 'global',
		);

		$reset_data2 = array(
			'reset'   => false,
			'post_id' => '0',
		);

		$load_preconnects1 = $preconnects->load_auto_preconnects($reset_data1);
		$load_preconnects2 = $preconnects->load_auto_preconnects($reset_data2);

		$this->assertEquals( true, $load_preconnects1 );
		$this->assertEquals( false, $load_preconnects2 );
	}

	public function test_both_load_auto_preconnects():void {
		$preconnects = new \PPRH\Preconnects();
		$autoload_option = 'pprh_preconnect_autoload';
		$set = 'pprh_preconnect_set';
		$autoload_initial = get_option( $autoload_option );
		$preconnects_set_initial = get_option( $set );

		$reset_pro = array(
			'reset'   => true,
			'post_id' => '0',
		);

		update_option( $autoload_option, 'true' );
		update_option( $set, 'true' );
		$load_preconnects = $preconnects->load_auto_preconnects($reset_pro);
		$this->assertEquals( true, $load_preconnects );

		$reset_pro = array(
			'reset'   => false,
			'post_id' => '0',
		);

		update_option( $autoload_option, 'true' );
		update_option( $set, 'true' );
		$load_preconnects2 = $preconnects->load_auto_preconnects($reset_pro);
		$this->assertEquals( false, $load_preconnects2 );

		update_option( $autoload_option, $autoload_initial );
		update_option( $set, $preconnects_set_initial );
	}


	public function util_load_ajax_actions( $allow_unauth ) {
		$preconnects = new \PPRH\Preconnects();
		$preconnects->load_ajax_actions( $allow_unauth );
		$ajax_cb = 'pprh_post_domain_names';

		$ajax_cb_loaded = has_action( "wp_ajax_$ajax_cb", array($preconnects, $ajax_cb) );
		$ajax_cb_nopriv_loaded = has_action( "wp_ajax_nopriv_$ajax_cb", array($preconnects, $ajax_cb) );

		return array(
			$ajax_cb_loaded,
			$ajax_cb_nopriv_loaded
		);
	}

	// tests that only logged in users will load the preconnect ajax actions
	public function _load_ajax_actions1() {
		$ajax_actions = $this->util_load_ajax_actions( 'false' );
		$this->assertEquals( array( 10, false ), $ajax_actions );
	}

	// tests that all users will load the preconnect ajax actions
	public function _load_ajax_actions2() {
		$ajax_actions = $this->util_load_ajax_actions( 'true' );
		$this->assertEquals( array( 10, true ), $ajax_actions );
	}

	public function _set_js_object() {
		$preconnects = new \PPRH\Preconnects();

		$arr = array(
			'hints'      => array(),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => time()
		);

		$js_object = apply_filters( 'pprh_append_js_object', $arr );

		$asdf = $preconnects->create_js_object();
		return $asdf;
//		$this->assertEquals( $js_object, $asdf );
	}

	public function test_pprh_post_domain_names() {
		$preconnects = new \PPRH\Preconnects();

		$_SERVER['HTTP_HOST'] = 'sphacks.local';
		$_SERVER['REQUEST_URI'] = '/wp-admin/admin.php?page=pprh-plugin-settings';
		$_SERVER['HTTP_REFERER'] = 'sphacks.local';
		$token = wp_get_session_token();
		$i     = wp_nonce_tick();
		$user  = wp_get_current_user();
		$uid   = (int) $user->ID;
		$action = 'pprh_ajax_nonce';

		// Nonce generated 0-12 hours ago.
		$expected_nonce = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );

		$_POST['pprh_data'] = '{"hints":["https://fonts.gstatic.com"],"nonce":"' . $expected_nonce . '","admin_url":"https://sphacks.local/wp-admin/admin-ajax.php","start_time":"1612589532","post_url":"/"}';
		$_POST['action'] = 'pprh_post_domain_names';
//		$actual = $preconnects->pprh_post_domain_names();
//		$actual_json = json_encode( $actual );
//		$expected_json = json_encode( array( true ) );

//		$this->assertEquals( $expected_json, $actual_json );
	}


	public function test_process_hints() {
		$preconnects = new \PPRH\Preconnects();
		$url1 = 'https://fonts.gstatic.com';
		$url2 = 'https://googleapis.com';
		$hint_data = (object) $this->_set_js_object();
		$hint_data->hints = array( $url1, $url2 );

		$result_arr = $preconnects->process_hints( $hint_data );
		$this->assertEquals( array( true, true ), $result_arr );
	}

}
