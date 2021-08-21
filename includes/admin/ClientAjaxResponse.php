<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ClientAjaxResponse extends ClientAjaxInit {

	protected function protected_post_domain_names():array {
		return $this->post_domain_names( $_POST['pprh_data'] );
	}

	public function post_domain_names( $pprh_data ):array {
		if ( ! is_array( $pprh_data ) ) {
			$pprh_data = Utils::json_to_array( $pprh_data );
		}

		$hints = $pprh_data['hints'] ?? array();
		$hints_created = array();

		if ( Utils::isArrayAndNotEmpty( $hints ) && Utils::isArrayAndNotEmpty( $pprh_data ) ) {
			$hints_created = $this->get_hint_results( $pprh_data );
		}

		$this->update_options( $pprh_data );

		return $hints_created;
	}

	// tested
	public function get_hint_results( array $hint_data ):array {
		$dao_ctrl = new DAOController();
		$results = array();

		foreach ( $hint_data['hints'] as $new_hint ) {
			$new_hint['op_code']      = 0;
			$new_hint['auto_created'] = 1;

			if ( isset( $hint_data['post_id'] ) ) {
				$new_hint['post_id'] = $hint_data['post_id'];
			}

			$result = $dao_ctrl->hint_controller( $new_hint );

			if ( is_object( $result ) && isset( $result->db_result['status'] ) && $result->db_result['status'] ) {
				$results[] = $result;
			}
		}

		return $results;
	}

	private function update_options( array $pprh_data ):bool {
//		$hint_type = $this->hint_type;

		$updated = \apply_filters( 'pprh_pro_ajax_update_options', $pprh_data, $this->hint_type );

		if ( is_array( $updated ) || false === $updated ) {
			return Utils::update_option( 'pprh_preconnect_set', 'true' );
		}

		return false;
	}

}
