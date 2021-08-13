<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class PreconnectInitTest extends TestCase {

	public static $preconnect_init;
	
	public function test_start() {
		self::$preconnect_init = new \PPRH\PreconnectInit();
	}

	public function test_entire_preconnects_feature() {
//		$ajax_url = 'https://sphacks.local/wp-admin/admin-ajax.php';
		$orig_allow_unauth = \get_option( 'pprh_preconnect_allow_unauth' );

		$js_object = self::$preconnect_init->create_js_object( time() );
		$pprh_data = array(
			'hints' => array(
				array( 'url' => 'https://fonts.gstatic.com', 'hint_type' => 'preconnect', 'media' => '', 'as_attr' => '', 'type_attr' => '', 'crossorigin' => 'crossorigin' )
			),
			'nonce' => $js_object['nonce'],
		);

		$args = array(
			'body' => array(
				'action'    => 'pprh_post_domain_names',
				'pprh_data' => json_encode( $pprh_data ),
				'nonce'     => $js_object['nonce']
			),
			'timeout'   => 20,
			'sslverify' => false,
		);

		\update_option( 'pprh_preconnect_allow_unauth', 'false' );
//		$config_1 = self::$preconnect_init->set_config( false, false, true, true, null );
//		$load_1 = self::$preconnect_init->initialize_ctrl( $config_1 );
		$response_1 = \wp_remote_post( $js_object['admin_url'], $args );
		$response_body_1  = \PPRH\PRO\UtilsPro::get_api_response_body( $response_1, 'error' );
		self::assertEmpty( $response_body_1 );


		\update_option( 'pprh_preconnect_allow_unauth', 'true' );
//		$config_2 = self::$preconnect_init->set_config( true, false, true, false, null );
//		$load_2 = self::$preconnect_init->initialize_ctrl( $config_2 );
		$response_2 = \wp_remote_post( $js_object['admin_url'], $args );
		$response_body_2  = \PPRH\PRO\UtilsPro::get_api_response_body( $response_2, 'error' );
		self::assertCount( 8, $response_body_2[0]['new_hint'] );
		self::assertTrue( $response_body_2[0]['db_result']['status'] );
		self::assertCount( count( $pprh_data['hints'] ), $response_body_2 );


		\update_option( 'pprh_preconnect_allow_unauth', $orig_allow_unauth );
	}


	public function test_constructor() {
		$loaded = \add_action( 'wp_loaded', array( self::$preconnect_init, 'init_controller' ) );
		self::assertTrue( $loaded );
	}


	public function test_initialize_ctrl() {
		$actual_1 = self::$preconnect_init->initialize_ctrl( false, true, true, null );
		self::assertFalse( $actual_1 );

		$actual_2 = self::$preconnect_init->initialize_ctrl( false, false, true, null );
		self::assertFalse( $actual_2 );

		$actual_3 = self::$preconnect_init->initialize_ctrl( true, true, false, null );
		self::assertTrue( $actual_3 );

		$actual_4 = self::$preconnect_init->initialize_ctrl( true, true, false, null );
		self::assertTrue( $actual_4 );

		$actual_5 = self::$preconnect_init->initialize_ctrl( true, true, false, true );
		self::assertTrue( $actual_5 );

		$actual_6 = self::$preconnect_init->initialize_ctrl( true, false, true, true );
		self::assertFalse( $actual_6 );

		$actual_7 = self::$preconnect_init->initialize_ctrl( true, true, false, false );
		self::assertFalse( $actual_7 );
	}


	public function test_load_ajax_callbacks() {
		$ajax_cb = 'pprh_post_domain_names';
		$callback = array( self::$preconnect_init, $ajax_cb );
		\remove_action( "wp_ajax_nopriv_$ajax_cb", $callback );

		$wp_ajax_nopriv_added_1 = \has_action( "wp_ajax_nopriv_$ajax_cb", $callback );
		self::assertFalse( $wp_ajax_nopriv_added_1 );

//		$wp_ajax_added_1 = \has_action( "wp_ajax_$ajax_cb", $callback );
//		self::assertFalse( $wp_ajax_added_1 );

		self::$preconnect_init->load_ajax_callbacks( false );
		$wp_ajax_nopriv_added_2 = \has_action( "wp_ajax_nopriv_$ajax_cb", $callback );
		self::assertFalse( $wp_ajax_nopriv_added_2 );

		$wp_ajax_added_2 = \has_action( "wp_ajax_$ajax_cb", $callback );
		self::assertEquals( 10, $wp_ajax_added_2 );

		self::$preconnect_init->load_ajax_callbacks( true );
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
			'start_time' => $time
		);

		$actual_object_1 = self::$preconnect_init->create_js_object( $time );
		self::assertEquals( $expected_arr_1, $actual_object_1 );
	}

}
