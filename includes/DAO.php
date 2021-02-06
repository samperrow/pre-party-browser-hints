<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAO {

	// db results
	public function create_db_result( $result, $hint_id, $last_error, $action = '', $new_hint = null ) {
		return (object) array(
			'new_hint'  => $new_hint,
			'db_result' => array(
				'msg'        => self::create_msg( $result, $last_error, $action ),
				'status'     => ( $result ) ? 'success' : 'error',
				'hint_id'    => $hint_id,
				'success'    => $result,
				'last_error' => $last_error
			)
		);
	}

	public static function create_msg( $result, $last_error, $action )  {
		if ( $result ) {
			$action .= 'd';
			$msg = "Resource hint $action successfully.";
		} elseif ( '' !== $last_error ) {
			$msg = $last_error;
		} else {
			$msg = "Failed to $action hint.";
		}

		return $msg;
	}


	public function create_hint( $new_hint ) {
		global $wpdb;
		$current_user = wp_get_current_user()->display_name;
		$auto_created = ( ! empty( $new_hint['auto_created'] ) ? (int) $new_hint['auto_created'] : 0 );

		$args = array(
			'types'   => array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' ),
			'columns' => array(
				'url'          => $new_hint['url'],
				'hint_type'    => $new_hint['hint_type'],
				'status'       => 'enabled',
				'as_attr'      => $new_hint['as_attr'],
				'type_attr'    => $new_hint['type_attr'],
				'crossorigin'  => $new_hint['crossorigin'],
				'created_by'   => $current_user,
				'auto_created' => $auto_created,
			)
		);

		$args = apply_filters( 'pprh_insert_hint_schema', $args, $new_hint );

		$wpdb->insert(
			PPRH_DB_TABLE,
			$args['columns'],
			$args['types']
		);

		return $this->create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, 'create', $new_hint );
	}


	public function update_hint( $new_hint, $hint_id ) {
		global $wpdb;
		$hint_id = (int) $hint_id;

		$wpdb->update(
			PPRH_DB_TABLE,
			array(
				'url'         => $new_hint['url'],
				'hint_type'   => $new_hint['hint_type'],
				'as_attr'     => $new_hint['as_attr'],
				'type_attr'   => $new_hint['type_attr'],
				'crossorigin' => $new_hint['crossorigin'],
			),
			array(
				'id' => $hint_id,
			),
			array( '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		return $this->create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, 'update', $new_hint );
	}

	public function bulk_update( $hint_ids, $action ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		$wpdb->query( $wpdb->prepare(
			"UPDATE $table SET status = %s WHERE id IN ($hint_ids)",
			$action . 'd'
		) );

		return $this->create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, $action, null );
	}

	public function delete_hint( $hint_ids ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;
		$wpdb->query( "DELETE FROM $table WHERE id IN ($hint_ids)" );
		return $this->create_db_result( $wpdb->result, $wpdb->insert_id, $wpdb->last_error, 'delete', null );
	}

	public function remove_prev_auto_preconnects() {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		$wpdb->query(
			$wpdb->prepare( "DELETE FROM $table WHERE auto_created = %d AND hint_type = %s", 1, 'preconnect' )
		);
	}

	public function get_hints( $query = null ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;
		$sql = "SELECT * FROM $table";

		if ( ! empty( $query['sql'] ) ) {
			$sql .= $query['sql'];
		}

		if ( ! empty( $query['args'] ) ) {
			$sql = $wpdb->prepare( $sql, $query['args'] );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	public function get_hints_ordered( $query = null ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;
		$sql = "SELECT * FROM $table";

		if ( defined( 'PPRH_PRO_ABS_DIR' ) ) {
			$sql = apply_filters( 'pprh_dh_append_sql', $sql );
		} elseif ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY url ASC';
		}

//		$query = apply_filters( 'pprh_sh_append_sql', $query );

		if ( ! empty( $query['args'] ) ) {
			$sql = $wpdb->prepare( $sql, $query['args'] );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
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
            status VARCHAR(55) DEFAULT 'enabled' NOT NULL,
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
