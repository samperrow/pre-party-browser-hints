<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class LoadAdminTest extends TestCase {

	public function test_register_admin_files():void {
		if ( ! WP_ADMIN ) return;

		global $wp_scripts;
		$load_admin = new \PPRH\LoadAdmin( false );
		$load_admin->register_admin_files( 'toplevel_page_pprh-plugin-settings' );
		$actual_scripts = array();

		foreach( $wp_scripts->queue as $script ) {
			$actual_scripts[] =  $wp_scripts->registered[$script]->handle;
		}

//		if ( WP_ADMIN && DOING_AJAX ) {
//			$expected_scripts[] = 'thickbox';
//		}

		$expected_scripts = array( 'pprh_create_hints_js', 'pprh_admin_js', 'post' );

		self::assertEquals( $expected_scripts, $actual_scripts);
	}

}
