<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax_Ops {

	public $results = array(
		'action' => '',
		'result' => '',
		'msg'    => '',
	);

	private $data = array();

	public function __construct() {
		add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
    }

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {

			check_ajax_referer( 'pprh_table_nonce', 'val' );
			$data = (object) json_decode( wp_unslash( $_POST['pprh_data'] ), true );
			$this->data = array( $data );
			$action = $data->action;

			include_once PPRH_ABS_DIR . '/includes/class-pprh-utils.php';
			include_once PPRH_ABS_DIR . '/includes/class-pprh-create-hints.php';
			include_once PPRH_ABS_DIR . '/includes/class-pprh-display-hints.php';

			if ( 'create' === $action ) {
				define( 'CREATING_HINT', true );
				$new_hint = new Create_Hints( $this->data );
				$this->results = $new_hint->results;
			} elseif ( 'update' === $action ) {
				$this->update_hint();
			} else {
				$this->bulk_update();
			}

			$msg = ( 'success' === $this->results['result'] ) ? ' Resource hints ' . $action . 'd successfully.' : '';

			$this->results['action'] = $action;
			$this->results['msg'] = $msg . $this->results['msg'];

			$display_hints = new Display_Hints();
			$display_hints->ajax_response( $this->results );
			wp_die();
		}
	}

	private function update_hint() {
		global $wpdb;
		$data = $this->data[0];
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
		$this->results['result'] = ( $wpdb->result ) ? 'success' : 'failure';
	}

	private function bulk_update() {
		global $wpdb;
		$data = $this->data[0];
		$table = PPRH_DB_TABLE;
		$concat_ids = implode( ',', array_map( 'absint', $data->hint_ids ) );

		if ( 'delete' === $data->action ) {
			$sql = "DELETE FROM $table WHERE id IN ($concat_ids)";
		} elseif ( 'enable' === $data->action || 'disable' === $data->action ) {
			$sql = $wpdb->prepare(
				"UPDATE $table SET status = %s WHERE id IN ($concat_ids)",
				$data->action . 'd'
			);
		}

		if ( ! empty( $sql ) ) {
			$wpdb->query( $sql );
		}

		$this->results['result'] = ( $wpdb->result ) ? 'success' : 'failure';
	}

}

if ( is_admin() ) {
	new Ajax_Ops();
}
