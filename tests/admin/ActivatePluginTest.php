<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ActivatePluginTest extends TestCase {

//	public function __construct () {
//	}

//	public function test_add_options():void {
//		$default_prefetch_ignore_links = 'wp-admin, /wp-login.php, /cart, /checkout, add-to-cart, logout, #, ?, .png, .jpeg, .jpg, .gif, .svg, .webp';
//
//		$opt1 = get_option( 'pprh_disable_wp_hints' );
//		$opt2 = get_option( 'pprh_html_head');
//		$opt3 = get_option( 'pprh_prefetch_disableForLoggedInUsers' );
//		$opt4 = get_option( 'pprh_prefetch_enabled' );
//		$opt5 = get_option( 'pprh_prefetch_delay' );
//		$opt6 = get_option( 'pprh_prefetch_ignoreKeywords');
//		$opt7 = get_option( 'pprh_prefetch_maxRPS' );
//		$opt8 = get_option( 'pprh_prefetch_hoverDelay');
//
//		$arr1 = array( $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8 );
//		$expected = array( 'true', 'true', 'true', 'false', '0', $default_prefetch_ignore_links, '3', '50' );
//		$this->assertEquals( $arr1, $expected );
//	}



	public function test_update_prefetch_ignoreKeywords():void {
		include_once PPRH_ABS_DIR . 'includes/admin/ActivatePlugin.php';
		$activate_plugin = new \PPRH\ActivatePlugin();
		$test_data = '/test, /sp-calendar-pro, cart, /wp-login.php';

		$option_name = 'pprh_prefetch_ignoreKeywords';
		$orig_ignore_keywords = get_option( $option_name );
		update_option( $option_name, $test_data);

		$actual_json = $activate_plugin->update_prefetch_ignoreKeywords();
		$ignore_keywords_arr = explode( ', ', $test_data);
		$expected_json = wp_unslash( json_encode( $ignore_keywords_arr ) );

		$this->assertEquals( $actual_json, $expected_json );
		update_option( $option_name, $orig_ignore_keywords);
	}

}
