<?php

namespace PPRH;

use PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PrerenderAnalytics {

	private $meta_key;

	public function __construct() {
		$this->meta_key = 'pprh_prerender_data';
	}

	public function config( $client_post_id ) {
		$user_logged_in               = \is_user_logged_in();
		$option_enabled_for_logged_in = ( 'true' === \PPRH\Utils\Utils::get_json_option_value( 'pprh_options', 'prerender_enable_for_logged_in_users' ) );
		$browser                      = \PPRH\Utils\Debug::get_browser();
		$this->config_ctrl( $client_post_id, $user_logged_in, $option_enabled_for_logged_in, $browser );
	}

	public function config_ctrl( int $client_post_id, bool $user_logged_in, bool $option_enabled_for_logged_in, string $browser ) {
		$do_init = $this->do_init( $user_logged_in, $option_enabled_for_logged_in, $browser );

		if ( $do_init && $client_post_id >= 0 ) {
			$this->analytics_init( $client_post_id );
		}
	}


	public function do_init( bool $user_logged_in, bool $option_enabled_for_logged_in, $browser ):bool {
		return ( ( ! $user_logged_in || $option_enabled_for_logged_in ) && ( '' !== $browser ) );
	}

	public function analytics_init( int $post_id ) {
		$nav_to_post_url    = UtilsPro::get_request_uri();
		$postmeta           = \get_post_meta( $post_id, $this->meta_key, true );
		$referer_args       = $this->get_referer_args( $post_id, $nav_to_post_url );
		$referer_args_valid = $this->are_referer_args_valid( $referer_args );

		if ( ! $referer_args_valid ) {
			return false;
		}

		if ( empty( $postmeta ) ) {
			$postmeta = (object) array( 'post_id' => $post_id );
		}

		$new_post_meta = $this->create_postmeta_data( $postmeta, $referer_args );

		if ( ! isset( $new_post_meta ) ) {
			return false;
		}

		$post_metadata_obj = UtilsPro::create_post_metadata_obj( $post_id, $new_post_meta );
		$updated           = $this->update_post_info( $post_metadata_obj, $this->meta_key );
		return ( is_bool( $updated ) ) ? $updated : false;
	}


	private function get_referer_args( int $post_id, string $nav_to_post_url ):\stdClass {
		$referer = Utils::get_referer();

		return (object) array(
			'post_id'         => $post_id,
			'referer'         => $referer,
			'site_url'        => PPRH_SITE_URL,
			'admin_url'       => \get_admin_url(),
			'nav_to_post_url' => $nav_to_post_url
		);
	}

	public function are_referer_args_valid( \stdClass $args ):bool {
		if ( isset( $args->referer, $args->admin_url, $args->site_url ) ) {
			$login_url = \wp_login_url();
			$referer_is_wp_login       = str_contains( $args->referer, $login_url );
			$referer_is_admin_url      = str_contains( $args->referer, $args->admin_url );
			$referer_is_from_same_site = str_contains( $args->referer, $args->site_url );
			return ( $referer_is_from_same_site && ! $referer_is_admin_url && ! $referer_is_wp_login );
		}

		return false;
	}

	public function create_postmeta_data( \stdClass $postmeta, \stdClass $args ):\stdClass {
		if ( ! isset( $args->referer, $args->nav_to_post_url, $postmeta->post_id ) ) {
			return (object) array();
		}

		$referer_count = $this->calculate_referer_traffic_count( $postmeta, $args->referer );
		$referer_data  = array( $args->referer => $referer_count );
		$new_metadata  = $this->create_prerender_metadata_obj( $referer_data, $args->nav_to_post_url );

		return UtilsPro::create_post_metadata_obj( $postmeta->post_id, $new_metadata );
	}

	public function create_prerender_metadata_obj( array $referer_data, string $nav_to_post_url ):\stdClass {
		$time = time();

		return (object) array(
			'updated'         => $time,
			'date_created'    => $time,
			'referer_data'    => $referer_data,
			'nav_to_post_url' => $nav_to_post_url
		);
	}

	public function calculate_referer_traffic_count( \stdClass $postmeta, string $referer ):int {
		// if stored postmeta data contains the existing referer, increase the count by 1. otherwise return 0 as count.
		if ( isset( $postmeta->referer_data[ $referer ] ) ) {
			$previous_count = (int) $postmeta->referer_data[ $referer ];
			return ++$previous_count;
		}

		return 1;
	}

	private function update_post_info( \stdClass $post_metadata_obj, string $meta_key ) {
		if ( isset( $post_metadata_obj->post_id, $post_metadata_obj->metadata ) ) {
			$post_id  = $post_metadata_obj->post_id;
			$metadata = $post_metadata_obj->metadata;
			return ( 0 === $post_id ) ? Utils::update_option( $meta_key . '_home', $metadata ) : \PPRH\Utils\Utils::update_post_meta( $post_id, $meta_key, $metadata );
		}

		return false;
	}

}
