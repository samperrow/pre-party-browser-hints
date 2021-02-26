<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class GeneralSettingsTest extends TestCase {

//	public function test_set_values () {
//		if ( ! PPRH_IS_ADMIN ) return;

//		$general_settings = new \PPRH\GeneralSettings();
//		$general_settings->set_values();
//		$actual = \PPRH\Utils::is_option_checked( 'pprh_disable_wp_hints' );
//
//		$this->assertEquals($actual, $general_settings->disable_wp_hints);
//
//	}

	public function test_save_options():void {
		if ( ! PPRH_IS_ADMIN ) return;

		$this->option_update( 'disable_wp_hints', 'false');
		$this->option_update( 'html_head', 'true');
	}

//	public function test_save_options2():void {
//		$this->option_update( 'disable_wp_hints', 'true');
//		$this->option_update( 'html_head', 'false');
//	}

	public function option_update( $short_option_name, $test_value ) {
		$general_settings = new \PPRH\GeneralSettings();
		$full_option_name = 'pprh_' . $short_option_name;
		$orig_value = get_option( $full_option_name );
		if ( 'true' === $test_value) {
			$_POST[$short_option_name] = $test_value;
		}
		$general_settings->save_options();

		$this->assertEquals(get_option( $full_option_name ), $test_value );
		update_option( $full_option_name, $orig_value );
	}


//	public function test_markup() {
//
//	}




}
