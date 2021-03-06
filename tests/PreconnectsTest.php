<?php

declare(strict_types=1);
<<<<<<< HEAD
use PHPUnit\Framework\TestCase;

class PreconnectsTest extends TestCase {

	public function test_constructor():void {
		$preconnects = new \PPRH\Preconnects();
		$loaded = has_action( 'wp_loaded', array($preconnects, 'init_controller') );

		$reset_data = array(
			'autoload'        => get_option( 'pprh_preconnect_autoload' ),
			'allow_unauth'    => get_option( 'pprh_preconnect_allow_unauth' ),
			'preconnects_set' => get_option( 'pprh_preconnect_set' ),
		);

		$this->assertEquals( true, $loaded );
		$this->assertEquals( $reset_data, $preconnects->config['reset_data'] );
	}

	public function test_entire_preconnect_class_free(): void {
		$preconnects = new \PPRH\Preconnects();

		$config_1 = $this->util_create_free_config_arr( 'true', 'true', 'true' );
		$actual_1 = $preconnects->initialize( $config_1 );
		$this->assertEquals( false, $actual_1 );

		$config_2 = $this->util_create_free_config_arr( 'true', 'true', 'false' );
		$actual_2 = $preconnects->initialize( $config_2 );
		$this->assertEquals( true, $actual_2 );

		$config_3 = $this->util_create_free_config_arr( 'false', 'true', 'true' );
		$actual_3 = $preconnects->initialize( $config_3 );
		$this->assertEquals( false, $actual_3 );

		$config_4 = $this->util_create_free_config_arr( 'false', 'true', 'false' );
		$actual_4 = $preconnects->initialize( $config_4 );
		$this->assertEquals( false, $actual_4 );
	}

	public function util_create_free_config_arr( $autoload, $allow_unauth, $preconnects_set )  {
		return array(
			'reset_data' => array(
				'autoload' => $autoload,
				'allow_unauth' => $allow_unauth,
				'preconnects_set' => $preconnects_set,
				'reset_pro' => null
			)
		);
	}

//	public function util_create_pro_config_arr( $args ) {
//		$free_config = $this->util_create_free_config_arr( $args['autoload'], $args['allow_unauth'], $args['preconnects_set'] );
//
//		$arr = array(
//			'reset_data' => $free_config
//		);
//
//		$arr['reset_pro'] = array(
//			'post_id'       => $args['post_id'],
//			'reset_globals' => $args['reset_globals'],
//			'reset_home'    => $args['reset_home'],
//			'post_reset'    => $args['post_reset']
//		);
//
//		return $arr;
//	}

	public function test_entire_preconnect_class_pro(): void {
		$preconnects = new \PPRH\Preconnects();
		$autoload = 'true';
		$allow_unauth = 'true';
		$preconnects_set = 'true';
		$post_id = '2326';
		$reset_globals = 'false';
		$do_reset = false;

		update_post_meta( $post_id, 'pprh_preconnect_post_do_reset', 'false' );

		$config = array(
			'is_admin' => WP_ADMIN,
			'reset_data' => array(
				'autoload'        => $autoload,
				'allow_unauth'    => $allow_unauth,
				'preconnects_set' => $preconnects_set,
				'reset_pro' => array(
					'post_id'       => $post_id,
					'reset_globals' => $reset_globals,
					'perform_reset' => $do_reset,
				)
			)
		);


		$config['reset_data']['reset_pro']['perform_reset'] = false;

		$actual_1 = $preconnects->initialize( $config );
		$this->assertEquals( false, $actual_1 );

		update_post_meta( $post_id, 'pprh_preconnect_post_do_reset', 'true' );
		$config['reset_data']['reset_pro']['perform_reset'] = true;
		$actual_2 = $preconnects->initialize( $config );

		$this->assertEquals( true, $actual_2 );

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

		$actual_1 = $preconnects_1->init_controller();
		$expected_1 = ( 'true' === get_option( 'pprh_preconnect_pro_reset_globals' ) );

		$expected_config = array(
			'is_admin' => WP_ADMIN,
			'reset_data' => array(
				'autoload'        => get_option( 'pprh_preconnect_autoload' ),
				'allow_unauth'    => get_option( 'pprh_preconnect_allow_unauth' ),
				'preconnects_set' => get_option( 'pprh_preconnect_set' ),
				'reset_pro' => apply_filters( 'pprh_preconnects_do_reset_init', null )
			)
		);

		$this->assertEquals($expected_1, $actual_1);
		$this->assertEquals($expected_config, $preconnects_1->config);

	}

