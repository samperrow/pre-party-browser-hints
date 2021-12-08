<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class LoadAdminTest extends TestCase {

	public function test_register_admin_files() {
		global $wp_scripts;
		$load_admin = new \PPRH\LoadAdmin( false );
		$load_admin->register_admin_files( PPRH_ADMIN_SCREEN );
		$actual_scripts = array();

		foreach( $wp_scripts->queue as $script ) {
			$actual_scripts[] =  $wp_scripts->registered[$script]->handle;
		}

		$expected_scripts = array( 'thickbox', 'pprh_admin_js', 'post' );
		self::assertEquals( $expected_scripts, $actual_scripts);


		$load_admin->register_admin_files( null );

	}

}
