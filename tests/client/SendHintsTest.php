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
		if ( WP_ADMIN ) return;

		$send_hints = new \PPRH\SendHints();

		$hints_1 = array();

		$hints_2 = array(
			array('url' => 'https://asdf.com', 'hint_type' => 'preconnect', 'status' => 'enabled' ),
		);

		$hints_3 = array();

		$actual_1 = $send_hints->init($hints_1);
		$actual_2 = $send_hints->init($hints_2);
		$actual_3 = $send_hints->init($hints_3);

		$send_hints_in_html = get_option( 'pprh_html_head' );

		if ( 'false' === $send_hints_in_html && ! headers_sent() ) {
			$actual_4 = has_action( 'send_headers', array( $send_hints, 'send_header' ) );
		} else {
			$actual_4 = has_action( 'wp_head', array( $send_hints, 'send_html_head' ) );
		}

		$this->assertEquals( false, $actual_1 );
		$this->assertEquals( true, $actual_2 );
		$this->assertEquals( false, $actual_3 );
		$this->assertEquals( true, $actual_4 );
	}


	public function test_send_to_html_head() {

	}

	public function test_send_in_http_header() {

	}






}
