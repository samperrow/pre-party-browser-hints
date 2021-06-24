<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ActivatePluginTest extends TestCase {

	public $activate_plugin;

	/**
	 * @before
	 */
	public function test_start():void {
		$this->activate_plugin = new \PPRH\ActivatePlugin();
	}

	public function test_convert_prefetch_string_to_array():void {
		$orig_prefetch_ignore_links = '/wp-admin, /wp-login.php, /cart, /checkout, add-to-cart, logout, #, ?, .png, .jpeg, .jpg, .gif, .svg, .webp';
		$keyword_array = $this->activate_plugin->convert_prefetch_string_to_array( $orig_prefetch_ignore_links );
		self::assertEquals( true, is_array( $keyword_array ) );


//		$keywords_1 = '["/cart","test","wp-login.php"]';
//		$keywords_2 = '["["["/cart","test","wp-login.php"]"]"]';
//		$keywords_3 = '/cart, test, wp-login.php';
//		$keywords_4 = '["["wp-admin","/wp-login.php","/cart","/checkout","add-to-cart","logout","#","?",".png",".jpeg",".jpg",".gif",".svg",".webp"]"]';
//
//		$updated_keywords_1 = $this->activate_plugin->reformat_prefetch_keywords( $keywords_1 );
//		$expected_1 = '/cart, test, wp-login.php';
//		self::assertEquals( $expected_1, $updated_keywords_1 );
//
//		$updated_keywords_2 = $this->activate_plugin->reformat_prefetch_keywords( $keywords_2 );
//		$expected_2 = '/cart, test, wp-login.php';
//		self::assertEquals( $expected_2, $updated_keywords_2 );
//
//		$updated_keywords_3 = $this->activate_plugin->reformat_prefetch_keywords( $keywords_3 );
//		$expected_3 = '/cart, test, wp-login.php';
//		self::assertEquals( $expected_3, $updated_keywords_3 );
//
//		$updated_keywords_4 = $this->activate_plugin->reformat_prefetch_keywords( $keywords_4 );
//		$expected_4 = 'wp-admin, /wp-login.php, /cart, /checkout, add-to-cart, logout, #, ?, .png, .jpeg, .jpg, .gif, .svg, .webp';
//		self::assertEquals( $expected_4, $updated_keywords_4 );
	}


}
