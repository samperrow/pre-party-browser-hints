<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class PrePartyBrowserHintsTest extends TestCase {

	public static $pprh;

//	public static function test_start() {
//		self::$pprh = new \PPRH\Pre_Party_Browser_Hints();
//	}

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
		$utils = class_exists(\PPRH\Utils\Utils::class);
		self::assertTrue(  $utils );

		$dao = class_exists( \PPRH\DAO::class );
		self::assertTrue( $dao );

		$hint_ctrl = class_exists( \PPRH\HintController::class );
		self::assertTrue( $hint_ctrl );

		$hint_builder = class_exists( \PPRH\HintBuilder::class );
		self::assertTrue( $hint_builder );

//		$activate_plugin = class_exists( \PPRH\ActivatePlugin::class );
//		self::assertTrue( $activate_plugin );
	}

	public function test_load_dashboard() {
		$load_admin = class_exists( \PPRH\LoadAdmin::class );
		self::assertTrue( $load_admin );

		$manage_options = current_user_can( 'manage_options' );
		self::assertTrue( $manage_options );
	}

//	public function test_activate_plugin() {
//		$actual = self::$pprh->activate_plugin();
//		self::assertTrue( $actual );
//	}

	public function test_register_activation_hook() {
		$registered = \has_filter( 'activate_pre-party-browser-hints/pre-party-browser-hints.php' );
		self::assertTrue( $registered );

		$wp_loaded = \has_filter( 'wp_loaded' );
		self::assertTrue( $wp_loaded );
	}

}
