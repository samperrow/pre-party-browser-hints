<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class ClientAjaxInitTest extends TestCase {

	public static $client_ajax_init_preconnect;

	public function test_start() {
		self::$client_ajax_init_preconnect = new \PPRH\ClientAjaxInit( 'preconnect', array() );
	}

	public function test_entire_preconnects_feature() {
		$orig_preconnect_allow_unauth = \get_option( 'pprh_preconnect_allow_unauth' );

		$js_object = self::$client_ajax_init_preconnect->create_js_object( time(), 'preconnect' );
		$pprh_data = array(
			'hints' => array(
				array( 'url' => 'https://fonts.gstatic.comTest', 'hint_type' => 'preconnect', 'media' => '', 'as_attr' => '', 'type_attr' => '', 'crossorigin' => 'crossorigin' )
			),
			'nonce' => $js_object['nonce'],
		);

		$args = array(
			'body' => array(
				'action'    => self::$client_ajax_init_preconnect->callback,
				'pprh_data' => json_encode( $pprh_data ),
				'nonce'     => $js_object['nonce']
			),
			'timeout'   => 20,
			'sslverify' => false,
		);

		\update_option( 'pprh_preconnect_allow_unauth', 'false' );
		$response_1 = \wp_remote_post( $js_object['admin_url'], $args );
		$response_body_1  = \PPRH\Utils::get_api_response_body( $response_1, 'error' );
		self::assertEmpty( $response_body_1 );


		\update_option( 'pprh_preconnect_allow_unauth', 'true' );
		$pprh_data['hints'][0]['url'] = 'https://fonts.gstatic.comTest2';
		$args['body']['pprh_data'] = json_encode( $pprh_data );
		$response_2 = \wp_remote_post( $js_object['admin_url'], $args );
		$response_body_2  = \PPRH\Utils::get_api_response_body( $response_2, 'error' );
		self::assertGreaterThan( 8, $response_body_2[0]['new_hint'] );
		self::assertTrue( $response_body_2[0]['db_result']['status'] );
		self::assertCount( count( $pprh_data['hints'] ), $response_body_2 );
		$hint_id = (string) $response_body_2[0]['new_hint']['id'];

		\PPRH\DAO::delete_hint( $hint_id );

		\update_option( 'pprh_preconnect_allow_unauth', $orig_preconnect_allow_unauth );
	}


	public function test_constructor() {
		$loaded = \add_action( 'wp_loaded', array( self::$client_ajax_init_preconnect, 'init_controller' ) );
		self::assertTrue( $loaded );
	}


	public function test_initialize_ctrl() {
		$actual_1 = self::$client_ajax_init_preconnect->initialize_ctrl( false, true, true, null );
		self::assertFalse( $actual_1 );

		$actual_2 = self::$client_ajax_init_preconnect->initialize_ctrl( false, false, true, null );
		self::assertFalse( $actual_2 );

//		$actual_3 = self::$client_ajax_init_preconnect->initialize_ctrl( true, true, false, 'preload' );
//		self::assertTrue( $actual_3 );

		$actual_4 = self::$client_ajax_init_preconnect->initialize_ctrl( true, true, false, false );
		self::assertFalse( $actual_4 );

		$actual_5 = self::$client_ajax_init_preconnect->initialize_ctrl( true, true, false, true );
		self::assertTrue( $actual_5 );

		$actual_6 = self::$client_ajax_init_preconnect->initialize_ctrl( true, false, true, true );
		self::assertFalse( $actual_6 );

		$actual_7 = self::$client_ajax_init_preconnect->initialize_ctrl( true, true, false, false );
		self::assertFalse( $actual_7 );
	}


	public function test_load_ajax_callbacks() {
		$ajax_cb = 'pprh_preconnect_callback';
		$callback = array( self::$client_ajax_init_preconnect, $ajax_cb );
		\remove_action( "wp_ajax_nopriv_$ajax_cb", $callback );

		$wp_ajax_nopriv_added_1 = \has_action( "wp_ajax_nopriv_$ajax_cb", $callback );
		self::assertFalse( $wp_ajax_nopriv_added_1 );

//		$wp_ajax_added_1 = \has_action( "wp_ajax_$ajax_cb", $callback );
//		self::assertFalse( $wp_ajax_added_1 );

		self::$client_ajax_init_preconnect->load_ajax_callbacks( false );
		$wp_ajax_nopriv_added_2 = \has_action( "wp_ajax_nopriv_$ajax_cb", $callback );
		self::assertFalse( $wp_ajax_nopriv_added_2 );

		$wp_ajax_added_2 = \has_action( "wp_ajax_$ajax_cb", $callback );
		self::assertEquals( 10, $wp_ajax_added_2 );

		self::$client_ajax_init_preconnect->load_ajax_callbacks( true );
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
			'timeout'    => PPRH_IN_DEV ? 1000 : 7000,
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => $time
		);

		$actual_object_1 = self::$client_ajax_init_preconnect->create_js_object( $time, 'preconnect' );
		self::assertEquals( $expected_arr_1, $actual_object_1 );
	}

}
