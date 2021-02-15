<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

if (!defined('ABSPATH')) {
	exit;
}

final class AjaxOpsTest extends TestCase {

	public function test_onAdmin():bool {
		$on_admin = is_admin();
		$this->assertEquals($on_admin, $on_admin);
		return $on_admin;
	}

	/**
	 * @depends test_onAdmin
	 */
	public function test_pprh_update_hints( bool $on_admin ):void {
		if ( ! $on_admin ) {
			return;
		}
		$dao = new \PPRH\DAO();
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

		$_POST['pprh_data'] = '{"url":"tester","hint_type":"dns-prefetch","crossorigin":"","as_attr":"","type_attr":"","action":"create","hint_ids":null,"post_id":"global"}';
		$_POST['action'] = 'pprh_update_hints';
		$_REQUEST['nonce'] = $expected_nonce;

		$ajax_ops = new \PPRH\AjaxOps();

		$json = $ajax_ops->pprh_update_hints();
		$response = json_decode( $json, true );
		$db_result = $response['result']['db_result'];
		$result = $db_result['success'];
		$hint_id = $db_result['hint_id'];
		$this->assertEquals(true, $result);
		$dao->delete_hint( $hint_id );
	}

	// also need to verify update, delete, enable, disable, bulk operations work properly.

}



