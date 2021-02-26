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

	public function test_init():void {
		if ( PPRH_IS_ADMIN ) return;

		$send_hints = new \PPRH\SendHints();

		$send_hints->hints = array();
		$actual_1 = $send_hints->init();

		$send_hints->hints = false;
		$actual_2 = $send_hints->init();

		$send_hints->hints = array(
			array(
				'url' => 'https://asdf.com',
				'hint_type' => 'preconnect'
			)
		);

		$send_hints->init();
		$send_hints_in_html = get_option( 'pprh_html_head' );

		if ( 'false' === $send_hints_in_html && ! headers_sent() ) {
			$actual_3 = has_action( 'send_headers', array( $send_hints, 'send_in_http_header' ) );
		} else {
			$actual_3 = has_action( 'wp_head', array( $send_hints, 'send_to_html_head' ) );
		}

		$this->assertEquals( false, $actual_1 );
		$this->assertEquals( false, $actual_2 );
		$this->assertEquals( true, $actual_3 );
	}

	public function test_get_query() {
		if ( PPRH_IS_ADMIN ) return;

//		$this->construct();
		$_SERVER['REQUEST_URI'] = '/sp-calendar-pro/core/';
		$send_hints = new \PPRH\SendHints();
		$actual_query = $send_hints->get_query();
		$table = PPRH_DB_TABLE;

		$expected_query = array(
			'sql'  => "SELECT * FROM $table WHERE status = %s",
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
		if ( PPRH_IS_ADMIN ) return;

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
