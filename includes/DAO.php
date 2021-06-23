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
	public static function create_db_result( $result, $hint_id, $msg = '', $op_code = '', $new_hint = null ) {
		$actions = self::code_action_arr( $op_code );
		$new_msg = ( '' === $msg ) ? self::create_msg( $result, $actions ) : $msg;

		return (object) array(
			'new_hint'  => $new_hint,
			'db_result' => array(
				'msg'        => $new_msg,
				'status'     => ( $result ) ? 'success' : 'error',
				'hint_id'    => $hint_id,
				'success'    => $result,
				'last_error' => $msg
			)
		);
	}

	public static function create_msg( $result, $actions ) {
		return ( $result ) ? "Resource hint $actions[1] successfully." : "Failed to $actions[0] hint.";
	}

	public static function code_action_arr( $op_code ) {
		$actions = array(
			0 => array( 'create', 'created' ),
			1 => array( 'update', 'updated' ),
			2 => array( 'delete', 'deleted' ),
			3 => array( 'enable', 'enabled' ),
			4 => array( 'disable', 'disabled' )
		);

		return $actions[$op_code];
	}

	public function insert_hint( $new_hint ) {
		global $wpdb;

		if ( ! is_array( $new_hint ) )  {
			return;
		}

		$args = array(
			'types'   => array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
			'columns' => array(
				'url'          => $new_hint['url'],
				'hint_type'    => $new_hint['hint_type'],
				'status'       => 'enabled',
				'as_attr'      => $new_hint['as_attr'],
				'type_attr'    => $new_hint['type_attr'],
				'crossorigin'  => $new_hint['crossorigin'],
				'created_by'   => $new_hint['current_user'],
				'media'        => $new_hint['media']
			)
		);

		$args = \apply_filters( 'pprh_dao_insert_hint_schema', $args, $new_hint );

		$wpdb->insert(
			$this->table,
			$args['columns'],
			$args['types']
		);

		return self::create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, 0, $new_hint );
	}


	public function update_hint( $new_hint, $hint_ids ) {
		global $wpdb;
		$hint_id = (int) $hint_ids;
		$current_user = wp_get_current_user()->display_name;

		$wpdb->update(
			$this->table,
			array(
				'url'         => $new_hint['url'],
				'hint_type'   => $new_hint['hint_type'],
				'as_attr'     => $new_hint['as_attr'],
				'type_attr'   => $new_hint['type_attr'],
				'crossorigin' => $new_hint['crossorigin'],
				'media'       => $new_hint['media'],
				'created_by'  => $current_user,
			),
			array(
				'id' => $hint_id,
			),
			array( '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		return self::create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, 1, $new_hint );
	}

	public function delete_hint( $hint_ids ) {
		global $wpdb;
		$hint_id_exists = preg_match('/\d/', $hint_ids );

		if ( $hint_id_exists > 0 ) {
			$wpdb->query( "DELETE FROM $this->table WHERE id IN ($hint_ids)" );
			return self::create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, 2, null );
		}

		return self::create_db_result( false, null, 'No hint IDs to delete.', 2, null );
	}

	public function bulk_update( $hint_ids, $code ) {
		global $wpdb;
		$action = ( 3 === $code ) ? 'enabled' : 'disabled';

		$wpdb->query( $wpdb->prepare(
			"UPDATE $this->table SET status = %s WHERE id IN ($hint_ids)",
			$action
		) );

		return self::create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, $code, null );
	}


	public function get_duplicate_hints( $url, $hint_type ) {
		global $wpdb;
		$sql = "SELECT * FROM $this->table WHERE url = %s AND hint_type = %s";

		return $wpdb->get_results( $wpdb->prepare( $sql, $url, $hint_type ), ARRAY_A );
	}

	public function get_pprh_hints( $is_admin ) {
		if ( $is_admin )  {
			$query = $this->get_admin_hints_query();
		} else {
			$query = $this->get_client_hints_query();
		}

		return $this->get_db_results( $query );
	}

	public function get_admin_hints_query() {
		$sql = "SELECT * FROM $this->table";
		$query = array(
			'sql' => $sql,
			'args' => array()
		);

		$new_query = \apply_filters( 'pprh_append_admin_sql', $query );

		if ( ! empty( $_REQUEST['orderby'] ) && ! empty( $_REQUEST['order'] ) ) {
			$valid_orderby_columns = array( 'url', 'hint_type', 'status', 'created_by', 'post_id' );
			$valid_order_columns = array( 'asc', 'desc' );
			$valid_orderby = Utils::string_in_array( $valid_orderby_columns, $_REQUEST['orderby'] );
			$valid_order = Utils::string_in_array( $valid_order_columns, $_REQUEST['order'] );

			if ( $valid_orderby && $valid_order ) {
				$orderby = esc_sql( $_REQUEST['orderby'] );
				$order = esc_sql( $_REQUEST['order'] );
				$new_query['sql'] .= " ORDER BY $orderby $order";
			}
		}

		elseif ( $new_query === $query ) {
			$new_query['sql'] .= ' ORDER BY url ASC';
		}

		return $new_query;
	}


	public function get_client_hints_query() {
		$sql = "SELECT * FROM $this->table WHERE status = %s";
		$query = array(
			'sql'  => $sql,
			'args' => array( 'enabled' )
		);

		return \apply_filters( 'pprh_append_client_sql', $query );
	}

	private function get_db_results( $query ) {
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
