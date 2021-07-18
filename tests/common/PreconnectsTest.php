<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class PreconnectsTest extends TestCase {

	public $preconnects;

	/**
	 * @before
	 */
	public function test_start() {
		$this->preconnects = new \PPRH\Preconnects();
	}

	public static function create_config( $allow_unauth, $is_user_logged_in, $preconnects_set, $reset_pro = null ) {
		return array(
			'allow_unauth_opt'       => $allow_unauth,
			'is_user_logged_in'      => $is_user_logged_in,
			'preconnects_set_option' => $preconnects_set,
			'reset_pro'              => $reset_pro
		);
	}


	public function test_constructor() {
		$loaded = \add_action( 'wp_loaded', array( $this->preconnects, 'init_controller' ) );
		self::assertEquals( true, $loaded );
	}

	public function test_initialize() {
		$config_1 = self::create_config( true, true, true, false );
		$actual_1 = $this->preconnects->initialize( $config_1 );
		self::assertEquals( false, $actual_1 );

		$config_2 = self::create_config( false, true, false, false );
		$actual_2 = $this->preconnects->initialize( $config_2);
		self::assertEquals( true, $actual_2 );

		$config_3 = self::create_config( false, true, false, false );
		$actual_3 = $this->preconnects->initialize( $config_3 );
		self::assertEquals( true, $actual_3 );

		$config_4 = self::create_config( true, false, true, false );
		$actual_4 = $this->preconnects->initialize( $config_4 );
		self::assertEquals( false, $actual_4 );

		$config_5 = self::create_config( false, false, false, false );
		$actual_5 = $this->preconnects->initialize( $config_5 );
		self::assertEquals( false, $actual_5 );

		$config_6 = self::create_config( false, false, true, false );
		$actual_6 = $this->preconnects->initialize( $config_6 );
		self::assertEquals( false, $actual_6 );

		$config_7 = self::create_config( true, false, false,false );
		$actual_7 = $this->preconnects->initialize( $config_7 );
		self::assertEquals( true, $actual_7 );
	}


	public function test_check_to_perform_reset() {
		$actual_1 = $this->preconnects->check_to_perform_reset( true, false );
		self::assertFalse( $actual_1 );

		$actual_2 = $this->preconnects->check_to_perform_reset( false, false );
		self::assertTrue( $actual_2 );

		$actual_3 = $this->preconnects->check_to_perform_reset( false, false );
		self::assertTrue( $actual_3 );
	}


	public function test_load_ajax_callbacks() {
		$ajax_cb = 'pprh_post_domain_names';

		$wp_ajax_nopriv_added_1 = \has_action( "wp_ajax_nopriv_$ajax_cb", array( $this->preconnects, $ajax_cb ) );
		self::assertFalse($wp_ajax_nopriv_added_1);

		$wp_ajax_added_1 = \has_action( "wp_ajax_$ajax_cb", array( $this->preconnects, $ajax_cb ) );
		self::assertFalse( $wp_ajax_added_1 );

		$this->preconnects->load_ajax_callbacks( false );
		$wp_ajax_nopriv_added_2 = \has_action( "wp_ajax_nopriv_$ajax_cb", array( $this->preconnects, $ajax_cb ) );
		self::assertFalse( $wp_ajax_nopriv_added_2 );

		$wp_ajax_added_2 = \has_action( "wp_ajax_$ajax_cb", array( $this->preconnects, $ajax_cb ) );
		self::assertEquals( 10, $wp_ajax_added_2 );

		$this->preconnects->load_ajax_callbacks( true );
		$wp_ajax_nopriv_added_3 = \has_action( "wp_ajax_nopriv_$ajax_cb", array( $this->preconnects, $ajax_cb ) );
		self::assertEquals( 10, $wp_ajax_nopriv_added_3 );

		$wp_ajax_added_3 = \has_action( "wp_ajax_$ajax_cb", array( $this->preconnects, $ajax_cb ) );
		self::assertEquals( 10, $wp_ajax_added_3 );
	}


	public function test_create_js_object() {
		$time = time();
		$expected_arr_1 = array(
			'hints'      => array(),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => $time,
		);

		$actual_object_1 = $this->preconnects->create_js_object( $time );
		self::assertEquals( $expected_arr_1, $actual_object_1 );
	}


	public function test_allow_user() {
		$expected_1 = $this->preconnects->allow_user( true, true );
		self::assertTrue( $expected_1 );

		$expected_2 = $this->preconnects->allow_user( true, false );
		self::assertTrue( $expected_2 );

		$expected_3 = $this->preconnects->allow_user( false, true );
		self::assertTrue( $expected_3 );

		$expected_4 = $this->preconnects->allow_user( false, false );
		self::assertFalse( $expected_4 );
	}


	public function test_post_domain_names() {
		$time = time();
		$pprh_data = $this->preconnects->create_js_object( $time );
		$hint_1 = TestUtils::create_hint_array( 'https://fonts.gstaticTest.com', 'preconnect' );
		$hint_2 = TestUtils::create_hint_array( 'https://fonts.gstaticTest2.com', 'preconnect' );

		$config_1 = array( 'allow_unauth_opt' => false, 'is_user_logged_in' => false );
		$actual_1 = $this->preconnects->post_domain_names( $pprh_data, $config_1 );
		self::assertFalse( $actual_1 );

		$config_2 = array( 'allow_unauth_opt' => true, 'is_user_logged_in' => true );
		$actual_2 = $this->preconnects->post_domain_names( $pprh_data, $config_2 );
		self::assertFalse( $actual_2 );

		$config_3 = array( 'allow_unauth_opt' => true, 'is_user_logged_in' => false );
		$pprh_data['hints'] = array( $hint_1, $hint_2 );
		$actual_3 = $this->preconnects->post_domain_names( $pprh_data, $config_3 );
		self::assertTrue( $actual_3 );

		$config_4 = array( 'allow_unauth_opt' => false, 'is_user_logged_in' => true );
		$hint_3 = TestUtils::create_hint_array( '', 'preconnect' );
		$pprh_data['hints'] = array( $hint_1, $hint_3 );
		$actual_4 = $this->preconnects->post_domain_names( $pprh_data, $config_4 );
		self::assertFalse( $actual_4 );

		$config_5 = array( 'allow_unauth_opt' => false, 'is_user_logged_in' => true );
		$pprh_data_5 = array(
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => $time
		);
		$actual_5 = $this->preconnects->post_domain_names( $pprh_data_5, $config_5 );
		self::assertFalse( $actual_5 );
	}


}