	public function eval_free_initialize() {
		$preconnects_1 = new \PPRH\Preconnects();
		$preconnects_1->config['reset_data']['autoload'] = 'false';
		$actual_1 = $preconnects_1->initialize($preconnects_1->config);

		$preconnects_2 = new \PPRH\Preconnects();
		$preconnects_2->config['reset_data']['autoload'] = 'true';
		$preconnects_2->config['reset_data']['preconnects_set'] = 'true';
		$actual_2 = $preconnects_2->initialize($preconnects_2->config);

		$preconnects_3 = new \PPRH\Preconnects();
		$preconnects_3->config['reset_data']['autoload'] = 'true';
		$preconnects_3->config['reset_data']['preconnects_set'] = 'false';
		$actual_3 = $preconnects_3->initialize($preconnects_3->config);

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

		$actual_1 = $preconnects->check_to_enqueue_scripts(true);
		$actual_2 = $preconnects->check_to_enqueue_scripts(false);

		$scripts_enqueued = has_action( 'wp_enqueue_scripts', array($preconnects, 'enqueue_scripts') );
		$this->assertEquals(true, $scripts_enqueued);

		$this->assertEquals(true, $actual_1);
		$this->assertEquals(false, $actual_2);
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
		$actual_reset_pro = apply_filters('pprh_preconnects_do_reset_init', null);

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


//	public function test_enqueue_scripts() {
//		global $wp_scripts;
//		$preconnects_1 = new \PPRH\Preconnects();
//		$preconnects_1->is_admin = true;
//		$actual_1 = $preconnects_1->enqueue_scripts();
//		$this->assertEquals( false, $actual_1);
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
//		$this->assertEquals( $expected_scripts, $actual_scripts);
//	}

	public function test_create_js_object() {
		if ( WP_ADMIN ) return;

		$preconnects = new \PPRH\Preconnects();

		$expected_arr_1 = array(
			'hints'         => array(),
			'nonce'         => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'     => admin_url() . 'admin-ajax.php',
			'start_time'    => time(),
			'hint_type' => 'preconnect'
		);


		if ( \PPRH\Utils::pprh_is_plugin_active() ) {
			$expected_arr_1['post_id'] = '';
			$expected_arr_1['reset_globals'] = 'false';
			$preconnects->config['reset_data']['reset_pro'] = apply_filters( 'pprh_preconnects_do_reset_init', null );
		}

		$actual_object_1 = $preconnects->create_js_object();
		$this->assertEquals( $expected_arr_1, $actual_object_1 );


		$preconnects->config['reset_data']['reset_pro'] = array(
			'post_id'  => '100',
		);

		$expected_object_2 = apply_filters( 'pprh_preconnects_append_hint_object', $expected_arr_1, $preconnects->config['reset_data']['reset_pro'] );
		$actual_object_2 = $preconnects->create_js_object();
		$this->assertEquals( $expected_object_2, $actual_object_2 );
	}

	public function test_allow_unauth_users():void {
		$preconnects = new \PPRH\Preconnects();

		$expected_1 = $preconnects->allow_unauth_users( 'true', true );
		$expected_2 = $preconnects->allow_unauth_users( 'true', false );
		$expected_3 = $preconnects->allow_unauth_users( 'false', true );
		$expected_4 = $preconnects->allow_unauth_users( 'false', false );

		$this->assertEquals( true, $expected_1 );
		$this->assertEquals( true, $expected_2 );
		$this->assertEquals( true, $expected_3 );
		$this->assertEquals( false, $expected_4 );
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
		if ( ! wp_doing_ajax() ) {
			return;
		}
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
		if ( WP_ADMIN || ! wp_doing_ajax() ) {
			return;
		}

//		$dao = new \PPRH\DAO();
		$preconnects = new \PPRH\Preconnects();
		$expected_nonce = TestUtils::create_nonce( 'pprh_ajax_nonce' );

		$url1 = 'https://fonts.gstaticTest.com';
		$url2 = 'https://fonts.gstaticTest2.net';

		$test_data = $preconnects->create_js_object();
		$test_data['hints'] = array( $url1, $url2 );

		$_POST['pprh_data'] = json_encode( $test_data );
		$_REQUEST['_ajax_nonce'] = $expected_nonce;
		$_POST['action'] = 'pprh_post_domain_names';

		$actual = $preconnects->pprh_post_domain_names();

		$this->assertEquals( true, $actual );

	}

//	public function test_do_ajax_callback():void {
//		$preconnects = new \PPRH\Preconnects();
//
//		$test_data = $preconnects->create_js_object();
//		$test_data['hints'] = array( 'testtest.com' );
//
//		$_POST['pprh_data'] = json_encode( $test_data );
//
//		$preconnects->config['reset_data']['allow_unauth'] = 'false';
//
//		$actual_1 = $preconnects->do_ajax_callback();
//
//		$preconnects->config['reset_data']['allow_unauth'] = 'true';
//		$actual_2 = $preconnects->do_ajax_callback();
//
//		$this->assertEquals( false, $actual_1 );
//		$this->assertEquals( true, $actual_2 );
//	}

	public function test_process_hints() {
		$preconnects = new \PPRH\Preconnects();

		$hint_1 = TestUtils::create_hint_array( 'https://test-process-hints.com', 'preconnect' );
		$hint_2 = TestUtils::create_hint_array( 'https://test-process-hints.net', 'preconnect' );

		$test_data = $preconnects->create_js_object();
		$test_data['hints'] = array( $hint_1, $hint_2 );

		$actual_arr = $preconnects->process_hints( $test_data );

		$this->assertEquals( count( $test_data['hints'] ), count( $actual_arr ) );
	}

}
=======

use PPRH\Preconnects;
use PHPUnit\Framework\TestCase;

final class PreconnectsTest extends TestCase {

//	public function __construct() {}

