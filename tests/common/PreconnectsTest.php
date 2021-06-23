<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class PreconnectsTest extends TestCase {


	public function util_create_free_config_arr( $autoload, $allow_unauth, $preconnects_set )  {
		return array(
			'autoload' => $autoload,
			'allow_unauth' => $allow_unauth,
			'preconnects_set' => $preconnects_set,
		);
	}


	public function test_constructor():void {
		$preconnects = new \PPRH\Preconnects();
		$loaded = \add_action( 'wp_loaded', array($preconnects, 'init_controller') );
		self::assertEquals( true, $loaded );

		$actual_config = $preconnects->get_config( 'true', 'true', 'true' );
		$expected_config = array(
			'autoload'        => true,
			'allow_unauth'    => true,
			'preconnects_set' => true,
		);
		self::assertEquals( $expected_config, $actual_config );
	}

	public function test_entire_preconnect_class(): void {
		$preconnects = new \PPRH\Preconnects();

		$config_1 = $preconnects->get_config( 'true', 'true', 'true' );
		$actual_1 = $preconnects->initialize( $config_1 );
		self::assertEquals( false, $actual_1 );

		$config_2 = $preconnects->get_config( 'true', 'true', 'false' );
		$actual_2 = $preconnects->initialize( $config_2 );
		self::assertEquals( true, $actual_2 );

		$config_3 = $preconnects->get_config( 'false', 'true', 'true' );
		$actual_3 = $preconnects->initialize( $config_3 );
		self::assertEquals( false, $actual_3 );

		$config_4 = $preconnects->get_config( 'false', 'true', 'false' );
		$actual_4 = $preconnects->initialize( $config_4 );
		self::assertEquals( false, $actual_4 );
	}





	public function test_initialize() {
		$preconnects = new \PPRH\Preconnects();
		$config = $preconnects->config;

		$config['autoload'] = false;
		$actual_1 = $preconnects->initialize($config);

		$config['autoload'] = true;
		$config['preconnects_set'] = true;
		$actual_2 = $preconnects->initialize($config);

		$config['autoload'] = true;
		$config['preconnects_set'] = false;
		$actual_3 = $preconnects->initialize($config);

		self::assertEquals(false, $actual_1);
		self::assertEquals(false, $actual_2);
		self::assertEquals(true, $actual_3);
	}





	public function test_check_to_enqueue_scripts():void {
		$preconnects = new \PPRH\Preconnects();

		$actual_1 = $preconnects->check_to_enqueue_scripts(true);
		$actual_2 = $preconnects->check_to_enqueue_scripts(false);

		$scripts_enqueued = \add_action( 'wp_enqueue_scripts', array($preconnects, 'enqueue_scripts') );
		self::assertEquals(true, $scripts_enqueued);

		self::assertEquals(true, $actual_1);
		self::assertEquals(false, $actual_2);
	}


	public function test_load_ajax_actions() {
		$preconnects = new \PPRH\Preconnects();
		$ajax_cb = 'pprh_post_domain_names';

		$wp_ajax_nopriv_added_1 = \has_action( "wp_ajax_nopriv_$ajax_cb", array($preconnects, $ajax_cb) );
		$wp_ajax_added_1 = \has_action( "wp_ajax_$ajax_cb", array($preconnects, $ajax_cb) );
		self::assertEquals(false, $wp_ajax_nopriv_added_1);
		self::assertEquals(false, $wp_ajax_added_1);


		$preconnects->load_ajax_actions( false );
		$wp_ajax_nopriv_added_2 = \has_action( "wp_ajax_nopriv_$ajax_cb", array($preconnects, $ajax_cb) );
		$wp_ajax_added_2 = \has_action( "wp_ajax_$ajax_cb", array($preconnects, $ajax_cb) );
		self::assertEquals(false, $wp_ajax_nopriv_added_2);
		self::assertEquals(true, $wp_ajax_added_2);

		$preconnects->load_ajax_actions( true );
		$wp_ajax_nopriv_added_3 = \has_action( "wp_ajax_nopriv_$ajax_cb", array($preconnects, $ajax_cb) );
		$wp_ajax_added_3 = \has_action( "wp_ajax_$ajax_cb", array($preconnects, $ajax_cb) );
		self::assertEquals(true, $wp_ajax_nopriv_added_3);
		self::assertEquals(true, $wp_ajax_added_3);
	}


//	public function test_enqueue_scripts() {
//		global $wp_scripts;
//		$preconnects_1 = new \PPRH\Preconnects();
//		$preconnects_1->is_admin = true;
//		$actual_1 = $preconnects_1->enqueue_scripts();
//		self::assertEquals( false, $actual_1);
//
//		$preconnects_2 = new \PPRH\Preconnects();
//		$preconnects_2->is_admin = false;
//		$preconnects_2->enqueue_scripts();
//		$actual_scripts = array();
//
//		foreach( $wp_scripts->queue as $script ) {
//			$actual_scripts[] =  $wp_scripts->registered[$script]->handle;
//		}
//
//		$expected_scripts = array();
//
//		if ( WP_ADMIN ) {
//			$expected_scripts = array( 'thickbox', 'pprh_admin_js' );
//		} else {
//			$expected_scripts[] = 'pprh-find-domain-names';
//		}
//
//		self::assertEquals( $expected_scripts, $actual_scripts);
//	}

	public function test_create_js_object() {
		$preconnects = new \PPRH\Preconnects();

		$expected_arr_1 = array(
			'hints'      => array(),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => time(),
			'hint_type'  => 'preconnect'
		);

		$actual_object_1 = $preconnects->create_js_object();
		self::assertEquals( $expected_arr_1, $actual_object_1 );
	}


	public function test_allow_unauth_users():void {
		$preconnects = new \PPRH\Preconnects();

		$expected_1 = $preconnects->allow_unauth_users( true, true );
		$expected_2 = $preconnects->allow_unauth_users( true, false );
		$expected_3 = $preconnects->allow_unauth_users( false, true );
		$expected_4 = $preconnects->allow_unauth_users( false, false );

		self::assertEquals( true, $expected_1 );
		self::assertEquals( true, $expected_2 );
		self::assertEquals( true, $expected_3 );
		self::assertEquals( false, $expected_4 );
	}



//	public function test_free_load_auto_preconnects():void {
//		$preconnects = new \PPRH\Preconnects();
//		$autoload_option = 'pprh_preconnect_autoload';
//		$set = 'pprh_preconnect_set';
//		$autoload_initial = \get_option( $autoload_option );
//		$preconnects_set_initial = \get_option( $set );
//
//		update_option( $autoload_option, 'true' );
//		update_option( $set, 'false' );
//		$load_preconnects = $preconnects->load_auto_preconnects(null);
//		self::assertEquals( true, $load_preconnects );
//
//		update_option( $autoload_option, 'false' );
//		$load_preconnects2 = $preconnects->load_auto_preconnects(null);
//		self::assertEquals( false, $load_preconnects2 );
//
//		update_option( $set, 'true' );
//		$load_preconnects3 = $preconnects->load_auto_preconnects(null);
//		self::assertEquals( false, $load_preconnects3 );
//
//		update_option( $autoload_option, $autoload_initial );
//		update_option( $set, $preconnects_set_initial );
//	}
//

	public function util_load_ajax_actions( $allow_unauth ) {
		$preconnects = new \PPRH\Preconnects();
		$preconnects->load_ajax_actions( $allow_unauth );
		$ajax_cb = 'pprh_post_domain_names';

		$ajax_cb_loaded = \has_action( "wp_ajax_$ajax_cb", array($preconnects, $ajax_cb) );
		$ajax_cb_nopriv_loaded = \has_action( "wp_ajax_nopriv_$ajax_cb", array($preconnects, $ajax_cb) );

		return array(
			$ajax_cb_loaded,
			$ajax_cb_nopriv_loaded
		);
	}

	// tests that only logged in users will load the preconnect ajax actions
//	public function _load_ajax_actions1() {
//		$ajax_actions = $this->util_load_ajax_actions( 'false' );
//		self::assertEquals( array( 10, false ), $ajax_actions );
//	}

//	// tests that all users will load the preconnect ajax actions
//	public function _load_ajax_actions2() {
//		$ajax_actions = $this->util_load_ajax_actions( 'true' );
//		self::assertEquals( array( 10, true ), $ajax_actions );
//	}



	public function test_pprh_post_domain_names() {
		$preconnects = new \PPRH\Preconnects();
		$expected_nonce = TestUtils::create_nonce( 'pprh_ajax_nonce' );

		$hint_1 = TestUtils::create_hint_array( 'https://fonts.gstaticTest.com', 'preconnect' );
		$hint_2 = TestUtils::create_hint_array( 'https://fonts.gstaticTest2.com', 'preconnect' );

		$test_data = $preconnects->create_js_object();
		$test_data['hints'] = array( $hint_1, $hint_2 );

		$_POST['pprh_data'] = json_encode( $test_data );
		$_REQUEST['_ajax_nonce'] = $expected_nonce;
		$_POST['action'] = 'pprh_post_domain_names';
		$actual = $preconnects->pprh_post_domain_names();

		if ( wp_doing_ajax() ) {
			self::assertEquals( true, $actual );
		} else {
			self::assertEquals( null, $actual );
		}
	}



	public function test_process_hints() {
		$preconnects = new \PPRH\Preconnects();

		$hint_1 = TestUtils::create_hint_array( 'https://test-process-hints.com', 'preconnect' );
		$hint_2 = TestUtils::create_hint_array( 'https://tester.com', 'preconnect', 'font', 'font/woff', 'crossorigin', '' );

		$test_data = array();
		$test_data['hints'] = array( $hint_1, $hint_2 );
		$expected = count( $test_data['hints'] );

		$actual = $preconnects->process_hints( $test_data );
		self::assertEquals( $expected, count( $actual ) );
	}

}
