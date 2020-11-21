<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAO {

	public function __construct() {}

	public function insert_hint( $new_hint ) {
		global $wpdb;
		$current_user = wp_get_current_user()->display_name;
		$action = 'create';

		$wpdb->insert(
			PPRH_DB_TABLE,
			array(
				'url'         => $new_hint->url,
				'hint_type'   => $new_hint->hint_type,
				'status'      => 'enabled',
				'as_attr'     => $new_hint->as_attr,
				'type_attr'   => $new_hint->type_attr,
				'crossorigin' => $new_hint->crossorigin,
				'created_by'  => $current_user,
				'auto_created' => $new_hint->auto_created,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return Utils::get_wpdb_result( $wpdb, $action );
	}

	public function get_hints( $sql ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	public function remove_prev_auto_preconnects() {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		$wpdb->query(
			$wpdb->prepare( "DELETE FROM $table WHERE auto_created = %d AND hint_type = %s", 1, 'preconnect' )
		);
	}

//	public function get_hints() {
//		global $wpdb;
//		$table = PPRH_DB_TABLE;
//
//		return $wpdb->get_results(
//			$wpdb->prepare( "SELECT url, hint_type, as_attr, type_attr, crossorigin FROM $table WHERE status = %s", 'enabled' )
//		);
//	}

}
