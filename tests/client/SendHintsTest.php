<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class SendHintsTest extends TestCase {

	public static $send_hints;
	
	public function test_start() {
		self::$send_hints = new \PPRH\SendHints();
	}

	public function test_init() {
		$hints_1 = array();
		$actual_1 = self::$send_hints->init($hints_1, true);
		self::assertFalse( $actual_1 );

		$hints_2 = array( TestUtils::create_hint_array( 'https://asdf.com', 'preconnect', '', '', '', '' ) );
		$actual_2 = self::$send_hints->init($hints_2, false);
		self::assertTrue(  $actual_2 );

		$hints_3 = array();
		$actual_3 = self::$send_hints->init($hints_3, true);
		self::assertFalse( $actual_3 );
	}


	public function test_add_action_ctrl() {
		$actual_1 = self::$send_hints->add_action_ctrl( true, true );
		self::assertFalse( $actual_1 );

		$actual_2 = self::$send_hints->add_action_ctrl( true, false );
		self::assertFalse( $actual_2 );

		$actual_3 = self::$send_hints->add_action_ctrl( false, true);
		self::assertFalse( $actual_3 );

		$actual_4 = self::$send_hints->add_action_ctrl( false, false );
		self::assertTrue(  $actual_4 );
	}

	public function test_send_in_http_header() {

	}






}
