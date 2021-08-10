<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class PreconnectsTest extends TestCase {

	public static $preconnects;
	
	public function test_start() {
		self::$preconnects = new \PPRH\Preconnects();
	}


	public function test_constructor() {
		$loaded = \add_action( 'wp_loaded', array( self::$preconnects, 'init_controller' ) );
		self::assertTrue( $loaded );
	}

	public function test_check_to_perform_reset() {
		$actual_1 = self::$preconnects->check_to_perform_reset( false, false, false );
		self::assertFalse( $actual_1 );

		$actual_2 = self::$preconnects->check_to_perform_reset( true, false, false );
		self::assertFalse( $actual_2 );

		$actual_3 = self::$preconnects->check_to_perform_reset( false, true, false );
		self::assertFalse( $actual_3 );

		$actual_4 = self::$preconnects->check_to_perform_reset( true, true, false );
		self::assertFalse( $actual_4 );

		$actual_5 = self::$preconnects->check_to_perform_reset( false, false, true );
		self::assertFalse( $actual_5 );

		$actual_6 = self::$preconnects->check_to_perform_reset( true, false, true );
		self::assertTrue( $actual_6 );

		$actual_7 = self::$preconnects->check_to_perform_reset( false, true, true );
		self::assertTrue( $actual_7 );

		$actual_8 = self::$preconnects->check_to_perform_reset( true, true, true );
		self::assertTrue( $actual_8 );
	}

	public function test_initialize() {
		$actual_1 = self::$preconnects->initialize( false, false, null );
		self::assertFalse( $actual_1 );

		$actual_2 = self::$preconnects->initialize( true, false, null );
		self::assertFalse( $actual_2 );

		$actual_3 = self::$preconnects->initialize( false, true, null );
		self::assertTrue( $actual_3 );

		$actual_4 = self::$preconnects->initialize( true, true, null );
		self::assertTrue( $actual_4 );
	}


	public function test_load_ajax_callbacks() {
		$ajax_cb = 'pprh_post_domain_names';
		$callback = array( self::$preconnects, $ajax_cb );
		\remove_action( "wp_ajax_nopriv_$ajax_cb", $callback );

		$wp_ajax_nopriv_added_1 = \has_action( "wp_ajax_nopriv_$ajax_cb", $callback );
		self::assertFalse( $wp_ajax_nopriv_added_1 );

//		$wp_ajax_added_1 = \has_action( "wp_ajax_$ajax_cb", $callback );
//		self::assertFalse( $wp_ajax_added_1 );

		self::$preconnects->load_ajax_callbacks( false );
		$wp_ajax_nopriv_added_2 = \has_action( "wp_ajax_nopriv_$ajax_cb", $callback );
		self::assertFalse( $wp_ajax_nopriv_added_2 );

		$wp_ajax_added_2 = \has_action( "wp_ajax_$ajax_cb", $callback );
		self::assertEquals( 10, $wp_ajax_added_2 );

		self::$preconnects->load_ajax_callbacks( true );
		$wp_ajax_nopriv_added_3 = \has_action( "wp_ajax_nopriv_$ajax_cb", $callback );
		self::assertEquals( 10, $wp_ajax_nopriv_added_3 );

		$wp_ajax_added_3 = \has_action( "wp_ajax_$ajax_cb", $callback );
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

		$actual_object_1 = self::$preconnects->create_js_object( $time );
		self::assertEquals( $expected_arr_1, $actual_object_1 );
	}



	public function test_post_domain_names() {
		$time = time();
		$pprh_data = self::$preconnects->create_js_object( $time );
		$hint_1 = TestUtils::create_hint_array( 'https://fonts.gstaticTest.com', 'preconnect' );
		$hint_2 = TestUtils::create_hint_array( 'https://fonts.gstaticTest2.com', 'preconnect' );

		$actual_1 = self::$preconnects->post_domain_names( $pprh_data );
		self::assertFalse( $actual_1 );

		$actual_2 = self::$preconnects->post_domain_names( $pprh_data );
		self::assertFalse( $actual_2 );

		$pprh_data['hints'] = array( $hint_1, $hint_2 );
		$actual_3 = self::$preconnects->post_domain_names( $pprh_data );
		self::assertTrue( $actual_3 );

		$hint_3 = TestUtils::create_hint_array( '', 'preconnect' );
		$pprh_data['hints'] = array( $hint_1, $hint_3 );
		$actual_4 = self::$preconnects->post_domain_names( $pprh_data );
		self::assertFalse( $actual_4 );

		$pprh_data_5 = array(
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => $time
		);
		$actual_5 = self::$preconnects->post_domain_names( $pprh_data_5 );
		self::assertFalse( $actual_5 );
	}


}
