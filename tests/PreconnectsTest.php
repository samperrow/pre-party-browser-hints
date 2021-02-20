<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class PreconnectsTest extends TestCase {

	public function test_constructor():void {
		$preconnects = new \PPRH\Preconnects();
		$loaded = has_action( 'wp_loaded', array($preconnects, 'initialize') );

		$reset_data = array(
			'autoload'        => get_option( 'pprh_preconnect_autoload' ),
			'allow_unauth'    => get_option( 'pprh_preconnect_allow_unauth' ),
			'preconnects_set' => get_option( 'pprh_preconnect_set' )
		);

		$this->assertEquals( true, $loaded );
		$this->assertEquals( $reset_data, $preconnects->reset_data );
	}

	public function test_initialize() {
		if (defined('PPRH_PRO_PLUGIN_ACTIVE') && PPRH_PRO_PLUGIN_ACTIVE) {
			$this->eval_pro_initialize();
		} else {
			$this->eval_free_initialize();
		}

	}

	public function eval_pro_initialize() {
		$preconnects_1 = new \PPRH\Preconnects();
		$actual_1 = $preconnects_1->initialize();
		$expected = ( 'true' === get_option( 'pprh_preconnect_pro_reset_globals' ) );


		$this->assertEquals($expected, $actual_1);
	}



	public function eval_free_initialize() {
		$preconnects_1 = new \PPRH\Preconnects();
		$preconnects_1->reset_data['autoload'] = 'false';
		$actual_1 = $preconnects_1->initialize();

		$preconnects_2 = new \PPRH\Preconnects();
		$preconnects_2->reset_data['autoload'] = 'true';
		$preconnects_2->reset_data['preconnects_set'] = 'true';
		$actual_2 = $preconnects_2->initialize();

		$preconnects_3 = new \PPRH\Preconnects();
		$preconnects_3->reset_data['autoload'] = 'true';
		$preconnects_3->reset_data['preconnects_set'] = 'false';
		$actual_3 = $preconnects_3->initialize();

		$this->assertEquals(false, $actual_1);
		$this->assertEquals(false, $actual_2);
		$this->assertEquals(true, $actual_3);
	}

	public function test_check_to_perform_reset():void {
		$preconnects = new \PPRH\Preconnects();

		$reset_data_1 = array(
			'autoload'        => 'true',
			'allow_unauth'    => 'true',
			'preconnects_set' => 'true',
			'reset_pro'       => null
		);

		$reset_data_2 = array(
			'autoload'        => 'true',
			'allow_unauth'    => 'true',
			'preconnects_set' => 'false',
			'reset_pro'       => null
		);

		$reset_data_3 = array(
			'autoload'        => 'true',
			'allow_unauth'    => 'true',
			'preconnects_set' => 'true',
			'reset_pro'       => array(
				'perform_reset' => false,
			)
		);

		$actual_1 = $preconnects->check_to_perform_reset( $reset_data_1 );
		$actual_2 = $preconnects->check_to_perform_reset( $reset_data_2 );
		$actual_3 = $preconnects->check_to_perform_reset( $reset_data_3 );

		$this->assertEquals(false, $actual_1);
		$this->assertEquals(true, $actual_2);
		$this->assertEquals(false, $actual_3);

		if ( defined( 'PPRH_PRO_PLUGIN_ACTIVE' ) && PPRH_PRO_PLUGIN_ACTIVE ) {
			$reset_data_4 = array(
				'autoload'        => 'true',
				'allow_unauth'    => 'true',
				'preconnects_set' => 'true',
				'reset_pro'       => array(
					'perform_reset' => true,
				)
			);

			$reset_data_5 = array(
				'autoload'        => 'true',
				'allow_unauth'    => 'true',
				'preconnects_set' => 'true',
				'reset_pro'       => array(
					'perform_reset' => false,
				)
			);

			$actual_4 = $preconnects->check_to_perform_reset( $reset_data_4 );
			$actual_5 = $preconnects->check_to_perform_reset( $reset_data_5 );

			$this->assertEquals(true, $actual_4);
			$this->assertEquals(false, $actual_5);
		}
	}

	public function test_check_to_enqueue_scripts():void {
		$preconnects = new \PPRH\Preconnects();

		$actual_1 = $preconnects->check_to_enqueue_scripts( 'false', false );
		$actual_2 = $preconnects->check_to_enqueue_scripts( 'true', true );

		$scripts_enqueued = has_action( 'wp_enqueue_scripts', array($preconnects, 'enqueue_scripts') );
		$this->assertEquals(true, $scripts_enqueued);

		$actual_3 = $preconnects->check_to_enqueue_scripts( 'true', false );
		$actual_4 = $preconnects->check_to_enqueue_scripts( 'false', true );

		$this->assertEquals(false, $actual_1);
		$this->assertEquals(true, $actual_2);
		$this->assertEquals(true, $actual_3);
		$this->assertEquals(true, $actual_4);
	}

	public function test_perform_free_reset():void {
		$preconnects = new \PPRH\Preconnects();

		$test_1 = array(
			'autoload'        => 'true',
			'preconnects_set' => 'true'
		);

		$test_2 = array(
			'autoload'        => 'true',
			'preconnects_set' => 'false'
		);

		$test_3 = array(
			'autoload'        => 'false',
			'preconnects_set' => 'true'
		);

		$test_4 = array(
			'autoload'        => 'false',
			'preconnects_set' => 'false'
		);

		$actual_1 = $preconnects->perform_free_reset($test_1);
		$actual_2 = $preconnects->perform_free_reset($test_2);
		$actual_3 = $preconnects->perform_free_reset($test_3);
		$actual_4 = $preconnects->perform_free_reset($test_4);

		$this->assertEquals(false, $actual_1);
		$this->assertEquals(true, $actual_2);
		$this->assertEquals(false, $actual_3);
		$this->assertEquals(false, $actual_4);
	}

	public function test_perform_pro_reset():void {
		$preconnects = new \PPRH\Preconnects();
		$reset_pro_1 = null;
		$reset_pro_2 = false;
		$reset_pro_3 = array(
			'perform_reset' => false
		);
		$reset_pro_4 = array(
			'perform_reset' => true
		);

		$actual_1 = $preconnects->perform_pro_reset($reset_pro_1);
		$actual_2 = $preconnects->perform_pro_reset($reset_pro_2);
		$actual_3 = $preconnects->perform_pro_reset($reset_pro_3);
		$actual_4 = $preconnects->perform_pro_reset($reset_pro_4);

		$this->assertEquals(false, $actual_1);
		$this->assertEquals(false, $actual_2);
		$this->assertEquals(false, $actual_3);
		$this->assertEquals(true, $actual_4);
	}

	public function test_filters_work() {
		$actual_reset_pro = apply_filters('pprh_preconnects_perform_reset', null);

		if ( defined( 'PPRH_PRO_PLUGIN_ACTIVE' ) && PPRH_PRO_PLUGIN_ACTIVE ) {
			$expected_reset_pro = $actual_reset_pro;
		} else {
			$expected_reset_pro = null;
		}

		$this->assertEquals($expected_reset_pro, $actual_reset_pro);
	}

	public function test_load_ajax_actions() {
		$preconnects = new \PPRH\Preconnects();
		$ajax_cb = 'pprh_post_domain_names';

		$wp_ajax_nopriv_added_1 = has_action( "wp_ajax_nopriv_$ajax_cb", array($preconnects, $ajax_cb) );
		$wp_ajax_added_1 = has_action( "wp_ajax_$ajax_cb", array($preconnects, $ajax_cb) );
		$this->assertEquals(false, $wp_ajax_nopriv_added_1);
		$this->assertEquals(false, $wp_ajax_added_1);


		$preconnects->load_ajax_actions( 'false' );
		$wp_ajax_nopriv_added_2 = has_action( "wp_ajax_nopriv_$ajax_cb", array($preconnects, $ajax_cb) );
		$wp_ajax_added_2 = has_action( "wp_ajax_$ajax_cb", array($preconnects, $ajax_cb) );
		$this->assertEquals(false, $wp_ajax_nopriv_added_2);
		$this->assertEquals(true, $wp_ajax_added_2);

		$preconnects->load_ajax_actions( 'true' );
		$wp_ajax_nopriv_added_3 = has_action( "wp_ajax_nopriv_$ajax_cb", array($preconnects, $ajax_cb) );
		$wp_ajax_added_3 = has_action( "wp_ajax_$ajax_cb", array($preconnects, $ajax_cb) );
		$this->assertEquals(true, $wp_ajax_nopriv_added_3);
		$this->assertEquals(true, $wp_ajax_added_3);
	}


	public function test_enqueue_scripts() {
		global $wp_scripts;
		$preconnects_1 = new \PPRH\Preconnects();
		$preconnects_1->is_admin = true;
		$actual_1 = $preconnects_1->enqueue_scripts();
		$this->assertEquals( false, $actual_1);

		$preconnects_2 = new \PPRH\Preconnects();
		$preconnects_2->is_admin = false;
		$preconnects_2->enqueue_scripts();
		$actual_scripts = array();

		foreach( $wp_scripts->queue as $script ) {
			$actual_scripts[] =  $wp_scripts->registered[$script]->handle;
		}

		if ( is_admin() ) {
			$expected_scripts = array( 'pprh_admin_js' );
		}

		$expected_scripts[] = 'pprh-find-domain-names';

		$this->assertEquals( $expected_scripts, $actual_scripts);
	}

	public function test_create_js_object() {
		$preconnects = new \PPRH\Preconnects();

		$test_arr_1 = array(
			'hints'      => array(),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => time()
		);

		$expected_object_1 = apply_filters( 'pprh_preconnects_append_js_object', $test_arr_1 );
		$actual_object_1 = $preconnects->create_js_object();
		$this->assertEquals( $expected_object_1, $actual_object_1 );
	}

//	public function _set_js_object() {
//
//	}


//	public function test_free_load_auto_preconnects():void {
//		$preconnects = new \PPRH\Preconnects();
//		$autoload_option = 'pprh_preconnect_autoload';
//		$set = 'pprh_preconnect_set';
//		$autoload_initial = get_option( $autoload_option );
//		$preconnects_set_initial = get_option( $set );
//
//		update_option( $autoload_option, 'true' );
//		update_option( $set, 'false' );
//		$load_preconnects = $preconnects->load_auto_preconnects(null);
//		$this->assertEquals( true, $load_preconnects );
//
//		update_option( $autoload_option, 'false' );
//		$load_preconnects2 = $preconnects->load_auto_preconnects(null);
//		$this->assertEquals( false, $load_preconnects2 );
//
//		update_option( $set, 'true' );
//		$load_preconnects3 = $preconnects->load_auto_preconnects(null);
//		$this->assertEquals( false, $load_preconnects3 );
//
//		update_option( $autoload_option, $autoload_initial );
//		update_option( $set, $preconnects_set_initial );
//	}
//
//	public function test_pro_load_auto_preconnects():void {
//		$preconnects = new \PPRH\Preconnects();
//
//		$reset_data1 = array(
//			'reset'   => true,
//			'post_id' => 'global',
//		);
//
//		$reset_data2 = array(
//			'reset'   => false,
//			'post_id' => '0',
//		);
//
//		$load_preconnects1 = $preconnects->load_auto_preconnects($reset_data1);
//		$load_preconnects2 = $preconnects->load_auto_preconnects($reset_data2);
//
//		$this->assertEquals( true, $load_preconnects1 );
//		$this->assertEquals( false, $load_preconnects2 );
//	}
//
//	public function test_both_load_auto_preconnects():void {
//		$preconnects = new \PPRH\Preconnects();
//		$autoload_option = 'pprh_preconnect_autoload';
//		$set = 'pprh_preconnect_set';
//		$autoload_initial = get_option( $autoload_option );
//		$preconnects_set_initial = get_option( $set );
//
//		$reset_pro = array(
//			'reset'   => true,
//			'post_id' => '0',
//		);
//
//		update_option( $autoload_option, 'true' );
//		update_option( $set, 'true' );
//		$load_preconnects = $preconnects->load_auto_preconnects($reset_pro);
//		$this->assertEquals( true, $load_preconnects );
//
//		$reset_pro = array(
//			'reset'   => false,
//			'post_id' => '0',
//		);
//
//		update_option( $autoload_option, 'true' );
//		update_option( $set, 'true' );
//		$load_preconnects2 = $preconnects->load_auto_preconnects($reset_pro);
//		$this->assertEquals( false, $load_preconnects2 );
//
//		update_option( $autoload_option, $autoload_initial );
//		update_option( $set, $preconnects_set_initial );
//	}


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



	public function test_pprh_post_domain_names() {
		$preconnects = new \PPRH\Preconnects();

		$_SERVER['HTTP_HOST'] = 'sphacks.local';
		$_SERVER['HTTP_REFERER'] = 'sphacks.local';
		$token = wp_get_session_token();
		$i     = wp_nonce_tick();
		$user  = wp_get_current_user();
		$uid   = (int) $user->ID;
		$action = 'pprh_ajax_nonce';

		// Nonce generated 0-12 hours ago.
		$expected_nonce = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );

		$_POST['pprh_data'] = '{"hints":["https://fonts.gstaticTest.com"],"nonce":"' . 'f477d2885b' . '","admin_url":"https://sphacks.local/wp-admin/admin-ajax.php","start_time":"1613812044","post_url":"/sam-perrow/","post_id":"global"}';
		$_REQUEST['_ajax_nonce'] = $expected_nonce;

		$_POST['action'] = 'pprh_post_domain_names';
		$actual = $preconnects->pprh_post_domain_names();
		$actual_json = $actual;
		$expected_json = json_encode( array( true ) );

		$this->assertEquals( $expected_json, $actual_json );
	}


	public function test_process_hints() {
		$preconnects = new \PPRH\Preconnects();
		$url1 = 'https://test-process-hints.com';
		$url2 = 'https://test-process-hints.net';

		$test_data = array(
			'hints' => array( $url1, $url2 ),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => time()
		);

		$result_arr = $preconnects->process_hints( $test_data );

		$this->assertEquals( array( true, true ), $result_arr );
	}

	public function test_create_hint_array():void {
		$preconnects = new \PPRH\Preconnects();
		$test_url_1 = 'https://www.testerasdf.com';
		$new_cols_1 = array(
			'post_id'  => 'global',
    		'post_url' => '/author/'
		);
		$actual_1 = $preconnects->create_hint_array( $test_url_1, $new_cols_1 );

		$expected_arr_1 = array(
			'url'          => 'https://www.testerasdf.com',
			'post_id'      => 'global',
    		'post_url'     => '/author/',
    		'hint_type'    => 'preconnect',
			'auto_created' => 1
		);

		$this->assertEquals( $expected_arr_1, $actual_1);
	}
}
