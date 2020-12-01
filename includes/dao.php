<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAO {

//	public function __construct() {}

	public function create_hint( $new_hint ) {
		global $wpdb;
		$current_user = wp_get_current_user()->display_name;
		$auto_created = ( ! empty( $new_hint->auto_created ) ? $new_hint->auto_created : 0 );

		$wpdb->insert(
			PPRH_DB_TABLE,
			array(
				'url'          => $new_hint->url,
				'hint_type'    => $new_hint->hint_type,
				'status'       => 'enabled',
				'as_attr'      => $new_hint->as_attr,
				'type_attr'    => $new_hint->type_attr,
				'crossorigin'  => $new_hint->crossorigin,
				'created_by'   => $current_user,
				'auto_created' => $auto_created,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return Utils::get_wpdb_result( $wpdb, 'create' );
	}

	public function update_hint( $data ) {
		global $wpdb;
		$hint_id = (int) $data->hint_id;

		$wpdb->update(
			PPRH_DB_TABLE,
			array(
				'url'         => $data->url,
				'hint_type'   => $data->hint_type,
				'as_attr'     => $data->as_attr,
				'type_attr'   => $data->type_attr,
				'crossorigin' => $data->crossorigin,
			),
			array(
				'id' => $hint_id,
			),
			array( '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		return Utils::get_wpdb_result( $wpdb, 'update' );
	}

	public function delete_hint( $data ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		if ( ! is_array( $data->hint_ids ) ) {
			return false;
		}

		$concat_ids = implode( ',', array_map( 'absint', $data->hint_ids ) );
		$wpdb->query( "DELETE FROM $table WHERE id IN ($concat_ids)" );
		return Utils::get_wpdb_result( $wpdb, 'delete' );
	}

	public function bulk_update( $data, $action ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;
		$concat_ids = implode( ',', array_map( 'absint', $data->hint_ids ) );

		$wpdb->query( $wpdb->prepare(
			"UPDATE $table SET status = %s WHERE id IN ($concat_ids)",
			$action
		) );

		return Utils::get_wpdb_result( $wpdb, $action );
	}



	public function remove_prev_auto_preconnects() {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		$wpdb->query(
			$wpdb->prepare( "DELETE FROM $table WHERE auto_created = %d AND hint_type = %s", 1, 'preconnect' )
		);
	}


	public function get_hints( $sql ) {
		global $wpdb;

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	public function get_hints_query( $sql, $arr ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare( $sql, $arr )
		);
	}


	public function get_multisite_tables() {
		global $wpdb;
		$blog_table = $wpdb->base_prefix . 'blogs';
		$ms_table_names = array();

		$ms_blog_ids = $wpdb->get_results(
			$wpdb->prepare( "SELECT blog_id FROM $blog_table WHERE blog_id != %d", 1 )
		);

		if ( ! empty( $ms_blog_ids ) && count( $ms_blog_ids ) > 0 ) {
			foreach ( $ms_blog_ids as $ms_blog_id ) {
				$ms_table_name = $wpdb->base_prefix . $ms_blog_id->blog_id . '_pprh_table';
				$ms_table_names[] = $ms_table_name;
			}
		}
		return $ms_table_names;
	}

	public function create_table( $table_name ) {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		if ( ! function_exists( 'dbDelta' ) ) {
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$sql = "CREATE TABLE $table_name (
            id INT(9) NOT NULL AUTO_INCREMENT,
            url VARCHAR(255) DEFAULT '' NOT NULL,
            hint_type VARCHAR(55) DEFAULT '' NOT NULL,
            status VARCHAR(55) DEFAULT 'enable' NOT NULL,
            as_attr VARCHAR(55) DEFAULT '',
            type_attr VARCHAR(55) DEFAULT '',
            crossorigin VARCHAR(55) DEFAULT '',
            created_by VARCHAR(55) DEFAULT '' NOT NULL,
            auto_created INT(2) DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id)
        ) $charset;";

		dbDelta( $sql, true );
	}

}
