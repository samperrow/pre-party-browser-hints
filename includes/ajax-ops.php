<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax_Ops {

	public $results = array(
		'post_id'   => '',
		'query'     => array(),
		'new_hints' => array(),
	);

	private $data = array();

	public function __construct() {
		add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
    }

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {

			check_ajax_referer( 'pprh_table_nonce', 'val' );
			$data_obj = json_decode( wp_unslash( $_POST['pprh_data'] ) );

			if ( ! is_object( $data_obj ) ) {
				wp_die();
			}

			$action = $data_obj->action;
			$data = array( $data_obj );

			include_once PPRH_ABS_DIR . '/includes/utils.php';
			include_once PPRH_ABS_DIR . '/includes/create-hints.php';
			include_once PPRH_ABS_DIR . '/includes/display-hints.php';

			$this->results['query'] = $this->handle_action( $data, $action );
			$display_hints = new Display_Hints();
			$display_hints->ajax_response( $this->results );
			wp_die();
		}
	}

	private function handle_action( $data, $action ) {
		$wp_db = null;
		if ( preg_match( '/create|update|delete/', $action ) ) {
			 $wp_db = $this->{$action . '_hint'}( $data, $action );
		} elseif ( preg_match( '/enable|disable/', $action ) ) {
			$wp_db = $this->bulk_update( $data[0], $action );
		}
		return $wp_db;
	}

	private function create_hint( $data ) {
		define( 'CREATING_HINT', true );
		$new_hint = new Create_Hints( $data );
		return $new_hint->results['query'];
	}

	private function update_hint( $data, $action ) {
		global $wpdb;
		$data = $data[0];
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

		return Utils::get_wpdb_result( $wpdb, $action );
	}

	private function delete_hint( $data, $action ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;
		$data = $data[0];

		if ( ! is_array( $data->hint_ids ) ) {
			return false;
		}

		$concat_ids = implode( ',', array_map( 'absint', $data->hint_ids ) );
		$wpdb->query( "DELETE FROM $table WHERE id IN ($concat_ids)" );
		return Utils::get_wpdb_result( $wpdb, $action );
	}

	private function bulk_update( $data, $action ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;
		$concat_ids = implode( ',', array_map( 'absint', $data->hint_ids ) );

		$wpdb->query( $wpdb->prepare(
			"UPDATE $table SET status = %s WHERE id IN ($concat_ids)",
			$action
		) );

		return Utils::get_wpdb_result( $wpdb, $action );
	}
}

if ( is_admin() ) {
	new Ajax_Ops();
}
