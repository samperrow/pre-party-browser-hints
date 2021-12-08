<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAO {

	public function insert_hint( array $new_hint ):\stdClass {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		$args = array(
			'types'   => array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' ),
			'columns' => array(
				'url'          => $new_hint['url'],
				'hint_type'    => $new_hint['hint_type'],
				'status'       => 'enabled',
				'as_attr'      => $new_hint['as_attr'] ?? '',
				'type_attr'    => $new_hint['type_attr'] ?? '',
				'crossorigin'  => $new_hint['crossorigin'] ?? '',
				'media'        => $new_hint['media'] ?? '',
				'created_by'   => $new_hint['current_user'] ?? '',
				'auto_created' => $new_hint['auto_created'] ?? 0
			)
		);

		$args = \apply_filters( 'pprh_dao_insert_hint_schema', $args, $new_hint );

		if ( PPRH_RUNNING_UNIT_TESTS ) {
			return self::create_db_result( '', true, 0, $new_hint );
		}

		try {
			$wpdb->insert( $table, $args['columns'], $args['types'] );
			$new_hint['id'] = ( isset( $wpdb->insert_id ) && $wpdb->insert_id > 0 ) ? $wpdb->insert_id : 0;
			return self::create_db_result( $wpdb->last_error, $wpdb->result, 0, $new_hint );
		} catch ( \Exception $e ) {
			return self::create_db_result( $e->getMessage(), false, 0, $new_hint );
		}
	}


	public function update_hint( $new_hint, $hint_ids ):\stdClass {
		global $wpdb;
		$hint_id      = (int) $hint_ids;
		$current_user = \wp_get_current_user()->display_name;
		$hint_arg = array(
			'url'          => $new_hint['url'],
			'hint_type'    => $new_hint['hint_type'],
			'as_attr'      => $new_hint['as_attr'],
			'type_attr'    => $new_hint['type_attr'],
			'crossorigin'  => $new_hint['crossorigin'],
			'media'        => $new_hint['media'],
			'created_by'   => $current_user,
			'auto_created' => 0
		);
		$where    = array( 'id' => $hint_id );
		$type_arg = array( '%s', '%s', '%s', '%s', '%s' );

		try {
			$wpdb->update( PPRH_DB_TABLE, $hint_arg, $where, $type_arg, array( '%d' ) );
			$new_hint['id'] = ( isset( $wpdb->insert_id ) && $wpdb->insert_id > 0 ) ? $wpdb->insert_id : 0;
			return self::create_db_result( $wpdb->last_error, $wpdb->result, 1, $new_hint );
		} catch ( \Exception $e ) {
			return self::create_db_result( $e->getMessage(), false, 0, $new_hint );
		}
	}

	public function delete_hint( string $hint_ids ) {
		global $wpdb;
		$pprh_table = PPRH_DB_TABLE;
		$valid_hint_id = ( 0 < preg_match( '/\d/', $hint_ids ) );

		if ( $valid_hint_id ) {
			$wpdb->query( "DELETE FROM $pprh_table WHERE id IN ($hint_ids)" );
			return self::create_db_result( $wpdb->last_error, $wpdb->result, 2, array() );
		}

		return self::create_db_result( 'Invalid hint ID.', false, 2, array() );
	}

	public function bulk_update( $hint_ids, $op_code ) {
		global $wpdb;
		$pprh_table = PPRH_DB_TABLE;
		$action = ( 3 === $op_code ) ? 'enabled' : 'disabled';

		$wpdb->query( $wpdb->prepare(
			"UPDATE $pprh_table SET status = %s WHERE id IN ($hint_ids)", $action )
		);

		return self::create_db_result( $wpdb->last_error, $wpdb->result, $op_code, array() );
	}


	public function get_duplicate_hints( string $url, string $hint_type, int $op_code, string $hint_ids ):array {
		global $wpdb;
		$pprh_table = PPRH_DB_TABLE;
		$sql = "SELECT * FROM $pprh_table WHERE url = %s AND hint_type = %s";

		if ( 1 === $op_code && ! empty( $hint_ids ) ) {			// hint is being updated, so ignore the existing one.
			$sql .= " AND id != %d";
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $url, $hint_type, $hint_ids ), ARRAY_A );
		} else {
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $url, $hint_type ), ARRAY_A );
		}

		return $results;
	}

	public static function get_admin_hints() {
		$query = self::get_admin_hints_query();
		return self::get_db_results( $query );
	}

	public static function get_admin_hints_query() {
		$pprh_table = PPRH_DB_TABLE;
		$sql   = "SELECT * FROM $pprh_table";
		$query = array(
			'sql'  => $sql,
			'args' => array()
		);

		$req_order_by = strtolower( \esc_sql( $_REQUEST['orderby'] ?? '' ) );
		$req_order    = strtoupper( \esc_sql( $_REQUEST['order'] ?? '' ) );
		$order_by     = ( 0 < preg_match( '/url|hint_type|status|created_by|post_id/i', $req_order_by ) ) ? $req_order_by : '';
		$order        = ( 0 < preg_match( '/ASC|DESC/', $req_order ) ) ? $req_order : '';

		$new_query = \apply_filters( 'pprh_append_admin_sql', $query, $order_by, $order );

		if ( $new_query === $query ) {
			if ( '' === $order_by ) {
				$order_by = 'url';
			}
			if ( '' === $order ) {
				$order = 'ASC';
			}
			$new_query['sql'] .= " ORDER BY $order_by $order";
		}

		return $new_query;
	}


	public static function get_client_hints( array $client_data ) {
		$query = self::get_client_hints_query( $client_data );
		return self::get_db_results( $query );
	}

	public static function get_client_hints_query( array $client_data ) {
		$pprh_table = PPRH_DB_TABLE;
		$sql   = "SELECT * FROM $pprh_table WHERE status = %s";
		$query = array(
			'sql'  => $sql,
			'args' => array( 'enabled' ),
		);

		return \apply_filters( 'pprh_append_client_sql', $query, $client_data );
	}

	private static function get_db_results( array $query ):array {
		global $wpdb;

		if ( ! empty( $query['args'] ) ) {
			$prepared_stmt = $wpdb->prepare( $query['sql'], $query['args'] );
			$results       = $wpdb->get_results( $prepared_stmt, ARRAY_A );
		} else {
			$results = $wpdb->get_results( $query['sql'], ARRAY_A );
		}

		return $results;
	}

	public static function get_all_db_tables( bool $is_multisite ) {
		$db_tables = array();

		if ( $is_multisite ) {
			$db_tables = self::get_multisite_tables();
		}

		$db_tables[] = PPRH_DB_TABLE;
		return $db_tables;
	}

	private static function get_multisite_tables():array {
		global $wpdb;
		$blog_table     = $wpdb->base_prefix . 'blogs';
		$ms_table_names = array();
		$multisite_table_exists = self::check_for_multisite_table( $blog_table );

		if ( ! $multisite_table_exists ) {
			return array();
		}

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

	private static function check_for_multisite_table( string $blog_table ):bool {
		global $wpdb;

		$blog_tables = $wpdb->get_results(
			$wpdb->prepare( "SHOW TABLES LIKE %s", $blog_table )
		);

		return count( $blog_tables ) > 0;
	}

	public static function create_table( $table_name ) {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		if ( ! function_exists( 'dbDelta' ) ) {
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$sql = "CREATE TABLE {$table_name} (
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

		\dbDelta( array( $sql ), true );
	}

	public static function delete_auto_created_hints( string $hint_type, string $post_id ):bool {
		global $wpdb;
		$pprh_table = PPRH_DB_TABLE;
		$query = array(
			'sql'  => "DELETE FROM $pprh_table WHERE hint_type = %s AND auto_created = %d",
			'args' => array( $hint_type, 1 )
		);

		$query = \apply_filters( 'pprh_delete_auto_created_hints', $query, $post_id );

		if ( PPRH_RUNNING_UNIT_TESTS ) {
			return true;
		}

		$wpdb->query(
			$wpdb->prepare( $query['sql'], $query['args'] )
		);

		$success = ( is_bool( $wpdb->result ) ) ? $wpdb->result : false;

		if ( ! $success ) {
			return false;
		}

		return true;
	}

	/**
	 * UTIL methods below
	 */
	public static function create_db_result( string $wpdb_last_error, bool $wpdb_result, int $op_code, array $new_hint = array() ):\stdClass {
		$success = ( empty( $wpdb_last_error ) && $wpdb_result );
		$msg = self::get_msg( $success, $op_code, 0 );
		$msg .= ( $success ) ? ' Plear your cache if you are having difficulty viewing these changes.' : " Error: $wpdb_last_error";

		return (object) array(
			'new_hint'  => $new_hint,
			'db_result' => array(
				'msg'    => $msg,
				'status' => $success,
			)
		);
	}

	private static function get_msg( bool $success, int $op_code, int $success_code ):string {
		$dup_hints_alert    = 'A duplicate hint exists!';

		$actions = array(
			0 => array( 'create', 'created' ),
			1 => array( 'update', 'updated' ),
			2 => array( 'delete', 'deleted' ),
			3 => array( 'enable', 'enabled' ),
			4 => array( 'disable', 'disabled' )
		);

		if ( 400 === $success_code ) {
			$msg = 'Invalid API key. Please verify your API key and try again.';
		} elseif ( 429 === $success_code ) {
			$msg = 'API quota limit exceeded. Please wait a few moments and try again.';
		}

		elseif ( 4 >= $op_code ) {
			if ( 0 === $op_code && 1 === $success_code ) {
				$msg = $dup_hints_alert;
			} else {
				$action = $actions[ $op_code ];
				$msg    = ( $success ) ? "Resource hint $action[1] successfully." : "Failed to $action[0] hint.";
			}
		} else {
			$msg = $actions[ $op_code ][ $success_code ];
		}

		return $msg;
	}

}
