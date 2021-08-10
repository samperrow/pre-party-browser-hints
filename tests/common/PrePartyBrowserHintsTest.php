<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class PrePartyBrowserHintsTest extends TestCase {

	public $pprh;

	/**
	 * @before
	 */
	public function test_start() {
		$this->pprh = new \PPRH\Pre_Party_Browser_Hints();
	}

	public function test_create_constants() {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';
		self::assertEquals( PPRH_DB_TABLE, $table );

		$abs_dir = WP_PLUGIN_DIR . '/pre-party-browser-hints/';
		self::assertEquals( PPRH_ABS_DIR, $abs_dir );

		$rel_dir = \plugins_url() . '/pre-party-browser-hints/';
		self::assertEquals( PPRH_REL_DIR, $rel_dir );

//		$home_url = \admin_url() . 'admin.php?page=' . PPRH_MENU_SLUG;
//		self::assertEquals( PPRH_HOME_URL, $home_url );

		$version = \get_option( 'pprh_version' );
		self::assertEquals( PPRH_VERSION, $version );
	}

	public function test_load_common_files() {
		$utils = class_exists(\PPRH\Utils::class);
		self::assertTrue(  $utils );

		$dao = class_exists( \PPRH\DAO::class );
		self::assertTrue( $dao );

		$create_hints = class_exists( \PPRH\CreateHints::class );
		self::assertTrue( $create_hints );
	}

	public function test_load_dashboard() {
		$load_admin = class_exists( \PPRH\LoadAdmin::class );
		self::assertTrue( $load_admin );

		$manage_options = current_user_can( 'manage_options' );
		self::assertTrue( $manage_options );
	}

	public function test_activate_plugin() {
		$actual = $this->pprh->activate_plugin();

		self::assertTrue( $actual );
	}

	public function test_register_activation_hook() {
		$registered = \has_filter( 'activate_pre-party-browser-hints/pre-party-browser-hints.php' );
		self::assertTrue( $registered );

		$wp_loaded = \has_filter( 'wp_loaded' );
		self::assertTrue( $wp_loaded );
	}


//	public function test_() {
//
//	}
}