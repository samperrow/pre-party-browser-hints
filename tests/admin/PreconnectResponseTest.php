<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PreconnectResponseTest extends TestCase {

	public static $preconnect_response;

	public function test_start() {
		self::$preconnect_response = new \PPRH\PreconnectResponse();
	}

	public function test_post_domain_names() {
		$time = time();
		$pprh_data = self::$preconnect_response->create_js_object( $time );
		$hint_1 = TestUtils::create_hint_array( 'https://fonts.gstaticTest.com', 'preconnect' );
		$hint_2 = TestUtils::create_hint_array( 'https://fonts.gstaticTest2.com', 'preconnect' );

		$actual_1 = self::$preconnect_response->post_domain_names( $pprh_data );
		self::assertEmpty( $actual_1 );

		$pprh_data['hints'] = array( $hint_1, $hint_2 );
		$actual_3 = self::$preconnect_response->post_domain_names( $pprh_data );
		self::assertCount( 2, $actual_3 );

		$hint_3 = TestUtils::create_hint_array( '', 'preconnect' );
		$pprh_data['hints'] = array( $hint_1, $hint_3 );
		$actual_4 = self::$preconnect_response->post_domain_names( $pprh_data );
		self::assertCount( 1, $actual_4 );

		$pprh_data_5 = array( 'admin_url' => $pprh_data['admin_url'], 'start_time' => $pprh_data['start_time'] );
		$actual_5 = self::$preconnect_response->post_domain_names( $pprh_data_5 );
		self::assertEmpty( $actual_5 );
	}

}
