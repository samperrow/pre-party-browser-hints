<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ClientAjaxResponseTest extends TestCase {

	public static $hint_type;
	public static $client_ajax_response_preconnect;
	public static $client_ajax_response_preload;
	
	public function test_start() {
		self::$hint_type = 'preconnect';
		self::$client_ajax_response_preconnect = new \PPRH\ClientAjaxResponse( self::$hint_type );
		self::$client_ajax_response_preload    = new \PPRH\ClientAjaxResponse( 'preload' );
	}

	public function test_post_domain_names() {
		$time = time();
		$pprh_data = self::$client_ajax_response_preconnect->create_js_object( $time, self::$hint_type );
		$hint_preconnect_1 = TestUtils::create_hint_array( 'https://fonts.gstaticTest.com', self::$hint_type );
		$hint_preconnect_2 = TestUtils::create_hint_array( 'https://fonts.gstaticTest2.com', self::$hint_type );

		$actual_1 = self::$client_ajax_response_preconnect->post_domain_names( $pprh_data );
		self::assertEmpty( $actual_1 );


		$pprh_data['hints'] = array( $hint_preconnect_1, $hint_preconnect_2 );
		$actual_2 = self::$client_ajax_response_preconnect->post_domain_names( $pprh_data );
		self::assertCount( 2, $actual_2 );

		$hint_3 = TestUtils::create_hint_array( '', self::$hint_type );
		$pprh_data['hints'] = array( $hint_preconnect_1, $hint_3 );
		$actual_3 = self::$client_ajax_response_preconnect->post_domain_names( $pprh_data );
		self::assertCount( 1, $actual_3 );

		$pprh_data_5 = array( 'admin_url' => $pprh_data['admin_url'], 'start_time' => $pprh_data['start_time'] );
		$actual_4 = self::$client_ajax_response_preconnect->post_domain_names( $pprh_data_5 );
		self::assertEmpty( $actual_4 );


		/**
		 * PRELOADS
		 */
		$hint_preload_1 = TestUtils::create_hint_array( 'https://example.com/assets/styles.css', 'preload' );
		$hint_preload_2 = TestUtils::create_hint_array( 'https://example.com/assets/scripts/js.js', 'preload' );

		$pprh_data['hints'] = array( $hint_preload_1, $hint_preload_2 );
		$actual_preload_1 = self::$client_ajax_response_preload->post_domain_names( $pprh_data );
		self::assertCount( 2, $actual_preload_1 );


	}

}
