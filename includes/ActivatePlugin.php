<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ActivatePlugin {

	public $default_option_values;

	public function __construct() {
		if ( ! defined( 'PPRH_DB_TABLE' ) ) {
			global $wpdb;
			$table = $wpdb->prefix . 'pprh_table';
			define( 'PPRH_DB_TABLE', $table );
		}

		// default options
		$default_modal_post_types = $this->get_default_modal_post_types();
		$this->default_option_values = array(
			'clear_dup_nonglobals'                 => 'true',
//			'duplicate_hint_removal_percent'       => '65',
			'post_modal_types'                     => $default_modal_post_types,
			'preload_enabled'                      => 'true',
			'prerender_enabled'                    => 'true',
			'prerender_auto_reset_days'            => '30',
			'prerender_enable_for_logged_in_users' => 'false',
//			'pprh_preconnect_set'              => array( '' ),
//			'pprh_preload_set'              => array( '' )
		);
	}

	public function activate_plugin_init() {
		$is_multisite         = \is_multisite();
		$saved_default_option = \get_option( 'pprh_options', array() );

		$this->create_options( $saved_default_option );
//		$this->update_table_schema( $is_multisite );

		// set the prerender transient if it is not there.
		if ( false === \get_transient( 'pprh_prerender_reset' ) ) {
			$prerender_auto_reset_days = \PPRH\Utils\Utils::get_json_option_value( 'pprh_options', 'prerender_auto_reset_days' );
			\set_transient( 'pprh_prerender_reset', 'true', ( $prerender_auto_reset_days * DAY_IN_SECONDS ) );
		}

		return true;
	}

	public function create_options( array $saved_default_option ):array {
		$results = array();
		\add_option( 'pprh_debug_enabled', 'true', '', 'yes' );

		if ( empty( $saved_default_option ) ) {
			\add_option( 'pprh_options', $this->default_option_values, '', 'yes' );
			$results[] = $this->default_option_values;
		}

		// if the original option has any values missing for some reason, add them back with the default value.
		elseif ( ! $this->count_equals( $this->default_option_values, $saved_default_option ) ) {
			foreach( $this->default_option_values as $default_option => $value ) {
				if ( ! isset( $saved_default_option[$default_option] ) ) {
					\PPRH\Utils\Utils::update_json_option( 'pprh_options', $default_option, $value );
					$results[] = $default_option;
				}
			}
		}

		return $results;
	}

	public function get_default_modal_post_types():array {
		global $wp_post_types;
		$results = array();

		foreach ( $wp_post_types as $post_type ) {
			if ( $post_type->public && 'attachment' !== $post_type->name ) {
				$results[] = $post_type->name;
			}
		}

		return ( empty( $results ) ? $results : array( 'post', 'page' ) );
	}

	public function count_equals( array $arr_1, array $arr_2 ):bool {
		return count( $arr_1 ) === count( $arr_2 );
	}

}
