<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ActivatePluginTest extends TestCase {

//	public function __construct () {
//	}

	public function test_update_prefetch_keywords():void {
		$pprh = new \PPRH\Pre_Party_Browser_Hints();
		$pprh->do_upgrade();

		$activate_plugin = new \PPRH\ActivatePlugin();

		$keywords_1 = '["/cart","test","wp-login.php"]';
		$keywords_2 = '["["["/cart","test","wp-login.php"]"]"]';
		$keywords_3 = '/cart, test, wp-login.php';

		$updated_keywords_1 = $activate_plugin->update_prefetch_keywords( $keywords_1 );
		$updated_keywords_2 = $activate_plugin->update_prefetch_keywords( $keywords_2 );
		$updated_keywords_3 = $activate_plugin->update_prefetch_keywords( $keywords_3 );

		$expected_1 = '/cart, test, wp-login.php';
		$expected_2 = '/cart, test, wp-login.php';
		$expected_3 = '/cart, test, wp-login.php';

		$this->assertEquals( $expected_1, $updated_keywords_1 );
		$this->assertEquals( $expected_2, $updated_keywords_2 );
		$this->assertEquals( $expected_3, $updated_keywords_3 );
	}


}
