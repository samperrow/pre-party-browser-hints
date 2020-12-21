<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Create_HintsTest extends TestCase {

//	public function __construct() {}

	public function testInit():void {
		define( 'CREATING_HINT', true );
		$create_hints = new \PPRH\Create_Hints();
		$data = \PPRH\Utils::create_hint_object( 'https://www.espn.com', 'dns-prefetch', 0 );

		$new_hint = $create_hints->initialize( $data );
		$this->assertEquals( $new_hint, $data );
	}


	public function testEmptyDataFails():void {
		$create_hints = new \PPRH\Create_Hints();

		$data1 = (object) array(
			'url'       => '',
			'hint_type' => 'dns-prefetch'
		);

		$bool1 = $create_hints->initialize( $data1 );
		$this->assertEquals( false, $bool1 );
	}

}
