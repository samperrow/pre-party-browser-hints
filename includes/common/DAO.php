<?php

namespace PPRH;

use PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAO {

	private static $table = PPRH_DB_TABLE;

//	public function __construct() {}

	public function get_table() {
		return self::$table;
	}

	public static function create_db_result( bool $success, int $op_code, int $success_code, array $new_hint = null ):\stdClass {
		$msg = self::get_msg( $success, $op_code, $success_code );
		$msg .= ' If you have an active caching system, it is recommended that you clear your cache if you are having difficulty viewing these changes.';

		return (object) array(
			'new_hint'  => $new_hint ?? null,
			'db_result' => array(
				'msg'    => $msg,
				'status' => $success,
			)
		);
	}

	private static function get_msg( bool $success, int $op_code, int $success_code ):string {
		$dup_hints_alert    = 'A duplicate hint exists!';

		$preconnect_success = 'Preconnect resource hints were created successfully.';
		$preconnect_fail    = 'Failed to reset preconnect hint data. Please refresh the page and try again.';

		$preload_success    = 'Preload resource hints were created successfully.';
		$preload_fail       = 'Failed to reset preload hint data. Please refresh the page and try again.';

		$prerender_single_success   = 'Prerender hint successfully created.';
		$prerender_multiple_success = 'Prerender hints have been successfully set for all posts with sufficiently available data.';
		$prerender_no_data          = 'There is not enough analytics data for this page to generate accurate prerender hints yet. Please try again soon.';

		$actions = array(
			0 => array( 'create', 'created' ),
			1 => array( 'update', 'updated' ),
			2 => array( 'delete', 'deleted' ),
			3 => array( 'enable', 'enabled' ),
			4 => array( 'disable', 'disabled' ),
			5 => array( 0 => $preconnect_success, 1 => $preconnect_fail ),
			6 => array( 0 => $preload_success, 1 => $preload_fail ),
			7 => array( 0 => $prerender_single_success, 1 => $prerender_multiple_success, 2 => $prerender_no_data )
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

	public function handle_wpdb_result( $wpdb_result, $wpdb_last_error, array $new_hint = array() ):array {
		$success = ( is_bool( $wpdb_result ) ) ? $wpdb_result : false;

		if ( ! $success ) {
			Utils::log_error( $wpdb_last_error );
		}

		return array(
			'success' => $success,
			'new_hint' => $new_hint
		);
	}

	public function insert_hint( array $new_hint ) {
		global $wpdb;
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
			return $this->handle_wpdb_result( true, '', $new_hint );
		}

		$wpdb->insert( self::$table, $args['columns'], $args['types'] );

		if ( isset( $wpdb->insert_id ) && $wpdb->insert_id > 0 ) {
			$new_hint['id'] = $wpdb->insert_id;
		}

		return $this->handle_wpdb_result( $wpdb->result, $wpdb->last_error, $new_hint );
	}


	public function update_hint( $new_hint, $hint_ids ) {
		global $wpdb;
		$hint_id      = (int) $hint_ids;
		$current_user = \wp_get_current_user()->display_name;
		$hint_arg = array(
			'url'         => $new_hint['url'],
			'hint_type'   => $new_hint['hint_type'],
			'as_attr'     => $new_hint['as_attr'],
			'type_attr'   => $new_hint['type_attr'],
			'crossorigin' => $new_hint['crossorigin'],
			'media'       => $new_hint['media'],
			'created_by'  => $current_user
		);
		$where    = array( 'id' => $hint_id );
		$type_arg = array( '%s', '%s', '%s', '%s', '%s' );

		if ( PPRH_RUNNING_UNIT_TESTS ) {
			return $this->handle_wpdb_result( true, '', $new_hint );
		}

		$wpdb->update( self::$table, $hint_arg, $where, $type_arg, array( '%d' ) );

		if ( isset( $wpdb->result ) && $wpdb->insert_id > 0 ) {
			$new_hint['id'] = $hint_id;
		}

		return $this->handle_wpdb_result( $wpdb->result, $wpdb->last_error, $new_hint );
	}

	public function delete_hint( string $hint_ids ) {
		global $wpdb;
		$table = self::$table;
		$valid_hint_id = ( 0 < preg_match( '/\d/', $hint_ids ) );

		if ( $valid_hint_id ) {
			$wpdb->query( "DELETE FROM $table WHERE id IN ($hint_ids)" );
			return $this->handle_wpdb_result( $wpdb->result, $wpdb->last_error );
		}

		return $this->handle_wpdb_result( false, 'Error in DAO::delete_hint().' );
	}

	public function bulk_update( $hint_ids, $op_code ) {
		global $wpdb;
		$table = self::$table;
		$action = ( 3 === $op_code ) ? 'enabled' : 'disabled';

		if ( PPRH_RUNNING_UNIT_TESTS ) {
			return $this->handle_wpdb_result( true, '' );
		}

		$wpdb->query( $wpdb->prepare(
			"UPDATE $table SET status = %s WHERE id IN ($hint_ids)", $action )
		);

		return $this->handle_wpdb_result( $wpdb->result, $wpdb->last_error );
	}


	public function get_duplicate_hints( string $url, string $hint_type, int $op_code, string $hint_ids ):array {
		global $wpdb;
		$table = self::$table;
		$sql = "SELECT * FROM $table WHERE url = %s AND hint_type = %s";

		if ( 1 === $op_code && ! empty( $hint_ids ) ) {			// hint is being updated, so ignore the existing one.
			$sql .= " AND id != %d";
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $url, $hint_type, $hint_ids ), ARRAY_A );
		} else {
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $url, $hint_type ), ARRAY_A );
		}

		if ( ! $wpdb->result ) {
			Utils::log_error( $wpdb->last_error );
		}

		return $results;
	}

	public static function get_pprh_hints( bool $is_admin, array $data = array() ):array {
		if ( $is_admin ) {
			$query = self::get_admin_hints_query();
		} else {
			$query = self::get_client_hints_query( $data );
		}

		return self::get_db_results( $query );
	}

	public static function get_admin_hints_query() {
		$table = PPRH_DB_TABLE;
		$sql   = "SELECT * FROM $table";
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


	public static function get_client_hints_query( array $data ) {
		$table = PPRH_DB_TABLE;
		$sql   = "SELECT * FROM $table WHERE status = %s";
		$query = array(
			'sql'     => $sql,
			'args'    => array( 'enabled' ),
		);

		return \apply_filters( 'pprh_append_client_sql', $query, $data );
	}

	private static function get_db_results( array $query ):array {
		global $wpdb;

		if ( ! empty( $query['args'] ) ) {
			$prepared_stmt = $wpdb->prepare( $query['sql'], $query['args'] );
			$results       = $wpdb->get_results( $prepared_stmt, ARRAY_A );
		} else {
			$results = $wpdb->get_results( $query['sql'], ARRAY_A );
		}

		if ( ! $wpdb->result ) {
			Utils::log_error( $wpdb->last_error );
		}

		return $results;
	}


	public function get_multisite_tables() {
		global $wpdb;
		$blog_table     = $wpdb->base_prefix . 'blogs';
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

		if ( ! $wpdb->result ) {
			Utils::log_error( $wpdb->last_error );
		}

		return $ms_table_names;
	}

	public function get_table_column() {
		global $wpdb;
		$table = self::$table;
		$results = $wpdb->get_results( "SHOW COLUMNS FROM $table LIKE 'auto_created'", ARRAY_A );

		if ( ! $wpdb->result ) {
			Utils::log_error( $wpdb->last_error );
		}

		return $results;
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

		if ( ! $wpdb->result ) {
			Utils::log_error( $wpdb->last_error );
		}
	}

	public static function delete_auto_created_hints( string $hint_type, string $post_id ):bool {
		global $wpdb;
		$table = PPRH_DB_TABLE;
		$query = array(
			'sql'  => "DELETE FROM $table WHERE hint_type = %s AND auto_created = %d",
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
			Utils::log_error( $wpdb->last_error );
			return false;
		}

		return true;
	}

}
