<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAO {

	public function code_action_arr( $code ) {
		$actions = array(
			0 => array( 'create', 'created' ),
			1 => array( 'update', 'updated' ),
			2 => array( 'delete', 'deleted' ),
			3 => array( 'enable', 'enabled' ),
			4 => array( 'disable', 'disabled' )
		);

		return $actions[$code];
	}

	// db results
	public function create_db_result( $result, $hint_id, $last_error, $action_code = '', $new_hint = null ) {
		return (object) array(
			'new_hint'  => $new_hint,
			'db_result' => array(
				'msg'        => $this->create_msg( $result, $last_error, $action_code ),
				'status'     => ( $result ) ? 'success' : 'error',
				'hint_id'    => $hint_id,
				'success'    => $result,
				'last_error' => $last_error
			)
		);
	}

	public function create_msg( $result, $last_error, $action_code )  {
		$actions = $this->code_action_arr( $action_code );

		if ( $result ) {
			$msg = "Resource hint $actions[1] successfully.";
		} elseif ( '' !== $last_error ) {
			$msg = $last_error;
		} else {
			$msg = "Failed to $actions[0] hint.";
		}

		return $msg;
	}




	public function insert_hint( $new_hint ) {
		global $wpdb;
		$current_user = wp_get_current_user()->display_name;

		$args = array(
			'types'   => array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
			'columns' => array(
				'url'          => $new_hint['url'],
				'hint_type'    => $new_hint['hint_type'],
				'status'       => 'enabled',
				'as_attr'      => $new_hint['as_attr'],
				'type_attr'    => $new_hint['type_attr'],
				'crossorigin'  => $new_hint['crossorigin'],
				'created_by'   => $current_user,
				'media'        => $new_hint['media']
			)
		);

		$args = apply_filters( 'pprh_ch_insert_hint_schema', $args, $new_hint );

		$wpdb->insert(
			PPRH_DB_TABLE,
			$args['columns'],
			$args['types']
		);

		return $this->create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, 0, $new_hint );
	}


	public function update_hint( $new_hint, $hint_id ) {
		global $wpdb;
		$hint_id = (int) $hint_id;
		$current_user = wp_get_current_user()->display_name;

		$wpdb->update(
			PPRH_DB_TABLE,
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

		return $this->create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, 1, $new_hint );
	}

	public function delete_hint( $hint_ids ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;
		$hint_id_exists = preg_match('/\d/', $hint_ids );

		if ( $hint_id_exists > 0 ) {
			$wpdb->query( "DELETE FROM $table WHERE id IN ($hint_ids)" );
			return $this->create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, 2, null );
		}

		else {
			return $this->create_db_result( false, null, 'No hint IDs to delete.', 2, null );
		}
	}

	public function bulk_update( $hint_ids, $code ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		$action = ( 3 === $code ) ? 'enabled' : 'disabled';

		$wpdb->query( $wpdb->prepare(
			"UPDATE $table SET status = %s WHERE id IN ($hint_ids)",
			$action . 'd'
		) );

		return $this->create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, $code, null );
	}


	public function get_all_hints( $query_code = null ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;
		$sql = "SELECT * FROM $table";

		$query = $this->parse_query_code( $sql, $query_code );

		if ( ! empty( $query['args'] ) ) {
			$prepared_stmt = $wpdb->prepare( $query['sql'], $query['args'] );
			$results = $wpdb->get_results( $prepared_stmt, ARRAY_A );
		} else {
			$results = $wpdb->get_results( $query['sql'], ARRAY_A );
		}

		return $results;
	}

	private function parse_query_code( $sql, $query_code ) {
		$query = array( 'sql' => $sql );

		if ( 1 === $query_code ) {
			$query['sql'] .= ' WHERE status = %s';
			$query['args'] = array( 'enabled' );
		} elseif ( 2 === $query_code ) {
			$query['sql'] .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$query['sql'] .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} elseif ( 3 === $query_code ) {
			$query['sql'] .= ' ORDER BY url ASC';
		}

		return $query;
	}

	public function parse_query_code( $sql, $query_code ) {

		$query = array( 'sql' => $sql );

		if ( 1 === $query_code ) {
			$query['sql'] .= ' WHERE status = %s';
			$query['args'] = array( 'enabled' );
		} elseif ( 2 === $query_code ) {
			$query['sql'] .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$query['sql'] .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} elseif ( 3 === $query_code ) {
			$query['sql'] .= ' ORDER BY url ASC';
		}

		return $query;
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
            hint_type ENUM('dns-prefetch', 'prefetch', 'prerender', 'preconnect', 'preload') NOT NULL,
            status VARCHAR(55) DEFAULT 'enabled' NOT NULL,
            as_attr VARCHAR(55) DEFAULT '',
            type_attr VARCHAR(55) DEFAULT '',
            crossorigin VARCHAR(55) DEFAULT '',
            media VARCHAR(255) DEFAULT '',
            created_by VARCHAR(55) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset;";

		dbDelta( $sql, true );
	}

}
