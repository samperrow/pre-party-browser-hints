<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class UtilsProTest extends TestCase {


	public function testLicense_key_seems_legit():void {
		$legit_key1 = '5fe959c58cbfe9.62303133';
		$legit_key2 = '5fe9c6d5241942.73540162';
		$bad_key1 = '5fe9c6d5241942.735401a62';


		$legit_key1_result = \PPRH\Utils_Pro::license_key_seems_legit( $legit_key1 );
		$this->assertEquals( $legit_key1_result, true );

		$legit_key2_result = \PPRH\Utils_Pro::license_key_seems_legit( $legit_key2 );
		$this->assertEquals( $legit_key2_result, true );

		$bad_key2_result = \PPRH\Utils_Pro::license_key_seems_legit( $bad_key1 );
		$this->assertEquals( $bad_key2_result, false );
	}

}