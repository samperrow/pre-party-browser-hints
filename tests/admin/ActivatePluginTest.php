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
		$keywords_4 = '["["wp-admin","/wp-login.php","/cart","/checkout","add-to-cart","logout","#","?",".png",".jpeg",".jpg",".gif",".svg",".webp"]"]';

		$updated_keywords_1 = $activate_plugin->reformat_prefetch_keywords( $keywords_1 );
		$expected_1 = '/cart, test, wp-login.php';
		self::assertEquals( $expected_1, $updated_keywords_1 );

		$updated_keywords_2 = $activate_plugin->reformat_prefetch_keywords( $keywords_2 );
		$expected_2 = '/cart, test, wp-login.php';
		self::assertEquals( $expected_2, $updated_keywords_2 );

		$updated_keywords_3 = $activate_plugin->reformat_prefetch_keywords( $keywords_3 );
		$expected_3 = '/cart, test, wp-login.php';
		self::assertEquals( $expected_3, $updated_keywords_3 );

		$updated_keywords_4 = $activate_plugin->reformat_prefetch_keywords( $keywords_4 );
		$expected_4 = 'wp-admin, /wp-login.php, /cart, /checkout, add-to-cart, logout, #, ?, .png, .jpeg, .jpg, .gif, .svg, .webp';
		self::assertEquals( $expected_4, $updated_keywords_4 );
	}


}
