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
		$all_hints = \PPRH\Utils::get_all_hints();

		$send_hints->hints = array();
		$actual_1 = $send_hints->init($all_hints);

		$send_hints->hints = false;
		$actual_2 = $send_hints->init($all_hints);

		$send_hints->hints = array(
			array(
				'url' => 'https://asdf.com',
				'hint_type' => 'preconnect'
			)
		);

		$send_hints->init($all_hints);
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


	public function test_send_to_html_head() {

	}

	public function test_send_in_http_header() {

	}






}
