<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LoadClientTest extends TestCase {

	public static $load_client;

	public function test_start() {
		self::$load_client = new \PPRH\LoadClient();
	}


	public function test_verify_to_load_fp() {
		self::$load_client = new \PPRH\LoadClient();

		$action = \has_action( 'wp_enqueue_scripts', array( self::$load_client, 'load_flying_pages' ) );
		self::assertFalse( $action );

		$actual_1 = self::$load_client->verify_to_load_fp(true, false);
		self::assertTrue(  $actual_1 );

		$actual_2 = self::$load_client->verify_to_load_fp(true, true);
		self::assertFalse( $actual_2 );

		$actual_3 = self::$load_client->verify_to_load_fp(false, true);
		self::assertFalse( $actual_3 );

		$actual_4 = self::$load_client->verify_to_load_fp(false, false);
		self::assertFalse( $actual_4 );
	}


}
