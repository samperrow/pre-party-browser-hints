<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if (!defined('ABSPATH')) {
	exit;
}

final class AjaxOpsTest extends TestCase {

	public function test_pprh_update_hints(): void {
		if ( ! WP_ADMIN || ! wp_doing_ajax() ) return;

		$expected = true;
		$ajax_ops = new \PPRH\AjaxOps();
		$expected_nonce = TestUtils::create_nonce('pprh_table_nonce');
		$_POST['pprh_data'] = '{"url":"tester","hint_type":"dns-prefetch","action":"create","hint_ids":null"}';
		$_POST['action'] = 'pprh_update_hints';
		$_REQUEST['nonce'] = $expected_nonce;
		$initiated = $ajax_ops->pprh_update_hints();
		$this->assertEquals($expected, $initiated);
	}

	// also need to verify update, delete, enable, disable, bulk operations work properly.

//	public function test_init():void {
//		$dao = new \PPRH\DAO();
//		$response = json_decode( $json, true );
//		$db_result = $response['result']['db_result'];
//		$result = $db_result['success'];
//		$hint_id = $db_result['hint_id'];
//	}


}


