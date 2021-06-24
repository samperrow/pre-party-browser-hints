<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class SendHintsTest extends TestCase {

	public $test_hint;
	public $send_hints;

	/**
	 * @before
	 */
	public function test_start():void {
		$this->send_hints = new \PPRH\SendHints();
	}

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
		$hints_1 = array();
		$actual_1 = $this->send_hints->init($hints_1, true);
		self::assertEquals( false, $actual_1 );

		$hints_2 = array( TestUtils::create_hint_array( 'https://asdf.com', 'preconnect', '', '', '', '' ) );
		$actual_2 = $this->send_hints->init($hints_2, false);
		self::assertEquals( true, $actual_2 );

		$hints_3 = array();
		$actual_3 = $this->send_hints->init($hints_3, true);
		self::assertEquals( false, $actual_3 );
	}


	public function test_add_action_ctrl():void {
		$actual_1 = $this->send_hints->add_action_ctrl( true, true );
		self::assertEquals( false, $actual_1 );

		$actual_2 = $this->send_hints->add_action_ctrl( true, false );
		self::assertEquals( false, $actual_2 );

		$actual_3 = $this->send_hints->add_action_ctrl( false, true);
		self::assertEquals( false, $actual_3 );

		$actual_4 = $this->send_hints->add_action_ctrl( false, false );
		self::assertEquals( true, $actual_4 );
	}

	public function test_send_in_http_header() {

	}






}
