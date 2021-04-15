<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class PrePartyBrowserHintsTest extends TestCase {

	public function test_Create_constants():void {
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

	public function test_load_common_files():void {
		$utils = class_exists(\PPRH\Utils::class);
		$dao = class_exists( \PPRH\DAO::class );
		$create_hints = class_exists( \PPRH\CreateHints::class );

		$this->assertEquals( true, $utils );
		$this->assertEquals( true, $dao );
		$this->assertEquals( true, $create_hints );
	}



	public function test_Load_dashboard():void {
		$load_admin = class_exists( \PPRH\LoadAdmin::class );

		if ( WP_ADMIN ) {
			$manage_options = current_user_can( 'manage_options' );
			$this->assertEquals( true, $load_admin );
			$this->assertEquals( true, $manage_options );
		} else {
			$this->assertEquals( false, $load_admin);
		}

	}

	public function test_check_to_upgrade():void {
		$pprh = new \PPRH\Pre_Party_Browser_Hints();
		$new_version = '1.7.5.4';
		$pprh->check_to_upgrade( $new_version );
		$activate_plugin = class_exists(\PPRH\ActivatePlugin::class );

		$expected_true = $new_version !== PPRH_VERSION;
		$expected_false = $new_version === PPRH_VERSION;

		$this->assertEquals( $expected_true, $activate_plugin);
		$this->assertEquals( $expected_false, !$activate_plugin);
	}

	public function test_upgrade_notice():void {

	}



	public function test_():void {

	}
}