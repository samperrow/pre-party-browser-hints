<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PPRH\Utils\Debug;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DebugTest extends TestCase {

	public static Debug $debug;

	/**
	 * @before Class
	 */
	public function init() {
//		$this->setOutputCallback(function () {});
		self::$debug = new Debug();
	}

	public function test_get_browser_name() {
		$user_agent_1 = '';
		$actual_1 = self::$debug::get_browser_name( $user_agent_1 );
		self::assertSame( 'unknown browser.', $actual_1 );

		$user_agent_2 = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:89.0) Gecko/20100101 Firefox/89.0';
		$actual_2 = self::$debug::get_browser_name( $user_agent_2 );
		self::assertEquals( 'Firefox', $actual_2 );

		$user_agent_3 = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15';
		$actual_3 = self::$debug::get_browser_name( $user_agent_3 );
		self::assertEquals( 'Safari', $actual_3 );

		$user_agent_4 = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_16_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36';
		$actual_4 = self::$debug::get_browser_name( $user_agent_4 );
		self::assertEquals( 'Chrome', $actual_4 );

		$user_agent_5 = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.106 Safari/537.36 Edg/91.0.864.53';
		$actual_5 = self::$debug::get_browser_name( $user_agent_5 );
		self::assertEquals( 'Edge', $actual_5 );

		$user_agent_6 = 'Mozilla/5.0 (Macintosh;IntelMacOSX10_16_0)AppleWebKit/537.36(KHTML,likeGecko)Chrome/85.0.4183.121Safari/537.36OPR/71.0.3770.228';
		$actual_6 = self::$debug::get_browser_name( $user_agent_6 );
		self::assertEquals( 'Opera', $actual_6 );
	}

}
