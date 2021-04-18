<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CreateHintsTest extends TestCase {



	public function test_create_hint(): void {
		$create_hints = new \PPRH\CreateHints();
		$url_1 = 'https://sphacks.local/wp-content/themes/sphacks/images/icons/newspaper.woff?19';

		$test1 = TestUtils::create_hint_array( 'https://www.espn.com', 'dns-prefetch' );
		$test2 = TestUtils::create_hint_array( 'ht<tps://www.e>\'sp"n.com', 'dns-prefetch', 'font', 'font/woff2', 'crossorigin', '(max-width: 600px)' );
		$test3 = TestUtils::create_hint_array( '//espn.com', 'dns-prefetch' );
		$test_4 = TestUtils::create_hint_array( '//espn.com', '' );
		$test_5 = TestUtils::create_hint_array( $url_1, 'preload', 'font', 'font/woff', 'crossorigin', '' );

		$test_hint1 = $create_hints->create_hint($test1);
		$this->assertEquals($test_hint1, $test1);

		$actual_hint_2 = $create_hints->create_hint($test2);
		$test2['url'] = 'https://www.espn.com';
		$this->assertEquals($test2, $actual_hint_2);

		$test_hint3 = $create_hints->create_hint($test3);
		$this->assertEquals($test_hint3, $test3);

		$test_hint_4 = $create_hints->create_hint($test_4);
		$this->assertEquals(false, $test_hint_4);

		$data1 = TestUtils::create_hint_array( '', 'dns-prefetch' );
		$bool1 = $create_hints->create_hint($data1);
		$this->assertEquals(false, $bool1);

		$actual_6 = $create_hints->create_hint( $test_5 );
		$this->assertEquals( $test_5, $actual_6);
	}



	public function test_new_hint_controller():void {
		$create_hints = new \PPRH\CreateHints();

		$dummy_hint = TestUtils::create_hint_array( 'https://free-hint.com', 'dns-prefetch', '', '', '', 'screen' );
		$dummy_hint['op_code'] = 0;
		$actual = $create_hints->create_hint( $dummy_hint );
		$expected = $create_hints->new_hint_controller( $dummy_hint );
		$this->assertEquals( $expected, $actual );


		$dummy_hint['op_code'] = 1;
		$actual_2 = $create_hints->create_hint( $dummy_hint );
		$expected_2 = $create_hints->new_hint_controller( $dummy_hint );
		$this->assertEquals( $expected_2, $actual_2 );
	}






	public function test_duplicate_hints_exist() {
		$create_hints = new \PPRH\CreateHints();

		$test_hint_1 = TestUtils::create_hint_array( 'https://duplicate-hint.com', 'dns-prefetch', '', '', '', '' );
		$test_hint_2 = TestUtils::create_hint_array( 'https://hint2.com', 'dns-prefetch', '', '', '', '' );
		$test_hint_3 = TestUtils::create_hint_array( 'https://hint3.com', 'dns-prefetch', '', '', '', '' );

		$dup_hints = array( $test_hint_1, $test_hint_2, $test_hint_3 );

		$actual_1 = $create_hints->duplicate_hints_exist( $dup_hints );
		$actual_2 = $create_hints->duplicate_hints_exist( array() );

		$this->assertEquals( true, $actual_1 );
		$this->assertEquals( false, $actual_2 );
	}







	public function test_create_pprh_hint_fail():void {
		$create_hints = new \PPRH\CreateHints();
		$raw_data1 = TestUtils::create_hint_array( '', '' );
		$actual = $create_hints->create_hint( $raw_data1 );
		$this->assertEquals( false, $actual );
	}

	public function test_handle_duplicate_hints():void {
		$dao = new \PPRH\DAO();
		$create_hints = new \PPRH\CreateHints();
		$test_hint_1 = TestUtils::create_hint_array( 'https://duplicate-hints.com', 'preconnect', '', '', '', '' );
		$test_hint_2 = TestUtils::create_hint_array( 'https://testerroo.com', 'preconnect', '', '', '', '' );
		$test_hint_3 = TestUtils::create_hint_array( 'https://asdf3.com', 'preconnect', '', '', '', '' );
		$test_hint_4 = TestUtils::create_hint_array( 'https://test-get-duplicate-hints.com', 'dns-prefetch', '', '', '', '' );

		$all_hints = array( $test_hint_1, $test_hint_2, $test_hint_3, $test_hint_4 );

		$candidate_hint = TestUtils::create_hint_array( 'https://duplicate-hints.com', 'preconnect', '', '', '', '' );

		$actual_1 = $create_hints->handle_duplicate_hints( $all_hints, $candidate_hint );
		$expected_1 = $dao->create_db_result( false, '', 'A duplicate hint already exists!', 0, null );
		$this->assertEquals( $expected_1, $actual_1 );

		$candidate_hint = TestUtils::create_hint_array( 'https://new-unique-hint.com', 'preconnect', '', '', '', '' );

		$actual_2 = $create_hints->handle_duplicate_hints( $all_hints, $candidate_hint );
		$this->assertEquals( true, $actual_2 );
	}

	public function test_get_duplicate_hints():void {
		$create_hints = new \PPRH\CreateHints();
		$test_hint_1 = TestUtils::create_hint_array( 'https://test-get-duplicate-hints.com', 'preconnect', '', '', '', '' );
		$test_hint_2 = TestUtils::create_hint_array( 'https://testerroo.com', 'preconnect', '', '', '', '' );
		$test_hint_3 = TestUtils::create_hint_array( 'https://test-get-duplicate-hints.com', 'preconnect', '', '', '', '' );
		$test_hint_4 = TestUtils::create_hint_array( 'https://asdf3.com', 'preconnect', '', '', '', '' );
		$test_hint_5 = TestUtils::create_hint_array( 'https://test-get-duplicate-hints.com', 'dns-prefetch', '', '', '', '' );

		$all_hints = array( $test_hint_1, $test_hint_2, $test_hint_3, $test_hint_4, $test_hint_5 );
		$new_hint = $create_hints->create_hint( $test_hint_1 );

		$actual = $create_hints->get_duplicate_hints( $all_hints, $new_hint );
		$expected = array( $test_hint_1, $test_hint_3 );

		$this->assertEquals( $expected, $actual );
	}



	public function test_resolve_duplicate_hints() {
		$create_hints = new \PPRH\CreateHints();
		$hint_1 = TestUtils::create_hint_array( 'asdf.com', 'dns-prefetch', '', '', '', 'screen' );
		$actual_1 = $create_hints->resolve_duplicate_hints( $hint_1, array() );
		$this->assertEquals( false, $actual_1 );
	}

}
