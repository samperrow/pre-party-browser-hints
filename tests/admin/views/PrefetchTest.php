<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class PrefetchTest extends TestCase {

	public function test_flying_pages_loaded(): void {
		$option_name = 'pprh_prefetch_enabled';
		$option_value = get_option($option_name);
		$expected = ('true' === $option_value);

		$actual_enabled = $this->flying_pages_loaded();

		self::assertEquals($actual_enabled, $expected);
	}


	public function flying_pages_loaded():bool {
		global $wp_scripts;
//		\do_action('wp_enqueue_scripts');

		foreach ($wp_scripts->queue as $handle) {
			if ('pprh_prefetch_flying_pages' === $handle) {
				return true;
			}
		}

		return false;
	}

//	public function test_update_prefetch_ignoreKeywords() {
//		$orig_ignore_keywords = get_option( 'pprh_prefetch_ignoreKeywords' );
//		$ignore_keywords_arr = explode( ',', $orig_ignore_keywords);
//		$json = json_encode( $ignore_keywords_arr );
//
//		$arr = json_decode($json, true);
//		$string = implode( ',', $arr);
//		self::assertEquals(true, true);
//	}

//	public function test_disabled_for_logged_in_users(): void {
//		$option_name = 'pprh_prefetch_disableForLoggedInUsers';
//		wp_set_current_user(2, 'phpUnitTesting' );
//
//		$disable_for_logged_in_users = get_option( $option_name );
//		$expected = ('true' === $disable_for_logged_in_users);
//		$actual_enabled = $this->flying_pages_loaded();
//
//		self::assertEquals($actual_enabled, $expected);
//	}


}