	public function test_PreconnectDoesNotLoad (): void {
		update_option('pprh_preconnect_autoload', 'true');
		update_option('pprh_preconnect_set', 'true');

		$prec = new \PPRH\Preconnects();
		$wp_loaded = has_action( 'wp_loaded', array( $prec, 'initialize' ) );
		$this->assertEquals(false, $wp_loaded);
	}

	public function test_PreconnectsDoesLoad (): void {
		update_option('pprh_preconnect_autoload', 'true');
		update_option('pprh_preconnect_set', 'false');

		$prec = new \PPRH\Preconnects();
		$wp_loaded = has_action( 'wp_loaded', array( $prec, 'initialize' ) );
		$this->assertEquals(true, $wp_loaded);
	}

	public function test_load_preconnects_only_for_logged_in_users(): void {
		update_option('pprh_preconnect_autoload', 'true');
		update_option('pprh_preconnect_set', 'false');
		update_option( 'pprh_preconnect_allow_unauth', 'false' );

		remove_action( 'wp_ajax_pprh_post_domain_names', array(Preconnects::class, 'pprh_post_domain_names' ) );
		remove_action( 'wp_ajax_nopriv_pprh_post_domain_names', array( Preconnects::class, 'pprh_post_domain_names' ) );

		$prec = new \PPRH\Preconnects();
		$prec->initialize();

		$loaded_ajax = has_action( 'wp_ajax_pprh_post_domain_names' );
		$stop_load_ajax_for_all = has_action( 'wp_ajax_nopriv_pprh_post_domain_names' );

		$this->assertEquals(true, $loaded_ajax);
		$this->assertEquals(false, $stop_load_ajax_for_all);

		remove_action( 'wp_ajax_pprh_post_domain_names', array(Preconnects::class, 'pprh_post_domain_names' ) );
		remove_action( 'wp_ajax_nopriv_pprh_post_domain_names', array(Preconnects::class, 'pprh_post_domain_names' ) );
	}

//	public function test_load_preconnects_for_all_users(): void {
//		update_option('pprh_preconnect_autoload', 'true');
//		update_option('pprh_preconnect_set', 'false');
//		update_option( 'pprh_preconnect_allow_unauth', 'true' );
//
//		remove_action( 'wp_ajax_pprh_post_domain_names', array(Preconnects::class, 'pprh_post_domain_names' ) );
//		remove_action( 'wp_ajax_nopriv_pprh_post_domain_names', array( Preconnects::class, 'pprh_post_domain_names' ) );
//
//		$prec = new \PPRH\Preconnects();
//		$prec->initialize();
//
//		$loaded_ajax = has_action( 'wp_ajax_pprh_post_domain_names' );
//		$stop_load_ajax_for_all = has_action( 'wp_ajax_nopriv_pprh_post_domain_names' );
//
//		$this->assertEquals(true, $loaded_ajax);
//		$this->assertEquals(true, $stop_load_ajax_for_all);
//
//		remove_action( 'wp_ajax_pprh_post_domain_names', array(Preconnects::class, 'pprh_post_domain_names' ) );
//		remove_action( 'wp_ajax_nopriv_pprh_post_domain_names', array( Preconnects::class, 'pprh_post_domain_names' ) );
//	}



//	public function test_pprh_post_domain_names(): void {}


//	public function test_create_hint(): void {}

//	public function test_update_options(): void {}

}
>>>>>>> master
