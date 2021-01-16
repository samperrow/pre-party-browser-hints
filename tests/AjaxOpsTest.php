<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PPRH\PPRH_Pro;

if (!defined('ABSPATH')) {
	exit;
}

final class AjaxOpsTest extends TestCase {

	public function test_pprh_update_hints():void {
		define( 'DOING_AJAX', true );
		$_SERVER['HTTP_HOST'] = 'sphacks.local';
		$_SERVER['REQUEST_URI'] = '/wp-admin/admin.php?page=pprh-plugin-settings';
		$_SERVER['HTTP_REFERER'] = 'sphacks.local';
		$token = wp_get_session_token();
		$i     = wp_nonce_tick();
		$user  = wp_get_current_user();
		$uid   = (int) $user->ID;
		$action = 'pprh_table_nonce';

		// Nonce generated 0-12 hours ago.
		$expected_nonce = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );

		$_POST['pprh_data'] = '{"url":"tester","hint_type":"dns-prefetch","crossorigin":"","as_attr":"","type_attr":"","action":"create","hint_id":null,"post_id":""}';
		$_POST['action'] = 'pprh_update_hints';
		$_REQUEST['val'] = $expected_nonce;

		$ajax_ops = new \PPRH\Ajax_Ops();

		$response = $ajax_ops->pprh_update_hints();
		$obj = json_decode( $response, false );
		$result = $obj->result->db_result->success;
		$this->assertEquals(true, $result);
	}

	// also need to verify update, delete, enable, disable, bulk operations work properly.

}



