<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

//define( 'WP_ADMIN', true);
//$_SERVER['PHP_SELF'] = '/Users/samperrow/repos/WordPress/wp-admin/admin.php';
//include_once '/Users/samperrow/Desktop/repos/WordPress/wp-load.php';
//include_once '/Users/samperrow/Desktop/repos/WordPress/wp-admin/includes/plugin.php';

final class ActivatePluginTest extends TestCase {

//	public function __construct () {}

	public function test_constructor():void {
//		$activate_plugin = new \PPRH\Activate_Plugin();

		$opt1 = get_option( 'pprh_disable_wp_hints' );
		$opt2 = get_option( 'pprh_html_head');
		$opt3 = get_option( 'pprh_prefetch_enabled' );
		$opt4 = get_option( 'pprh_prefetch_delay' );
		$opt5 = get_option( 'pprh_prefetch_ignoreKeywords');
		$opt6 = get_option( 'pprh_prefetch_maxRPS' );
		$opt7 = get_option( 'pprh_prefetch_hoverDelay');

		$arr1 = array( $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7 );
		$expected = array( 'true', 'true', 'false', '0', '', '3', '50' );
		$this->assertEquals( $arr1, $expected );

		
	}	

}
