<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CreateHintsTest extends TestCase {

	public $create_hints;

	/**
	 * @before
	 */
	public function test_start():void {
		$this->create_hints = new \PPRH\CreateHints();
	}

	/**
	 * @covers \PPRH\CreateHints::create_hint
	 */
	public function test_create_hint(): void {
		$url_1 = 'https://sphacks.local/wp-content/themes/sphacks/images/icons/newspaper.woff?19';

		$test1 = TestUtils::create_hint_array( 'https://www.espn.com', 'dns-prefetch' );
		$test2 = TestUtils::create_hint_array( 'ht<tps://www.e>\'sp"n.com', 'dns-prefetch', 'font', 'font/woff2', 'crossorigin', '(max-width: 600px)' );
		$test3 = TestUtils::create_hint_array( '//espn.com', 'dns-prefetch' );
		$test_4 = TestUtils::create_hint_array( '//espn.com', '' );
		$test_5 = TestUtils::create_hint_array( $url_1, 'preload', 'font', 'font/woff', 'crossorigin', '' );

		$expected_1 = $this->create_hints->create_hint($test1);
		self::assertEquals($expected_1, $test1);

		$actual_hint_2 = $this->create_hints->create_hint($test2);
		$test2['url'] = 'https://www.espn.com';
		self::assertEquals($test2, $actual_hint_2);

		$test_hint3 = $this->create_hints->create_hint($test3);
		self::assertEquals($test_hint3, $test3);

		$test_hint_4 = $this->create_hints->create_hint($test_4);
		self::assertEquals(false, $test_hint_4);

		$data1 = TestUtils::create_hint_array( '', 'dns-prefetch' );
		$bool1 = $this->create_hints->create_hint($data1);
		self::assertEquals(false, $bool1);

		$actual_6 = $this->create_hints->create_hint( $test_5 );
		self::assertEquals( $test_5, $actual_6);
	}



	public function test_new_hint_ctrl():void {
		$dummy_hint = TestUtils::create_hint_array( 'https://free-hint.com', 'dns-prefetch', '', '', '', 'screen' );
		$dummy_hint['op_code'] = 0;
		$actual = $this->create_hints->create_hint( $dummy_hint );
		$expected = $this->create_hints->new_hint_ctrl( $dummy_hint );
		self::assertEquals( $expected, $actual );


		$dummy_hint['op_code'] = 1;
		$actual_2 = $this->create_hints->create_hint( $dummy_hint );
		$expected_2 = $this->create_hints->new_hint_ctrl( $dummy_hint );
		self::assertEquals( $expected_2, $actual_2 );
	}

	public function test_new_hint_controller() {
		$test_hint_1 = TestUtils::create_hint_array( 'https://test.com', 'dns-prefetch', '', '', '', 'screen' );
		$candidate_hint_1 = TestUtils::create_hint_array( 'https://test.com', 'dns-prefetch', '', '', '', 'screen' );
		$dup_hints_1 = array( $test_hint_1 );
		$actual_1 = $this->create_hints->new_hint_controller( 0, $candidate_hint_1, $dup_hints_1 );
		self::assertEquals( true, is_object( $actual_1 ) );

		$candidate_hint_2 = TestUtils::create_hint_array( 'https://test2.com', 'dns-prefetch', '', '', '', 'screen' );
		$dup_hints_2 = array();
		$actual_2 = $this->create_hints->new_hint_controller( 0, $candidate_hint_2, $dup_hints_2 );
		self::assertEquals( $candidate_hint_2, $actual_2 );
	}

	public function test_create_pprh_hint_fail():void {
		$raw_data1 = TestUtils::create_hint_array( '', '' );
		$actual = $this->create_hints->create_hint( $raw_data1 );
		self::assertEquals( false, $actual );
	}




	public function test_handle_duplicate_hints():void {
		$test_hint_1 = TestUtils::create_hint_array( 'https://test.com', 'preconnect', '', '', '', '' );
		$dup_hints = array( $test_hint_1 );
		$candidate_hint = TestUtils::create_hint_array( 'https://test.com', 'preconnect', '', '', '', '' );
		$actual_1 = $this->create_hints->handle_duplicate_hints( $dup_hints, $candidate_hint );
		self::assertEquals( false, $actual_1 );
	}


}