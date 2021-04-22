<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LoadClientTest extends TestCase {



	public function test_init():void {
		if ( WP_ADMIN ) return;

		$load_client = new \PPRH\LoadClient();


	}

}
