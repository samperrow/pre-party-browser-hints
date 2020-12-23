<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

//if (!defined('ABSPATH')) {
//	exit;
//}

final class Pre_Party_Browser_HintsTest extends TestCase {

//	public function __construct() {}

//	public function testInit(): void  {
////		$pprh = new Pre_Party_Browser_Hints();
//		$this->assertEquals( 'asdf', 'asdf' );
//	}

	public function testCreate_constants():void {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';
		$abs_dir = WP_PLUGIN_DIR . '/pre-party-browser-hints/';
		$rel_dir = plugins_url() . '/pre-party-browser-hints/';
		$home_url = admin_url() . 'admin.php?page=pprh-plugin-setttings';
		$version = get_option( 'pprh_version' );

		$this->assertEquals( PPRH_VERSION, $version );
		$this->assertEquals( PPRH_DB_TABLE, $table );
		$this->assertEquals( PPRH_ABS_DIR, $abs_dir );
		$this->assertEquals( PPRH_REL_DIR, $rel_dir );
		$this->assertEquals( PPRH_HOME_URL, $home_url );
	}

//	public function testRegister_admin_files():void {
//
//		$this->assertTrue( wp_script_is( 'pprh_admin_js', 'queue' ) );
//
//	}
}