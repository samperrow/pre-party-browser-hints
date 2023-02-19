<?php

namespace PPRH;

use PPRH\DAO\DAOPro;
use PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Prerender {

	private $meta_key;
	private $show_posts_on_front;

	protected $prerender_results;

	public $prerender_auto_reset_days;

	public function __construct( bool $show_posts_on_front ) {
		$this->meta_key                  = 'pprh_pro_prerender_data';
		$this->prerender_auto_reset_days = \PPRH\Utils\Utils::get_json_option_value( 'pprh_pro_options', 'prerender_auto_reset_days' );
		$transient                       = \get_transient( 'pprh_pro_prerender_reset' );
		$this->show_posts_on_front       = $show_posts_on_front;
		$this->check_transient( $transient );
	}

	public function check_transient( $transient ):bool {
		if ( empty( $transient ) ) {
			$this->prerender_results = $this->prerender_config( 'global' );
			\add_action( 'pprh_notice', array( $this, 'add_prerender_notice' ) );
			\set_transient( 'pprh_pro_prerender_reset', 'true', ( $this->prerender_auto_reset_days * DAY_IN_SECONDS ) );
			return true;
		}

		return false;
	}

	public function prerender_config( string $admin_post_id = '' ):array {
		$db_results = array();

		if ( '' === $admin_post_id ) {
			$admin_post_id = UtilsPro::get_admin_post_id();
		}

		\PPRH\DAO::delete_auto_created_hints( 'prerender', $admin_post_id );
		$meta_values = $this->get_meta_values( $admin_post_id, $this->meta_key, $this->show_posts_on_front );

		if ( empty( $meta_values ) ) {
			$db_results[] = $this->get_response_object( array() );
		} else {
			$db_results = $this->create_prerender_hints( $meta_values );
		}

		return $db_results;
	}

	public function create_prerender_hints( array $postmeta_arr ):array {
		$hint_ctrl = new \PPRH\HintController();
		$db_results = array();

		foreach ( $postmeta_arr as $postmeta ) {
			if ( isset( $postmeta->post_id ) ) {

				$referer_data = $this->get_most_common_referer_data( $postmeta );
				$raw_hint     = $this->create_prerender_hint( $referer_data );

				if ( Utils::isArrayAndNotEmpty( $raw_hint ) ) {
					$db_results[] = $hint_ctrl->hint_ctrl_init( $raw_hint );
				}
			}
		}

		return $db_results;
	}

	public function create_prerender_hint( \stdClass $referer_data ):array {
		if ( isset( $referer_data->referer_url ) ) {
			return \PPRH\HintBuilder::create_raw_hint( $referer_data->referer_url, 'prerender', 1, '', '', '', '', $referer_data->nav_to_post_id, 0 );
		}

		return array();
	}

	private function get_most_common_referer_data( \stdClass $postmeta ):\stdClass {
		$count        = 0;
		$referer_data = (object) array();

		if ( ! isset( $postmeta->metadata->referer_data ) ) {
			return $referer_data;
		}

		foreach ( $postmeta->metadata->referer_data as $referer => $ref_count ) {
			if ( $ref_count >= $count ) {
				$count = $ref_count;

				if ( isset( $postmeta->metadata->updated, $postmeta->metadata->date_created, $postmeta->metadata->nav_to_post_url ) ) {
					$referer_data = (object) array(
						'updated'         => $postmeta->metadata->updated,
						'date_created'    => $postmeta->metadata->date_created,
						'referer_url'     => $referer,
						'nav_to_post_id'  => $postmeta->post_id,
						'nav_to_post_url' => $postmeta->metadata->nav_to_post_url
					);
				}
			}
		}

		return $referer_data;
	}

	public function get_response_object( array $db_results ):\stdClass {
		if ( empty( $db_results ) ) {
			$db_result = \PPRH\DAO::create_db_result( 'There is not enough data to create prerender hints yet. Please try again later.', false, 0 );
		} elseif ( 1 === count( $db_results ) ) {
			$db_result = \PPRH\DAO::create_db_result( '', true, 0 );
		} else {
			$db_result = \PPRH\DAO::create_db_result( '', true, 0 );
		}

		return $db_result;
	}

	public function add_prerender_notice() {

		if ( Utils::isArrayAndNotEmpty( $this->prerender_results ) ) {
			$hints_created = $this->get_new_hint_total( $this->prerender_results );
			$msg = ( $hints_created > 0 ) ? "A total of $hints_created prerender hints have just been generated automatically." : "There is insufficient new data to generate new prerender hints.";
		} else {
			$msg = 'There is not sufficient visitor data to generate prerender hints yet.';
		}

		$msg .= " Data will be collected and fresh prerender hints will be generated in $this->prerender_auto_reset_days days.";
		Utils::show_notice( $msg, true );
	}

	public function get_new_hint_total( array $results ):int {
		$count = 0;

		foreach ( $results as $result ) {
			if ( isset( $result->new_hint, $result->db_result['status'] ) && $result->db_result['status'] && ! empty( $result->new_hint ) ) {
				$count++;
			}
		}

		return $count;
	}

	public function get_meta_values( $post_id, string $meta_key, bool $show_posts_on_front ) {
		$meta_values = array();

		if ( 'global' === $post_id ) {
			$postmeta_arr = DAO::get_all_postmeta_values( $meta_key );

			foreach( $postmeta_arr as $postmeta ) {
				if ( isset( $postmeta->post_id, $postmeta->meta_value ) ) {
					$meta_value = unserialize( $postmeta->meta_value, array( false ) );

					if ( isset( $meta_value->metadata ) ) {
						$post_metadata_obj = UtilsPro::create_post_metadata_obj( $postmeta->post_id, $meta_value->metadata );
						$meta_values[] = $post_metadata_obj;
					}
				}
			}

			if ( $show_posts_on_front ) {
				$meta_values[] = $this->get_post_metadata( 0, $meta_key );
			}

		}
		else {
			$post_info = $this->get_post_metadata( $post_id, $meta_key );
			if ( isset( $post_info->metadata ) ) {
				$meta_values[] = UtilsPro::create_post_metadata_obj( $post_id, $post_info->metadata );
			}
		}

		return $meta_values;
	}

	public function get_post_metadata( int $post_id, string $meta_key ):\stdClass {
		$post_meta = ( 0 === $post_id ) ? \get_option( $meta_key . '_home' ) : \get_post_meta( $post_id, $meta_key, true );
		return ( is_object( $post_meta ) ) ? $post_meta : (object) array();
	}

}
