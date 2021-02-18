<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class SendHintsTest extends TestCase {

	public $test_hint;

//	public function construct() {
//		$dao = new \PPRH\DAO();
//		$test_hint_data = array(
//			'url' => 'https://SendHintsTest.com',
//			'hint_type' => 'prefetch',
//		);
//		$new_hint = \PPRH\CreateHints::create_pprh_hint( $test_hint_data );
//		$this->test_hint = $dao->insert_hint( $new_hint );
//	}

	public function test_get_query() {
		if ( is_admin() ) {
			return;
		}
//		$this->construct();
		$send_hints = new \PPRH\SendHints();
		$actual_query = $send_hints->get_query();

		$expected_query = array(
			'sql'  => " WHERE status = %s",
			'args' => array( 'enabled' )
		);

		$expected_query = apply_filters( 'pprh_sh_append_sql', $expected_query );
		$this->assertEquals( $expected_query, $actual_query );
		return $actual_query;
	}

	/**
	 * @depends test_get_query
	 */
	public function test_get_resource_hints( $query ) {
		if ( is_admin() ) {
			return;
		}
		$send_hints = new \PPRH\SendHints();
		$actual_hints = $send_hints->get_resource_hints( $query );
		$actual_hints_count = is_array( $actual_hints );
		$this->assertEquals( true, $actual_hints_count );
	}





	public function test_send_to_html_head() {

	}

	public function test_send_in_http_header() {

	}






}
