<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ActivatePluginTest extends TestCase {

	public static $activate_plugin;

	public function test_start() {
		self::$activate_plugin = new \PPRH\ActivatePlugin();
	}

	public function test_convert_prefetch_string_to_array() {
		$orig_prefetch_ignore_links = '/wp-admin, /wp-login.php, /cart, /checkout, add-to-cart, logout, #, ?, .png, .jpeg, .jpg, .gif, .svg, .webp';
		$keyword_array = self::$activate_plugin->convert_prefetch_string_to_array( $orig_prefetch_ignore_links );
		self::assertIsArray( $keyword_array );
	}


}
