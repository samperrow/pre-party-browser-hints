<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CreateHintsTest extends TestCase {

	public static $create_hints;

	/**
	 * @before Class
	 */
	public function test_start() {
		self::$create_hints = new \PPRH\CreateHints();
	}

	public function test_new_hint_ctrl() {
		$dummy_hint = \PPRH\Utils::create_raw_hint( 'https://free-hint.com', 'dns-prefetch', '', '', '', '' );

		$dummy_hint['op_code'] = 0;
		$actual_1 = self::$create_hints->new_hint_ctrl( $dummy_hint );
		self::assertCount( 8, $actual_1 );

		$dummy_hint['op_code'] = 1;
		$actual_2 = self::$create_hints->create_pprh_hint( $dummy_hint );
		$expected_2 = self::$create_hints->new_hint_ctrl( $dummy_hint );
		self::assertEquals( $expected_2, $actual_2 );

		$dummy_hint['op_code'] = 1;
		$dummy_hint['hint_ids'] = '';
		$actual_3 = self::$create_hints->create_pprh_hint( $dummy_hint );
		$expected_3 = self::$create_hints->new_hint_ctrl( $dummy_hint );
		self::assertEquals( $expected_3, $actual_3 );

		$raw_data_4 = \PPRH\Utils::create_raw_hint( '', '' );
		$actual_4 = self::$create_hints->create_pprh_hint( $raw_data_4 );
		self::assertFalse( $actual_4 );
	}

	public function test_new_hint_controller() {
		$hint_1 = \PPRH\Utils::create_raw_hint( 'https://test.com', 'dns-prefetch', '', '', '', 'screen' );
		$dup_hints_1 = array( $hint_1 );
		$candidate_hint_1 = $hint_1;
		$actual_1 = self::$create_hints->new_hint_controller( $candidate_hint_1, $dup_hints_1 );
		self::assertEmpty( $actual_1 );

		$candidate_hint_2 = \PPRH\Utils::create_raw_hint( 'https://test2.com', 'dns-prefetch', '', '', '', 'screen' );
		$dup_hints_2 = array();
		$actual_2 = self::$create_hints->new_hint_controller( $candidate_hint_2, $dup_hints_2 );
		self::assertNotEmpty( $actual_2 );

		$hint_3 = \PPRH\Utils::create_raw_hint( 'https://asdf.com', 'preconnect', '', '', 'crossorigin', 'screen' );
		$dup_hints_3 = array( $hint_3 );
		$actual_3 = self::$create_hints->new_hint_controller( $hint_3, $dup_hints_3 );
		self::assertEmpty( $actual_3 );
	}




//	public function test_handle_duplicate_hints() {
//		$test_hint_1 = \PPRH\Utils::create_raw_hint( 'https://test.com', 'preconnect', '', '', '', '' );
//		$dup_hints = array( $test_hint_1 );
//		$candidate_hint = \PPRH\Utils::create_raw_hint( 'https://test.com', 'preconnect', '', '', '', '' );
//		$actual_1 = self::$create_hints->handle_duplicate_hints( $dup_hints, $candidate_hint );
//		self::assertFalse( $actual_1 );
//	}


}
