<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAO {

	public $table;

	public function __construct() {
		$this->table = PPRH_DB_TABLE;
	}

	// db results
	public static function create_db_result( bool $success, int $action_code, int $success_code, array $new_hint = null ) {
		$msg = self::get_msg( $success, $action_code, $success_code );

		return (object) array(
			'new_hint'  => $new_hint ?? null,
			'db_result' => array(
				'msg'    => $msg,
				'status' => $success,
			)
		);
	}

	private static function get_msg( bool $success, int $action_code, int $success_code ):string {
		$dup_hints_alert = 'A duplicate hint exists!';
		$preconnect_success = 'Auto preconnect hints for this post have been reset. Please load this page on the front end to re-create the preconnect hints.';
		$preconnect_fail = 'Failed to reset this post\'s preconnect data. Please refresh the page and try again.';

		$prerender_single_success = 'Prerender hint successfully created for this post.';
		$prerender_multiple_success = 'Prerender hints have been successfully set for all posts with sufficiently available data.';
		$prerender_no_data = 'There is not enough analytics data for this page to generate accurate prerender hints yet. Please try again soon.';

		$actions = array(
			0 => array( 'create', 'created' ),
			1 => array( 'update', 'updated' ),
			2 => array( 'delete', 'deleted' ),
			3 => array( 'enable', 'enabled' ),
			4 => array( 'disable', 'disabled' ),
			5 => array( 0 => $preconnect_success, 1 => $preconnect_fail ),
			6 => array( 0 => $prerender_single_success, 1 => $prerender_multiple_success, 2 => $prerender_no_data )
		);

		if ( 4 >= $action_code ) {
			if ( 0 === $action_code && 1 === $success_code ) {
				$msg = $dup_hints_alert;
			} else {
				$action = $actions[$action_code];
				$msg = ( $success ) ? "Resource hint $action[1] successfully." : "Failed to $action[0] hint.";
			}
		} else {
			$msg = $actions[$action_code][$success_code];
		}

		return $msg;
	}

	public function insert_hint( $new_hint ) {
		global $wpdb;

		if ( ! isset( $new_hint['url'], $new_hint['hint_type'] ) ) {
			return;
		}

		$args = array(
			'types'   => array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
			'columns' => array(
				'url'          => $new_hint['url'],
				'hint_type'    => $new_hint['hint_type'],
				'status'       => 'enabled',
				'as_attr'      => $new_hint['as_attr'] ?? '',
				'type_attr'    => $new_hint['type_attr'] ?? '',
				'crossorigin'  => $new_hint['crossorigin'] ?? '',
				'created_by'   => $new_hint['current_user'] ?? '',
				'media'        => $new_hint['media'] ?? '',
				'auto_created' => $new_hint['auto_created'] ?? 0
			)
		);

		$args = \apply_filters( 'pprh_dao_insert_hint_schema', $args, $new_hint );

		if ( PPRH_RUNNING_UNIT_TESTS ) {
			return self::create_db_result( true, 0, 0, $new_hint );
		}

		$wpdb->insert( $this->table, $args['columns'], $args['types'] );

		if ( isset( $wpdb->insert_id ) && $wpdb->insert_id > 0 ) {
			$new_hint['id'] = $wpdb->insert_id;
		}

		return self::create_db_result( $wpdb->result, 0, 0, $new_hint );
	}


	public function update_hint( $new_hint, $hint_ids ) {
		global $wpdb;
		$hint_id = (int) $hint_ids;
		$current_user = wp_get_current_user()->display_name;
		$hint_arg = array(
			'url'         => $new_hint['url'],
			'hint_type'   => $new_hint['hint_type'],
			'as_attr'     => $new_hint['as_attr'],
			'type_attr'   => $new_hint['type_attr'],
			'crossorigin' => $new_hint['crossorigin'],
			'media'       => $new_hint['media'],
			'created_by'  => $current_user
		);
		$where = array( 'id' => $hint_id );
		$type_arg = array( '%s', '%s', '%s', '%s', '%s' );

		if ( PPRH_RUNNING_UNIT_TESTS ) {
			return self::create_db_result( true, 1, 0, $new_hint );
		}

		$wpdb->update( $this->table, $hint_arg, $where, $type_arg, array( '%d' ) );

		if ( isset( $wpdb->result ) && $wpdb->insert_id > 0 ) {
			$new_hint['id'] = $hint_id;
		}

		return self::create_db_result( $wpdb->result, 1, 0, $new_hint );
	}

	public function delete_hint( string $hint_ids ) {
		global $wpdb;
		$hint_id_exists = preg_match('/\d/', $hint_ids );

		if ( $hint_id_exists > 0 ) {

			if ( PPRH_RUNNING_UNIT_TESTS ) {
				return self::create_db_result( true, 2, 0, null );
			}

			$wpdb->query( "DELETE FROM $this->table WHERE id IN ($hint_ids)" );
			return self::create_db_result( $wpdb->result, 2, 0, null );
		}

	}

	public function bulk_update( $hint_ids, $op_code ) {
		global $wpdb;
		$action = ( 3 === $op_code ) ? 'enabled' : 'disabled';

		if ( PPRH_RUNNING_UNIT_TESTS ) {
			return self::create_db_result( true, $op_code, 0, null );
		}

		$wpdb->query( $wpdb->prepare(
			"UPDATE $this->table SET status = %s WHERE id IN ($hint_ids)", $action )
		);

		return self::create_db_result( $wpdb->result, $op_code, 0, null );
	}


	public function get_duplicate_hints( $url, $hint_type ) {
		global $wpdb;
		$sql = "SELECT * FROM $this->table WHERE url = %s AND hint_type = %s";

		return $wpdb->get_results( $wpdb->prepare( $sql, $url, $hint_type ), ARRAY_A );
	}



	public static function get_admin_hints() {
		$query = self::get_admin_hints_query();
		return self::get_db_results( $query );
	}

	public static function get_client_hints( $data ) {
		$query = self::get_client_hints_query( $data );
		return self::get_db_results( $query );
	}

	public static function get_admin_hints_query() {
		$table = PPRH_DB_TABLE;
		$sql = "SELECT * FROM $table";
		$query = array(
			'sql'  => $sql,
			'args' => array()
		);

		$req_order_by = strtolower( \esc_sql( $_REQUEST['orderby'] ?? '' ) );
		$req_order = strtoupper( \esc_sql( $_REQUEST['order'] ?? '' ) );
		$order_by = ( 0 < preg_match( '/url|hint_type|status|created_by|post_id/i', $req_order_by ) ) ? $req_order_by : '';
		$order = ( 0 < preg_match( '/ASC|DESC/', $req_order ) ) ? $req_order : '';

		$new_query = \apply_filters( 'pprh_append_admin_sql', $query, $order_by, $order );

		if ( $new_query === $query ) {
			if ( '' === $order_by ) $order_by = 'url';
			if ( '' === $order ) $order = 'ASC';
			$new_query['sql'] .= " ORDER BY $order_by $order";
		}

		return $new_query;
	}


	public static function get_client_hints_query( array $data ) {
		$table = PPRH_DB_TABLE;
		$sql = "SELECT * FROM $table WHERE status = %s";
		$query = array(
			'sql'     => $sql,
			'args'    => array( 'enabled' ),
		);

		return \apply_filters( 'pprh_append_client_sql', $query, $data );
	}

	private static function get_db_results( $query ) {
		global $wpdb;

		if ( ! empty( $query['args'] ) ) {
			$prepared_stmt = $wpdb->prepare( $query['sql'], $query['args'] );
			$results = $wpdb->get_results( $prepared_stmt, ARRAY_A );
		} else {
			$results = $wpdb->get_results( $query['sql'], ARRAY_A );
		}

		return $results;
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

	public function get_table_column() {
		global $wpdb;
		return $wpdb->get_results( "SHOW COLUMNS FROM $this->table LIKE 'auto_created'", ARRAY_A );
	}

	public function drop_table_column() {
		global $wpdb;
		$wpdb->query( "ALTER TABLE $this->table DROP COLUMN auto_created" );
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
            hint_type ENUM('dns-prefetch', 'prefetch', 'prerender', 'preconnect', 'preload') NOT NULL,
            status VARCHAR(55) DEFAULT 'enabled' NOT NULL,
            as_attr VARCHAR(55) DEFAULT '',
            type_attr VARCHAR(55) DEFAULT '',
            crossorigin VARCHAR(55) DEFAULT '',
            media VARCHAR(255) DEFAULT '',
            created_by VARCHAR(55) DEFAULT '' NOT NULL,
			auto_created INT(2) DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id)
        ) $charset;";

		dbDelta( $sql, true );
	}

}
