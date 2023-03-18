<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PrePartyBrowserHintsTest extends TestCase {

	public static $pprh;

	public static function test_start() {
		self::$pprh = new \PPRH\Pre_Party_Browser_Hints();
	}

	public function test_pprh_activate_plugin() {

		$actual = \PPRH\pprh_activate_plugin();
		self::assertTrue( $actual );
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

	public function test_get_plugin_page_ctrl() {
		$actual_1 = self::$pprh->get_plugin_page_ctrl( false, 'https://sptrix.local/wp-admin/plugins.php?plugin_status=all&paged=1&s', '/wp-admin/admin.php?page=pprh-plugin-settings' );
		self::assertSame( 1, $actual_1 );

//		$actual_2 = Utils::get_plugin_page_ctrl( false, 'https://sptrix.local/wp-admin/edit.php?post_type=page', 'post.php' );
//		self::assertTrue( $actual_2 );

		$actual_3 = self::$pprh->get_plugin_page_ctrl( true, 'https://sptrix.local/wp-admin/admin.php?page=pprh-plugin-settings', 'admin-ajax.php' );
		self::assertSame( 1, $actual_3 );

//		$actual_4 = Utils::get_plugin_page_ctrl(true, 'https://sptrix.local/wp-admin/post.php?post=2128&action=edit', 'admin-ajax.php' );
//		self::assertTrue( $actual_4 );

		$actual_5 = self::$pprh->get_plugin_page_ctrl(false, 'https://sptrix.local/wp-admin/admin.php?page=pprh-plugin-settings', '/wp-admin/upload.php' );
		self::assertSame( 0, $actual_5 );

		$actual_6 = self::$pprh->get_plugin_page_ctrl(false, 'https://sptrix.local/wp-admin/admin.php?page=pprh-plugin-settings', '/wp-admin/themes.php' );
		self::assertSame( 0, $actual_6 );

		$actual_7 = self::$pprh->get_plugin_page_ctrl(false, 'https://sptrix.local/wp-admin/themes.php', '/wp-admin/options-general.php' );
		self::assertSame( 0, $actual_7 );

		$actual_8 = self::$pprh->get_plugin_page_ctrl( false, 'https://sptrix.local/', '' );
		self::assertSame( 0, $actual_8 );

		$actual_9 = self::$pprh->get_plugin_page_ctrl( true, 'asdfasys4ygdadf<>######%', '?' );
		self::assertSame( 0, $actual_9 );
	}

	public function test_load_dashboard() {
		$load_admin = class_exists( \PPRH\LoadAdmin::class );
		self::assertTrue( $load_admin );

//		$manage_optionsanage_options = current_user_can( 'manage_options' );
//		self::assertTrueertTruetTrue( $manage_options );
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
