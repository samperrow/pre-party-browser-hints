<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

//class GeneralSettingsTest extends TestCase {

//	public function test_set_values () {
//		$general_settings = new \PPRH\GeneralSettings();
//		$general_settings->set_values();
//		$actual = \PPRH\Utils::is_option_checked( 'pprh_disable_wp_hints' );
//
//		self::assertEquals($actual, $general_settings->disable_wp_hints);
//
//	}

//	public function test_save_options():void {//
//		$orig_value_1 = \get_option( 'pprh_disable_wp_hints' );
//		$orig_value_2 = \get_option( 'pprh_html_head' );
//
//		$this->option_update( 'pprh_disable_wp_hints', 'false');
//		$this->option_update( 'pprh_html_head', 'true');
//
//		\update_option( 'pprh_disable_wp_hints', $orig_value_1 );
//		\update_option( 'pprh_html_head', $orig_value_2 );
//	}

//	public function test_save_options2():void {
//		$this->option_update( 'pprh_disable_wp_hints', 'true');
//		$this->option_update( 'pprh_html_head', 'false');
//	}

//	public function option_update( $option_name, $test_value ) {
//		$general_settings = new \PPRH\GeneralSettings();
//		$_POST[$option_name] = $test_value;
//		$general_settings->save_options();
//		self::assertEquals(\get_option( $option_name ), $test_value );
//	}


//	public function test_markup() {
//
//	}




//}
