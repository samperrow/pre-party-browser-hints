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
        add_action( 'wp_ajax_pprh_update_url', array( $this, 'pprh_update_url' ) );
    }

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {

			check_ajax_referer( 'pprh_table_nonce', 'val' );
			$data = json_decode( wp_unslash( $_POST['pprh_data'] ), false );
			$this->data = $data;
			$action = $data->action;
			$post_id = ( isset( $data->post_id ) ) ? $data->post_id : Utils::getPostID();

			if ( 'create' === $action ) {
				define( 'CREATING_HINT', true );
				include_once PPRH_PLUGIN_DIR . '/class-pprh-create-hints.php';
				$new_hint = new Create_Hints( $data );
				$this->results = $new_hint->results;
			} elseif ( 'update' === $action ) {
				$this->update_hint();
			} elseif ( 'reset' === $action ) {
				$this->reset_post_preconnects( $post_id );
			} else {
				$this->bulk_update();
			}

			include_once PPRH_PLUGIN_DIR . '/class-pprh-display-hints.php';

			$msg = ( 'success' === $this->results['result'] ) ? ' Resource hints ' . $action . 'd successfully.' : '';

			$this->results['action'] = $action;
			$this->results['msg'] = $msg . $this->results['msg'];

			$display_hints = new Display_Hints();
			$display_hints->ajax_response( $post_id, $this->results );
			wp_die();
		}
	}

	private function reset_post_preconnects( $post_id ) {
		update_post_meta( $post_id, 'pprh_reset_post_preconnects', 'true' );
		$this->results['result'] = 'success';

	}

	private function update_hint() {
		global $wpdb;
		$data = $this->data;
		$hint_id = (int) $data->hint_id;

		$wpdb->update(
			PPRH_DB_TABLE,
			array(
				'url'         => $data->url[0],
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
		$data = $this->data;
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
