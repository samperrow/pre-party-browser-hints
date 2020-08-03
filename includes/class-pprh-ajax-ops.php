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
		'post_id' => '',
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

			do_action( 'pprh_load_ajax_ops_child' );
			include_once PPRH_ABS_DIR . '/includes/class-pprh-utils.php';
			include_once PPRH_ABS_DIR . '/includes/class-pprh-create-hints.php';
			include_once PPRH_ABS_DIR . '/includes/class-pprh-display-hints.php';

			if ( 'create' === $action ) {
				define( 'CREATING_HINT', true );
				$new_hint = new Create_Hints( $this->data );
				$this->results = $new_hint->results;
			} elseif ( 'update' === $action ) {
				$this->update_hint();
			} elseif ( 'reset_post_prec' === $action ) {
				$this->results = apply_filters( 'pprh_ajax_ops_reset_post_prec', $this->data );
			} elseif ( 'reset_post_prerender' === $action ) {
				$this->results = apply_filters( 'pprh_ajax_ops_reset_post_ga_prerender', $this->data );
			}


			elseif ( preg_match( '/disable|enable|delete/', $action ) ) {
				$this->bulk_update();
			}

			$this->results['post_id'] = $data->post_id;
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

		$this->results = $this->set_str( $wpdb->result, 'Resource hints updated successfully.' );
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

		$msg = 'Resource hints ' . $data->action . 'd successfully.';
		$this->results = $this->set_str( $wpdb->result, $msg );
	}

	protected function set_str( $result, $msg ) {
		return array(
			'msg'    => ( $result ) ? $msg : 'Error saving data.',
			'result' => ( $result ) ? 'success' : 'failure',
		);
	}

}

if ( is_admin() ) {
	new Ajax_Ops();
}
