<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class PreconnectSettingsTest extends TestCase {

	public function test_constructor() {
		if ( PPRH_IS_ADMIN ) {
			$this->eval_load_reset_settings();
		}
	}

	public function eval_load_reset_settings() {
		$on_pprh_admin = false;
		$preconnect_settings = new \PPRH\PreconnectSettings($on_pprh_admin);
		$is_pro_loaded = $preconnect_settings->load_reset_settings();

		$expected = ( $on_pprh_admin && PPRH_PRO_PLUGIN_ACTIVE );
		$this->assertEquals( $expected, $is_pro_loaded );
	}

}
